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

$log_table = $wpdb->prefix . 'nivo_search_log';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $log_table ) ) === $log_table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( "DROP TABLE IF EXISTS `{$log_table}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
