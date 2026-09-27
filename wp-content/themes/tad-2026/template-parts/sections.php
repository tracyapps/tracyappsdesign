<?php
/**
 * Full-width block sections wrapper.
 *
 * @package TAD
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'tad-sections' ); ?>>
	<?php the_content(); ?>
</article>
