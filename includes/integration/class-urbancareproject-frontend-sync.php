<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Frontend_Sync {
	private const POST_TYPES = array(
		'ucp_project',
		'ucp_activity',
		'ucp_publication',
		'ucp_team',
		'ucp_partner',
		'ucp_study_site',
		'ucp_field_story',
	);

	public function content_status_changed( $new_status, $old_status, $post ) {
		if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, self::POST_TYPES, true ) ) {
			return;
		}
		if ( 'publish' !== $old_status || 'publish' === $new_status ) {
			return;
		}

		$this->request_revalidation( substr( $post->post_type, 4 ), $post->post_name, false );
	}

	public function content_saved( $post_id, $post ) {
		if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, self::POST_TYPES, true ) || 'publish' !== $post->post_status ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		$this->request_revalidation( substr( $post->post_type, 4 ), $post->post_name, false );
	}

	public function test_revalidation() {
		$this->authorize_admin_action( 'ucp_test_revalidation' );
		$result = $this->request_revalidation( 'project', '', true );
		$this->redirect_with_result( 'revalidation', $result );
	}

	public function trigger_deploy() {
		$this->authorize_admin_action( 'ucp_trigger_deploy' );
		$url = get_option( 'ucp_vercel_deploy_webhook', '' );
		if ( ! $this->is_https_url( $url ) ) {
			$this->redirect_with_result( 'deploy', new WP_Error( 'ucp_missing_webhook', __( 'Configure a valid HTTPS Vercel deploy webhook first.', 'urbancareproject' ) ) );
		}

		$response = wp_safe_remote_post(
			$url,
			array(
				'timeout' => 10,
			)
		);
		$this->redirect_with_result( 'deploy', $this->validate_response( $response ) );
	}

	private function request_revalidation( $type, $slug, $blocking ) {
		$frontend_url = get_option( 'ucp_nextjs_url', '' );
		$secret       = get_option( 'ucp_revalidate_secret', '' );
		if ( ! $this->is_http_url( $frontend_url ) || ! is_string( $secret ) || '' === $secret ) {
			return new WP_Error( 'ucp_sync_not_configured', __( 'Configure the frontend URL and revalidation secret first.', 'urbancareproject' ) );
		}

		$response = wp_safe_remote_post(
			trailingslashit( $frontend_url ) . 'api/revalidate',
			array(
				'blocking' => $blocking,
				'timeout'  => $blocking ? 10 : 3,
				'headers'  => array(
					'Content-Type'                       => 'application/json',
					'X-UrbanCare-Revalidate-Secret'     => $secret,
				),
				'body'     => wp_json_encode(
					array(
						'type' => $type,
						'slug' => $slug,
					)
				),
			)
		);

		return $blocking ? $this->validate_response( $response ) : $response;
	}

	private function validate_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$status = wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			return new WP_Error( 'ucp_remote_request_failed', sprintf( __( 'The remote service returned HTTP %d.', 'urbancareproject' ), $status ) );
		}
		return true;
	}

	private function authorize_admin_action( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'urbancareproject' ) );
		}
		check_admin_referer( $action );
	}

	private function redirect_with_result( $action, $result ) {
		$query = array(
			'page'            => 'urbancareproject-settings',
			'ucp_sync_action' => $action,
			'ucp_sync_status' => is_wp_error( $result ) ? 'error' : 'success',
		);
		if ( is_wp_error( $result ) ) {
			$query['ucp_sync_message'] = $result->get_error_message();
		}
		wp_safe_redirect( add_query_arg( $query, admin_url( 'admin.php' ) ) );
		exit;
	}

	private function is_http_url( $url ) {
		return is_string( $url ) && (bool) wp_http_validate_url( $url );
	}

	private function is_https_url( $url ) {
		return $this->is_http_url( $url ) && 'https' === wp_parse_url( $url, PHP_URL_SCHEME );
	}
}