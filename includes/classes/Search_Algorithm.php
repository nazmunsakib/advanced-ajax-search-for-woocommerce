<?php
/**
 * Search Algorithm with AI Layer
 *
 * @package NASFWC
 * @since 1.0.0
 */

namespace NASFWC;

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
        
        return apply_filters('NASFWC_processed_query', $query);
    }
    
    /**
     * Get products using enhanced multi-field search
     *
     * @since 1.0.0
     * @param string $query Search query
     * @param array $args Search arguments
     * @return array Products
     */
    private function get_products($query, $args) {
        $search_args = wp_parse_args($args, [
            'status' => 'publish',
            'limit' => get_option('NASFWC_search_limit', 10),
            'meta_query' => [],
            'tax_query' => []
        ]);
        
        // Exclude out of stock if enabled
        if (get_option('NASFWC_exclude_out_of_stock', 0)) {
            $search_args['meta_query'][] = [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            ];
        }
        
        $all_products = [];
        
        // 1. Title search (highest priority)
        if (get_option('NASFWC_search_in_title', 1)) {
            $title_products = wc_get_products(array_merge($search_args, ['s' => $query]));
            $all_products = array_merge($all_products, $title_products);
        }
        
        // 2. SKU search
        if (get_option('NASFWC_search_in_sku', 1)) {
            $sku_args = $search_args;
            $sku_args['sku'] = $query;
            unset($sku_args['s']); // Remove title search
            $sku_products = wc_get_products($sku_args);
            $all_products = array_merge($all_products, $sku_products);
        }
        
        // 3. Description search
        if (get_option('NASFWC_search_in_content', 0)) {
            $desc_products = $this->search_in_content($query, $search_args);
            $all_products = array_merge($all_products, $desc_products);
        }
        
        // 4. Short description search
        if (get_option('NASFWC_search_in_excerpt', 0)) {
            $excerpt_products = $this->search_in_excerpt($query, $search_args);
            $all_products = array_merge($all_products, $excerpt_products);
        }
        
        // 5. Category search
        if (get_option('NASFWC_search_in_categories', 1)) {
            $cat_products = $this->search_in_categories($query, $search_args);
            $all_products = array_merge($all_products, $cat_products);
        }
        
        // 6. Tag search
        if (get_option('NASFWC_search_in_tags', 1)) {
            $tag_products = $this->search_in_tags($query, $search_args);
            $all_products = array_merge($all_products, $tag_products);
        }
        
        // 7. Attribute search
        if (get_option('NASFWC_search_in_attributes', 0)) {
            $attr_products = $this->search_in_attributes($query, $search_args);
            $all_products = array_merge($all_products, $attr_products);
        }
        
        // Deduplicate products
        $unique_products = [];
        foreach ($all_products as $product) {
            $unique_products[$product->get_id()] = $product;
        }
        
        return array_values($unique_products);
    }
    
    /**
     * Search in product content
     */
    private function search_in_content($query, $args) {
        global $wpdb;
        $product_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'product' 
             AND post_status = 'publish' 
             AND post_content LIKE %s",
            '%' . $wpdb->esc_like($query) . '%'
        ));
        
        if (empty($product_ids)) return [];
        
        return wc_get_products(array_merge($args, ['include' => $product_ids]));
    }
    
    /**
     * Search in product excerpt
     */
    private function search_in_excerpt($query, $args) {
        global $wpdb;
        $product_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type = 'product' 
             AND post_status = 'publish' 
             AND post_excerpt LIKE %s",
            '%' . $wpdb->esc_like($query) . '%'
        ));
        
        if (empty($product_ids)) return [];
        
        return wc_get_products(array_merge($args, ['include' => $product_ids]));
    }
    
    /**
     * Search in categories
     */
    private function search_in_categories($query, $args) {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'name__like' => $query,
            'hide_empty' => true
        ]);
        
        if (empty($terms)) return [];
        
        $term_ids = wp_list_pluck($terms, 'term_id');
        return wc_get_products(array_merge($args, ['category' => $term_ids]));
    }
    
    /**
     * Search in tags
     */
    private function search_in_tags($query, $args) {
        $terms = get_terms([
            'taxonomy' => 'product_tag',
            'name__like' => $query,
            'hide_empty' => true
        ]);
        
        if (empty($terms)) return [];
        
        $term_ids = wp_list_pluck($terms, 'term_id');
        return wc_get_products(array_merge($args, ['tag' => $term_ids]));
    }
    
    /**
     * Search in product attributes
     */
    private function search_in_attributes($query, $args) {
        global $wpdb;
        
        // Get attribute taxonomies
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $product_ids = [];
        
        foreach ($attribute_taxonomies as $tax) {
            $taxonomy = 'pa_' . $tax->attribute_name;
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'name__like' => $query,
                'hide_empty' => true
            ]);
            
            if (!empty($terms)) {
                $term_ids = wp_list_pluck($terms, 'term_id');
                $ids = $wpdb->get_col(
                    "SELECT object_id FROM {$wpdb->term_relationships} 
                     WHERE term_taxonomy_id IN (" . implode(',', array_map('intval', $term_ids)) . ")"
                );
                $product_ids = array_merge($product_ids, $ids);
            }
        }
        
        if (empty($product_ids)) return [];
        
        return wc_get_products(array_merge($args, ['include' => array_unique($product_ids)]));
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
     * Calculate enhanced relevance score
     *
     * @since 1.0.0
     * @param WC_Product $product Product object
     * @param string $query Search query
     * @return int Relevance score
     */
    private function calculate_relevance_score($product, $query) {
        $score = 0;
        $query_lower = strtolower($query);
        $title_lower = strtolower($product->get_name());
        
        // Exact title match (highest priority)
        if ($title_lower === $query_lower) {
            $score += 200;
        }
        // Title starts with query
        elseif (strpos($title_lower, $query_lower) === 0) {
            $score += 150;
        }
        // Title contains query
        elseif (strpos($title_lower, $query_lower) !== false) {
            $score += 100;
        }
        
        // SKU exact match
        if (strtolower($product->get_sku()) === $query_lower) {
            $score += 120;
        }
        // SKU contains query
        elseif (stripos($product->get_sku(), $query) !== false) {
            $score += 80;
        }
        
        // Category match
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
        foreach ($categories as $category) {
            if (stripos($category, $query) !== false) {
                $score += 70;
                break;
            }
        }
        
        // Tag match
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']);
        foreach ($tags as $tag) {
            if (stripos($tag, $query) !== false) {
                $score += 60;
                break;
            }
        }
        
        // Attribute match
        $attributes = $product->get_attributes();
        foreach ($attributes as $attribute) {
            if (is_object($attribute)) {
                $terms = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                foreach ($terms as $term) {
                    if (stripos($term, $query) !== false) {
                        $score += 50;
                        break 2;
                    }
                }
            }
        }
        
        // Short description match
        if (stripos($product->get_short_description(), $query) !== false) {
            $score += 40;
        }
        
        // Description match (lowest weight)
        if (stripos($product->get_description(), $query) !== false) {
            $score += 30;
        }
        
        // Boost for popular products (sales count)
        $sales = $product->get_total_sales();
        if ($sales > 0) {
            $score += min($sales / 10, 20); // Max 20 points boost
        }
        
        // Boost for products on sale
        if ($product->is_on_sale()) {
            $score += 10;
        }
        
        // Boost for featured products
        if ($product->is_featured()) {
            $score += 15;
        }
        
        return apply_filters('NASFWC_relevance_score', $score, $product, $query);
    }
    
    /**
     * Enhanced typo correction with Levenshtein distance
     *
     * @since 1.0.0
     * @param string $query Query to correct
     * @return string Corrected query
     */
    private function correct_typos($query) {
        if (!get_option('NASFWC_enable_typo_correction', 1)) {
            return $query;
        }
        
        // Enhanced typo corrections
        $corrections = apply_filters('NASFWC_typo_corrections', [
            // Common product typos
            'tshirt' => 't-shirt',
            'tee shirt' => 't-shirt',
            'jens' => 'jeans',
            'shose' => 'shoes',
            'accesories' => 'accessories',
            'jewelery' => 'jewelry',
            'cloths' => 'clothes',
            'womens' => 'women',
            'mens' => 'men',
            'childs' => 'children',
            'babys' => 'baby',
            // Brand typos
            'addidas' => 'adidas',
            'nike' => 'nike',
            'puma' => 'puma',
            // Color typos
            'blak' => 'black',
            'whit' => 'white',
            'blu' => 'blue',
            'grean' => 'green',
            'yelow' => 'yellow',
            'purpel' => 'purple'
        ]);
        
        $corrected = str_ireplace(array_keys($corrections), array_values($corrections), $query);
        
        // If no correction found, try fuzzy matching with product titles
        if ($corrected === $query && strlen($query) > 3) {
            $corrected = $this->fuzzy_match_products($query);
        }
        
        return $corrected;
    }
    
    /**
     * Fuzzy match against product titles
     */
    private function fuzzy_match_products($query) {
        global $wpdb;
        
        // Get recent product titles for fuzzy matching
        $titles = $wpdb->get_col(
            "SELECT post_title FROM {$wpdb->posts} 
             WHERE post_type = 'product' 
             AND post_status = 'publish' 
             ORDER BY post_date DESC 
             LIMIT 100"
        );
        
        $best_match = $query;
        $min_distance = strlen($query);
        
        foreach ($titles as $title) {
            $words = explode(' ', strtolower($title));
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $distance = levenshtein(strtolower($query), $word);
                    if ($distance < $min_distance && $distance <= 2) {
                        $min_distance = $distance;
                        $best_match = $word;
                    }
                }
            }
        }
        
        return $best_match;
    }
    
    /**
     * Enhanced synonym expansion
     *
     * @since 1.0.0
     * @param string $query Query to expand
     * @return string Expanded query
     */
    private function expand_synonyms($query) {
        if (!get_option('NASFWC_enable_synonyms', 1)) {
            return $query;
        }
        
        $synonyms = apply_filters('NASFWC_synonyms', [
            'shirt' => ['top', 'blouse', 'tee', 'polo', 'tank'],
            'pants' => ['trousers', 'jeans', 'slacks', 'bottoms'],
            'shoes' => ['footwear', 'sneakers', 'boots', 'sandals'],
            'bag' => ['purse', 'handbag', 'backpack', 'tote'],
            'watch' => ['timepiece', 'clock'],
            'phone' => ['mobile', 'smartphone', 'cell'],
            'laptop' => ['computer', 'notebook', 'pc'],
            'dress' => ['gown', 'frock', 'outfit'],
            'jacket' => ['coat', 'blazer', 'cardigan'],
            'hat' => ['cap', 'beanie', 'headwear']
        ]);
        
        // For now, return original query
        // Future enhancement: implement full synonym expansion
        return $query;
    }
}