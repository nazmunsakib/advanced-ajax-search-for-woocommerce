<?php
/**
 * Search Algorithm
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Search Algorithm Class
 *
 * Handles nivo search
 *
 * @since 1.0.0
 */
class Search_Algorithm {
    
    /**
     * Search products
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Additional search arguments
     * @return array Search results
     */
    /**
     * Search products
     *
     * @since 1.0.0
     * @param string $query Search query.
     * @param array  $args  Additional search arguments.
     * @return array Search results: products, categories, tags, total, execution_time.
     */
    public function search( $query, $args = array() ) {
        $start_time = microtime( true );

        // Default arguments.
        $defaults = array(
            'limit'                     => 10,
            'post_types'                => array( 'product' ),
            'post_status'               => 'publish',
            'search_fields'             => array( 'title', 'content', 'sku' ),
            'exclude_out_of_stock'      => false,
            'excluded_products'         => array(),
            'tax_query'                 => array(),
            'meta_query'                => array(),
            'search_product_categories' => 0,
            'search_product_tags'       => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        // Map 'exclude' to 'excluded_products' for back-compat.
        if ( ! empty( $args['exclude'] ) && empty( $args['excluded_products'] ) ) {
            $args['excluded_products'] = $args['exclude'];
        }

        // Sanitize query.
        $query = sanitize_text_field( $query );

        // Capture pre-normalization form so get_categories/get_tags can also
        // run a complementary search for accented term names ("Café Accessories").
        $raw_query = $query;

        // Normalize diacritical marks (café → cafe, résumé → resume, etc.).
        $query = self::normalize_diacritics( $query );

        // Keep original for typo-correction retry (applied only after 0 results).
        $original_query = $query;
        $corrected_from = null;
        $corrected_to   = null;

        // Get matching tags if enabled — pass both normalized and raw queries so
        // that accented taxonomy names are found regardless of MySQL collation,
        // and pass the current language so WPML/Polylang filter results correctly.
        $tags = [];
        if ( ! empty( $args['search_product_tags'] ) ) {
            $tags = $this->get_tags( $query, $raw_query, $args );
        }

        // Get matching categories if enabled — same dual-query + language strategy.
        $categories = [];
        if ( ! empty( $args['search_product_categories'] ) ) {
            $categories = $this->get_categories( $query, $raw_query, $args );
        }

        // -------------------------------------------------------
        // Helper: run WP_Query for a given search term.
        // -------------------------------------------------------
        $run_wp_query = function( $search_term ) use ( $args ) {
            add_filter( 'posts_search',   array( $this, 'search_where' ),    10, 2 );
            add_filter( 'posts_join',     array( $this, 'search_join' ),     10, 2 );
            add_filter( 'posts_distinct', array( $this, 'search_distinct' ), 10, 2 );

            $qargs = array(
                'post_type'              => $args['post_types'],
                'post_status'            => $args['post_status'],
                'posts_per_page'         => $args['limit'],
                's'                      => $search_term,
                'nivo_search_args'       => $args,
                'tax_query'              => $args['tax_query'],
                'meta_query'             => $args['meta_query'],
                'post__not_in'           => $args['excluded_products'],
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => true,
            );

            if ( $args['exclude_out_of_stock'] === 'yes' || $args['exclude_out_of_stock'] === 1 || $args['exclude_out_of_stock'] === true ) {
                $qargs['meta_query'][] = array(
                    'key'     => '_stock_status',
                    'value'   => 'outofstock',
                    'compare' => '!=',
                );
            }

            $qargs = self::apply_language_filter( $qargs );

            $wq = new \WP_Query( $qargs );

            remove_filter( 'posts_search',   array( $this, 'search_where' ),    10 );
            remove_filter( 'posts_join',     array( $this, 'search_join' ),     10 );
            remove_filter( 'posts_distinct', array( $this, 'search_distinct' ), 10 );

            return $wq;
        };

        // -------------------------------------------------------
        // Pass 1: search with the diacritics-normalized query.
        // "café" → "cafe", so users find ASCII-titled products.
        // We do NOT apply typo corrections yet — valid product words
        // like "logo", "case", "cord" must not be mangled by the
        // Levenshtein fallback before we even try the real search.
        // -------------------------------------------------------
        $search_query    = $run_wp_query( $original_query );
        $ranked_products = $this->rank_results( $search_query->posts, $original_query, $args );

        // -------------------------------------------------------
        // Pass 1b (diacritics complement): if the user typed without
        // accents and Pass 1 found nothing, also try the RAW
        // (pre-normalization) query so that products whose titles
        // contain actual accent characters ("Café Mug") are found.
        // MySQL LIKE is accent-sensitive in most WP collations, so
        // "cafe" won't match "Café" — this pass covers the gap.
        // Only runs when the raw sanitized query differs from the
        // normalized one (i.e. normalization actually removed accents).
        // -------------------------------------------------------
        // $raw_query already captured above (post-sanitize, pre-normalization) —
        // no need to re-read $_POST here; using the local variable is cleaner and
        // works even when search() is called programmatically (not via AJAX).
        if ( empty( $ranked_products ) && '' !== $raw_query && $raw_query !== $original_query ) {
            $alt_query   = $run_wp_query( $raw_query );
            $alt_ranked  = $this->rank_results( $alt_query->posts, $raw_query, $args );
            if ( ! empty( $alt_ranked ) ) {
                $ranked_products = $alt_ranked;
            }
        }

        // -------------------------------------------------------
        // Pass 2 (typo-correction retry): only when Pass 1 returns
        // nothing AND typo tolerance is enabled. Apply the full
        // correction pipeline (exact dict + Levenshtein fallback)
        // and re-run WP_Query with the corrected term. This prevents
        // valid words from being "corrected" (e.g. logo → lego).
        // -------------------------------------------------------
        if ( empty( $ranked_products ) && get_option( 'nivo_search_enable_typo_tolerance', 1 ) ) {
            $corrected_query = $this->apply_typo_corrections( $original_query, $corrected_from, $corrected_to );

            if ( $corrected_query !== $original_query ) {
                $retry_query     = $run_wp_query( $corrected_query );
                $ranked_products = $this->rank_results( $retry_query->posts, $corrected_query, $args );
                // Update the active query term so fuzzy fallback and did_you_mean
                // both reference the corrected word, not the original typo.
                if ( ! empty( $ranked_products ) ) {
                    $query = $corrected_query;
                }
            }
        }

        // -------------------------------------------------------
        // Fuzzy fallback (Phase 3+): when both passes return no
        // products, try the index-based Fuzzy_Search engine.
        // Only runs when typo tolerance is globally enabled.
        // -------------------------------------------------------
        $did_you_mean = null;

        if ( empty( $ranked_products ) && get_option( 'nivo_search_enable_fuzzy_search', 1 ) ) {
            $fuzzy   = new Fuzzy_Search();
            $fuzzy_r = $fuzzy->search( $original_query, $args );

            if ( ! empty( $fuzzy_r['product_ids'] ) ) {
                $fuzzy_posts = get_posts( array(
                    'post_type'      => 'product',
                    'post_status'    => 'publish',
                    'post__in'       => $fuzzy_r['product_ids'],
                    'posts_per_page' => $args['limit'],
                    'orderby'        => 'post__in',
                    'post__not_in'   => $args['excluded_products'],
                ) );

                if ( ! empty( $fuzzy_posts ) ) {
                    $ranked_products = $this->rank_results( $fuzzy_posts, $original_query, $args );
                }
            }

            // Pass "did you mean" through regardless of whether we found products.
            if ( ! empty( $fuzzy_r['did_you_mean'] ) ) {
                $did_you_mean = $fuzzy_r['did_you_mean'];
            }
        }

        $execution_time = microtime(true) - $start_time;

        $result = array(
            'products'       => $ranked_products,
            'categories'     => $categories,
            'tags'           => $tags,
            'total'          => count( $ranked_products ),
            'execution_time' => $execution_time,
        );

        if ( null !== $corrected_from ) {
            $result['corrected_from'] = $corrected_from;
            $result['corrected_to']   = $corrected_to;
        }

        if ( null !== $did_you_mean ) {
            $result['did_you_mean'] = $did_you_mean;
        }

        return $result;
    }

    /**
     * Modify search WHERE clause
     *
     * @since 1.0.0
     */
    public function search_where($where, $wp_query) {
        global $wpdb;
        
        if (empty($where)) {
            return $where;
        }
        
        $args        = $wp_query->get( 'nivo_search_args' );
        $search_term = $wp_query->get( 's' );

        if ( empty( $args ) || empty( $search_term ) ) {
            return $where;
        }

        $search_fields   = $args['search_fields'];
        $n               = '%';
        $escaped         = $wpdb->esc_like( $search_term );
        $term_conditions = array();

        // Title search.
        if ( in_array( 'title', $search_fields, true ) ) {
            $term_conditions[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $n . $escaped . $n );
        }

        // Content search.
        if ( in_array( 'content', $search_fields, true ) ) {
            $term_conditions[] = $wpdb->prepare( "{$wpdb->posts}.post_content LIKE %s", $n . $escaped . $n );
        }

        // Excerpt search.
        if ( in_array( 'excerpt', $search_fields, true ) ) {
            $term_conditions[] = $wpdb->prepare( "{$wpdb->posts}.post_excerpt LIKE %s", $n . $escaped . $n );
        }

        // SKU search — parent product SKU.
        if ( in_array( 'sku', $search_fields, true ) ) {
            $term_conditions[] = $wpdb->prepare( 'sku_meta.meta_value LIKE %s', $n . $escaped . $n );
            // Variation-level SKU: match returns the parent product.
            $term_conditions[] = $wpdb->prepare( 'variation_sku_meta.meta_value LIKE %s', $n . $escaped . $n );
        }

        if ( ! empty( $term_conditions ) ) {
            // The posts_search filter is designed to replace the search clause.
            // We replace it entirely so our custom field conditions (SKU, excerpt,
            // variation SKU, meta joins) are the sole search logic. meta_query and
            // tax_query conditions are added via separate filter hooks and are not
            // passed through posts_search, so replacing here does not affect them.
            $where  = ' AND (' . implode( ' OR ', $term_conditions ) . ') ';
            $where .= " AND {$wpdb->posts}.post_type IN ('product') ";
            $where .= " AND {$wpdb->posts}.post_status = 'publish' ";
        }

        return $where;
    }
    
    /**
     * Modify search JOIN clause
     *
     * @since 1.0.0
     */
    public function search_join($join, $wp_query) {
        global $wpdb;
        
        $args = $wp_query->get('nivo_search_args');
        
        if (empty($args)) {
            return $join;
        }
        
        $search_fields = $args['search_fields'];
        
        // Join postmeta for parent product SKU search.
        if ( in_array( 'sku', $search_fields, true ) ) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS sku_meta ON ({$wpdb->posts}.ID = sku_meta.post_id AND sku_meta.meta_key = '_sku') ";
            // Join variation posts and their SKU meta so a variation SKU match
            // surfaces the parent product in results (free-tier feature).
            $join .= " LEFT JOIN {$wpdb->posts} AS variation_posts ON (variation_posts.post_parent = {$wpdb->posts}.ID AND variation_posts.post_type = 'product_variation' AND variation_posts.post_status = 'publish') ";
            $join .= " LEFT JOIN {$wpdb->postmeta} AS variation_sku_meta ON (variation_posts.ID = variation_sku_meta.post_id AND variation_sku_meta.meta_key = '_sku') ";
        }

        return $join;
    }
    
    /**
     * Modify search DISTINCT clause
     *
     * @since 1.0.0
     */
    public function search_distinct($distinct, $wp_query) {
        return "DISTINCT";
    }
    
    /**
     * Apply typo corrections to a search query.
     *
     * Delegates to Typo_Manager which handles loading, caching, custom rules,
     * and the developer filter — all in one cached call.
     *
     * @since 2.0.2
     * @since 2.0.2 Delegated to Typo_Manager::correct_query().
     * @param string      $query          Original search query.
     * @param string|null &$corrected_from Set to the original query if corrected.
     * @param string|null &$corrected_to   Set to the corrected query if corrected.
     * @return string Corrected (or original) query.
     */
    /**
     * Add multilingual language constraints to WP_Query args.
     *
     * Supports WPML (via ICL_LANGUAGE_CODE constant) and Polylang
     * (via pll_current_language() function). When either plugin is active,
     * the `lang` argument is added to WP_Query so that search results are
     * limited to the current visitor's language.
     *
     * Does nothing when neither plugin is active — safe to call everywhere.
     *
     * @since  2.3.0
     * @param  array $query_args WP_Query argument array.
     * @return array Modified argument array.
     */
    public static function apply_language_filter( array $query_args ) {
        // On AJAX requests the JS always sends 'lang' in the POST body (set via
        // wp_localize_script). Reading it here ensures the correct language is
        // used even when WPML/Polylang haven't yet had a chance to set their own
        // constants / globals for the AJAX context.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- lang is informational, not security-sensitive.
        $ajax_lang = isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : '';
        if ( '' !== $ajax_lang ) {
            $query_args['lang'] = $ajax_lang;
            return apply_filters( 'nivo_search_language_query_args', $query_args );
        }

        // Page-load context: WPML sets ICL_LANGUAGE_CODE on every request.
        if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ) {
            $query_args['lang'] = ICL_LANGUAGE_CODE;
            return apply_filters( 'nivo_search_language_query_args', $query_args );
        }

        // Polylang — pll_current_language() returns the active language slug.
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language( 'slug' );
            if ( $lang ) {
                $query_args['lang'] = $lang;
            }
        }

        /**
         * Filters the language slug added to WP_Query for multilingual search.
         * Return an empty string to disable the filter.
         *
         * @since 2.0.2
         * @param array $query_args Current WP_Query args.
         */
        return apply_filters( 'nivo_search_language_query_args', $query_args );
    }

    public function apply_typo_corrections( $query, &$corrected_from, &$corrected_to ) {
        return Typo_Manager::correct_query( $query, $corrected_from, $corrected_to );
    }

    /**
     * Normalize diacritical marks in a string.
     *
     * Converts accented characters to their ASCII equivalents so that
     * searches like "cafe" match "café", "resume" matches "résumé", etc.
     * Uses PHP's Intl Transliterator when available (PHP 5.4+, ext-intl),
     * falling back to iconv with TRANSLIT, and finally a manual char map.
     *
     * @since  2.3.0
     * @param  string $string Input string.
     * @return string Normalized string.
     */
    public static function normalize_diacritics( $string ) {
        if ( '' === $string ) {
            return $string;
        }

        // Try PHP Intl transliterator first (best quality).
        if ( function_exists( 'transliterator_transliterate' ) ) {
            $normalized = transliterator_transliterate( 'Any-Latin; Latin-ASCII; Lower()', $string );
            if ( false !== $normalized ) {
                return $normalized;
            }
        }

        // Try iconv with TRANSLIT fallback (suppress notices — some glibc builds
        // emit E_NOTICE for characters it cannot transliterate).
        if ( function_exists( 'iconv' ) ) {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            $normalized = @iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $string );
            if ( false !== $normalized && '' !== $normalized ) {
                return strtolower( $normalized );
            }
        }

        // Manual char map for common European characters.
        $map = array(
            // Lowercase.
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y', 'ß' => 'ss', 'ł' => 'l', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',
            'ń' => 'n', 'ć' => 'c', 'ą' => 'a', 'ę' => 'e', 'ó' => 'o',
            // Uppercase.
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'Å' => 'a',
            'Æ' => 'ae', 'Ç' => 'c', 'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'Ð' => 'd', 'Ñ' => 'n',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o', 'Ø' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y', 'Þ' => 'th',
        );

        $result = strtolower( strtr( $string, $map ) );

        // Final safety guard — never return an empty string (could happen if all
        // characters were non-ASCII and the char map had no mapping for them).
        return '' !== $result ? $result : strtolower( $string );
    }

    /**
     * Rank results based on relevance.
     *
     * Uses configurable weights from the preset settings, falling back to
     * sensible defaults when weights are not set.
     *
     * @since 1.0.0
     * @since 2.0.2 Weights are now configurable via preset settings.
     * @param array  $products WP_Post objects from WP_Query.
     * @param string $query    Search query (original, post-correction).
     * @param array  $args     Search arguments including optional weight keys.
     * @return array Products sorted by relevance_score descending.
     */
    private function rank_results( $products, $query, $args ) {
        $query = strtolower( $query );

        // Configurable weights with defaults.
        $w_title_exact    = isset( $args['weight_title_exact'] )    ? (int) $args['weight_title_exact']    : 100;
        $w_title_starts   = isset( $args['weight_title_starts'] )   ? (int) $args['weight_title_starts']   : 50;
        $w_title_contains = isset( $args['weight_title_contains'] ) ? (int) $args['weight_title_contains'] : 20;
        $w_sku_exact      = isset( $args['weight_sku'] )            ? (int) $args['weight_sku']            : 80;
        $w_sku_partial    = max( 1, (int) round( $w_sku_exact * 0.375 ) ); // ~30 at default weight 80.

        $ranked = array();

        // Phase 4.2 — Bulk-fetch all SKUs in a single query instead of one
        // get_post_meta() call per product (N+1 fix).
        // Also fetches variation SKUs so that a search for e.g. "TSHIRT-RED-XL"
        // boosts the parent product's relevance score correctly.
        $sku_map          = array(); // parent product ID → parent SKU.
        $variation_sku_map = array(); // parent product ID → lowest variation SKU (for scoring).
        $needs_sku_rank   = in_array( 'sku', $args['search_fields'], true );
        if ( $needs_sku_rank && ! empty( $products ) ) {
            global $wpdb;
            $ids          = array_map( static fn( $p ) => (int) $p->ID, $products );
            $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

            // Parent SKUs.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    array_merge( array( '_sku' ), $ids )
                )
            );
            foreach ( $rows as $row ) {
                $sku_map[ (int) $row->post_id ] = strtolower( (string) $row->meta_value );
            }

            // Variation SKUs — one query: get all variation post IDs whose parent
            // is one of our result product IDs, then fetch their _sku meta.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $var_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT p.post_parent AS parent_id, pm.meta_value AS sku
                     FROM {$wpdb->posts} p
                     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sku'
                     WHERE p.post_type = 'product_variation'
                       AND p.post_status = 'publish'
                       AND p.post_parent IN ({$placeholders})
                       AND pm.meta_value != ''", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $ids
                )
            );
            // Store all variation SKUs per parent (we check all of them during scoring).
            foreach ( $var_rows as $vrow ) {
                $parent_id = (int) $vrow->parent_id;
                if ( ! isset( $variation_sku_map[ $parent_id ] ) ) {
                    $variation_sku_map[ $parent_id ] = array();
                }
                $variation_sku_map[ $parent_id ][] = strtolower( (string) $vrow->sku );
            }
        }

        foreach ( $products as $product ) {
            $score = 0;
            $title = strtolower( $product->post_title );

            if ( $title === $query ) {
                $score += $w_title_exact;
            } elseif ( strpos( $title, $query ) === 0 ) {
                $score += $w_title_starts;
            } elseif ( strpos( $title, $query ) !== false ) {
                $score += $w_title_contains;
            }

            // SKU match — parent product SKU.
            if ( $needs_sku_rank ) {
                $sku = $sku_map[ $product->ID ] ?? '';
                if ( $sku ) {
                    if ( $sku === $query ) {
                        $score += $w_sku_exact;
                    } elseif ( strpos( $sku, $query ) !== false ) {
                        $score += $w_sku_partial;
                    }
                }

                // Variation SKU match — score as partial SKU hit on the parent.
                // Exact variation SKU match gets full exact weight.
                if ( isset( $variation_sku_map[ $product->ID ] ) ) {
                    foreach ( $variation_sku_map[ $product->ID ] as $var_sku ) {
                        if ( $var_sku === $query ) {
                            $score += $w_sku_exact;
                            break;
                        } elseif ( strpos( $var_sku, $query ) !== false ) {
                            $score += $w_sku_partial;
                            break;
                        }
                    }
                }
            }

            $product->relevance_score = $score;
            $ranked[]                 = $product;
        }

        usort( $ranked, static function ( $a, $b ) {
            return $b->relevance_score - $a->relevance_score;
        } );

        return $ranked;
    }

    /**
     * Get matching product categories.
     *
     * Runs two name__like passes (normalized + raw) so both ASCII-titled and
     * accented taxonomy names are found regardless of MySQL collation.
     * Language-filters via WPML/Polylang when active.
     *
     * @since 1.0.0
     * @since 2.0.2 Added $raw_query parameter and multilingual support.
     * @param string $query     Normalized (diacritics-stripped) search query.
     * @param string $raw_query Pre-normalization query (may contain accent chars).
     * @param array  $args      Search arguments.
     * @return array WP_Term objects.
     */
    private function get_categories( $query, $raw_query, $args ) {
        return $this->fetch_terms( 'product_cat', $query, $raw_query );
    }

    /**
     * Get matching product tags.
     *
     * @since 1.0.0
     * @since 2.0.2 Added $raw_query parameter and multilingual support.
     * @param string $query     Normalized search query.
     * @param string $raw_query Pre-normalization query.
     * @param array  $args      Search arguments.
     * @return array WP_Term objects.
     */
    private function get_tags( $query, $raw_query, $args ) {
        return $this->fetch_terms( 'product_tag', $query, $raw_query );
    }

    /**
     * Fetch taxonomy terms with diacritics complement and language filtering.
     *
     * Two-pass strategy:
     *   Pass A — normalized query ("cafe") finds ASCII-titled terms.
     *   Pass B — raw query ("café") finds terms whose names contain actual
     *             accent characters, covering stores where product categories
     *             are named with accents (e.g. "Café Accessories").
     *
     * Language filtering:
     *   - Polylang: `lang` argument in get_terms() args (supported natively).
     *   - WPML: temporarily switch language context via wpml_switch_language
     *     action before querying, then restore — ensures WPML's term-language
     *     filter applies even on admin-ajax.php / wc-ajax endpoints where
     *     WPML may not have set its global language state yet.
     *
     * @since  2.3.1
     * @param  string $taxonomy  Taxonomy slug ('product_cat', 'product_tag').
     * @param  string $query     Normalized search term.
     * @param  string $raw_query Pre-normalization search term.
     * @return array             WP_Term objects, deduplicated.
     */
    private function fetch_terms( $taxonomy, $query, $raw_query ) {
        $lang      = self::get_current_lang();
        $base_args = array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
            'number'     => 5,
        );

        // Polylang: native support for 'lang' in get_terms() args.
        if ( '' !== $lang && function_exists( 'pll_current_language' ) ) {
            $base_args['lang'] = $lang;
        }

        // WPML: switch language context so get_terms() is filtered correctly.
        $wpml_switched = false;
        if ( '' !== $lang && defined( 'ICL_SITEPRESS_VERSION' ) ) {
            do_action( 'wpml_switch_language', $lang );
            $wpml_switched = true;
        }

        // Pass A: normalized query — finds ASCII-titled and ASCII-collated terms.
        $terms = get_terms( array_merge( $base_args, array( 'name__like' => $query ) ) );
        if ( is_wp_error( $terms ) ) {
            $terms = array();
        }

        // Pass B (diacritics complement): also search with the raw (accented) query
        // when it differs from the normalized form, to find terms like "Café Mugs".
        if ( $raw_query !== $query ) {
            $raw_terms = get_terms( array_merge( $base_args, array( 'name__like' => $raw_query ) ) );
            if ( ! is_wp_error( $raw_terms ) && ! empty( $raw_terms ) ) {
                // Merge, deduplicate by term_id.
                $seen_ids = array_map( static fn( $t ) => $t->term_id, $terms );
                foreach ( $raw_terms as $rt ) {
                    if ( ! in_array( $rt->term_id, $seen_ids, true ) ) {
                        $terms[]    = $rt;
                        $seen_ids[] = $rt->term_id;
                    }
                }
            }
        }

        // Restore WPML's original language context.
        if ( $wpml_switched ) {
            do_action( 'wpml_switch_language', null );
        }

        return $terms;
    }

    /**
     * Resolve the active language code for multilingual filtering.
     *
     * Priority: (1) explicit AJAX POST param → (2) WPML constant → (3) Polylang.
     * Returns '' on monolingual sites so callers can skip language logic entirely.
     *
     * @since  2.3.1
     * @return string Language slug (e.g. 'en', 'fr') or empty string.
     */
    private static function get_current_lang() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- lang is informational, not a privileged action.
        $ajax_lang = isset( $_POST['lang'] ) ? sanitize_key( wp_unslash( $_POST['lang'] ) ) : '';
        if ( '' !== $ajax_lang ) {
            return $ajax_lang;
        }
        if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ) {
            return (string) ICL_LANGUAGE_CODE;
        }
        if ( function_exists( 'pll_current_language' ) ) {
            $lang = pll_current_language( 'slug' );
            if ( $lang ) {
                return (string) $lang;
            }
        }
        return '';
    }
}