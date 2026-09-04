<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Admin {
	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'Urban Care Project', 'urbancareproject' ),
			__( 'Urban Care', 'urbancareproject' ),
			'manage_options',
			'urbancareproject-settings',
			array( $this, 'display_plugin_setup_page' ),
			'dashicons-location-alt',
			3
		);

		add_submenu_page(
			'urbancareproject-settings',
			__( 'Project Content', 'urbancareproject' ),
			__( 'Project Content', 'urbancareproject' ),
			'edit_posts',
			'ucp-project-content',
			array( $this, 'open_project_content' )
		);

		add_submenu_page(
			'urbancareproject-settings',
			__( 'Settings', 'urbancareproject' ),
			__( 'Settings', 'urbancareproject' ),
			'manage_options',
			'urbancareproject-settings',
			array( $this, 'display_plugin_setup_page' )
		);

		add_submenu_page(
			'urbancareproject-settings',
			__( 'Stats & Logs', 'urbancareproject' ),
			__( 'Stats & Logs', 'urbancareproject' ),
			'manage_options',
			'urbancareproject-stats',
			array( $this, 'display_stats_page' )
		);
	}

	public function open_project_content() {
		$project_id = UrbanCareProject_Seeder::canonical_project_id();
		if ( $project_id ) {
			wp_safe_redirect( admin_url( 'post.php?post=' . $project_id . '&action=edit' ) );
			exit;
		}
		wp_die( esc_html__( 'The canonical Project record is unavailable. Reactivate the plugin to create it.', 'urbancareproject' ) );
	}

	public function register_settings() {
		register_setting(
			'urbancareproject_options_group',
			'ucp_nextjs_url',
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
		register_setting(
			'urbancareproject_options_group',
			'ucp_revalidate_secret',
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		register_setting(
			'urbancareproject_options_group',
			'ucp_vercel_deploy_webhook',
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
	}

	public function display_plugin_setup_page() {
		include URBANCAREPROJECT_PATH . 'includes/admin/partials/urbancareproject-admin-display.php';
	}

	public function display_stats_page() {
		include URBANCAREPROJECT_PATH . 'includes/admin/partials/urbancareproject-stats-display.php';
	}
}
