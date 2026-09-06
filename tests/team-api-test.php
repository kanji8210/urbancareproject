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
	return 41 === (int) $post->ID ? 'Published paper' : ( 42 === (int) $post->ID ? 'Draft paper' : 'Dr. Jane Doe' );
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

function wp_get_attachment_url() {
	return false;
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

echo "WordPress Team API contract passed\n";