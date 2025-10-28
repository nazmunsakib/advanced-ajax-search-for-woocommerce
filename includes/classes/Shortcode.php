<?php
/**
 * Shortcode Handler
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

defined('ABSPATH') || exit;

/**
 * Shortcode Class
 *
 * Handles all shortcode functionality for the plugin
 *
 * @since 1.0.0
 */
class Shortcode {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode('aasfwc_ajax_search', [$this, 'render_search_form']);
    }
    
    /**
     * Render search form shortcode
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_search_form($atts = []) {
        // Parse shortcode attributes
        $atts = shortcode_atts([
            'placeholder' => __('Search products...', 'advanced-ajax-search-for-woocommerce'),
            'container_class' => 'aasfwc-ajax-search-container',
            'input_class' => 'aasfwc-product-search',
            'results_class' => 'aasfwc-search-results',
            'show_icon' => 'true',
            'style' => ''
        ], $atts, 'aasfwc_ajax_search');
        
        $show_icon = $atts['show_icon'] === 'true';
        $icon_html = $show_icon ? '<span class="aasfwc-search-icon">🔍</span>' : '';
        $style_attr = !empty($atts['style']) ? ' style="' . esc_attr($atts['style']) . '"' : '';
        
        // Build HTML
        $html = sprintf(
            '<div class="%s"%s>
                <div class="aasfwc-search-wrapper">
                    %s
                    <input type="text" class="%s" placeholder="%s">
                </div>
                <div class="%s"></div>
            </div>',
            esc_attr($atts['container_class']),
            $style_attr,
            $icon_html,
            esc_attr($atts['input_class']),
            esc_attr($atts['placeholder']),
            esc_attr($atts['results_class'])
        );
        
        return apply_filters('aasfwc_shortcode_html', $html, $atts);
    }
}