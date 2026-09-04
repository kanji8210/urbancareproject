<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UrbanCareProject_Seeder {
	const PROJECT_ID_OPTION = 'ucp_canonical_project_id';
	const SEED_VERSION_OPTION = 'ucp_content_seed_version';
	const SEED_VERSION = '1';

	public function seed() {
		$this->seed_terms();
		$this->seed_project();
		update_option( self::SEED_VERSION_OPTION, self::SEED_VERSION, false );
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
}