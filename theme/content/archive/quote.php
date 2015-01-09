<?php
/**
 * A template part for displaying a quote entry within an archive.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
?>

<?php tha_entry_before(); ?>

<article <?php hybrid_attr( 'post' ); ?>>

	<?php tha_entry_top(); ?>

	<div <?php hybrid_attr( 'entry-content' ); ?>>
		<?php the_content(); ?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<p class="entry-meta">
			<?php hybrid_post_format_link(); ?>
			<?php flagship_entry_published(); ?>
			<a class="entry-permalink" href="<?php the_permalink(); ?>" rel="bookmark" itemprop="url"><?php _e( 'Permalink', 'tracyappsdesign' ); ?></a>
			<?php flagship_entry_comments_link(); ?>
			<?php edit_post_link(); ?>
		</p><!-- .entry-meta -->
	</footer><!-- .entry-footer -->

	<?php tha_entry_bottom(); ?>

</article><!-- .entry -->

<?php
tha_entry_after();
