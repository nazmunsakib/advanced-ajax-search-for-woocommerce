<?php
namespace NivoSearch;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Settings Page
 *
 * @package NivoSearch
 * @since 1.0.0
 */

/**
 * Admin Settings Class
 *
 * Handles plugin admin settings and configuration
 *
 * @since 1.0.0
 */
class Admin_Settings {
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_head', array( $this, 'remove_notices' ) );
        add_action( 'admin_head', array( $this, 'fix_menu_icon_style' ) );
        add_action( 'admin_init', array( $this, 'save_settings' ) );
    }

    /**
     * Fix menu icon style
     * 
     * @since 1.1.0
     */
    public function fix_menu_icon_style() {
        echo '<style>
            #toplevel_page_nivo-search .wp-menu-image img {
                max-width: 20px;
                max-height: 20px;
                padding-top: 7px;
            }
        </style>';
    }
        
    /**
     * Add admin menu
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        // Add main menu
        add_menu_page(
            __('NivoSearch', 'nivo-ajax-search-for-woocommerce'),
            __('NivoSearch', 'nivo-ajax-search-for-woocommerce'),
            'manage_options',
            'nivo-search',
            array( $this, 'settings_page' ),
            NIVO_SEARCH_PLUGIN_URL . 'assets/imgs/dashboard-icon.png',
            56
        );
        
        // Add Settings submenu (rename the first submenu)
        add_submenu_page(
            'nivo-search',
            __('Settings', 'nivo-ajax-search-for-woocommerce'),
            __('Settings', 'nivo-ajax-search-for-woocommerce'),
            'manage_options',
            'nivo-search',
            array( $this, 'settings_page' )
        );
    }
    
    /**
     * Remove other plugin notices
     *
     * @since 1.1.0
     */
    public function remove_notices() {
        $screen = get_current_screen();
        
        if ( ! $screen ) {
            return;
        }
        
        // Remove notices from settings page and preset pages
        if ( $screen->id === 'toplevel_page_nivo-search' || 
             $screen->post_type === 'nivo_search_preset' ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
    }
    
    
    /**
     * Save global plugin settings submitted from the settings page.
     *
     * @since 1.2.0
     */
    public function save_settings() {
        if ( ! isset( $_POST['nivo_save_settings'], $_POST['_nivo_settings_nonce'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nivo_settings_nonce'] ) ), 'nivo_save_settings' ) ) {
            return;
        }

        // "Delete data on uninstall" checkbox — unchecked = 'no', checked = 'yes'.
        $delete_data = isset( $_POST['nivo_search_delete_data_on_uninstall'] ) ? 'yes' : 'no';
        update_option( 'nivo_search_delete_data_on_uninstall', $delete_data );

        // Redirect back with a success notice.
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'          => 'nivo-search',
                    'settings-saved' => '1',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Settings page HTML
     *
     * @since 1.0.0
     */
    public function settings_page() {
        $default_preset = get_option( 'nivo_search_default_preset_created') ?? '123';
        ?>
        <?php
        // Show save confirmation notice.
        if ( isset( $_GET['settings-saved'] ) && '1' === $_GET['settings-saved'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'nivo-ajax-search-for-woocommerce' ) . '</p></div>';
        }
        ?>
        <div class="nivo-settings-page">
            <div class="nivo-settings-header">
                <img src="<?php echo esc_url( NIVO_SEARCH_PLUGIN_URL . 'assets/imgs/nivo-search-icon.png' ); ?>" alt="NivoSearch Icon" class="nivo-settings-icon">
                <div>
                    <h1><?php _e('NivoSearch', 'nivo-ajax-search-for-woocommerce'); ?></h1>
                    <p><?php _e('AJAX Product Search for WooCommerce', 'nivo-ajax-search-for-woocommerce'); ?></p>
                </div>
            </div>
            
            <div class="nivo-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <?php _e('Quick Start Guide', 'nivo-ajax-search-for-woocommerce'); ?>
                </h2>
                <p><?php _e('Create unlimited search presets with custom settings:', 'nivo-ajax-search-for-woocommerce'); ?></p>
                <ol>
                    <li><?php _e('Go to <strong>NivoSearch → Search Presets</strong>', 'nivo-ajax-search-for-woocommerce'); ?></li>
                    <li><?php _e('Click <strong>"Add New Preset"</strong>', 'nivo-ajax-search-for-woocommerce'); ?></li>
                    <li><?php _e('Configure all settings (search scope, styling, display options)', 'nivo-ajax-search-for-woocommerce'); ?></li>
                    <li><?php _e('Click <strong>Publish</strong> to generate your shortcode', 'nivo-ajax-search-for-woocommerce'); ?></li>
                    <li><?php _e('Copy and use the shortcode anywhere: ', 'nivo-ajax-search-for-woocommerce'); ?><code>[nivo_search id="<?php echo esc_attr(  $default_preset ); ?>"]</code></li>
                </ol>
            </div>
            
            <div class="nivo-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <?php _e( 'Key Features', 'nivo-ajax-search-for-woocommerce' ); ?>
                </h2>
                <div class="nivo-feature-grid">
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M7 2v11h3v9l7-12h-4l4-8z"/></svg>
                            <?php _e( 'High Performance', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Transient-cached AJAX search with ~200ms response time. Cache auto-invalidates on product or preset changes.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                            <?php _e( 'Unlimited Presets', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Create multiple search bars with different scopes, layouts, and styles. Use anywhere via shortcode or block.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                            <?php _e( 'Smart Search', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Search across product title, SKU, description, excerpt, categories, and tags — all configurable per preset.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            <?php _e( 'Add to Cart', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Customers can add products directly from search results with AJAX — including a quantity selector and instant mini-cart update.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
                            <?php _e( 'Rich Results', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Each result shows thumbnail, title, SKU, short description, current price, stock status, and category badges.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                    <div class="nivo-feature-item">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>
                            <?php _e( 'Fully Responsive', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </h3>
                        <p><?php _e( 'Compact result layout adapts to all screen sizes with mobile-optimised design.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="nivo-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/></svg>
                    <?php _e( 'Preset Settings Reference', 'nivo-ajax-search-for-woocommerce' ); ?>
                </h2>
                <p style="color:#50575e; margin-bottom:12px;"><?php _e( 'Each preset supports the following option groups:', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                <ul style="font-size: 14px; line-height: 1.9; color: #50575e;">
                    <li><strong><?php _e( 'General:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Results limit, minimum characters, search delay (debounce), placeholder text', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                    <li><strong><?php _e( 'Search Scope:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Title, SKU, description, excerpt — toggle each independently. Hide out-of-stock products.', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                    <li><strong><?php _e( 'Other Content:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Include product categories and/or tags as separate result sections', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                    <li><strong><?php _e( 'Display Options:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Thumbnail, price, SKU, short description, stock status badge, category badges, add-to-cart button, quantity selector, "View all results" link', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                    <li><strong><?php _e( 'Search Bar Style:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Width, height, background colour, text colour, border colour and radius', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                    <li><strong><?php _e( 'Results Panel Style:', 'nivo-ajax-search-for-woocommerce' ); ?></strong> <?php _e( 'Background, text colour, border colour — all set per preset', 'nivo-ajax-search-for-woocommerce' ); ?></li>
                </ul>
            </div>

            <div class="nivo-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                    <?php esc_html_e( 'Data &amp; Privacy', 'nivo-ajax-search-for-woocommerce' ); ?>
                </h2>

                <p style="color:#50575e; font-size:14px; margin-bottom:16px;">
                    <?php esc_html_e( 'By default, your search presets and settings are kept when you delete this plugin, so you can reinstall without losing anything. Enable the option below only if you want a complete clean removal.', 'nivo-ajax-search-for-woocommerce' ); ?>
                </p>

                <form method="post" action="">
                    <?php wp_nonce_field( 'nivo_save_settings', '_nivo_settings_nonce' ); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row" style="padding:12px 0; font-weight:600;">
                                <?php esc_html_e( 'Delete data on uninstall', 'nivo-ajax-search-for-woocommerce' ); ?>
                            </th>
                            <td style="padding:12px 0;">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="nivo_search_delete_data_on_uninstall"
                                        value="yes"
                                        <?php checked( 'yes', get_option( 'nivo_search_delete_data_on_uninstall', 'no' ) ); ?>
                                    >
                                    <?php esc_html_e( 'Permanently delete all presets, settings, and plugin data when the plugin is deleted.', 'nivo-ajax-search-for-woocommerce' ); ?>
                                </label>
                                <p class="description" style="color:#d63638; font-weight:600; margin-top:6px;">
                                    &#9888; <?php esc_html_e( 'Warning: this cannot be undone. Leave unchecked to keep your data safe.', 'nivo-ajax-search-for-woocommerce' ); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p>
                        <button type="submit" name="nivo_save_settings" class="button button-primary">
                            <?php esc_html_e( 'Save Settings', 'nivo-ajax-search-for-woocommerce' ); ?>
                        </button>
                    </p>
                </form>
            </div>

        </div>
        <?php
    }
}