<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_REST_API extends WP_REST_Controller {
	private const COLLECTIONS = array(
		'activities'    => 'ucp_activity',
		'publications'  => 'ucp_publication',
		'team'          => 'ucp_team',
		'partners'      => 'ucp_partner',
		'study-sites'   => 'ucp_study_site',
		'field-stories' => 'ucp_field_story',
	);

	private $serializer;

	public function __construct() {
		$this->namespace  = 'urbancareproject/v1';
		$this->serializer = new UrbanCareProject_Serializer();
	}

	public function register_routes() {
		foreach ( self::COLLECTIONS as $route => $post_type ) {
			$collection_args               = $this->collection_args();
			$collection_args['post_type'] = array( 'default' => $post_type );
			register_rest_route(
				$this->namespace,
				'/' . $route,
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_collection' ),
					'permission_callback' => '__return_true',
					'args'                => $collection_args,
				)
			);
			register_rest_route(
				$this->namespace,
				'/' . $route . '/(?P<slug>[a-z0-9-]+)',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'slug'      => array( 'required' => true, 'sanitize_callback' => 'sanitize_title' ),
						'post_type' => array( 'default' => $post_type ),
					),
				)
			);
		}

		register_rest_route(
			$this->namespace,
			'/project',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_project' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_collection( $request ) {
		$post_type = $this->resolve_post_type( $request );
		if ( ! $post_type ) {
			return new WP_Error( 'ucp_invalid_collection', __( 'Unknown content collection.', 'urbancareproject' ), array( 'status' => 400 ) );
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'paged'          => max( 1, (int) $request->get_param( 'page' ) ),
			'posts_per_page' => min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$this->apply_filters( $args, $request );
		$query = new WP_Query( $args );
		$data  = array_values( array_filter( array_map( array( $this->serializer, 'serialize' ), $query->posts ) ) );

		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', (int) $query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );
		return $response;
	}

	public function get_item( $request ) {
		$post_type = $this->resolve_post_type( $request );
		$post      = $post_type ? get_page_by_path( $request->get_param( 'slug' ), OBJECT, $post_type ) : null;
		if ( ! $post || 'publish' !== $post->post_status ) {
			return new WP_Error( 'ucp_content_not_found', __( 'Published content was not found.', 'urbancareproject' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( $this->serializer->serialize( $post ) );
	}

	public function get_project() {
		$project_id = UrbanCareProject_Seeder::canonical_project_id();
		$data       = $project_id ? $this->serializer->serialize( $project_id ) : null;
		if ( ! $data ) {
			return new WP_Error( 'ucp_project_not_found', __( 'The published Project record was not found.', 'urbancareproject' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( $data );
	}

	private function resolve_post_type( $request ) {
		$post_type = $request->get_param( 'post_type' );
		return in_array( $post_type, self::COLLECTIONS, true ) ? $post_type : null;
	}

	private function collection_args() {
		return array(
			'page'             => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
			'per_page'         => array( 'default' => 10, 'sanitize_callback' => 'absint' ),
			'theme'            => array( 'sanitize_callback' => 'sanitize_title' ),
			'method'           => array( 'sanitize_callback' => 'sanitize_title' ),
			'activity_type'    => array( 'sanitize_callback' => 'sanitize_title' ),
			'publication_type' => array( 'sanitize_callback' => 'sanitize_key' ),
			'featured'         => array( 'sanitize_callback' => 'rest_sanitize_boolean' ),
			'after'            => array( 'sanitize_callback' => array( 'UrbanCareProject_Metadata', 'sanitize_date' ) ),
			'before'           => array( 'sanitize_callback' => array( 'UrbanCareProject_Metadata', 'sanitize_date' ) ),
			'related_site'     => array( 'sanitize_callback' => 'absint' ),
			'related_partner'  => array( 'sanitize_callback' => 'absint' ),
			'related_team'     => array( 'sanitize_callback' => 'absint' ),
			'related_activity' => array( 'sanitize_callback' => 'absint' ),
		);
	}

	private function apply_filters( &$args, $request ) {
		$taxonomy_filters = array( 'theme' => 'ucp_theme', 'method' => 'ucp_method', 'activity_type' => 'ucp_activity_type' );
		foreach ( $taxonomy_filters as $parameter => $taxonomy ) {
			$value = $request->get_param( $parameter );
			if ( $value ) {
				$args['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $value );
			}
		}

		$meta_filters = array(
			'publication_type' => '_ucp_publication_type',
			'related_site'     => '_ucp_related_site_ids',
			'related_partner'  => '_ucp_related_partner_ids',
			'related_team'     => '_ucp_related_team_ids',
			'related_activity' => '_ucp_related_activity_ids',
		);
		foreach ( $meta_filters as $parameter => $meta_key ) {
			$value = $request->get_param( $parameter );
			if ( $value ) {
				$args['meta_query'][] = array( 'key' => $meta_key, 'value' => is_numeric( $value ) ? 'i:' . (int) $value . ';' : $value, 'compare' => 'LIKE' );
			}
		}
		if ( null !== $request->get_param( 'featured' ) ) {
			$args['meta_query'][] = array( 'key' => '_ucp_featured', 'value' => $request->get_param( 'featured' ) ? '1' : '', 'compare' => '=' );
		}
		if ( $request->get_param( 'after' ) || $request->get_param( 'before' ) ) {
			$args['date_query'][] = array( 'after' => $request->get_param( 'after' ), 'before' => $request->get_param( 'before' ), 'inclusive' => true );
		}
	}
}
