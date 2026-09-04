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
