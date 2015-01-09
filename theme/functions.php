<?php
/**
 * Theme Setup Functions and Definitions.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

// Include Hybrid Core.
require_once( trailingslashit( get_template_directory() ) . 'hybrid-core/hybrid.php' );
new Hybrid();

add_action( 'after_setup_theme', 'tracyappsdesign_setup', 10 );
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * @since   1.0.0
 * @return  void
 */
function tracyappsdesign_setup() {
	// http://themehybrid.com/docs/theme-layouts
	add_theme_support(
		'theme-layouts',
		array(
			'1c'        => __( '1 Column Wide',                'tracyappsdesign' ),
			'1c-narrow' => __( '1 Column Narrow',              'tracyappsdesign' ),
			'2c-l'      => __( '2 Columns: Content / Sidebar', 'tracyappsdesign' ),
			'2c-r'      => __( '2 Columns: Sidebar / Content', 'tracyappsdesign' )
		),
		array( 'default' => is_rtl() ? '2c-r' :'2c-l' )
	);

	// http://themehybrid.com/docs/hybrid_set_content_width
	hybrid_set_content_width( 1140 );

	// http://codex.wordpress.org/Automatic_Feed_Links
	add_theme_support( 'automatic-feed-links' );

	// http://themehybrid.com/docs/hybrid-core-styles
	add_theme_support( 'hybrid-core-styles', array( 'style', 'google-fonts', 'genericons', ) );

	// Add navigation menus.
	register_nav_menu( 'after-header', _x( 'After Header Menu', 'nav menu location', 'tracyappsdesign' ) );

	$formats = array(
		'aside',
		'gallery',
		'link',
		'image',
		'quote',
		'status',
		'video',
		'audio',
		'chat',
	);

	// http://codex.wordpress.org/Post_Formats
	add_theme_support( 'post-formats', $formats );

	// http://codex.wordpress.org/Post_Thumbnails
	add_theme_support( 'post-thumbnails' );

	// https://github.com/justintadlock/breadcrumb-trail
	add_theme_support( 'breadcrumb-trail' );

	// https://github.com/justintadlock/get-the-image
	add_theme_support( 'get-the-image' );

	// https://github.com/justintadlock/cleaner-gallery
	add_theme_support( 'cleaner-gallery' );

	// https://github.com/justintadlock/hybrid-core/blob/master/extensions/cleaner-caption.php
	add_theme_support( 'cleaner-caption' );

	// http://themehybrid.com/docs/loop-pagination
	add_theme_support( 'loop-pagination' );

	// http://themehybrid.com/docs/template-hierarchy
	add_theme_support( 'hybrid-core-template-hierarchy' );

	// Add support for flagship footer widgets.
	add_theme_support( 'flagship-footer-widgets', 3 );
}

add_action( 'after_setup_theme', 'tracyappsdesign_includes', 10 );
/**
 * Load all required theme files.
 *
 * @since   1.0.0
 * @return  void
 */
function tracyappsdesign_includes() {
	// Set the includes directories.
	$includes_dir = trailingslashit( get_template_directory() ) . 'includes/';

	// Load the main file in the Flagship library directory.
	require_once $includes_dir . 'vendor/flagship-library/flagship-library.php';

	// Load all PHP files in the vendor directory.
	require_once $includes_dir . 'vendor/tha-theme-hooks.php';

	// Load all PHP files in the includes directory.
	require_once $includes_dir . 'compatibility.php';
	require_once $includes_dir . 'general.php';
	require_once $includes_dir . 'scripts.php';
	require_once $includes_dir . 'widgetize.php';
}

// Add a hook for child themes to execute code.
do_action( 'flagship_after_setup_parent' );
