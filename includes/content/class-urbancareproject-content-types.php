<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Content_Types {
	public function register() {
		$this->register_post_types();
		$this->register_taxonomies();
	}

	public function register_post_types() {
		$post_types = array(
			'ucp_project'     => array(
				'singular' => __( 'Project', 'urbancareproject' ),
				'plural'   => __( 'Project Content', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'archive'  => false,
			),
			'ucp_activity'    => array(
				'singular' => __( 'Activity', 'urbancareproject' ),
				'plural'   => __( 'Activities', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
				'archive'  => true,
			),
			'ucp_publication' => array(
				'singular' => __( 'Publication', 'urbancareproject' ),
				'plural'   => __( 'Publications', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'revisions' ),
				'archive'  => true,
			),
			'ucp_team'        => array(
				'singular' => __( 'Team Member', 'urbancareproject' ),
				'plural'   => __( 'Team Members', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
				'archive'  => true,
			),
			'ucp_partner'     => array(
				'singular' => __( 'Partner', 'urbancareproject' ),
				'plural'   => __( 'Partners', 'urbancareproject' ),
				'supports' => array( 'title', 'revisions' ),
				'archive'  => true,
			),
			'ucp_study_site'  => array(
				'singular' => __( 'Study Site', 'urbancareproject' ),
				'plural'   => __( 'Study Sites', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'thumbnail', 'revisions' ),
				'archive'  => true,
			),
			'ucp_field_story' => array(
				'singular' => __( 'Field Story', 'urbancareproject' ),
				'plural'   => __( 'Field Stories', 'urbancareproject' ),
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
				'archive'  => true,
			),
		);

		foreach ( $post_types as $post_type => $definition ) {
			$is_project = 'ucp_project' === $post_type;
			register_post_type(
				$post_type,
				array(
					'labels'             => $this->post_type_labels( $definition['singular'], $definition['plural'] ),
					'public'             => true,
					'show_in_rest'       => true,
					'show_in_menu'       => 'urbancareproject-settings',
					'menu_icon'          => 'dashicons-admin-post',
					'supports'           => $definition['supports'],
					'has_archive'        => $definition['archive'],
					'rewrite'            => array( 'slug' => str_replace( 'ucp_', '', $post_type ) ),
					'show_in_nav_menus'  => false,
					'exclude_from_search' => false,
					'map_meta_cap'        => true,
					'capabilities'        => $is_project ? array( 'create_posts' => 'do_not_allow' ) : array(),
				)
			);
		}
	}

	public function register_taxonomies() {
		$this->register_taxonomy(
			'ucp_theme',
			__( 'Research Theme', 'urbancareproject' ),
			__( 'Research Themes', 'urbancareproject' ),
			array( 'ucp_activity', 'ucp_publication', 'ucp_field_story' )
		);
		$this->register_taxonomy(
			'ucp_method',
			__( 'Research Method', 'urbancareproject' ),
			__( 'Research Methods', 'urbancareproject' ),
			array( 'ucp_activity', 'ucp_publication', 'ucp_field_story' )
		);
		$this->register_taxonomy(
			'ucp_activity_type',
			__( 'Activity Type', 'urbancareproject' ),
			__( 'Activity Types', 'urbancareproject' ),
			array( 'ucp_activity' )
		);
	}

	private function register_taxonomy( $taxonomy, $singular, $plural, $post_types ) {
		register_taxonomy(
			$taxonomy,
			$post_types,
			array(
				'labels'            => array(
					'name'          => $plural,
					'singular_name' => $singular,
					'search_items'  => sprintf( __( 'Search %s', 'urbancareproject' ), $plural ),
					'all_items'     => sprintf( __( 'All %s', 'urbancareproject' ), $plural ),
					'edit_item'     => sprintf( __( 'Edit %s', 'urbancareproject' ), $singular ),
					'add_new_item'  => sprintf( __( 'Add New %s', 'urbancareproject' ), $singular ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => false,
			)
		);
	}

	private function post_type_labels( $singular, $plural ) {
		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'add_new'            => __( 'Add New', 'urbancareproject' ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'urbancareproject' ), $singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'urbancareproject' ), $singular ),
			'new_item'           => sprintf( __( 'New %s', 'urbancareproject' ), $singular ),
			'view_item'          => sprintf( __( 'View %s', 'urbancareproject' ), $singular ),
			'search_items'       => sprintf( __( 'Search %s', 'urbancareproject' ), $plural ),
			'not_found'          => sprintf( __( 'No %s found.', 'urbancareproject' ), strtolower( $plural ) ),
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'urbancareproject' ), strtolower( $plural ) ),
			'all_items'          => sprintf( __( 'All %s', 'urbancareproject' ), $plural ),
			'menu_name'          => $plural,
		);
	}
}