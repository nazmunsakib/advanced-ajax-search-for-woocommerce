<?php
/**
 * Asset Enqueue Handler
 *
 * @package NivoSearch
 * @since 1.0.0
 */


namespace NivoSearch;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		$this->version    = NIVO_SEARCH_VERSION;
		$this->plugin_url = NIVO_SEARCH_PLUGIN_URL;

		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue on pages that need it
		if ( ! $this->should_enqueue_assets() ) {
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
	public function enqueue_admin_assets( $hook ) {
		// Enqueue on settings page
		if ( 'woocommerce_page_nivo_search-settings' === $hook ) {
			// Admin CSS
			wp_enqueue_style( 'nivo-search-admin', $this->plugin_url . 'assets/css/admin.css', array(), $this->version );

			// WordPress React
			wp_enqueue_script( 'wp-element' );
			wp_enqueue_script( 'wp-components' );
			wp_enqueue_script( 'wp-api-fetch' );

			// Admin React app
			wp_enqueue_script(
				'nivo-search-admin-react',
				$this->plugin_url . 'assets/js/admin-react.js',
				array( 'wp-element', 'wp-components', 'wp-api-fetch' ),
				$this->version,
				true
			);

			// Localize script data
			wp_localize_script(
				'nivo-search-admin-react',
				'nivoSearchAdmin',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'nivo_search_admin_nonce' ),
					'rest_url'   => rest_url( 'wp/v2/' ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'strings'    => array(
						'title'  => __( 'Nivo AJAX Search Settings', 'nivo-ajax-search-for-woocommerce' ),
						'save'   => __( 'Save Settings', 'nivo-ajax-search-for-woocommerce' ),
						'saving' => __( 'Saving...', 'nivo-ajax-search-for-woocommerce' ),
						'saved'  => __( 'Settings saved successfully!', 'nivo-ajax-search-for-woocommerce' ),
					),
				)
			);
		}

		// Allow filtering of admin pages where assets should load
		$allowed_pages = apply_filters( 'nivo_search_admin_asset_pages', array( 'woocommerce_page_nivo_search-settings' ) );

		if ( ! empty( $allowed_pages ) && in_array( $hook, $allowed_pages, true ) ) {
			do_action( 'nivo_search_enqueue_admin_assets', $hook );
		}
	}

	/**
	 * Enqueue JavaScript files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function enqueue_scripts() {
		// Main search script (no jQuery dependency)
		$script_deps    = apply_filters( 'nivo_search_script_dependencies', array() );
		$script_version = apply_filters( 'nivo_search_script_version', $this->version );

		wp_enqueue_script(
			'nivo-search',
			$this->plugin_url . 'assets/js/nivo-search.js',
			$script_deps,
			$script_version,
			true
		);

		// Allow additional scripts
		do_action( 'nivo_search_after_enqueue_scripts' );
	}

	/**
	 * Enqueue CSS files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function enqueue_styles() {
		$style_deps    = apply_filters( 'nivo_search_style_dependencies', array() );
		$style_version = apply_filters( 'nivo_search_style_version', $this->version );

		wp_enqueue_style(
			'nivo-search',
			$this->plugin_url . 'assets/css/nivo-search.css',
			$style_deps,
			$style_version
		);

		// Add inline styles for customization
		$custom_css = $this->generate_custom_css();
		wp_add_inline_style( 'nivo-search', $custom_css );

		// Allow additional styles
		do_action( 'nivo_search_after_enqueue_styles' );
	}

	/**
	 * Localize scripts with data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_scripts() {
		$wc_ajax_url = '';
		if ( class_exists( 'WC_AJAX' ) ) {
			$wc_ajax_url = \WC_AJAX::get_endpoint( 'nivo_search' );
		}

		$localize_data = apply_filters(
			'nivo_search_localize_data',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'wc_ajax_url' => $wc_ajax_url,
				'nonce'       => wp_create_nonce( 'nivo_search_nonce' ),
				'strings'     => array(
					'no_results' => esc_html__( 'No products found', 'nivo-ajax-search-for-woocommerce' ),
					'loading'    => esc_html__( 'Loading...', 'nivo-ajax-search-for-woocommerce' ),
					'error'      => esc_html__( 'Search error occurred', 'nivo-ajax-search-for-woocommerce' ),
					'view_all'   => esc_html__( 'View All Results', 'nivo-ajax-search-for-woocommerce' ),
				),
				'settings' => array(
					'min_length'       => (int) get_option( 'nivo_search_min_chars', 3 ),
					'delay'            => (int) get_option( 'nivo_search_delay', 300 ),
					'max_results'      => (int) get_option( 'nivo_search_limit', 10 ),
					'show_images'      => (int) get_option( 'nivo_search_show_images', 1 ),
					'show_price'       => (int) get_option( 'nivo_search_show_price', 1 ),
					'show_sku'         => (int) get_option( 'nivo_search_show_sku', 0 ),
					'show_description' => (int) get_option( 'nivo_search_show_description', 0 ),
					'show_add_to_cart' => (int) get_option( 'nivo_search_show_add_to_cart', 0 ),
					'border_width' => get_option( 'nivo_search_border_width', 1 ),
					'border_color' => get_option( 'nivo_search_border_color', '#dfdfdf' ),
					'border_radius' => get_option( 'nivo_search_border_radius', 30 ),
					'bg_color' => get_option( 'nivo_search_bg_color', '#dfdfdf' ),
					'results_border_width' => get_option( 'nivo_search_results_border_width', 1 ),
					'results_border_color' => get_option( 'nivo_search_results_border_color', '#ddd' ),
					'results_border_radius' => get_option( 'nivo_search_results_border_radius', 4 ),
					'results_bg_color' => get_option( 'nivo_search_results_bg_color', '#ffffff' ),
					'results_padding' => get_option( 'nivo_search_results_padding', 5 ),
				),
			)
		);

		wp_localize_script( 'nivo-search', 'nivo_search', $localize_data );
	}

	/**
	 * Generate custom CSS based on settings
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function generate_custom_css() {
		$css = '';
		
		// Search bar styles.
		$center_align = get_option( 'nivo_search_center_align', 0 );
		$css .= sprintf(
			'.nivo-search-product-search { border: %dpx solid %s !important; border-radius: %dpx !important; background-color: %s !important; padding: %dpx 45px !important; }',
			get_option( 'nivo_search_border_width', 1 ),
			get_option( 'nivo_search_border_color', '#dfdfdf' ),
			get_option( 'nivo_search_border_radius', 30 ),
			get_option( 'nivo_search_bg_color', '#dfdfdf' ),
			get_option( 'nivo_search_padding_vertical', 15 )
		);
		
		$css .= '.nivo-search-product-search:focus { background-color: #ffffff !important; border-color: #666666 !important; }';
		
		if ( $center_align ) {
			$css .= '.nivo-ajax-search-container { margin-left: auto !important; margin-right: auto !important; }';
		}
		
		// Theme inheritance overrides.
		$custom_font_family = get_option( 'nivo_search_font_family', '' );
		$custom_text_color  = get_option( 'nivo_search_text_color', '' );
		$custom_hover_color = get_option( 'nivo_search_hover_color', '' );
		$custom_hover_bg    = get_option( 'nivo_search_hover_bg', '' );
		
		if ( ! empty( $custom_font_family ) || ! empty( $custom_text_color ) || ! empty( $custom_hover_color ) || ! empty( $custom_hover_bg ) ) {
			$css .= ':root {';
			
			if ( ! empty( $custom_font_family ) ) {
				$css .= '--nivo-search-font-family: ' . esc_attr( $custom_font_family ) . ';';
			}
			
			if ( ! empty( $custom_text_color ) ) {
				$css .= '--nivo-search-text-color: ' . esc_attr( $custom_text_color ) . ';';
			}
			
			if ( ! empty( $custom_hover_color ) ) {
				$css .= '--nivo-search-hover-color: ' . esc_attr( $custom_hover_color ) . ';';
			}
			
			if ( ! empty( $custom_hover_bg ) ) {
				$css .= '--nivo-search-hover-bg: ' . esc_attr( $custom_hover_bg ) . ';';
			}
			
			$css .= '}';
		}
		
		return $css;
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Enqueue frontend styles in block editor
		$this->enqueue_styles();
		$this->enqueue_scripts();
		$this->localize_scripts();
	}

	/**
	 * Check if assets should be enqueued
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function should_enqueue_assets() {
		// Always enqueue for now - can be optimized later.
		$should_enqueue = true;

		// Allow filtering.
		return apply_filters( 'nivo_search_should_enqueue_assets', $should_enqueue );
	}
}