<?php
/**
 * Site Options helpers.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_get_copyright_text() {
	$text = tad_get_option( 'copyright_text', 'Copyright [year] ' . get_bloginfo( 'name' ) );
	$text = str_replace( '[year]', gmdate( 'Y' ), $text );

	return $text;
}

function tad_copyright_text() {
	echo wp_kses_post( wptexturize( tad_get_copyright_text() ) );
}

function tad_get_contact_info() {
	return tad_get_option( 'contact_info', '' );
}

function tad_contact_info() {
	echo wp_kses_post( tad_get_contact_info() );
}

function tad_get_social_links() {
	if ( ! tad_has_acf() || ! have_rows( 'social_links', 'option' ) ) {
		return array();
	}

	$links = array();

	while ( have_rows( 'social_links', 'option' ) ) {
		the_row();

		$url = get_sub_field( 'url' );

		if ( empty( $url ) ) {
			continue;
		}

		$links[] = array(
			'service' => sanitize_title( get_sub_field( 'service' ) ),
			'label'   => get_sub_field( 'link_text' ) ?: get_sub_field( 'service' ),
			'url'     => $url,
		);
	}

	return $links;
}

function tad_social_links( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'       => 'social-links',
			'link_target' => '',
		)
	);

	$links = tad_get_social_links();

	if ( empty( $links ) ) {
		return;
	}

	echo '<ul class="' . esc_attr( $args['class'] ) . '">';

	foreach ( $links as $link ) {
		$target = $args['link_target'] ? ' target="' . esc_attr( $args['link_target'] ) . '" rel="noopener noreferrer"' : '';

		printf(
			'<li class="social-links__item social-links__item--%1$s"><a class="social-links__link" href="%2$s"%3$s><span class="screen-reader-text">%4$s</span><svg aria-hidden="true" focusable="false" class="icon"><use href="#icon-%1$s"></use></svg></a></li>',
			esc_attr( $link['service'] ),
			esc_url( $link['url'] ),
			$target, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $link['label'] )
		);
	}

	echo '</ul>';
}

function tad_excerpt_length( $length ) {
	$unit = tad_get_option( 'excerpt_length_unit', 'words' );

	if ( 'words' === $unit ) {
		return (int) tad_get_option( 'excerpt_length', $length );
	}

	return $length;
}
add_filter( 'excerpt_length', 'tad_excerpt_length' );

function tad_excerpt_more( $more ) {
	$text = tad_get_option( 'excerpt_link_text', __( 'Read more', 'tad' ) );

	return ' <a class="more-link" href="' . esc_url( get_permalink() ) . '">' . esc_html( $text ) . '</a>';
}
add_filter( 'excerpt_more', 'tad_excerpt_more' );

function tad_trim_excerpt_characters( $trimmed, $raw_excerpt ) {
	if ( '' !== $raw_excerpt || 'chars' !== tad_get_option( 'excerpt_length_unit', 'words' ) ) {
		return $trimmed;
	}

	$limit = (int) tad_get_option( 'excerpt_length', 0 );

	if ( ! $limit ) {
		return $trimmed;
	}

	$text = get_the_content( '' );
	$text = strip_shortcodes( $text );
	$text = apply_filters( 'the_content', $text );
	$text = wp_strip_all_tags( str_replace( ']]>', ']]&gt;', $text ) );
	$text = preg_replace( '/[\n\r\t ]+/', ' ', $text );

	if ( function_exists( 'mb_substr' ) ) {
		$text = mb_substr( $text, 0, $limit );
	} else {
		$text = substr( $text, 0, $limit );
	}

	$text = preg_replace( '/\s+\S*$/', '', $text );

	return trim( $text ) . apply_filters( 'excerpt_more', ' [&hellip;]' );
}
add_filter( 'wp_trim_excerpt', 'tad_trim_excerpt_characters', 10, 2 );

function tad_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! tad_feature_enabled( 'default_thumbnail' ) || $post_thumbnail_id || ! empty( $html ) ) {
		return $html;
	}

	$default_id = tad_get_option( 'default_post_thumbnail_id', 0 );

	if ( $default_id ) {
		return wp_get_attachment_image( $default_id, $size, false, $attr );
	}

	return $html;
}
add_filter( 'post_thumbnail_html', 'tad_default_post_thumbnail_html', 10, 5 );
