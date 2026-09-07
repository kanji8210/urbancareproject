<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Seeder {
	const PROJECT_ID_OPTION = 'ucp_canonical_project_id';
	const SEED_VERSION_OPTION = 'ucp_content_seed_version';
	const SEED_VERSION = '2';

	public function seed() {
		$this->seed_terms();
		$this->seed_project();
		$this->seed_field_stories();
		update_option( self::SEED_VERSION_OPTION, self::SEED_VERSION, false );
	}

	public function maybe_seed() {
		if ( self::SEED_VERSION !== get_option( self::SEED_VERSION_OPTION, '' ) ) {
			$this->seed();
		}
	}

	public function prevent_additional_project( $maybe_empty, $postarr ) {
		if ( 'ucp_project' !== ( isset( $postarr['post_type'] ) ? $postarr['post_type'] : '' ) ) {
			return $maybe_empty;
		}

		$canonical_id = self::canonical_project_id();
		$post_id      = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
		return $canonical_id && $canonical_id !== $post_id ? true : $maybe_empty;
	}

	public static function canonical_project_id() {
		$project_id = absint( get_option( self::PROJECT_ID_OPTION, 0 ) );
		if ( $project_id && 'ucp_project' === get_post_type( $project_id ) ) {
			return $project_id;
		}

		$projects = get_posts(
			array(
				'post_type'      => 'ucp_project',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		return empty( $projects ) ? 0 : absint( $projects[0] );
	}

	private function seed_terms() {
		$terms = array(
			'ucp_theme' => array(
				'urbanization'         => 'Urbanization and land-use change',
				'environmental-health' => 'Environment and health',
				'biodiversity'         => 'Biodiversity and ecological functions',
				'public-policy'        => 'Public policy and urban planning',
			),
			'ucp_method' => array(
				'remote-sensing'       => 'Remote sensing and spatial analysis',
				'household-surveys'    => 'Household surveys',
				'interviews'           => 'Interviews and qualitative research',
				'pollution-monitoring' => 'Pollution monitoring',
				'ecological-surveys'   => 'Ecological surveys',
				'participatory'        => 'Participatory research',
			),
			'ucp_activity_type' => array(
				'fieldwork'          => 'Fieldwork',
				'workshop'           => 'Workshop',
				'community-meeting'  => 'Community meeting',
				'training'           => 'Training',
				'public-engagement'  => 'Public engagement',
			),
		);

		foreach ( $terms as $taxonomy => $taxonomy_terms ) {
			foreach ( $taxonomy_terms as $slug => $name ) {
				if ( ! term_exists( $slug, $taxonomy ) ) {
					wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				}
			}
		}
	}

	private function seed_project() {
		$project_id = self::canonical_project_id();
		if ( $project_id ) {
			update_option( self::PROJECT_ID_OPTION, $project_id, false );
			return;
		}

		$project_id = wp_insert_post(
			array(
				'post_type'    => 'ucp_project',
				'post_status'  => 'draft',
				'post_title'   => 'Urban Care Here',
				'post_excerpt' => 'A multidisciplinary and participatory research initiative studying how rapid urban growth transforms land, environments, health, and everyday life, beginning in Kitengela.',
				'post_content' => '<p>Small towns such as Kitengela are growing rapidly in population and area. Agricultural land, pasture, and natural areas are being converted into residential plots, reshaping landscapes, livelihoods, access to services, and patterns of environmental exposure.</p><p>Urban Care brings environmental science, social science, spatial analysis, and public health together at the territorial scale. The project combines long-term research with the knowledge of residents, schools, clinics, NGOs, and public authorities.</p><p>Urban Care approaches care in two connected ways: paying close attention to the conditions that make a place habitable, and acting to maintain those conditions over time.</p>',
			),
			true
		);

		if ( is_wp_error( $project_id ) ) {
			return;
		}

		$metadata = array(
			'_ucp_funding_statement' => 'Urban Care is an international, multidisciplinary research initiative focused on urbanization, environment, health, and everyday life in Kenya.',
			'_ucp_objectives' => array(
				'Build lasting dialogue between science, society, and public policy.',
				'Measure land-use change, access to services, pollution, biodiversity, and ecological functions.',
				'Understand how residents experience environmental change and urbanization-related risks.',
				'Provide open data and tools for evidence-based planning, land governance, and public-health strategies.',
				'Develop indicators, dashboards, and accessible ways of sharing scientific knowledge.',
			),
			'_ucp_methodology_summary' => 'The project combines remote sensing, cadastral and field observation, household surveys, interviews, environmental monitoring, biodiversity inventories, health questionnaires, and participatory research.',
			'_ucp_participation_summary' => 'Residents and neighborhood associations help shape research protocols and field surveys. Schools, clinics, NGOs, and municipal teams contribute to monitoring, health research, and public-policy dialogue.',
			'_ucp_study_area_overview' => 'The first case study follows a gradient across Kitengela from dense central neighborhoods to rapidly subdividing rural edges, including Noonkopir, New Valley, Milimani, Acacia, Enkasiti, and Isinya.',
			'_ucp_seo_description' => 'Discover how Urban Care studies urbanization, environmental change, and health with communities and research partners in Kitengela, Kenya.',
		);

		foreach ( $metadata as $key => $value ) {
			update_post_meta( $project_id, $key, $value );
		}
		update_option( self::PROJECT_ID_OPTION, $project_id, false );
	}

	private function seed_field_stories() {
		foreach ( $this->field_story_definitions() as $story ) {
			if ( get_page_by_path( $story['slug'], OBJECT, 'ucp_field_story' ) ) {
				continue;
			}

			$story_id = wp_insert_post(
				array(
					'post_type'    => 'ucp_field_story',
					'post_status'  => 'publish',
					'post_name'    => $story['slug'],
					'post_title'   => $story['title'],
					'post_excerpt' => $story['excerpt'],
					'post_content' => $story['content'],
				),
				true
			);
			if ( is_wp_error( $story_id ) ) {
				continue;
			}

			$gallery_ids = array();
			foreach ( $story['images'] as $asset ) {
				$attachment_id = $this->import_seed_image( $story_id, $asset );
				if ( $attachment_id ) {
					$gallery_ids[] = $attachment_id;
				}
			}

			$metadata = array(
				'_ucp_gallery_ids'          => $gallery_ids,
				'_ucp_story_lenses'         => $story['lenses'],
				'_ucp_creator_credit'       => $story['creator_credit'],
				'_ucp_closing_statement'    => $story['closing_statement'],
				'_ucp_related_site_ids'     => $this->resolve_post_ids( 'ucp_study_site', $story['related_sites'] ),
				'_ucp_related_team_ids'     => $this->resolve_post_ids( 'ucp_team', $story['related_team'] ),
				'_ucp_related_activity_ids' => $this->resolve_post_ids( 'ucp_activity', $story['related_activities'] ),
				'_ucp_featured'             => $story['featured'],
				'_ucp_display_order'        => $story['display_order'],
			);
			foreach ( $metadata as $key => $value ) {
				update_post_meta( $story_id, $key, $value );
			}
			if ( $gallery_ids ) {
				set_post_thumbnail( $story_id, $gallery_ids[0] );
			}
			wp_set_object_terms( $story_id, $story['themes'], 'ucp_theme' );
			wp_set_object_terms( $story_id, $story['methods'], 'ucp_method' );
		}
	}

	private function resolve_post_ids( $post_type, $slugs ) {
		$ids = array();
		foreach ( $slugs as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $post ) {
				$ids[] = (int) $post->ID;
			}
		}
		return $ids;
	}

	protected function import_seed_image( $story_id, $asset ) {
		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_ucp_seed_asset_key',
				'meta_value'     => $asset['key'],
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}

		$source = URBANCAREPROJECT_PATH . 'assets/field-stories/' . $asset['file'];
		if ( ! is_readable( $source ) ) {
			error_log( 'Urban Care Field Story seed image is unavailable: ' . $asset['file'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return 0;
		}

		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			error_log( 'Urban Care Field Story image upload failed: ' . $uploads['error'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return 0;
		}
		$filename    = wp_unique_filename( $uploads['path'], basename( $source ) );
		$destination = trailingslashit( $uploads['path'] ) . $filename;
		if ( ! copy( $source, $destination ) ) {
			error_log( 'Urban Care Field Story image could not be copied: ' . $asset['file'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return 0;
		}

		$filetype      = wp_check_filetype( $filename );
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => $asset['title'],
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$destination,
			$story_id,
			true
		);
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $destination );
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $destination ) );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $asset['alt'] );
		update_post_meta( $attachment_id, '_ucp_seed_asset_key', $asset['key'] );
		return (int) $attachment_id;
	}

	private function field_story_definitions() {
		return array(
			array(
				'slug'              => 'research-in-action-across-kitengela',
				'title'             => 'Research in action across Kitengela',
				'excerpt'           => 'Across Kitengela, Urban Care works with residents while surveying community water points and monitoring air quality near schools and waste sites.',
				'content'           => '<p>Urban Care combines environmental science, social science, and public health through fieldwork carried out with local residents and neighborhood associations. Community collaborators help researchers navigate changing neighborhoods, conduct surveys, and connect scientific questions with everyday experience.</p><p>At community boreholes, surveys and water-quality work examine access, use, and possible contamination. In Noonkopir, portable sensors extend this environmental monitoring to the air near a school and a dumpsite, helping the team understand how settlement, roads, waste, and pollution shape exposure.</p><p>These encounters are part of a wider science-society approach: evidence is produced through shared observation, dialogue, and practical work across the places where urban change is unfolding.</p>',
				'lenses'            => array(
					array( 'title' => 'Working with residents', 'description' => 'Local residents and neighborhood associations help shape field surveys, communication, and knowledge exchange.' ),
					array( 'title' => 'Surveying community boreholes', 'description' => 'Field teams connect water-quality sampling with questions about access, use, and everyday health.' ),
					array( 'title' => 'Monitoring air near Noonkopir school', 'description' => 'Portable sensors document environmental conditions where children learn and communities live.' ),
					array( 'title' => 'Monitoring air near the dumpsite', 'description' => 'Measurements near waste sites help trace how pollution sources affect surrounding neighborhoods.' ),
				),
				'creator_credit'    => 'Text source: Bérénice Bon',
				'closing_statement' => 'Shared fieldwork connects environmental change with everyday life.',
				'related_sites'     => array( 'kitengela', 'noonkopir' ),
				'related_team'      => array( 'berenice-bon' ),
				'related_activities' => array( 'engagement-with-residents-associations', 'public-health-surveys', 'pollution-and-environmental-transformations', 'collaboration-with-schools' ),
				'themes'            => array( 'environmental-health' ),
				'methods'           => array( 'participatory', 'household-surveys', 'pollution-monitoring' ),
				'featured'          => true,
				'display_order'     => 0,
				'images'            => array(
					array( 'key' => 'fieldwork-residents', 'file' => 'research-in-action-across-kitengela/1_interacting with residents.jpeg', 'title' => 'Interacting with residents', 'alt' => 'Urban Care researchers meeting residents in Kitengela' ),
					array( 'key' => 'fieldwork-borehole', 'file' => 'research-in-action-across-kitengela/2_surveys on community borehole.jpeg', 'title' => 'Community borehole survey', 'alt' => 'Field researchers conducting a survey at a community borehole' ),
					array( 'key' => 'fieldwork-noonkopir-school', 'file' => 'research-in-action-across-kitengela/3_installing a sensor near a school at noonkopir kitengela.jpeg', 'title' => 'Sensor installation near Noonkopir school', 'alt' => 'Installing an environmental sensor near a school in Noonkopir, Kitengela' ),
					array( 'key' => 'fieldwork-dumpsite', 'file' => 'research-in-action-across-kitengela/4_installing a sensor near dumping site.jpeg', 'title' => 'Monitoring near a dumpsite', 'alt' => 'Field researchers working with a monitoring sensor near a dumpsite' ),
				),
			),
			array(
				'slug'              => 'from-the-plot-to-the-particle',
				'title'             => 'From the plot to the particle',
				'excerpt'           => 'In Kitengela, dust enters homes, coats plants and vehicles, fills school buses, and makes breathing difficult when the air is dry and hot.',
				'content'           => '<p>Urban Care follows dust in motion and at rest. Mobile sensors trace particles and metals in the air, while samples from soil, leaves, rooftops, and vehicles reveal its composition, origin, and persistence. Satellite imagery helps connect these observations across the landscape.</p><p>The research also asks how vegetation captures dust and how people adapt to an omnipresent veil stirred by wind, drought, unpaved roads, and quarry trucks. Photography and sound make these often-overlooked exposures tangible again.</p>',
				'lenses'            => array(
					array( 'title' => 'In the air', 'description' => 'Mobile sensors trace real-time concentrations of particles and metals.' ),
					array( 'title' => 'On surfaces', 'description' => 'Dust collected from soil, leaves, rooftops, and vehicles reveals what settles.' ),
					array( 'title' => 'Through ecology', 'description' => 'Trees and plants show how dust is captured, retained, or resisted.' ),
					array( 'title' => 'In everyday life', 'description' => 'Residents describe how they avoid, adapt to, or live with constant exposure.' ),
				),
				'creator_credit'    => '',
				'closing_statement' => 'It moves. It settles. It shapes life.',
				'related_sites'     => array( 'kitengela' ),
				'related_team'      => array(),
				'related_activities' => array( 'pollution-and-environmental-transformations' ),
				'themes'            => array( 'environmental-health' ),
				'methods'           => array( 'pollution-monitoring', 'remote-sensing' ),
				'featured'          => false,
				'display_order'     => 10,
				'images'            => array(
					array( 'key' => 'dust-everyday-life', 'file' => 'from-the-plot-to-the-particle/dust-everyday-life.webp', 'title' => 'Dust in everyday life', 'alt' => 'People walking through a dusty commercial street in Kitengela' ),
					array( 'key' => 'dust-landscape', 'file' => 'from-the-plot-to-the-particle/dust-landscape.webp', 'title' => 'Dust across the landscape', 'alt' => 'Dust hanging over a green peri-urban landscape in Kitengela' ),
					array( 'key' => 'dust-in-motion', 'file' => 'from-the-plot-to-the-particle/dust-in-motion.webp', 'title' => 'Dust in motion', 'alt' => 'A dusty road reflected in a roadside surface in Kitengela' ),
				),
			),
		);
	}
}