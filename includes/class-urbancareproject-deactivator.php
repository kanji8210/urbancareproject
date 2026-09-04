<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Deactivator {
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
