<?php
/**
 * Typo Manager
 *
 * Single source of truth for all typo-correction rules.
 * Merges the built-in dictionary with custom rules added by store owners.
 * Caches the merged result in a transient so the dictionary is only loaded
 * and merged once per cache period, not on every search request.
 *
 * @package NivoSearch
 * @since   2.2.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Typo_Manager Class
 *
 * Usage:
 *   // Correct a query (used by Search_Algorithm):
 *   $corrected = Typo_Manager::correct_query( $query, $from, $to );
 *
 *   // CRUD for custom rules (used by Search_Optimization admin page):
 *   Typo_Manager::save_rule( 'nikee', 'Nike' );
 *   Typo_Manager::delete_rule( $index );
 *   Typo_Manager::import_rules( $csv_text );
 *   $csv = Typo_Manager::export_rules_csv();
 *
 * @since 2.2.0
 */
class Typo_Manager {

	/**
	 * Option key for custom rules.
	 *
	 * Stored as an indexed array of ['from' => string, 'to' => string] pairs.
	 *
	 * @since 2.2.0
	 */
	const CUSTOM_RULES_OPTION = 'nivo_search_custom_typo_rules';

	/**
	 * Maximum number of custom typo rules allowed in the free tier.
	 *
	 * Developers can override this via the `nivo_search_max_custom_rules` filter.
	 * Pro tier sets this to PHP_INT_MAX to remove the cap.
	 *
	 * @since 2.3.0
	 */
	const FREE_RULES_LIMIT = 10;

	/**
	 * Transient key for the merged (built-in + custom) rule set.
	 *
	 * @since 2.2.0
	 */
	const CACHE_TRANSIENT = 'nivo_search_merged_typo_rules';

	/**
	 * Transient TTL in seconds (1 hour).
	 *
	 * @since 2.2.0
	 */
	const CACHE_TTL = HOUR_IN_SECONDS;

	// -------------------------------------------------------------------------
	// Public API — rule application
	// -------------------------------------------------------------------------

	/**
	 * Correct a search query using the merged rule set.
	 *
	 * Priority chain:
	 *   1. nivo_search_corrected_term filter (developer override)
	 *   2. Exact match in merged dictionary
	 *   3. Levenshtein fallback against dictionary keys (distance ≤ 2, fast O(dict_size))
	 *
	 * @since  2.2.0
	 * @param  string      $query          Original search query.
	 * @param  string|null &$corrected_from Set to original query when corrected.
	 * @param  string|null &$corrected_to   Set to corrected value when corrected.
	 * @return string Corrected query (or original if no match).
	 */
	public static function correct_query( $query, &$corrected_from, &$corrected_to ) {
		$rules       = self::get_merged_rules();
		$query_lower = strtolower( trim( $query ) );

		/**
		 * Allow developers to short-circuit the correction for a specific term.
		 *
		 * Return a non-null string to override. Return null to continue with
		 * the default pipeline.
		 *
		 * @since 2.2.0
		 * @param string|null $override     Return a corrected term, or null to continue.
		 * @param string      $query        Original query.
		 * @param array       $merged_rules Merged rule set (from => to).
		 */
		$override = apply_filters( 'nivo_search_corrected_term', null, $query, $rules );
		if ( null !== $override ) {
			$corrected_from = $query;
			$corrected_to   = sanitize_text_field( (string) $override );
			return $corrected_to;
		}

		// Exact dictionary match.
		if ( isset( $rules[ $query_lower ] ) ) {
			$corrected_from = $query;
			$corrected_to   = $rules[ $query_lower ];
			return $corrected_to;
		}

		// Levenshtein fallback against dictionary keys (fast — max ~300 keys).
		$lev = self::levenshtein_fallback( $query_lower, $rules );
		if ( null !== $lev ) {
			$corrected_from = $query;
			$corrected_to   = $lev;
			return $corrected_to;
		}

		return $query;
	}

	/**
	 * Get the merged (built-in + custom) rule set, loading from cache if fresh.
	 *
	 * Custom rules always take priority over built-in rules.
	 *
	 * @since 2.2.0
	 * @return array<string,string> Map of lowercase misspelling => correction.
	 */
	public static function get_merged_rules() {
		$cached = get_transient( self::CACHE_TRANSIENT );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Built-in dictionary.
		$data_file = NIVO_SEARCH_PLUGIN_DIR . 'includes/data/typo-corrections.php';
		$built_in  = file_exists( $data_file ) ? (array) include $data_file : array();

		// Custom rules from wp_options.
		$custom_raw = (array) get_option( self::CUSTOM_RULES_OPTION, array() );
		$custom     = array();
		foreach ( $custom_raw as $rule ) {
			if ( ! empty( $rule['from'] ) && ! empty( $rule['to'] ) ) {
				$custom[ strtolower( trim( $rule['from'] ) ) ] = trim( $rule['to'] );
			}
		}

		// Custom overrides built-in.
		$merged = array_merge( $built_in, $custom );

		/**
		 * Filter the complete merged typo correction rule set.
		 *
		 * Developers can add, remove, or replace entries. The filtered result
		 * is cached, so this filter runs at most once per cache period.
		 *
		 * @since 1.0.0 (original filter, now runs inside Typo_Manager)
		 * @param array<string,string> $merged Merged rule set.
		 */
		$merged = (array) apply_filters( 'nivo_search_typo_corrections', $merged );

		set_transient( self::CACHE_TRANSIENT, $merged, self::CACHE_TTL );

		return $merged;
	}

	/**
	 * Invalidate the merged-rules transient cache.
	 *
	 * Must be called after any change to custom rules.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public static function invalidate_cache() {
		delete_transient( self::CACHE_TRANSIENT );
	}

	// -------------------------------------------------------------------------
	// Public API — custom rule CRUD
	// -------------------------------------------------------------------------

	/**
	 * Retrieve all custom rules.
	 *
	 * @since 2.2.0
	 * @return array<int, array{from: string, to: string}> Indexed array of rules.
	 */
	public static function get_custom_rules() {
		return array_values( (array) get_option( self::CUSTOM_RULES_OPTION, array() ) );
	}

	/**
	 * Add or update a custom rule.
	 *
	 * If a rule with the same 'from' already exists it is overwritten in-place.
	 * If $index is provided, that position is updated directly (used for inline edit).
	 *
	 * @since 2.2.0
	 * @param string   $from  Misspelling (case-insensitive, stored lowercase).
	 * @param string   $to    Correction.
	 * @param int|null $index Existing rule index to update, or null to add new.
	 * @return bool|string True on success, false on empty/invalid input,
	 *                     'limit_reached' when the free-tier cap is hit.
	 */
	public static function save_rule( $from, $to, $index = null ) {
		$from = strtolower( sanitize_text_field( trim( $from ) ) );
		$to   = sanitize_text_field( trim( $to ) );

		if ( '' === $from || '' === $to ) {
			return false;
		}

		$rules = self::get_custom_rules();

		if ( null !== $index && isset( $rules[ $index ] ) ) {
			// Direct index update (edit mode) — never counts against the limit.
			$rules[ $index ] = array( 'from' => $from, 'to' => $to );
		} else {
			// Check for an existing rule with the same 'from' key — updating in-place
			// does not count as a new rule, so no limit check here.
			$found = false;
			foreach ( $rules as $i => $rule ) {
				if ( isset( $rule['from'] ) && $rule['from'] === $from ) {
					$rules[ $i ]['to'] = $to;
					$found             = true;
					break;
				}
			}

			if ( ! $found ) {
				// Brand-new rule — enforce the free-tier cap.
				if ( self::is_at_limit() ) {
					return 'limit_reached';
				}
				$rules[] = array( 'from' => $from, 'to' => $to );
			}
		}

		update_option( self::CUSTOM_RULES_OPTION, array_values( $rules ) );
		self::invalidate_cache();

		return true;
	}

	/**
	 * Delete a custom rule by array index.
	 *
	 * @since 2.2.0
	 * @param int $index Zero-based index into the custom rules array.
	 * @return bool True on success, false if index does not exist.
	 */
	public static function delete_rule( $index ) {
		$rules = self::get_custom_rules();
		$index = (int) $index;

		if ( ! isset( $rules[ $index ] ) ) {
			return false;
		}

		array_splice( $rules, $index, 1 );
		update_option( self::CUSTOM_RULES_OPTION, array_values( $rules ) );
		self::invalidate_cache();

		return true;
	}

	/**
	 * Bulk-import rules from plain text.
	 *
	 * Supported line formats:
	 *   misspelling => correction
	 *   misspelling -> correction
	 *   misspelling,correction
	 *
	 * Lines beginning with # are treated as comments and skipped.
	 *
	 * @since  2.2.0
	 * @param  string $raw_text  Multi-line text block.
	 * @param  bool   $overwrite If true, existing custom rules are replaced.
	 * @return array{imported: int, errors: int, limit_reached: int} Import summary.
	 */
	public static function import_rules( $raw_text, $overwrite = false ) {
		if ( $overwrite ) {
			update_option( self::CUSTOM_RULES_OPTION, array() );
			self::invalidate_cache();
		}

		$lines         = preg_split( '/\r\n|\n|\r/', trim( $raw_text ) );
		$imported      = 0;
		$errors        = 0;
		$limit_reached = 0;

		foreach ( $lines as $line ) {
			$line = trim( $line );

			// Skip blank lines and comments.
			if ( '' === $line || '#' === $line[0] ) {
				continue;
			}

			// Detect separator.
			if ( false !== strpos( $line, '=>' ) ) {
				$parts = explode( '=>', $line, 2 );
			} elseif ( false !== strpos( $line, '->' ) ) {
				$parts = explode( '->', $line, 2 );
			} elseif ( false !== strpos( $line, ',' ) ) {
				$parts = explode( ',', $line, 2 );
			} else {
				$errors++;
				continue;
			}

			$from   = trim( $parts[0] );
			$to     = trim( $parts[1] );
			$result = self::save_rule( $from, $to );

			if ( true === $result ) {
				$imported++;
			} elseif ( 'limit_reached' === $result ) {
				$limit_reached++;
				// Stop processing — further rules will also be blocked.
				break;
			} else {
				$errors++;
			}
		}

		return array( 'imported' => $imported, 'errors' => $errors, 'limit_reached' => $limit_reached );
	}

	/**
	 * Export all custom rules as a plain-text file (one rule per line).
	 *
	 * @since 2.2.0
	 * @return string CSV-like text ready to write or output as download.
	 */
	public static function export_rules_csv() {
		$rules = self::get_custom_rules();
		$lines = array(
			'# NivoSearch Custom Typo Rules',
			'# Format: misspelling => correction',
			'# One rule per line. Lines starting with # are comments.',
			'',
		);

		foreach ( $rules as $rule ) {
			if ( ! empty( $rule['from'] ) && ! empty( $rule['to'] ) ) {
				$lines[] = $rule['from'] . ' => ' . $rule['to'];
			}
		}

		return implode( "\n", $lines );
	}

	// -------------------------------------------------------------------------
	// Public API — statistics
	// -------------------------------------------------------------------------

	/**
	 * Return the number of entries in the built-in dictionary.
	 *
	 * @since 2.2.0
	 * @return int
	 */
	public static function get_built_in_count() {
		$data_file = NIVO_SEARCH_PLUGIN_DIR . 'includes/data/typo-corrections.php';
		if ( ! file_exists( $data_file ) ) {
			return 0;
		}
		$rules = include $data_file;
		return is_array( $rules ) ? count( $rules ) : 0;
	}

	/**
	 * Return the number of custom rules saved by the store owner.
	 *
	 * @since 2.2.0
	 * @return int
	 */
	public static function get_custom_count() {
		return count( self::get_custom_rules() );
	}

	/**
	 * Return the effective maximum number of custom rules for the current tier.
	 *
	 * Pro tier can hook `nivo_search_max_custom_rules` and return PHP_INT_MAX
	 * to remove the cap entirely.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public static function get_rules_limit() {
		/**
		 * Filter the maximum number of custom typo rules allowed.
		 *
		 * Pro tier hooks this filter and returns PHP_INT_MAX.
		 *
		 * @since 2.3.0
		 * @param int $limit Current limit (FREE_RULES_LIMIT = 10 on free tier).
		 */
		return (int) apply_filters( 'nivo_search_max_custom_rules', self::FREE_RULES_LIMIT );
	}

	/**
	 * Check whether the custom rules limit has been reached.
	 *
	 * @since 2.3.0
	 * @return bool True when the store is at or above the limit.
	 */
	public static function is_at_limit() {
		return self::get_custom_count() >= self::get_rules_limit();
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Levenshtein fallback: scan dictionary keys for a close match.
	 *
	 * Runs against the dictionary key set only (≤ 300–400 strings),
	 * never against product titles — so it is fast and safe in the free tier.
	 *
	 * Constraints:
	 *   - Query must be ≥ 4 characters (short queries produce false positives).
	 *   - Distance ≤ 2.
	 *   - Similarity ≥ 70 % (similar_text check).
	 *   - 50 ms time budget guard.
	 *
	 * @since 2.2.0 (moved from Search_Algorithm)
	 * @param  string $query_lower Lowercase search query.
	 * @param  array  $rules       Merged rule set (key => value).
	 * @return string|null Corrected term, or null if no confident match.
	 */
	private static function levenshtein_fallback( $query_lower, $rules ) {
		$query_len = strlen( $query_lower );

		// Require at least 5 characters before running Levenshtein against the
		// dictionary. Short real words (logo, case, cord, ring, band…) are only
		// 4 chars long and frequently match unrelated 3-char typo keys at
		// distance 1, producing incorrect corrections (logo → lgo → lego).
		if ( $query_len < 5 ) {
			return null;
		}

		$start      = microtime( true );
		$best_dist  = PHP_INT_MAX;
		$best_value = null;

		foreach ( $rules as $key => $value ) {
			// 50 ms budget guard.
			if ( ( microtime( true ) - $start ) > 0.05 ) {
				break;
			}

			$key_len = strlen( $key );

			// Quick length pre-filter — distance can't be ≤ 2 if lengths differ by > 2.
			if ( abs( $query_len - $key_len ) > 2 ) {
				continue;
			}

			$dist = levenshtein( $query_lower, $key );

			if ( $dist > 2 ) {
				continue;
			}

			// Confidence gate: ≥ 70 % character similarity.
			similar_text( $query_lower, $key, $pct );
			if ( $pct < 70.0 ) {
				continue;
			}

			if ( $dist < $best_dist ) {
				$best_dist  = $dist;
				$best_value = $value;
			}
		}

		return $best_value;
	}
}
