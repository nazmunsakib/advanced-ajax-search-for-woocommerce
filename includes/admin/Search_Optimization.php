<?php
/**
 * Search Optimization Admin Page
 *
 * Provides the UI for managing custom typo-correction rules, viewing analytics,
 * and bulk-importing / exporting the correction dictionary.
 *
 * Submenu slug: nivo-search-optimization
 * Parent menu : nivo-search
 *
 * @package NivoSearch
 * @since   2.2.0
 */

namespace NivoSearch;

defined( 'ABSPATH' ) || exit;

/**
 * Search_Optimization class
 *
 * @since 2.2.0
 */
class Search_Optimization {

	/**
	 * Nonce action for the add/edit rule form.
	 *
	 * @since 2.2.0
	 */
	const NONCE_RULE = 'nivo_save_typo_rule';

	/**
	 * Nonce action for the bulk import form.
	 *
	 * @since 2.2.0
	 */
	const NONCE_IMPORT = 'nivo_import_typo_rules';

	/**
	 * Nonce action for admin AJAX (delete rule, clear analytics).
	 *
	 * @since 2.2.0
	 */
	const NONCE_AJAX = 'nivo_optimization_ajax';

	/**
	 * Register hooks.
	 *
	 * @since 2.2.0
	 */
	public function __construct() {
		add_action( 'nivo_render_optimization_tab',    array( $this, 'render_tab_content' ) );
		add_action( 'admin_init',                      array( $this, 'handle_form_posts' ) );
		add_action( 'admin_init',                      array( $this, 'handle_export' ) );
		add_action( 'wp_ajax_nivo_delete_typo_rule', array( $this, 'ajax_delete_rule' ) );
	}

	// -------------------------------------------------------------------------
	// Form POST handlers (redirect-after-POST pattern)
	// -------------------------------------------------------------------------

	/**
	 * Handle add/edit rule and bulk import form submissions.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function handle_form_posts() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// --- Add / Edit rule ---
		if ( isset( $_POST['nivo_save_typo_rule'], $_POST['_nivo_rule_nonce'] ) ) {
			check_admin_referer( self::NONCE_RULE, '_nivo_rule_nonce' );

			$from  = sanitize_text_field( wp_unslash( $_POST['nivo_rule_from'] ?? '' ) );
			$to    = sanitize_text_field( wp_unslash( $_POST['nivo_rule_to'] ?? '' ) );
			$index = isset( $_POST['nivo_rule_index'] ) && '' !== $_POST['nivo_rule_index']
				? (int) wp_unslash( $_POST['nivo_rule_index'] )
				: null;

			$saved = Typo_Manager::save_rule( $from, $to, $index );

			if ( 'limit_reached' === $saved ) {
				$notice = 'rule-limit';
			} elseif ( $saved ) {
				$notice = 'rule-saved';
			} else {
				$notice = 'rule-error';
			}

			wp_safe_redirect( add_query_arg( array(
				'page'   => 'nivo-search',
				'tab'    => 'typo-rules',
				'notice' => $notice,
			), admin_url( 'admin.php' ) ) );
			exit;
		}

		// --- Bulk import ---
		if ( isset( $_POST['nivo_import_rules'], $_POST['_nivo_import_nonce'] ) ) {
			check_admin_referer( self::NONCE_IMPORT, '_nivo_import_nonce' );

			$raw_text  = sanitize_textarea_field( wp_unslash( $_POST['nivo_import_text'] ?? '' ) );
			$overwrite = isset( $_POST['nivo_import_overwrite'] );
			$result    = Typo_Manager::import_rules( $raw_text, $overwrite );

			wp_safe_redirect( add_query_arg( array(
				'page'          => 'nivo-search',
				'tab'           => 'typo-rules',
				'notice'        => 'imported',
				'imported'      => $result['imported'],
				'errors'        => $result['errors'],
				'limit_reached' => $result['limit_reached'],
			), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/**
	 * Handle CSV export download (GET request with action=nivo_export_rules).
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function handle_export() {
		if ( ! isset( $_GET['action'], $_GET['_nivo_export_nonce'] ) ) {
			return;
		}
		if ( 'nivo_export_rules' !== sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_nivo_export_nonce'] ) ), 'nivo_export_rules' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'nivo-ajax-search-for-woocommerce' ) );
		}

		$csv = Typo_Manager::export_rules_csv();

		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="nivo-typo-rules-' . gmdate( 'Y-m-d' ) . '.txt"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $csv;
		exit;
	}

	// -------------------------------------------------------------------------
	// Admin AJAX handlers
	// -------------------------------------------------------------------------

	/**
	 * AJAX: delete a single custom rule.
	 *
	 * Expects: nonce, index (int).
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function ajax_delete_rule() {
		check_ajax_referer( self::NONCE_AJAX, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'nivo-ajax-search-for-woocommerce' ) ), 403 );
		}

		$index   = isset( $_POST['index'] ) ? (int) $_POST['index'] : -1;
		$deleted = Typo_Manager::delete_rule( $index );

		if ( $deleted ) {
			wp_send_json_success( array(
				'message'      => __( 'Rule deleted.', 'nivo-ajax-search-for-woocommerce' ),
				'custom_count' => Typo_Manager::get_custom_count(),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Rule not found.', 'nivo-ajax-search-for-woocommerce' ) ), 404 );
		}
	}

	// -------------------------------------------------------------------------
	// Page render
	// -------------------------------------------------------------------------

	/**
	 * Render the Search Optimization tab content (called via nivo_render_optimization_tab action).
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function render_tab_content() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Collect data.
		$built_in_count  = Typo_Manager::get_built_in_count();
		$custom_count    = Typo_Manager::get_custom_count();
		$custom_rules    = Typo_Manager::get_custom_rules();


		// Rule being edited (passed back via query arg).
		$edit_index = isset( $_GET['edit_index'] ) ? (int) $_GET['edit_index'] : null;
		$edit_rule  = ( null !== $edit_index && isset( $custom_rules[ $edit_index ] ) ) ? $custom_rules[ $edit_index ] : null;

		// Filter rules by search term.
		$rules_search   = isset( $_GET['rules_search'] ) ? sanitize_text_field( wp_unslash( $_GET['rules_search'] ) ) : '';
		$filtered_rules = $custom_rules;
		if ( '' !== $rules_search ) {
			$filtered_rules = array_filter( $custom_rules, function( $rule ) use ( $rules_search ) {
				return false !== stripos( $rule['from'], $rules_search )
					|| false !== stripos( $rule['to'], $rules_search );
			} );
		}

		// Export URL.
		$export_url = add_query_arg( array(
			'action'             => 'nivo_export_rules',
			'_nivo_export_nonce' => wp_create_nonce( 'nivo_export_rules' ),
		), admin_url( 'admin.php' ) );

		// Admin AJAX nonce for JS.
		$ajax_nonce = wp_create_nonce( self::NONCE_AJAX );

		// Notice handling.
		$notice   = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
		$imported = isset( $_GET['imported'] ) ? (int) $_GET['imported'] : 0;
		$errors   = isset( $_GET['errors'] ) ? (int) $_GET['errors'] : 0;

		// Free-tier rule limit.
		$rules_limit    = Typo_Manager::get_rules_limit();
		$at_limit       = Typo_Manager::is_at_limit();
		$rules_pct      = $rules_limit > 0 ? min( 100, (int) round( $custom_count / $rules_limit * 100 ) ) : 100;

		?>
		<div>

			<?php $this->render_notice( $notice, $imported, $errors ); ?>

			<!-- ── Card 1: Dictionary Overview ───────────────────────────── -->
			<div class="nivo-card nivo-accuracy-card">
				<div class="nivo-dict-compact">
					<div class="nivo-dict-stat">
						<span class="nivo-dict-stat__value"><?php echo esc_html( number_format_i18n( $built_in_count ) ); ?></span>
						<span class="nivo-dict-stat__label"><?php esc_html_e( 'Built-in rules', 'nivo-ajax-search-for-woocommerce' ); ?></span>
					</div>
					<div class="nivo-dict-stat">
						<span class="nivo-dict-stat__value <?php echo $at_limit ? 'nivo-dict-stat__value--warn' : ''; ?>" id="nivo-custom-count">
							<?php echo esc_html( number_format_i18n( $custom_count ) ); ?><span class="nivo-dict-stat__of">&thinsp;/&thinsp;<?php echo esc_html( number_format_i18n( $rules_limit ) ); ?></span>
						</span>
						<span class="nivo-dict-stat__label"><?php esc_html_e( 'Custom rules', 'nivo-ajax-search-for-woocommerce' ); ?></span>
						<div class="nivo-rules-limit-bar nivo-dict-stat__bar">
							<div class="nivo-rules-limit-bar__fill <?php echo $at_limit ? 'nivo-rules-limit-bar__fill--full' : ''; ?>"
								style="width:<?php echo esc_attr( $rules_pct ); ?>%"></div>
						</div>
					</div>
					<div class="nivo-dict-stat">
						<span class="nivo-dict-stat__value"><?php echo esc_html( number_format_i18n( $built_in_count + $custom_count ) ); ?></span>
						<span class="nivo-dict-stat__label"><?php esc_html_e( 'Total size', 'nivo-ajax-search-for-woocommerce' ); ?></span>
					</div>
					<div class="nivo-dict-stat nivo-dict-stat--action">
						<?php if ( $at_limit ) : ?>
						<span class="nivo-rules-limit-label nivo-rules-limit-label--warn" style="font-size:11px;margin-top:4px">
							<?php esc_html_e( 'Limit reached', 'nivo-ajax-search-for-woocommerce' ); ?>
						</span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- ── Card 2: Add / Edit Rule ───────────────────────────────── -->
			<div class="nivo-card nivo-accuracy-card">
				<div class="nivo-settings-card__header">
					<span class="nivo-settings-card__icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
					</span>
					<div>
						<h2 class="nivo-settings-card__title">
							<?php echo $edit_rule
								? esc_html__( 'Edit Rule', 'nivo-ajax-search-for-woocommerce' )
								: esc_html__( 'Add Custom Rule', 'nivo-ajax-search-for-woocommerce' ); ?>
						</h2>
						<p class="nivo-settings-card__sub"><?php esc_html_e( 'Add a misspelling and its correction. The search engine will apply it automatically before every query.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
					</div>
				</div>

				<?php if ( $at_limit && ! $edit_rule ) : ?>
				<div class="nivo-setting-row" style="border-bottom:none;padding-bottom:0">
					<div class="nivo-setting-row__info">
						<p class="nivo-setting-row__desc nivo-rules-limit-label--warn" style="margin:0">
							<?php
							printf(
								/* translators: %d: rule limit */
								esc_html__( 'You have reached the %d-rule free-plan limit. Upgrade to Pro to add more rules.', 'nivo-ajax-search-for-woocommerce' ),
								esc_html( $rules_limit )
							);
							?>
						</p>
					</div>
				</div>
				<?php else : ?>
				<form method="post" action="">
					<?php wp_nonce_field( self::NONCE_RULE, '_nivo_rule_nonce' ); ?>
					<?php if ( null !== $edit_index ) : ?>
						<input type="hidden" name="nivo_rule_index" value="<?php echo esc_attr( $edit_index ); ?>">
					<?php endif; ?>

					<div class="nivo-rule-row" style="padding:16px 20px">
						<div class="nivo-rule-field">
							<label for="nivo_rule_from"><?php esc_html_e( 'Misspelling', 'nivo-ajax-search-for-woocommerce' ); ?></label>
							<input type="text" id="nivo_rule_from" name="nivo_rule_from"
								value="<?php echo $edit_rule ? esc_attr( $edit_rule['from'] ) : ''; ?>"
								placeholder="<?php esc_attr_e( 'e.g. nikee', 'nivo-ajax-search-for-woocommerce' ); ?>"
								class="widefat" required>
						</div>
						<div class="nivo-rule-field">
							<label for="nivo_rule_to"><?php esc_html_e( 'Correction', 'nivo-ajax-search-for-woocommerce' ); ?></label>
							<input type="text" id="nivo_rule_to" name="nivo_rule_to"
								value="<?php echo $edit_rule ? esc_attr( $edit_rule['to'] ) : ''; ?>"
								placeholder="<?php esc_attr_e( 'e.g. Nike', 'nivo-ajax-search-for-woocommerce' ); ?>"
								class="widefat" required>
						</div>
					</div>

					<div class="nivo-settings-footer nivo-card-footer" style="border-top:none">
						<button type="submit" name="nivo_save_typo_rule" class="button button-primary">
							<?php echo $edit_rule
								? esc_html__( 'Update Rule', 'nivo-ajax-search-for-woocommerce' )
								: esc_html__( 'Add Rule', 'nivo-ajax-search-for-woocommerce' ); ?>
						</button>
						<?php if ( $edit_rule ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=nivo-search&tab=typo-rules' ) ); ?>" class="button">
							<?php esc_html_e( 'Cancel', 'nivo-ajax-search-for-woocommerce' ); ?>
						</a>
						<?php endif; ?>
					</div>
				</form>
				<?php endif; ?>
			</div>

			<!-- ── Card 3: Bulk Import ───────────────────────────────────── -->
			<div class="nivo-card nivo-accuracy-card">
				<div class="nivo-settings-card__header">
					<span class="nivo-settings-card__icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>
					</span>
					<div>
						<h2 class="nivo-settings-card__title"><?php esc_html_e( 'Bulk Import', 'nivo-ajax-search-for-woocommerce' ); ?></h2>
						<p class="nivo-settings-card__sub">
							<?php esc_html_e( 'One rule per line. Supported formats:', 'nivo-ajax-search-for-woocommerce' ); ?>
							<code>misspelling =&gt; correction</code>,
							<code>misspelling -&gt; correction</code>,
							<code>misspelling,correction</code>.
							<?php esc_html_e( 'Lines starting with # are comments.', 'nivo-ajax-search-for-woocommerce' ); ?>
						</p>
					</div>
				</div>

				<form method="post" action="">
					<?php wp_nonce_field( self::NONCE_IMPORT, '_nivo_import_nonce' ); ?>

					<div style="padding:4px 20px 16px">
						<textarea name="nivo_import_text" id="nivo_import_text" rows="6"
							class="widefat code"
							style="font-size:12px;resize:vertical"
							placeholder="# Example&#10;nikee => Nike&#10;samsng => Samsung"></textarea>
					</div>

					<div class="nivo-setting-row">
						<div class="nivo-setting-row__info">
							<strong class="nivo-setting-row__title"><?php esc_html_e( 'Replace existing custom rules', 'nivo-ajax-search-for-woocommerce' ); ?></strong>
							<p class="nivo-setting-row__desc"><?php esc_html_e( 'When enabled, all current custom rules are deleted before importing.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
						</div>
						<div class="nivo-setting-row__control">
							<label class="nivo-gs-toggle">
								<input type="checkbox" name="nivo_import_overwrite" value="1">
								<span class="nivo-gs-toggle__slider"></span>
							</label>
						</div>
					</div>

					<div class="nivo-settings-footer nivo-card-footer" style="border-top:none">
						<button type="submit" name="nivo_import_rules" class="button button-primary">
							<?php esc_html_e( 'Import Rules', 'nivo-ajax-search-for-woocommerce' ); ?>
						</button>
						<a href="<?php echo esc_url( $export_url ); ?>" class="button">
							<?php esc_html_e( 'Export Rules', 'nivo-ajax-search-for-woocommerce' ); ?>
						</a>
					</div>
				</form>
			</div>

			<!-- ── Card 4: Custom Rules Table ────────────────────────────── -->
			<div class="nivo-card nivo-accuracy-card">
				<div class="nivo-settings-card__header" style="align-items:center">
					<span class="nivo-settings-card__icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
					</span>
					<div style="flex:1;min-width:0">
						<h2 class="nivo-settings-card__title" style="margin:0;display:flex;align-items:center;gap:8px">
							<?php esc_html_e( 'Custom Rules', 'nivo-ajax-search-for-woocommerce' ); ?>
							<span class="nivo-opt-badge" id="nivo-custom-badge"><?php echo esc_html( $custom_count ); ?></span>
						</h2>
					</div>
					<input type="text" id="nivo-rules-search"
						placeholder="<?php esc_attr_e( 'Search rules…', 'nivo-ajax-search-for-woocommerce' ); ?>"
						class="nivo-opt-search-input"
						value="<?php echo esc_attr( $rules_search ); ?>"
						style="flex-shrink:0">
				</div>

				<?php if ( empty( $custom_rules ) ) : ?>
					<p class="nivo-opt-empty" style="padding:14px 16px;margin:0;font-size:13px;color:#787c82"><?php esc_html_e( 'No custom rules yet. Add one using the form above.', 'nivo-ajax-search-for-woocommerce' ); ?></p>
				<?php else : ?>
					<table class="nivo-rules-table nivo-rules-table--compact" id="nivo-rules-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Misspelling', 'nivo-ajax-search-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Correction', 'nivo-ajax-search-for-woocommerce' ); ?></th>
								<th style="width:110px"><?php esc_html_e( 'Actions', 'nivo-ajax-search-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $custom_rules as $idx => $rule ) : ?>
								<tr data-index="<?php echo esc_attr( $idx ); ?>"
									data-from="<?php echo esc_attr( $rule['from'] ); ?>"
									data-to="<?php echo esc_attr( $rule['to'] ); ?>">
									<td><code class="nivo-code"><?php echo esc_html( $rule['from'] ); ?></code></td>
									<td class="nivo-rules-table__correction"><?php echo esc_html( $rule['to'] ); ?></td>
									<td class="nivo-rules-table__actions">
										<a href="<?php echo esc_url( add_query_arg( array(
											'page'       => 'nivo-search',
											'tab'        => 'typo-rules',
											'edit_index' => $idx,
										), admin_url( 'admin.php' ) ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Edit', 'nivo-ajax-search-for-woocommerce' ); ?>
										</a>
										<button type="button" class="button button-small nivo-delete-rule"
											data-index="<?php echo esc_attr( $idx ); ?>"
											data-nonce="<?php echo esc_attr( $ajax_nonce ); ?>">
											<?php esc_html_e( 'Delete', 'nivo-ajax-search-for-woocommerce' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<script>
			(function($) {
				var ajaxUrl      = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
				var nonce        = <?php echo wp_json_encode( $ajax_nonce ); ?>;
				var deleteConfirm = <?php echo wp_json_encode( __( 'Delete this rule? This cannot be undone.', 'nivo-ajax-search-for-woocommerce' ) ); ?>;
				var PER_PAGE     = 10;

				// ── Pagination engine ─────────────────────────────────────────
				function Paginator(tableId) {
					var $table = $('#' + tableId);
					if ( ! $table.length ) return null;
					var $wrap = $('<div class="nivo-pagination"></div>').insertAfter($table);
					var page  = 1;

					function visible() {
						return $table.find('tbody tr').not('[data-hidden]');
					}

					function render() {
						var $vis   = visible();
						var total  = $vis.length;
						var pages  = Math.max(1, Math.ceil(total / PER_PAGE));
						if (page > pages) page = pages;
						var start  = (page - 1) * PER_PAGE;

						$table.find('tbody tr').hide();
						$vis.slice(start, start + PER_PAGE).show();

						var from  = total === 0 ? 0 : start + 1;
						var to    = Math.min(start + PER_PAGE, total);
						var info  = from + '-' + to + ' of ' + total;

						var html = '<span class="nivo-page-info">' + info + '</span>';
						if (pages > 1) {
							html += '<div class="nivo-page-btns">';
							html += '<button class="button nivo-pprev"' + (page <= 1 ? ' disabled' : '') + '>&larr;</button>';
							// show window of max 7 page numbers
							var lo = Math.max(1, page - 3), hi = Math.min(pages, page + 3);
							if (lo > 1) html += '<button class="button nivo-pnum" data-p="1">1</button>' + (lo > 2 ? '<span class="nivo-hellip">&hellip;</span>' : '');
							for (var i = lo; i <= hi; i++) {
								html += '<button class="button nivo-pnum' + (i === page ? ' nivo-page-active' : '') + '" data-p="' + i + '">' + i + '</button>';
							}
							if (hi < pages) html += (hi < pages - 1 ? '<span class="nivo-hellip">&hellip;</span>' : '') + '<button class="button nivo-pnum" data-p="' + pages + '">' + pages + '</button>';
							html += '<button class="button nivo-pnext"' + (page >= pages ? ' disabled' : '') + '>&rarr;</button>';
							html += '</div>';
						}
						$wrap.html(html);

						$wrap.find('.nivo-pprev').on('click', function() { if (page > 1) { page--; render(); } });
						$wrap.find('.nivo-pnext').on('click', function() { if (page < pages) { page++; render(); } });
						$wrap.find('.nivo-pnum').on('click', function() { page = parseInt($(this).data('p')); render(); });
					}

					render();
					return {
						reset: function() { page = 1; render(); },
						render: render
					};
				}

				var rulesPager = Paginator('nivo-rules-table');

				// ── Search filter (works with pagination) ─────────────────────
				$('#nivo-rules-search').on('input', function() {
					var val = $(this).val().toLowerCase();
					$('#nivo-rules-table tbody tr').each(function() {
						var match = ($(this).data('from') || '').toLowerCase().indexOf(val) !== -1
							     || ($(this).data('to')   || '').toLowerCase().indexOf(val) !== -1;
						$(this).attr('data-hidden', match ? null : '1');
					});
					if (rulesPager) rulesPager.reset();
				});

				// ── Delete rule ───────────────────────────────────────────────
				$(document).on('click', '.nivo-delete-rule', function() {
					if ( ! confirm(deleteConfirm) ) return;
					var btn = $(this), row = btn.closest('tr');
					btn.prop('disabled', true);
					$.post(ajaxUrl, { action: 'nivo_delete_typo_rule', nonce: nonce, index: btn.data('index') }, function(res) {
						if (res.success) {
							row.fadeOut(200, function() {
								$(this).remove();
								if (rulesPager) rulesPager.render();
							});
							if (res.data && res.data.custom_count !== undefined) {
								$('#nivo-custom-count, #nivo-custom-badge').text(res.data.custom_count);
							}
						} else {
							alert(res.data && res.data.message ? res.data.message : 'Error deleting rule.');
							btn.prop('disabled', false);
						}
					});
				});


				// ── Pre-fill form from analytics Add Rule link ────────────────
				var urlParams   = new URLSearchParams(window.location.search);
				var prefillFrom = urlParams.get('nivo_rule_from');
				var prefillTo   = urlParams.get('nivo_rule_to');
				if (prefillFrom) { $('#nivo_rule_from').val(decodeURIComponent(prefillFrom)); }
				if (prefillTo)   { $('#nivo_rule_to').val(decodeURIComponent(prefillTo)); }

			}(jQuery));
			</script>

		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Render an admin notice from a query-arg notice code.
	 *
	 * @since 2.2.0
	 * @param string $notice   Notice key from redirect.
	 * @param int    $imported Number of rules imported (used for 'imported' notice).
	 * @param int    $errors   Number of import errors (used for 'imported' notice).
	 * @return void
	 */
	private function render_notice( $notice, $imported, $errors ) {
		if ( '' === $notice ) {
			return;
		}

		$limit = Typo_Manager::get_rules_limit();

		// Icon SVGs per type.
		$icons = array(
			'success' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>',
			'error'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
			'warning' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
		);

		$messages = array(
			'rule-saved' => array(
				'type' => 'success',
				'text' => __( 'Rule saved successfully.', 'nivo-ajax-search-for-woocommerce' ),
			),
			'rule-error' => array(
				'type' => 'error',
				'text' => __( 'Could not save rule. Please check that both fields are filled.', 'nivo-ajax-search-for-woocommerce' ),
			),
			'rule-limit' => array(
				'type' => 'warning',
				/* translators: %d: maximum rules allowed in free tier */
				'text' => sprintf(
					__( 'You have reached the %d custom rule limit on the free plan. Upgrade to NivoSearch Pro for unlimited rules.', 'nivo-ajax-search-for-woocommerce' ),
					$limit
				),
			),
			'settings-saved' => array(
				'type' => 'success',
				'text' => __( 'Settings saved.', 'nivo-ajax-search-for-woocommerce' ),
			),
		);

		if ( 'imported' === $notice ) {
			$limit_reached = (int) ( isset( $_GET['limit_reached'] ) ? $_GET['limit_reached'] : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $limit_reached > 0 ) {
				$text = sprintf(
					/* translators: 1: rules imported, 2: rules skipped due to limit, 3: max rules */
					__( 'Import stopped: %1$d rules imported. %2$d rules were not imported because you reached the %3$d-rule free-plan limit. Upgrade to Pro for unlimited rules.', 'nivo-ajax-search-for-woocommerce' ),
					$imported,
					$limit_reached,
					$limit
				);
				$type = 'warning';
			} else {
				$text = sprintf(
					/* translators: 1: number of rules imported, 2: number of errors */
					__( 'Import complete: %1$d rules imported, %2$d errors.', 'nivo-ajax-search-for-woocommerce' ),
					$imported,
					$errors
				);
				$type = $errors > 0 ? 'warning' : 'success';
			}
			$messages['imported'] = array( 'type' => $type, 'text' => $text );
		}

		if ( ! isset( $messages[ $notice ] ) ) {
			return;
		}

		$msg  = $messages[ $notice ];
		$type = $msg['type'];
		$icon = isset( $icons[ $type ] ) ? $icons[ $type ] : $icons['success'];

		// Strip all notice-related params from the URL so a refresh won't re-show it.
		?>
		<div class="nivo-saved-notice nivo-saved-notice--<?php echo esc_attr( $type ); ?>">
			<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( $msg['text'] ); ?>
		</div>
		<script>
		(function() {
			if ( window.history && window.history.replaceState ) {
				var url = new URL( window.location.href );
				['notice','imported','errors','limit_reached','edit_index'].forEach(function(p){ url.searchParams.delete(p); });
				window.history.replaceState( {}, '', url.toString() );
			}
		})();
		</script>
		<?php
	}
}
