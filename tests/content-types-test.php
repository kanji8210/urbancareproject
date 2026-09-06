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

if ( ! in_array( 'excerpt', $GLOBALS['ucp_test_post_types']['ucp_team']['supports'], true ) ) {
	throw new RuntimeException( 'Team profiles do not support short biographies through excerpts.' );
}

if ( ! in_array( 'ucp_team', $GLOBALS['ucp_test_taxonomies']['ucp_theme']['post_types'], true ) ) {
	throw new RuntimeException( 'Research themes are not registered for Team profiles.' );
}

echo "WordPress content types contract passed\n";