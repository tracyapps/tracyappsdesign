<?php
/**
 * ACF block registration.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_register_acf_blocks() {
	if ( ! tad_feature_enabled( 'acf_blocks' ) || ! tad_has_acf() ) {
		return;
	}

	foreach ( glob( TAD_PATH . 'blocks/*/block.json' ) as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}
add_action( 'init', 'tad_register_acf_blocks' );

function tad_block_categories( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'tad',
				'title' => __( 'Site Sections', 'tad' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'tad_block_categories' );
