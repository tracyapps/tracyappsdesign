<?php
/**
 * tracyappsdesign 2026 theme bootstrap.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAD_VERSION', '2.0.0' );
define( 'TAD_PATH', trailingslashit( get_template_directory() ) );
define( 'TAD_URI', trailingslashit( get_template_directory_uri() ) );

$tad_includes = array(
	'inc/starter-config.php',
	'inc/helpers.php',
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/editor.php',
	'inc/admin.php',
	'inc/acf.php',
	'inc/site-options.php',
	'inc/tad-helpers.php',
	'inc/accent.php',
	'inc/contact.php',
	'inc/seed.php',
	'inc/blocks.php',
	'inc/post-types.php',
	'inc/projects.php',
	'inc/template-tags.php',
);

foreach ( $tad_includes as $tad_include ) {
	require_once TAD_PATH . $tad_include;
}
