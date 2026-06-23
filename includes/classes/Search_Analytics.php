<?php
/**
 * Search Correction Analytics
 *
 * Tracks every search where a typo correction was applied.
 * Uses an UPSERT pattern so a search for "iphne" increments the same row
 * rather than creating duplicates. This keeps the table small and fast.
 *
 * @package NivoSearch
 * @since   2.2.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Search_Analytics Class
 *
 * Table: wp_nivo_search_corrections_log
 *
 * @since 2.2.0
 */
class Search_Analytics {

	/**
	 * Unprefixed table name.
	 *
	 * @since 2.2.0
	 */
	const TABLE_NAME = 'nivo_search_corrections_log';

	/**
	 * Return the full (prefixed) table name.
	 *
	 * @since 2.2.0
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Create the analytics table if it does not already exist.
	 *
	 * Safe to call repeatedly (CREATE TABLE IF NOT EXISTS).
	 *
	 * Schema:
	 *   search_term    — original misspelled query
	 *   corrected_term — what the system corrected it to
	 *   search_count   — cumulative number of times this pair was searched
	 *   last_searched  — datetime of the most recent occurrence
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public static function maybe_create_table() {
		global $wpdb;

		$table           = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			search_term varchar(200) NOT NULL,
			corrected_term varchar(200) NOT NULL,
			search_count bigint(20) unsigned NOT NULL DEFAULT 1,
			last_searched datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_correction (search_term(100),corrected_term(100)),
			KEY idx_count (search_count),
			KEY idx_last_searched (last_searched)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Log (or increment) a correction event.
	 *
	 * Uses INSERT … ON DUPLICATE KEY UPDATE so one DB call handles both
	 * new rows and existing ones.
	 *
	 * @since 2.2.0
	 * @param string $search_term    The original misspelled query.
	 * @param string $corrected_term The corrected query.
	 * @return void
	 */
	public static function log_correction( $search_term, $corrected_term ) {
		global $wpdb;

		$search_term    = sanitize_text_field( $search_term );
		$corrected_term = sanitize_text_field( $corrected_term );

		if ( '' === $search_term || '' === $corrected_term ) {
			return;
		}

		// Don't log when the "correction" is the same as the input.
		if ( strtolower( $search_term ) === strtolower( $corrected_term ) ) {
			return;
		}

		$table = self::get_table_name();
		if ( ! self::table_exists() ) {
			return;
		}

		$now = current_time( 'mysql' );

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (search_term, corrected_term, search_count, last_searched)
				 VALUES (%s, %s, 1, %s)
				 ON DUPLICATE KEY UPDATE search_count = search_count + 1, last_searched = %s",
				$search_term,
				$corrected_term,
				$now,
				$now
			)
		);
	}

	/**
	 * Return the top N correction pairs by search count.
	 *
	 * @since 2.2.0
	 * @param int $limit Maximum rows to return (default 25).
	 * @return array Array of stdClass objects with search_term, corrected_term, search_count, last_searched.
	 */
	public static function get_top_corrections( $limit = 25 ) {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return array();
		}

		$table = self::get_table_name();

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT search_term, corrected_term, search_count, last_searched
				 FROM {$table}
				 ORDER BY search_count DESC
				 LIMIT %d",
				$limit
			)
		);
	}

	/**
	 * Return the total number of correction events logged today.
	 *
	 * @since 2.2.0
	 * @return int
	 */
	public static function get_today_count() {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();
		$today = current_time( 'Y-m-d' );

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COALESCE(SUM(search_count), 0) FROM {$table} WHERE DATE(last_searched) = %s",
				$today
			)
		);
	}

	/**
	 * Return the single most-searched correction pair.
	 *
	 * @since 2.2.0
	 * @return object|null stdClass with search_term, corrected_term, search_count, or null.
	 */
	public static function get_most_corrected() {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return null;
		}

		$table = self::get_table_name();

		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT search_term, corrected_term, search_count FROM {$table} ORDER BY search_count DESC LIMIT 1"
		);
	}

	/**
	 * Get total number of unique correction pairs logged.
	 *
	 * @since 2.2.0
	 * @return int
	 */
	public static function get_total_pairs() {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore
	}

	/**
	 * Delete all rows from the analytics table.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public static function clear_log() {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return;
		}

		$wpdb->query( 'TRUNCATE TABLE ' . self::get_table_name() ); // phpcs:ignore
	}

	/**
	 * Check whether the analytics table exists in the DB.
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	public static function table_exists() {
		global $wpdb;
		$table = self::get_table_name();
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}
}
