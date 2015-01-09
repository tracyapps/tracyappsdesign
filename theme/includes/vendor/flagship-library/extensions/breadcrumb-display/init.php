<?php
/**
 * Flagship Breadcrumb Display Init
 *
 * @package     FlagshipLibrary
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship Software, LLC
 * @license     GPL-2.0+
 * @link        http://flagshipwp.com/
 * @since       1.1.0
 */

// Include our required extension files.
require_once( trailingslashit( dirname( __FILE__ ) ) . 'includes/class-breadcrumb-display.php' );

/**
 * Allow themes and plugins to access Flagship_Breadcrumb_Display methods and
 * properties.
 *
 * Because we aren't using a singleton pattern for this class, we need to make
 * sure it's only instantiated once through the helper function. Plugins and
 * themes should use the provided template tags, but if you need to access
 * methods inside our class for some reason, use this function.
 *
 * Example:
 *
 * <?php flagship_breadcrumb_display()->get_breadcrumb_options(); ?>
 *
 * @uses   Flagship_Breadcrumb_Display
 * @return object Site_Logo
 */
function flagship_breadcrumb_display() {
	static $extension;
	if ( null === $extension ) {
		$extension = new Flagship_Breadcrumb_Display();
	}
	return $extension;
}

// Get Flagship breadcrumb display running!
flagship_breadcrumb_display()->run();
