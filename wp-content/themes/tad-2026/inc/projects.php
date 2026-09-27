<?php
/**
 * Projects: the "project" post type, its taxonomies, and helpers for the
 * Work Grid block, the archives and the single template.
 *
 * Detail pages + archives are switchable in Site Options › Projects. While that is
 * OFF, projects still exist (and show as cards in the Work Grid block) but have no
 * public URLs of their own: single pages and the category/tag archives return 404.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Site Options › Projects › "Enable detail pages & archives". Off by default. */
function tad_projects_detail_enabled() {
	if ( defined( 'TAD_PROJECT_DETAIL' ) ) {
		return (bool) TAD_PROJECT_DETAIL;
	}

	return (bool) get_option( 'options_projects_detail', 0 );
}

/** 'grid' (uniform masonry) or 'list' (list/detail). ?view=list|grid previews the other one. */
function tad_projects_archive_layout() {
	$layout = get_option( 'options_projects_archive_layout', 'grid' );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : '';

	if ( in_array( $view, array( 'grid', 'list' ), true ) ) {
		$layout = $view;
	}

	return 'list' === $layout ? 'list' : 'grid';
}

function tad_register_projects() {
	$detail = tad_projects_detail_enabled();

	register_taxonomy(
		'project_category',
		'project',
		array(
			'labels'             => array(
				'name'          => __( 'Project Categories', 'tad' ),
				'singular_name' => __( 'Project Category', 'tad' ),
				'menu_name'     => __( 'Categories', 'tad' ),
			),
			'hierarchical'       => true,
			'public'             => true,
			'publicly_queryable' => $detail,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => $detail ? array( 'slug' => 'work/category', 'with_front' => false ) : false,
		)
	);

	register_taxonomy(
		'project_tag',
		'project',
		array(
			'labels'             => array(
				'name'          => __( 'Project Tags', 'tad' ),
				'singular_name' => __( 'Project Tag', 'tad' ),
				'menu_name'     => __( 'Tags', 'tad' ),
			),
			'hierarchical'       => false,
			'public'             => true,
			'publicly_queryable' => $detail,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => $detail ? array( 'slug' => 'work/tag', 'with_front' => false ) : false,
		)
	);

	register_post_type(
		'project',
		array(
			'labels'              => array(
				'name'               => __( 'Projects', 'tad' ),
				'singular_name'      => __( 'Project', 'tad' ),
				'add_new_item'       => __( 'Add New Project', 'tad' ),
				'edit_item'          => __( 'Edit Project', 'tad' ),
				'new_item'           => __( 'New Project', 'tad' ),
				'view_item'          => __( 'View Project', 'tad' ),
				'search_items'       => __( 'Search Projects', 'tad' ),
				'not_found'          => __( 'No projects found.', 'tad' ),
				'all_items'          => __( 'All Projects', 'tad' ),
				'featured_image'     => __( 'Screenshot / artwork', 'tad' ),
				'set_featured_image' => __( 'Set screenshot / artwork', 'tad' ),
			),
			'public'              => true,
			'publicly_queryable'  => $detail,
			'exclude_from_search' => ! $detail,
			'has_archive'         => $detail ? 'work' : false,
			'rewrite'             => $detail ? array( 'slug' => 'work', 'with_front' => false ) : false,
			'menu_icon'           => 'dashicons-portfolio',
			'menu_position'       => 21,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'taxonomies'          => array( 'project_category', 'project_tag' ),
		)
	);
}
add_action( 'init', 'tad_register_projects' );

/** Changing the toggle (or switching themes) changes URLs, so refresh the rewrite rules once. */
function tad_projects_flag_flush() {
	update_option( 'tad_flush_rewrite', 1, false );
}
add_action( 'update_option_options_projects_detail', 'tad_projects_flag_flush' );
add_action( 'add_option_options_projects_detail', 'tad_projects_flag_flush' );
add_action( 'after_switch_theme', 'tad_projects_flag_flush' );

function tad_projects_maybe_flush() {
	if ( get_option( 'tad_flush_rewrite' ) ) {
		flush_rewrite_rules( false );
		delete_option( 'tad_flush_rewrite' );
	}
}
add_action( 'init', 'tad_projects_maybe_flush', 99 );

/** Archives: page size from Site Options. */
function tad_projects_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'project' ) || $query->is_tax( array( 'project_category', 'project_tag' ) ) ) {
		$per_page = (int) get_option( 'options_projects_per_page', 12 );

		$query->set( 'posts_per_page', $per_page > 0 ? min( 60, $per_page ) : 12 );
	}
}
add_action( 'pre_get_posts', 'tad_projects_archive_query' );

/* --------------------------------------------------------------------------
   Data helpers
   -------------------------------------------------------------------------- */

/** Aspect ratios offered for the CSS-art fallback thumbnail. */
function tad_project_art_ratios() {
	return array(
		'16/10' => '16:10 landscape',
		'4/3'   => '4:3',
		'1/1'   => 'Square',
		'3/4'   => '3:4 portrait',
	);
}

/** Read one project field (post meta / ACF). */
function tad_project_field( $post_id, $name, $default = '' ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );

	return ( null === $value || '' === $value || false === $value ) ? $default : $value;
}

/** Everything a card / template needs about one project, normalized. */
function tad_project_data( $post_id ) {
	$post_id = (int) $post_id;

	$layout = tad_project_field( $post_id, 'card_layout', 'top' );
	$size   = tad_project_field( $post_id, 'card_size', 'standard' );
	$ratio  = tad_project_field( $post_id, 'card_art_ratio', '16/10' );
	$url    = esc_url_raw( (string) tad_project_field( $post_id, 'project_url', '' ) );

	$detail     = tad_projects_detail_enabled();
	$categories = get_the_terms( $post_id, 'project_category' );
	$tags       = get_the_terms( $post_id, 'project_tag' );

	return array(
		'id'         => $post_id,
		'title'      => get_the_title( $post_id ),
		'excerpt'    => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 28 ),
		'layout'     => 'side' === $layout ? 'side' : 'top',
		'size'       => in_array( $size, array( 'standard', 'wide', 'feature' ), true ) ? $size : 'standard',
		'art'        => tad_project_field( $post_id, 'card_art', '' ),
		'art_ratio'  => array_key_exists( $ratio, tad_project_art_ratios() ) ? $ratio : '16/10',
		'image_id'   => (int) get_post_thumbnail_id( $post_id ),
		'url'        => $url,
		'url_label'  => (string) tad_project_field( $post_id, 'project_link_label', '' ),
		'year'       => (string) tad_project_field( $post_id, 'project_year', '' ),
		'role'       => (string) tad_project_field( $post_id, 'project_role', '' ),
		'client'     => (string) tad_project_field( $post_id, 'project_client', '' ),
		'permalink'  => $detail ? get_permalink( $post_id ) : '',
		'categories' => is_array( $categories ) ? $categories : array(),
		'tags'       => is_array( $tags ) ? $tags : array(),
	);
}

/** Visible label for the external link ("visit papr.world"). */
function tad_project_link_label( array $p ) {
	if ( $p['url_label'] ) {
		return $p['url_label'];
	}

	$host = wp_parse_url( $p['url'], PHP_URL_HOST );

	/* translators: %s: site host name, e.g. papr.world */
	return sprintf( __( 'visit %s', 'tad' ), $host ? preg_replace( '/^www\./', '', $host ) : __( 'site', 'tad' ) );
}

/** "Opens in a new tab" icon (decorative; the warning is text for screen readers). */
function tad_icon_external() {
	return '<svg class="icon-ext" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>';
}

/** Screen-reader warning that goes with tad_icon_external(). */
function tad_new_tab_notice() {
	return '<span class="screen-reader-text"> ' . esc_html__( '(opens in a new tab)', 'tad' ) . '</span>';
}

/**
 * Projects for a grid.
 *
 * @param array $args order (random|newest|manual), count (0 = all), categories (term ids),
 *                    picked (post ids, overrides the rest), exclude (post ids).
 * @return WP_Query
 */
function tad_projects_query( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'order'      => 'random',
			'count'      => 9,
			'categories' => array(),
			'picked'     => array(),
			'exclude'    => array(),
		)
	);

	$query = array(
		'post_type'           => 'project',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $args['count'] > 0 ? (int) $args['count'] : -1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( ! empty( $args['picked'] ) ) {
		$query['post__in'] = array_map( 'intval', (array) $args['picked'] );
		$query['orderby']  = 'post__in';
	} else {
		if ( 'newest' === $args['order'] ) {
			$query['orderby'] = 'date';
			$query['order']   = 'DESC';
		} elseif ( 'manual' === $args['order'] ) {
			$query['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
		} else {
			$query['orderby'] = 'rand';
		}

		if ( ! empty( $args['categories'] ) ) {
			$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'project_category',
					'field'    => 'term_id',
					'terms'    => array_map( 'intval', (array) $args['categories'] ),
				),
			);
		}
	}

	if ( ! empty( $args['exclude'] ) ) {
		$query['post__not_in'] = array_map( 'intval', (array) $args['exclude'] );
	}

	return new WP_Query( $query );
}

/** Similar projects: shared tags/categories first, topped up with others. Returns post IDs. */
function tad_project_related_ids( $post_id, $limit = 3 ) {
	$limit = max( 0, (int) $limit );

	if ( ! $limit ) {
		return array();
	}

	$term_ids = array();

	foreach ( array( 'project_tag', 'project_category' ) as $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$term_ids = array_merge( $term_ids, wp_list_pluck( $terms, 'term_id' ) );
		}
	}

	$ids = array();

	if ( $term_ids ) {
		$ids = get_posts(
			array(
				'post_type'           => 'project',
				'post_status'         => 'publish',
				'posts_per_page'      => $limit,
				'post__not_in'        => array( $post_id ),
				'fields'              => 'ids',
				'orderby'             => 'rand',
				'ignore_sticky_posts' => true,
				'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'OR',
					array(
						'taxonomy' => 'project_tag',
						'field'    => 'term_id',
						'terms'    => $term_ids,
					),
					array(
						'taxonomy' => 'project_category',
						'field'    => 'term_id',
						'terms'    => $term_ids,
					),
				),
			)
		);
	}

	if ( count( $ids ) < $limit ) {
		$more = get_posts(
			array(
				'post_type'           => 'project',
				'post_status'         => 'publish',
				'posts_per_page'      => $limit - count( $ids ),
				'post__not_in'        => array_merge( array( $post_id ), $ids ),
				'fields'              => 'ids',
				'orderby'             => 'rand',
				'ignore_sticky_posts' => true,
			)
		);
		$ids  = array_merge( $ids, $more );
	}

	return array_map( 'intval', $ids );
}

/** Render one card. $context: 'grid' (masonry, mixed sizes) | 'uniform' | 'list'. */
function tad_project_card( $post_id, $args = array() ) {
	get_template_part(
		'template-parts/project-card',
		null,
		wp_parse_args(
			$args,
			array(
				'post_id' => $post_id,
				'context' => 'grid',
				'heading' => 'h3',
			)
		)
	);
}
