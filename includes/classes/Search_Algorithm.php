<?php
/**
 * Search Algorithm with AI Layer
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Search Algorithm Class
 *
 * Handles nivo search with AI-powered query understanding
 *
 * @since 1.0.0
 */
class Search_Algorithm {
    
    /**
     * Search products with AI enhancement
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Additional search arguments
     * @return array Search results
     */
    public function search($query, $args = []) {
        $query = $this->process_query($query);
        
        $results = [];
        
        // Get categories if enabled
        if (get_option('nivo_search_in_categories', 0)) {
            $categories = $this->get_categories($query, $args);
            if (!empty($categories)) {
                $results['categories'] = $categories;
            }
        }
        
        // Get WooCommerce products using optimized search
        $products = $this->get_products($query, $args);
        
        // Apply AI scoring and ranking
        $results['products'] = $this->rank_results($products, $query);
        
        return $results;
    }
    
    /**
     * Process and enhance search query
     *
     * @since 1.0.0
     * @param string $query Original query
     * @return string Enhanced query
     */
    private function process_query($query) {
        // Basic typo correction and synonym handling
        $query = $this->correct_typos($query);
        $query = $this->expand_synonyms($query);
        
        return apply_filters('nivo_search_processed_query', $query);
    }
    
    /**
     * Get products using Search approach
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Products
     */
    private function get_products($query, $args) {
        add_filter('posts_search', array($this, 'search_filters'), 501, 2);
        add_filter('posts_join', array($this, 'search_filters_join'), 501, 2);
        add_filter('posts_distinct', array($this, 'search_distinct'), 501, 2);
        
        $search_args = array(
            's' => $query,
            'posts_per_page' => get_option('nivo_search_limit', 10),
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,
            'suppress_filters' => false,
        );
        
        // Add tax query for WC 3.0+
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        $search_args['tax_query'] = $this->get_tax_query();
        
        $posts = get_posts($search_args);
        
        remove_filter('posts_search', array($this, 'search_filters'), 501);
        remove_filter('posts_join', array($this, 'search_filters_join'), 501);
        remove_filter('posts_distinct', array($this, 'search_distinct'), 501);
        
        $products = [];
        foreach ($posts as $post) {
            $product = wc_get_product($post->ID);
            if ($product && $product->is_visible()) {
                $products[] = $product;
            }
        }
        
        return $products;
    }
    
    /**
     * Search filters
     */
    public function search_filters($search, $wp_query) {
        global $wpdb;
        
        if (empty($search)) {
            return $search;
        }
        
        $q = $wp_query->query_vars;
        if ($q['post_type'] !== 'product') {
            return $search;
        }
        
        $n = (!empty($q['exact']) ? '' : '%');
        $search = $searchand = '';
        
        if (!empty($q['search_terms'])) {
            foreach ((array) $q['search_terms'] as $term) {
                $like = $n . $wpdb->esc_like($term) . $n;
                $search .= "{$searchand} (";
                
                // Search in title
                if (get_option('nivo_search_in_title', 1)) {
                    $search .= $wpdb->prepare("({$wpdb->posts}.post_title LIKE %s)", $like);
                } else {
                    $search .= "(0 = 1)";
                }
                
                // Search in content
                if (get_option('nivo_search_in_content', 0)) {
                    $search .= $wpdb->prepare(" OR ({$wpdb->posts}.post_content LIKE %s)", $like);
                }
                
                // Search in excerpt
                if (get_option('nivo_search_in_excerpt', 0)) {
                    $search .= $wpdb->prepare(" OR ({$wpdb->posts}.post_excerpt LIKE %s)", $like);
                }
                
                // Search in SKU
                if (get_option('nivo_search_in_sku', 1)) {
                    $search .= $wpdb->prepare(" OR (nivo_sku.meta_key='_sku' AND nivo_sku.meta_value LIKE %s)", $like);
                }
                
                // Note: Category search removed from product filters to avoid showing category products
                
                $search .= ")";
                $searchand = ' AND ';
            }
        }
        
        if (!empty($search)) {
            $search = " AND ({$search}) ";
            if (!is_user_logged_in()) {
                $search .= " AND ({$wpdb->posts}.post_password = '') ";
            }
        }
        
        return $search;
    }
    
    /**
     * Join for SKU and category search
     */
    public function search_filters_join($join, $query) {
        global $wpdb;
        
        if (empty($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'product') {
            return $join;
        }
        
        if (get_option('nivo_search_in_sku', 1)) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS nivo_sku ON ({$wpdb->posts}.ID = nivo_sku.post_id)";
        }
        
        // Category JOIN removed - categories searched separately
        
        return $join;
    }
    
    /**
     * Make search distinct
     */
    public function search_distinct($where) {
        return 'DISTINCT';
    }
    
    /**
     * Get tax query for WooCommerce
     */
    private function get_tax_query() {
        $product_visibility_term_ids = wc_get_product_visibility_term_ids();
        $tax_query = array('relation' => 'AND');
        
        $tax_query[] = array(
            'taxonomy' => 'product_visibility',
            'field' => 'term_taxonomy_id',
            'terms' => $product_visibility_term_ids['exclude-from-search'],
            'operator' => 'NOT IN',
        );
        
        // Exclude out of stock if enabled
        if (get_option('nivo_search_exclude_out_of_stock', 0)) {
            $tax_query[] = array(
                'taxonomy' => 'product_visibility',
                'field' => 'term_taxonomy_id',
                'terms' => $product_visibility_term_ids['outofstock'],
                'operator' => 'NOT IN',
            );
        }
        
        return $tax_query;
    }
    
    /**
     * Rank search results by relevance
     *
     * @since 1.0.0
     * @param array $products Products to rank
     * @param string $query Search query
     * @return array Ranked products
     */
    private function rank_results($products, $query) {
        $scored_products = [];
        
        foreach ($products as $product) {
            $score = $this->calculate_score($query, $product->get_name());
            $scored_products[] = [
                'product' => $product,
                'score' => $score
            ];
        }
        
        // Sort by score (highest first)
        usort($scored_products, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_column($scored_products, 'product');
    }
    
    /**
     * Calculate similarity score like Search
     */
    private function calculate_score($keyword, $title) {
        $keyword = strtolower($keyword);
        $title = strtolower($title);
        
        // Exact match
        if ($title === $keyword) {
            return 100;
        }
        
        // Title starts with keyword
        if (strpos($title, $keyword) === 0) {
            return 90;
        }
        
        // Title contains keyword
        if (strpos($title, $keyword) !== false) {
            return 80;
        }
        
        // Levenshtein distance for fuzzy matching
        $distance = levenshtein($keyword, $title);
        $maxLen = max(strlen($keyword), strlen($title));
        
        if ($maxLen === 0) {
            return 0;
        }
        
        return max(0, (1 - $distance / $maxLen) * 70);
    }
    
    /**
     * Enhanced typo correction
     */
    private function correct_typos($query) {
        if (!get_option('nivo_search_enable_typo_correction', 1)) {
            return $query;
        }
        
        $corrections = apply_filters('nivo_search_typo_corrections', [
            'tshirt' => 't-shirt',
            'jens' => 'jeans',
            'shose' => 'shoes',
            'accesories' => 'accessories',
            'jewelery' => 'jewelry',
            'cloths' => 'clothes',
            'womens' => 'women',
            'mens' => 'men',
        ]);
        
        return str_ireplace(array_keys($corrections), array_values($corrections), $query);
    }
    
    /**
     * Enhanced synonym expansion
     */
    private function expand_synonyms($query) {
        if (!get_option('nivo_search_enable_synonyms', 1)) {
            return $query;
        }
        
        // For now, return original query
        return $query;
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
}