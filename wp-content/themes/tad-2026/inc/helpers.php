<?php
/**
 * General theme helpers.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_config( $key = null, $default = null ) {
	static $config = null;

	if ( null === $config ) {
		$config = require TAD_PATH . 'inc/starter-config.php';
	}

	if ( null === $key ) {
		return $config;
	}

	$parts = explode( '.', $key );
	$value = $config;

	foreach ( $parts as $part ) {
		if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
			return $default;
		}
		$value = $value[ $part ];
	}

	return $value;
}

function tad_feature_enabled( $feature ) {
	return (bool) tad_config( 'features.' . $feature, false );
}

function tad_plugin_active( $plugin_file ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return is_plugin_active( $plugin_file );
}

function tad_has_acf() {
	return class_exists( 'ACF' ) || tad_plugin_active( 'advanced-custom-fields-pro/acf.php' );
}

function tad_has_acfe() {
	return function_exists( 'acfe' ) || tad_plugin_active( 'acf-extended-pro/acf-extended.php' ) || tad_plugin_active( 'acf-extended/acf-extended.php' );
}

function tad_asset_version( $relative_path ) {
	$file = TAD_PATH . ltrim( $relative_path, '/' );

	return file_exists( $file ) ? (string) filemtime( $file ) : TAD_VERSION;
}

function tad_get_option( $field, $default = '' ) {
	if ( ! tad_has_acf() || ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field, 'option' );

	return null !== $value && '' !== $value ? $value : $default;
}

function tad_get_theme_mode() {
	$mode = tad_get_option( 'theme_mode_default', 'system' );

	return in_array( $mode, array( 'light', 'dark', 'system' ), true ) ? $mode : 'system';
}
