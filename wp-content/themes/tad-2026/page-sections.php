<?php
/**
 * Template Name: Sections (full-width blocks)
 * Template Post Type: page
 *
 * For any page built entirely from the section blocks (Hero, Work Grid, Accent Section, ...).
 *
 * @package TAD
 */

get_header();

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/sections' );
}

get_footer();
