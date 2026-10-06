<?php
/**
 * Template helpers.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	if ( is_front_page() && is_home() ) {
		printf( '<h1 class="site-branding__name"><a href="%s">%s</a></h1>', esc_url( home_url( '/' ) ), esc_html( get_bloginfo( 'name' ) ) );
		return;
	}

	printf( '<p class="site-branding__name"><a href="%s">%s</a></p>', esc_url( home_url( '/' ) ), esc_html( get_bloginfo( 'name' ) ) );
}

function tad_posted_on() {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

function tad_page_title() {
	if ( is_home() && ! is_front_page() ) {
		single_post_title( '<h1 class="page-title">', '</h1>' );
		return;
	}

	if ( is_archive() ) {
		the_archive_title( '<h1 class="page-title">', '</h1>' );
		the_archive_description( '<div class="archive-description">', '</div>' );
		return;
	}

	if ( is_search() ) {
		printf( '<h1 class="page-title">%s</h1>', esc_html( sprintf( __( 'Search results for "%s"', 'tad' ), get_search_query() ) ) );
		return;
	}

	if ( is_singular() ) {
		$title = get_the_title();

		if ( tad_has_acf() && get_field( 'override_page_title' ) && get_field( 'custom_page_title' ) ) {
			$title = get_field( 'custom_page_title' );
		}

		printf( '<h1 class="page-title">%s</h1>', esc_html( $title ) );

		if ( tad_has_acf() && get_field( 'page_tagline' ) ) {
			printf( '<div class="page-tagline">%s</div>', wp_kses_post( get_field( 'page_tagline' ) ) );
		}
	}
}

function tad_the_svg_sprite() {
	$sprite = TAD_PATH . 'assets/dist/svg/icons.svg';

	if ( file_exists( $sprite ) ) {
		echo '<div hidden aria-hidden="true">';
		include $sprite;
		echo '</div>';
	}
}

/**
 * Fallback menu (used until menus are assigned under Appearance › Menus):
 * anchors to the home page sections.
 */
function tad_default_menu( $args = array() ) {
	$home  = home_url( '/' );
	$class = ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'primary-menu';
	$items = array(
		'work'         => __( 'work', 'tad' ),
		'capabilities' => __( 'capabilities', 'tad' ),
		'process'      => __( 'process', 'tad' ),
		'studio'       => __( 'studio', 'tad' ),
	);

	echo '<ul class="' . esc_attr( $class ) . '">';

	foreach ( $items as $anchor => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $home . '#' . $anchor ), esc_html( $label ) );
	}

	echo '</ul>';
}

/**
 * Pause-motion button (WCAG 2.2.2: a mechanism to pause moving content).
 * State is applied as html[data-motion="paused"] by assets/src/js/modules/motion.js.
 */
function tad_motion_toggle() {
	printf(
		'<button class="motion-toggle" type="button" data-motion-toggle aria-pressed="false" data-label-on="%1$s" data-label-off="%2$s"><span class="motion-toggle__icon" aria-hidden="true"></span><span data-motion-label>%1$s</span></button>',
		esc_attr__( 'pause motion', 'tad' ),
		esc_attr__( 'resume motion', 'tad' )
	);
}

/** Validated, optional controls for the standard inside-page template. */
function tad_page_layout_classes() {
	$classes = array( 'entry', 'layout' );
	$width   = tad_field( 'tad_page_width', 'default' );
	$space   = tad_field( 'tad_page_spacing', 'default' );
	if ( in_array( $width, array( 'reading', 'compact' ), true ) ) {
		$classes[] = 'entry--width-' . $width;
	}
	if ( in_array( $space, array( 'tight', 'roomy' ), true ) ) {
		$classes[] = 'entry--space-' . $space;
	}
	return implode( ' ', $classes );
}
