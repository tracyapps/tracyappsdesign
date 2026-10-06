<?php
/**
 * Page template.
 *
 * @package TAD
 */

get_header();
?>

<?php
while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( tad_page_layout_classes() ); ?>>
		<header class="page-header">
			<?php tad_page_title(); ?>
		</header>

		<div class="entry-content flow">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
