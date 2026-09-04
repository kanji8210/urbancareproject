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
