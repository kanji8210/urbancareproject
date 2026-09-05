<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Fields {
	const NONCE_ACTION = 'ucp_save_structured_fields';
	const NONCE_NAME   = 'ucp_structured_fields_nonce';

	public function add_meta_boxes() {
		foreach ( UrbanCareProject_Metadata::fields() as $post_type => $fields ) {
			if ( empty( $fields ) ) {
				continue;
			}
			add_meta_box( 'ucp_structured_fields', __( 'Urban Care Details', 'urbancareproject' ), array( $this, 'render' ), $post_type, 'normal', 'high' );
		}

		remove_meta_box( 'postimagediv', 'ucp_team', 'side' );
	}

	public function render( $post ) {
		$schemas = UrbanCareProject_Metadata::fields();
		$fields  = isset( $schemas[ $post->post_type ] ) ? $schemas[ $post->post_type ] : array();
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		if ( 'ucp_team' === $post->post_type ) {
			$this->render_media_field( '_ucp_team_portrait_id', __( 'Portrait', 'urbancareproject' ), get_post_thumbnail_id( $post ), __( 'Choose portrait', 'urbancareproject' ) );
		}

		foreach ( $fields as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			$this->render_field( $key, $field, $value );
		}
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$schemas = UrbanCareProject_Metadata::fields();
		if ( ! isset( $schemas[ $post->post_type ] ) ) {
			return;
		}

		if ( 'ucp_team' === $post->post_type ) {
			$portrait_id = isset( $_POST['_ucp_team_portrait_id'] ) ? absint( wp_unslash( $_POST['_ucp_team_portrait_id'] ) ) : 0;
			if ( $portrait_id ) {
				set_post_thumbnail( $post_id, $portrait_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		foreach ( $schemas[ $post->post_type ] as $key => $field ) {
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ( 'checkbox' === $field['input'] ? false : $field['default'] );
			$value = call_user_func( array( 'UrbanCareProject_Metadata', $field['sanitize'] ), $raw );
			update_post_meta( $post_id, $key, $value );
		}
	}

	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! in_array( $screen->post_type, array( 'ucp_partner', 'ucp_team' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'urbancareproject-partner-fields',
			URBANCAREPROJECT_URL . 'includes/admin/js/urbancareproject-partner-fields.js',
			array( 'jquery' ),
			URBANCAREPROJECT_VERSION,
			true
		);
	}

	public function title_placeholder( $placeholder, $post ) {
		if ( 'ucp_partner' === $post->post_type ) {
			return __( 'Partner name', 'urbancareproject' );
		}
		return 'ucp_team' === $post->post_type ? __( 'Team member name', 'urbancareproject' ) : $placeholder;
	}

	private function render_field( $key, $field, $value ) {
		$id          = ltrim( $key, '_' );
		$description = isset( $field['description'] ) ? $field['description'] : '';
		?>
		<p class="ucp-field ucp-field--<?php echo esc_attr( $field['input'] ); ?>">
			<label for="<?php echo esc_attr( $id ); ?>"<?php echo '_ucp_media_id' === $key ? ' data-ucp-media-label' : ''; ?>><strong><?php echo esc_html( $field['label'] ); ?></strong></label><br />
			<?php if ( 'textarea' === $field['input'] || 'lines' === $field['input'] ) : ?>
				<textarea class="widefat" rows="5" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>"><?php echo esc_textarea( is_array( $value ) ? implode( "\n", $value ) : $value ); ?></textarea>
			<?php elseif ( 'ids' === $field['input'] ) : ?>
				<input class="widefat" type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( is_array( $value ) ? implode( ', ', $value ) : $value ); ?>" />
			<?php elseif ( 'select' === $field['input'] ) : ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>">
					<?php foreach ( $field['options'] as $option_value => $label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ( 'partner_select' === $field['input'] ) : ?>
				<?php $partners = get_posts( array( 'post_type' => 'ucp_partner', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
				<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>">
					<option value="0"><?php esc_html_e( 'No institution selected', 'urbancareproject' ); ?></option>
					<?php foreach ( $partners as $partner ) : ?>
						<option value="<?php echo esc_attr( $partner->ID ); ?>" <?php selected( (int) $value, (int) $partner->ID ); ?>><?php echo esc_html( get_the_title( $partner ) ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ( 'checkbox' === $field['input'] ) : ?>
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( (bool) $value ); ?> />
			<?php elseif ( 'media' === $field['input'] ) : ?>
				<?php $this->render_media_control( $key, $value, __( 'Choose media', 'urbancareproject' ) ); ?>
			<?php else : ?>
				<input class="widefat" type="<?php echo esc_attr( $field['input'] ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $field['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
			<?php endif; ?>
			<?php if ( $description ) : ?><span class="description"><?php echo esc_html( $description ); ?></span><?php endif; ?>
		</p>
		<?php
	}

	private function render_media_field( $key, $label, $value, $button_label ) {
		?>
		<p class="ucp-field ucp-field--media">
			<label for="<?php echo esc_attr( ltrim( $key, '_' ) ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br />
			<?php $this->render_media_control( $key, $value, $button_label ); ?>
		</p>
		<?php
	}

	private function render_media_control( $key, $value, $button_label ) {
		$id          = ltrim( $key, '_' );
		$preview_url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
		?>
		<span class="ucp-media-field" data-ucp-media-field data-ucp-media-title="<?php echo esc_attr( $button_label ); ?>">
			<span class="ucp-media-preview" data-ucp-media-preview>
				<?php if ( $preview_url ) : ?><img src="<?php echo esc_url( $preview_url ); ?>" alt="" style="display:block;max-width:240px;max-height:160px;margin:8px 0;object-fit:contain;background:#fff;border:1px solid #dcdcde;padding:8px;" /><?php endif; ?>
			</span>
			<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" data-ucp-media-id />
			<button type="button" class="button" data-ucp-media-select><?php echo esc_html( $button_label ); ?></button>
			<button type="button" class="button-link-delete" data-ucp-media-remove<?php echo $value ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'urbancareproject' ); ?></button>
		</span>
		<?php
	}
}