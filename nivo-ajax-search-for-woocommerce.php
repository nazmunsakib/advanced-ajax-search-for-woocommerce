<?php
/**
 * Plugin Name: Nivo AJAX Search for WooCommerce
 * Plugin URI: https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce
 * Description: Professional live product search with AJAX functionality for WooCommerce stores
 * Version: 1.0.0
 * Author: Nazmun Sakib
 * Author URI: https://nazmunsakib.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nivo-ajax-search-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Network: false
 *
<<<<<<< HEAD
 * @package NivoSearch
=======
 * @package NASFWC
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58
 * @author Nazmun Sakib
 * @since 1.0.0
 */

<<<<<<< HEAD
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'NIVO_SEARCH_VERSION', '1.0.0' );
define( 'NIVO_SEARCH_PLUGIN_FILE', __FILE__ );
define( 'NIVO_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIVO_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NIVO_SEARCH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
=======
// Prevent direct access
defined('ABSPATH') || exit;

// Define plugin constants
define('NASFWC_VERSION', '1.0.0');
define('NASFWC_PLUGIN_FILE', __FILE__);
define('NASFWC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NASFWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NASFWC_PLUGIN_BASENAME', plugin_basename(__FILE__));
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Declare WooCommerce HPOS compatibility
 *
 * @since 1.0.0
 * @return void
 */
<<<<<<< HEAD
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
=======
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
<<<<<<< HEAD
function nivo_search_init() {
	// Load Composer autoloader.
	if ( file_exists( NIVO_SEARCH_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		require_once NIVO_SEARCH_PLUGIN_DIR . 'vendor/autoload.php';
	}

	// Initialize main plugin class.
	NivoSearch\Nivo_Ajax_Search::get_instance();
}

// Hook initialization.
add_action( 'plugins_loaded', 'nivo_search_init' );
=======
function nasfwc_init() {
    // Load Composer autoloader
    if (file_exists(NASFWC_PLUGIN_DIR . 'vendor/autoload.php')) {
        require_once NASFWC_PLUGIN_DIR . 'vendor/autoload.php';
    }
    
    // Initialize main plugin class
    NASFWC\Nivo_Ajax_Search::get_instance();
}

// Hook initialization
add_action('plugins_loaded', 'nasfwc_init');
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Add settings link to plugin action links
 *
 * @since 1.0.0
 * @param array $links Plugin action links
 * @return array Modified plugin action links
 */
<<<<<<< HEAD
add_filter(
	'plugin_action_links_' . NIVO_SEARCH_PLUGIN_BASENAME,
	function( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=nivo_search-settings' ) ) . '">' . esc_html__( 'Settings', 'nivo-ajax-search-for-woocommerce' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
);
=======
add_filter('plugin_action_links_' . NASFWC_PLUGIN_BASENAME, function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=nasfwc-settings') . '">' . __('Settings', 'nivo-ajax-search-for-woocommerce') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
<<<<<<< HEAD
register_activation_hook(
	__FILE__,
	function() {
		// Check WooCommerce dependency.
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'This plugin requires WooCommerce to be installed and active.', 'nivo-ajax-search-for-woocommerce' ) );
		}
	}
);
=======
register_activation_hook(__FILE__, function() {
    // Check WooCommerce dependency
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('This plugin requires WooCommerce to be installed and active.', 'nivo-ajax-search-for-woocommerce'));
    }
});
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
<<<<<<< HEAD
register_deactivation_hook(
	__FILE__,
	function() {
		// Cleanup if needed.
	}
);
=======
register_deactivation_hook(__FILE__, function() {
    // Cleanup if needed
});
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58

/**
 * Add plugin meta links
 *
 * @since 1.0.0
 * @param array $links Plugin meta links
 * @param string $file Plugin file
 * @return array Modified plugin meta links
 */
<<<<<<< HEAD
add_filter(
	'plugin_row_meta',
	function( $links, $file ) {
		if ( NIVO_SEARCH_PLUGIN_BASENAME === $file ) {
			$links[] = '<a href="https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce" target="_blank">' . esc_html__( 'Documentation', 'nivo-ajax-search-for-woocommerce' ) . '</a>';
			$links[] = '<a href="https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce/issues" target="_blank">' . esc_html__( 'Support', 'nivo-ajax-search-for-woocommerce' ) . '</a>';
		}
		return $links;
	},
	10,
	2
);
=======
add_filter('plugin_row_meta', function($links, $file) {
    if ($file === NASFWC_PLUGIN_BASENAME) {
        $links[] = '<a href="https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce" target="_blank">' . __('Documentation', 'nivo-ajax-search-for-woocommerce') . '</a>';
        $links[] = '<a href="https://github.com/nazmunsakib/nivo-ajax-search-for-woocommerce/issues" target="_blank">' . __('Support', 'nivo-ajax-search-for-woocommerce') . '</a>';
    }
    return $links;
}, 10, 2);
>>>>>>> 643a01bd53cd81e4a6e0b6db43d1b2086a4ddc58
