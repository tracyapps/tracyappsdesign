<?php
/**
 * Script and Style Loaders and Related Functions.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

add_action( 'admin_init', 'tracyappsdesign_add_editor_styles' );
/**
 * Replace the default theme stylesheet with a RTL version when a RTL
 * language is being used.
 *
 * @since  1.2.0
 * @access public
 * @return void
 */
function tracyappsdesign_add_editor_styles() {
	// Set up editor styles
	$editor_styles = array(
		'//fonts.googleapis.com/css?family=Playfair+Display:700,900|Hind:400,700',
		'css/genericons.css',
		'css/editor-style.css',
	);

	// Add the editor styles.
	add_editor_style( $editor_styles );
}

add_action( 'wp_enqueue_scripts', 'tracyappsdesign_rtl_add_data' );
/**
 * Replace the default theme stylesheet with a RTL version when a RTL
 * language is being used.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function tracyappsdesign_rtl_add_data() {
	wp_style_add_data( 'style', 'rtl', 'replace' );
	wp_style_add_data( 'style', 'suffix', hybrid_get_min_suffix() );
}

add_action( 'wp_enqueue_scripts', 'tracyappsdesign_enqueue_styles', 4 );
/**
 * Register our core parent theme styles.
 *
 * Normally we would enqueue all styles here, but because we've defined our base
 * styles in the theme setup function as Hybrid Core Styles, we only need to
 * register them. Hybrid Core will ensure they're loaded in the correct order.
 * Any non-global styles can still be enqueued manually in the usual way within
 * this function.
 *
 * @since  1.0.0
 * @access public
 * @see    http://themehybrid.com/docs/hybrid-core-styles
 * @return void
 */
function tracyappsdesign_enqueue_styles() {
	$css_dir = trailingslashit( get_template_directory_uri() ) . 'css/';
	$suffix  = hybrid_get_min_suffix();

	wp_register_style(
		'google-fonts',
		'//fonts.googleapis.com/css?family=Playfair+Display:700,900|Hind:400,700',
		array(),
		null
	);
	wp_register_style(
		'genericons',
		$css_dir . "genericons{$suffix}.css",
		array(),
		'3.2'
	);
}

add_action( 'wp_enqueue_scripts', 'tracyappsdesign_enqueue_scripts' );
/**
 * Enqueue theme scripts.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function tracyappsdesign_enqueue_scripts() {
	$js_dir = trailingslashit( get_template_directory_uri() ) . 'js/';
	$suffix = hybrid_get_min_suffix();

	wp_enqueue_script(
		'tracyappsdesign',
		$js_dir . "theme{$suffix}.js",
		array( 'jquery' ),
		null,
		true
	);
}

/**
 * quick and dirty, adding "data-skrollr-stylesheet" to the <link> tag for the main stylesheet
 *
 * @param $tag
 * @return mixed
 */

function tracyappsdesign_style_loader_tag( $tag ) {
	return preg_replace( "/id='style-css'/", "id='style-css' data-skrollr-stylesheet ", $tag );
}
add_filter( 'style_loader_tag', 'tracyappsdesign_style_loader_tag' );
