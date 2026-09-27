<?php
/**
 * 404 template.
 *
 * @package TAD
 */

get_header();
?>

<section class="page-header layout layout--narrow">
	<h1 class="page-title"><?php esc_html_e( 'Page not found', 'tad' ); ?></h1>
	<p><?php esc_html_e( 'The page you requested could not be found. Try a search or return to the homepage.', 'tad' ); ?></p>
	<?php get_search_form(); ?>
</section>

<?php
get_footer();
