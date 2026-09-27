<?php
/**
 * Front page: the block-built, full-width home page.
 * (If the site shows the latest posts on the front page, fall back to index.php.)
 *
 * @package TAD
 */

if ( 'posts' === get_option( 'show_on_front' ) ) {
	require get_template_directory() . '/index.php';
	return;
}

get_header();

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/sections' );
}

get_footer();
