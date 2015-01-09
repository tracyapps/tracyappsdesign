<?php
/**
 * The after-header nav menu template.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
?>

<?php if ( has_nav_menu( 'after-header' ) ) : ?>

	<nav <?php hybrid_attr( 'menu', 'after-header' ); ?>>

		<span id="menu-after-header-title" class="screen-reader-text">
			<?php
			// Translators: %s is the nav menu name. This is the nav menu title shown to screen readers.
			printf( _x( '%s', 'nav menu title', 'tracyappsdesign' ), hybrid_get_menu_location_name( 'after-header' ) );
			?>
		</span>

		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'after-header',
				'container'       => '',
				'menu_id'         => 'after-header',
				'menu_class'      => 'nav-menu after-header',
				'fallback_cb'     => '',
				'items_wrap'      => '<div ' . hybrid_get_attr( 'wrap', 'after-header' ) . '><ul id="%s" class="%s">%s</ul></div>',
			)
		);
		?>

	</nav><!-- #menu-after-header -->

	<?php

endif;
