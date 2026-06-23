<?php
/**
 * Shortcode Handler
 *
 * @package NivoSearch
 * @since 1.0.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Shortcode Class
 *
 * Handles all shortcode functionality for the plugin
 *
 * @since 1.0.0
 */
class Shortcode {
    
    /**
     * Store used presets
     *
     * @since 1.0.0
     * @var array
     */
    private static $used_presets = [];

    /**
     * Store used presets style
     *
     * @since 1.0.0
     * @var array
     */
    private static $used_presets_style = [];
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode('nivo_search', [$this, 'render_search_form']);
        add_action('wp_footer', [$this, 'output_preset_styles'], 1);
        add_action('before_delete_post', [$this, 'cleanup_default_preset_option']);
    }

    /**
     * Build the inner search form HTML (input + icons)
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string
     */
    private function get_search_form_html( $atts ) {
        // Build the input class: always include the base class + any custom class.
        $input_classes = 'nivo-search-product-search';
        if ( ! empty( $atts['input_class'] ) && 'nivo-search-product-search' !== $atts['input_class'] ) {
            $input_classes .= ' ' . sanitize_html_class( $atts['input_class'] );
        }

        ob_start();
        ?>
        <input
            type="search"
            class="<?php echo esc_attr( $input_classes ); ?>"
            name="s"
            placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
            aria-label="<?php echo esc_attr( $atts['placeholder'] ); ?>"
            autocomplete="off"
        >
        <div class="nivo-search-loader-icons">
            <svg class="nivo-search-close-icon" xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 24 24" width="18" aria-hidden="true" focusable="false">
                <path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.89c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"></path>
            </svg>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render search form shortcode
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_search_form($atts = []) {
        ob_start();
        
        // Check for preset ID
        $default_preset = Helper::get_default_preset_id();
        $preset_id = isset($atts['id']) ? absint($atts['id']) : $default_preset;

        // Get preset settings using Helper
        $preset_settings = Helper::get_preset_settings($preset_id);
        $preset_style_settings = Helper::get_preset_style_settings($preset_id);

        // Parse shortcode attributes with preset fallback
        $atts = shortcode_atts([
            'id' => 0,
            'placeholder' => $preset_settings['placeholder'] ?? __('Search products...', 'nivo-ajax-search-for-woocommerce'),
            'container_class' => 'nivo-ajax-search-container',
            'input_class' => 'nivo-search-product-search',
            'results_class' => 'nivo-search-results',
            'search_bar_layout' => $preset_settings['search_bar_layout'] ?? 1,
        ], $atts, 'nivo_search');

        $search_icon_html = '<svg class="nivosearch-ico-magnifier" xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 51.539 51.361" width="18">
                                    <path d="M51.539,49.356L37.247,35.065c3.273-3.74,5.272-8.623,5.272-13.983c0-11.742-9.518-21.26-21.26-21.26 S0,9.339,0,21.082s9.518,21.26,21.26,21.26c5.361,0,10.244-1.999,13.983-5.272l14.292,14.292L51.539,49.356z M2.835,21.082 c0-10.176,8.249-18.425,18.425-18.425s18.425,8.249,18.425,18.425S31.436,39.507,21.26,39.507S2.835,31.258,2.835,21.082z"></path>
                                </svg>';
        
        // Get action URL with WooCommerce fallback
        $action_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');

        // Generate unique class for preset
        $preset_class = $preset_id ? ' nivo-preset-' . $preset_id : '';
        
        // Store preset for CSS generation
        if ($preset_id && !empty($preset_settings) && !isset(self::$used_presets[$preset_id])) {
            self::$used_presets[$preset_id] = $preset_settings;
        }

        if ($preset_id && !empty($preset_style_settings) && !isset(self::$used_presets_style[$preset_id])) {
            self::$used_presets_style[$preset_id] = $preset_style_settings;
        }

        $settings_compress = ! empty( $preset_settings ) ? wp_json_encode( $preset_settings ) : '{}';

        // Build container class: base + preset-specific + custom class from shortcode attr.
        $container_classes = 'nivo-ajax-search-container' . $preset_class;
        if ( ! empty( $atts['container_class'] ) && 'nivo-ajax-search-container' !== $atts['container_class'] ) {
            $container_classes .= ' ' . sanitize_html_class( $atts['container_class'] );
        }

        // Build results class: base + custom class from shortcode attr.
        $results_classes = 'nivo-search-results';
        if ( ! empty( $atts['results_class'] ) && 'nivo-search-results' !== $atts['results_class'] ) {
            $results_classes .= ' ' . sanitize_html_class( $atts['results_class'] );
        }

        ?>
        <div
            class="<?php echo esc_attr( $container_classes ); ?>"
            data-preset-id="<?php echo esc_attr( $preset_id ); ?>"
            data-preset-settings="<?php echo esc_attr( $settings_compress ); ?>"
            role="search"
        >
            <div class="nivo-search-form-wrapper">
                <form class="nivo-search-form" method="get" action="<?php echo esc_url( $action_url ); ?>">
                    <div class="nivo-search-wrapper nivo-search-box-style-<?php echo esc_attr( $atts['search_bar_layout'] ); ?>">

                        <?php echo $this->get_search_form_html( $atts ); ?>

                        <button type="submit" class="nivosearch-search-submit" aria-label="<?php esc_attr_e( 'Search', 'nivo-ajax-search-for-woocommerce' ); ?>">
                            <span class="screen-reader-text"><?php esc_html_e( 'Search', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                        </button>

                        <div class="nivo-search-search-icon-wrap" aria-hidden="true">
                            <?php echo $search_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                        </div>

                    </div>
                </form>
            </div>

            <div
                class="<?php echo esc_attr( $results_classes ); ?>"
                aria-live="polite"
                aria-atomic="true"
                role="listbox"
            ></div>
        </div>
        <?php
        $markup = ob_get_clean();
        return apply_filters('nivo_search_shortcode_html', $markup, $atts);
    }
    
    /**
     * Generate CSS for preset
     *
     * @param int $preset_id
     * @param array $settings
     * @return string
     */
    private function generate_preset_css($preset_id, $settings) {
        return Helper::generate_preset_css($preset_id, $settings);
    }
    
    /**
     * Output preset styles in footer.
     *
     * We echo a <style> block directly rather than using wp_add_inline_style()
     * because wp_print_styles() already ran in <head> — styles enqueued during
     * wp_footer will never be printed by WordPress.
     *
     * All CSS values come from sanitized DB fields (sanitize_hex_color / absint)
     * so direct output is safe here.
     *
     * @since 1.0.0
     */
    public function output_preset_styles() {
        if ( empty( self::$used_presets_style ) ) {
            return;
        }

        $css = '';
        foreach ( self::$used_presets_style as $preset_id => $settings ) {
            $css .= $this->generate_preset_css( $preset_id, $settings );
        }

        if ( $css ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS built from sanitized values only
            echo '<style id="nivo-search-preset-styles">' . $css . '</style>' . "\n";
        }
    }
    
    /**
     * Cleanup default preset option when preset is deleted
     *
     * @since 1.0.0
     * @param int $post_id Post ID being deleted
     */
    public function cleanup_default_preset_option($post_id) {
        if (get_post_type($post_id) !== 'nivo_search_preset') {
            return;
        }
        
        $default_preset_id = get_option('nivo_search_default_preset_created');
        
        if ($default_preset_id && absint($default_preset_id) === absint($post_id)) {
            delete_option('nivo_search_default_preset_created');
        }
    }
}