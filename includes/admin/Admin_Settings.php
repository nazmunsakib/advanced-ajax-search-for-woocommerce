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
        
        // AI Settings
        register_setting('aasfwc_settings', 'aasfwc_enable_typo_correction');
        register_setting('aasfwc_settings', 'aasfwc_enable_synonyms');
        
        // Display Settings
        register_setting('aasfwc_settings', 'aasfwc_show_images');
        register_setting('aasfwc_settings', 'aasfwc_show_price');
        register_setting('aasfwc_settings', 'aasfwc_show_add_to_cart');
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
            'enable_typo_correction' => get_option('aasfwc_enable_typo_correction', 1),
            'enable_synonyms' => get_option('aasfwc_enable_synonyms', 1),
            'show_images' => get_option('aasfwc_show_images', 1),
            'show_price' => get_option('aasfwc_show_price', 1),
            'show_add_to_cart' => get_option('aasfwc_show_add_to_cart', 0)
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
        update_option('aasfwc_enable_typo_correction', !empty($settings['enable_typo_correction']));
        update_option('aasfwc_enable_synonyms', !empty($settings['enable_synonyms']));
        update_option('aasfwc_show_images', !empty($settings['show_images']));
        update_option('aasfwc_show_price', !empty($settings['show_price']));
        update_option('aasfwc_show_add_to_cart', !empty($settings['show_add_to_cart']));
        
        wp_send_json_success(['message' => __('Settings saved successfully', 'advanced-ajax-search-for-woocommerce')]);
    }
}