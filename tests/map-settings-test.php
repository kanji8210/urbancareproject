<?php

define( 'ABSPATH', __DIR__ );
define( 'URBANCAREPROJECT_PATH', dirname( __DIR__ ) . '/' );

class WP_Error {}
class WP_REST_Server { const READABLE = 'GET'; }
class WP_REST_Controller {}
class WP_Post {
	public $post_type = 'ucp_page';
	public $post_status = 'publish';
	public $post_name = 'home';
}

$GLOBALS['ucp_options'] = array(
	'ucp_nextjs_url'        => 'https://frontend.example',
	'ucp_revalidate_secret' => 'secret',
);
$GLOBALS['ucp_settings_errors'] = array();
$GLOBALS['ucp_calls'] = array();

function __( $text ) { return $text; }
function get_option( $key, $default = '' ) { return $GLOBALS['ucp_options'][ $key ] ?? $default; }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function add_settings_error( $setting, $code, $message ) { $GLOBALS['ucp_settings_errors'][] = $code; }
function rest_ensure_response( $data ) { return $data; }
function wp_http_validate_url( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : false; }
function trailingslashit( $url ) { return rtrim( $url, '/' ) . '/'; }
function wp_json_encode( $value ) { return json_encode( $value ); }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_safe_remote_post( $url, $args ) { $GLOBALS['ucp_calls'][] = array( $url, $args ); return array(); }

require dirname( __DIR__ ) . '/includes/admin/class-urbancareproject-admin.php';
require dirname( __DIR__ ) . '/includes/api/class-urbancareproject-serializer.php';
require dirname( __DIR__ ) . '/includes/api/class-urbancareproject-rest-api.php';
require dirname( __DIR__ ) . '/includes/integration/class-urbancareproject-frontend-sync.php';

$admin = new UrbanCareProject_Admin( 'urbancareproject', '1.0.0' );

$valid_key = 'AIzaSyA-1234567890abcdefghijklmnopqrstu';
if ( $valid_key !== $admin->sanitize_map_api_key( '  ' . $valid_key . ' ' ) ) {
	throw new RuntimeException( 'A valid Google Maps API key was not accepted and trimmed.' );
}
if ( '' !== $admin->sanitize_map_api_key( '' ) ) {
	throw new RuntimeException( 'An empty Google Maps API key did not clear the setting.' );
}

$GLOBALS['ucp_options']['ucp_google_maps_api_key'] = $valid_key;
if ( $valid_key !== $admin->sanitize_map_api_key( '<script>alert(1)</script>' ) || array( 'ucp_google_maps_api_key_invalid' ) !== $GLOBALS['ucp_settings_errors'] ) {
	throw new RuntimeException( 'An invalid Google Maps API key replaced the saved key or raised no error.' );
}

$api = new UrbanCareProject_REST_API();
$map = $api->get_map_settings();
if ( 'google' !== $map['provider'] || $valid_key !== $map['googleMapsApiKey'] ) {
	throw new RuntimeException( 'Map settings did not expose the configured Google provider and key.' );
}
unset( $GLOBALS['ucp_options']['ucp_google_maps_api_key'] );
$map = $api->get_map_settings();
if ( 'default' !== $map['provider'] || '' !== $map['googleMapsApiKey'] ) {
	throw new RuntimeException( 'Map settings did not fall back to the default provider without a key.' );
}

$sync = new UrbanCareProject_Frontend_Sync();
$sync->map_settings_saved();
$body = json_decode( $GLOBALS['ucp_calls'][0][1]['body'], true );
if ( 1 !== count( $GLOBALS['ucp_calls'] ) || 'https://frontend.example/api/revalidate' !== $GLOBALS['ucp_calls'][0][0] || 'settings' !== $body['type'] ) {
	throw new RuntimeException( 'Saving the map key did not request frontend revalidation.' );
}

echo "WordPress map settings contract passed\n";
