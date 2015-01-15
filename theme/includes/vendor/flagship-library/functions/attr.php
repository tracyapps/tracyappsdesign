<?php
/**
 * HTML attribute functions and filters. The purposes of this is to provide a
 * way for theme/plugin devs to hook into the attributes for specific HTML
 * elements and create new or modify existing attributes. The biggest benefit of
 * using this is to provide richer microdata while being forward compatible with
 * the ever-changing Web.  Currently, the default microdata vocabulary supported
 * is Schema.org.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.0.0
 */

// Attributes for major structural elements.
add_filter( 'hybrid_attr_header',         'flagship_attr_header_class'         );
add_filter( 'hybrid_attr_site-container', 'flagship_attr_site_container'       );
add_filter( 'hybrid_attr_site-inner',     'flagship_attr_site_inner'           );
add_filter( 'hybrid_attr_wrap',           'flagship_attr_wrap',          10, 2 );
add_filter( 'hybrid_attr_content',        'flagship_attr_content_class'        );
add_filter( 'hybrid_attr_footer',         'flagship_attr_footer_class'         );
add_filter( 'hybrid_attr_sidebar',        'flagship_attr_sidebar_class', 10, 2 );
add_filter( 'hybrid_attr_menu',           'flagship_attr_menu_class',    10, 2 );
add_filter( 'hybrid_attr_widget-menu',    'flagship_attr_widget_menu',   10, 2 );

// Header attributes.
add_filter( 'hybrid_attr_branding',         'flagship_attr_branding_class'   );
add_filter( 'hybrid_attr_site-title',       'flagship_attr_site_title_class' );
add_filter( 'hybrid_attr_site-description', 'flagship_attr_site_desc_class'  );

// Post-specific attributes.
add_filter( 'hybrid_attr_entry-summary', 'flagship_attr_entry_summary_class' );

// Other attributes.
add_filter( 'hybrid_attr_author-box', 'flagship_attr_author_box', 10, 2 );

/**
 * Page <header> element attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_header_class( $attr ) {
	$attr['class'] = 'header';
	return $attr;
}

/**
 * Page site container element attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_site_container( $attr ) {
	$attr['id']    = 'site-container';
	$attr['class'] = 'site-container';
	return $attr;
}

/**
 * Page site inner element attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_site_inner( $attr ) {
	$attr['id']    = 'site-inner';
	$attr['class'] = 'site-inner';
	return $attr;
}

/**
 * Page wrap element attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_wrap( $attr, $context ) {
	if ( empty( $context ) ) {
		return $attr;
	}
	$attr['class'] = "wrap {$context}-wrap";
	return $attr;
}

/**
 * Main content container of the page attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_content_class( $attr ) {
	$attr['class'] = 'content';
	return $attr;
}

/**
 * Page <footer> element attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_footer_class( $attr ) {
	$attr['class'] = 'footer';
	return $attr;
}

/**
 * Sidebar attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @param  string $context
 * @return array
 */
function flagship_attr_sidebar_class( $attr, $context ) {
	$class = 'sidebar';
	if ( ! empty( $context ) ) {
		$class .= " sidebar-{$context}";
	}
	$attr['class'] = $class;
	return $attr;
}

/**
 * Nav menu attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @param  string $context
 * @return array
 */
function flagship_attr_menu_class( $attr, $context ) {
	$class = 'menu';
	if ( ! empty( $context ) ) {
		$class .= " menu-{$context}";
	}
	$attr['class'] = $class;
	return $attr;
}

/**
 * Widget nav menu attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @param  string $context
 * @return array
 */
function flagship_attr_widget_menu( $attr, $context ) {
	$class = 'menu';

	if ( ! empty( $context ) ) {
		$attr['id'] = "menu-{$context}";
		$class    .= " menu-{$context}";
	}

	$attr['class'] = $class;
	$attr['role']  = 'navigation';

	if ( ! empty( $context ) ) {
		// Translators: The %s is the menu name. This is used for the 'aria-label' attribute.
		$attr['aria-label'] = esc_attr( sprintf( _x( '%s Menu', 'nav menu aria label', 'flagship-library' ), ucwords( $context ) ) );
	}

	$attr['itemscope']  = 'itemscope';
	$attr['itemtype']   = 'http://schema.org/SiteNavigationElement';

	return $attr;
}

/**
 * Branding (usually a wrapper for title and tagline) attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_branding_class( $attr ) {
	$attr['class'] = 'branding';
	return $attr;
}

/**
 * Site title attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_site_title_class( $attr ) {
	$attr['class'] = 'site-title';
	return $attr;
}

/**
 * Site description attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_site_desc_class( $attr ) {
	$attr['class'] = 'site-description';
	return $attr;
}

/**
 * Post summary/excerpt attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @return array
 */
function flagship_attr_entry_summary_class( $attr ) {
	$attr['class'] = 'entry-content summary';
	return $attr;
}

/**
 * Author box attributes.
 *
 * @since  1.0.0
 * @access public
 * @param  array $attr
 * @param  string $context
 * @return array
 */
function flagship_attr_author_box( $attr, $context ) {
	$class      = 'author-box';
	$attr['id'] = 'author-box';

	if ( ! empty( $context ) ) {
		$attr['id'] = "author-box-{$context}";
		$class    .= " author-box-{$context}";
	}

	$attr['class']     = $class;
	$attr['itemscope'] = 'itemscope';
	$attr['itemtype']  = 'http://schema.org/Person';
	$attr['itemprop']  = 'author';

	return $attr;
}
