<?php

define( 'ABSPATH', __DIR__ );

class WP_REST_Controller {}

class WP_REST_Server {
	const READABLE = 'GET';
}

class UCP_Test_REST_Response {
	public $data;
	public $headers = array();

	public function __construct( $data ) {
		$this->data = $data;
	}

	public function header( $name, $value ) {
		$this->headers[ $name ] = $value;
	}
}

class UCP_Test_REST_Request {
	private $params;

	public function __construct( $params ) {
		$this->params = $params;
	}

	public function get_param( $key ) {
		return array_key_exists( $key, $this->params ) ? $this->params[ $key ] : null;
	}
}

class WP_Query {
	public $posts = array();
	public $found_posts = 0;
	public $max_num_pages = 0;

	public function __construct( $args ) {
		$GLOBALS['ucp_test_query_args'] = $args;
	}
}

$GLOBALS['ucp_test_posts'] = array(
	7  => (object) array( 'ID' => 7, 'post_type' => 'ucp_team', 'post_status' => 'publish', 'post_name' => 'jane-doe', 'post_content' => 'Biography' ),
	8  => (object) array( 'ID' => 8, 'post_type' => 'ucp_study_site', 'post_status' => 'publish', 'post_name' => 'noonkopir', 'post_content' => 'Study site description' ),
	9  => (object) array( 'ID' => 9, 'post_type' => 'ucp_activity', 'post_status' => 'publish', 'post_name' => 'collective-fieldwork', 'post_content' => 'Activity narrative' ),
	10 => (object) array( 'ID' => 10, 'post_type' => 'ucp_partner', 'post_status' => 'publish', 'post_name' => 'research-partner', 'post_content' => '' ),
	11 => (object) array( 'ID' => 11, 'post_type' => 'ucp_team', 'post_status' => 'draft', 'post_name' => 'draft-member', 'post_content' => '' ),
	12 => (object) array( 'ID' => 12, 'post_type' => 'ucp_field_story', 'post_status' => 'publish', 'post_name' => 'research-in-action-across-kitengela', 'post_content' => 'Field Story narrative' ),
	41 => (object) array( 'ID' => 41, 'post_type' => 'ucp_publication', 'post_status' => 'publish', 'post_name' => 'published-paper', 'post_content' => '' ),
	42 => (object) array( 'ID' => 42, 'post_type' => 'ucp_publication', 'post_status' => 'draft', 'post_name' => 'draft-paper', 'post_content' => '' ),
);

$GLOBALS['ucp_test_meta'] = array(
	7 => array(
		'_ucp_role'                    => 'Researcher',
		'_ucp_selected_publications'   => array( array( 'title' => 'Manual paper', 'citation' => 'Journal', 'year' => 2025, 'url' => 'https://doi.org/example' ) ),
		'_ucp_related_publication_ids' => array( 41, 42 ),
		'_ucp_public_email'            => 'private@example.org',
		'_ucp_show_email'              => false,
		'_ucp_display_order'           => 2,
	),
	8 => array(
		'_ucp_location_name'        => 'Noonkopir, Kitengela, Kajiado County',
		'_ucp_site_category'        => 'Community health site',
		'_ucp_latitude'             => -1.4692,
		'_ucp_longitude'            => 36.9586,
		'_ucp_coordinates_verified' => true,
		'_ucp_gallery_ids'          => array(),
		'_ucp_related_activity_ids' => array(),
	),
	9 => array(
		'_ucp_start_date'          => '2025-09-01',
		'_ucp_end_date'            => '',
		'_ucp_ongoing'             => true,
		'_ucp_location'            => 'Kitengela',
		'_ucp_activity_phases'     => array(
			array( 'title' => 'Resident meetings', 'startDate' => '2025-09-01', 'endDate' => '', 'ongoing' => true, 'summary' => 'Protocol discussions', 'imageId' => 51 ),
		),
		'_ucp_gallery_ids'         => array( 51 ),
		'_ucp_related_team_ids'    => array( 7, 11 ),
		'_ucp_related_partner_ids' => array( 10 ),
		'_ucp_related_site_ids'    => array( 8 ),
		'_ucp_display_order'       => 1,
		'_ucp_featured'            => true,
		'_ucp_activity_date'       => '2025-09-01',
	),
	12 => array(
		'_ucp_gallery_ids'          => array( 51 ),
		'_ucp_story_lenses'         => array( array( 'title' => 'Working with residents', 'description' => 'Shared fieldwork.' ) ),
		'_ucp_creator_credit'       => 'Text source: Bérénice Bon',
		'_ucp_closing_statement'    => 'Shared fieldwork connects environmental change with everyday life.',
		'_ucp_related_site_ids'     => array( 8 ),
		'_ucp_related_team_ids'     => array( 7, 11 ),
		'_ucp_related_activity_ids' => array( 9 ),
		'_ucp_featured'             => true,
		'_ucp_display_order'        => 0,
	),
);

function __( $text ) {
	return $text;
}

function get_post( $post ) {
	if ( is_object( $post ) ) {
		return $post;
	}
	return isset( $GLOBALS['ucp_test_posts'][ $post ] ) ? $GLOBALS['ucp_test_posts'][ $post ] : null;
}

function get_the_title( $post ) {
	$titles = array( 7 => 'Dr. Jane Doe', 8 => 'Noonkopir', 9 => 'Collective fieldwork', 10 => 'Research Partner', 11 => 'Draft Member', 12 => 'Research in action across Kitengela', 41 => 'Published paper', 42 => 'Draft paper' );
	return isset( $titles[ (int) $post->ID ] ) ? $titles[ (int) $post->ID ] : '';
}

function get_the_excerpt() {
	return 'Short biography';
}

function apply_filters( $filter, $value ) {
	return $value;
}

function get_post_time() {
	return '2026-01-01T00:00:00+00:00';
}

function get_post_modified_time() {
	return '2026-01-02T00:00:00+00:00';
}

function get_post_thumbnail_id() {
	return 0;
}

function get_object_taxonomies() {
	return array();
}

function get_post_meta( $post_id, $key ) {
	return isset( $GLOBALS['ucp_test_meta'][ $post_id ][ $key ] ) ? $GLOBALS['ucp_test_meta'][ $post_id ][ $key ] : '';
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_get_attachment_url( $attachment_id ) {
	return 51 === (int) $attachment_id ? 'https://example.org/activity.jpg' : false;
}

function wp_get_attachment_metadata( $attachment_id ) {
	return 51 === (int) $attachment_id ? array( 'width' => 1600, 'height' => 1067 ) : array();
}

function rest_ensure_response( $data ) {
	return new UCP_Test_REST_Response( $data );
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-metadata.php';
require dirname( __DIR__ ) . '/includes/api/class-urbancareproject-serializer.php';
require dirname( __DIR__ ) . '/includes/api/class-urbancareproject-rest-api.php';

$serializer = new UrbanCareProject_Serializer();
$team = $serializer->serialize( 7 );

if ( isset( $team['meta']['publicEmail'] ) ) {
	throw new RuntimeException( 'Private Team email was exposed by the serializer.' );
}
if ( 1 !== count( $team['meta']['relatedPublicationIds'] ) || 41 !== $team['meta']['relatedPublicationIds'][0]['id'] ) {
	throw new RuntimeException( 'Related publications were not filtered and serialized as public relations.' );
}
if ( 'Manual paper' !== $team['meta']['selectedPublications'][0]['title'] ) {
	throw new RuntimeException( 'Manual publications were not preserved in the Team response.' );
}

$study_site = $serializer->serialize( 8 );
if ( 'Noonkopir, Kitengela, Kajiado County' !== $study_site['meta']['locationName'] || -1.4692 !== $study_site['meta']['latitude'] || 36.9586 !== $study_site['meta']['longitude'] ) {
	throw new RuntimeException( 'Study Site location or verified coordinates were not serialized correctly.' );
}

$GLOBALS['ucp_test_meta'][8]['_ucp_coordinates_verified'] = false;
$unverified_study_site = $serializer->serialize( 8 );
if ( isset( $unverified_study_site['meta']['latitude'] ) || isset( $unverified_study_site['meta']['longitude'] ) ) {
	throw new RuntimeException( 'Unverified Study Site coordinates were exposed by the serializer.' );
}

$activity = $serializer->serialize( 9 );
if ( 51 !== $activity['meta']['activityPhases'][0]['image']['id'] || isset( $activity['meta']['activityPhases'][0]['imageId'] ) ) {
	throw new RuntimeException( 'Activity phase images were not serialized as public attachment objects.' );
}
if ( 1 !== count( $activity['meta']['relatedTeamIds'] ) || 7 !== $activity['meta']['relatedTeamIds'][0]['id'] ) {
	throw new RuntimeException( 'Activity Team relationships were not filtered to published records.' );
}
if ( 51 !== $activity['meta']['gallery'][0]['id'] || 10 !== $activity['meta']['relatedPartnerIds'][0]['id'] || 8 !== $activity['meta']['relatedSiteIds'][0]['id'] ) {
	throw new RuntimeException( 'Activity gallery, Partner, or Study Site relations were not serialized correctly.' );
}

$field_story = $serializer->serialize( 12 );
if ( 'Working with residents' !== $field_story['meta']['storyLenses'][0]['title'] || 51 !== $field_story['meta']['gallery'][0]['id'] ) {
	throw new RuntimeException( 'Field Story lenses or gallery were not serialized correctly.' );
}
if ( 8 !== $field_story['meta']['relatedSiteIds'][0]['id'] || 7 !== $field_story['meta']['relatedTeamIds'][0]['id'] || 9 !== $field_story['meta']['relatedActivityIds'][0]['id'] ) {
	throw new RuntimeException( 'Field Story relations were not serialized correctly.' );
}
if ( true !== $field_story['meta']['featured'] || 0 !== $field_story['meta']['displayOrder'] ) {
	throw new RuntimeException( 'Field Story homepage priority was not serialized correctly.' );
}

$api = new UrbanCareProject_REST_API();
$api->get_collection( new UCP_Test_REST_Request( array( 'post_type' => 'ucp_team', 'page' => 1, 'per_page' => 10 ) ) );
$query_args = $GLOBALS['ucp_test_query_args'];

if ( array( 'team_display_order' => 'ASC', 'title' => 'ASC' ) !== $query_args['orderby'] ) {
	throw new RuntimeException( 'Team collection is not ordered by display order and title.' );
}
$display_order_query = $query_args['meta_query'][0];
if ( 'OR' !== $display_order_query['relation'] || 'EXISTS' !== $display_order_query['team_display_order']['compare'] || 'NOT EXISTS' !== $display_order_query['team_display_order_missing']['compare'] ) {
	throw new RuntimeException( 'Team collection ordering does not retain profiles without display-order metadata.' );
}

$api->get_collection( new UCP_Test_REST_Request( array( 'post_type' => 'ucp_activity', 'page' => 1, 'per_page' => 10 ) ) );
$activity_query_args = $GLOBALS['ucp_test_query_args'];
if ( array( 'activity_display_order' => 'ASC', 'activity_start_date' => 'DESC', 'title' => 'ASC' ) !== $activity_query_args['orderby'] ) {
	throw new RuntimeException( 'Activity collection is not ordered by display order, start date, and title.' );
}

$api->get_collection( new UCP_Test_REST_Request( array( 'post_type' => 'ucp_field_story', 'page' => 1, 'per_page' => 10 ) ) );
$field_story_query_args = $GLOBALS['ucp_test_query_args'];
if ( array( 'field_story_display_order' => 'ASC', 'date' => 'DESC', 'title' => 'ASC' ) !== $field_story_query_args['orderby'] ) {
	throw new RuntimeException( 'Field Story collection is not ordered by display order, publication date, and title.' );
}

echo "WordPress Team API contract passed\n";