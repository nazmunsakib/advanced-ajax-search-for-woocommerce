<?php
/**
 * Admin Settings Page
 *
 * @package NASFWC
 * @since 1.0.0
 */

namespace NASFWC;

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
        add_action('wp_ajax_NASFWC_save_settings', [$this, 'save_settings_ajax']);
        add_action('wp_ajax_NASFWC_get_settings', [$this, 'get_settings_ajax']);
        add_action('wp_ajax_NASFWC_reset_settings', [$this, 'reset_settings_ajax']);
    }
    
    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('AJAX Search Settings', 'nivo-ajax-search-for-woocommerce'),
            __('Nivo AJAX Search', 'nivo-ajax-search-for-woocommerce'),
            'manage_woocommerce',
            'NASFWC-settings',
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
        register_setting('NASFWC_settings', 'NASFWC_enable_ajax');
        register_setting('NASFWC_settings', 'NASFWC_enable_ai');
        register_setting('NASFWC_settings', 'NASFWC_search_limit');
        register_setting('NASFWC_settings', 'NASFWC_min_chars');
        register_setting('NASFWC_settings', 'NASFWC_search_delay');
        register_setting('NASFWC_settings', 'NASFWC_excluded_products');
        
        // Search Scope Settings
        register_setting('NASFWC_settings', 'NASFWC_search_in_title');
        register_setting('NASFWC_settings', 'NASFWC_search_in_sku');
        register_setting('NASFWC_settings', 'NASFWC_search_in_content');
        register_setting('NASFWC_settings', 'NASFWC_search_in_excerpt');
        register_setting('NASFWC_settings', 'NASFWC_search_in_categories');
        register_setting('NASFWC_settings', 'NASFWC_search_in_tags');
        register_setting('NASFWC_settings', 'NASFWC_search_in_attributes');
        register_setting('NASFWC_settings', 'NASFWC_exclude_out_of_stock');
        
        // AI Settings
        register_setting('NASFWC_settings', 'NASFWC_enable_typo_correction');
        register_setting('NASFWC_settings', 'NASFWC_enable_synonyms');
        
        // Style & Layout Settings - Search Bar
        register_setting('NASFWC_settings', 'NASFWC_placeholder_text');
        register_setting('NASFWC_settings', 'NASFWC_search_bar_width');
        register_setting('NASFWC_settings', 'NASFWC_border_width');
        register_setting('NASFWC_settings', 'NASFWC_border_color');
        register_setting('NASFWC_settings', 'NASFWC_border_radius');
        register_setting('NASFWC_settings', 'NASFWC_bg_color');
        register_setting('NASFWC_settings', 'NASFWC_padding_vertical');
        register_setting('NASFWC_settings', 'NASFWC_center_align');
        register_setting('NASFWC_settings', 'NASFWC_show_search_icon');
        register_setting('NASFWC_settings', 'NASFWC_show_submit_button');
        // Style & Layout Settings - Results
        register_setting('NASFWC_settings', 'NASFWC_results_border_width');
        register_setting('NASFWC_settings', 'NASFWC_results_border_color');
        register_setting('NASFWC_settings', 'NASFWC_results_border_radius');
        register_setting('NASFWC_settings', 'NASFWC_results_bg_color');
        register_setting('NASFWC_settings', 'NASFWC_results_padding');
        
        // Theme Inheritance Settings
        register_setting('NASFWC_settings', 'NASFWC_font_family');
        register_setting('NASFWC_settings', 'NASFWC_text_color');
        register_setting('NASFWC_settings', 'NASFWC_hover_color');
        register_setting('NASFWC_settings', 'NASFWC_hover_bg');
        
        // Display Settings
        register_setting('NASFWC_settings', 'NASFWC_show_images');
        register_setting('NASFWC_settings', 'NASFWC_show_price');
        register_setting('NASFWC_settings', 'NASFWC_show_add_to_cart');
        register_setting('NASFWC_settings', 'NASFWC_show_sku');
        register_setting('NASFWC_settings', 'NASFWC_show_description');
        register_setting('NASFWC_settings', 'NASFWC_show_categories');
        register_setting('NASFWC_settings', 'NASFWC_show_tags');
    }
    
    /**
     * Settings page HTML
     *
     * @since 1.0.0
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <div id="NASFWC-settings-root"></div>
        </div>
        <?php
    }
    
    /**
     * Get settings via AJAX
     *
     * @since 1.0.0
     */
    public function get_settings_ajax() {
        check_ajax_referer('NASFWC_admin_nonce', 'nonce');
        
        $settings = [
            'enable_ajax' => (int)get_option('NASFWC_enable_ajax', 1),
            'enable_ai' => (int)get_option('NASFWC_enable_ai', 0),
            'search_limit' => (int)get_option('NASFWC_search_limit', 10),
            'min_chars' => (int)get_option('NASFWC_min_chars', 3),
            'search_delay' => (int)get_option('NASFWC_search_delay', 300),
            'excluded_products' => get_option('NASFWC_excluded_products', ''),
            // Search scope
            'search_in_title' => (int)get_option('NASFWC_search_in_title', 1),
            'search_in_sku' => (int)get_option('NASFWC_search_in_sku', 0),
            'search_in_content' => (int)get_option('NASFWC_search_in_content', 0),
            'search_in_excerpt' => (int)get_option('NASFWC_search_in_excerpt', 0),
            'search_in_categories' => (int)get_option('NASFWC_search_in_categories', 0),
            'search_in_attributes' => (int)get_option('NASFWC_search_in_attributes', 0),
            'exclude_out_of_stock' => (int)get_option('NASFWC_exclude_out_of_stock', 0),
            // AI features
            'enable_typo_correction' => (int)get_option('NASFWC_enable_typo_correction', 1),
            'enable_synonyms' => (int)get_option('NASFWC_enable_synonyms', 1),
            // Style & Layout - Search Bar
            'placeholder_text' => get_option('NASFWC_placeholder_text', 'Search products...'),
            'search_bar_width' => (int)get_option('NASFWC_search_bar_width', 600),
            'border_width' => (int)get_option('NASFWC_border_width', 1),
            'border_color' => get_option('NASFWC_border_color', '#dfdfdf'),
            'border_radius' => (int)get_option('NASFWC_border_radius', 30),
            'bg_color' => get_option('NASFWC_bg_color', '#dfdfdf'),
            'padding_vertical' => (int)get_option('NASFWC_padding_vertical', 15),
            'center_align' => (int)get_option('NASFWC_center_align', 0),
            'show_search_icon' => (int)get_option('NASFWC_show_search_icon', 1),
            'show_submit_button' => (int)get_option('NASFWC_show_submit_button', 0),
            // Style & Layout - Results
            'results_border_width' => (int)get_option('NASFWC_results_border_width', 1),
            'results_border_color' => get_option('NASFWC_results_border_color', '#ddd'),
            'results_border_radius' => (int)get_option('NASFWC_results_border_radius', 4),
            'results_bg_color' => get_option('NASFWC_results_bg_color', '#ffffff'),
            'results_padding' => (int)get_option('NASFWC_results_padding', 5),
            // Theme inheritance
            'font_family' => get_option('NASFWC_font_family', ''),
            'text_color' => get_option('NASFWC_text_color', ''),
            'hover_color' => get_option('NASFWC_hover_color', ''),
            'hover_bg' => get_option('NASFWC_hover_bg', ''),
            // Display options
            'show_images' => (int)get_option('NASFWC_show_images', 1),
            'show_price' => (int)get_option('NASFWC_show_price', 1),
            'show_add_to_cart' => (int)get_option('NASFWC_show_add_to_cart', 0),
            'show_sku' => (int)get_option('NASFWC_show_sku', 0),
            'show_description' => (int)get_option('NASFWC_show_description', 0),
            'show_categories' => (int)get_option('NASFWC_show_categories', 1),
            'show_tags' => (int)get_option('NASFWC_show_tags', 0)
        ];
        
        wp_send_json_success($settings);
    }
    
    /**
     * Save settings via AJAX
     *
     * @since 1.0.0
     */
    public function save_settings_ajax() {
        check_ajax_referer('NASFWC_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'nivo-ajax-search-for-woocommerce')]);
        }
        
        $settings = json_decode(stripslashes($_POST['settings']), true);
        
        if (!$settings) {
            wp_send_json_error(['message' => __('Invalid settings data', 'nivo-ajax-search-for-woocommerce')]);
        }
        
        // Helper function to convert to int
        $to_int = function($val) { return isset($val) && $val ? 1 : 0; };
        
        // Save each setting
        update_option('NASFWC_enable_ajax', $to_int($settings['enable_ajax']));
        update_option('NASFWC_enable_ai', $to_int($settings['enable_ai']));
        update_option('NASFWC_search_limit', intval($settings['search_limit']));
        update_option('NASFWC_min_chars', intval($settings['min_chars']));
        update_option('NASFWC_search_delay', intval($settings['search_delay']));
        update_option('NASFWC_excluded_products', sanitize_text_field($settings['excluded_products']));
        // Search scope
        update_option('NASFWC_search_in_title', $to_int($settings['search_in_title']));
        update_option('NASFWC_search_in_sku', $to_int($settings['search_in_sku']));
        update_option('NASFWC_search_in_content', $to_int($settings['search_in_content']));
        update_option('NASFWC_search_in_excerpt', $to_int($settings['search_in_excerpt']));
        update_option('NASFWC_search_in_categories', $to_int($settings['search_in_categories']));
        update_option('NASFWC_search_in_tags', $to_int($settings['search_in_tags']));
        update_option('NASFWC_search_in_attributes', $to_int($settings['search_in_attributes']));
        update_option('NASFWC_exclude_out_of_stock', $to_int($settings['exclude_out_of_stock']));
        // AI features
        update_option('NASFWC_enable_typo_correction', $to_int($settings['enable_typo_correction']));
        update_option('NASFWC_enable_synonyms', $to_int($settings['enable_synonyms']));
        // Style & Layout - Search Bar
        update_option('NASFWC_placeholder_text', sanitize_text_field($settings['placeholder_text']));
        update_option('NASFWC_search_bar_width', intval($settings['search_bar_width']));
        update_option('NASFWC_border_width', intval($settings['border_width']));
        update_option('NASFWC_border_color', sanitize_hex_color($settings['border_color']));
        update_option('NASFWC_border_radius', intval($settings['border_radius']));
        update_option('NASFWC_bg_color', sanitize_hex_color($settings['bg_color']));
        update_option('NASFWC_padding_vertical', intval($settings['padding_vertical']));
        update_option('NASFWC_center_align', $to_int($settings['center_align']));
        update_option('NASFWC_show_search_icon', $to_int($settings['show_search_icon']));
        update_option('NASFWC_show_submit_button', $to_int($settings['show_submit_button']));
        // Style & Layout - Results
        update_option('NASFWC_results_border_width', intval($settings['results_border_width']));
        update_option('NASFWC_results_border_color', sanitize_hex_color($settings['results_border_color']));
        update_option('NASFWC_results_border_radius', intval($settings['results_border_radius']));
        update_option('NASFWC_results_bg_color', sanitize_hex_color($settings['results_bg_color']));
        update_option('NASFWC_results_padding', intval($settings['results_padding']));
        // Theme inheritance
        update_option('NASFWC_font_family', sanitize_text_field($settings['font_family']));
        update_option('NASFWC_text_color', sanitize_hex_color($settings['text_color']));
        update_option('NASFWC_hover_color', sanitize_hex_color($settings['hover_color']));
        update_option('NASFWC_hover_bg', sanitize_text_field($settings['hover_bg']));
        // Display options
        update_option('NASFWC_show_images', $to_int($settings['show_images']));
        update_option('NASFWC_show_price', $to_int($settings['show_price']));
        update_option('NASFWC_show_add_to_cart', $to_int($settings['show_add_to_cart']));
        update_option('NASFWC_show_sku', $to_int($settings['show_sku']));
        update_option('NASFWC_show_description', $to_int($settings['show_description']));
        update_option('NASFWC_show_categories', $to_int($settings['show_categories']));
        update_option('NASFWC_show_tags', $to_int($settings['show_tags']));
        
        wp_send_json_success(['message' => __('Settings saved successfully', 'nivo-ajax-search-for-woocommerce')]);
    }
    
    /**
     * Reset settings via AJAX
     *
     * @since 1.0.0
     */
    public function reset_settings_ajax() {
        check_ajax_referer('NASFWC_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'nivo-ajax-search-for-woocommerce')]);
        }
        
        // Delete all settings
        $options = [
            'NASFWC_enable_ajax', 'NASFWC_enable_ai', 'NASFWC_search_limit', 'NASFWC_min_chars', 'NASFWC_search_delay',
            'NASFWC_excluded_products', 'NASFWC_search_in_title', 'NASFWC_search_in_sku', 'NASFWC_search_in_content',
            'NASFWC_search_in_excerpt', 'NASFWC_search_in_categories', 'NASFWC_search_in_tags', 'NASFWC_search_in_attributes',
            'NASFWC_exclude_out_of_stock', 'NASFWC_enable_typo_correction', 'NASFWC_enable_synonyms', 'NASFWC_placeholder_text',
            'NASFWC_search_bar_width', 'NASFWC_border_width', 'NASFWC_border_color', 'NASFWC_border_radius', 'NASFWC_bg_color',
            'NASFWC_padding_vertical', 'NASFWC_center_align', 'NASFWC_show_search_icon', 'NASFWC_show_submit_button',
            'NASFWC_results_border_width', 'NASFWC_results_border_color', 'NASFWC_results_border_radius', 'NASFWC_results_bg_color',
            'NASFWC_results_padding', 'NASFWC_font_family', 'NASFWC_text_color', 'NASFWC_hover_color', 'NASFWC_hover_bg',
            'NASFWC_show_images', 'NASFWC_show_price', 'NASFWC_show_add_to_cart', 'NASFWC_show_sku', 'NASFWC_show_description',
            'NASFWC_show_categories', 'NASFWC_show_tags'
        ];
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Return default settings
        $settings = [
            'enable_ajax' => 1,
            'enable_ai' => 0,
            'search_limit' => 10,
            'min_chars' => 3,
            'search_delay' => 300,
            'excluded_products' => '',
            'search_in_title' => 1,
            'search_in_sku' => 0,
            'search_in_content' => 0,
            'search_in_excerpt' => 0,
            'search_in_categories' => 0,
            'search_in_attributes' => 0,
            'exclude_out_of_stock' => 0,
            'enable_typo_correction' => 1,
            'enable_synonyms' => 1,
            'placeholder_text' => 'Search products...',
            'search_bar_width' => 600,
            'border_width' => 1,
            'border_color' => '#dfdfdf',
            'border_radius' => 30,
            'bg_color' => '#dfdfdf',
            'padding_vertical' => 15,
            'center_align' => 0,
            'show_search_icon' => 1,
            'show_submit_button' => 0,
            'results_border_width' => 1,
            'results_border_color' => '#ddd',
            'results_border_radius' => 4,
            'results_bg_color' => '#ffffff',
            'results_padding' => 5,
            'font_family' => '',
            'text_color' => '',
            'hover_color' => '',
            'hover_bg' => '',
            'show_images' => 1,
            'show_price' => 1,
            'show_add_to_cart' => 0,
            'show_sku' => 0,
            'show_description' => 0,
            'show_categories' => 1,
            'show_tags' => 0
        ];
        
        wp_send_json_success(['message' => __('Settings reset successfully', 'nivo-ajax-search-for-woocommerce'), 'settings' => $settings]);
    }
}