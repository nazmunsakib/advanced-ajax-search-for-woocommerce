<?php
/**
 * Database Migration System
 *
 * Runs incremental, version-tracked migrations whenever the plugin
 * is updated. Each migration method runs exactly once and is idempotent.
 *
 * @package NivoSearch
 * @since 1.2.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Migrator Class
 *
 * Usage: call Migrator::maybe_migrate() on plugins_loaded.
 * Add new migrations as private static methods named migrate_to_X_Y_Z().
 * Register them in the MIGRATIONS constant in version order.
 * IMPORTANT: Never reorder or rename existing entries — only append new ones.
 *
 * @since 1.2.0
 */
class Migrator {

	/**
	 * Ordered list of migrations: version string => method name.
	 * Add new entries at the bottom — never reorder existing ones.
	 *
	 * @since 1.2.0
	 * @var array<string,string>
	 */
	const MIGRATIONS = array(
		'1.2.0' => 'migrate_to_1_2_0',
		'1.3.0' => 'migrate_to_1_3_0',
		'1.4.0' => 'migrate_to_1_4_0',
		'1.5.0' => 'migrate_to_1_5_0',
		// 2.0.2: consolidation migration for users updating from the live 2.0.1 release.
		// The live 2.0.1 stored nivo_search_db_version = '2.0.1', which is greater than
		// all the 1.x.x keys above — so those migrations are skipped for existing users.
		// This entry ensures they still get all new DB tables and meta backfills.
		// All methods called here are idempotent (IF NOT EXISTS / array_merge), so running
		// them twice on a fresh install is completely safe.
		'2.0.2' => 'migrate_to_2_0_2',
	);

	/**
	 * Run any pending migrations.
	 *
	 * Compares the stored DB version with the current plugin version
	 * and executes every migration whose version is greater than the stored one.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function maybe_migrate() {
		$stored_version = get_option( 'nivo_search_db_version', '0.0.0' );

		// Nothing to do if already at current version.
		if ( version_compare( $stored_version, NIVO_SEARCH_VERSION, '>=' ) ) {
			return;
		}

		foreach ( self::MIGRATIONS as $version => $method ) {
			if ( version_compare( $stored_version, $version, '<' ) ) {
				self::$method();
			}
		}

		// Stamp the new version after all migrations complete.
		update_option( 'nivo_search_db_version', NIVO_SEARCH_VERSION );

		/**
		 * Fires after all pending migrations have completed.
		 *
		 * @since 1.2.0
		 * @param string $previous_version The version before migration.
		 * @param string $current_version  The version after migration.
		 */
		do_action( 'nivo_search_migrations_complete', $stored_version, NIVO_SEARCH_VERSION );
	}

	/**
	 * Migration: 1.2.0
	 *
	 * Backfills new meta keys introduced in v1.2.0 for all existing presets.
	 * Uses array_merge( $defaults, $existing ) so existing user values are
	 * never overwritten — only missing keys are added.
	 *
	 * Keys added:
	 * - _nivo_search_generale: `delay` (300ms debounce)
	 * - _nivo_search_display: `show_ratings`, `show_stock_status`, `show_category_badge`, `show_qty_selector`
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private static function migrate_to_1_2_0() {
		$presets = get_posts( array(
			'post_type'      => 'nivo_search_preset',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		if ( empty( $presets ) ) {
			return;
		}

		foreach ( $presets as $preset_id ) {

			// --- Generale settings: add `delay` if missing ---
			$generale = get_post_meta( $preset_id, '_nivo_search_generale', true );
			if ( ! is_array( $generale ) ) {
				$generale = array();
			}
			$generale_defaults = array(
				'limit'       => 10,
				'min_chars'   => 2,
				'delay'       => 300,
				'placeholder' => __( 'Search products...', 'nivo-ajax-search-for-woocommerce' ),
			);
			$generale = array_merge( $generale_defaults, $generale );
			update_post_meta( $preset_id, '_nivo_search_generale', $generale );

			// --- Query settings: add Phase-3 keys if missing ---
			$query = get_post_meta( $preset_id, '_nivo_search_query', true );
			if ( ! is_array( $query ) ) {
				$query = array();
			}
			$query_defaults = array(
				'search_in_title'           => 1,
				'search_in_sku'             => 1,
				'search_in_content'         => 1,
				'search_in_excerpt'         => 1,
				'search_product_categories' => 1,
				'search_product_tags'       => 0,
				'exclude_out_of_stock'      => 0,
			);
			$query = array_merge( $query_defaults, $query );
			update_post_meta( $preset_id, '_nivo_search_query', $query );

			// --- Display settings: add Phase-2 keys if missing ---
			$display = get_post_meta( $preset_id, '_nivo_search_display', true );
			if ( ! is_array( $display ) ) {
				$display = array();
			}
			$display_defaults = array(
				'show_images'         => 1,
				'show_price'          => 1,
				'show_sku'            => 1,
				'show_description'    => 1,
				'show_stock_status'   => 1,
				'show_category_badge' => 0,
				'show_add_to_cart'    => 0,
				'show_qty_selector'   => 0,
				'show_view_all'       => 1,
			);
			$display = array_merge( $display_defaults, $display );
			update_post_meta( $preset_id, '_nivo_search_display', $display );
		}
	}

	/**
	 * Migration: 1.3.0
	 *
	 * Creates the wp_nivo_search_index table used by the fuzzy search engine.
	 * Uses CREATE TABLE IF NOT EXISTS so it is safe to run on fresh installs
	 * and on upgrades from any earlier version.
	 *
	 * @since 2.0.2
	 * @return void
	 */
	private static function migrate_to_1_3_0() {
		Product_Indexer::maybe_create_table();
	}

	/**
	 * Migration: 1.4.0
	 *
	 * Creates the wp_nivo_search_corrections_log table used by Search_Analytics
	 * to track which queries were auto-corrected and how often.
	 *
	 * @since 2.0.2
	 * @return void
	 */
	private static function migrate_to_1_4_0() {
		Search_Analytics::maybe_create_table();
	}

	/**
	 * Migration: 1.5.0
	 *
	 * Backfills display meta keys introduced in v1.5.0 for all existing presets:
	 * - show_add_to_cart (default: 0 — opt-in)
	 * - show_view_all    (default: 1 — always show)
	 *
	 * Uses array_merge( $defaults, $existing ) so user-saved values are never
	 * overwritten — only missing keys are added.
	 *
	 * @since 2.0.2
	 * @return void
	 */
	private static function migrate_to_1_5_0() {
		$presets = get_posts( array(
			'post_type'      => 'nivo_search_preset',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		if ( empty( $presets ) ) {
			return;
		}

		foreach ( $presets as $preset_id ) {
			$display = get_post_meta( $preset_id, '_nivo_search_display', true );
			if ( ! is_array( $display ) ) {
				$display = array();
			}

			$display_defaults = array(
				'show_add_to_cart' => 0,
				'show_view_all'    => 1,
			);

			// Only fill keys that are genuinely absent (array_merge puts defaults first,
			// so existing user values in $display overwrite the defaults).
			$display = array_merge( $display_defaults, $display );
			update_post_meta( $preset_id, '_nivo_search_display', $display );
		}
	}

	/**
	 * Migration: 2.0.2
	 *
	 * Consolidation migration for users updating from the live 2.0.1 release.
	 * Their stored nivo_search_db_version is '2.0.1', which is greater than all
	 * the 1.x.x migration keys, so those are skipped. This method runs all of
	 * them explicitly to ensure every existing installation gets the full schema.
	 *
	 * All methods are idempotent (CREATE TABLE IF NOT EXISTS, array_merge), so
	 * running them again on a fresh install causes no harm.
	 *
	 * @since 2.0.2
	 * @return void
	 */
	private static function migrate_to_2_0_2() {
		self::migrate_to_1_2_0();
		self::migrate_to_1_3_0();
		self::migrate_to_1_4_0();
		self::migrate_to_1_5_0();
	}
}
