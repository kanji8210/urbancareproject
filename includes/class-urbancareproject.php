<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject {
	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->plugin_name = 'urbancareproject';
		$this->version     = URBANCAREPROJECT_VERSION;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_api_hooks();
	}

	private function load_dependencies() {
		require_once URBANCAREPROJECT_PATH . 'includes/class-urbancareproject-loader.php';
		require_once URBANCAREPROJECT_PATH . 'includes/content/class-urbancareproject-content-types.php';
		require_once URBANCAREPROJECT_PATH . 'includes/content/class-urbancareproject-metadata.php';
		require_once URBANCAREPROJECT_PATH . 'includes/content/class-urbancareproject-seeder.php';
		require_once URBANCAREPROJECT_PATH . 'includes/admin/class-urbancareproject-admin.php';
		require_once URBANCAREPROJECT_PATH . 'includes/admin/class-urbancareproject-fields.php';
		require_once URBANCAREPROJECT_PATH . 'includes/api/class-urbancareproject-serializer.php';
		require_once URBANCAREPROJECT_PATH . 'includes/api/class-urbancareproject-rest-api.php';

		$this->loader = new UrbanCareProject_Loader();
	}

	private function define_admin_hooks() {
		$content_types = new UrbanCareProject_Content_Types();
		$metadata      = new UrbanCareProject_Metadata();
		$seeder        = new UrbanCareProject_Seeder();
		$admin = new UrbanCareProject_Admin( $this->plugin_name, $this->version );
		$fields = new UrbanCareProject_Fields();

		$this->loader->add_action( 'init', $content_types, 'register' );
		$this->loader->add_action( 'init', $metadata, 'register' );
		$this->loader->add_filter( 'wp_insert_post_empty_content', $seeder, 'prevent_additional_project', 10, 2 );
		$this->loader->add_action( 'admin_menu', $admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'add_meta_boxes', $fields, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post', $fields, 'save', 10, 2 );
	}

	private function define_api_hooks() {
		$api = new UrbanCareProject_REST_API();
		$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
	}

	public function run() {
		$this->loader->run();
	}
}
