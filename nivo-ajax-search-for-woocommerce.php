<?php
/**
 * Plugin Name: NivoSearch – Ajax Product Search for WooCommerce
 * Plugin URI: https://nivosearch.com
 * Description: The fast, modern WooCommerce product search. Give your customers a beautiful live AJAX search bar with instant product results.
 * Version: 1.1.1
 * Author: Nazmun Sakib
 * Author URI: https://nazmunsakib.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nivo-ajax-search-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 *
 * @package NivoSearch
 * @author Nazmun Sakib
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'NIVO_SEARCH_VERSION', '1.1.1' );
define( 'NIVO_SEARCH_PLUGIN_FILE', __FILE__ );
define( 'NIVO_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NIVO_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NIVO_SEARCH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare WooCommerce HPOS compatibility
 *
 * @since 1.0.0
 * @return void
 */
function before_woocommerce_init_render(){
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_filter('before_woocommerce_init', 'before_woocommerce_init_render');


/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
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

/**
 * Add settings link to plugin action links
 *
 * @since 1.0.0
 * @param array $links Plugin action links
 * @return array Modified plugin action links
 */
function plugin_action_links_render($links){
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=nivo-search' ) ) . '">' . esc_html__( 'Settings', 'nivo-ajax-search-for-woocommerce' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter('plugin_action_links_' . NIVO_SEARCH_PLUGIN_BASENAME, 'plugin_action_links_render', 10, 2);


/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function nivo_search_activate() {
	$default_preset = get_option( 'nivo_search_default_preset_created' );
	
	if ( $default_preset ) {
		return;
	}
	
	$preset_id = wp_insert_post(
		array(
			'post_title'  => __( 'Default AJAX Search', 'nivo-ajax-search-for-woocommerce' ),
			'post_type'   => 'nivo_search_preset',
			'post_status' => 'publish',
		)
	);
	
	if ( ! $preset_id || is_wp_error( $preset_id ) ) {
		return;
	}

	// 1. Process Query Settings
	$genarale_settings = [
		'limit' 		=> 10,
		'min_chars' 	=> 2,
		'placeholder' 	=> __( 'Search products...', 'nivo-ajax-search-for-woocommerce' ),
	];
	
	// 2. Process Query Settings
	$query_settings = [
		'search_in_title' 			=> 1,
		'search_in_sku' 			=> 1,
		'search_in_content' 		=> 1,
		'search_in_excerpt' 		=> 1,
		'search_product_categories' => 1,
		'search_product_tags' 		=> 0,	
		'exclude_out_of_stock' 		=> 0,
	];

	// 3. Process Display Settings
	$display_settings = [
		'show_images' 		=> 1,
		'show_price' 		=> 1,
		'show_sku' 			=> 1,
		'show_description' 	=> 1,
	];

	// 4. Process Style Settings
	$style_settings = [
		'bar_width' 		=> 600,
		'bar_height' 		=> 50,
		'border_color' 		=> '#ddd',
		'bg_color' 			=> '#dfdfdf',
		'text_color' 		=> '#333333',
		'results_width'		=> 600,
		'results_text_color' => '#333333',
		'results_border_color' => '#ddd',
		'results_bg_color' 	=> '#ffffff',
	];

	// Save split keys
	update_post_meta($preset_id, '_nivo_search_generale', $genarale_settings);
	update_post_meta($preset_id, '_nivo_search_query', $query_settings);
	update_post_meta($preset_id, '_nivo_search_display', $display_settings);
	update_post_meta($preset_id, '_nivo_search_style', $style_settings);
	
	update_option( 'nivo_search_default_preset_created', $preset_id );
}
register_activation_hook( __FILE__, 'nivo_search_activate' );

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
function nivo_search_deactivate() {
	// Cleanup if needed
}
register_deactivation_hook( __FILE__, 'nivo_search_deactivate' );

/**
 * Add plugin meta links
 *
 * @since 1.0.0
 * @param array $links Plugin meta links
 * @param string $file Plugin file
 * @return array Modified plugin meta links
 */

function plugin_row_meta_render($links, $file){
	if ( NIVO_SEARCH_PLUGIN_BASENAME === $file ) {
		$links[] = '<a href="https://nivosearch.com/docs" target="_blank">' . esc_html__( 'Docs', 'nivo-ajax-search-for-woocommerce' ) . '</a>';
	}
	return $links;
}
add_filter('plugin_row_meta', 'plugin_row_meta_render', 10, 2);