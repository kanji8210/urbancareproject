<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Metadata {
	public function register() {
		foreach ( self::fields() as $post_type => $fields ) {
			foreach ( $fields as $key => $field ) {
				$args = array(
					'single'            => true,
					'type'              => $field['type'],
					'default'           => $field['default'],
					'sanitize_callback' => array( __CLASS__, $field['sanitize'] ),
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
					'show_in_rest'      => self::rest_schema( $field ),
				);

				register_post_meta( $post_type, $key, $args );
			}
		}
	}

	public static function fields() {
		return array(
			'ucp_project'     => array(
				'_ucp_funding_statement'   => self::field( 'Funding statement', 'textarea' ),
				'_ucp_objectives'          => self::array_field( 'Objectives', 'One objective per line.' ),
				'_ucp_methodology_summary' => self::field( 'Methodology summary', 'textarea' ),
				'_ucp_participation_summary' => self::field( 'Participation summary', 'textarea' ),
				'_ucp_study_area_overview' => self::field( 'Study-area overview', 'textarea' ),
				'_ucp_seo_description'     => self::field( 'SEO description', 'textarea' ),
			),
			'ucp_activity'    => array(
				'_ucp_activity_date'       => self::field( 'Activity date', 'date', 'string', '', 'sanitize_date' ),
				'_ucp_location'            => self::field( 'Location / field site', 'text' ),
				'_ucp_gallery_ids'         => self::id_array_field( 'Gallery attachment IDs' ),
				'_ucp_related_site_ids'    => self::id_array_field( 'Related study-site IDs' ),
				'_ucp_related_partner_ids' => self::id_array_field( 'Related partner IDs' ),
			),
			'ucp_publication' => array(
				'_ucp_author_display'   => self::field( 'Authors' ),
				'_ucp_publication_date' => self::field( 'Publication date', 'date', 'string', '', 'sanitize_date' ),
				'_ucp_publication_type' => self::field( 'Publication type', 'select', 'string', 'other', 'sanitize_publication_type', '', self::publication_types() ),
				'_ucp_journal'          => self::field( 'Journal / publisher' ),
				'_ucp_doi_url'          => self::field( 'DOI URL', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_pdf_attachment_id' => self::field( 'PDF attachment ID', 'number', 'integer', 0, 'sanitize_integer' ),
				'_ucp_pdf_url'          => self::field( 'External PDF URL', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_related_team_ids' => self::id_array_field( 'Related team-member IDs' ),
				'_ucp_featured'         => self::field( 'Featured publication', 'checkbox', 'boolean', false, 'sanitize_boolean' ),
			),
			'ucp_team'        => array(
				'_ucp_role'                      => self::field( 'Project role / title' ),
				'_ucp_institutional_affiliation' => self::field( 'Institutional affiliation' ),
				'_ucp_selected_publications'     => self::publication_array_field(),
				'_ucp_related_publication_ids'   => self::publication_id_array_field(),
				'_ucp_orcid_url'                  => self::field( 'ORCID iD', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_google_scholar_url'         => self::field( 'Google Scholar profile', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_researchgate_url'           => self::field( 'ResearchGate profile', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_portfolio_url'              => self::field( 'Portfolio / media profile', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_linkedin_url'               => self::field( 'LinkedIn profile', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_public_email'               => self::field( 'Public email', 'email', 'string', '', 'sanitize_email_value' ),
				'_ucp_show_email'                 => self::field( 'Show email publicly', 'checkbox', 'boolean', false, 'sanitize_boolean' ),
				'_ucp_display_order'              => self::field( 'Display order', 'number', 'integer', 0, 'sanitize_integer' ),
				'_ucp_partner_id'                 => self::legacy_field( 'Institution / partner', 'partner_select', 'integer', 0, 'sanitize_integer' ),
				'_ucp_profile_url'                => self::legacy_field( 'Profile URL', 'url', 'string', '', 'sanitize_url' ),
				'_ucp_additional_links'           => self::legacy_array_field( 'Additional links', 'sanitize_url_array' ),
			),
			'ucp_partner'     => array(
				'_ucp_partner_type' => self::field( 'Partner type', 'select', 'string', 'institutional', 'sanitize_partner_type', '', self::partner_types() ),
				'_ucp_media_id'     => self::field( 'Logo', 'media', 'integer', 0, 'sanitize_integer' ),
				'_ucp_project_role' => self::field( 'Role in the project', 'textarea' ),
				'_ucp_about'        => self::field( 'About the institution', 'textarea' ),
				'_ucp_website_url'  => self::field( 'Website or profile URL', 'url', 'string', '', 'sanitize_url' ),
			),
			'ucp_study_site'  => array(
				'_ucp_location_name'       => self::field( 'Location', 'text', 'string', '', 'sanitize_text', '', array() ),
				'_ucp_site_category'       => self::field( 'Site category' ),
				'_ucp_latitude'            => self::field( 'Latitude', 'number', 'number', null, 'sanitize_latitude', 'step="any"' ),
				'_ucp_longitude'           => self::field( 'Longitude', 'number', 'number', null, 'sanitize_longitude', 'step="any"' ),
				'_ucp_coordinates_verified' => self::field( 'Coordinates verified', 'checkbox', 'boolean', false, 'sanitize_boolean' ),
				'_ucp_gallery_ids'         => self::id_array_field( 'Gallery attachment IDs' ),
				'_ucp_related_activity_ids' => self::id_array_field( 'Related activity IDs' ),
			),
			'ucp_field_story' => array(
				'_ucp_gallery_ids'       => self::id_array_field( 'Gallery attachment IDs' ),
				'_ucp_creator_credit'    => self::field( 'Photographer / creator credit' ),
				'_ucp_closing_statement' => self::field( 'Closing statement', 'textarea' ),
				'_ucp_related_site_ids'  => self::id_array_field( 'Related study-site IDs' ),
				'_ucp_related_team_ids'  => self::id_array_field( 'Related team-member IDs' ),
			),
		);
	}

	public static function publication_types() {
		return array(
			'journal-article'  => 'Journal article',
			'book-chapter'     => 'Book chapter',
			'report'           => 'Report',
			'working-paper'    => 'Working paper',
			'policy-brief'     => 'Policy brief',
			'conference-paper' => 'Conference paper',
			'other'            => 'Other',
		);
	}

	public static function partner_types() {
		return array(
			'institutional' => 'Institutional',
			'individual'    => 'Individual',
		);
	}

	private static function field( $label, $input = 'text', $type = 'string', $default = '', $sanitize = 'sanitize_text', $attributes = '', $options = array() ) {
		return compact( 'label', 'input', 'type', 'default', 'sanitize', 'attributes', 'options' );
	}

	private static function array_field( $label, $description = '', $sanitize = 'sanitize_text_array' ) {
		$field                = self::field( $label, 'lines', 'array', array(), $sanitize );
		$field['description'] = $description;
		$field['items_type']  = 'string';
		return $field;
	}

	private static function id_array_field( $label, $input = 'ids' ) {
		$field                = self::array_field( $label, 'Comma-separated WordPress IDs.', 'sanitize_id_array' );
		$field['input']       = $input;
		$field['items_type']  = 'integer';
		return $field;
	}

	private static function publication_array_field() {
		$field                 = self::field( 'Selected publications', 'publications', 'array', array(), 'sanitize_publications' );
		$field['items_type']   = 'object';
		$field['items_schema'] = array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'title'    => array( 'type' => 'string' ),
				'citation' => array( 'type' => 'string' ),
				'year'     => array( 'type' => array( 'integer', 'null' ) ),
				'url'      => array( 'type' => 'string', 'format' => 'uri' ),
			),
		);
		return $field;
	}

	private static function publication_id_array_field() {
		$field             = self::id_array_field( 'Related CMS publications', 'publication_select' );
		$field['sanitize'] = 'sanitize_publication_id_array';
		return $field;
	}

	private static function legacy_field( $label, $input, $type, $default, $sanitize ) {
		$field          = self::field( $label, $input, $type, $default, $sanitize );
		$field['legacy'] = true;
		return $field;
	}

	private static function legacy_array_field( $label, $sanitize ) {
		$field           = self::array_field( $label, '', $sanitize );
		$field['legacy'] = true;
		return $field;
	}

	private static function rest_schema( $field ) {
		if ( 'array' !== $field['type'] ) {
			return true;
		}

		return array(
			'schema' => array(
				'type'  => 'array',
				'items' => isset( $field['items_schema'] ) ? $field['items_schema'] : array( 'type' => $field['items_type'] ),
			),
		);
	}

	public static function sanitize_text( $value ) {
		return sanitize_text_field( $value );
	}

	public static function sanitize_textarea( $value ) {
		return sanitize_textarea_field( $value );
	}

	public static function sanitize_url( $value ) {
		return esc_url_raw( $value );
	}

	public static function sanitize_email_value( $value ) {
		return sanitize_email( $value );
	}

	public static function sanitize_integer( $value ) {
		return absint( $value );
	}

	public static function sanitize_boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	public static function sanitize_date( $value ) {
		$value = sanitize_text_field( $value );
		$date  = DateTime::createFromFormat( '!Y-m-d', $value );
		return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
	}

	public static function sanitize_latitude( $value ) {
		return self::sanitize_coordinate( $value, -90, 90 );
	}

	public static function sanitize_longitude( $value ) {
		return self::sanitize_coordinate( $value, -180, 180 );
	}

	private static function sanitize_coordinate( $value, $minimum, $maximum ) {
		if ( '' === $value || null === $value || ! is_numeric( $value ) ) {
			return null;
		}
		$value = (float) $value;
		return $value >= $minimum && $value <= $maximum ? $value : null;
	}

	public static function sanitize_text_array( $value ) {
		return self::sanitize_array( $value, 'sanitize_text_field' );
	}

	public static function sanitize_url_array( $value ) {
		return self::sanitize_array( $value, 'esc_url_raw' );
	}

	public static function sanitize_id_array( $value ) {
		$values = is_array( $value ) ? $value : preg_split( '/[\s,]+/', (string) $value );
		$values = array_filter( array_map( 'absint', $values ) );
		return array_values( array_unique( $values ) );
	}

	public static function sanitize_publication_id_array( $value ) {
		return array_values(
			array_filter(
				self::sanitize_id_array( $value ),
				function ( $post_id ) {
					return 'ucp_publication' === get_post_type( $post_id );
				}
			)
		);
	}

	public static function sanitize_publications( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$publications = array();
		$maximum_year = (int) gmdate( 'Y' ) + 1;
		foreach ( $value as $publication ) {
			if ( ! is_array( $publication ) ) {
				continue;
			}

			$title = sanitize_text_field( isset( $publication['title'] ) ? $publication['title'] : '' );
			if ( '' === $title ) {
				continue;
			}

			$year = isset( $publication['year'] ) ? absint( $publication['year'] ) : 0;
			$publications[] = array(
				'title'    => $title,
				'citation' => sanitize_textarea_field( isset( $publication['citation'] ) ? $publication['citation'] : '' ),
				'year'     => $year >= 1000 && $year <= $maximum_year ? $year : null,
				'url'      => esc_url_raw( isset( $publication['url'] ) ? $publication['url'] : '' ),
			);
		}

		return $publications;
	}

	public static function sanitize_publication_type( $value ) {
		$value = sanitize_key( $value );
		return array_key_exists( $value, self::publication_types() ) ? $value : 'other';
	}

	public static function sanitize_partner_type( $value ) {
		$value = sanitize_key( $value );
		return array_key_exists( $value, self::partner_types() ) ? $value : 'institutional';
	}

	private static function sanitize_array( $value, $callback ) {
		$values = is_array( $value ) ? $value : preg_split( '/\r\n|\r|\n/', (string) $value );
		$values = array_filter( array_map( $callback, $values ) );
		return array_values( array_unique( $values ) );
	}
}