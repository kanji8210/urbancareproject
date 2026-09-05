<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Urban Care Project Settings', 'urbancareproject' ); ?></h1>
	<?php settings_errors(); ?>
	<?php
	$sync_status  = isset( $_GET['ucp_sync_status'] ) ? sanitize_key( wp_unslash( $_GET['ucp_sync_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sync_action  = isset( $_GET['ucp_sync_action'] ) ? sanitize_key( wp_unslash( $_GET['ucp_sync_action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sync_message = isset( $_GET['ucp_sync_message'] ) ? sanitize_text_field( wp_unslash( $_GET['ucp_sync_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $sync_status ) :
		$is_success = 'success' === $sync_status;
		$label      = 'deploy' === $sync_action ? __( 'Vercel deployment', 'urbancareproject' ) : __( 'Frontend revalidation', 'urbancareproject' );
		?>
		<div class="notice <?php echo $is_success ? 'notice-success' : 'notice-error'; ?> is-dismissible"><p>
			<?php echo esc_html( $is_success ? sprintf( __( '%s request completed successfully.', 'urbancareproject' ), $label ) : $sync_message ); ?>
		</p></div>
	<?php endif; ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'urbancareproject_options_group' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="ucp_nextjs_url"><?php esc_html_e( 'Next.js frontend URL', 'urbancareproject' ); ?></label></th>
				<td>
					<input id="ucp_nextjs_url" class="regular-text" type="url" name="ucp_nextjs_url" value="<?php echo esc_attr( get_option( 'ucp_nextjs_url', '' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Public frontend origin, for example https://urbancare.example.', 'urbancareproject' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="ucp_revalidate_secret"><?php esc_html_e( 'Revalidation secret', 'urbancareproject' ); ?></label></th>
				<td>
					<input id="ucp_revalidate_secret" class="regular-text" type="password" name="ucp_revalidate_secret" value="" placeholder="<?php echo get_option( 'ucp_revalidate_secret', '' ) ? esc_attr__( 'Configured - enter a value to replace', 'urbancareproject' ) : ''; ?>" autocomplete="new-password" />
					<p class="description"><?php esc_html_e( 'Must match REVALIDATION_SECRET in the Next.js deployment environment.', 'urbancareproject' ); ?></p>
					<?php if ( get_option( 'ucp_revalidate_secret', '' ) ) : ?><label><input type="checkbox" name="ucp_clear_revalidate_secret" value="1" /> <?php esc_html_e( 'Clear saved secret', 'urbancareproject' ); ?></label><?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="ucp_vercel_deploy_webhook"><?php esc_html_e( 'Vercel deploy webhook URL', 'urbancareproject' ); ?></label></th>
				<td>
					<input id="ucp_vercel_deploy_webhook" class="regular-text" type="password" name="ucp_vercel_deploy_webhook" value="" placeholder="<?php echo get_option( 'ucp_vercel_deploy_webhook', '' ) ? esc_attr__( 'Configured - enter a URL to replace', 'urbancareproject' ) : 'https://api.vercel.com/v1/integrations/deploy/...'; ?>" autocomplete="off" />
					<p class="description"><?php esc_html_e( 'Optional HTTPS deploy hook. It is triggered only with the manual button below.', 'urbancareproject' ); ?></p>
					<?php if ( get_option( 'ucp_vercel_deploy_webhook', '' ) ) : ?><label><input type="checkbox" name="ucp_clear_vercel_deploy_webhook" value="1" /> <?php esc_html_e( 'Clear saved webhook', 'urbancareproject' ); ?></label><?php endif; ?>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<hr />
	<h2><?php esc_html_e( 'Connection actions', 'urbancareproject' ); ?></h2>
	<p><?php esc_html_e( 'Test on-demand revalidation after saving the settings. Trigger a full deployment only when revalidation is insufficient.', 'urbancareproject' ); ?></p>
	<div style="display:flex;gap:8px;align-items:center;">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ucp_test_revalidation" />
			<?php wp_nonce_field( 'ucp_test_revalidation' ); ?>
			<?php submit_button( __( 'Test frontend revalidation', 'urbancareproject' ), 'secondary', 'submit', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ucp_trigger_deploy" />
			<?php wp_nonce_field( 'ucp_trigger_deploy' ); ?>
			<?php submit_button( __( 'Trigger Vercel deployment', 'urbancareproject' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
</div>
