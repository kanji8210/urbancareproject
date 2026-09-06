<?php

define( 'ABSPATH', __DIR__ );

$GLOBALS['ucp_test_meta']      = array();
$GLOBALS['ucp_test_thumbnail'] = 0;
$GLOBALS['ucp_test_metaboxes'] = array();

function __( $text ) {
	return $text;
}

function add_meta_box( $id, $title, $callback, $post_type ) {
	$GLOBALS['ucp_test_metaboxes'][ $post_type ] = array(
		'id'       => $id,
		'title'    => $title,
		'callback' => $callback[1],
	);
}

function remove_meta_box() {}

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

function get_post_type( $post_id ) {
	return in_array( (int) $post_id, array( 8, 12 ), true ) ? 'ucp_publication' : 'ucp_partner';
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
if ( 'text' !== $team_fields['_ucp_institutional_affiliation']['input'] ) {
	throw new RuntimeException( 'Team institutional affiliation is not a text field.' );
}
if ( 'publications' !== $team_fields['_ucp_selected_publications']['input'] || 'publication_select' !== $team_fields['_ucp_related_publication_ids']['input'] ) {
	throw new RuntimeException( 'Team publication fields are not configured correctly.' );
}

$publications = UrbanCareProject_Metadata::sanitize_publications(
	array(
		array( 'title' => '  Valid <b>paper</b> ', 'citation' => "Journal\nDetails", 'year' => '2025', 'url' => 'https://doi.org/10.1/example' ),
		array( 'title' => '', 'citation' => 'Missing title', 'year' => '2024', 'url' => 'https://example.org' ),
		array( 'title' => 'Invalid details', 'citation' => 'Citation', 'year' => '20', 'url' => 'not-a-url' ),
	)
);
if ( 2 !== count( $publications ) || 'Valid paper' !== $publications[0]['title'] || 2025 !== $publications[0]['year'] || null !== $publications[1]['year'] || '' !== $publications[1]['url'] ) {
	throw new RuntimeException( 'Selected publications were not normalized correctly.' );
}

$post = (object) array( 'post_type' => 'ucp_team' );
$_POST = array(
	UrbanCareProject_Fields::NONCE_NAME => 'valid',
	'_ucp_team_portrait_id'             => '42',
	'_ucp_role'                         => 'Principal investigator',
	'_ucp_partner_id'                   => '17',
	'_ucp_institutional_affiliation'    => 'IRD',
	'_ucp_selected_publications'        => array(
		array( 'title' => 'Air quality study', 'citation' => 'Example Journal', 'year' => '2023', 'url' => 'https://doi.org/10.1/study' ),
	),
	'_ucp_related_publication_ids'      => array( '8', '8', '12', '17', '0' ),
	'_ucp_orcid_url'                    => 'https://orcid.org/0000-0000-0000-0000',
	'_ucp_google_scholar_url'           => 'invalid',
	'_ucp_researchgate_url'             => 'https://www.researchgate.net/profile/example',
	'_ucp_portfolio_url'                => 'https://example.org/work',
	'_ucp_linkedin_url'                 => 'https://www.linkedin.com/in/example',
	'_ucp_public_email'                 => 'researcher@example.org',
	'_ucp_show_email'                   => '1',
	'_ucp_profile_url'                  => 'https://example.org/researcher',
	'_ucp_additional_links'             => "https://orcid.org/0000-0000\nhttps://example.org/profile",
	'_ucp_display_order'                => '3',
);

$fields = new UrbanCareProject_Fields();
$fields->add_meta_boxes();

if ( array( 'id' => 'ucp_partner_details', 'title' => 'Partner Details', 'callback' => 'render_partner' ) !== $GLOBALS['ucp_test_metaboxes']['ucp_partner'] ) {
	throw new RuntimeException( 'Partner form is not registered independently.' );
}
if ( array( 'id' => 'ucp_team_details', 'title' => 'Team Member Details', 'callback' => 'render_team' ) !== $GLOBALS['ucp_test_metaboxes']['ucp_team'] ) {
	throw new RuntimeException( 'Team Member form is not registered independently.' );
}

$fields->save( 9, $post );

if ( 42 !== $GLOBALS['ucp_test_thumbnail'] ) {
	throw new RuntimeException( 'Team portrait was not stored as the featured image.' );
}
if ( 17 !== $GLOBALS['ucp_test_meta']['_ucp_partner_id'] || true !== $GLOBALS['ucp_test_meta']['_ucp_show_email'] ) {
	throw new RuntimeException( 'Team Partner relationship or email visibility was not saved correctly.' );
}
if ( 'IRD' !== $GLOBALS['ucp_test_meta']['_ucp_institutional_affiliation'] || array( 8, 12 ) !== $GLOBALS['ucp_test_meta']['_ucp_related_publication_ids'] ) {
	throw new RuntimeException( 'Team affiliation or publication relationships were not saved correctly.' );
}
if ( 2023 !== $GLOBALS['ucp_test_meta']['_ucp_selected_publications'][0]['year'] || '' !== $GLOBALS['ucp_test_meta']['_ucp_google_scholar_url'] ) {
	throw new RuntimeException( 'Team publication or profile URL values were not sanitized correctly.' );
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

$GLOBALS['ucp_test_meta'] = array();
$partner_post = (object) array( 'post_type' => 'ucp_partner' );
$_POST = array(
	UrbanCareProject_Fields::NONCE_NAME => 'valid',
	'_ucp_partner_type'                 => 'institutional',
	'_ucp_media_id'                     => '31',
	'_ucp_project_role'                 => 'Research partner',
	'_ucp_about'                        => 'Partner description',
	'_ucp_website_url'                  => 'https://partner.example.org',
	'_ucp_role'                         => 'This Team-only field must be ignored',
);
$fields->save( 10, $partner_post );

if ( isset( $GLOBALS['ucp_test_meta']['_ucp_role'] ) ) {
	throw new RuntimeException( 'A Team Member field was saved through the Partner form.' );
}
if ( 'Research partner' !== $GLOBALS['ucp_test_meta']['_ucp_project_role'] ) {
	throw new RuntimeException( 'Partner form fields were not saved correctly.' );
}

$study_site_fields = UrbanCareProject_Metadata::fields()['ucp_study_site'];
$expected_study_site_fields = array(
	'_ucp_location_name',
	'_ucp_site_category',
	'_ucp_latitude',
	'_ucp_longitude',
	'_ucp_coordinates_verified',
	'_ucp_gallery_ids',
	'_ucp_related_activity_ids',
);
foreach ( $expected_study_site_fields as $key ) {
	if ( ! isset( $study_site_fields[ $key ] ) ) {
		throw new RuntimeException( sprintf( 'Study Site field %s is not registered.', $key ) );
	}
}

$study_site_post = (object) array( 'post_type' => 'ucp_study_site' );
if ( 'Study site name' !== $fields->title_placeholder( 'Add title', $study_site_post ) ) {
	throw new RuntimeException( 'Study Site title placeholder does not identify the site-name field.' );
}

$GLOBALS['ucp_test_meta'] = array();
$_POST = array(
	UrbanCareProject_Fields::NONCE_NAME => 'valid',
	'_ucp_location_name'                => '  Noonkopir, Kitengela, Kajiado County  ',
	'_ucp_site_category'                => 'Community health site',
	'_ucp_latitude'                     => '-1.4692',
	'_ucp_longitude'                    => '36.9586',
	'_ucp_coordinates_verified'         => '1',
	'_ucp_gallery_ids'                  => '31, 32, 31',
	'_ucp_related_activity_ids'         => '41, 42',
);
$fields->save( 11, $study_site_post );

if ( 'Noonkopir, Kitengela, Kajiado County' !== $GLOBALS['ucp_test_meta']['_ucp_location_name'] ) {
	throw new RuntimeException( 'Study Site location name was not sanitized and saved.' );
}
if ( -1.4692 !== $GLOBALS['ucp_test_meta']['_ucp_latitude'] || 36.9586 !== $GLOBALS['ucp_test_meta']['_ucp_longitude'] || true !== $GLOBALS['ucp_test_meta']['_ucp_coordinates_verified'] ) {
	throw new RuntimeException( 'Study Site verified coordinates were not saved correctly.' );
}
if ( array( 31, 32 ) !== $GLOBALS['ucp_test_meta']['_ucp_gallery_ids'] || array( 41, 42 ) !== $GLOBALS['ucp_test_meta']['_ucp_related_activity_ids'] ) {
	throw new RuntimeException( 'Study Site gallery or related activities were not retained.' );
}

echo "WordPress admin fields contract passed\n";