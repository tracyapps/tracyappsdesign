<?php
/**
 * Assets.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_enqueue_assets() {
	wp_enqueue_style(
		'tad-main',
		TAD_URI . 'assets/dist/css/main.css',
		array(),
		tad_asset_version( 'assets/dist/css/main.css' )
	);

	wp_enqueue_script(
		'tad-main',
		TAD_URI . 'assets/dist/js/main.js',
		array(),
		tad_asset_version( 'assets/dist/js/main.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tad_enqueue_assets' );

/**
 * Preload the two self-hosted variable fonts (display + body) to avoid a flash of fallback type.
 */
function tad_preload_fonts() {
	foreach ( array( 'recursive-latin-full.woff2', 'inter-latin-wght.woff2' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( TAD_URI . 'assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'tad_preload_fonts', 1 );

function tad_enqueue_editor_assets() {
	wp_enqueue_style(
		'tad-editor',
		TAD_URI . 'assets/dist/css/editor.css',
		array(),
		tad_asset_version( 'assets/dist/css/editor.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'tad_enqueue_editor_assets' );
