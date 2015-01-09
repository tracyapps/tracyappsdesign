<?php
/**
 * A template part for displaying a comment.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
?>

<li <?php hybrid_attr( 'comment' ); ?>>

	<article>
		<header class="comment-meta">
			<?php echo get_avatar( $comment ); ?>
			<cite <?php hybrid_attr( 'comment-author' ); ?>><?php comment_author_link(); ?></cite><br />
			<time <?php hybrid_attr( 'comment-published' ); ?>><?php printf( __( '%s ago', 'tracyappsdesign' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ); ?></time>
			<a <?php hybrid_attr( 'comment-permalink' ); ?>><?php _e( 'Permalink', 'tracyappsdesign' ); ?></a>
			<?php edit_comment_link(); ?>
		</header><!-- .comment-meta -->

		<div <?php hybrid_attr( 'comment-content' ); ?>>
			<?php comment_text(); ?>
		</div><!-- .comment-content -->

		<?php hybrid_comment_reply_link(); ?>
	</article>
