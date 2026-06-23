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
        add_action( 'wp_ajax_nivo_rebuild_index', array( $this, 'handle_rebuild_index' ) );
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

        // "Delete data on uninstall" checkbox  -  unchecked = 'no', checked = 'yes'.
        $delete_data = isset( $_POST['nivo_search_delete_data_on_uninstall'] ) ? 'yes' : 'no';
        update_option( 'nivo_search_delete_data_on_uninstall', $delete_data );

        // Search results page integration.
        $results_page = isset( $_POST['nivo_search_results_page'] ) ? 'yes' : 'no';
        update_option( 'nivo_search_results_page', $results_page );

        // Seamless theme integration.
        $auto_replace = isset( $_POST['nivo_search_auto_replace'] ) ? 'yes' : 'no';
        update_option( 'nivo_search_auto_replace', $auto_replace );

        // Preset used when auto-replacing the theme search form.
        $theme_preset_id = isset( $_POST['nivo_search_theme_preset_id'] ) ? absint( $_POST['nivo_search_theme_preset_id'] ) : 0;
        update_option( 'nivo_search_theme_preset_id', $theme_preset_id );

        // Google Analytics tracking.
        $ga_tracking = isset( $_POST['nivo_search_ga_tracking'] ) ? 'yes' : 'no';
        update_option( 'nivo_search_ga_tracking', $ga_tracking );

        // Accuracy settings (also handled on Settings tab).
        if ( isset( $_POST['nivo_search_enable_typo_tolerance'] ) || isset( $_POST['nivo_has_accuracy_fields'] ) ) {
            $enable_fuzzy = isset( $_POST['nivo_search_enable_fuzzy_search'] ) ? 1 : 0;
            update_option( 'nivo_search_enable_fuzzy_search', $enable_fuzzy );

            $enable_typo = isset( $_POST['nivo_search_enable_typo_tolerance'] ) ? 1 : 0;
            update_option( 'nivo_search_enable_typo_tolerance', $enable_typo );

            // Always use distance 2 — hardcoded, no longer user-configurable.
            update_option( 'nivo_search_max_typo_distance', 2 );

            $show_dym = isset( $_POST['nivo_search_show_did_you_mean'] ) ? 1 : 0;
            update_option( 'nivo_search_show_did_you_mean', $show_dym );

        }

        // Redirect back with a success notice.
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'           => 'nivo-search',
                    'tab'            => 'settings',
                    'settings-saved' => '1',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * AJAX handler: rebuild the product search index.
     *
     * Called via wp_ajax_nivo_rebuild_index. Requires manage_options capability
     * and a valid nonce. Delegates to Product_Indexer::rebuild_all().
     *
     * @since 2.3.0
     * @return void  Sends JSON and exits.
     */
    public function handle_rebuild_index() {
        check_ajax_referer( 'nivo_rebuild_index', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'nivo-ajax-search-for-woocommerce' ) ), 403 );
        }

        $indexer = new Product_Indexer();
        $count   = $indexer->rebuild_all();
        $stats   = Product_Indexer::get_stats();

        wp_send_json_success(
            array(
                'indexed'      => $stats['indexed'],
                'total'        => $stats['total'],
                'count'        => $count,
                'last_rebuilt' => $stats['last_rebuilt'],
                /* translators: %d number of products */
                'message'      => sprintf( __( 'Index rebuilt. %d products indexed.', 'nivo-ajax-search-for-woocommerce' ), $count ),
            )
        );
    }

    /**
     * Settings page HTML
     *
     * @since 1.0.0
     */
    public function settings_page() {
        $default_preset = get_option( 'nivo_search_default_preset_created' ) ?: '123';

        $allowed_tabs = array( 'settings', 'typo-rules', 'help' );
        $active_tab   = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $allowed_tabs, true ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ? sanitize_key( $_GET['tab'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            : 'settings';

        ?>
        <div class="nivo-settings-page">

            <!-- ── Brand Header ───────────────────────────────────────────── -->
            <div class="nivo-settings-header">
                <img src="<?php echo esc_url( NIVO_SEARCH_PLUGIN_URL . 'assets/imgs/nivo-search-icon.png' ); ?>" alt="NivoSearch Icon" class="nivo-settings-icon">
                <div>
                    <h1><?php esc_html_e( 'NivoSearch', 'nivo-ajax-search-for-woocommerce' ); ?></h1>
                    <p><?php esc_html_e( 'AJAX Product Search for WooCommerce', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </div>
            </div>

            <!-- ── Top-level Tab Nav ──────────────────────────────────────── -->
            <nav class="nav-tab-wrapper nivo-top-tabs">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=settings' ) ); ?>"
                    class="nav-tab<?php echo 'settings' === $active_tab ? ' nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Settings', 'nivo-ajax-search-for-woocommerce' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=typo-rules' ) ); ?>"
                    class="nav-tab<?php echo 'typo-rules' === $active_tab ? ' nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Typo Rules', 'nivo-ajax-search-for-woocommerce' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=help' ) ); ?>"
                    class="nav-tab<?php echo 'help' === $active_tab ? ' nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Help', 'nivo-ajax-search-for-woocommerce' ); ?>
                </a>
            </nav>

            <?php if ( 'typo-rules' === $active_tab ) : ?>
                <?php do_action( 'nivo_render_optimization_tab' ); ?>
            <?php elseif ( 'help' === $active_tab ) : ?>
                <?php $this->render_help_tab( $default_preset ); ?>
            <?php else : ?>
                <?php $this->render_settings_tab(); ?>
            <?php endif; ?>

        </div><!-- .nivo-settings-page -->
        <?php
    }

    /**
     * Render the Dashboard tab content.
     *
     * @since 2.2.0
     * @param string|int $default_preset ID of the default preset.
     * @return void
     */
    private function render_dashboard_tab( $default_preset ) {
        $presets_url    = admin_url( 'edit.php?post_type=nivo_search_preset' );
        $new_preset_url = admin_url( 'post-new.php?post_type=nivo_search_preset' );
        $stats          = Product_Indexer::get_stats();
        $idx_indexed    = (int) $stats['indexed'];
        $idx_total      = (int) $stats['total'];
        $idx_pct        = $idx_total > 0 ? min( 100, round( $idx_indexed / $idx_total * 100 ) ) : 0;
        $idx_rebuilt    = (int) $stats['last_rebuilt'];
        $idx_stale      = $idx_total > 0 && ( $idx_rebuilt === 0 || ( time() - $idx_rebuilt ) > DAY_IN_SECONDS );
        $idx_ok         = $idx_total > 0 && $idx_indexed >= $idx_total && ! $idx_stale;
        $idx_rebuilt_label = $idx_rebuilt > 0
            ? sprintf(
                /* translators: %s is a human-readable time-ago string */
                __( 'Last rebuilt %s ago', 'nivo-ajax-search-for-woocommerce' ),
                human_time_diff( $idx_rebuilt )
            )
            : __( 'Never rebuilt', 'nivo-ajax-search-for-woocommerce' );
        ?>

        <!-- ── Index Health Widget ─────────────────────────────────────── -->
        <div class="nivo-card nivo-dash-index-card">
            <div class="nivo-dash-index-card__header">
                <span class="nivo-index-status-dot nivo-index-status-dot--lg <?php echo $idx_ok ? 'nivo-index-status-dot--ok' : 'nivo-index-status-dot--warn'; ?>" id="nivo-dash-dot"></span>
                <div class="nivo-dash-index-card__meta">
                    <strong class="nivo-dash-index-card__title">
                        <?php if ( $idx_ok ) : ?>
                            <?php esc_html_e( 'Search Index is Healthy', 'nivo-ajax-search-for-woocommerce' ); ?>
                        <?php elseif ( $idx_rebuilt === 0 ) : ?>
                            <?php esc_html_e( 'Search Index Not Built', 'nivo-ajax-search-for-woocommerce' ); ?>
                        <?php elseif ( $idx_stale ) : ?>
                            <?php esc_html_e( 'Search Index is Stale', 'nivo-ajax-search-for-woocommerce' ); ?>
                        <?php else : ?>
                            <?php esc_html_e( 'Search Index Incomplete', 'nivo-ajax-search-for-woocommerce' ); ?>
                        <?php endif; ?>
                    </strong>
                    <span class="nivo-dash-index-card__sub" id="nivo-dash-index-sub">
                        <?php printf( esc_html__( '%1$d / %2$d products indexed (%3$d%%)', 'nivo-ajax-search-for-woocommerce' ), $idx_indexed, $idx_total, $idx_pct ); ?>
                        &bull; <?php echo esc_html( $idx_rebuilt_label ); ?>
                    </span>
                </div>
                <button type="button" id="nivo-dash-rebuild-btn" class="button button-small nivo-rebuild-btn"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'nivo_rebuild_index' ) ); ?>"
                    data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                    <span class="nivo-rebuild-btn__spinner" style="display:none;">
                        <svg class="nivo-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </span>
                    <svg class="nivo-rebuild-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="12" height="12"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.99"/></svg>
                    <?php esc_html_e( 'Rebuild Index', 'nivo-ajax-search-for-woocommerce' ); ?>
                </button>
            </div>
            <!-- Progress bar -->
            <div class="nivo-dash-index-card__bar-wrap">
                <div class="nivo-dash-index-card__bar" id="nivo-dash-bar" style="width:<?php echo esc_attr( $idx_pct ); ?>%"></div>
            </div>
            <div class="nivo-index-rebuild-result" id="nivo-dash-rebuild-result" style="display:none;margin-top:8px;"></div>
        </div>
        <script>
        (function() {
            var btn     = document.getElementById('nivo-dash-rebuild-btn');
            var result  = document.getElementById('nivo-dash-rebuild-result');
            var dot     = document.getElementById('nivo-dash-dot');
            var sub     = document.getElementById('nivo-dash-index-sub');
            var bar     = document.getElementById('nivo-dash-bar');
            if (!btn) return;
            btn.addEventListener('click', function() {
                btn.disabled = true;
                btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'inline-flex';
                btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'none';
                result.style.display = 'none';
                var fd = new FormData();
                fd.append('action', 'nivo_rebuild_index');
                fd.append('nonce',  btn.dataset.nonce);
                fetch(btn.dataset.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data) {
                        btn.disabled = false;
                        btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'none';
                        btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'inline';
                        if (data.success) {
                            var d   = data.data;
                            var pct = d.total > 0 ? Math.round(d.indexed / d.total * 100) : 0;
                            dot.className = 'nivo-index-status-dot nivo-index-status-dot--lg nivo-index-status-dot--ok';
                            sub.textContent = d.indexed + ' / ' + d.total + ' <?php echo esc_js( __( "products indexed", "nivo-ajax-search-for-woocommerce" ) ); ?> (' + pct + '%) • <?php echo esc_js( __( "Just rebuilt", "nivo-ajax-search-for-woocommerce" ) ); ?>';
                            if (bar) bar.style.width = pct + '%';
                            result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--ok';
                            result.textContent = d.message;
                            result.style.display = 'flex';
                        } else {
                            result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--err';
                            result.textContent = '<?php echo esc_js( __( "Rebuild failed. Please try again.", "nivo-ajax-search-for-woocommerce" ) ); ?>';
                            result.style.display = 'flex';
                        }
                    })
                    .catch(function() {
                        btn.disabled = false;
                        btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'none';
                        btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'inline';
                        result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--err';
                        result.textContent = '<?php echo esc_js( __( "Network error. Please try again.", "nivo-ajax-search-for-woocommerce" ) ); ?>';
                        result.style.display = 'flex';
                    });
            });
        })();
        </script>

        <!-- ── Quick Start ────────────────────────────────────────────── -->
        <div class="nivo-card nivo-gs-card">
            <div class="nivo-gs-card__header">
                <span class="nivo-gs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <h2 class="nivo-gs-card__title"><?php esc_html_e( 'Quick Start', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>
            <p class="nivo-gs-card__lead"><?php esc_html_e( 'Get NivoSearch live on your store in three steps.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
            <div class="nivo-gs-steps">
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">1</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Create a Search Preset', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Go to NivoSearch → Search Presets and click "Add New". Configure what to search, what to display, and how it should look.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <a href="<?php echo esc_url( $new_preset_url ); ?>" class="button button-primary button-small"><?php esc_html_e( 'Create Preset', 'nivo-ajax-search-for-woocommerce' ); ?></a>
                    </div>
                </div>
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">2</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Copy Your Shortcode', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'After publishing your preset, a shortcode is generated automatically.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <code class="nivo-gs-code">[nivo_search id="<?php echo esc_attr( $default_preset ); ?>"]</code>
                    </div>
                </div>
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">3</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Place It Anywhere', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Paste the shortcode into any page, post, or widget. Or use the NivoSearch block in the Gutenberg editor.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * Render the Settings tab — accuracy and global plugin settings.
     *
     * @since 2.3.0
     * @return void
     */
    private function render_settings_tab() {
        $delete_data   = get_option( 'nivo_search_delete_data_on_uninstall', 'no' );
        $fuzzy_enabled     = (int) get_option( 'nivo_search_enable_fuzzy_search', 1 );
        $typo_enabled      = (int) get_option( 'nivo_search_enable_typo_tolerance', 1 );
        $typo_distance     = (int) get_option( 'nivo_search_max_typo_distance', 2 );
        $show_dym          = (int) get_option( 'nivo_search_show_did_you_mean', 1 );
        $stats         = \NivoSearch\Product_Indexer::get_stats();
        $indexed       = (int) $stats['indexed'];
        $total         = (int) $stats['total'];
        $pct           = $total > 0 ? min( 100, round( $indexed / $total * 100 ) ) : 0;
        $health_ok     = $total > 0 && $indexed >= $total;

        // Show saved notice (once — JS strips the param so refresh won't re-show it).
        if ( isset( $_GET['settings-saved'] ) && '1' === $_GET['settings-saved'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ?>
            <div class="nivo-saved-notice" id="nivo-saved-notice">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
                <?php esc_html_e( 'Settings saved.', 'nivo-ajax-search-for-woocommerce' ); ?>
            </div>
            <script>
            (function() {
                // Remove settings-saved from the URL so a page refresh won't re-show the notice.
                if ( window.history && window.history.replaceState ) {
                    var url = new URL( window.location.href );
                    url.searchParams.delete( 'settings-saved' );
                    window.history.replaceState( {}, '', url.toString() );
                }
            })();
            </script>
            <?php
        }
        ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'nivo_save_settings', '_nivo_settings_nonce' ); ?>
            <input type="hidden" name="nivo_has_accuracy_fields" value="1">

            <!-- __ Search Accuracy ____________________________________________ -->
            <div class="nivo-card nivo-accuracy-card">
                <div class="nivo-settings-card__header">
                    <span class="nivo-settings-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                    <div>
                        <h2 class="nivo-settings-card__title"><?php esc_html_e( 'Search Accuracy', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
                        <p class="nivo-settings-card__sub"><?php esc_html_e( 'Control how forgiving the search is when customers make spelling mistakes.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <?php
                $last_rebuilt  = (int) $stats['last_rebuilt'];
                $is_stale      = $total > 0 && ( $last_rebuilt === 0 || ( time() - $last_rebuilt ) > DAY_IN_SECONDS );
                $rebuilt_label = $last_rebuilt > 0
                    ? sprintf(
                        /* translators: %s is a human-readable time-ago string */
                        __( 'Last rebuilt %s ago', 'nivo-ajax-search-for-woocommerce' ),
                        human_time_diff( $last_rebuilt )
                    )
                    : __( 'Never rebuilt', 'nivo-ajax-search-for-woocommerce' );
                ?>

                <!-- Inner accuracy cards: Search Index full-width, then 3-col row -->
                <div class="nivo-integration-cards">

                    <!-- Card 1: Search Index (full width) -->
                    <div class="nivo-integration-card" style="grid-column: 1 / -1; flex-direction: row; align-items: center; gap: 16px;">
                        <!-- Left: icon + status info -->
                        <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                            <span class="nivo-integration-card__icon" style="flex-shrink:0;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                            </span>
                            <div style="min-width:0;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <strong class="nivo-integration-card__title" style="flex:none;"><?php esc_html_e( 'Search Index', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                                    <span class="nivo-index-status-dot <?php echo ( $health_ok && ! $is_stale ) ? 'nivo-index-status-dot--ok' : 'nivo-index-status-dot--warn'; ?>" id="nivo-index-dot"></span>
                                </div>
                                <p class="nivo-integration-card__desc" id="nivo-index-text" style="margin-top:2px;">
                                    <?php printf( esc_html__( '%1$d / %2$d products indexed (%3$d%%)', 'nivo-ajax-search-for-woocommerce' ), $indexed, $total, $pct ); ?>
                                    &nbsp;&bull;&nbsp;<span id="nivo-index-rebuilt"><?php echo esc_html( $rebuilt_label ); ?></span>
                                </p>
                                <?php if ( $is_stale ) : ?>
                                <p class="nivo-integration-card__desc" id="nivo-index-stale" style="color:#b45309; margin-top:2px;">
                                    <?php echo $last_rebuilt === 0
                                        ? esc_html__( 'Index not built yet. Click Rebuild to enable fuzzy search and typo correction.', 'nivo-ajax-search-for-woocommerce' )
                                        : esc_html__( 'Index is over 24 h old — rebuild to include new products.', 'nivo-ajax-search-for-woocommerce' ); ?>
                                </p>
                                <?php endif; ?>
                                <div class="nivo-index-rebuild-result" id="nivo-rebuild-result" style="display:none; margin-top:4px;"></div>
                            </div>
                        </div>
                        <!-- Right: rebuild button -->
                        <div style="flex-shrink:0;">
                            <button type="button" id="nivo-rebuild-btn" class="button button-secondary nivo-rebuild-btn"
                                data-nonce="<?php echo esc_attr( wp_create_nonce( 'nivo_rebuild_index' ) ); ?>"
                                data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                                <span class="nivo-rebuild-btn__spinner" style="display:none;"><svg class="nivo-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg></span>
                                <svg class="nivo-rebuild-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="12" height="12"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.99"/></svg>
                                <?php esc_html_e( 'Rebuild Index', 'nivo-ajax-search-for-woocommerce' ); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Card 2: Fuzzy Search -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><path d="M11 8a3 3 0 0 1 0 6"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( 'Fuzzy Search', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" name="nivo_search_enable_fuzzy_search" value="1" <?php checked( 1, $fuzzy_enabled ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'When no exact results are found, searches the index for the closest matching product names and terms.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>

                    <!-- Card 3: Typo Tolerance -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( 'Typo Tolerance', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" name="nivo_search_enable_typo_tolerance" value="1" <?php checked( 1, $typo_enabled ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'Auto-corrects spelling mistakes so "bleutooth" still finds Bluetooth products.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>

                    <!-- Card 4: Did You Mean? -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( '"Did You Mean?" Hint', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" name="nivo_search_show_did_you_mean" value="1" <?php checked( 1, $show_dym ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'Shows a clickable suggestion when a typo was detected — e.g. "Did you mean: bluetooth?"', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>

                </div><!-- .nivo-integration-cards -->
                <script>
                (function() {
                    var btn     = document.getElementById('nivo-rebuild-btn');
                    var result  = document.getElementById('nivo-rebuild-result');
                    var stale   = document.getElementById('nivo-index-stale');
                    var dot     = document.getElementById('nivo-index-dot');
                    var text    = document.getElementById('nivo-index-text');
                    var rebuilt = document.getElementById('nivo-index-rebuilt');
                    if (!btn) return;
                    btn.addEventListener('click', function() {
                        btn.disabled = true;
                        btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'inline-flex';
                        btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'none';
                        result.style.display = 'none';
                        var fd = new FormData();
                        fd.append('action', 'nivo_rebuild_index');
                        fd.append('nonce',  btn.dataset.nonce);
                        fetch(btn.dataset.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
                            .then(function(r){ return r.json(); })
                            .then(function(data) {
                                btn.disabled = false;
                                btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'none';
                                btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'inline';
                                if (data.success) {
                                    var d   = data.data;
                                    var pct = d.total > 0 ? Math.round(d.indexed / d.total * 100) : 0;
                                    text.childNodes[0].textContent = d.indexed + ' / ' + d.total + ' <?php echo esc_js( __( "products indexed", "nivo-ajax-search-for-woocommerce" ) ); ?> (' + pct + '%) • ';
                                    rebuilt.textContent = '<?php echo esc_js( __( 'Just rebuilt', 'nivo-ajax-search-for-woocommerce' ) ); ?>';
                                    dot.className = 'nivo-index-status-dot nivo-index-status-dot--ok nivo-integration-card__toggle';
                                    if (stale) stale.style.display = 'none';
                                    result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--ok';
                                    result.textContent = d.message;
                                    result.style.display = 'flex';
                                } else {
                                    result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--err';
                                    result.textContent = '<?php echo esc_js( __( 'Rebuild failed. Please try again.', 'nivo-ajax-search-for-woocommerce' ) ); ?>';
                                    result.style.display = 'flex';
                                }
                            })
                            .catch(function() {
                                btn.disabled = false;
                                btn.querySelector('.nivo-rebuild-btn__spinner').style.display = 'none';
                                btn.querySelector('.nivo-rebuild-btn__icon').style.display    = 'inline';
                                result.className  = 'nivo-index-rebuild-result nivo-index-rebuild-result--err';
                                result.textContent = '<?php echo esc_js( __( 'Network error. Please try again.', 'nivo-ajax-search-for-woocommerce' ) ); ?>';
                                result.style.display = 'flex';
                            });
                    });
                })();
                </script>

            </div>

            <!-- __ Search Integration _________________________________________ -->
            <?php
            $results_page_val    = get_option( 'nivo_search_results_page', 'yes' );
            $auto_replace_val    = get_option( 'nivo_search_auto_replace', 'no' );
            $ga_tracking_val     = get_option( 'nivo_search_ga_tracking', 'yes' );
            $theme_preset_id_val = (int) get_option( 'nivo_search_theme_preset_id', 0 );

            // Fetch all presets for the dropdown.
            $all_presets = get_posts( array(
                'post_type'      => 'nivo_search_preset',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            ?>
            <div class="nivo-card nivo-accuracy-card">
                <div class="nivo-settings-card__header">
                    <span class="nivo-settings-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <div>
                        <h2 class="nivo-settings-card__title"><?php esc_html_e( 'Search Integration', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
                        <p class="nivo-settings-card__sub"><?php esc_html_e( 'Control how NivoSearch integrates with WordPress, your theme, and analytics.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <!-- Inner integration setting cards -->
                <div class="nivo-integration-cards">

                    <!-- Card 1: Auto-replace theme search form (first — most important) -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( 'Replace theme search form', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" id="nivo_auto_replace_toggle" name="nivo_search_auto_replace" value="yes" <?php checked( 'yes', $auto_replace_val ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'Swap your theme\'s default search box with NivoSearch — no shortcode needed. Works with classic themes and block themes (Twenty Twenty-Four, Twenty Twenty-Five).', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <!-- Preset sub-option — visible only when toggle is on -->
                        <div class="nivo-integration-card__sub" id="nivo_theme_preset_row" style="<?php echo 'yes' === $auto_replace_val ? '' : 'display:none'; ?>">
                            <label class="nivo-integration-card__sub-label"><?php esc_html_e( 'Which preset to use:', 'nivo-ajax-search-for-woocommerce' ); ?></label>
                            <select name="nivo_search_theme_preset_id" class="nivo-select">
                                <option value="0"><?php esc_html_e( '— Default Preset —', 'nivo-ajax-search-for-woocommerce' ); ?></option>
                                <?php foreach ( $all_presets as $preset ) : ?>
                                    <option value="<?php echo esc_attr( $preset->ID ); ?>" <?php selected( $theme_preset_id_val, $preset->ID ); ?>>
                                        <?php echo esc_html( $preset->post_title ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Card 2: Search results page -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( 'Redirect search to shop page', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" name="nivo_search_results_page" value="yes" <?php checked( 'yes', $results_page_val ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'When a customer presses Enter, send them to the WooCommerce shop page instead of WordPress search results — so they see a proper product grid.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>

                    <!-- Card 3: Google Analytics tracking -->
                    <div class="nivo-integration-card">
                        <div class="nivo-integration-card__top">
                            <span class="nivo-integration-card__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </span>
                            <strong class="nivo-integration-card__title"><?php esc_html_e( 'Google Analytics tracking', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                            <label class="nivo-gs-toggle nivo-integration-card__toggle">
                                <input type="checkbox" name="nivo_search_ga_tracking" value="yes" <?php checked( 'yes', $ga_tracking_val ); ?>>
                                <span class="nivo-gs-toggle__slider"></span>
                            </label>
                        </div>
                        <p class="nivo-integration-card__desc"><?php esc_html_e( 'Send a search event to GA4, Universal Analytics, or GTM after each search. See what customers look for in your Analytics dashboard.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>

                </div><!-- .nivo-integration-cards -->
                <script>
                (function() {
                    var toggle = document.getElementById('nivo_auto_replace_toggle');
                    var sub    = document.getElementById('nivo_theme_preset_row');
                    if ( toggle && sub ) {
                        toggle.addEventListener('change', function() {
                            sub.style.display = this.checked ? '' : 'none';
                        });
                    }
                })();
                </script>

            </div>

            <!-- __ Data & Privacy _____________________________________________ -->
            <div class="nivo-card nivo-accuracy-card">
                <div class="nivo-settings-card__header">
                    <span class="nivo-settings-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                    <div>
                        <h2 class="nivo-settings-card__title"><?php esc_html_e( 'Data & Privacy', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
                        <p class="nivo-settings-card__sub"><?php esc_html_e( 'Control what happens to your data if the plugin is removed.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>

                <div class="nivo-setting-row">
                    <div class="nivo-setting-row__info">
                        <strong class="nivo-setting-row__title"><?php esc_html_e( 'Delete all data on uninstall', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p class="nivo-setting-row__desc"><?php esc_html_e( 'By default your presets and settings are kept when the plugin is deleted so you can reinstall without losing anything. Enable this only if you want a completely clean removal.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <?php if ( 'yes' === $delete_data ) : ?>
                            <p class="nivo-gs-warning"><?php esc_html_e( 'Warning: all presets, settings, and search data will be permanently deleted when the plugin is removed.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="nivo-setting-row__control">
                        <label class="nivo-gs-toggle">
                            <input type="checkbox" id="nivo_delete_data" name="nivo_search_delete_data_on_uninstall" value="yes" <?php checked( 'yes', $delete_data ); ?>>
                            <span class="nivo-gs-toggle__slider"></span>
                        </label>
                    </div>
                </div>

            </div><!-- end Data & Privacy card -->

            <div class="nivo-settings-footer">
                <button type="submit" name="nivo_save_settings" class="button button-primary">
                    <?php esc_html_e( 'Save Settings', 'nivo-ajax-search-for-woocommerce' ); ?>
                </button>
            </div>

        </form>
        <script>
        (function() {
            // Distance radio cards — highlight selected card.
            document.querySelectorAll('.nivo-distance-choice input').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('.nivo-distance-choice').forEach(function(el) {
                        el.classList.remove('nivo-distance-choice--active');
                    });
                    radio.closest('.nivo-distance-choice').classList.add('nivo-distance-choice--active');
                });
            });

            // Feature box toggles — reflect enabled state with --on class.
            document.querySelectorAll('.nivo-feature-box input[type="checkbox"]').forEach(function(cb) {
                var box = cb.closest('.nivo-feature-box');
                if (!box) return;
                cb.addEventListener('change', function() {
                    box.classList.toggle('nivo-feature-box--on', cb.checked);
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * Render the Help tab content.
     *
     * @since 2.3.0
     * @param string|int $default_preset ID of the default preset for examples.
     * @return void
     */
    private function render_help_tab( $default_preset ) {
        $presets_url    = admin_url( 'edit.php?post_type=nivo_search_preset' );
        $new_preset_url = admin_url( 'post-new.php?post_type=nivo_search_preset' );
        $opt_url        = admin_url( 'admin.php?page=nivo-search&tab=typo-rules' );
        ?>

        <!-- ── Quick Start ────────────────────────────────────────────── -->
        <div class="nivo-card nivo-gs-card">
            <div class="nivo-gs-card__header">
                <span class="nivo-gs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                <h2 class="nivo-gs-card__title"><?php esc_html_e( 'Quick Start', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>
            <p class="nivo-gs-card__lead"><?php esc_html_e( 'Get NivoSearch live on your store in three steps.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
            <div class="nivo-gs-steps">
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">1</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Create a Search Preset', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Go to NivoSearch → Search Presets and click "Add New". Configure what to search, what to display, and how it should look.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <a href="<?php echo esc_url( $new_preset_url ); ?>" class="button button-primary button-small"><?php esc_html_e( 'Create Preset', 'nivo-ajax-search-for-woocommerce' ); ?></a>
                    </div>
                </div>
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">2</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Copy Your Shortcode', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'After publishing your preset, a shortcode is generated automatically.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                        <code class="nivo-gs-code">[nivo_search id="<?php echo esc_attr( $default_preset ); ?>"]</code>
                    </div>
                </div>
                <div class="nivo-gs-step">
                    <span class="nivo-gs-step__num">3</span>
                    <div class="nivo-gs-step__body">
                        <strong><?php esc_html_e( 'Place It Anywhere', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Paste the shortcode into any page, post, or widget. Or use the NivoSearch block in the Gutenberg editor. To replace your theme\'s search form automatically, go to Settings → Integrations and enable "Auto-replace theme search form".', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Documentation & Resources ─────────────────────────────── -->
        <div class="nivo-card nivo-help-resources">
            <div class="nivo-help-resources__grid">

                <a href="https://nivosearch.com/documentation/" target="_blank" rel="noopener noreferrer" class="nivo-help-resource">
                    <span class="nivo-help-resource__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Official Documentation', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <span><?php esc_html_e( 'Full guides and reference', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </div>
                    <svg class="nivo-help-resource__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </a>

                <a href="https://nivosearch.com/docs/getting-started/" target="_blank" rel="noopener noreferrer" class="nivo-help-resource">
                    <span class="nivo-help-resource__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Getting Started Guide', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <span><?php esc_html_e( 'Plugin intro and requirements', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </div>
                    <svg class="nivo-help-resource__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </a>

                <a href="https://nivosearch.com/live-demo-woocommerce-product-search/" target="_blank" rel="noopener noreferrer" class="nivo-help-resource">
                    <span class="nivo-help-resource__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'Live Demo', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <span><?php esc_html_e( 'See NivoSearch in action', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </div>
                    <svg class="nivo-help-resource__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </a>

                <a href="https://wordpress.org/plugins/nivo-ajax-search-for-woocommerce/" target="_blank" rel="noopener noreferrer" class="nivo-help-resource">
                    <span class="nivo-help-resource__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </span>
                    <div>
                        <strong><?php esc_html_e( 'WordPress.org Page', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <span><?php esc_html_e( 'Reviews, support forum', 'nivo-ajax-search-for-woocommerce' ); ?></span>
                    </div>
                    <svg class="nivo-help-resource__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </a>

            </div>
        </div>

        <!-- ── How to add the search bar ─────────────────────────────── -->
        <div class="nivo-card nivo-docs-card">
            <div class="nivo-docs-card__header">
                <span class="nivo-docs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </span>
                <h2 class="nivo-docs-card__title"><?php esc_html_e( 'Adding the Search Bar to Your Site', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>

            <div class="nivo-docs-methods">

                <div class="nivo-docs-method">
                    <h3 class="nivo-docs-method__title">
                        <span class="nivo-docs-method__badge">A</span>
                        <?php esc_html_e( 'Using a Shortcode (works everywhere)', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </h3>
                    <p><?php esc_html_e( 'Copy your preset\'s shortcode and paste it into any page, post, or text widget.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    <div class="nivo-docs-code"><code>[nivo_search id="<?php echo esc_attr( $default_preset ); ?>"]</code></div>
                    <p class="nivo-docs-tip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php esc_html_e( 'Find the ID number on your preset\'s edit page  -  it\'s shown in the shortcode box on the right-hand side.', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </p>
                </div>

                <div class="nivo-docs-method">
                    <h3 class="nivo-docs-method__title">
                        <span class="nivo-docs-method__badge">B</span>
                        <?php esc_html_e( 'Using the Gutenberg Block', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </h3>
                    <p><?php esc_html_e( 'Edit any page in the block editor and search for "NivoSearch" in the block inserter. Select your preset from the dropdown  -  no shortcode needed.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </div>

                <div class="nivo-docs-method">
                    <h3 class="nivo-docs-method__title">
                        <span class="nivo-docs-method__badge">C</span>
                        <?php esc_html_e( 'In Your Header or Navigation', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </h3>
                    <p><?php esc_html_e( 'Most themes let you add HTML or shortcodes to the header or menu area. Go to Appearance → Widgets (or your theme\'s header options) and add a "Custom HTML" widget with your shortcode.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    <p class="nivo-docs-tip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php esc_html_e( 'For theme builders like Elementor or Divi, use a "Shortcode" element and paste the code there.', 'nivo-ajax-search-for-woocommerce' ); ?>
                    </p>
                </div>

            </div>
        </div>

        <!-- ── Configuring your preset ───────────────────────────────── -->
        <div class="nivo-card nivo-docs-card">
            <div class="nivo-docs-card__header">
                <span class="nivo-docs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                </span>
                <h2 class="nivo-docs-card__title"><?php esc_html_e( 'Configuring Your Search Preset', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>

            <div class="nivo-docs-sections">

                <div class="nivo-docs-section">
                    <h3><?php esc_html_e( 'General Settings', 'nivo-ajax-search-for-woocommerce' ); ?></h3>
                    <table class="nivo-docs-table">
                        <thead><tr><th><?php esc_html_e( 'Setting', 'nivo-ajax-search-for-woocommerce' ); ?></th><th><?php esc_html_e( 'What it does', 'nivo-ajax-search-for-woocommerce' ); ?></th></tr></thead>
                        <tbody>
                            <tr><td><strong><?php esc_html_e( 'Results Limit', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'How many products appear in the dropdown. 5-10 is ideal for most stores.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Min. Characters', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Search only fires after this many characters are typed. 2-3 is recommended to avoid too-broad results.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Placeholder Text', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The hint text shown inside the search box before the customer starts typing.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Search Delay', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'How long (in milliseconds) to wait after the customer stops typing before firing a search. 300 ms is a good balance.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="nivo-docs-section">
                    <h3><?php esc_html_e( 'What to Search', 'nivo-ajax-search-for-woocommerce' ); ?></h3>
                    <table class="nivo-docs-table">
                        <thead><tr><th><?php esc_html_e( 'Option', 'nivo-ajax-search-for-woocommerce' ); ?></th><th><?php esc_html_e( 'What it searches', 'nivo-ajax-search-for-woocommerce' ); ?></th></tr></thead>
                        <tbody>
                            <tr><td><strong><?php esc_html_e( 'Product Title', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The main name of the product as shown in your store.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Short Description', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The brief summary shown on product listings and in the cart.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Full Description', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The long product description. Useful for matching keywords buried in detailed specs.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'SKU', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Your internal product code. Useful for B2B customers who know exact part numbers.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Categories & Tags', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Returns products matching the searched category or tag  -  great for browsing searches like "shirts" or "gifts".', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Attributes', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Searches product variation attributes such as colour, size, or material.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'GTIN / Barcode', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Matches UPC, EAN, ISBN, or other barcode values stored in product meta.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="nivo-docs-section">
                    <h3><?php esc_html_e( 'What to Show in Results', 'nivo-ajax-search-for-woocommerce' ); ?></h3>
                    <table class="nivo-docs-table">
                        <thead><tr><th><?php esc_html_e( 'Option', 'nivo-ajax-search-for-woocommerce' ); ?></th><th><?php esc_html_e( 'Shows in each result', 'nivo-ajax-search-for-woocommerce' ); ?></th></tr></thead>
                        <tbody>
                            <tr><td><strong><?php esc_html_e( 'Product Image', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The featured photo thumbnail on the left side of the result.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Price', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Regular price or sale price (with strikethrough). Variable products show a "from" price.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Short Description', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'A brief excerpt below the product title.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'SKU', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'Your internal product code shown next to the title.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Stock Status', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'A badge showing "In Stock", "Out of Stock", or "On Backorder".', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Category Badge', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'The product\'s first category shown as a label.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Add to Cart', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'A button to add the product to the cart without leaving the page.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'Quantity Selector', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'A ＋/− quantity field next to the Add to Cart button.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                            <tr><td><strong><?php esc_html_e( 'View All Results', 'nivo-ajax-search-for-woocommerce' ); ?></strong></td><td><?php esc_html_e( 'A link at the bottom of the dropdown that takes the customer to the full WooCommerce search results page.', 'nivo-ajax-search-for-woocommerce' ); ?></td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- ── Typo correction ───────────────────────────────────────── -->
        <div class="nivo-card nivo-docs-card">
            <div class="nivo-docs-card__header">
                <span class="nivo-docs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </span>
                <h2 class="nivo-docs-card__title"><?php esc_html_e( 'Typo Correction & "Did You Mean?"', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>
            <p><?php esc_html_e( 'NivoSearch automatically fixes common spelling mistakes before running the search so customers always get results even when they mistype.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
            <div class="nivo-docs-qa">
                <div class="nivo-docs-qa__item">
                    <h4><?php esc_html_e( 'How does it work?', 'nivo-ajax-search-for-woocommerce' ); ?></h4>
                    <p><?php esc_html_e( 'The plugin ships with over 300 built-in corrections for common e-commerce misspellings. If a customer types a word that matches a correction, the corrected word is used for the search. A "Did you mean: [word]?" link appears so the customer knows what happened.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </div>
                <div class="nivo-docs-qa__item">
                    <h4><?php esc_html_e( 'How do I add my own corrections?', 'nivo-ajax-search-for-woocommerce' ); ?></h4>
                    <p>
                        <?php echo wp_kses(
                            sprintf(
                                /* translators: %s is a link to the Search Optimization tab */
                                __( 'Go to the <a href="%s">Search Optimization tab</a> and use "Add Custom Rule" to map any misspelling to the correct word. For example, map "beutiful" → "beautiful".', 'nivo-ajax-search-for-woocommerce' ),
                                esc_url( $opt_url )
                            ),
                            array( 'a' => array( 'href' => array() ) )
                        ); ?>
                    </p>
                </div>
                <div class="nivo-docs-qa__item">
                    <h4><?php esc_html_e( 'What is "fuzzy search"?', 'nivo-ajax-search-for-woocommerce' ); ?></h4>
                    <p><?php esc_html_e( 'Fuzzy search catches typos that aren\'t in the dictionary. If a customer types something close to a product name  -  like "samsang"  -  the search finds "Samsung" anyway by checking how similar the words look. You can adjust the sensitivity in the Search Optimization settings.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </div>
            </div>
        </div>

        <!-- ── Tips for better results ───────────────────────────────── -->
        <div class="nivo-card nivo-docs-card">
            <div class="nivo-docs-card__header">
                <span class="nivo-docs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                </span>
                <h2 class="nivo-docs-card__title"><?php esc_html_e( 'Tips for Better Search Results', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>
            <div class="nivo-docs-tips">
                <div class="nivo-docs-tip-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <div>
                        <strong><?php esc_html_e( 'Write detailed product titles', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Customers search the way they think. Include brand, material, colour, and size in the title so searches like "red cotton hoodie" return the right result.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
                <div class="nivo-docs-tip-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <div>
                        <strong><?php esc_html_e( 'Use short descriptions wisely', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'The short description is shown in search results and indexed for searching. Put your most important keywords here  -  use terms a customer would actually type.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
                <div class="nivo-docs-tip-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <div>
                        <strong><?php esc_html_e( 'Tag products consistently', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Enable "Search product tags" to let customers find products by theme  -  for example, searching "birthday" to find all birthday-related products across different categories.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
                <div class="nivo-docs-tip-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <div>
                        <strong><?php esc_html_e( 'Add custom typo rules', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Use the Typo Rules tab to add up to 10 custom corrections for brand names or product-specific spellings your customers commonly mistype.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
                <div class="nivo-docs-tip-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <div>
                        <strong><?php esc_html_e( 'Exclude out-of-stock products', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
                        <p><?php esc_html_e( 'Enable "Exclude out-of-stock products" in your preset to keep search results frustration-free  -  customers won\'t see products they can\'t buy.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── FAQ ───────────────────────────────────────────────────── -->
        <div class="nivo-card nivo-docs-card">
            <div class="nivo-docs-card__header">
                <span class="nivo-docs-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <h2 class="nivo-docs-card__title"><?php esc_html_e( 'Frequently Asked Questions', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
            </div>
            <div class="nivo-docs-faq">
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'The search bar isn\'t showing on my page  -  what should I check?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'First confirm the shortcode or block is saved correctly, and that the preset is published (not in draft). Then check that your theme doesn\'t have a custom search bar that\'s overriding it.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'Can I have a different search bar on different pages?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'Yes  -  create a separate preset for each page. For example, a "Header Search" preset with compact results and a "Shop Page Search" preset that shows more detail. Each gets its own shortcode.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'Why do some products not appear in results?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'Check that: (1) "Exclude out-of-stock" is not enabled if those products are out of stock, (2) the product is published and not in draft/private status, (3) the search term you\'re testing matches a word in the fields you have enabled (title, description, SKU, etc.).', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'Will NivoSearch work with my page builder (Elementor, Divi, etc.)?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'Yes. Use a Shortcode element in any page builder and paste the [nivo_search] shortcode. Most builders also let you use the Gutenberg block in hybrid layouts.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'Does it slow down my site?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'No. The plugin loads a small JavaScript file only on pages where you\'ve placed the search bar. Searches are cached for 5 minutes and reset automatically when products change, so repeat queries are near-instant.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
                <details class="nivo-docs-faq__item">
                    <summary><?php esc_html_e( 'How do I update the styling to match my theme?', 'nivo-ajax-search-for-woocommerce' ); ?></summary>
                    <p><?php esc_html_e( 'Open your preset and scroll to the "Styling" section. You can set colours, widths, and border styles from there without writing any code. For advanced customisation, you can add CSS to your theme\'s stylesheet  -  all elements use .nivo-* class names.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
                </details>
            </div>
        </div>

        <?php
    }
}
