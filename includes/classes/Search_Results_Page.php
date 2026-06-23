<?php
/**
 * Search Results Page Integration
 *
 * When the "Show products on search results page" setting is ON, any WordPress
 * search (pressing Enter, submitting a search form) is redirected to the
 * WooCommerce shop page with the query string — giving customers a proper
 * WooCommerce product grid with pagination, filters, and sorting.
 *
 * Why redirect instead of pre_get_posts: WP's own search template doesn't know
 * how to render WooCommerce product cards (price, image, add-to-cart). The shop
 * page template does. Redirecting to /shop/?s=term is the right approach and is
 * what Advanced Woo Search and FiboSearch do.
 *
 * When WooCommerce is NOT active or has no shop page, the class falls back to
 * a pre_get_posts filter that shows only products on the WP search page.
 *
 * @package NivoSearch
 * @since   2.3.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Search_Results_Page class.
 *
 * @since 2.0.2
 */
class Search_Results_Page {

	/**
	 * Constructor — registers hooks only when the feature is enabled.
	 *
	 * @since 2.0.2
	 */
	public function __construct() {
		// Skip entirely during AJAX requests — redirects must never fire during AJAX.
		// Covers both WP AJAX (admin-ajax.php) and WC AJAX (?wc-ajax=).
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! empty( $_GET['wc-ajax'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( 'yes' !== get_option( 'nivo_search_results_page', 'yes' ) ) {
			return;
		}

		// Primary: redirect /?s=term → /shop/?s=term on the `wp` hook (after
		// conditional tags like is_search() are available but before output).
		add_action( 'wp', array( $this, 'redirect_search_to_shop' ) );

		// Fallback: if WC / shop page is unavailable, modify the query so at
		// least only products appear on the WP search results page.
		add_action( 'pre_get_posts', array( $this, 'filter_search_query_fallback' ), 5 );
	}

	/**
	 * Redirect WordPress search to the WooCommerce shop page.
	 *
	 * Fires on the `wp` action, when conditional tags (is_search, is_admin)
	 * are fully resolved. Sends a 302 redirect so customers land on the shop
	 * page which uses WooCommerce's own product grid template.
	 *
	 * Only fires when:
	 *   - This is a front-end search request (is_search() = true).
	 *   - We are NOT already on the shop/product page (avoids infinite loops).
	 *   - WooCommerce is active and has a shop page configured.
	 *
	 * @since 2.0.2
	 * @return void
	 */
	public function redirect_search_to_shop() {
		// Must be a front-end search page.
		if ( ! is_search() || is_admin() ) {
			return;
		}

		// Skip if already on a WooCommerce page to prevent redirect loops.
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			return;
		}

		// Need WooCommerce + a configured shop page.
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return; // Fallback pre_get_posts hook will handle this.
		}

		$shop_url = wc_get_page_permalink( 'shop' );
		if ( ! $shop_url || false === $shop_url ) {
			return;
		}

		$search_term = get_search_query();
		if ( '' === $search_term ) {
			return;
		}

		/**
		 * Filters the redirect URL used for search results.
		 *
		 * Return an empty string to cancel the redirect.
		 *
		 * @since 2.0.2
		 * @param string $redirect_url  URL to redirect to.
		 * @param string $search_term   The search query.
		 * @param string $shop_url      The WooCommerce shop page URL.
		 */
		$redirect_url = apply_filters(
			'nivo_search_results_redirect_url',
			add_query_arg( 's', rawurlencode( $search_term ), trailingslashit( $shop_url ) ),
			$search_term,
			$shop_url
		);

		if ( empty( $redirect_url ) ) {
			return;
		}

		wp_safe_redirect( esc_url_raw( $redirect_url ), 302 );
		exit;
	}

	/**
	 * Fallback: modify main search query to show products only.
	 *
	 * This only activates when WooCommerce is not available or has no shop page
	 * (so the redirect above never fired). Filters the WP search results page
	 * to show published products only instead of all post types.
	 *
	 * @since 2.0.2
	 * @param \WP_Query $query The current WP_Query instance.
	 * @return void
	 */
	public function filter_search_query_fallback( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}

		// If WC is active with a shop page, the redirect above handles it.
		if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) {
			return;
		}

		/** @see redirect_search_to_shop for the primary path */
		$post_types = apply_filters( 'nivo_search_results_post_types', array( 'product' ) );

		$query->set( 'post_type',   $post_types );
		$query->set( 'post_status', 'publish' );

		do_action( 'nivo_search_results_query', $query );
	}
}
