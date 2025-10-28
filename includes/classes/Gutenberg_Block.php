<?php
/**
 * Gutenberg Block Handler
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

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
        
        register_block_type('aasfwc/ajax-search', [
            'attributes' => [
                'placeholder' => [
                    'type' => 'string',
                    'default' => __('Search products...', 'advanced-ajax-search-for-woocommerce')
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
            'aasfwc-block-editor',
            AASFWC_PLUGIN_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            AASFWC_VERSION,
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
        $placeholder = esc_attr($attributes['placeholder'] ?? __('Search products...', 'advanced-ajax-search-for-woocommerce'));
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
        
        $icon_html = $show_icon ? '<span class="aasfwc-search-icon">🔍</span>' : '';
        
        return sprintf(
            '<div class="aasfwc-ajax-search-container aasfwc-block">
                <div class="aasfwc-search-wrapper" style="%s">
                    %s
                    <input type="text" class="aasfwc-product-search" placeholder="%s" style="%s">
                </div>
                <div class="aasfwc-search-results"></div>
            </div>',
            $style,
            $icon_html,
            $placeholder,
            $style
        );
    }
}