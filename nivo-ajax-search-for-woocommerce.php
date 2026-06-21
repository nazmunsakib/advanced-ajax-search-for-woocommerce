<?php
/**
 * Plugin Name: NivoSearch – Ajax Search for WooCommerce
 * Plugin URI: https://nivosearch.com
 * Description: The fast, modern WooCommerce product search. Give your customers a beautiful live AJAX search bar with instant product results.
 * Version: 1.2.0
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
define( 'NIVO_SEARCH_VERSION', '1.2.0' );
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
 * Load the Composer autoloader
 *
 * @since 1.2.0
 * @return void
 */
function nivo_search_load_autoloader() {
	if ( file_exists( NIVO_SEARCH_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
		require_once NIVO_SEARCH_PLUGIN_DIR . 'vendor/autoload.php';
	}
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function nivo_search_init() {
	nivo_search_load_autoloader();

	// Run database migrations if version has changed.
	NivoSearch\Migrator::maybe_migrate();

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
 * Registers the CPT inline before inserting the default preset so that
 * wp_insert_post() recognises 'nivo_search_preset' on a fresh install.
 * (The CPT is normally registered on init, which fires after activation.)
 *
 * @since 1.0.0
 * @since 1.2.0 Fixed CPT race condition; added DB version stamp.
 * @return void
 */
function nivo_search_activate() {
	// Load autoloader so we can call class methods.
	nivo_search_load_autoloader();

	// Register the CPT now so wp_insert_post() recognises it.
	nivo_search_register_preset_cpt();

	// Stamp the DB version so the migrator knows this is a fresh install.
	update_option( 'nivo_search_db_version', NIVO_SEARCH_VERSION );

	// Create the default preset only on a truly fresh install.
	$default_preset = get_option( 'nivo_search_default_preset_created' );
	if ( ! $default_preset ) {
		nivo_search_create_default_preset();
	}

	// Flush rewrite rules so CPT slugs resolve immediately.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'nivo_search_activate' );

/**
 * Register the Search Preset CPT (used during activation and on init)
 *
 * Extracted so the activation hook can call it before init fires.
 *
 * @since 1.2.0
 * @return void
 */
function nivo_search_register_preset_cpt() {
	register_post_type(
		'nivo_search_preset',
		array(
			'labels'          => array(
				'name'          => __( 'Search Presets', 'nivo-ajax-search-for-woocommerce' ),
				'singular_name' => __( 'Search Preset', 'nivo-ajax-search-for-woocommerce' ),
				'add_new'       => __( 'Add New Preset', 'nivo-ajax-search-for-woocommerce' ),
				'add_new_item'  => __( 'Add New Search Preset', 'nivo-ajax-search-for-woocommerce' ),
				'edit_item'     => __( 'Edit Search Preset', 'nivo-ajax-search-for-woocommerce' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'nivo-search',
			'show_in_rest'    => true,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'manage_options' ),
			'map_meta_cap'    => true,
		)
	);
}

/**
 * Create the default preset with sensible defaults
 *
 * @since 1.2.0
 * @return void
 */
function nivo_search_create_default_preset() {
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

	update_post_meta( $preset_id, '_nivo_search_generale', array(
		'limit'       => 10,
		'min_chars'   => 2,
		'delay'       => 300,
		'placeholder' => __( 'Search products...', 'nivo-ajax-search-for-woocommerce' ),
	) );

	update_post_meta( $preset_id, '_nivo_search_query', array(
		'search_in_title'           => 1,
		'search_in_sku'             => 1,
		'search_in_content'         => 1,
		'search_in_excerpt'         => 1,
		'search_product_categories' => 1,
		'search_product_tags'       => 0,
		'exclude_out_of_stock'      => 0,
		'search_in_gtin'            => 0,
		'search_in_attributes'      => 0,
	) );

	update_post_meta( $preset_id, '_nivo_search_display', array(
		'show_images'       => 1,
		'show_price'        => 1,
		'show_sku'          => 1,
		'show_description'  => 1,
		'show_ratings'      => 1,
		'show_stock_status' => 1,
		'show_category_badge' => 0,
		'show_qty_selector' => 0,
	) );

	update_post_meta( $preset_id, '_nivo_search_style', array(
		'bar_width'            => 600,
		'bar_height'           => 50,
		'border_color'         => '#dddddd',
		'bg_color'             => '#ffffff',
		'text_color'           => '#333333',
		'results_width'        => 600,
		'results_text_color'   => '#333333',
		'results_border_color' => '#dddddd',
		'results_bg_color'     => '#ffffff',
	) );

	update_option( 'nivo_search_default_preset_created', $preset_id );
}

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @since 1.2.0 Added flush_rewrite_rules() to clean up CPT rewrite slugs.
 * @return void
 */
function nivo_search_deactivate() {
	flush_rewrite_rules();
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