<?php
/**
 * Curate the block editor for client editing.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_disable_remote_patterns() {
	return false;
}

if ( ! tad_feature_enabled( 'patterns' ) ) {
	add_filter( 'should_load_remote_block_patterns', 'tad_disable_remote_patterns' );
}

function tad_allowed_block_types( $allowed_blocks, $editor_context ) {
	if ( ! tad_feature_enabled( 'curated_editor' ) ) {
		return $allowed_blocks;
	}

	$blocks = array(
		'core/group',
		'core/columns',
		'core/column',
		'core/media-text',
		'core/cover',
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/image',
		'core/gallery',
		'core/buttons',
		'core/button',
		'core/separator',
		'core/spacer',
		'core/embed',
		'core/shortcode',
		'core/table',
		'core/html',
	);

	if ( tad_feature_enabled( 'acf_blocks' ) && tad_has_acf() ) {
		$blocks = array_merge(
			$blocks,
			array(
				'acf/tad-hero',
				'acf/tad-ticker',
				'acf/tad-accent-section',
				'acf/tad-work-grid',
				'acf/tad-capabilities',
				'acf/tad-manifesto',
				'acf/tad-process',
				'acf/tad-studio',
				'acf/tad-contact',
			)
		);
	}

	// Curate core blocks, while allowing registered plugin blocks (including ACF).
	foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
		if ( 0 !== strpos( $name, 'core/' ) ) {
			$blocks[] = $name;
		}
	}

	$blocks = array_values( array_unique( $blocks ) );

	// Preserve restrictions supplied by another plugin or editor context.
	if ( false === $allowed_blocks ) {
		return false;
	}
	if ( is_array( $allowed_blocks ) ) {
		return array_values( array_intersect( $allowed_blocks, $blocks ) );
	}

	return $blocks;
}
add_filter( 'allowed_block_types_all', 'tad_allowed_block_types', 10, 2 );

function tad_remove_editor_noise() {
	if ( ! tad_feature_enabled( 'curated_editor' ) ) {
		return;
	}

	remove_post_type_support( 'page', 'comments' );
	remove_post_type_support( 'post', 'comments' );
}
add_action( 'init', 'tad_remove_editor_noise', 20 );

function tad_unregister_block_styles() {
	if ( ! tad_feature_enabled( 'curated_editor' ) ) {
		return;
	}

	wp_enqueue_script(
		'tad-editor-curation',
		TAD_URI . 'assets/dist/js/editor.js',
		array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
		tad_asset_version( 'assets/dist/js/editor.js' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'tad_unregister_block_styles' );

/** Useful content treatments, shared by editor and frontend CSS. */
function tad_register_group_styles() {
	register_block_style( 'core/group', array(
		'name'  => 'tad-panel',
		'label' => __( 'Inset panel', 'tad' ),
	) );
	register_block_style( 'core/group', array(
		'name'  => 'tad-reading',
		'label' => __( 'Reading width', 'tad' ),
	) );
}
add_action( 'init', 'tad_register_group_styles' );
