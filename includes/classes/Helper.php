<?php
/**
 * Helper Class
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Helper Class
 *
 * Provides common utility methods used across the plugin
 *
 * @since 1.0.0
 */
class Helper {
    
    /**
     * Get default preset ID
     *
     * @since 1.0.0
     * @return int Default preset ID or 0 if not set
     */
    public static function get_default_preset_id() {
        return absint(get_option('nivo_search_default_preset_created', 0));
    }
    
    /**
     * Validate if preset ID is valid
     *
     * @since 1.0.0
     * @param int $preset_id Preset ID to validate
     * @return bool True if valid preset, false otherwise
     */
    public static function is_valid_preset($preset_id) {
        if (!$preset_id) {
            return false;
        }
        
        return get_post_type($preset_id) === 'nivo_search_preset';
    }
    
    /**
     * Get all preset settings (generale + display)
     *
     * @since 1.0.0
     * @param int $preset_id Preset ID
     * @return array Merged preset settings array
     */
    public static function get_preset_settings($preset_id) {
        if (!self::is_valid_preset($preset_id)) {
            return [];
        }

        $generale_settings = get_post_meta($preset_id, '_nivo_search_generale', true) ?: [];
        $query_settings    = get_post_meta($preset_id, '_nivo_search_query', true) ?: [];
        $display_settings  = get_post_meta($preset_id, '_nivo_search_display', true) ?: [];

        // Merge generale + query + display for the frontend data-preset-settings attribute.
        // Style settings are handled separately via get_preset_style_settings().
        return array_merge($generale_settings, $query_settings, $display_settings);
    }
    
    /**
     * Get preset style settings only
     *
     * @since 1.0.0
     * @param int $preset_id Preset ID
     * @return array Style settings array
     */
    public static function get_preset_style_settings($preset_id) {
        if (!self::is_valid_preset($preset_id)) {
            return [];
        }
        
        return get_post_meta($preset_id, '_nivo_search_style', true) ?: [];
    }
    
    /**
     * Generate CSS for preset
     *
     * @since 1.0.0
     * @param int $preset_id Preset ID
     * @param array $settings Style settings
     * @return string Generated CSS
     */
    public static function generate_preset_css($preset_id, $settings) {
        $css = '';
        $selector = '.nivo-preset-' . $preset_id;
        
        if (isset($settings['bar_width'])) {
            $css .= $selector . ' .nivo-search-form-wrapper{max-width:' . absint($settings['bar_width']) . 'px}';
        }
        
        // Search bar input styles.
        $input_styles = [];
        if ( isset( $settings['bar_height'] ) ) {
            $input_styles[] = 'height:' . absint( $settings['bar_height'] ) . 'px';
        }
        if ( isset( $settings['bg_color'] ) ) {
            $input_styles[] = 'background-color:' . esc_attr( $settings['bg_color'] );
        }
        if ( isset( $settings['text_color'] ) ) {
            $input_styles[] = 'color:' . esc_attr( $settings['text_color'] );
        }
        if ( isset( $settings['border_color'] ) ) {
            $input_styles[] = 'border-color:' . esc_attr( $settings['border_color'] );
        }

        if ( ! empty( $input_styles ) ) {
            $css .= $selector . ' .nivo-search-wrapper input[type=search].nivo-search-product-search{' . implode( ';', $input_styles ) . '}';
        }

        // Results panel styles.
        $results_styles = [];
        if ( isset( $settings['results_width'] ) ) {
            $results_styles[] = 'max-width:' . absint( $settings['results_width'] ) . 'px';
        }
        if ( isset( $settings['results_bg_color'] ) ) {
            $results_styles[] = 'background-color:' . esc_attr( $settings['results_bg_color'] );
        }
        if ( isset( $settings['results_border_color'] ) ) {
            $results_styles[] = 'border-color:' . esc_attr( $settings['results_border_color'] );
        }

        if ( ! empty( $results_styles ) ) {
            $css .= $selector . ' .nivo-search-results{' . implode( ';', $results_styles ) . '}';
        }

        // Results text color — cascade from the panel root so ALL text (title, price,
        // description, SKU, section headings) inherits it. The specific selectors below
        // override any inline color set by WooCommerce price HTML.
        if ( isset( $settings['results_text_color'] ) ) {
            $color = esc_attr( $settings['results_text_color'] );
            // Cascade to every element inside the results panel.
            $css .= $selector . ' .nivo-search-results{color:' . $color . '}';
            // Explicit overrides for elements that may carry their own color rule.
            $css .= $selector . ' .nivo-search-results .nivo-search-product-title{color:' . $color . '}';
            $css .= $selector . ' .nivo-search-results .nivo-search-product-description{color:' . $color . '}';
            $css .= $selector . ' .nivo-search-results .nivo-search-product-price{color:' . $color . '}';
            $css .= $selector . ' .nivo-search-results .nivo-search-product-sku{color:' . $color . '}';
            $css .= $selector . ' .nivo-search-results .nivo-search-section-title{color:' . $color . '}';
        }
        
        return $css;
    }
    
    /**
     * Get default settings array
     *
     * @since 1.0.0
     * @return array Default settings
     */
    public static function get_default_settings() {
        return [
            // General
            'limit'                     => 10,
            'min_chars'                 => 2,
            'placeholder'               => 'Search products...',
            'delay'                     => 300,
            // Query
            'search_in_title'           => 1,
            'search_in_sku'             => 1,
            'search_in_content'         => 1,
            'search_in_excerpt'         => 1,
            'search_product_categories' => 1,
            'search_product_tags'       => 0,
            'exclude_out_of_stock'      => 0,
            // Display
            'show_images'               => 1,
            'show_price'                => 1,
            'show_sku'                  => 1,
            'show_description'          => 1,
            'show_stock_status'         => 0,
            'show_category_badge'       => 0,
            'show_add_to_cart'          => 0,
            'show_qty_selector'         => 0,
            'show_view_all'             => 1,
            // Style
            'bar_width'                 => 600,
            'bar_height'                => 50,
            'border_color'              => '#dddddd',
            'bg_color'                  => '#ffffff',
            'text_color'                => '#333333',
            'results_width'             => 600,
            'results_text_color'        => '#333333',
            'results_border_color'      => '#dddddd',
            'results_bg_color'          => '#ffffff',
        ];
    }
}
