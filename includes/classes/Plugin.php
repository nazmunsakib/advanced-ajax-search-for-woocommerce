<?php
/**
 * Main Plugin Class
 *
 * @package AASFWC
 * @since 1.0.0
 */

namespace AASFWC;

defined( 'ABSPATH' ) || exit;

/**
 * Main Plugin Class
 *
 * Handles the core functionality of the Advanced AJAX Search plugin.
 * Uses singleton pattern for scalability and extensibility.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = AASFWC_VERSION;

	/**
	 * Enqueue handler
	 *
	 * @since 1.0.0
	 * @var Enqueue
	 */
	public $enqueue;

	/**
	 * Search algorithm handler
	 *
	 * @since 1.0.0
	 * @var Search_Algorithm
	 */
	public $search_algorithm;

	/**
	 * Admin settings handler
	 *
	 * @since 1.0.0
	 * @var Admin_Settings
	 */
	public $admin_settings;

	/**
	 * Gutenberg block handler
	 *
	 * @since 1.0.0
	 * @var Gutenberg_Block
	 */
	public $gutenberg_block;

	/**
	 * Shortcode handler
	 *
	 * @since 1.0.0
	 * @var Shortcode
	 */
	public $shortcode;

	/**
	 * Get plugin instance (Singleton)
	 *
	 * @since 1.0.0
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init_hooks();
		$this->init_components();
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_ajax_aasfwc_ajax_search', array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_nopriv_aasfwc_ajax_search', array( $this, 'handle_search' ) );
		add_action( 'wc_ajax_aasfwc_ajax_search', array( $this, 'handle_search' ) );

		// Allow other plugins to hook into our initialization
		do_action( 'aasfwc_plugin_loaded', $this );
	}

	/**
	 * Initialize plugin components
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_components() {
		$this->enqueue          = new Enqueue();
		$this->search_algorithm = new Search_Algorithm();

		// Initialize admin components
		if ( is_admin() ) {
			$this->admin_settings = new Admin_Settings();
		}

		// Initialize Gutenberg block
		$this->gutenberg_block = new Gutenberg_Block();

		// Initialize shortcode
		$this->shortcode = new Shortcode();

		// Allow other plugins to add components
		do_action( 'aasfwc_components_loaded', $this );
	}

	/**
	 * Handle AJAX search request
	 *
	 * Processes the live product search and returns JSON response.
	 * Uses advanced search algorithm with AI capabilities.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_search() {
		// Verify nonce for security (skip for wc-ajax)
		if ( ! isset( $_GET['wc-ajax'] ) ) {
			check_ajax_referer( 'aasfwc_search_nonce', 'nonce' );
		}

		$query = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : ( isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '' );

		// Check if AJAX search is enabled
		if ( ! get_option( 'aasfwc_enable_ajax', 1 ) ) {
			wp_send_json_error( array( 'message' => __( 'AJAX search is disabled', 'advanced-ajax-search-for-woocommerce' ) ) );
		}

		// Validate minimum query length
		$min_length = get_option( 'aasfwc_min_chars', 2 );
		if ( strlen( $query ) < $min_length ) {
			wp_send_json_error( array( 'message' => __( 'Query too short', 'advanced-ajax-search-for-woocommerce' ) ) );
		}

		// Get search parameters
		$search_args = apply_filters(
			'aasfwc_search_args',
			array(
				'limit'   => get_option( 'aasfwc_search_limit', 10 ),
				'exclude' => $this->get_excluded_products(),
			),
			$query
		);

		// Use advanced search algorithm
		$products = $this->search_algorithm->search( $query, $search_args );

		// Format results
		$results = array();
		foreach ( $products as $product ) {
			$result    = $this->format_search_result( $product, $query );
			$results[] = apply_filters( 'aasfwc_search_result_item', $result, $product, $query );
		}

		// Send results directly for JavaScript compatibility
		wp_send_json_success( apply_filters( 'aasfwc_search_results', $results, $query ) );
	}

	/**
	 * Format individual search result
	 *
	 * @since 1.0.0
	 * @param WC_Product $product Product object
	 * @param string     $query Search query
	 * @return array Formatted result
	 */
	private function format_search_result( $product, $query ) {
		// Always return all data, let frontend handle display
		$result = array(
			'id'                => $product->get_id(),
			'title'             => $product->get_name(),
			'url'               => $product->get_permalink(),
			'image'             => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
			'price'             => $product->get_price_html(),
			'sku'               => $product->get_sku(),
			'short_description' => wp_trim_words( $product->get_short_description(), 15 ),
		);

		return $result;
	}

	/**
	 * Get excluded products from settings
	 *
	 * @since 1.0.0
	 * @return array Excluded product IDs
	 */
	private function get_excluded_products() {
		$excluded = get_option( 'aasfwc_excluded_products', '' );
		if ( empty( $excluded ) ) {
			return array();
		}

		return array_map( 'intval', explode( ',', $excluded ) );
	}

	/**
	 * Get "View All Results" URL
	 *
	 * @since 1.0.0
	 * @param string $query Search query
	 * @return string Search results page URL
	 */
	private function get_view_all_url( $query ) {
		return add_query_arg( 's', urlencode( $query ), wc_get_page_permalink( 'shop' ) );
	}

	/**
	 * Get plugin version
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Prevent cloning
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {}
}