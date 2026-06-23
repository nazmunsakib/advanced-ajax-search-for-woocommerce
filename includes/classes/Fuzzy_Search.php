<?php
/**
 * Fuzzy Search Engine
 *
 * Runs Levenshtein-based typo matching against the search index rather than
 * directly against product titles. This makes fuzzy matching O(candidates)
 * not O(all_products), keeping it fast even on large catalogues.
 *
 * Priority chain (called when WP_Query returns 0 products):
 *   1. Prefix/partial SQL candidate narrowing against wp_nivo_search_index
 *   2. Levenshtein distance check on the small candidate set
 *   3. Return matching product IDs + "did_you_mean" suggestion
 *
 * @package NivoSearch
 * @since 2.0.2
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Fuzzy_Search Class
 *
 * @since 2.0.2
 */
class Fuzzy_Search {

	/**
	 * Run a fuzzy search against the product index.
	 *
	 * Tokenises the query, finds candidate index tokens via SQL prefix/partial
	 * matching, applies Levenshtein on the candidate set only, then returns
	 * the matching product IDs and a "did_you_mean" suggestion when the query
	 * was corrected.
	 *
	 * @since 2.0.2
	 * @param string $query  User search query (already sanitised).
	 * @param array  $args   Search arguments (limit, excluded_products, exclude_out_of_stock).
	 * @return array {
	 *   @type int[]       $product_ids Array of matching product post IDs.
	 *   @type string|null $did_you_mean Suggested corrected query, or null.
	 * }
	 */
	public function search( $query, $args ) {
		global $wpdb;

		$table = Product_Indexer::get_table_name();

		// Guard: table must exist before querying it.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return array( 'product_ids' => array(), 'did_you_mean' => null );
		}

		$tokens = $this->tokenize( $query );
		if ( empty( $tokens ) ) {
			return array( 'product_ids' => array(), 'did_you_mean' => null );
		}

		// Read admin-configured max Levenshtein distance (default 2).
		$global_max_dist = (int) get_option( 'nivo_search_max_typo_distance', 2 );
		$global_max_dist = max( 1, min( 2, $global_max_dist ) );

		$all_product_ids = array();
		$corrections     = array(); // original_word => corrected_word

		foreach ( $tokens as $token ) {
			$token_len = strlen( $token );

			// Skip tokens too short to produce meaningful fuzzy matches.
			if ( $token_len < 3 ) {
				continue;
			}

			// Tighter distance for short tokens to avoid false positives.
			$max_dist = $token_len <= 5 ? 1 : $global_max_dist;

			// Step 1: Narrow candidates via SQL (prefix of first 2 chars + length window).
			$candidates = $this->get_candidate_tokens( $token, $table );
			if ( empty( $candidates ) ) {
				continue;
			}

			// Step 2: Levenshtein on the small candidate set.
			$best = $this->find_best_match( $token, $candidates, $max_dist );
			if ( null === $best ) {
				continue;
			}

			// Record correction if the query word changed.
			if ( $best['token'] !== $token ) {
				$corrections[ $token ] = $best['token'];
			}

			// Step 3: Fetch product IDs for the matched token.
			$ids             = $this->get_products_for_token( $best['token'], $table, $args );
			$all_product_ids = array_merge( $all_product_ids, $ids );
		}

		// Build the "did_you_mean" suggestion by substituting corrected words.
		$did_you_mean = null;
		if ( ! empty( $corrections ) ) {
			$words           = explode( ' ', $query );
			$corrected_words = array_map(
				static function ( $word ) use ( $corrections ) {
					$key = strtolower( $word );
					return isset( $corrections[ $key ] ) ? $corrections[ $key ] : $word;
				},
				$words
			);
			$corrected_query = implode( ' ', $corrected_words );
			if ( $corrected_query !== $query ) {
				$did_you_mean = $corrected_query;
			}
		}

		return array(
			'product_ids' => array_unique( array_map( 'intval', $all_product_ids ) ),
			'did_you_mean' => $did_you_mean,
		);
	}

	/**
	 * Fetch candidate index tokens for a given query token.
	 *
	 * Uses a two-clause LIKE query:
	 *   1. Prefix match on the first 2 characters (fast — covered by idx_token).
	 *   2. Substring match as a fallback for transpositions.
	 * A CHAR_LENGTH window of ±2 chars keeps the candidate set small.
	 *
	 * @since 2.0.2
	 * @param string $token Search token (lowercase).
	 * @param string $table Full table name.
	 * @return string[] Array of candidate tokens.
	 */
	private function get_candidate_tokens( $token, $table ) {
		global $wpdb;

		$len    = strlen( $token );
		$prefix = $wpdb->esc_like( substr( $token, 0, 2 ) ) . '%';
		$substr = '%' . $wpdb->esc_like( $token ) . '%';

		$results = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT DISTINCT token FROM {$table}
				 WHERE ( token LIKE %s OR token LIKE %s )
				   AND CHAR_LENGTH(token) BETWEEN %d AND %d
				 LIMIT 300",
				$prefix,
				$substr,
				max( 1, $len - 2 ),
				$len + 2
			)
		);

		return $results ?: array();
	}

	/**
	 * Find the closest candidate token within the allowed Levenshtein distance.
	 *
	 * Returns the candidate with the lowest edit distance, or null if no candidate
	 * is within $max_dist edits.
	 *
	 * @since 2.0.2
	 * @param string   $token      Query token (lowercase).
	 * @param string[] $candidates Candidate tokens from the index.
	 * @param int      $max_dist   Maximum allowed Levenshtein distance.
	 * @return array|null ['token' => string, 'dist' => int] or null.
	 */
	private function find_best_match( $token, $candidates, $max_dist ) {
		$best_dist  = PHP_INT_MAX;
		$best_token = null;

		foreach ( $candidates as $candidate ) {
			// Quick length pre-filter — can't be within $max_dist if length differs more.
			if ( abs( strlen( $token ) - strlen( $candidate ) ) > $max_dist ) {
				continue;
			}

			$dist = levenshtein( $token, $candidate );

			// Exact match — stop immediately.
			if ( 0 === $dist ) {
				return array( 'token' => $candidate, 'dist' => 0 );
			}

			if ( $dist <= $max_dist && $dist < $best_dist ) {
				$best_dist  = $dist;
				$best_token = $candidate;
			}
		}

		return $best_token !== null ? array( 'token' => $best_token, 'dist' => $best_dist ) : null;
	}

	/**
	 * Fetch product IDs associated with a token in the index.
	 *
	 * Uses a prefix LIKE so "shoe" also matches "shoes", "shoebox", etc.
	 * Applies exclusion, out-of-stock, and publish status filters.
	 *
	 * @since 2.0.2
	 * @param string $token Matched index token.
	 * @param string $table Full table name.
	 * @param array  $args  Search arguments.
	 * @return int[] Array of published product IDs.
	 */
	private function get_products_for_token( $token, $table, $args ) {
		global $wpdb;

		$like  = $wpdb->esc_like( $token ) . '%';
		$limit = isset( $args['limit'] ) ? (int) $args['limit'] : 10;

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT DISTINCT product_id FROM {$table} WHERE token LIKE %s LIMIT %d",
				$like,
				$limit
			)
		);

		if ( empty( $ids ) ) {
			return array();
		}

		$ids = array_map( 'intval', $ids );

		// Remove explicitly excluded products.
		$exclude = ! empty( $args['excluded_products'] ) ? array_map( 'intval', $args['excluded_products'] ) : array();
		if ( ! empty( $exclude ) ) {
			$ids = array_values( array_diff( $ids, $exclude ) );
		}

		// Filter out-of-stock products if requested.
		// Use a single wc_get_products() call (batch) instead of one
		// wc_get_product() per ID to avoid N+1 queries.
		if ( ! empty( $args['exclude_out_of_stock'] ) && ! empty( $ids ) ) {
			$in_stock_products = wc_get_products(
				array(
					'include'      => $ids,
					'stock_status' => 'instock',
					'limit'        => count( $ids ),
					'return'       => 'ids',
				)
			);
			// Preserve original relevance order — only keep IDs that are in stock.
			$in_stock_set = array_flip( $in_stock_products );
			$ids          = array_values( array_filter( $ids, static fn( $id ) => isset( $in_stock_set[ $id ] ) ) );
		}

		// Only published products.
		$ids = array_values(
			array_filter(
				$ids,
				static function ( $id ) {
					return get_post_status( $id ) === 'publish';
				}
			)
		);

		return $ids;
	}

	/**
	 * Tokenise a string into lowercase words (mirrors Product_Indexer::tokenize).
	 *
	 * @since 2.0.2
	 * @param string $text Input text.
	 * @return string[] Tokens.
	 */
	private function tokenize( $text ) {
		$text = strtolower( trim( $text ) );
		$text = preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $text );

		return array_values(
			array_filter(
				preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY ),
				static function ( $t ) {
					return strlen( $t ) >= 2;
				}
			)
		);
	}
}
