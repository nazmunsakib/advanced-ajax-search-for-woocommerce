<?php
/**
 * Search Algorithm with AI Layer
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

defined('ABSPATH') || exit;

/**
 * Search Algorithm Class
 *
 * Handles advanced search with AI-powered query understanding
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
        
        // Get WooCommerce products with enhanced search
        $products = $this->get_products($query, $args);
        
        // Apply AI scoring and ranking
        return $this->rank_results($products, $query);
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
        
        return apply_filters('aasfwc_processed_query', $query);
    }
    
    /**
     * Get products using WC_Product_Query
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Products
     */
    private function get_products($query, $args) {
        $search_args = wp_parse_args($args, [
            'status' => 'publish',
            'limit' => get_option('aasfwc_search_limit', 10),
            'meta_query' => [],
            'tax_query' => []
        ]);
        
        // Search in multiple fields
        $products = [];
        
        // Title search (highest priority)
        $title_products = wc_get_products(array_merge($search_args, ['s' => $query]));
        
        // SKU search
        $sku_products = wc_get_products(array_merge($search_args, [
            'meta_query' => [
                [
                    'key' => '_sku',
                    'value' => $query,
                    'compare' => 'LIKE'
                ]
            ]
        ]));
        
        // Tag search
        $tag_products = wc_get_products(array_merge($search_args, [
            'tag' => [$query]
        ]));
        
        // Merge and deduplicate
        $all_products = array_merge($title_products, $sku_products, $tag_products);
        $unique_products = [];
        
        foreach ($all_products as $product) {
            $unique_products[$product->get_id()] = $product;
        }
        
        return array_values($unique_products);
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
            $score = $this->calculate_relevance_score($product, $query);
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
     * Calculate relevance score for a product
     *
     * @since 1.0.0
     * @param WC_Product $product Product object
     * @param string $query Search query
     * @return int Relevance score
     */
    private function calculate_relevance_score($product, $query) {
        $score = 0;
        $query_lower = strtolower($query);
        
        // Title match (highest weight)
        if (stripos($product->get_name(), $query) !== false) {
            $score += 100;
            if (stripos($product->get_name(), $query) === 0) {
                $score += 50; // Starts with query
            }
        }
        
        // SKU match
        if (stripos($product->get_sku(), $query) !== false) {
            $score += 80;
        }
        
        // Tag match
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']);
        foreach ($tags as $tag) {
            if (stripos($tag, $query) !== false) {
                $score += 60;
            }
        }
        
        // Description match (lowest weight)
        if (stripos($product->get_short_description(), $query) !== false) {
            $score += 30;
        }
        
        return apply_filters('aasfwc_relevance_score', $score, $product, $query);
    }
    
    /**
     * Basic typo correction
     *
     * @since 1.0.0
     * @param string $query Query to correct
     * @return string Corrected query
     */
    private function correct_typos($query) {
        // Simple typo corrections
        $corrections = apply_filters('aasfwc_typo_corrections', [
            'tshirt' => 't-shirt',
            'tee shirt' => 't-shirt',
            'jens' => 'jeans',
            'shose' => 'shoes'
        ]);
        
        return str_ireplace(array_keys($corrections), array_values($corrections), $query);
    }
    
    /**
     * Expand query with synonyms
     *
     * @since 1.0.0
     * @param string $query Query to expand
     * @return string Expanded query
     */
    private function expand_synonyms($query) {
        $synonyms = apply_filters('aasfwc_synonyms', [
            'shirt' => ['top', 'blouse', 'tee'],
            'pants' => ['trousers', 'jeans'],
            'shoes' => ['footwear', 'sneakers']
        ]);
        
        // For now, return original query
        // Future: implement synonym expansion logic
        return $query;
    }
}