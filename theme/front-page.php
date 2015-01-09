<?php
/**
 * The static front page template.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
?>

<?php get_header(); ?>

<div <?php hybrid_attr( 'site-inner' ); ?>>

	<?php tha_content_before(); ?>

	<main <?php hybrid_attr( 'content' ); ?>>

		<?php tha_content_top(); ?>

		<?php hybrid_get_menu( 'breadcrumbs' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<section id="home" class="full-height">
					<?php hybrid_get_content_template(); ?>
				</section>

				<?php
				$homepage_sections_array = tracyappsdesign_get_meta( 'homepage_onepage_content' );
				foreach ( $homepage_sections_array as $homepage_section ) :
					echo tracyappsdesign_loop_home_section( $homepage_section );
				endforeach;
				?>

			<?php endwhile; ?>

			<?php get_template_part( 'misc-templates/loop-nav' ); ?>

		<?php else : ?>

			<?php get_template_part( 'content/error' ); ?>

		<?php endif; ?>

		<?php tha_content_bottom(); ?>

	</main><!-- #content -->

	<?php tha_content_after(); ?>


</div><!-- #site-inner -->

<?php
get_footer();
