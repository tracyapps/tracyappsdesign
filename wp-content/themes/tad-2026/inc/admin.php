<?php
/**
 * Admin cleanup.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_admin_cleanup() {
	if ( ! tad_feature_enabled( 'comments' ) ) {
		remove_menu_page( 'edit-comments.php' );
	}

	if ( ! tad_feature_enabled( 'widgets' ) ) {
		remove_submenu_page( 'themes.php', 'widgets.php' );
	}
}
add_action( 'admin_menu', 'tad_admin_cleanup', 999 );

function tad_disable_comments_admin_bar( $wp_admin_bar ) {
	if ( ! tad_feature_enabled( 'comments' ) ) {
		$wp_admin_bar->remove_node( 'comments' );
	}
}
add_action( 'admin_bar_menu', 'tad_disable_comments_admin_bar', 999 );

function tad_disable_emoji() {
	if ( tad_feature_enabled( 'emoji' ) ) {
		return;
	}

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'tad_disable_emoji' );
