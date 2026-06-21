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
                    <?php _e('Copy Shortcode', 'nivo-ajax-search-for-woocommerce'); ?>
                </button>
            </div>
            <?php
        } else {
            echo '<p>' . __('Publish to generate shortcode', 'nivo-ajax-search-for-woocommerce') . '</p>';
        }
    }

    /**
     * Render settings meta box
     */

    public function render_settings_box($post) {
        wp_nonce_field('nivo_preset_meta', 'nivo_preset_nonce');
        
        // Fetch new split meta
        $generale_settings = get_post_meta($post->ID, '_nivo_search_generale', true) ?: [];
        $query_settings    = get_post_meta($post->ID, '_nivo_search_query', true) ?: [];
        $display_settings  = get_post_meta($post->ID, '_nivo_search_display', true) ?: [];
        $style_settings    = get_post_meta($post->ID, '_nivo_search_style', true) ?: [];

        // Merge all for display logic
        $settings = array_merge($generale_settings, $query_settings, $display_settings, $style_settings);

        $defaults = Helper::get_default_settings();
        $settings = wp_parse_args($settings, $defaults);
        ?>
        <style>
            .nivo-settings-section { margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 4px; }
            .nivo-settings-section h3 { margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
            .nivo-setting-row { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
            .nivo-setting-row label { min-width: 180px; font-weight: 600; }
            .nivo-setting-row input[type="number"]{ width: 100px; }
            .nivo-setting-row input[type="text"] { width: 200px; }
            .nivo-setting-row input[type="color"] { width: 80px; height: 35px; }
            h4.nivo-search-settings-sub {font-size: 16px;margin-top: 15px;}
        </style>

        <!-- General Settings -->
        <div class="nivo-settings-section">
            <h3><?php _e('General Settings', 'nivo-ajax-search-for-woocommerce'); ?></h3>
            
            <div class="nivo-setting-row">
                <label><?php _e('Results Limit', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[limit]" value="<?php echo esc_attr($settings['limit']); ?>" min="1" max="50">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Minimum Characters', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[min_chars]" value="<?php echo esc_attr($settings['min_chars']); ?>" min="1" max="5">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Placeholder Text', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" name="nivo_settings[placeholder]" value="<?php echo esc_attr($settings['placeholder']); ?>">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Search Delay (ms)', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[delay]" value="<?php echo esc_attr($settings['delay']); ?>" min="0" max="2000" step="50">
                <span style="color:#666;font-size:12px;"><?php _e('Debounce delay before sending search request (default: 300)', 'nivo-ajax-search-for-woocommerce'); ?></span>
            </div>
        </div>

        <!-- Search Bar Styling -->
        <div class="nivo-settings-section">
            <h3><?php _e('Search Bar Layout & Styling', 'nivo-ajax-search-for-woocommerce'); ?></h3>

            <div class="nivo-setting-row">
                <label><?php _e('Height (px)', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[bar_height]" value="<?php echo esc_attr($settings['bar_height']); ?>" min="30" max="100">
            </div>
            
            <div class="nivo-setting-row">
                <label><?php _e('Width (px)', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[bar_width]" value="<?php echo esc_attr($settings['bar_width']); ?>" min="200" max="1200">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Border Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[border_color]" value="<?php echo esc_attr($settings['border_color']); ?>">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Background Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[bg_color]" value="<?php echo esc_attr($settings['bg_color']); ?>">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Text Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[text_color]" value="<?php echo esc_attr($settings['text_color']); ?>">
            </div>
        </div>

        <!-- Results Styling -->
        <div class="nivo-settings-section">
            <h3><?php _e('Search Results Layout & Styling', 'nivo-ajax-search-for-woocommerce'); ?></h3>

            <h4 class="nivo-search-settings-sub"><?php _e('Display Products Info', 'nivo-ajax-search-for-woocommerce'); ?></h4>
            
            <div class="nivo-setting-row">
                <label><?php _e('Show Product Images', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[show_images]" value="1" <?php checked($settings['show_images'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label> <?php _e('Show Price', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[show_price]" value="1" <?php checked($settings['show_price'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Show SKU', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[show_sku]" value="1" <?php checked($settings['show_sku'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Show Short Description', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[show_description]" value="1" <?php checked($settings['show_description'], 1); ?>>
            </div>

            <h4 class="nivo-search-settings-sub"><?php _e('Display Styling', 'nivo-ajax-search-for-woocommerce'); ?></h4>

            <div class="nivo-setting-row">
                <label><?php _e('Width (px)', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="number" name="nivo_settings[results_width]" value="<?php echo esc_attr($settings['results_width']); ?>" min="200" max="1200">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Text Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[results_text_color]" value="<?php echo esc_attr($settings['results_text_color']); ?>">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Border Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[results_border_color]" value="<?php echo esc_attr($settings['results_border_color']); ?>">
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Background Color', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="text" class="nivo-color-picker" name="nivo_settings[results_bg_color]" value="<?php echo esc_attr($settings['results_bg_color']); ?>">
            </div>

        </div>

        <!-- Configuration -->
        <div class="nivo-settings-section">
            <h3><?php _e('Search Configuration', 'nivo-ajax-search-for-woocommerce'); ?></h3>

            <h4 class="nivo-search-settings-sub"><?php _e('Search Scope', 'nivo-ajax-search-for-woocommerce'); ?></h4>
            
            <div class="nivo-setting-row">
                <label><?php _e('Search in Title', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_in_title]" value="1" <?php checked($settings['search_in_title'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Search in SKU', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_in_sku]" value="1" <?php checked($settings['search_in_sku'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Search in Description', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_in_content]" value="1" <?php checked($settings['search_in_content'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Search in Short Description', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_in_excerpt]" value="1" <?php checked($settings['search_in_excerpt'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Exclude Out of Stock', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[exclude_out_of_stock]" value="1" <?php checked($settings['exclude_out_of_stock'], 1); ?>>
            </div>

            <h4 class="nivo-search-settings-sub"><?php _e('Others Content', 'nivo-ajax-search-for-woocommerce'); ?></h4>
            
            <div class="nivo-setting-row">
                <label><?php _e('Show Product Categories', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_product_categories]" value="1" <?php checked($settings['search_product_categories'], 1); ?>>
            </div>

            <div class="nivo-setting-row">
                <label><?php _e('Show Product Tags', 'nivo-ajax-search-for-woocommerce'); ?></label>
                <input type="checkbox" name="nivo_settings[search_product_tags]" value="1" <?php checked($settings['search_product_tags'], 1); ?>>
            </div>

        </div>
        <?php
    }

    /**
     * Save preset meta
     */
    public function save_preset_meta($post_id, $post) {
        if (!isset($_POST['nivo_preset_nonce']) || !wp_verify_nonce($_POST['nivo_preset_nonce'], 'nivo_preset_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['nivo_settings'])) {
            $settings = $_POST['nivo_settings'];

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

            // 2. Query Settings — merge form values over existing (preserves gtin, attributes, synonyms).
            $query_settings = array_merge( $existing_query, array(
                'search_in_title'           => isset( $settings['search_in_title'] ) ? 1 : 0,
                'search_in_sku'             => isset( $settings['search_in_sku'] ) ? 1 : 0,
                'search_in_content'         => isset( $settings['search_in_content'] ) ? 1 : 0,
                'search_in_excerpt'         => isset( $settings['search_in_excerpt'] ) ? 1 : 0,
                'search_product_categories' => isset( $settings['search_product_categories'] ) ? 1 : 0,
                'search_product_tags'       => isset( $settings['search_product_tags'] ) ? 1 : 0,
                'exclude_out_of_stock'      => isset( $settings['exclude_out_of_stock'] ) ? 1 : 0,
            ) );

            // 3. Display Settings — merge form values over existing (preserves ratings, stock, badge, qty).
            $display_settings = array_merge( $existing_display, array(
                'show_images'      => isset( $settings['show_images'] ) ? 1 : 0,
                'show_price'       => isset( $settings['show_price'] ) ? 1 : 0,
                'show_sku'         => isset( $settings['show_sku'] ) ? 1 : 0,
                'show_description' => isset( $settings['show_description'] ) ? 1 : 0,
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
