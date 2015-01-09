<?php
/**
 * The breadcrumbs template.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

if ( function_exists( 'flagship_display_breadcrumbs' ) ) {
	// Display breadcrumbs based on user selections in the customizer.
	if ( flagship_display_breadcrumbs() ) {
		// Use Yoast breadcrumbs if they're available.
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			yoast_breadcrumb(
				'<nav class="breadcrumbs" itemprop="breadcrumb">',
				'</nav>'
			);
		}
		// Fall back to Hybrid Core breadcrumbs if Yoast isn't available.
		else {
			breadcrumb_trail(
				array(
					'container'     => 'nav',
					'separator'     => '//',
					'show_browse'   => false,
					'show_on_front' => false,
				)
			);
		}
	}
}
