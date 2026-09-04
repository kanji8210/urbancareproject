<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Activator {
	public static function activate() {
		require_once URBANCAREPROJECT_PATH . 'includes/content/class-urbancareproject-content-types.php';

		$content_types = new UrbanCareProject_Content_Types();
		$content_types->register();
		flush_rewrite_rules();
	}
}
