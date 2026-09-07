<?php

define( 'ABSPATH', __DIR__ );

function __( $text ) {
	return $text;
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
	$types = array(
		21 => 'ucp_team',
		22 => 'ucp_team',
		31 => 'ucp_partner',
		41 => 'ucp_study_site',
		51 => 'attachment',
		52 => 'attachment',
	);
	return isset( $types[ (int) $post_id ] ) ? $types[ (int) $post_id ] : false;
}

function wp_attachment_is_image( $post_id ) {
	return in_array( (int) $post_id, array( 51, 52 ), true );
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-metadata.php';

$fields = UrbanCareProject_Metadata::fields()['ucp_activity'];
$expected_fields = array(
	'_ucp_start_date',
	'_ucp_end_date',
	'_ucp_ongoing',
	'_ucp_location',
	'_ucp_activity_phases',
	'_ucp_gallery_ids',
	'_ucp_related_team_ids',
	'_ucp_related_partner_ids',
	'_ucp_related_site_ids',
	'_ucp_display_order',
	'_ucp_featured',
);
foreach ( $expected_fields as $key ) {
	if ( ! isset( $fields[ $key ] ) ) {
		throw new RuntimeException( sprintf( 'Activity field %s is not registered.', $key ) );
	}
}
if ( empty( $fields['_ucp_activity_date']['legacy'] ) ) {
	throw new RuntimeException( 'The original Activity date is not retained as a legacy field.' );
}

$phases = UrbanCareProject_Metadata::sanitize_activity_phases(
	array(
		array(
			'title'     => '  Residents <b>meetings</b> ',
			'startDate' => '2025-09-01',
			'endDate'   => '2025-08-01',
			'ongoing'   => false,
			'summary'   => '  Protocol discussions ',
			'imageId'   => '51',
		),
		array(
			'title'     => 'School collaboration',
			'startDate' => '2026-01-01',
			'endDate'   => '2026-04-01',
			'ongoing'   => true,
			'summary'   => 'Science clubs',
			'imageId'   => '999',
		),
		array( 'title' => '', 'summary' => 'Discard this row' ),
	)
);

if ( 2 !== count( $phases ) || 'Residents meetings' !== $phases[0]['title'] ) {
	throw new RuntimeException( 'Activity phase titles were not normalized correctly.' );
}
if ( '' !== $phases[0]['endDate'] || 51 !== $phases[0]['imageId'] ) {
	throw new RuntimeException( 'Invalid Activity phase date ranges or valid images were not normalized correctly.' );
}
if ( '' !== $phases[1]['endDate'] || 0 !== $phases[1]['imageId'] || true !== $phases[1]['ongoing'] ) {
	throw new RuntimeException( 'Ongoing Activity phases or invalid images were not normalized correctly.' );
}

if ( array( 51, 52 ) !== UrbanCareProject_Metadata::sanitize_image_id_array( array( '51', '52', '51', '999' ) ) ) {
	throw new RuntimeException( 'Activity gallery image IDs were not validated and deduplicated.' );
}
if ( array( 21, 22 ) !== UrbanCareProject_Metadata::sanitize_team_id_array( array( 21, 31, 22 ) ) ) {
	throw new RuntimeException( 'Related Team IDs were not restricted to Team Member posts.' );
}
if ( array( 31 ) !== UrbanCareProject_Metadata::sanitize_partner_id_array( array( 21, 31 ) ) ) {
	throw new RuntimeException( 'Related Partner IDs were not restricted to Partner posts.' );
}
if ( array( 41 ) !== UrbanCareProject_Metadata::sanitize_study_site_id_array( array( 41, 51 ) ) ) {
	throw new RuntimeException( 'Related Study Site IDs were not restricted to Study Site posts.' );
}

echo "WordPress Activity fields contract passed\n";