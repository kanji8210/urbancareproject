<?php

define( 'ABSPATH', __DIR__ );

$GLOBALS['ucp_test_options'] = array();
$GLOBALS['ucp_test_meta'] = array(
	1 => array( '_ucp_activity_date' => '2025-09-01', '_ucp_start_date' => '' ),
	2 => array( '_ucp_activity_date' => '2025-02-01', '_ucp_start_date' => '2026-01-01' ),
	3 => array( '_ucp_activity_date' => 'not-a-date', '_ucp_start_date' => '' ),
);
$GLOBALS['ucp_test_updates'] = array();

function __( $text ) {
	return $text;
}

function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}

function get_option( $key, $default = '' ) {
	return isset( $GLOBALS['ucp_test_options'][ $key ] ) ? $GLOBALS['ucp_test_options'][ $key ] : $default;
}

function update_option( $key, $value ) {
	$GLOBALS['ucp_test_options'][ $key ] = $value;
}

function get_posts() {
	return array( 1, 2, 3 );
}

function get_post_meta( $post_id, $key ) {
	return isset( $GLOBALS['ucp_test_meta'][ $post_id ][ $key ] ) ? $GLOBALS['ucp_test_meta'][ $post_id ][ $key ] : '';
}

function update_post_meta( $post_id, $key, $value ) {
	$GLOBALS['ucp_test_meta'][ $post_id ][ $key ] = $value;
	$GLOBALS['ucp_test_updates'][] = array( $post_id, $key, $value );
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-metadata.php';

$metadata = new UrbanCareProject_Metadata();
$metadata->maybe_migrate_activity_dates();

if ( array( array( 1, '_ucp_start_date', '2025-09-01' ) ) !== $GLOBALS['ucp_test_updates'] ) {
	throw new RuntimeException( 'Legacy Activity dates were not migrated safely.' );
}
if ( UrbanCareProject_Metadata::ACTIVITY_DATE_MIGRATION_VERSION !== $GLOBALS['ucp_test_options'][ UrbanCareProject_Metadata::ACTIVITY_DATE_MIGRATION_OPTION ] ) {
	throw new RuntimeException( 'Activity migration version was not stored.' );
}

$metadata->maybe_migrate_activity_dates();
if ( 1 !== count( $GLOBALS['ucp_test_updates'] ) ) {
	throw new RuntimeException( 'Activity date migration is not idempotent.' );
}

echo "WordPress Activity migration contract passed\n";