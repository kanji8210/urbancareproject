#!/usr/bin/env bash

set -euo pipefail

TARGET_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_FILE="$TARGET_DIR/urbancareproject.php"

if [[ -e "$PLUGIN_FILE" ]]; then
  echo "Refusing to overwrite existing plugin: $PLUGIN_FILE" >&2
  exit 1
fi

echo "Creating Urban Care Project plugin in: $TARGET_DIR"

mkdir -p \
  "$TARGET_DIR/languages" \
  "$TARGET_DIR/includes/admin/partials" \
  "$TARGET_DIR/includes/api" \
  "$TARGET_DIR/includes/public"

cat > "$PLUGIN_FILE" <<'PHP'
<?php
/**
 * Plugin Name:       Urban Care Project
 * Plugin URI:        https://urbancareproject.org
 * Description:       Content and REST API services for the Urban Care Project frontend.
 * Version:           1.0.0
 * Author:            Dennis Kosgei
 * Text Domain:       urbancareproject
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'URBANCAREPROJECT_VERSION', '1.0.0' );
define( 'URBANCAREPROJECT_PATH', plugin_dir_path( __FILE__ ) );
define( 'URBANCAREPROJECT_URL', plugin_dir_url( __FILE__ ) );

require_once URBANCAREPROJECT_PATH . 'includes/class-urbancareproject-activator.php';
require_once URBANCAREPROJECT_PATH . 'includes/class-urbancareproject-deactivator.php';

register_activation_hook( __FILE__, array( 'UrbanCareProject_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'UrbanCareProject_Deactivator', 'deactivate' ) );

require_once URBANCAREPROJECT_PATH . 'includes/class-urbancareproject.php';

function run_urbancareproject() {
	$plugin = new UrbanCareProject();
	$plugin->run();
}

run_urbancareproject();
PHP

cat > "$TARGET_DIR/uninstall.php" <<'PHP'
<?php
/**
 * Runs when Urban Care Project is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'ucp_nextjs_url' );
delete_option( 'ucp_revalidate_secret' );
delete_option( 'ucp_vercel_deploy_webhook' );
PHP

cat > "$TARGET_DIR/includes/class-urbancareproject-activator.php" <<'PHP'
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Activator {
	public static function activate() {
		require_once URBANCAREPROJECT_PATH . 'includes/admin/class-urbancareproject-admin.php';

		$admin = new UrbanCareProject_Admin( 'urbancareproject', URBANCAREPROJECT_VERSION );
		$admin->register_custom_post_types();
		flush_rewrite_rules();
	}
}
PHP

cat > "$TARGET_DIR/includes/class-urbancareproject-deactivator.php" <<'PHP'
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Deactivator {
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
PHP

cat > "$TARGET_DIR/includes/class-urbancareproject-loader.php" <<'PHP'
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Loader {
	protected $actions = array();
	protected $filters = array();

	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
PHP

cat > "$TARGET_DIR/includes/class-urbancareproject.php" <<'PHP'
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
		require_once URBANCAREPROJECT_PATH . 'includes/admin/class-urbancareproject-admin.php';
		require_once URBANCAREPROJECT_PATH . 'includes/api/class-urbancareproject-rest-api.php';

		$this->loader = new UrbanCareProject_Loader();
	}

	private function define_admin_hooks() {
		$admin = new UrbanCareProject_Admin( $this->plugin_name, $this->version );

		$this->loader->add_action( 'admin_menu', $admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'init', $admin, 'register_custom_post_types' );
		$this->loader->add_action( 'add_meta_boxes', $admin, 'add_custom_meta_boxes' );
		$this->loader->add_action( 'save_post_ucp_activity', $admin, 'save_activity_meta' );
	}

	private function define_api_hooks() {
		$api = new UrbanCareProject_REST_API();
		$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
	}

	public function run() {
		$this->loader->run();
	}
}
PHP

cat > "$TARGET_DIR/includes/admin/class-urbancareproject-admin.php" <<'PHP'
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

	public function register_custom_post_types() {
		register_post_type(
			'ucp_activity',
			array(
				'labels'       => array(
					'name'          => __( 'Activities', 'urbancareproject' ),
					'singular_name' => __( 'Activity', 'urbancareproject' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => 'urbancareproject-settings',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'has_archive'  => true,
			)
		);

		register_post_type(
			'ucp_team',
			array(
				'labels'       => array(
					'name'          => __( 'Team Profiles', 'urbancareproject' ),
					'singular_name' => __( 'Team Member', 'urbancareproject' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'show_in_menu' => 'urbancareproject-settings',
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
			)
		);
	}

	public function add_custom_meta_boxes() {
		add_meta_box(
			'ucp_activity_details',
			__( 'Activity Metadata', 'urbancareproject' ),
			array( $this, 'render_activity_meta_box' ),
			'ucp_activity',
			'normal',
			'high'
		);
	}

	public function render_activity_meta_box( $post ) {
		$location = get_post_meta( $post->ID, '_ucp_location', true );
		wp_nonce_field( 'ucp_save_activity_meta', 'ucp_activity_meta_nonce' );
		?>
		<p>
			<label for="ucp_location"><?php esc_html_e( 'Location / Field Site', 'urbancareproject' ); ?></label>
			<input id="ucp_location" class="widefat" type="text" name="ucp_location" value="<?php echo esc_attr( $location ); ?>" />
		</p>
		<?php
	}

	public function save_activity_meta( $post_id ) {
		if ( ! isset( $_POST['ucp_activity_meta_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['ucp_activity_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'ucp_save_activity_meta' ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['ucp_location'] ) ) {
			$location = sanitize_text_field( wp_unslash( $_POST['ucp_location'] ) );
			update_post_meta( $post_id, '_ucp_location', $location );
		}
	}

	public function display_plugin_setup_page() {
		include URBANCAREPROJECT_PATH . 'includes/admin/partials/urbancareproject-admin-display.php';
	}

	public function display_stats_page() {
		include URBANCAREPROJECT_PATH . 'includes/admin/partials/urbancareproject-stats-display.php';
	}
}
PHP

cat > "$TARGET_DIR/includes/admin/partials/urbancareproject-admin-display.php" <<'PHP'
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Urban Care Project Settings', 'urbancareproject' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'urbancareproject_options_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="ucp_nextjs_url"><?php esc_html_e( 'Next.js frontend URL', 'urbancareproject' ); ?></label></th>
				<td><input id="ucp_nextjs_url" class="regular-text" type="url" name="ucp_nextjs_url" value="<?php echo esc_attr( get_option( 'ucp_nextjs_url', '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="ucp_revalidate_secret"><?php esc_html_e( 'Revalidation secret', 'urbancareproject' ); ?></label></th>
				<td><input id="ucp_revalidate_secret" class="regular-text" type="password" name="ucp_revalidate_secret" value="<?php echo esc_attr( get_option( 'ucp_revalidate_secret', '' ) ); ?>" autocomplete="off" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="ucp_vercel_deploy_webhook"><?php esc_html_e( 'Vercel deploy webhook URL', 'urbancareproject' ); ?></label></th>
				<td><input id="ucp_vercel_deploy_webhook" class="regular-text" type="url" name="ucp_vercel_deploy_webhook" value="<?php echo esc_attr( get_option( 'ucp_vercel_deploy_webhook', '' ) ); ?>" /></td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
PHP

cat > "$TARGET_DIR/includes/admin/partials/urbancareproject-stats-display.php" <<'PHP'
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Urban Care Project API & Build Logs', 'urbancareproject' ); ?></h1>
	<p><?php esc_html_e( 'API and build monitoring will appear here in a future release.', 'urbancareproject' ); ?></p>
</div>
PHP

cat > "$TARGET_DIR/includes/api/class-urbancareproject-rest-api.php" <<'PHP'
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
PHP

cat > "$TARGET_DIR/includes/public/class-urbancareproject-public.php" <<'PHP'
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Public {
	// Public-facing hooks can be registered here when needed.
}
PHP

touch "$TARGET_DIR/languages/.gitkeep"

echo "Urban Care Project plugin boilerplate created successfully."