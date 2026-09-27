<?php
/**
 * Main template.
 *
 * @package TAD
 */

get_header();
?>

<section class="page-header layout">
	<?php tad_page_title(); ?>
</section>

<div class="content-index layout">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
		endwhile;

		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => __( 'Previous', 'tad' ),
				'next_text'          => __( 'Next', 'tad' ),
				'screen_reader_text' => __( 'Posts navigation', 'tad' ),
			)
		);
	else :
		get_template_part( 'template-parts/content', 'none' );
	endif;
	?>
</div>

<?php
get_footer();
