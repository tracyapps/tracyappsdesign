<?php
/**
 * Flagship Footer Widgets Init
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.1.0
 */

// Include our required extension files.
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/class-footer-widgets.php' );

/**
 * Allow themes and plugins to access Flagship_Footer_Widgets methods and
 * properties.
 *
 * Because we aren't using a singleton pattern for this class, we need to make
 * sure it's only instantiated once through the helper function. Plugins and
 * themes shouldn't need to access anything in this class, but if you need to
 * for some reason, use this function.
 *
 * Example:
 *
 * <?php flagship_footer_widgets()->register_footer_sidebars(); ?>
 *
 * @uses   Flagship_Footer_Widgets
 * @return object Flagship_Footer_Widgets
 */
function flagship_footer_widgets() {
	static $extension;
	if ( null === $extension ) {
		$extension = new Flagship_Footer_Widgets();
	}
	return $extension;
}

// Get Flagship footer widgets running!
flagship_footer_widgets()->run();

/**
 * Legacy function no longer in use. This will be removed on the next major
 * release. Originally used to register footer widget areas.
 *
 * @since  1.1.0
 * @deprecated
 * @return null
 */
function flagship_register_footer_widget_areas() {}
