<?php
/**
 * A template part for displaying a video entry within an archive.
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

	<?php
	// Display a video if we have one.
	echo $video = hybrid_media_grabber(
		array(
			'type'        => 'video',
			'split_media' => true,
			'before'      => '<div class="featured-media video">',
			'after'       => '</div>',
		)
	);
	?>

	<header class="entry-header">

		<?php the_title( '<h2 ' . hybrid_get_attr( 'entry-title' ) . '><a href="' . get_permalink() . '" rel="bookmark" itemprop="url">', '</a></h2>' ); ?>

		<p class="entry-meta">
			<?php hybrid_post_format_link(); ?>
			<?php flagship_entry_author(); ?>
			<?php flagship_entry_published(); ?>
			<?php flagship_entry_comments_link(); ?>
			<?php edit_post_link(); ?>
		</p><!-- .entry-meta -->

	</header><!-- .entry-header -->

	<?php if ( has_excerpt() ) : // If the post has an excerpt. ?>

		<div <?php hybrid_attr( 'entry-summary' ); ?>>
			<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->

	<?php elseif ( empty( $video ) ) : // Else, if the post does not have a video. ?>

		<div <?php hybrid_attr( 'entry-content' ); ?>>
			<?php the_content(); ?>
		</div><!-- .entry-content -->

	<?php endif; // End excerpt/video checks. ?>

	<?php tha_entry_bottom(); ?>

</article><!-- .entry -->

<?php
tha_entry_after();
