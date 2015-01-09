<?php
/**
 * A template part to display single entry navigation and pagination for archives.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
?>

<?php if ( is_singular( 'post' ) ) : ?>

	<nav class="nav-single">
		<?php previous_post_link( '<span class="nav-previous">' . __( '%link', 'tracyappsdesign' ) . '</span>', '&larr; Previous Post' ); ?>
		<?php next_post_link(     '<span class="nav-next">' . __( '%link', 'tracyappsdesign' ) . '</span>', 'Next Post &rarr;' ); ?>
	</nav><!-- .nav-singl -->

	<?php

endif;

if ( is_home() || is_archive() || is_search() ) :

	loop_pagination(
		array(
			'prev_text' => '<span class="screen-reader-text">' . __( 'Previous Page', 'tracyappsdesign' ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . __( 'Next Page', 'tracyappsdesign' ) . '</span>',
		)
	);

endif;
