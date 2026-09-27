<?php
/**
 * Post summary.
 *
 * @package TAD
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="entry-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="entry-card__body flow">
		<header>
			<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry-meta"><?php tad_posted_on(); ?></div>
		</header>
		<?php the_excerpt(); ?>
	</div>
</article>
