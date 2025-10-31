<?php
/**
 * Gutenberg Block Handler
 *
 * @package NASFWC
 * @since 1.0.0
 */

namespace NASFWC;

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
        
        register_block_type('NASFWC/ajax-search', [
            'attributes' => [
                'placeholder' => [
                    'type' => 'string',
                    'default' => __('Search products...', 'nivo-ajax-search-for-woocommerce')
                ],
                'backgroundColor' => [
                    'type' => 'string',
                    'default' => '#ffffff'
                ],
                'textColor' => [
                    'type' => 'string',
                    'default' => '#333333'
                ],
                'borderColor' => [
                    'type' => 'string',
                    'default' => '#dddddd'
                ],
                'showIcon' => [
                    'type' => 'boolean',
                    'default' => true
                ]
            ],
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
            'NASFWC-block-editor',
            NASFWC_PLUGIN_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            NASFWC_VERSION,
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
    public function render_block($attributes) {
        $placeholder = esc_attr($attributes['placeholder'] ?? __('Search products...', 'nivo-ajax-search-for-woocommerce'));
        $bg_color = esc_attr($attributes['backgroundColor'] ?? '#ffffff');
        $text_color = esc_attr($attributes['textColor'] ?? '#333333');
        $border_color = esc_attr($attributes['borderColor'] ?? '#dddddd');
        $show_icon = $attributes['showIcon'] ?? true;
        
        $style = sprintf(
            'background-color: %s; color: %s; border-color: %s;',
            $bg_color,
            $text_color,
            $border_color
        );
        
        $icon_html = $show_icon ? '<svg class="NASFWC-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>' : '';
        
        return sprintf(
            '<div class="NASFWC-ajax-search-container NASFWC-block">
                <form class="NASFWC-search-form" role="search" method="get" action="%s">
                    <div class="NASFWC-search-wrapper" style="%s">
                        %s
                        <input type="text" class="NASFWC-product-search" name="s" placeholder="%s" style="%s" autocomplete="off">
                        <span class="NASFWC-clear-search" style="display:none;">&times;</span>
                    </div>
                </form>
                <div class="NASFWC-search-results"></div>
            </div>',
            esc_url(wc_get_page_permalink('shop')),
            $style,
            $icon_html,
            $placeholder,
            $style
        );
    }
}