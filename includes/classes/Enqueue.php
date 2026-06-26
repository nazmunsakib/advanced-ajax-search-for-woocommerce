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
        $nivo_admin_pages = array(
            'toplevel_page_nivo-search',
            'nivo-search_page_nivo-search-optimization',
        );
        if ( in_array( $hook, $nivo_admin_pages, true ) ) {
            wp_enqueue_style(
                'nivo-search-admin',
                NIVO_SEARCH_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                NIVO_SEARCH_VERSION
            );
            // jQuery needed for optimization page AJAX (delete rule, clear analytics).
            wp_enqueue_script( 'jquery' );
        }

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            global $post_type;
            if ($post_type === 'nivo_search_preset') {
                // Admin UI styles
                wp_enqueue_style(
                    'nivo-search-admin',
                    NIVO_SEARCH_PLUGIN_URL . 'assets/css/admin.css',
                    array(),
                    NIVO_SEARCH_VERSION
                );
                // Frontend search styles — makes the live preview look exactly like the site
                wp_enqueue_style(
                    'nivo-search-frontend',
                    NIVO_SEARCH_PLUGIN_URL . 'assets/css/nivo-search.css',
                    array(),
                    NIVO_SEARCH_VERSION
                );
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');

				wp_enqueue_script(
					'nivo-search-admin',
					NIVO_SEARCH_PLUGIN_URL . 'assets/js/admin.js',
					array( 'jquery', 'wp-color-picker' ),
					NIVO_SEARCH_VERSION,
					true
				);
				wp_enqueue_script(
					'nivo-search-preview',
					NIVO_SEARCH_PLUGIN_URL . 'assets/js/admin-preview.js',
					array( 'jquery', 'nivo-search-admin' ),
					NIVO_SEARCH_VERSION,
					true
				);
            }
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
				'shop_url'        => function_exists( 'wc_get_page_permalink' ) ? esc_url( wc_get_page_permalink( 'shop' ) ) : home_url( '/' ),
			// WC AJAX template URL — JS replaces %%endpoint%% with the action name.
			'wc_cart_ajax_url' => class_exists( 'WC_AJAX' ) ? esc_url_raw( add_query_arg( 'wc-ajax', '%%endpoint%%', home_url( '/' ) ) ) : '',
				'strings'     => array(
					'no_results'    => esc_html__( 'No products found', 'nivo-ajax-search-for-woocommerce' ),
					'error'         => esc_html__( 'Search error occurred', 'nivo-ajax-search-for-woocommerce' ),
					/* translators: %s is the search query */
					'view_all'      => esc_html__( 'View all results for "%s"', 'nivo-ajax-search-for-woocommerce' ),
					'in_stock'      => esc_html__( 'In Stock', 'nivo-ajax-search-for-woocommerce' ),
					'out_of_stock'  => esc_html__( 'Out of Stock', 'nivo-ajax-search-for-woocommerce' ),
					'on_backorder'  => esc_html__( 'On Backorder', 'nivo-ajax-search-for-woocommerce' ),
					'add_to_cart'   => esc_html__( 'Add to cart', 'nivo-ajax-search-for-woocommerce' ),
					'added_to_cart' => esc_html__( 'Added!', 'nivo-ajax-search-for-woocommerce' ),
					/* translators: %s is the suggested corrected search query */
					'did_you_mean'  => esc_html__( 'Did you mean: %s?', 'nivo-ajax-search-for-woocommerce' ),
				),
				'settings'    => array( // Default settings
					'min_chars' => 2,
					'limit'     => 10,
					'delay'     => 300,
				),
				'ga_tracking' => 'yes' === get_option( 'nivo_search_ga_tracking', 'yes' ) ? 1 : 0,
				// Current language code for WPML / Polylang — passed with every
				// AJAX search request so the server-side language filter works
				// correctly even on admin-ajax.php / wc-ajax endpoints.
				'lang'        => $this->get_current_language(),
			)
		);

		// Use default preset settings if available.
		$default_preset = Helper::get_default_preset_id();
		if ( $default_preset ) {
			$preset_settings = Helper::get_preset_settings( $default_preset );

			if ( is_array( $preset_settings ) ) {
				$localize_data['settings']['min_chars'] = isset( $preset_settings['min_chars'] ) ? absint( $preset_settings['min_chars'] ) : 2;
				$localize_data['settings']['limit']     = isset( $preset_settings['limit'] ) ? absint( $preset_settings['limit'] ) : 10;
				if ( isset( $preset_settings['delay'] ) ) {
					$localize_data['settings']['delay'] = absint( $preset_settings['delay'] );
				}

			}
		}

		wp_localize_script( 'nivo-search', 'nivo_search', $localize_data );
	}

	/**
	 * Detect the current language code for WPML or Polylang.
	 *
	 * Returns the active language slug (e.g. 'en', 'fr', 'de') when either
	 * multilingual plugin is active, or an empty string on monolingual sites.
	 * The value is passed to the frontend via wp_localize_script() and sent
	 * back with every AJAX search request so the server-side language filter
	 * can restrict results to the correct language even on AJAX endpoints.
	 *
	 * @since 2.0.2
	 * @return string Language slug, or '' when no multilingual plugin is active.
	 */
	private function get_current_language() {
		// WPML — constant set on every request.
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ) {
			return (string) ICL_LANGUAGE_CODE;
		}

		// Polylang — function available when the plugin is active.
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'slug' );
			if ( $lang ) {
				return (string) $lang;
			}
		}

		return '';
	}

	/**
	 * Check if assets should be enqueued
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function should_enqueue_assets() {
		return apply_filters( 'nivo_search_should_enqueue_assets', true );
	}
}
