<?php
/**
 * Plugin Uninstall
 *
 * Runs when the user deletes the plugin via the WordPress admin.
 *
 * DATA SAFETY: By default, all preset data is KEPT so users can reinstall
 * without losing their configuration. Data is only deleted when the user
 * has explicitly enabled "Delete all data on uninstall" in NivoSearch Settings.
 *
 * Transients are always removed (they are cache data, not user data).
 *
 * @package NivoSearch
 * @since 1.2.0
 */

// Guard: only run when WordPress is uninstalling this plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// ------------------------------------------------------------------
// Always: remove transients (cache data, safe to delete).
// ------------------------------------------------------------------
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_nivo_search_%'
	    OR option_name LIKE '_transient_timeout_nivo_search_%'"
);

// ------------------------------------------------------------------
// Stop here unless the user has explicitly opted in to data deletion.
// ------------------------------------------------------------------
$delete_data = get_option( 'nivo_search_delete_data_on_uninstall', 'no' );

if ( 'yes' !== $delete_data ) {
	// User data (presets, settings) is intentionally preserved.
	// Reinstalling the plugin will restore full functionality.
	return;
}

// ------------------------------------------------------------------
// User opted in: delete all preset posts and their postmeta.
// ------------------------------------------------------------------
$preset_ids = $wpdb->get_col(
	"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'nivo_search_preset'"
);

if ( ! empty( $preset_ids ) ) {
	$ids_placeholder = implode( ',', array_map( 'intval', $preset_ids ) );

	// Delete postmeta first to avoid orphaned rows.
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$ids_placeholder})"
	);

	// Delete the CPT posts.
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->posts} WHERE ID IN ({$ids_placeholder})"
	);
}

// ------------------------------------------------------------------
// Delete plugin options.
// ------------------------------------------------------------------
$options = array(
	'nivo_search_default_preset_created',
	'nivo_search_enable_ajax',
	'nivo_search_excluded_products',
	'nivo_search_db_version',
	'nivo_search_delete_data_on_uninstall',
	'nivo_search_cache_ver',
	// Typo correction system (v2.2.0).
	'nivo_search_custom_typo_rules',
	'nivo_search_enable_fuzzy_search',
	'nivo_search_enable_typo_tolerance',
	'nivo_search_max_typo_distance',
	'nivo_search_show_did_you_mean',
	// Integration settings (v2.3.0).
	'nivo_search_results_page',
	'nivo_search_auto_replace',
	'nivo_search_theme_preset_id',
	'nivo_search_ga_tracking',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// ------------------------------------------------------------------
// Drop custom tables (if index engine table was created in Phase 4).
// ------------------------------------------------------------------
$index_table = $wpdb->prefix . 'nivo_search_index';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $index_table ) ) === $index_table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( "DROP TABLE IF EXISTS `{$index_table}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

// Search correction analytics table (v2.2.0).
$corrections_table = $wpdb->prefix . 'nivo_search_corrections_log';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $corrections_table ) ) === $corrections_table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( "DROP TABLE IF EXISTS `{$corrections_table}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
