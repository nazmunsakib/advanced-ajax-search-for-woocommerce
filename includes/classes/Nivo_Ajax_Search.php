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
	 * Product index manager
	 *
	 * @since 2.0.2
	 * @var Product_Indexer
	 */
	public $product_indexer;

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

		// Invalidate search result cache whenever products or presets change.
		add_action( 'save_post_product',                     array( __CLASS__, 'invalidate_search_cache' ) );
		add_action( 'deleted_post',                          array( __CLASS__, 'invalidate_search_cache' ) );
		add_action( 'woocommerce_product_set_stock_status',  array( __CLASS__, 'invalidate_search_cache' ) );
		add_action( 'save_post_nivo_search_preset',          array( __CLASS__, 'invalidate_search_cache' ) );

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

		// Boot the product index manager (hooks into save_post_product etc.).
		$this->product_indexer = new Product_Indexer();
		$this->product_indexer->init();

		// Initialize admin components
		if ( is_admin() ) {
			$this->admin_settings       = new Admin_Settings();
			$this->search_optimization  = new Search_Optimization();
		}

		// Search results page + seamless theme integration (front-end only).
		if ( ! is_admin() ) {
			new Search_Results_Page();
			new Theme_Integration();
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
		// Verify nonce on every request path — the JS sends it via both
		// admin-ajax.php and the wc-ajax endpoint (set in wp_localize_script).
		check_ajax_referer( 'nivo_search_nonce', 'nonce' );

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

			// Fill any keys missing from the DB (e.g. keys added in a new release
			// before migration ran, or presets saved before a new toggle was added).
			$preset_settings = wp_parse_args( $preset_settings, Helper::get_default_settings() );
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
				'limit'                     => $limit,
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'exclude'                   => $this->get_excluded_products(),
				'search_fields'             => $this->get_search_fields( $preset_settings ),
				'exclude_out_of_stock'      => $exclude_out_of_stock,
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
		
		// Add products — batch-load to avoid N+1 queries (Phase 4.1).
		$raw_products        = isset( $search_results['products'] ) ? $search_results['products'] : $search_results;
		$results['products'] = array();

		if ( ! empty( $raw_products ) ) {
			// Collect ordered post IDs preserving relevance sort from Search_Algorithm.
			$ordered_ids = array_map(
				static function ( $post ) {
					return is_object( $post ) ? (int) $post->ID : (int) $post;
				},
				$raw_products
			);

			// Single batch query — one DB round-trip for all products.
			$wc_products = wc_get_products(
				array(
					'include'  => $ordered_ids,
					'limit'    => count( $ordered_ids ),
					'status'   => 'publish',
					'orderby'  => 'include', // preserve relevance order
					'return'   => 'objects',
				)
			);

			// Index by ID for O(1) lookup while re-applying original sort order.
			$product_map = array();
			foreach ( $wc_products as $wc_product ) {
				$product_map[ $wc_product->get_id() ] = $wc_product;
			}

			foreach ( $ordered_ids as $pid ) {
				if ( ! isset( $product_map[ $pid ] ) ) {
					continue;
				}
				$product           = $product_map[ $pid ];
				$result            = $this->format_search_result( $product, $query );
				$results['products'][] = apply_filters( 'nivo_search_result_item', $result, $product, $query );
			}
		}


		// Send results directly for JavaScript compatibility
		$response_data = apply_filters( 'nivo_search_results', $results, $query );

		// Pass "did you mean" suggestion to the frontend when present.
		// Priority 1: fuzzy-search suggestion (fires when WP_Query returns 0 results).
		if ( ! empty( $search_results['did_you_mean'] ) && get_option( 'nivo_search_show_did_you_mean', 1 ) ) {
			$response_data['did_you_mean'] = sanitize_text_field( $search_results['did_you_mean'] );
		}

		// Priority 2: dictionary correction — show even when results were found,
		// so users learn the corrected spelling (e.g. "tshirt" → "t-shirt").
		if ( empty( $response_data['did_you_mean'] )
			&& ! empty( $search_results['corrected_to'] )
			&& get_option( 'nivo_search_show_did_you_mean', 1 ) ) {
			$response_data['did_you_mean'] = sanitize_text_field( $search_results['corrected_to'] );
		}

		// Log analytics whenever the dictionary corrected the query.
		if ( ! empty( $search_results['corrected_from'] ) && ! empty( $search_results['corrected_to'] ) ) {
			Search_Analytics::log_correction(
				$search_results['corrected_from'],
				$search_results['corrected_to']
			);
		}

		// Add sanitized display settings to response so the JS can adapt
		// rendering (e.g. show/hide add-to-cart, qty selector, view-all).
		// Only scalar, non-sensitive keys are forwarded — never raw meta arrays.
		if ( ! empty( $preset_settings ) ) {
			$response_data['settings'] = $this->sanitize_settings_for_response( $preset_settings );
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
		// Resolve variable products to their cheapest child for add-to-cart purposes.
		$purchasable_id = $product->get_id();
		$product_type   = $product->get_type();

		// Categories (first 2, to keep payload small).
		$categories = array();
		$cat_terms  = get_the_terms( $product->get_id(), 'product_cat' );
		if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
			foreach ( array_slice( $cat_terms, 0, 2 ) as $term ) {
				$categories[] = array(
					'name' => $term->name,
					'url'  => get_term_link( $term ),
				);
			}
		}

		// Stock.
		$stock_status   = $product->get_stock_status();   // 'instock' | 'outofstock' | 'onbackorder'
		$stock_quantity = $product->get_stock_quantity();  // int|null
		$is_in_stock    = $product->is_in_stock();


		// Add-to-cart data (only for simple/external; variable handled on product page).
		$is_purchasable   = $product->is_purchasable() && $is_in_stock;
		$add_to_cart_url  = $product->add_to_cart_url();
		$add_to_cart_text = $product->add_to_cart_text();

		$result = array(
			// Core
			'id'                => $product->get_id(),
			'title'             => $product->get_name(),
			'url'               => $product->get_permalink(),
			'image'             => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
			'price'             => $product->get_price_html(),
			'current_price'     => wc_price( (float) $product->get_price() ),
			'sku'               => $product->get_sku(),
			'short_description' => wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 15 ),
			'product_type'      => $product_type,
			// Stock
			'stock_status'      => $stock_status,
			'stock_quantity'    => $stock_quantity,
			'is_in_stock'       => $is_in_stock,
			// Categories
			'categories'        => $categories,
			// Add-to-cart
			'is_purchasable'    => $is_purchasable,
			'add_to_cart_url'   => esc_url( $add_to_cart_url ),
			'add_to_cart_text'  => esc_html( $add_to_cart_text ),
			'add_to_cart_nonce' => wp_create_nonce( 'add-to-cart' ),
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
	 * Sanitize preset settings array for inclusion in the AJAX JSON response.
	 *
	 * Only scalar display/behaviour flags are forwarded to the client.
	 * Style values (colors, widths) and internal query flags are excluded.
	 * All string values are run through sanitize_text_field() to strip tags.
	 *
	 * @since 2.0.2
	 * @param array $settings Raw merged preset settings.
	 * @return array Safe, client-facing subset of settings.
	 */
	private function sanitize_settings_for_response( array $settings ) {
		// Keys that the frontend JS actually reads from the settings object.
		$allowed_keys = array(
			'limit', 'min_chars', 'delay', 'placeholder',
			'show_images', 'show_price', 'show_sku', 'show_description',
			'show_stock_status', 'show_category_badge', 'show_add_to_cart',
			'show_qty_selector', 'show_view_all', 'show_ratings',
		);

		$safe = array();
		foreach ( $allowed_keys as $key ) {
			if ( ! isset( $settings[ $key ] ) ) {
				continue;
			}
			$val = $settings[ $key ];
			if ( is_string( $val ) ) {
				$safe[ $key ] = sanitize_text_field( $val );
			} elseif ( is_int( $val ) || is_float( $val ) ) {
				$safe[ $key ] = $val;
			} else {
				$safe[ $key ] = absint( $val );
			}
		}

		return $safe;
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