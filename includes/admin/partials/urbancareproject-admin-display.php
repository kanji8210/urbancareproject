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
