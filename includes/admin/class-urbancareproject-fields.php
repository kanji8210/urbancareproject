<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Fields {
	const NONCE_ACTION = 'ucp_save_structured_fields';
	const NONCE_NAME   = 'ucp_structured_fields_nonce';

	public function add_meta_boxes() {
		foreach ( UrbanCareProject_Metadata::fields() as $post_type => $fields ) {
			if ( empty( $fields ) || in_array( $post_type, array( 'ucp_activity', 'ucp_partner', 'ucp_team' ), true ) ) {
				continue;
			}
			add_meta_box( 'ucp_structured_fields', __( 'Urban Care Details', 'urbancareproject' ), array( $this, 'render' ), $post_type, 'normal', 'high' );
		}

		add_meta_box( 'ucp_activity_details', __( 'Activity Details', 'urbancareproject' ), array( $this, 'render_activity' ), 'ucp_activity', 'normal', 'high' );
		add_meta_box( 'ucp_partner_details', __( 'Partner Details', 'urbancareproject' ), array( $this, 'render_partner' ), 'ucp_partner', 'normal', 'high' );
		add_meta_box( 'ucp_team_details', __( 'Team Member Details', 'urbancareproject' ), array( $this, 'render_team' ), 'ucp_team', 'normal', 'high' );
		remove_meta_box( 'postimagediv', 'ucp_team', 'side' );
	}

	public function render( $post ) {
		$this->render_schema_fields( $post );
	}

	public function render_activity( $post ) {
		$fields = UrbanCareProject_Metadata::fields()['ucp_activity'];
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<div class="ucp-activity-editor">
			<section class="ucp-activity-section">
				<h3><?php esc_html_e( 'Programme timing', 'urbancareproject' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Set the overall period and primary place for this programme strand.', 'urbancareproject' ); ?></p>
				<div class="ucp-activity-grid">
					<?php foreach ( array( '_ucp_start_date', '_ucp_end_date', '_ucp_ongoing', '_ucp_location', '_ucp_display_order', '_ucp_featured' ) as $key ) : ?>
						<?php $this->render_field( $key, $fields[ $key ], get_post_meta( $post->ID, $key, true ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="ucp-activity-section">
				<h3><?php esc_html_e( 'Programme phases', 'urbancareproject' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Add the distinct stages, collaborations, or fieldwork streams within this activity.', 'urbancareproject' ); ?></p>
				<?php $this->render_field( '_ucp_activity_phases', $fields['_ucp_activity_phases'], get_post_meta( $post->ID, '_ucp_activity_phases', true ) ); ?>
			</section>
			<section class="ucp-activity-section">
				<h3><?php esc_html_e( 'Media gallery', 'urbancareproject' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Choose supporting images and arrange them in display order.', 'urbancareproject' ); ?></p>
				<?php $this->render_field( '_ucp_gallery_ids', $fields['_ucp_gallery_ids'], get_post_meta( $post->ID, '_ucp_gallery_ids', true ) ); ?>
			</section>
			<section class="ucp-activity-section">
				<h3><?php esc_html_e( 'People and places', 'urbancareproject' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Connect this activity to existing profiles and study locations.', 'urbancareproject' ); ?></p>
				<div class="ucp-activity-grid">
					<?php foreach ( array( '_ucp_related_team_ids', '_ucp_related_partner_ids', '_ucp_related_site_ids' ) as $key ) : ?>
						<?php $this->render_field( $key, $fields[ $key ], get_post_meta( $post->ID, $key, true ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<?php
	}

	public function render_partner( $post ) {
		$this->render_schema_fields( $post );
	}

	public function render_team( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$this->render_media_field( '_ucp_team_portrait_id', __( 'Portrait', 'urbancareproject' ), get_post_thumbnail_id( $post ), __( 'Choose portrait', 'urbancareproject' ) );
		$this->render_schema_fields( $post, false );
	}

	private function render_schema_fields( $post, $include_nonce = true ) {
		$schemas = UrbanCareProject_Metadata::fields();
		$fields  = isset( $schemas[ $post->post_type ] ) ? $schemas[ $post->post_type ] : array();
		if ( $include_nonce ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		}

		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['legacy'] ) ) {
				continue;
			}
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
			if ( ! empty( $field['legacy'] ) && ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ( 'checkbox' === $field['input'] ? false : $field['default'] );
			$value = call_user_func( array( 'UrbanCareProject_Metadata', $field['sanitize'] ), $raw );
			if ( 'ucp_activity' === $post->post_type && '_ucp_end_date' === $key ) {
				$start_date = UrbanCareProject_Metadata::sanitize_date( isset( $_POST['_ucp_start_date'] ) ? wp_unslash( $_POST['_ucp_start_date'] ) : '' );
				$ongoing    = UrbanCareProject_Metadata::sanitize_boolean( isset( $_POST['_ucp_ongoing'] ) ? wp_unslash( $_POST['_ucp_ongoing'] ) : false );
				if ( $ongoing || ( $start_date && $value && $value < $start_date ) ) {
					$value = '';
				}
			}
			update_post_meta( $post_id, $key, $value );
		}
	}

	public function enqueue_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! in_array( $screen->post_type, array( 'ucp_activity', 'ucp_partner', 'ucp_team' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		$asset = str_replace( 'ucp_', '', $screen->post_type );
		wp_enqueue_script(
			'urbancareproject-' . $asset . '-fields',
			URBANCAREPROJECT_URL . 'includes/admin/js/urbancareproject-' . $asset . '-fields.js',
			array( 'jquery' ),
			URBANCAREPROJECT_VERSION,
			true
		);
		if ( 'activity' === $asset ) {
			wp_enqueue_style(
				'urbancareproject-activity-fields',
				URBANCAREPROJECT_URL . 'includes/admin/css/urbancareproject-activity-fields.css',
				array(),
				URBANCAREPROJECT_VERSION
			);
		}
	}

	public function title_placeholder( $placeholder, $post ) {
		if ( 'ucp_partner' === $post->post_type ) {
			return __( 'Partner name', 'urbancareproject' );
		}
		if ( 'ucp_study_site' === $post->post_type ) {
			return __( 'Study site name', 'urbancareproject' );
		}
		if ( 'ucp_activity' === $post->post_type ) {
			return __( 'Programme strand title', 'urbancareproject' );
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
			<?php elseif ( 'publication_select' === $field['input'] ) : ?>
				<?php $publications = get_posts( array( 'post_type' => 'ucp_publication', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
				<select class="widefat" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>[]" multiple size="8">
					<?php foreach ( $publications as $publication ) : ?>
						<option value="<?php echo esc_attr( $publication->ID ); ?>" <?php selected( in_array( (int) $publication->ID, array_map( 'absint', (array) $value ), true ) ); ?>><?php echo esc_html( get_the_title( $publication ) ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php elseif ( 'publications' === $field['input'] ) : ?>
				<?php $this->render_publications_field( $key, (array) $value ); ?>
			<?php elseif ( 'activity_phases' === $field['input'] ) : ?>
				<?php $this->render_activity_phases_field( $key, (array) $value ); ?>
			<?php elseif ( 'gallery' === $field['input'] ) : ?>
				<?php $this->render_gallery_field( $key, (array) $value ); ?>
			<?php elseif ( in_array( $field['input'], array( 'team_select', 'partner_multi_select', 'study_site_select' ), true ) ) : ?>
				<?php
				$post_types = array(
					'team_select'          => 'ucp_team',
					'partner_multi_select' => 'ucp_partner',
					'study_site_select'    => 'ucp_study_site',
				);
				$this->render_relation_field( $key, $post_types[ $field['input'] ], (array) $value );
				?>
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

	private function render_publications_field( $key, $publications ) {
		?>
		<div data-ucp-publications>
			<div data-ucp-publication-list>
				<?php foreach ( $publications as $index => $publication ) : ?>
					<?php $this->render_publication_row( $key, $index, $publication ); ?>
				<?php endforeach; ?>
			</div>
			<template data-ucp-publication-template><?php $this->render_publication_row( $key, '__INDEX__', array() ); ?></template>
			<button type="button" class="button" data-ucp-publication-add><?php esc_html_e( 'Add publication', 'urbancareproject' ); ?></button>
		</div>
		<?php
	}

	private function render_activity_phases_field( $key, $phases ) {
		?>
		<div data-ucp-phases data-field-name="<?php echo esc_attr( $key ); ?>">
			<div data-ucp-phase-list>
				<?php foreach ( $phases as $index => $phase ) : ?>
					<?php $this->render_activity_phase_row( $key, $index, $phase ); ?>
				<?php endforeach; ?>
			</div>
			<template data-ucp-phase-template><?php $this->render_activity_phase_row( $key, '__INDEX__', array() ); ?></template>
			<button type="button" class="button button-secondary" data-ucp-phase-add><?php esc_html_e( 'Add programme phase', 'urbancareproject' ); ?></button>
		</div>
		<?php
	}

	private function render_activity_phase_row( $key, $index, $phase ) {
		$name     = $key . '[' . $index . ']';
		$image_id = isset( $phase['imageId'] ) ? absint( $phase['imageId'] ) : 0;
		$preview  = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<fieldset class="ucp-phase" data-ucp-phase-row>
			<legend><?php esc_html_e( 'Programme phase', 'urbancareproject' ); ?></legend>
			<div class="ucp-activity-grid">
				<p><label><strong><?php esc_html_e( 'Phase title', 'urbancareproject' ); ?></strong><br /><input class="widefat" type="text" name="<?php echo esc_attr( $name . '[title]' ); ?>" value="<?php echo esc_attr( isset( $phase['title'] ) ? $phase['title'] : '' ); ?>" /></label></p>
				<p><label><strong><?php esc_html_e( 'Start date', 'urbancareproject' ); ?></strong><br /><input type="date" name="<?php echo esc_attr( $name . '[startDate]' ); ?>" value="<?php echo esc_attr( isset( $phase['startDate'] ) ? $phase['startDate'] : '' ); ?>" /></label></p>
				<p><label><strong><?php esc_html_e( 'End date', 'urbancareproject' ); ?></strong><br /><input type="date" name="<?php echo esc_attr( $name . '[endDate]' ); ?>" value="<?php echo esc_attr( isset( $phase['endDate'] ) ? $phase['endDate'] : '' ); ?>" data-ucp-phase-end /></label></p>
				<p><label><input type="checkbox" name="<?php echo esc_attr( $name . '[ongoing]' ); ?>" value="1" <?php checked( ! empty( $phase['ongoing'] ) ); ?> data-ucp-phase-ongoing /> <strong><?php esc_html_e( 'Ongoing phase', 'urbancareproject' ); ?></strong></label></p>
			</div>
			<p><label><strong><?php esc_html_e( 'Short summary', 'urbancareproject' ); ?></strong><br /><textarea class="widefat" rows="4" name="<?php echo esc_attr( $name . '[summary]' ); ?>"><?php echo esc_textarea( isset( $phase['summary'] ) ? $phase['summary'] : '' ); ?></textarea></label></p>
			<div class="ucp-phase__media" data-ucp-phase-media>
				<strong><?php esc_html_e( 'Phase image', 'urbancareproject' ); ?></strong>
				<span data-ucp-phase-preview><?php if ( $preview ) : ?><img src="<?php echo esc_url( $preview ); ?>" alt="" /><?php endif; ?></span>
				<input type="hidden" name="<?php echo esc_attr( $name . '[imageId]' ); ?>" value="<?php echo esc_attr( $image_id ); ?>" data-ucp-phase-image-id />
				<button type="button" class="button" data-ucp-phase-image-select><?php esc_html_e( 'Choose image', 'urbancareproject' ); ?></button>
				<button type="button" class="button-link-delete" data-ucp-phase-image-remove<?php echo $image_id ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove image', 'urbancareproject' ); ?></button>
			</div>
			<div class="ucp-phase__actions">
				<button type="button" class="button" data-ucp-phase-up><?php esc_html_e( 'Move up', 'urbancareproject' ); ?></button>
				<button type="button" class="button" data-ucp-phase-down><?php esc_html_e( 'Move down', 'urbancareproject' ); ?></button>
				<button type="button" class="button-link-delete" data-ucp-phase-remove><?php esc_html_e( 'Remove phase', 'urbancareproject' ); ?></button>
			</div>
		</fieldset>
		<?php
	}

	private function render_gallery_field( $key, $image_ids ) {
		?>
		<div class="ucp-gallery" data-ucp-gallery>
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( implode( ',', array_map( 'absint', $image_ids ) ) ); ?>" data-ucp-gallery-ids />
			<div class="ucp-gallery__list" data-ucp-gallery-list>
				<?php foreach ( $image_ids as $image_id ) : ?>
					<?php $preview = wp_get_attachment_image_url( $image_id, 'thumbnail' ); ?>
					<?php if ( $preview ) : ?>
						<div class="ucp-gallery__item" data-ucp-gallery-item data-id="<?php echo esc_attr( $image_id ); ?>">
							<img src="<?php echo esc_url( $preview ); ?>" alt="" />
							<div><button type="button" class="button-link" data-ucp-gallery-up><?php esc_html_e( 'Earlier', 'urbancareproject' ); ?></button><button type="button" class="button-link" data-ucp-gallery-down><?php esc_html_e( 'Later', 'urbancareproject' ); ?></button><button type="button" class="button-link-delete" data-ucp-gallery-remove><?php esc_html_e( 'Remove', 'urbancareproject' ); ?></button></div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary" data-ucp-gallery-add><?php esc_html_e( 'Choose gallery images', 'urbancareproject' ); ?></button>
		</div>
		<?php
	}

	private function render_relation_field( $key, $post_type, $value ) {
		$posts = get_posts( array( 'post_type' => $post_type, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
		?>
		<input class="widefat ucp-relation-search" type="search" placeholder="<?php esc_attr_e( 'Search available records', 'urbancareproject' ); ?>" aria-label="<?php esc_attr_e( 'Search available records', 'urbancareproject' ); ?>" data-ucp-relation-search />
		<select class="widefat ucp-relation-select" id="<?php echo esc_attr( ltrim( $key, '_' ) ); ?>" name="<?php echo esc_attr( $key ); ?>[]" multiple size="8">
			<?php foreach ( $posts as $related_post ) : ?>
				<option value="<?php echo esc_attr( $related_post->ID ); ?>" <?php selected( in_array( (int) $related_post->ID, array_map( 'absint', $value ), true ) ); ?>><?php echo esc_html( get_the_title( $related_post ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="description"><?php esc_html_e( 'Hold Ctrl (Windows) or Command (Mac) to select multiple records.', 'urbancareproject' ); ?></span>
		<?php
	}

	private function render_publication_row( $key, $index, $publication ) {
		$name = $key . '[' . $index . ']';
		?>
		<fieldset data-ucp-publication-row style="border:1px solid #dcdcde;padding:12px;margin:0 0 12px;">
			<legend class="screen-reader-text"><?php esc_html_e( 'Selected publication', 'urbancareproject' ); ?></legend>
			<p><label><strong><?php esc_html_e( 'Title', 'urbancareproject' ); ?></strong><br /><input class="widefat" type="text" name="<?php echo esc_attr( $name . '[title]' ); ?>" value="<?php echo esc_attr( isset( $publication['title'] ) ? $publication['title'] : '' ); ?>" /></label></p>
			<p><label><strong><?php esc_html_e( 'Citation / journal details', 'urbancareproject' ); ?></strong><br /><textarea class="widefat" rows="3" name="<?php echo esc_attr( $name . '[citation]' ); ?>"><?php echo esc_textarea( isset( $publication['citation'] ) ? $publication['citation'] : '' ); ?></textarea></label></p>
			<p><label><strong><?php esc_html_e( 'Publication year', 'urbancareproject' ); ?></strong><br /><input type="number" min="1000" max="<?php echo esc_attr( (int) gmdate( 'Y' ) + 1 ); ?>" name="<?php echo esc_attr( $name . '[year]' ); ?>" value="<?php echo esc_attr( isset( $publication['year'] ) ? $publication['year'] : '' ); ?>" /></label></p>
			<p><label><strong><?php esc_html_e( 'External link / DOI', 'urbancareproject' ); ?></strong><br /><input class="widefat" type="url" name="<?php echo esc_attr( $name . '[url]' ); ?>" value="<?php echo esc_attr( isset( $publication['url'] ) ? $publication['url'] : '' ); ?>" /></label></p>
			<p>
				<button type="button" class="button" data-ucp-publication-up><?php esc_html_e( 'Move up', 'urbancareproject' ); ?></button>
				<button type="button" class="button" data-ucp-publication-down><?php esc_html_e( 'Move down', 'urbancareproject' ); ?></button>
				<button type="button" class="button-link-delete" data-ucp-publication-remove><?php esc_html_e( 'Remove', 'urbancareproject' ); ?></button>
			</p>
		</fieldset>
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