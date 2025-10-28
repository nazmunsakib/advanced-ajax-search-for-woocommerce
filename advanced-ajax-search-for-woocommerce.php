<?php
/**
 * Plugin Name: Advanced AJAX Search for WooCommerce
 * Plugin URI: https://github.com/nazmunsakib/advanced-ajax-search-for-woocommerce
 * Description: Professional live product search with AJAX functionality for WooCommerce stores
 * Version: 1.0.0
 * Author: Nazmun Sakib
 * Author URI: https://nazmunsakib.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: advanced-ajax-search-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Network: false
 *
 * @package AASFWC
 * @author Nazmun Sakib
 * @since 1.0.0
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Define plugin constants
define('AASFWC_VERSION', '1.0.0');
define('AASFWC_PLUGIN_FILE', __FILE__);
define('AASFWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AASFWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AASFWC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function aasfwc_init() {
    // Load Composer autoloader
    if (file_exists(AASFWC_PLUGIN_DIR . 'vendor/autoload.php')) {
        require_once AASFWC_PLUGIN_DIR . 'vendor/autoload.php';
    }
    
    // Initialize main plugin class
    AASFWC\Plugin::get_instance();
}

// Hook initialization
add_action('plugins_loaded', 'aasfwc_init');

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
register_activation_hook(__FILE__, function() {
    // Check WooCommerce dependency
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'advanced-ajax-search-for-woocommerce'));
    }
});

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
register_deactivation_hook(__FILE__, function() {
    // Cleanup if needed
});