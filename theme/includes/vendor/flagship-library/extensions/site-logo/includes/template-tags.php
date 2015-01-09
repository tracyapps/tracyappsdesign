<?php
/**
 * Template tags for displaying a logo.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.1.0
 */

/**
 * Retrieve the site logo URL or ID (URL by default). Pass in the string
 * 'id' for ID.
 *
 * @since  1.1.0
 * @uses   Flagship_Site_Logo::get_flagship_logo
 * @param  string $format the format to return
 * @return mixed The URL or ID of our site logo, false if not set
 */
function flagship_get_logo( $format = 'url' ) {
	if ( ! class_exists( 'Flagship_Site_Logo', false ) ) {
		if ( function_exists( 'jetpack_the_site_logo' ) ) {
			return jetpack_get_site_logo( $format );
		}
		if ( function_exists( 'the_site_logo' ) ) {
			return get_site_logo( $format );
		}
		return null;
	}
	return flagship_site_logo()->get_flagship_logo( $format );
}

/**
 * Determine if a site logo is assigned or not.
 *
 * @since  1.1.0
 * @uses   Flagship_Site_Logo::has_site_logo
 * @return boolean True if there is an active logo, false otherwise
 */
function flagship_has_logo() {
	if ( ! class_exists( 'Flagship_Site_Logo', false ) ) {
		if ( function_exists( 'jetpack_the_site_logo' ) ) {
			return jetpack_has_site_logo();
		}
		if ( function_exists( 'the_site_logo' ) ) {
			return has_site_logo();
		}
		return null;
	}
	return flagship_site_logo()->has_site_logo();
}

/**
 * Output an <img> tag of the site logo, at the size specified
 * in the theme's add_theme_support() declaration.
 *
 * @since  1.1.0
 * @uses   Flagship_Site_Logo::the_site_logo
 * @return void
 */
function flagship_the_logo() {
	if ( ! class_exists( 'Flagship_Site_Logo', false ) ) {
		if ( function_exists( 'jetpack_the_site_logo' ) ) {
			jetpack_the_site_logo();
			return;
		}
		if ( function_exists( 'the_site_logo' ) ) {
			the_site_logo();
			return;
		}
		return;
	}
	flagship_site_logo()->the_site_logo();
}
