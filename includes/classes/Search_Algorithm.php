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
            'enable_synonyms'           => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        // Map 'exclude' to 'excluded_products' for back-compat.
        if ( ! empty( $args['exclude'] ) && empty( $args['excluded_products'] ) ) {
            $args['excluded_products'] = $args['exclude'];
        }

        // Sanitize query.
        $query = sanitize_text_field( $query );

        // -------------------------------------------------------
        // Phase 1 stub: apply typo corrections filter.
        // Returns empty array until Phase 3 implements the map.
        // Developers can hook this now and their callbacks will run.
        // -------------------------------------------------------
        $typo_corrections = apply_filters( 'nivo_search_typo_corrections', array() );
        if ( ! empty( $typo_corrections ) && is_array( $typo_corrections ) ) {
            $query_lower = strtolower( $query );
            if ( isset( $typo_corrections[ $query_lower ] ) ) {
                $query = $typo_corrections[ $query_lower ];
            }
        }

        // -------------------------------------------------------
        // Phase 1 stub: apply synonym expansion filter.
        // Returns empty array until Phase 3 implements synonym map.
        // Developers can hook this now.
        // -------------------------------------------------------
        $synonym_terms = array( $query );
        if ( ! empty( $args['enable_synonyms'] ) ) {
            $synonyms = apply_filters( 'nivo_search_synonyms', array() );
            if ( ! empty( $synonyms ) && is_array( $synonyms ) ) {
                $query_lower = strtolower( $query );
                if ( isset( $synonyms[ $query_lower ] ) ) {
                    $synonym_terms = array_merge( array( $query ), (array) $synonyms[ $query_lower ] );
                    $synonym_terms = array_unique( array_map( 'sanitize_text_field', $synonym_terms ) );
                }
            }
        }
        
        // Get matching tags if enabled
        $tags = [];
        if (!empty($args['search_product_tags'])) {
            $tags = $this->get_tags($query, $args);
        }

        // Get matching categories if enabled
        $categories = [];
        if (!empty($args['search_product_categories'])) {
            $categories = $this->get_categories($query, $args);
        }
        
        // Search products
        add_filter('posts_search', [$this, 'search_where'], 10, 2);
        add_filter('posts_join', [$this, 'search_join'], 10, 2);
        add_filter('posts_distinct', [$this, 'search_distinct'], 10, 2);
        
        $query_args = [
            'post_type' => $args['post_types'],
            'post_status' => $args['post_status'],
            'posts_per_page' => $args['limit'],
            's' => $query,
            'nivo_search_args' => $args, // Pass args to filters
            'tax_query' => $args['tax_query'],
            'meta_query' => $args['meta_query'],
            'post__not_in'           => $args['excluded_products'],
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => true,
        ];
        
        // Handle out of stock
        if ($args['exclude_out_of_stock'] === 'yes' || $args['exclude_out_of_stock'] === 1 || $args['exclude_out_of_stock'] === true) {
            $query_args['meta_query'][] = [
                'key' => '_stock_status',
                'value' => 'outofstock',
                'compare' => '!='
            ];
        }
        
        $search_query = new \WP_Query($query_args);
        
        remove_filter('posts_search', [$this, 'search_where'], 10);
        remove_filter('posts_join', [$this, 'search_join'], 10);
        remove_filter('posts_distinct', [$this, 'search_distinct'], 10);
        
        $products = $search_query->posts;
        
        // Calculate relevance and sort
        $ranked_products = $this->rank_results($products, $query, $args);
        
        $execution_time = microtime(true) - $start_time;
        
        return [
            'products' => $ranked_products,
            'categories' => $categories,
            'tags' => $tags,
            'total' => $search_query->found_posts,
            'execution_time' => $execution_time
        ];
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
        
        $args = $wp_query->get('nivo_search_args');
        $search_term = $wp_query->get('s');
        
        if (empty($args) || empty($search_term)) {
            return $where;
        }
        
        $search_fields = $args['search_fields'];
        $n = '%';
        $term = $wpdb->esc_like($search_term);
        
        $search_conditions = [];
        
        // Title search
        if (in_array('title', $search_fields)) {
            $search_conditions[] = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", $n . $term . $n);
        }
        
        // Content search
        if (in_array('content', $search_fields)) {
            $search_conditions[] = $wpdb->prepare("{$wpdb->posts}.post_content LIKE %s", $n . $term . $n);
        }
        
        // Excerpt search
        if (in_array('excerpt', $search_fields)) {
            $search_conditions[] = $wpdb->prepare("{$wpdb->posts}.post_excerpt LIKE %s", $n . $term . $n);
        }
        
        // SKU search
        if (in_array('sku', $search_fields)) {
            $search_conditions[] = $wpdb->prepare("sku_meta.meta_value LIKE %s", $n . $term . $n);
        }
        
        if (!empty($search_conditions)) {
            $where = " AND (" . implode(' OR ', $search_conditions) . ") ";
            
            // Add post type and status checks
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
        
        // Join postmeta for SKU search
        if (in_array('sku', $search_fields)) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS sku_meta ON ({$wpdb->posts}.ID = sku_meta.post_id AND sku_meta.meta_key = '_sku') ";
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
     * Rank results based on relevance
     *
     * @since 1.0.0
     */
    private function rank_results($products, $query, $args) {
        $ranked = [];
        $query = strtolower($query);
        
        foreach ($products as $product) {
            $score = 0;
            $title = strtolower($product->post_title);
            
            // Exact match in title
            if ($title === $query) {
                $score += 100;
            }
            // Starts with query
            elseif (strpos($title, $query) === 0) {
                $score += 50;
            }
            // Contains query
            elseif (strpos($title, $query) !== false) {
                $score += 20;
            }
            
            // SKU match
            if (in_array('sku', $args['search_fields'])) {
                $sku = get_post_meta($product->ID, '_sku', true);
                if ($sku && strtolower($sku) === $query) {
                    $score += 80;
                } elseif ($sku && strpos(strtolower($sku), $query) !== false) {
                    $score += 30;
                }
            }
            
            $product->relevance_score = $score;
            $ranked[] = $product;
        }
        
        // Sort by score
        usort($ranked, function($a, $b) {
            return $b->relevance_score - $a->relevance_score;
        });
        
        return $ranked;
    }

    /**
     * Get matching categories
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Categories
     */
    private function get_categories($query, $args) {
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'name__like' => $query,
            'hide_empty' => true,
            'number' => 5
        ]);
        
        if (is_wp_error($categories)) {
            return [];
        }
        
        return $categories;
    }

    /**
     * Get matching tags
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Tags
     */
    private function get_tags($query, $args) {
        $tags = get_terms([
            'taxonomy' => 'product_tag',
            'name__like' => $query,
            'hide_empty' => true,
            'number' => 5
        ]);
        
        if (is_wp_error($tags)) {
            return [];
        }
        
        return $tags;
    }
}