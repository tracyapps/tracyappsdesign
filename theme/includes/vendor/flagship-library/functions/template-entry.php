<?php
/**
 * Helper functions for entry templates.
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.0.0
 */

/**
 * Outputs an entry's author.
 *
 * @since  1.1.0
 * @access public
 * @param  array $args
 * @return void
 */
function flagship_entry_author( $args = array() ) {
	echo flagship_get_entry_author( $args );
}

/**
 * This is simply a wrapper function for hybrid_get_post_author which adds a few
 * filters to make the function a bit more flexible. This will allow us to avoid
 * passing args into the function by default in our templates. Instead, we can
 * filter the defaults globally which gives us a cleaner template file.
 *
 * @since  1.1.0
 * @access public
 * @param  array   $args
 * @return string
 */
function flagship_get_entry_author( $args = array() ) {

	$defaults = apply_filters( 'flagship_entry_author_defaults',
		array(
			'text'   => '%s',
			'before' => '',
			'after'  => '',
			'wrap'   => '<span %s>%s</span>',
		)
	);

	$args = wp_parse_args( $args, $defaults );

	return apply_filters( 'flagship_entry_author', hybrid_get_post_author( $args ), $args );
}

/**
 * Outputs a post's pulbished date.
 *
 * @since  1.1.0
 * @access public
 * @param  array   $args
 * @return void
 */
function flagship_entry_published( $args = array() ) {
	echo flagship_get_entry_published( $args );
}

/**
 * Function for getting the current post's author in The Loop and linking to t
 * he author archive page. This function was created because core WordPress does
 * not have template tags with proper translation and RTL support for this.
 * An equivalent getter function for `the_author_posts_link()` would instantly
 * solve this issue.
 *
 * @since  1.1.0
 * @access public
 * @param  array   $args
 * @return string
 */
function flagship_get_entry_published( $args = array() ) {

	$output = '';

	$defaults = apply_filters( 'flagship_entry_published_defaults',
		array(
			'before' => '',
			'after'  => '',
			'wrap'   => '<time %s>%s</time>',
		)
	);

	$args = wp_parse_args( $args, $defaults );

	$output .= $args['before'];
	$output .= sprintf( $args['wrap'], hybrid_get_attr( 'entry-published' ), get_the_date() );
	$output .= $args['after'];

	return apply_filters( 'flagship_entry_published', $output, $args );
}

/**
 * Displays a formatted link to the current entry comments.
 *
 * @since  1.1.0
 * @access public
 * @param  array   $args
 * @return void
 */
function flagship_entry_comments_link( $args = array() ) {
	echo flagship_get_entry_comments_link( $args );
}

/**
 * Produces a formatted link to the current entry comments.
 *
 * Supported arguments are:
 * - after (output after link, default is empty string),
 * - before (output before link, default is empty string),
 * - hide_if_off (hide link if comments are off, default is 'enabled' (true)),
 * - more (text when there is more than 1 comment, use % character as placeholder
 *   for actual number, default is '% Comments')
 * - one (text when there is exactly one comment, default is '1 Comment'),
 * - zero (text when there are no comments, default is 'Leave a Comment').
 *
 * Output passes through 'flagship_get_entry_comments_link' filter before returning.
 *
 * @since  1.1.0
 * @param  array $args Empty array if no arguments.
 * @return string output
 */
function flagship_get_entry_comments_link( $args = array() ) {

	$defaults = apply_filters( 'flagship_entry_comments_link_defaults',
		array(
			'after'       => '',
			'before'      => '',
			'hide_if_off' => 'enabled',
			'more'        => __( '% Comments', 'tracyappsdesign' ),
			'one'         => __( '1 Comment', 'tracyappsdesign' ),
			'zero'        => __( 'Leave a Comment', 'tracyappsdesign' ),
		)
	);
	$args = wp_parse_args( $args, $defaults );

	if ( ! comments_open() && 'enabled' === $args['hide_if_off'] ) {
		return;
	}

	// I really would rather not do this, but WordPress is forcing me to.
	ob_start();
	comments_number( $args['zero'], $args['one'], $args['more'] );
	$comments = ob_get_clean();

	$comments = sprintf( '<a rel="nofollow" href="%s">%s</a>', get_comments_link(), $comments );

	$output = '<span class="entry-comments-link">' . $args['before'] . $comments . $args['after'] . '</span>';

	return apply_filters( 'flagship_entry_comments_link', $output, $args );

}
