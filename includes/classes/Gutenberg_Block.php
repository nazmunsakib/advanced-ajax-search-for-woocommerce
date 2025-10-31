<?php
/**
 * Gutenberg Block Handler
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Gutenberg Block Class
 *
 * Handles Gutenberg block registration and functionality
 *
 * @since 1.0.0
 */
class Gutenberg_Block {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('init', [$this, 'register_block']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_assets']);
    }
    
    /**
     * Register Gutenberg block
     *
     * @since 1.0.0
     */
    public function register_block() {
        if (!function_exists('register_block_type')) {
            return;
        }
        
        register_block_type('nivo-search/ajax-search', [
            'render_callback' => [$this, 'render_block']
        ]);
    }
    
    /**
     * Enqueue block editor assets
     *
     * @since 1.0.0
     */
    public function enqueue_block_assets() {
        wp_enqueue_script(
            'nivo-search-block-editor',
            NIVO_SEARCH_PLUGIN_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            NIVO_SEARCH_VERSION,
            true
        );
    }
    
    /**
     * Render block on frontend
     *
     * @since 1.0.0
     * @param array $attributes Block attributes
     * @return string Block HTML
     */
    public function render_block($attributes = []) {
        // Use global settings for all styling
        $placeholder = get_option('nivo_search_placeholder_text', __('Search products...', 'nivo-ajax-search-for-woocommerce'));
        $show_icon = get_option('nivo_search_show_search_icon', 1);
        
        $icon_html = $show_icon ? '<svg class="nivo_search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>' : '';
        
        return sprintf(
            '<div class="nivo-ajax-search-container nivo_search-block">
                <form class="nivo_search-form" role="search" method="get" action="%s">
                    <div class="nivo_search-wrapper">
                        %s
                        <input type="text" class="nivo_search-product-search" name="s" placeholder="%s" autocomplete="off">
                        <span class="nivo_search-clear-search" style="display:none;">&times;</span>
                    </div>
                </form>
                <div class="nivo_search-results"></div>
            </div>',
            esc_url(wc_get_page_permalink('shop')),
            $icon_html,
            esc_attr($placeholder)
        );
    }
}