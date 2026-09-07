<?php

define( 'ABSPATH', __DIR__ );
define( 'URBANCAREPROJECT_PATH', dirname( __DIR__ ) . '/' );
define( 'OBJECT', 'OBJECT' );

$GLOBALS['ucp_seed_options'] = array( 'ucp_canonical_project_id' => 1 );
$GLOBALS['ucp_seed_posts'] = array(
	1 => array( 'ID' => 1, 'post_type' => 'ucp_project', 'post_name' => 'urban-care-here', 'post_status' => 'draft' ),
	101 => array( 'ID' => 101, 'post_type' => 'ucp_team', 'post_name' => 'berenice-bon', 'post_status' => 'publish' ),
	201 => array( 'ID' => 201, 'post_type' => 'ucp_study_site', 'post_name' => 'kitengela', 'post_status' => 'publish' ),
	202 => array( 'ID' => 202, 'post_type' => 'ucp_study_site', 'post_name' => 'noonkopir', 'post_status' => 'publish' ),
	301 => array( 'ID' => 301, 'post_type' => 'ucp_activity', 'post_name' => 'engagement-with-residents-associations', 'post_status' => 'publish' ),
	302 => array( 'ID' => 302, 'post_type' => 'ucp_activity', 'post_name' => 'public-health-surveys', 'post_status' => 'publish' ),
	303 => array( 'ID' => 303, 'post_type' => 'ucp_activity', 'post_name' => 'pollution-and-environmental-transformations', 'post_status' => 'publish' ),
	304 => array( 'ID' => 304, 'post_type' => 'ucp_activity', 'post_name' => 'collaboration-with-schools', 'post_status' => 'publish' ),
);
$GLOBALS['ucp_seed_meta'] = array();
$GLOBALS['ucp_seed_terms'] = array();
$GLOBALS['ucp_seed_attachments'] = array();
$GLOBALS['ucp_seed_insert_count'] = 0;

function __( $text ) { return $text; }
function absint( $value ) { return abs( (int) $value ); }
function get_option( $key, $default = '' ) { return isset( $GLOBALS['ucp_seed_options'][ $key ] ) ? $GLOBALS['ucp_seed_options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['ucp_seed_options'][ $key ] = $value; }
function get_post_type( $post_id ) { return isset( $GLOBALS['ucp_seed_posts'][ $post_id ] ) ? $GLOBALS['ucp_seed_posts'][ $post_id ]['post_type'] : false; }
function get_posts( $args ) {
	$matches = array();
	foreach ( $GLOBALS['ucp_seed_posts'] as $post ) {
		if ( isset( $args['post_type'] ) && $post['post_type'] !== $args['post_type'] ) continue;
		if ( isset( $args['name'] ) && $post['post_name'] !== $args['name'] ) continue;
		if ( isset( $args['meta_key'] ) && ( $GLOBALS['ucp_seed_meta'][ $post['ID'] ][ $args['meta_key'] ] ?? '' ) !== $args['meta_value'] ) continue;
		$matches[] = 'ids' === ( $args['fields'] ?? '' ) ? $post['ID'] : (object) $post;
	}
	return array_slice( $matches, 0, isset( $args['posts_per_page'] ) && $args['posts_per_page'] > 0 ? $args['posts_per_page'] : null );
}
function get_page_by_path( $slug, $output, $post_type ) {
	foreach ( $GLOBALS['ucp_seed_posts'] as $post ) if ( $post['post_type'] === $post_type && $post['post_name'] === $slug ) return (object) $post;
	return null;
}
function wp_insert_post( $data ) {
	$id = max( array_keys( $GLOBALS['ucp_seed_posts'] ) ) + 1;
	$GLOBALS['ucp_seed_posts'][ $id ] = array( 'ID' => $id, 'post_type' => $data['post_type'], 'post_name' => $data['post_name'] ?? strtolower( str_replace( ' ', '-', $data['post_title'] ) ), 'post_status' => $data['post_status'], 'post_title' => $data['post_title'] );
	$GLOBALS['ucp_seed_insert_count']++;
	return $id;
}
function is_wp_error() { return false; }
function update_post_meta( $post_id, $key, $value ) { $GLOBALS['ucp_seed_meta'][ $post_id ][ $key ] = $value; }
function set_post_thumbnail( $post_id, $attachment_id ) { $GLOBALS['ucp_seed_meta'][ $post_id ]['_thumbnail_id'] = $attachment_id; }
function wp_set_object_terms( $post_id, $terms, $taxonomy ) { $GLOBALS['ucp_seed_terms'][ $post_id ][ $taxonomy ] = $terms; }
function term_exists() { return true; }
function wp_insert_term() { return true; }

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-seeder.php';

class UCP_Test_Seeder extends UrbanCareProject_Seeder {
	protected function import_seed_image( $story_id, $asset ) {
		$key = $asset['key'];
		if ( isset( $GLOBALS['ucp_seed_attachments'][ $key ] ) ) return $GLOBALS['ucp_seed_attachments'][ $key ];
		$id = 500 + count( $GLOBALS['ucp_seed_attachments'] );
		$GLOBALS['ucp_seed_attachments'][ $key ] = $id;
		return $id;
	}
}

$seeder = new UCP_Test_Seeder();
$seeder->seed();

$stories = array_filter( $GLOBALS['ucp_seed_posts'], function ( $post ) { return 'ucp_field_story' === $post['post_type']; } );
if ( 2 !== count( $stories ) || 7 !== count( $GLOBALS['ucp_seed_attachments'] ) ) throw new RuntimeException( 'Seeder did not create two stories and seven reusable media assets.' );
$kitengela = get_page_by_path( 'research-in-action-across-kitengela', OBJECT, 'ucp_field_story' );
if ( ! $kitengela || true !== $GLOBALS['ucp_seed_meta'][ $kitengela->ID ]['_ucp_featured'] || 0 !== $GLOBALS['ucp_seed_meta'][ $kitengela->ID ]['_ucp_display_order'] ) throw new RuntimeException( 'Kitengela story was not seeded as the primary homepage feature.' );
if ( array( 201, 202 ) !== $GLOBALS['ucp_seed_meta'][ $kitengela->ID ]['_ucp_related_site_ids'] || array( 301, 302, 303, 304 ) !== $GLOBALS['ucp_seed_meta'][ $kitengela->ID ]['_ucp_related_activity_ids'] ) throw new RuntimeException( 'Kitengela story source relations were not resolved by stable slug.' );
$insert_count = $GLOBALS['ucp_seed_insert_count'];
$seeder->seed();
if ( $insert_count !== $GLOBALS['ucp_seed_insert_count'] || 7 !== count( $GLOBALS['ucp_seed_attachments'] ) ) throw new RuntimeException( 'Field Story seeding is not idempotent.' );
if ( UrbanCareProject_Seeder::SEED_VERSION !== get_option( UrbanCareProject_Seeder::SEED_VERSION_OPTION, '' ) ) throw new RuntimeException( 'Field Story seed version was not persisted.' );

echo "WordPress Field Story seeder contract passed\n";
