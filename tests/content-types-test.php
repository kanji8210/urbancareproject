<?php

define( 'ABSPATH', __DIR__ );

$GLOBALS['ucp_test_post_types'] = array();
$GLOBALS['ucp_test_taxonomies'] = array();

function __( $text ) {
	return $text;
}

function register_post_type( $post_type, $args ) {
	$GLOBALS['ucp_test_post_types'][ $post_type ] = $args;
}

function register_taxonomy( $taxonomy, $post_types, $args ) {
	$GLOBALS['ucp_test_taxonomies'][ $taxonomy ] = array(
		'post_types' => $post_types,
		'args'       => $args,
	);
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-content-types.php';

$content_types = new UrbanCareProject_Content_Types();
$content_types->register();

if ( ! isset( $GLOBALS['ucp_test_post_types']['ucp_page'] ) || ! in_array( 'page-attributes', $GLOBALS['ucp_test_post_types']['ucp_page']['supports'], true ) ) {
	throw new RuntimeException( 'Fixed editorial pages are not registered with page ordering support.' );
}

if ( 'do_not_allow' !== $GLOBALS['ucp_test_post_types']['ucp_page']['capabilities']['create_posts'] ) {
	throw new RuntimeException( 'Editors can create unsupported editorial page records.' );
}

if ( ! isset( $GLOBALS['ucp_test_post_types']['ucp_gallery'] ) || ! in_array( 'editor', $GLOBALS['ucp_test_post_types']['ucp_gallery']['supports'], true ) ) {
	throw new RuntimeException( 'Reusable galleries are not registered with editable descriptions.' );
}

if ( $GLOBALS['ucp_test_post_types']['ucp_gallery']['public'] || $GLOBALS['ucp_test_post_types']['ucp_gallery']['publicly_queryable'] || ! $GLOBALS['ucp_test_post_types']['ucp_gallery']['show_ui'] ) {
	throw new RuntimeException( 'Reusable galleries must be editable without exposing public archive pages.' );
}

if ( isset( $GLOBALS['ucp_test_post_types']['ucp_gallery']['capabilities']['create_posts'] ) ) {
	throw new RuntimeException( 'Editors cannot create reusable gallery records.' );
}

if ( ! in_array( 'excerpt', $GLOBALS['ucp_test_post_types']['ucp_team']['supports'], true ) ) {
	throw new RuntimeException( 'Team profiles do not support short biographies through excerpts.' );
}

if ( ! in_array( 'ucp_team', $GLOBALS['ucp_test_taxonomies']['ucp_theme']['post_types'], true ) ) {
	throw new RuntimeException( 'Research themes are not registered for Team profiles.' );
}

echo "WordPress content types contract passed\n";