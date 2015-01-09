<?php
/**
 * Additional helper functions that the library or themes may use.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.1.1
 */

/**
 * A helper function to simplify the process of building complicated styles
 * based on user input within the WordPress customizer.
 *
 * Example:
 *
 * <?php flagship_style_builder()->add( $data ); ?>
 *
 * @since   1.1.1
 * @access  public
 * @uses    Flagship_Style_Builder
 * @see     /classes/class-style-builder.php
 * @return  object Flagship_Style_Builder
 */
function flagship_style_builder() {
	static $builder;
	if ( null === $builder ) {
		$builder = new Flagship_Style_Builder();
	}
	return $builder;
}

/**
 * Display our breadcrumbs based on selections made in the WordPress customizer.
 *
 * @since  1.1.0
 * @access public
 * @return bool true if both our template tag and theme mod return true.
 */
function flagship_display_breadcrumbs() {
	// Return early if our theme doesn't support breadcrumbs.
	if ( ! function_exists( 'flagship_breadcrumb_display' ) ) {
		return false;
	}
	// Grab our available breadcrumb display options.
	$breadcrumb_options = array_keys( flagship_breadcrumb_display()->get_breadcrumb_options() );
	// Set up an array of template tags to map to our breadcrumb display options.
	$template_tags = array(
		is_singular() && ! is_attachment(),
		is_page(),
		is_home() && ! is_front_page(),
		is_archive(),
		is_404(),
		is_attachment(),
	);

	// Use breadcrumb options as keys and template tags as values for our array of mods.
	$breadcrumb_mods = array_combine( $breadcrumb_options, $template_tags );

	// Allow developers to filter the mods that we're going to loop through.
	$breadcrumb_mods = apply_filters( 'flagship_breadcrumb_mods', $breadcrumb_mods, $breadcrumb_options, $template_tags );

	// Loop through our theme mods to see if we have a match.
	foreach ( $breadcrumb_mods as $mod => $tag ) {
		// Return true if we find an enabled theme mod within the correct section.
		if ( absint( get_theme_mod( $mod, 0 ) ) === 1 && $tag === true ) {
			return true;
		}
	}
	return false;
}
