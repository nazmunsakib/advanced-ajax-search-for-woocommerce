<?php
/**
 * Search Preset Custom Post Type
 *
 * @package NivoSearch
 * @since 1.1.0
 */

namespace NivoSearch;

defined('ABSPATH') || exit;

/**
 * Search Preset CPT Class
 *
 * @since 1.1.0
 */
class Search_Preset_CPT {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_nivo_search_preset', [$this, 'save_preset_meta'], 10, 2);
        add_filter('manage_nivo_search_preset_posts_columns', [$this, 'set_columns']);
        add_action('manage_nivo_search_preset_posts_custom_column', [$this, 'render_columns'], 10, 2);
    }

    /**
     * Register custom post type
     *
     * Delegates to the shared nivo_search_register_preset_cpt() function
     * defined in the main plugin file, which is also called during the
     * activation hook to avoid the CPT race condition.
     *
     * @since 1.1.0
     * @since 1.2.0 Delegates to shared nivo_search_register_preset_cpt() function.
     * @return void
     */
    public function register_post_type() {
        if ( function_exists( 'nivo_search_register_preset_cpt' ) ) {
            nivo_search_register_preset_cpt();
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'nivo_search_shortcode',
            __('Shortcode', 'nivo-ajax-search-for-woocommerce'),
            [$this, 'render_shortcode_box'],
            'nivo_search_preset',
            'side',
            'high'
        );

        add_meta_box(
            'nivo_search_settings',
            __('Search Settings', 'nivo-ajax-search-for-woocommerce'),
            [$this, 'render_settings_box'],
            'nivo_search_preset',
            'normal',
            'high'
        );

    }

    /**
     * Render shortcode meta box
     */
    public function render_shortcode_box($post) {
        if ($post->post_status === 'publish') {
            $shortcode = '[nivo_search id="' . $post->ID . '"]';
            ?>
            <div class="nivo-shortcode-box">
                <p style="margin:0 0 10px;font-weight:600;color:#1d2327;"><?php echo esc_html($post->post_title); ?></p>
                <input type="text" readonly value="<?php echo esc_attr($shortcode); ?>" 
                       onclick="this.select();" style="width:100%;padding:8px;font-family:monospace;">
                <button type="button" class="button button-secondary" style="width:100%;margin-top:10px;" 
                        onclick="navigator.clipboard.writeText('<?php echo esc_js($shortcode); ?>');this.innerText='Copied!';setTimeout(()=>this.innerText='Copy Shortcode',2000);">
                    <?php esc_html_e( 'Copy Shortcode', 'nivo-ajax-search-for-woocommerce' ); ?>
                </button>
            </div>
            <?php
        } else {
            echo '<p>' . esc_html__( 'Publish to generate shortcode', 'nivo-ajax-search-for-woocommerce' ) . '</p>';
        }
    }

    /**
     * Render settings meta box
     */

    public function render_settings_box( $post ) {
        wp_nonce_field( 'nivo_preset_meta', 'nivo_preset_nonce' );

        $generale_settings = get_post_meta( $post->ID, '_nivo_search_generale', true ) ?: array();
        $query_settings    = get_post_meta( $post->ID, '_nivo_search_query',    true ) ?: array();
        $display_settings  = get_post_meta( $post->ID, '_nivo_search_display',  true ) ?: array();
        $style_settings    = get_post_meta( $post->ID, '_nivo_search_style',    true ) ?: array();

        $settings = wp_parse_args(
            array_merge( $generale_settings, $query_settings, $display_settings, $style_settings ),
            Helper::get_default_settings()
        );

        // Pass current state to JS for live preview initialisation.
        $preview_state = array(
            'bar_height'          => (int) $settings['bar_height'],
            'border_color'        => $settings['border_color'],
            'bg_color'            => $settings['bg_color'],
            'text_color'          => $settings['text_color'],
            'results_border_color'=> $settings['results_border_color'],
            'results_bg_color'    => $settings['results_bg_color'],
            'results_text_color'  => $settings['results_text_color'],
            'show_images'         => ! empty( $settings['show_images'] ),
            'show_price'          => ! empty( $settings['show_price'] ),
            'show_sku'            => ! empty( $settings['show_sku'] ),
            'show_description'    => ! empty( $settings['show_description'] ),
            'show_stock_status'   => ! empty( $settings['show_stock_status'] ),
            'show_category_badge' => ! empty( $settings['show_category_badge'] ),
            'show_add_to_cart'    => ! empty( $settings['show_add_to_cart'] ),
            'show_qty_selector'   => ! empty( $settings['show_qty_selector'] ),
            'show_view_all'       => ! empty( $settings['show_view_all'] ),
        );
        ?>
        <script>window.nivoPresetData = <?php echo wp_json_encode( $preview_state ); ?>;</script>

        <div class="nivo-preset-wrap">
        <div class="nivo-preset-settings">

            <!-- ── Tab Navigation ────────────────────────────────────────── -->
            <div class="nivo-preset-tabs">
                <button type="button" class="nivo-preset-tab nivo-preset-tab--active" data-tab="settings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    <span class="nivo-preset-tab__label">
                        <span><?php esc_html_e( 'Settings', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                        <span class="nivo-preset-tab__sub"><?php esc_html_e( 'Search scope & display', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </span>
                </button>
                <button type="button" class="nivo-preset-tab" data-tab="style">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="nivo-preset-tab__label">
                        <span><?php esc_html_e( 'Style & Layout', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                        <span class="nivo-preset-tab__sub"><?php esc_html_e( 'Colors, sizes & borders', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </span>
                </button>
            </div>

            <!-- ══ Tab Panel: Settings ══════════════════════════════════════ -->
            <div class="nivo-preset-tab-panel" data-panel="settings">

            <!-- ── Section: General ───────────────────────────────────────── -->
            <div class="nivo-preset-section">
                <div class="nivo-preset-section-hd">
                    <span class="nivo-preset-section-hd__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    </span>
                    <div>
                        <strong class="nivo-preset-section-hd__title"><?php esc_html_e( 'General', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-preset-section-hd__sub"><?php esc_html_e( 'Results limit, minimum characters, placeholder and debounce delay.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Results Limit', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-setting-row__desc"><?php esc_html_e( 'Max products shown in the dropdown (1–50).', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[limit]" value="<?php echo esc_attr( $settings['limit'] ); ?>" min="1" max="50" class="nivo-preset-num">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Minimum Characters', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-setting-row__desc"><?php esc_html_e( 'Search fires after this many characters are typed (1–5).', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[min_chars]" value="<?php echo esc_attr( $settings['min_chars'] ); ?>" min="1" max="5" class="nivo-preset-num">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Placeholder Text', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-setting-row__desc"><?php esc_html_e( 'Text shown inside the search input when empty.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" name="nivo_settings[placeholder]" value="<?php echo esc_attr( $settings['placeholder'] ); ?>" class="nivo-preset-text">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Search Delay (ms)', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-setting-row__desc"><?php esc_html_e( 'Debounce wait after typing stops before sending the request. Default: 300.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[delay]" value="<?php echo esc_attr( $settings['delay'] ); ?>" min="0" max="2000" step="50" class="nivo-preset-num">
                    </div>
                </div>
            </div>

            <!-- ── Section: Results Display ───────────────────────────────── -->
            <div class="nivo-preset-section">
                <div class="nivo-preset-section-hd">
                    <span class="nivo-preset-section-hd__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </span>
                    <div>
                        <strong class="nivo-preset-section-hd__title"><?php esc_html_e( 'Results Display', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-preset-section-hd__sub"><?php esc_html_e( 'Choose which product fields appear in the dropdown results.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <?php
                $display_toggles = array(
                    'show_images'       => array( __( 'Product Images',      'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_price'        => array( __( 'Price',               'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_sku'          => array( __( 'SKU',                 'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_description'  => array( __( 'Short Description',   'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_stock_status' => array( __( 'Stock Status Badge',  'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_category_badge' => array( __( 'Category Badge',    'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_add_to_cart'  => array( __( 'Add to Cart Button',  'nivo-ajax-search-for-woocommerce' ), '' ),
                    'show_qty_selector' => array( __( 'Quantity Selector',   'nivo-ajax-search-for-woocommerce' ), __( 'Only visible alongside Add to Cart.', 'nivo-ajax-search-for-woocommerce' ) ),
                    'show_view_all'     => array( __( '"View All Results" Link', 'nivo-ajax-search-for-woocommerce' ), '' ),
                );
                $last_key = array_key_last( $display_toggles );
                foreach ( $display_toggles as $key => $meta ) :
                    $checked = checked( ! empty( $settings[ $key ] ), true, false );
                    $is_last = ( $key === $last_key );
                ?>
                <div class="nivo-setting-row<?php echo $is_last ? ' nivo-setting-row--no-border' : ''; ?>">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php echo esc_html( $meta[0] ); ?></strong>
                        <?php if ( $meta[1] ) : ?>
                        <p class="nivo-setting-row__desc"><?php echo esc_html( $meta[1] ); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="nivo-setting-row__control">
                        <label class="nivo-gs-toggle">
                            <input type="checkbox" name="nivo_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php echo $checked; ?>>
                            <span class="nivo-gs-toggle__slider"></span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Section: Search Configuration ─────────────────────────── -->
            <div class="nivo-preset-section">
                <div class="nivo-preset-section-hd">
                    <span class="nivo-preset-section-hd__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </span>
                    <div>
                        <strong class="nivo-preset-section-hd__title"><?php esc_html_e( 'Search Configuration', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-preset-section-hd__sub"><?php esc_html_e( 'What fields are searched and what content appears in results.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <?php
                $config_toggles = array(
                    'search_in_title'           => array( __( 'Search in Title',                'nivo-ajax-search-for-woocommerce' ), __( 'Scope', 'nivo-ajax-search-for-woocommerce' ) ),
                    'search_in_sku'             => array( __( 'Search in SKU',                  'nivo-ajax-search-for-woocommerce' ), __( 'Scope', 'nivo-ajax-search-for-woocommerce' ) ),
                    'search_in_content'         => array( __( 'Search in Description',          'nivo-ajax-search-for-woocommerce' ), __( 'Scope', 'nivo-ajax-search-for-woocommerce' ) ),
                    'search_in_excerpt'         => array( __( 'Search in Short Description',    'nivo-ajax-search-for-woocommerce' ), __( 'Scope', 'nivo-ajax-search-for-woocommerce' ) ),
                    'exclude_out_of_stock'      => array( __( 'Exclude Out of Stock Products',  'nivo-ajax-search-for-woocommerce' ), __( 'Scope', 'nivo-ajax-search-for-woocommerce' ) ),
                    'search_product_categories' => array( __( 'Show Category Results',          'nivo-ajax-search-for-woocommerce' ), __( 'Results', 'nivo-ajax-search-for-woocommerce' ) ),
                    'search_product_tags'       => array( __( 'Show Tag Results',               'nivo-ajax-search-for-woocommerce' ), __( 'Results', 'nivo-ajax-search-for-woocommerce' ) ),
                );
                $last_key      = array_key_last( $config_toggles );
                $current_group = '';
                foreach ( $config_toggles as $key => $meta ) :
                    $checked  = checked( ! empty( $settings[ $key ] ), true, false );
                    $group    = $meta[1];
                    $is_last  = ( $key === $last_key );
                    if ( $group !== $current_group ) :
                        $current_group = $group;
                ?>
                <p class="nivo-preset-group-label"><?php echo esc_html( $group ); ?></p>
                <?php endif; ?>
                <div class="nivo-setting-row<?php echo $is_last ? ' nivo-setting-row--no-border' : ''; ?>">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php echo esc_html( $meta[0] ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <label class="nivo-gs-toggle">
                            <input type="checkbox" name="nivo_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php echo $checked; ?>>
                            <span class="nivo-gs-toggle__slider"></span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Search Optimization banner ────────────────────────────── -->
            <div class="nivo-preset-opt-banner">
                <div class="nivo-preset-opt-banner__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="nivo-preset-opt-banner__body">
                    <strong><?php esc_html_e( 'Search Optimization', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    <p><?php esc_html_e( 'Fine-tune typo tolerance, fuzzy distance, and "Did you mean?" — or add your own correction rules.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    <div class="nivo-preset-opt-banner__links">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=settings' ) ); ?>"><?php esc_html_e( 'Accuracy Settings', 'nivo-ajax-search-for-woocommerce' ); ?> &rarr;</a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=typo-rules' ) ); ?>"><?php esc_html_e( 'Typo Rules', 'nivo-ajax-search-for-woocommerce' ); ?> &rarr;</a>
                    </div>
                </div>
            </div>

            </div><!-- .nivo-preset-tab-panel[settings] -->

            <!-- ══ Tab Panel: Style & Layout ════════════════════════════════ -->
            <div class="nivo-preset-tab-panel" data-panel="style" hidden>

            <!-- ── Section: Search Bar ───────────────────────────────────── -->
            <div class="nivo-preset-section">
                <div class="nivo-preset-section-hd">
                    <span class="nivo-preset-section-hd__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <div>
                        <strong class="nivo-preset-section-hd__title"><?php esc_html_e( 'Search Bar', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-preset-section-hd__sub"><?php esc_html_e( 'Size and colours of the search input bar.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Height (px)', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[bar_height]" value="<?php echo esc_attr( $settings['bar_height'] ); ?>" min="30" max="100" class="nivo-preset-num">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Width (px)', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[bar_width]" value="<?php echo esc_attr( $settings['bar_width'] ); ?>" min="200" max="1200" class="nivo-preset-num">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Border Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[border_color]" value="<?php echo esc_attr( $settings['border_color'] ); ?>">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Background Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[bg_color]" value="<?php echo esc_attr( $settings['bg_color'] ); ?>">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Text Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[text_color]" value="<?php echo esc_attr( $settings['text_color'] ); ?>">
                    </div>
                </div>
            </div>

            <!-- ── Section: Results Panel Style ─────────────────────────── -->
            <div class="nivo-preset-section">
                <div class="nivo-preset-section-hd">
                    <span class="nivo-preset-section-hd__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="10.5" r="2.5"/><circle cx="8.5" cy="7.5" r="2.5"/><circle cx="6.5" cy="12.5" r="2.5"/><path d="M12 20v-4M8 20v-2M16 20v-2"/></svg>
                    </span>
                    <div>
                        <strong class="nivo-preset-section-hd__title"><?php esc_html_e( 'Results Panel Style', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-preset-section-hd__sub"><?php esc_html_e( 'Width and colours of the dropdown results panel.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Width (px)', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="number" name="nivo_settings[results_width]" value="<?php echo esc_attr( $settings['results_width'] ); ?>" min="200" max="1200" class="nivo-preset-num">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Text Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[results_text_color]" value="<?php echo esc_attr( $settings['results_text_color'] ); ?>">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Border Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[results_border_color]" value="<?php echo esc_attr( $settings['results_border_color'] ); ?>">
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Background Color', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                    </div>
                    <div class="nivo-setting-row__control">
                        <input type="text" class="nivo-color-picker" name="nivo_settings[results_bg_color]" value="<?php echo esc_attr( $settings['results_bg_color'] ); ?>">
                    </div>
                </div>
            </div>

            </div><!-- .nivo-preset-tab-panel[style] -->

        </div><!-- .nivo-preset-settings -->

            <!-- ══ Live Preview Panel ══════════════════════════════════════ -->
            <div class="nivo-preset-preview" id="nivo-live-preview">

                <div class="nivo-pv-header">
                    <span class="nivo-pv-header__label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?php esc_html_e( 'Live Preview', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </span>
                    <div class="nivo-pv-layouts">
                        <button type="button" class="nivo-pv-layout-btn nivo-pv-layout-btn--active" data-layout="list" title="<?php esc_attr_e( 'List layout', 'nivo-ajax-search-for-woocommerce' ); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </button>
                        <button type="button" class="nivo-pv-layout-btn nivo-pv-layout-btn--soon" data-layout="grid" title="<?php esc_attr_e( 'Grid layout (coming soon)', 'nivo-ajax-search-for-woocommerce' ); ?>" disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        </button>
                        <button type="button" class="nivo-pv-layout-btn nivo-pv-layout-btn--soon" data-layout="compact" title="<?php esc_attr_e( 'Compact layout (coming soon)', 'nivo-ajax-search-for-woocommerce' ); ?>" disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                        </button>
                    </div>
                </div>

                <div class="nivo-pv-canvas">

                    <?php
                    // Cart icon SVG (same as frontend)
                    $cart_svg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';

                    // Image placeholder SVG (data URI so it works inside <img> src or <svg> element)
                    $img_placeholder = '<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="nivo-search-product-image"><rect width="48" height="48" rx="3" fill="#f0f0f1"/><path d="M10 36l10-13 7 9 5-6 10 10H10z" fill="#c3c4c7"/><circle cx="17" cy="18" r="4" fill="#c3c4c7"/></svg>';

                    $sample_products = array(
                        array(
                            'title'       => 'Wireless Headphones Pro',
                            'sku'         => 'WHP-100',
                            'price'       => '$89<sup>.99</sup>',
                            'desc'        => 'Premium noise-cancelling over-ear headphones.',
                            'stock_class' => 'instock',
                            'stock_label' => 'In Stock',
                            'cat'         => 'Electronics',
                        ),
                        array(
                            'title'       => 'Running Shoes Ultra',
                            'sku'         => 'RSU-200',
                            'price'       => '<del><span class="amount">$129.99</span></del> <ins><span class="amount">$99.00</span></ins>',
                            'desc'        => 'Lightweight trail runners for all terrains.',
                            'stock_class' => 'onbackorder',
                            'stock_label' => 'Low Stock',
                            'cat'         => 'Footwear',
                        ),
                        array(
                            'title'       => 'Coffee Maker Deluxe',
                            'sku'         => 'CMD-300',
                            'price'       => '$149<sup>.99</sup>',
                            'desc'        => 'Programmable 12-cup drip coffee maker.',
                            'stock_class' => 'instock',
                            'stock_label' => 'In Stock',
                            'cat'         => 'Kitchen',
                        ),
                    );
                    ?>

                    <!-- Real frontend markup — styled by nivo-search.css exactly as on site -->
                    <div class="nivo-ajax-search-container nivo-search-has-results" id="nivo-pv-container">

                        <!-- Search bar (identical structure to frontend shortcode output) -->
                        <div class="nivo-search-form">
                            <div class="nivo-search-wrapper">
                                <input
                                    type="search"
                                    class="nivo-search-product-search"
                                    placeholder="<?php echo esc_attr( $settings['placeholder'] ?: __( 'Search products…', 'nivo-ajax-search-for-woocommerce' ) ); ?>"
                                    value="wireless"
                                    readonly
                                    style="height:<?php echo (int) $settings['bar_height']; ?>px;border-color:<?php echo esc_attr( $settings['border_color'] ); ?>;background:<?php echo esc_attr( $settings['bg_color'] ); ?>;color:<?php echo esc_attr( $settings['text_color'] ); ?>;">
                                <span class="nivo-search-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </span>
                            </div>
                        </div>

                        <!-- Results panel — always visible in preview -->
                        <div class="nivo-search-results nivo-pv-results-always-open"
                             style="border-color:<?php echo esc_attr( $settings['results_border_color'] ); ?>;background:<?php echo esc_attr( $settings['results_bg_color'] ); ?>;color:<?php echo esc_attr( $settings['results_text_color'] ); ?>;">

                            <div class="nivo-search-scrollable">
                                <div class="nivo-search-products-section">
                                    <ul class="nivo-search-results-list">

                                        <?php foreach ( $sample_products as $prd ) : ?>
                                        <li class="nivo-search-result-item">

                                            <!-- Image -->
                                            <a href="#" class="nivo-search-product-link nivo-pv-toggle--images" onclick="return false;">
                                                <?php echo $img_placeholder; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                                            </a>

                                            <!-- Info column -->
                                            <div class="nivo-search-product-info">
                                                <div class="nivo-search-product-title-link">
                                                    <span class="nivo-search-product-title"><?php echo esc_html( $prd['title'] ); ?></span>
                                                    <span class="nivo-search-product-sku nivo-pv-toggle--sku">SKU: <?php echo esc_html( $prd['sku'] ); ?></span>
                                                </div>
                                                <span class="nivo-search-product-description nivo-pv-toggle--description"><?php echo esc_html( $prd['desc'] ); ?></span>
                                                <div class="nivo-search-meta-chips">
                                                    <span class="nivo-stock-badge nivo-stock-<?php echo esc_attr( $prd['stock_class'] ); ?> nivo-pv-toggle--stock_status"><?php echo esc_html( $prd['stock_label'] ); ?></span>
                                                    <span class="nivo-search-category-badges nivo-pv-toggle--category_badge">
                                                        <span class="nivo-cat-badge"><?php echo esc_html( $prd['cat'] ); ?></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Actions column -->
                                            <div class="nivo-search-product-actions">
                                                <span class="nivo-search-product-price nivo-pv-toggle--price"><?php echo wp_kses_post( $prd['price'] ); ?></span>
                                                <span class="nivo-add-to-cart-wrapper nivo-pv-toggle--add_to_cart">
                                                    <span class="nivo-qty-wrapper nivo-pv-toggle--qty_selector">
                                                        <button type="button" class="nivo-qty-btn nivo-qty-minus" onclick="return false;">−</button>
                                                        <input type="number" class="nivo-qty-input" value="1" min="1" max="99" readonly>
                                                        <button type="button" class="nivo-qty-btn nivo-qty-plus" onclick="return false;">+</button>
                                                    </span>
                                                    <button type="button" class="nivo-add-to-cart-btn nivo-atc-icon" onclick="return false;"><?php echo $cart_svg; // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
                                                </span>
                                            </div>

                                        </li>
                                        <?php endforeach; ?>

                                    </ul>
                                </div>
                            </div>

                            <div class="nivo-search-view-all nivo-pv-toggle--view_all">
                                <a href="#" class="nivo-search-view-all-link" onclick="return false;">
                                    <?php esc_html_e( 'View all results for "wireless"', 'nivo-ajax-search-for-woocommerce' ); ?>
                                </a>
                            </div>

                        </div><!-- .nivo-search-results -->
                    </div><!-- .nivo-ajax-search-container -->

                </div><!-- .nivo-pv-canvas -->

                <p class="nivo-pv-note"><?php esc_html_e( 'Updates instantly as you change settings.', 'nivo-ajax-search-for-woocommerce' ); ?></p>

            </div><!-- .nivo-preset-preview -->

        </div><!-- .nivo-preset-wrap -->
        <?php
    }

    /**
     * Save preset meta
     */
    public function save_preset_meta($post_id, $post) {
        if ( ! isset( $_POST['nivo_preset_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['nivo_preset_nonce'] ), 'nivo_preset_meta' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if ( isset( $_POST['nivo_settings'] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- individual values are sanitized below (absint, sanitize_text_field, sanitize_hex_color, isset).
            $settings = wp_unslash( $_POST['nivo_settings'] );

            // Read existing meta first so we never lose keys not present in the form
            // (e.g. keys added by migration for future phases, or Pro keys).
            $existing_generale = get_post_meta( $post_id, '_nivo_search_generale', true );
            $existing_query    = get_post_meta( $post_id, '_nivo_search_query', true );
            $existing_display  = get_post_meta( $post_id, '_nivo_search_display', true );
            $existing_style    = get_post_meta( $post_id, '_nivo_search_style', true );

            $existing_generale = is_array( $existing_generale ) ? $existing_generale : array();
            $existing_query    = is_array( $existing_query )    ? $existing_query    : array();
            $existing_display  = is_array( $existing_display )  ? $existing_display  : array();
            $existing_style    = is_array( $existing_style )    ? $existing_style    : array();

            // 1. General Settings — merge form values over existing (preserves delay and future keys).
            $genarale_settings = array_merge( $existing_generale, array(
                'limit'       => absint( $settings['limit'] ?? 10 ),
                'min_chars'   => absint( $settings['min_chars'] ?? 2 ),
                'placeholder' => sanitize_text_field( $settings['placeholder'] ?? '' ),
                'delay'       => absint( $settings['delay'] ?? 300 ),
            ) );

            // 2. Query Settings — merge form values over existing.
            $query_settings = array_merge( $existing_query, array(
                'search_in_title'           => isset( $settings['search_in_title'] ) ? 1 : 0,
                'search_in_sku'             => isset( $settings['search_in_sku'] ) ? 1 : 0,
                'search_in_content'         => isset( $settings['search_in_content'] ) ? 1 : 0,
                'search_in_excerpt'         => isset( $settings['search_in_excerpt'] ) ? 1 : 0,
                'search_product_categories' => isset( $settings['search_product_categories'] ) ? 1 : 0,
                'search_product_tags'       => isset( $settings['search_product_tags'] ) ? 1 : 0,
                'exclude_out_of_stock'      => isset( $settings['exclude_out_of_stock'] ) ? 1 : 0,
            ) );

            // 3. Display Settings — merge form values over existing.
            $display_settings = array_merge( $existing_display, array(
                'show_images'        => isset( $settings['show_images'] ) ? 1 : 0,
                'show_price'         => isset( $settings['show_price'] ) ? 1 : 0,
                'show_sku'           => isset( $settings['show_sku'] ) ? 1 : 0,
                'show_description'   => isset( $settings['show_description'] ) ? 1 : 0,
                'show_stock_status'  => isset( $settings['show_stock_status'] ) ? 1 : 0,
                'show_category_badge'=> isset( $settings['show_category_badge'] ) ? 1 : 0,
                'show_add_to_cart'   => isset( $settings['show_add_to_cart'] ) ? 1 : 0,
                'show_qty_selector'  => isset( $settings['show_qty_selector'] ) ? 1 : 0,
                'show_view_all'      => isset( $settings['show_view_all'] ) ? 1 : 0,
            ) );

            // 4. Style Settings — merge form values over existing.
            $style_settings = array_merge( $existing_style, array(
                'bar_width'           => absint( $settings['bar_width'] ?? 600 ),
                'bar_height'          => absint( $settings['bar_height'] ?? 50 ),
                'border_color'        => sanitize_hex_color( $settings['border_color'] ?? '#dddddd' ) ?: '#dddddd',
                'bg_color'            => sanitize_hex_color( $settings['bg_color'] ?? '#ffffff' ) ?: '#ffffff',
                'text_color'          => sanitize_hex_color( $settings['text_color'] ?? '#333333' ) ?: '#333333',
                'results_width'       => absint( $settings['results_width'] ?? 600 ),
                'results_text_color'  => sanitize_hex_color( $settings['results_text_color'] ?? '#333333' ) ?: '#333333',
                'results_border_color'=> sanitize_hex_color( $settings['results_border_color'] ?? '#dddddd' ) ?: '#dddddd',
                'results_bg_color'    => sanitize_hex_color( $settings['results_bg_color'] ?? '#ffffff' ) ?: '#ffffff',
            ) );

            // Save all four meta groups.
            update_post_meta( $post_id, '_nivo_search_generale', $genarale_settings );
            update_post_meta( $post_id, '_nivo_search_query',    $query_settings );
            update_post_meta( $post_id, '_nivo_search_display',  $display_settings );
            update_post_meta( $post_id, '_nivo_search_style',    $style_settings );
        }
    }

    /**
     * Set custom columns
     */
    public function set_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['shortcode'] = __('Shortcode', 'nivo-ajax-search-for-woocommerce');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    /**
     * Render custom columns
     */
    public function render_columns($column, $post_id) {
        if ($column === 'shortcode') {
            $shortcode = '[nivo_search id="' . $post_id . '"]';
            echo '<code style="background:#f0f0f0;padding:4px 8px;border-radius:3px;">' . esc_html($shortcode) . '</code>';
        }
    }
}
