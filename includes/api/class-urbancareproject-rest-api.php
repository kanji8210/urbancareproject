<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_REST_API extends WP_REST_Controller {
	public function register_routes() {
		register_rest_route(
			'urbancareproject/v1',
			'/activities',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_activities' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_activities() {
		$posts = get_posts(
			array(
				'post_type'      => 'ucp_activity',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		$data = array();
		foreach ( $posts as $post ) {
			$data[] = array(
				'id'       => $post->ID,
				'title'    => get_the_title( $post ),
				'excerpt'  => get_the_excerpt( $post ),
				'location' => get_post_meta( $post->ID, '_ucp_location', true ),
			);
		}

		return rest_ensure_response( $data );
	}
}
