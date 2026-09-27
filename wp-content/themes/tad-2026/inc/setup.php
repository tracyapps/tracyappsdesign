<?php
/**
 * Theme setup.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_setup() {
	load_theme_textdomain( 'tad', TAD_PATH . 'languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style', 'search-form', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/dist/css/editor.css' );

	if ( tad_feature_enabled( 'menus' ) ) {
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'tad' ),
				'footer'  => __( 'Footer Menu', 'tad' ),
			)
		);
	}

	if ( ! tad_feature_enabled( 'patterns' ) ) {
		remove_theme_support( 'core-block-patterns' );
	}
}
add_action( 'after_setup_theme', 'tad_setup' );

function tad_widgets_init() {
	if ( ! tad_feature_enabled( 'widgets' ) ) {
		return;
	}

	register_sidebar(
		array(
			'name'          => __( 'Footer', 'tad' ),
			'id'            => 'footer',
			'description'   => __( 'Footer widget area.', 'tad' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'tad_widgets_init' );
