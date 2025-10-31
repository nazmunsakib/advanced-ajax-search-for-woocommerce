<?php
/**
 * Shortcode Handler
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

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
        add_shortcode('nivo_search', [$this, 'render_search_form']);
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
            'placeholder' => __('Search products...', 'nivo-ajax-search-for-woocommerce'),
            'container_class' => 'nivo-ajax-search-container',
            'input_class' => 'nivo_search-product-search',
            'results_class' => 'nivo_search-results',
            'show_icon' => 'true',
            'style' => ''
        ], $atts, 'nivo_search');
        
        $show_icon = $atts['show_icon'] === 'true';
        $icon_html = $show_icon ? '<svg class="nivo_search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>' : '';
        $style_attr = !empty($atts['style']) ? ' style="' . esc_attr($atts['style']) . '"' : '';
        
        // Build HTML
        $html = sprintf(
            '<div class="%s"%s>
                <form class="nivo_search-form" role="search" method="get" action="%s">
                    <div class="nivo_search-wrapper">
                        %s
                        <input type="text" class="%s" name="s" placeholder="%s" autocomplete="off">
                        <span class="nivo_search-clear-search" style="display:none;">&times;</span>
                    </div>
                </form>
                <div class="%s"></div>
            </div>',
            esc_attr($atts['container_class']),
            $style_attr,
            esc_url(wc_get_page_permalink('shop')),
            $icon_html,
            esc_attr($atts['input_class']),
            esc_attr($atts['placeholder']),
            esc_attr($atts['results_class'])
        );
        
        return apply_filters('nivo_search_shortcode_html', $html, $atts);
    }
}