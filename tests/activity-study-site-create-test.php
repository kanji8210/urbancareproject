<?php

define( 'ABSPATH', __DIR__ );

$GLOBALS['ucp_can_edit']       = true;
$GLOBALS['ucp_existing_sites'] = array();
$GLOBALS['ucp_created_post']   = array();
$GLOBALS['ucp_created_meta']   = array();

class UCP_Test_JSON_Response extends RuntimeException {
	public $success;
	public $data;
	public $status;

	public function __construct( $success, $data, $status ) {
		parent::__construct( isset( $data['message'] ) ? $data['message'] : '' );
		$this->success = $success;
		$this->data    = $data;
		$this->status  = $status;
	}
}

function __( $text ) {
	return $text;
}

function check_ajax_referer( $action, $field ) {
	if ( 'ucp_create_study_site' !== $action || 'nonce' !== $field || 'valid' !== $_POST['nonce'] ) {
		throw new RuntimeException( 'The quick-create nonce was not verified.' );
	}
}

function current_user_can( $capability ) {
	return 'edit_posts' === $capability && $GLOBALS['ucp_can_edit'];
}

function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}

function wp_unslash( $value ) {
	return $value;
}

function get_posts() {
	return $GLOBALS['ucp_existing_sites'];
}

function wp_insert_post( $post, $return_error ) {
	$GLOBALS['ucp_created_post'] = $post;
	return $return_error ? 73 : 0;
}

function is_wp_error() {
	return false;
}

function update_post_meta( $post_id, $key, $value ) {
	$GLOBALS['ucp_created_meta'][ $key ] = $value;
}

function wp_send_json_error( $data, $status = 400 ) {
	throw new UCP_Test_JSON_Response( false, $data, $status );
}

function wp_send_json_success( $data, $status = 200 ) {
	throw new UCP_Test_JSON_Response( true, $data, $status );
}

require dirname( __DIR__ ) . '/includes/content/class-urbancareproject-metadata.php';
require dirname( __DIR__ ) . '/includes/admin/class-urbancareproject-fields.php';

$fields = new UrbanCareProject_Fields();

$_POST = array( 'nonce' => 'valid', 'title' => 'Noonkopir', 'location' => 'Kitengela' );
$GLOBALS['ucp_can_edit'] = false;
try {
	$fields->create_study_site();
	throw new RuntimeException( 'An unauthorized user created a Study Site.' );
} catch ( UCP_Test_JSON_Response $response ) {
	if ( $response->success || 403 !== $response->status ) {
		throw new RuntimeException( 'Unauthorized Study Site creation did not return 403.' );
	}
}

$GLOBALS['ucp_can_edit'] = true;
$_POST['location']       = '';
try {
	$fields->create_study_site();
	throw new RuntimeException( 'A Study Site was created without a location.' );
} catch ( UCP_Test_JSON_Response $response ) {
	if ( $response->success || 400 !== $response->status ) {
		throw new RuntimeException( 'Incomplete Study Site creation did not return 400.' );
	}
}

$_POST['location']                 = 'Kitengela';
$GLOBALS['ucp_existing_sites']     = array( (object) array( 'ID' => 22 ) );
try {
	$fields->create_study_site();
	throw new RuntimeException( 'A duplicate Study Site title was accepted.' );
} catch ( UCP_Test_JSON_Response $response ) {
	if ( $response->success || 409 !== $response->status ) {
		throw new RuntimeException( 'Duplicate Study Site creation did not return 409.' );
	}
}

$GLOBALS['ucp_existing_sites'] = array();
try {
	$fields->create_study_site();
	throw new RuntimeException( 'Study Site creation did not return a response.' );
} catch ( UCP_Test_JSON_Response $response ) {
	if ( ! $response->success || 73 !== $response->data['id'] || 'Noonkopir - Kitengela' !== $response->data['label'] ) {
		throw new RuntimeException( 'Created Study Site response cannot populate the Activity selectors.' );
	}
}

if ( 'ucp_study_site' !== $GLOBALS['ucp_created_post']['post_type'] || 'draft' !== $GLOBALS['ucp_created_post']['post_status'] ) {
	throw new RuntimeException( 'Quick-created Study Site was not saved as a draft Study Site.' );
}
if ( 'Kitengela' !== $GLOBALS['ucp_created_meta']['_ucp_location_name'] ) {
	throw new RuntimeException( 'Quick-created Study Site location was not persisted.' );
}

echo "WordPress Activity Study Site quick-create contract passed\n";