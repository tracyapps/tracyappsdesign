<?php
/**
 * Optional custom post types.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_register_optional_post_types() {
	$post_types = (array) tad_config( 'post_types', array() );

	if ( ! empty( $post_types['people'] ) ) {
		register_post_type(
			'person',
			array(
				'labels'       => array(
					'name'          => __( 'People', 'tad' ),
					'singular_name' => __( 'Person', 'tad' ),
				),
				'public'       => true,
				'menu_icon'    => 'dashicons-groups',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'people' ),
			)
		);
	}

	if ( ! empty( $post_types['work'] ) ) {
		register_post_type(
			'work',
			array(
				'labels'       => array(
					'name'          => __( 'Work', 'tad' ),
					'singular_name' => __( 'Work Item', 'tad' ),
				),
				'public'       => true,
				'menu_icon'    => 'dashicons-portfolio',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'work' ),
			)
		);
	}

	if ( ! empty( $post_types['events'] ) ) {
		register_post_type(
			'event',
			array(
				'labels'       => array(
					'name'          => __( 'Events', 'tad' ),
					'singular_name' => __( 'Event', 'tad' ),
				),
				'public'       => true,
				'menu_icon'    => 'dashicons-calendar-alt',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'events' ),
			)
		);
	}
}
add_action( 'init', 'tad_register_optional_post_types' );
