<?php

define( 'ABSPATH', __DIR__ );

class WP_Post {
	public $ID = 7;
	public $post_type = 'ucp_partner';
	public $post_status;
	public $post_name = 'test-partner';

	public function __construct( $status ) {
		$this->post_status = $status;
	}
}

class WP_Error {}

$GLOBALS['ucp_calls'] = array();

function wp_is_post_revision() {
	return false;
}

function get_option( $key, $default = '' ) {
	$options = array(
		'ucp_nextjs_url'        => 'https://frontend.example',
		'ucp_revalidate_secret' => 'secret',
	);
	return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}

function wp_http_validate_url( $url ) {
	return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : false;
}

function trailingslashit( $url ) {
	return rtrim( $url, '/' ) . '/';
}

function wp_safe_remote_post( $url, $args ) {
	$GLOBALS['ucp_calls'][] = array( $url, $args );
	return array( 'response' => array( 'code' => 200 ) );
}

function wp_json_encode( $value ) {
	return json_encode( $value );
}

function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}

function wp_remote_retrieve_response_code( $response ) {
	return $response['response']['code'];
}

require dirname( __DIR__ ) . '/includes/integration/class-urbancareproject-frontend-sync.php';

function assert_call_count( $expected, $message ) {
	if ( $expected !== count( $GLOBALS['ucp_calls'] ) ) {
		throw new RuntimeException( $message );
	}
}

$sync = new UrbanCareProject_Frontend_Sync();
$sync->content_saved( 7, new WP_Post( 'draft' ) );
assert_call_count( 0, 'Draft save triggered revalidation.' );

$sync->content_saved( 7, new WP_Post( 'publish' ) );
assert_call_count( 1, 'Published save did not trigger exactly once.' );

$call = $GLOBALS['ucp_calls'][0];
$body = json_decode( $call[1]['body'], true );
if ( 'https://frontend.example/api/revalidate' !== $call[0] || 'partner' !== $body['type'] || 'test-partner' !== $body['slug'] || 'secret' !== $call[1]['headers']['X-UrbanCare-Revalidate-Secret'] ) {
	throw new RuntimeException( 'Revalidation request contract mismatch.' );
}

$sync->content_status_changed( 'draft', 'publish', new WP_Post( 'draft' ) );
assert_call_count( 2, 'Unpublish did not trigger revalidation.' );

$sync->content_status_changed( 'pending', 'draft', new WP_Post( 'pending' ) );
assert_call_count( 2, 'A non-public transition triggered revalidation.' );

echo "WordPress frontend sync contract passed\n";