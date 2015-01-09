<?php
/**
 * The template for displaying comments.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

// If a post password is required or no comments are given and comments/pings are closed, return.
if ( post_password_required() || ( ! have_comments() && ! comments_open() && ! pings_open() ) ) {
	return;
}
?>

<?php tha_comments_before(); ?>

<section id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>

		<h3 class="comments-number" id="comments-number"><?php comments_number(); ?></h3>

		<ol class="comment-list">
			<?php wp_list_comments(
				array(
					'style'        => 'ol',
					'callback'     => 'hybrid_comments_callback',
					'end-callback' => 'hybrid_comments_end_callback',
				)
			); ?>
		</ol><!-- .comment-list -->

		<?php locate_template( array( 'misc-templates/comments-nav.php' ), true ); ?>

	<?php endif; // End check for comments. ?>

	<?php locate_template( array( 'misc-templates/comments-error.php' ), true ); ?>

	<?php comment_form(); ?>

</section><!-- #comments -->

<?php
tha_comments_after();
