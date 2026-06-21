<?php
/**
 * Main Plugin Class
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Main Nivo_Ajax_Search Class
 *
 * Handles the core functionality of the Nivo AJAX Search plugin.
 * Uses singleton pattern for scalability and extensibility.
 *
 * @since 1.0.0
 */
final class Nivo_Ajax_Search {

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Nivo_Ajax_Search|null
	 */
	private static $instance = null;

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $version = NIVO_SEARCH_VERSION;

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
	 * Search Preset CPT handler
	 *
	 * @since 1.1.0
	 * @var Search_Preset_CPT
	 */
	public $preset_cpt;

	/**
	 * Get plugin instance (Singleton)
	 *
	 * @since 1.0.0
	 * @return Nivo_Ajax_Search
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
		add_action( 'wp_ajax_nivo_search',        array( $this, 'handle_search' ) );
		add_action( 'wp_ajax_nopriv_nivo_search', array( $this, 'handle_search' ) );
		add_action( 'wc_ajax_nivo_search',        array( $this, 'handle_search' ) );

		// Invalidate search result cache whenever products change.
		add_action( 'save_post_product',                     array( __CLASS__, 'invalidate_search_cache' ) );
		add_action( 'deleted_post',                          array( __CLASS__, 'invalidate_search_cache' ) );
		add_action( 'woocommerce_product_set_stock_status',  array( __CLASS__, 'invalidate_search_cache' ) );

		// Allow other plugins to hook into our initialization.
		do_action( 'nivo_search_plugin_loaded', $this );
	}

	/**
	 * Bump the search cache version to invalidate all cached results.
	 *
	 * Uses a version number stored in options rather than deleting individual
	 * transients, which avoids expensive prefix-based DB deletes.
	 * Old transients expire naturally at their TTL.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function invalidate_search_cache() {
		$ver = (int) get_option( 'nivo_search_cache_ver', 1 );
		update_option( 'nivo_search_cache_ver', $ver + 1, false );
	}

	/**
	 * Return the current search cache version.
	 *
	 * @since 1.2.0
	 * @return int
	 */
	private function get_cache_version() {
		return (int) get_option( 'nivo_search_cache_ver', 1 );
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

		// Initialize preset CPT
		$this->preset_cpt = new Search_Preset_CPT();

		// Allow other plugins to add components
		do_action( 'nivo_search_components_loaded', $this );
	}

	/**
	 * Handle AJAX search request
	 *
	 * Processes the live product search and returns JSON response.
	 * Uses nivo search algorithm with AI capabilities.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_search() {
		// Verify nonce for security (skip for wc-ajax)
		if ( ! isset( $_GET['wc-ajax'] ) ) {
			check_ajax_referer( 'nivo_search_nonce', 'nonce' );
		}

		$query = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : ( isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '' );
		$preset_id = isset( $_POST['preset_id'] ) ? absint( $_POST['preset_id'] ) : 0;

		// Get preset settings if available
		$preset_settings = [];
		if ( $preset_id && get_post_type( $preset_id ) === 'nivo_search_preset' ) {
			$generale_settings = get_post_meta( $preset_id, '_nivo_search_generale', true ) ?: [];
			$query_settings    = get_post_meta( $preset_id, '_nivo_search_query', true ) ?: [];
			$display_settings  = get_post_meta( $preset_id, '_nivo_search_display', true ) ?: [];
			$style_settings    = get_post_meta( $preset_id, '_nivo_search_style', true ) ?: [];
			
			$preset_settings = array_merge( $generale_settings, $query_settings, $display_settings, $style_settings );
		}

		// Check if AJAX search is enabled
		if ( ! get_option( 'nivo_search_enable_ajax', 1 ) ) {
			wp_send_json_error( array( 'message' => __( 'AJAX search is disabled', 'nivo-ajax-search-for-woocommerce' ) ) );
		}

		// Validate minimum query length
		$min_length = ! empty( $preset_settings['min_chars'] ) ? absint( $preset_settings['min_chars'] ) : 2;
		if ( strlen( $query ) < $min_length ) {
			wp_send_json_error( array( 'message' => __( 'Query too short', 'nivo-ajax-search-for-woocommerce' ) ) );
		}

		// Prepare search arguments
		$limit = ! empty( $preset_settings['limit'] ) ? absint( $preset_settings['limit'] ) : 10;
		$exclude_out_of_stock = ! empty( $preset_settings['exclude_out_of_stock'] ) ? 1 : 0;
		
		$search_args = apply_filters(
			'nivo_search_args',
			array(
				'limit'                => $limit,
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'exclude'              => $this->get_excluded_products(),
				'search_fields'        => $this->get_search_fields( $preset_settings ),
				'exclude_out_of_stock' => $exclude_out_of_stock,
				'search_product_categories' => ! empty( $preset_settings['search_product_categories'] ) ? 1 : 0,
				'search_product_tags'       => ! empty( $preset_settings['search_product_tags'] ) ? 1 : 0,
			),
			$query
		);

		// --- Transient cache check -------------------------------------------
		$cache_ver = $this->get_cache_version();
		$cache_key = 'nivo_s_' . substr( md5( $query . '|' . $preset_id . '|' . $cache_ver ), 0, 24 );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			wp_send_json_success( $cached );
		}
		// --- /Transient cache check ------------------------------------------

		// Use nivo search algorithm
		$search_results = $this->search_algorithm->search( $query, $search_args );

		// Format results
		$results = array();
		
		// Add categories if present
		if ( isset( $search_results['categories'] ) && ! empty( $search_results['categories'] ) ) {
			$results['categories'] = array();
			foreach ( $search_results['categories'] as $category ) {
				$results['categories'][] = $this->format_category_result( $category, $query );
			}
		}

		// Add tags if present
		if ( isset( $search_results['tags'] ) && ! empty( $search_results['tags'] ) ) {
			$results['tags'] = array();
			foreach ( $search_results['tags'] as $tag ) {
				$results['tags'][] = $this->format_tag_result( $tag, $query );
			}
		}
		
		// Add products
		$products = isset( $search_results['products'] ) ? $search_results['products'] : $search_results;
		$results['products'] = array();
		foreach ( $products as $post ) {
			$product = wc_get_product( $post );
			if ( ! $product ) {
				continue;
			}
			$result = $this->format_search_result( $product, $query );
			$results['products'][] = apply_filters( 'nivo_search_result_item', $result, $product, $query );
		}


		// Send results directly for JavaScript compatibility
		$response_data = apply_filters( 'nivo_search_results', $results, $query );
		
		// Add settings to response if using a preset
		if ( ! empty( $preset_settings ) ) {
			$response_data['settings'] = $preset_settings;
		}

		// Store in transient cache (5 minutes TTL).
		set_transient( $cache_key, $response_data, 5 * MINUTE_IN_SECONDS );

		wp_send_json_success( $response_data );
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
	 * Get search fields from settings
	 *
	 * @since 1.0.0
	 * @param array $preset_settings Preset settings
	 * @return array Search fields
	 */
	private function get_search_fields( $preset_settings = [] ) {
		$fields = array();

		if ( ! empty( $preset_settings ) ) {
			// Use preset settings
			if ( ! empty( $preset_settings['search_in_title'] ) ) {
				$fields[] = 'title';
			}
			if ( ! empty( $preset_settings['search_in_content'] ) ) {
				$fields[] = 'content';
			}
			if ( ! empty( $preset_settings['search_in_excerpt'] ) ) {
				$fields[] = 'excerpt';
			}
			if ( ! empty( $preset_settings['search_in_sku'] ) ) {
				$fields[] = 'sku';
			}
		}

		// Fallback to title if no fields selected
		if ( empty( $fields ) ) {
			$fields[] = 'title';
		}

		return $fields;
	}

	/**
	 * Get excluded products from settings
	 *
	 * @since 1.0.0
	 * @return array Excluded product IDs
	 */
	private function get_excluded_products() {
		$excluded = get_option( 'nivo_search_excluded_products', '' );
		if ( empty( $excluded ) ) {
			return array();
		}

		return array_map( 'intval', explode( ',', $excluded ) );
	}

	/**
	 * Format category search result
	 *
	 * @since 1.0.0
	 * @param WP_Term $category Category term
	 * @param string  $query Search query
	 * @return array Formatted category result
	 */
	private function format_category_result( $category, $query ) {
		return array(
			'id'    => $category->term_id,
			'title' => $category->name,
			'url'   => get_term_link( $category ),
			'count' => $category->count,
		);
	}

    /**
     * Format tag result
     *
     * @since 1.0.0
     * @param WP_Term $tag Tag object
     * @param string $query Search query
     * @return array Formatted result
     */
    private function format_tag_result( $tag, $query ) {
        return array(
            'id'    => $tag->term_id,
            'title' => $tag->name,
            'url'   => get_term_link( $tag ),
            'type'  => 'tag',
            'count' => $tag->count,
        );
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