<?php
/**
 * Asset Enqueue Handler
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

defined('ABSPATH') || exit;

/**
 * Enqueue Class
 *
 * Handles all frontend and backend asset loading.
 * Provides hooks for extensibility and customization.
 *
 * @since 1.0.0
 */
class Enqueue {
    
    /**
     * Plugin version for cache busting
     *
     * @since 1.0.0
     * @var string
     */
    private $version;
    
    /**
     * Plugin URL for asset paths
     *
     * @since 1.0.0
     * @var string
     */
    private $plugin_url;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->version = AASFWC_VERSION;
        $this->plugin_url = AASFWC_PLUGIN_URL;
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Enqueue frontend assets
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_frontend_assets() {
        // Only enqueue on pages that need it
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        $this->enqueue_scripts();
        $this->enqueue_styles();
        $this->localize_scripts();
    }
    
    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Enqueue on settings page
        if ($hook === 'woocommerce_page_aasfwc-settings') {
            // Tailwind CSS
            wp_enqueue_style('aasfwc-tailwind', 'https://cdn.tailwindcss.com', [], $this->version);
            
            // React and ReactDOM
            wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.production.min.js', [], '18.0.0', true);
            wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', ['react'], '18.0.0', true);
            
            // Babel for JSX transformation
            wp_enqueue_script('babel-standalone', 'https://unpkg.com/@babel/standalone/babel.min.js', [], '7.0.0', true);
            
            // Admin React app
            wp_enqueue_script('aasfwc-admin-react', $this->plugin_url . 'assets/js/admin-react.js', ['react', 'react-dom', 'babel-standalone'], $this->version, true);
            
            // Localize script data
            wp_localize_script('aasfwc-admin-react', 'aasfwcAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aasfwc_admin_nonce'),
                'strings' => [
                    'title' => __('Advanced AJAX Search Settings', 'advanced-ajax-search-for-woocommerce'),
                    'save' => __('Save Settings', 'advanced-ajax-search-for-woocommerce'),
                    'saving' => __('Saving...', 'advanced-ajax-search-for-woocommerce'),
                    'saved' => __('Settings saved successfully!', 'advanced-ajax-search-for-woocommerce')
                ]
            ]);
        }
        
        // Allow filtering of admin pages where assets should load
        $allowed_pages = apply_filters('aasfwc_admin_asset_pages', ['woocommerce_page_aasfwc-settings']);
        
        if (!empty($allowed_pages) && in_array($hook, $allowed_pages)) {
            do_action('aasfwc_enqueue_admin_assets', $hook);
        }
    }
    
    /**
     * Enqueue JavaScript files
     *
     * @since 1.0.0
     * @return void
     */
    private function enqueue_scripts() {
        // jQuery dependency
        wp_enqueue_script('jquery');
        
        // Main search script
        $script_deps = apply_filters('aasfwc_script_dependencies', ['jquery']);
        $script_version = apply_filters('aasfwc_script_version', $this->version);
        
        wp_enqueue_script(
            'aasfwc-ajax-search',
            $this->plugin_url . 'assets/js/aasfwc-search.js',
            $script_deps,
            $script_version,
            true
        );
        
        // Allow additional scripts
        do_action('aasfwc_after_enqueue_scripts');
    }
    
    /**
     * Enqueue CSS files
     *
     * @since 1.0.0
     * @return void
     */
    private function enqueue_styles() {
        $style_deps = apply_filters('aasfwc_style_dependencies', []);
        $style_version = apply_filters('aasfwc_style_version', $this->version);
        
        wp_enqueue_style(
            'aasfwc-ajax-search',
            $this->plugin_url . 'assets/css/aasfwc-search.css',
            $style_deps,
            $style_version
        );
        
        // Allow additional styles
        do_action('aasfwc_after_enqueue_styles');
    }
    
    /**
     * Localize scripts with data
     *
     * @since 1.0.0
     * @return void
     */
    private function localize_scripts() {
        $localize_data = apply_filters('aasfwc_localize_data', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aasfwc_search_nonce'),
            'strings' => [
                'no_results' => __('No products found', 'advanced-ajax-search-for-woocommerce'),
                'loading' => __('Loading...', 'advanced-ajax-search-for-woocommerce'),
                'error' => __('Search error occurred', 'advanced-ajax-search-for-woocommerce'),
                'view_all' => __('View All Results', 'advanced-ajax-search-for-woocommerce')
            ],
            'settings' => [
                'min_length' => get_option('aasfwc_min_chars', 2),
                'delay' => get_option('aasfwc_search_delay', 300),
                'max_results' => get_option('aasfwc_search_limit', 10),
                'show_images' => get_option('aasfwc_show_images', 1),
                'show_price' => get_option('aasfwc_show_price', 1),
                'show_add_to_cart' => get_option('aasfwc_show_add_to_cart', 0)
            ]
        ]);
        
        wp_localize_script('aasfwc-ajax-search', 'aasfwc_ajax_search', $localize_data);
    }
    
    /**
     * Check if assets should be enqueued
     *
     * @since 1.0.0
     * @return bool
     */
    private function should_enqueue_assets() {
        // Default conditions
        $should_enqueue = is_shop() || is_product_category() || is_product_tag() || has_shortcode(get_post()->post_content ?? '', 'aasfwc_ajax_search');
        
        // Allow filtering
        return apply_filters('aasfwc_should_enqueue_assets', $should_enqueue);
    }
}