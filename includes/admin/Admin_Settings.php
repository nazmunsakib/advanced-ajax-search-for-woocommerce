<?php
/**
 * Admin Settings Page
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

defined('ABSPATH') || exit;

/**
 * Admin Settings Class
 *
 * Handles plugin admin settings and configuration
 *
 * @since 1.0.0
 */
class Admin_Settings {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_aasfwc_save_settings', [$this, 'save_settings_ajax']);
        add_action('wp_ajax_aasfwc_get_settings', [$this, 'get_settings_ajax']);
    }
    
    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('AJAX Search Settings', 'advanced-ajax-search-for-woocommerce'),
            __('Advanced AJAX Search', 'advanced-ajax-search-for-woocommerce'),
            'manage_woocommerce',
            'aasfwc-settings',
            [$this, 'settings_page']
        );
    }
    
    /**
     * Register settings
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // General Settings
        register_setting('aasfwc_settings', 'aasfwc_enable_ajax');
        register_setting('aasfwc_settings', 'aasfwc_enable_ai');
        register_setting('aasfwc_settings', 'aasfwc_search_limit');
        register_setting('aasfwc_settings', 'aasfwc_min_chars');
        register_setting('aasfwc_settings', 'aasfwc_search_delay');
        register_setting('aasfwc_settings', 'aasfwc_excluded_products');
        
        // Search Scope Settings
        register_setting('aasfwc_settings', 'aasfwc_search_in_title');
        register_setting('aasfwc_settings', 'aasfwc_search_in_sku');
        register_setting('aasfwc_settings', 'aasfwc_search_in_content');
        register_setting('aasfwc_settings', 'aasfwc_search_in_excerpt');
        register_setting('aasfwc_settings', 'aasfwc_search_in_categories');
        register_setting('aasfwc_settings', 'aasfwc_search_in_tags');
        register_setting('aasfwc_settings', 'aasfwc_search_in_attributes');
        register_setting('aasfwc_settings', 'aasfwc_exclude_out_of_stock');
        
        // AI Settings
        register_setting('aasfwc_settings', 'aasfwc_enable_typo_correction');
        register_setting('aasfwc_settings', 'aasfwc_enable_synonyms');
        
        // Style & Layout Settings - Search Bar
        register_setting('aasfwc_settings', 'aasfwc_placeholder_text');
        register_setting('aasfwc_settings', 'aasfwc_search_bar_width');
        register_setting('aasfwc_settings', 'aasfwc_border_width');
        register_setting('aasfwc_settings', 'aasfwc_border_color');
        register_setting('aasfwc_settings', 'aasfwc_border_radius');
        register_setting('aasfwc_settings', 'aasfwc_bg_color');
        register_setting('aasfwc_settings', 'aasfwc_padding_vertical');
        register_setting('aasfwc_settings', 'aasfwc_padding_horizontal');
        register_setting('aasfwc_settings', 'aasfwc_margin_vertical');
        register_setting('aasfwc_settings', 'aasfwc_margin_horizontal');
        register_setting('aasfwc_settings', 'aasfwc_show_search_icon');
        register_setting('aasfwc_settings', 'aasfwc_show_submit_button');
        // Style & Layout Settings - Results
        register_setting('aasfwc_settings', 'aasfwc_results_border_width');
        register_setting('aasfwc_settings', 'aasfwc_results_border_color');
        register_setting('aasfwc_settings', 'aasfwc_results_border_radius');
        register_setting('aasfwc_settings', 'aasfwc_results_bg_color');
        register_setting('aasfwc_settings', 'aasfwc_results_padding');
        
        // Display Settings
        register_setting('aasfwc_settings', 'aasfwc_show_images');
        register_setting('aasfwc_settings', 'aasfwc_show_price');
        register_setting('aasfwc_settings', 'aasfwc_show_add_to_cart');
        register_setting('aasfwc_settings', 'aasfwc_show_sku');
        register_setting('aasfwc_settings', 'aasfwc_show_description');
        register_setting('aasfwc_settings', 'aasfwc_show_categories');
        register_setting('aasfwc_settings', 'aasfwc_show_tags');
    }
    
    /**
     * Settings page HTML
     *
     * @since 1.0.0
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <div id="aasfwc-settings-root"></div>
        </div>
        <?php
    }
    
    /**
     * Get settings via AJAX
     *
     * @since 1.0.0
     */
    public function get_settings_ajax() {
        check_ajax_referer('aasfwc_admin_nonce', 'nonce');
        
        $settings = [
            'enable_ajax' => get_option('aasfwc_enable_ajax', 1),
            'enable_ai' => get_option('aasfwc_enable_ai', 0),
            'search_limit' => get_option('aasfwc_search_limit', 10),
            'min_chars' => get_option('aasfwc_min_chars', 2),
            'search_delay' => get_option('aasfwc_search_delay', 300),
            'excluded_products' => get_option('aasfwc_excluded_products', ''),
            // Search scope
            'search_in_title' => get_option('aasfwc_search_in_title', 1),
            'search_in_sku' => get_option('aasfwc_search_in_sku', 1),
            'search_in_content' => get_option('aasfwc_search_in_content', 0),
            'search_in_excerpt' => get_option('aasfwc_search_in_excerpt', 0),
            'search_in_categories' => get_option('aasfwc_search_in_categories', 1),
            'search_in_tags' => get_option('aasfwc_search_in_tags', 1),
            'search_in_attributes' => get_option('aasfwc_search_in_attributes', 0),
            'exclude_out_of_stock' => get_option('aasfwc_exclude_out_of_stock', 0),
            // AI features
            'enable_typo_correction' => get_option('aasfwc_enable_typo_correction', 1),
            'enable_synonyms' => get_option('aasfwc_enable_synonyms', 1),
            // Style & Layout - Search Bar
            'placeholder_text' => get_option('aasfwc_placeholder_text', 'Search products...'),
            'search_bar_width' => get_option('aasfwc_search_bar_width', 600),
            'border_width' => get_option('aasfwc_border_width', 1),
            'border_color' => get_option('aasfwc_border_color', '#ddd'),
            'border_radius' => get_option('aasfwc_border_radius', 4),
            'bg_color' => get_option('aasfwc_bg_color', '#ffffff'),
            'padding_vertical' => get_option('aasfwc_padding_vertical', 10),
            'padding_horizontal' => get_option('aasfwc_padding_horizontal', 15),
            'margin_vertical' => get_option('aasfwc_margin_vertical', 0),
            'margin_horizontal' => get_option('aasfwc_margin_horizontal', 0),
            'show_search_icon' => get_option('aasfwc_show_search_icon', 1),
            'show_submit_button' => get_option('aasfwc_show_submit_button', 0),
            // Style & Layout - Results
            'results_border_width' => get_option('aasfwc_results_border_width', 1),
            'results_border_color' => get_option('aasfwc_results_border_color', '#ddd'),
            'results_border_radius' => get_option('aasfwc_results_border_radius', 4),
            'results_bg_color' => get_option('aasfwc_results_bg_color', '#ffffff'),
            'results_padding' => get_option('aasfwc_results_padding', 10),
            // Display options
            'show_images' => get_option('aasfwc_show_images', 1),
            'show_price' => get_option('aasfwc_show_price', 1),
            'show_add_to_cart' => get_option('aasfwc_show_add_to_cart', 0),
            'show_sku' => get_option('aasfwc_show_sku', 0),
            'show_description' => get_option('aasfwc_show_description', 1),
            'show_categories' => get_option('aasfwc_show_categories', 1),
            'show_tags' => get_option('aasfwc_show_tags', 0)
        ];
        
        wp_send_json_success($settings);
    }
    
    /**
     * Save settings via AJAX
     *
     * @since 1.0.0
     */
    public function save_settings_ajax() {
        check_ajax_referer('aasfwc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'advanced-ajax-search-for-woocommerce')]);
        }
        
        $settings = json_decode(stripslashes($_POST['settings']), true);
        
        if (!$settings) {
            wp_send_json_error(['message' => __('Invalid settings data', 'advanced-ajax-search-for-woocommerce')]);
        }
        
        // Save each setting
        update_option('aasfwc_enable_ajax', !empty($settings['enable_ajax']));
        update_option('aasfwc_enable_ai', !empty($settings['enable_ai']));
        update_option('aasfwc_search_limit', intval($settings['search_limit']));
        update_option('aasfwc_min_chars', intval($settings['min_chars']));
        update_option('aasfwc_search_delay', intval($settings['search_delay']));
        update_option('aasfwc_excluded_products', sanitize_text_field($settings['excluded_products']));
        // Search scope
        update_option('aasfwc_search_in_title', !empty($settings['search_in_title']));
        update_option('aasfwc_search_in_sku', !empty($settings['search_in_sku']));
        update_option('aasfwc_search_in_content', !empty($settings['search_in_content']));
        update_option('aasfwc_search_in_excerpt', !empty($settings['search_in_excerpt']));
        update_option('aasfwc_search_in_categories', !empty($settings['search_in_categories']));
        update_option('aasfwc_search_in_tags', !empty($settings['search_in_tags']));
        update_option('aasfwc_search_in_attributes', !empty($settings['search_in_attributes']));
        update_option('aasfwc_exclude_out_of_stock', !empty($settings['exclude_out_of_stock']));
        // AI features
        update_option('aasfwc_enable_typo_correction', !empty($settings['enable_typo_correction']));
        update_option('aasfwc_enable_synonyms', !empty($settings['enable_synonyms']));
        // Style & Layout - Search Bar
        update_option('aasfwc_placeholder_text', sanitize_text_field($settings['placeholder_text']));
        update_option('aasfwc_search_bar_width', intval($settings['search_bar_width']));
        update_option('aasfwc_border_width', intval($settings['border_width']));
        update_option('aasfwc_border_color', sanitize_hex_color($settings['border_color']));
        update_option('aasfwc_border_radius', intval($settings['border_radius']));
        update_option('aasfwc_bg_color', sanitize_hex_color($settings['bg_color']));
        update_option('aasfwc_padding_vertical', intval($settings['padding_vertical']));
        update_option('aasfwc_padding_horizontal', intval($settings['padding_horizontal']));
        update_option('aasfwc_margin_vertical', intval($settings['margin_vertical']));
        update_option('aasfwc_margin_horizontal', intval($settings['margin_horizontal']));
        update_option('aasfwc_show_search_icon', !empty($settings['show_search_icon']));
        update_option('aasfwc_show_submit_button', !empty($settings['show_submit_button']));
        // Style & Layout - Results
        update_option('aasfwc_results_border_width', intval($settings['results_border_width']));
        update_option('aasfwc_results_border_color', sanitize_hex_color($settings['results_border_color']));
        update_option('aasfwc_results_border_radius', intval($settings['results_border_radius']));
        update_option('aasfwc_results_bg_color', sanitize_hex_color($settings['results_bg_color']));
        update_option('aasfwc_results_padding', intval($settings['results_padding']));
        // Display options
        update_option('aasfwc_show_images', !empty($settings['show_images']));
        update_option('aasfwc_show_price', !empty($settings['show_price']));
        update_option('aasfwc_show_add_to_cart', !empty($settings['show_add_to_cart']));
        update_option('aasfwc_show_sku', !empty($settings['show_sku']));
        update_option('aasfwc_show_description', !empty($settings['show_description']));
        update_option('aasfwc_show_categories', !empty($settings['show_categories']));
        update_option('aasfwc_show_tags', !empty($settings['show_tags']));
        
        wp_send_json_success(['message' => __('Settings saved successfully', 'advanced-ajax-search-for-woocommerce')]);
    }
}