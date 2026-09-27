<?php
/**
 * ACF and ACFE integration.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_acf_json_save_path( $path ) {
	return TAD_PATH . 'acf-json';
}
add_filter( 'acf/settings/save_json', 'tad_acf_json_save_path' );

function tad_acf_json_load_paths( $paths ) {
	$paths[] = TAD_PATH . 'acf-json';

	return array_values( array_unique( $paths ) );
}
add_filter( 'acf/settings/load_json', 'tad_acf_json_load_paths' );

function tad_acf_options_pages() {
	if ( ! tad_feature_enabled( 'acf_options' ) || ! tad_has_acf() || ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Site Options', 'tad' ),
			'menu_title'  => __( 'Site Options', 'tad' ),
			'menu_slug'   => 'tad-site-options',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
			'position'    => 58,
			'icon_url'    => 'dashicons-admin-generic',
			'autoload'    => true,
			'update_button' => __( 'Save Site Options', 'tad' ),
		)
	);
}
add_action( 'acf/init', 'tad_acf_options_pages' );

function tad_acf_admin_notice() {
	if ( tad_has_acf() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'The tracyappsdesign 2026 theme works best with Advanced Custom Fields Pro active. The theme will keep running, but Site Options and ACF blocks are disabled until ACF is active.', 'tad' )
	);
}
add_action( 'admin_notices', 'tad_acf_admin_notice' );
