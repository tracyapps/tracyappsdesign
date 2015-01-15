<?php
/**
 * General template helper functions.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.0.0
 */

add_action( 'wp_head', 'flagship_load_favicon', 5 );
/**
 * Echos a favicon link if one is found and falls back to the default Flagship
 * theme favicon when no custom one has been set.
 *
 * URL to favicon is filtered via `flagship_favicon_url` before being echoed.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function flagship_load_favicon() {
	$favicon = '';
	$path    = 'images/favicon.ico';

	// Use the child theme favicon if it exists.
	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $path ) ) {
		$favicon = trailingslashit( get_stylesheet_directory_uri() ) . $path;
	}
	// Fall back to the parent favicon if it exists.
	if ( file_exists( trailingslashit( get_template_directory() ) . $path ) ) {
		$favicon = trailingslashit( get_template_directory_uri() ) . $path;
	}

	// Allow developers to set a custom favicon file.
	$favicon = apply_filters( 'flagship_favicon_url', $favicon );

	// Bail if we don't have a favicon to display.
	if ( empty( $favicon ) ) {
		return;
	}

	echo '<link rel="Shortcut Icon" href="' . esc_url( $favicon ) . '" type="image/x-icon" />' . "\n";
}

/**
 * Sets a common class, `.nav-menu`, for the custom menu widget if used in the
 * header right sidebar.
 *
 * @since  1.0.0
 * @access public
 * @param  array $args Header menu args.
 * @return array $args Modified header menu args.
 */
function flagship_header_menu_args( $args ) {
	$args['menu_class'] .= ' nav-menu';
	return $args;
}

/**
 * Wrap the header navigation menu in its own nav tags with markup API.
 *
 * @since  1.0.0
 * @access public
 * @param  $menu Menu output.
 * @return string $menu Modified menu output.
 */
function flagship_header_menu_wrap( $menu ) {
	return sprintf( '<nav %s>', hybrid_get_attr( 'widget-menu', 'header' ) ) . $menu . '</nav>';
}

add_filter( 'get_search_form', 'flagship_get_search_form' );
/**
 * Customize the search form to improve accessibility.
 *
 * @since  1.0.0
 * @access public
 * @return string Search form markup.
 */
function flagship_get_search_form() {
	$search = new Flagship_Search_Form;
	return $search->get_form();
}

/**
 * Returns a formatted theme credit link.
 *
 * @since  1.1.0
 * @access public
 * @return string
 */
function flagship_get_credit_link() {
	$theme = wp_get_theme( get_template() );
	$uri   = $theme->get( 'AuthorURI' );
	$name  = $theme->display( 'Author', false, true );

	// Translators: Theme name.
	$title = sprintf( __( 'Purpose-Built WordPress Theme by %s', 'flagship-library' ), $name );

	return sprintf( '<a class="author-link" href="%s" title="%s">%s</a>', esc_url( $uri ), esc_attr( $title ), $name );
}
