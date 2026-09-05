<?php

define( 'ABSPATH', __DIR__ );

$GLOBALS['ucp_test_meta']      = array();
$GLOBALS['ucp_test_thumbnail'] = 0;

function wp_verify_nonce( $nonce, $action ) {
	return 'valid' === $nonce && UrbanCareProject_Fields::NONCE_ACTION === $action;
}

function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}

function sanitize_textarea_field( $value ) {
	return sanitize_text_field( $value );
}

function sanitize_email( $value ) {
	return filter_var( $value, FILTER_SANITIZE_EMAIL );
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_-]/', '', (string) $value ) );
}

function esc_url_raw( $value ) {
	return filter_var( trim( (string) $value ), FILTER_VALIDATE_URL ) ? trim( (string) $value ) : '';
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_unslash( $value ) {
	return $value;
}

function wp_is_post_revision() {
	return false;
}

function current_user_can() {
	return true;
}

function update_post_meta( $post_id, $key, $value ) {
	$GLOBALS['ucp_test_meta'][ $key ] = $value;
}

function set_post_thumbnail( $post_id, $attachment_id ) {
	$GLOBALS['ucp_test_thumbnail'] = $attachment_id;
}

function delete_post_thumbnail() {
	$GLOBALS['ucp_test_thumbnail'] = 0;
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-metadata.php';
require dirname( __DIR__ ) . '/includes/admin/class-urbancareproject-fields.php';

$team_fields = UrbanCareProject_Metadata::fields()['ucp_team'];
if ( 'partner_select' !== $team_fields['_ucp_partner_id']['input'] ) {
	throw new RuntimeException( 'Team institution is not configured as a Partner selector.' );
}

$post = (object) array( 'post_type' => 'ucp_team' );
$_POST = array(
	UrbanCareProject_Fields::NONCE_NAME => 'valid',
	'_ucp_team_portrait_id'             => '42',
	'_ucp_role'                         => 'Principal investigator',
	'_ucp_partner_id'                   => '17',
	'_ucp_public_email'                 => 'researcher@example.org',
	'_ucp_show_email'                   => '1',
	'_ucp_profile_url'                  => 'https://example.org/researcher',
	'_ucp_additional_links'             => "https://orcid.org/0000-0000\nhttps://example.org/profile",
	'_ucp_display_order'                => '3',
);

$fields = new UrbanCareProject_Fields();
$fields->save( 9, $post );

if ( 42 !== $GLOBALS['ucp_test_thumbnail'] ) {
	throw new RuntimeException( 'Team portrait was not stored as the featured image.' );
}
if ( 17 !== $GLOBALS['ucp_test_meta']['_ucp_partner_id'] || true !== $GLOBALS['ucp_test_meta']['_ucp_show_email'] ) {
	throw new RuntimeException( 'Team Partner relationship or email visibility was not saved correctly.' );
}
if ( 2 !== count( $GLOBALS['ucp_test_meta']['_ucp_additional_links'] ) ) {
	throw new RuntimeException( 'Team additional links were not normalized correctly.' );
}

$_POST = array(
	UrbanCareProject_Fields::NONCE_NAME => 'valid',
	'_ucp_team_portrait_id'             => '',
);
$fields->save( 9, $post );

if ( 0 !== $GLOBALS['ucp_test_thumbnail'] ) {
	throw new RuntimeException( 'Removing a Team portrait did not clear the featured image.' );
}
if ( false !== $GLOBALS['ucp_test_meta']['_ucp_show_email'] ) {
	throw new RuntimeException( 'An unchecked public-email control did not save as false.' );
}

echo "WordPress admin fields contract passed\n";