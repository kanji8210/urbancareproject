<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Serializer {
	private const RELATION_FIELDS = array(
		'_ucp_related_site_ids',
		'_ucp_related_partner_ids',
		'_ucp_related_team_ids',
		'_ucp_related_activity_ids',
	);

	public function serialize( $post ) {
		$post = get_post( $post );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return null;
		}

		return array(
			'id'            => (int) $post->ID,
			'type'          => $post->post_type,
			'slug'          => $post->post_name,
			'title'         => get_the_title( $post ),
			'excerpt'       => apply_filters( 'the_excerpt', get_the_excerpt( $post ) ),
			'content'       => apply_filters( 'the_content', $post->post_content ),
			'publishedAt'   => get_post_time( DATE_ATOM, true, $post ),
			'modifiedAt'    => get_post_modified_time( DATE_ATOM, true, $post ),
			'featuredImage' => $this->serialize_attachment( get_post_thumbnail_id( $post ) ),
			'taxonomies'    => $this->serialize_taxonomies( $post ),
			'meta'          => $this->serialize_meta( $post ),
		);
	}

	private function serialize_meta( $post ) {
		$schemas = UrbanCareProject_Metadata::fields();
		$fields  = isset( $schemas[ $post->post_type ] ) ? $schemas[ $post->post_type ] : array();
		$data    = array();

		foreach ( $fields as $key => $field ) {
			if ( '_ucp_public_email' === $key && ! get_post_meta( $post->ID, '_ucp_show_email', true ) ) {
				continue;
			}
			if ( in_array( $key, array( '_ucp_show_email', '_ucp_coordinates_verified' ), true ) ) {
				continue;
			}
			if ( in_array( $key, array( '_ucp_latitude', '_ucp_longitude' ), true ) && ! get_post_meta( $post->ID, '_ucp_coordinates_verified', true ) ) {
				continue;
			}

			$value      = get_post_meta( $post->ID, $key, true );
			$public_key = lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', substr( $key, 5 ) ) ) ) );
			if ( '_ucp_gallery_ids' === $key ) {
				$data['gallery'] = array_values( array_filter( array_map( array( $this, 'serialize_attachment' ), (array) $value ) ) );
				continue;
			}
			if ( '_ucp_pdf_attachment_id' === $key ) {
				$data['pdfAttachment'] = $this->serialize_attachment( $value );
				continue;
			}
			if ( '_ucp_media_id' === $key ) {
				$data['media'] = $this->serialize_attachment( $value );
				continue;
			}
			if ( in_array( $key, self::RELATION_FIELDS, true ) ) {
				$data[ $public_key ] = array_values( array_filter( array_map( array( $this, 'serialize_relation' ), (array) $value ) ) );
				continue;
			}

			$data[ $public_key ] = $value;
		}

		return $data;
	}

	private function serialize_taxonomies( $post ) {
		$data = array();
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}
			$data[ $taxonomy ] = array_map(
				function ( $term ) {
					return array( 'id' => (int) $term->term_id, 'name' => $term->name, 'slug' => $term->slug );
				},
				$terms
			);
		}
		return $data;
	}

	public function serialize_relation( $post_id ) {
		$post = get_post( absint( $post_id ) );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return null;
		}
		return array( 'id' => (int) $post->ID, 'type' => $post->post_type, 'slug' => $post->post_name, 'title' => get_the_title( $post ) );
	}

	private function serialize_attachment( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		$url           = $attachment_id ? wp_get_attachment_url( $attachment_id ) : false;
		if ( ! $url ) {
			return null;
		}
		$metadata = wp_get_attachment_metadata( $attachment_id );
		return array(
			'id'     => $attachment_id,
			'url'    => $url,
			'alt'    => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'width'  => isset( $metadata['width'] ) ? (int) $metadata['width'] : null,
			'height' => isset( $metadata['height'] ) ? (int) $metadata['height'] : null,
		);
	}
}