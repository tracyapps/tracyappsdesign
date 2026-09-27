<?php
/**
 * tad-2026 helpers shared by the ACF block templates.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF value with a default. Only "empty" values (null, '', false, []) fall back,
 * so a deliberate 0 or an unchecked toggle stays as-is when $strict is true.
 */
function tad_field( $name, $default = '', $strict = false ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $name );

	if ( $strict ) {
		return null === $value ? $default : $value;
	}

	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $default : $value;
}

/** Toggle helper: unset => default, otherwise real boolean. */
function tad_toggle( $name, $default = false ) {
	$value = tad_field( $name, null, true );

	if ( null === $value || '' === $value ) {
		return (bool) $default;
	}

	return (bool) $value;
}

/**
 * Allowed inline markup for short text fields. Use tad_kses_inline().
 */
function tad_kses_inline( $text ) {
	return wp_kses(
		$text,
		array(
			'em'     => array(),
			'strong' => array(),
			'br'     => array(),
			'span'   => array( 'class' => array() ),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		)
	);
}

/**
 * Turns "design + technology that {speaks human}." into escaped text with the
 * braced phrase wrapped in the gradient span.
 */
function tad_gradient_text( $text ) {
	$escaped = esc_html( $text );

	return preg_replace( '/\{(.+?)\}/u', '<span class="grad-text">$1</span>', $escaped );
}

/**
 * Attributes for a block's outer element. Wraps get_block_wrapper_attributes()
 * so it also works in the editor preview and in the test harness.
 */
function tad_block_attrs( $block, $classes = '', $extra = array() ) {
	$args = array_merge( array( 'class' => $classes ), $extra );

	// Explicit id from the Anchor field, so it never depends on core adding it.
	if ( ! empty( $block['anchor'] ) && empty( $args['id'] ) ) {
		$args['id'] = sanitize_html_class( $block['anchor'] );
	}

	if ( function_exists( 'get_block_wrapper_attributes' ) ) {
		return get_block_wrapper_attributes( $args );
	}

	$id = ! empty( $block['anchor'] ) ? ' id="' . esc_attr( $block['anchor'] ) . '"' : '';

	return trim( $id . ' class="' . esc_attr( $classes ) . '"' );
}

/** Section-level attributes: base class, spacing modifier, plus extras. */
function tad_section_attrs( $block, $classes = '', $extra = array() ) {
	$space = tad_field( 'section_space', 'default' );
	$space = in_array( $space, array( 'default', 'tight', 'roomy', 'none' ), true ) ? $space : 'default';

	$class = trim( 'tad-section tad-section--space-' . $space . ' ' . $classes );

	return tad_block_attrs( $block, $class, $extra );
}

/** Stable id for aria-labelledby. */
function tad_block_dom_id( $block, $suffix = 'title' ) {
	$base = ! empty( $block['id'] ) ? $block['id'] : 'tad';

	return sanitize_html_class( $base ) . '-' . $suffix;
}

/** Eyebrow / H2 / intro. Returns the heading element id (or '' when no heading). */
function tad_section_head( $block ) {
	$eyebrow = tad_field( 'eyebrow' );
	$heading = tad_field( 'heading' );
	$intro   = tad_field( 'intro' );

	if ( ! $eyebrow && ! $heading && ! $intro ) {
		return '';
	}

	$title_id = tad_block_dom_id( $block );
	?>
	<div class="sec-head reveal">
		<?php if ( $eyebrow ) : ?>
			<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>
		<?php if ( $heading ) : ?>
			<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo tad_kses_inline( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
		<?php endif; ?>
		<?php if ( $intro ) : ?>
			<p><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</div>
	<?php

	return $heading ? $title_id : '';
}

/**
 * Opening <a> for an ACF link array. Returns [ open_tag, close_suffix_html ].
 * External/new-tab links get rel="noopener" and a screen-reader note.
 */
function tad_link_parts( $link, $class = '' ) {
	if ( empty( $link['url'] ) ) {
		return array( '', '' );
	}

	$new_tab = ! empty( $link['target'] ) && '_blank' === $link['target'];
	$attrs   = ' href="' . esc_url( $link['url'] ) . '"';

	if ( $class ) {
		$attrs .= ' class="' . esc_attr( $class ) . '"';
	}

	if ( $new_tab ) {
		$attrs .= ' target="_blank" rel="noopener"';
	}

	$suffix = $new_tab ? '<span class="screen-reader-text"> ' . esc_html__( '(opens in a new tab)', 'tad' ) . '</span>' : '';

	return array( '<a' . $attrs . '>', $suffix . '</a>' );
}

/** Complete link/button from an ACF link field. Echoes nothing when empty. */
function tad_link_button( $link, $class = 'btn btn-primary' ) {
	if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
		return;
	}

	list( $open, $close ) = tad_link_parts( $link, $class );

	echo $open . esc_html( $link['title'] ) . $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/** Named art tiles (CSS-only illustrations) usable as thumbnails. */
function tad_art_choices() {
	return array(
		'papr'   => 'Paper (sun + flare)',
		'exp'    => 'Deep violet panel',
		'coloma' => 'Golden hour',
		'grid'   => 'Dot grid',
		'wave'   => 'Violet-to-flare wave',
		'ink'    => 'Ink',
		'game'   => 'Stripes',
	);
}

/**
 * Art tile / image thumbnail. $image is an attachment ID (or empty).
 * Empty alt => decorative (aria-hidden); otherwise role="img" with the label.
 */
function tad_art( $art, $image_id = 0, $alt = '' ) {
	$art = array_key_exists( $art, tad_art_choices() ) ? $art : '';

	if ( ! $art && ! $image_id ) {
		return '';
	}

	$class = 'art' . ( $art ? ' art--' . $art : '' );
	$a11y  = $alt ? ' role="img" aria-label="' . esc_attr( $alt ) . '"' : ' aria-hidden="true"';
	$inner = $image_id ? wp_get_attachment_image( (int) $image_id, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : '';

	return '<div class="' . esc_attr( $class ) . '"' . $a11y . '>' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/** Icons available to the Capabilities block (24×24, stroke-based). */
function tad_icon_choices() {
	return array(
		'web'           => 'Browser window',
		'wordpress'     => 'Stacked diamond',
		'product'       => 'Panels',
		'games'         => 'Two circles',
		'brand'         => 'Sparkle',
		'accessibility' => 'Info circle',
		'code'          => 'Code brackets',
		'chat'          => 'Speech bubble',
		'heart'         => 'Heart',
		'layers'        => 'Layers',
		'compass'       => 'Compass',
		'teach'         => 'Mortarboard',
	);
}

function tad_icon_svg( $slug ) {
	$paths = array(
		'web'           => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 21h8"/>',
		'wordpress'     => '<path d="M12 3v18M5 8l7-5 7 5M5 16l7 5 7-5"/>',
		'product'       => '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M4 10h16M10 20V10"/>',
		'games'         => '<circle cx="8" cy="12" r="4"/><circle cx="16" cy="12" r="4"/>',
		'brand'         => '<path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/>',
		'accessibility' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v.01M11 12h1v4h1"/>',
		'code'          => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
		'chat'          => '<path d="M4 5h16v11H9l-5 4z"/>',
		'heart'         => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
		'layers'        => '<path d="M12 3l9 5-9 5-9-5zM3 13l9 5 9-5"/>',
		'compass'       => '<circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/>',
		'teach'         => '<path d="M2 9l10-5 10 5-10 5zM6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/>',
	);

	if ( ! isset( $paths[ $slug ] ) ) {
		$slug = 'brand';
	}

	return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $slug ] . '</svg>';
}

/** Wordmark markup (header + footer). Text comes from Site Options. */
function tad_wordmark( $extra_class = '', $label = '' ) {
	$light = tad_get_option( 'wordmark_light', 'tracy apps' );
	$heavy = tad_get_option( 'wordmark_heavy', 'design' );
	$llc   = tad_get_option( 'wordmark_llc', 'LLC' );

	$aria = $label ? $label : sprintf(
		/* translators: %s: site name */
		__( '%s — home', 'tad' ),
		trim( $light . ' ' . $heavy . ( $llc ? ', ' . $llc : '' ) )
	);

	printf(
		'<a class="wordmark %1$s" href="%2$s" aria-label="%3$s"><span class="wordmark__row"><span class="wordmark__light">%4$s</span></span><span class="wordmark__row"><span class="wordmark__heavy">%5$s</span>%6$s</span></a>',
		esc_attr( $extra_class ),
		esc_url( home_url( '/' ) ),
		esc_attr( $aria ),
		esc_html( $light ),
		esc_html( $heavy ),
		$llc ? '<span class="wordmark__llc">' . esc_html( $llc ) . '</span>' : ''
	);
}
