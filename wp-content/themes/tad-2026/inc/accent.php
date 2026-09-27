<?php
/**
 * Accent section ("the transition"): settings + wave-layer generator.
 *
 * Settings resolve in this order (later wins):
 *   1. tad_accent_defaults()                 — built-in defaults, below
 *   2. Site Options › Glass Transition        — site-wide (field: accent_defaults)
 *   3. the block's own override, when its "Override site settings" toggle is on
 *
 * The wave shapes are generated here, from the seed / length / complexity /
 * height settings, as tiny SVGs used as CSS masks (see blocks/tad-accent-section
 * and assets/src/css/blocks/accent.css). Every visual value ends up as a CSS
 * variable on the section, so it can also be tweaked live in dev tools.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Built-in defaults. Keys mirror the ACF "Glass transition settings" group. */
function tad_accent_defaults() {
	return array(
		// Glass.
		'layers'            => 4,
		'tint'              => '#0a0518',
		'opacity'           => 24,
		'blur'              => 14,
		'saturation'        => 160,
		'brightness'        => 100,
		'edge'              => 14,
		'edge_width'        => 2,
		// Shape.
		'height'            => 220,
		'mobile_scale'      => 65,
		'peak'              => 70,
		'length'            => 1800,
		'complexity'        => 3,
		'seed'              => 7,
		'spacing'           => 22,
		'fade'              => 55,
		// Motion.
		'animate'           => 1,
		'drift_duration'    => 160,
		'drift_variance'    => 35,
		'bob'               => 12,
		'bob_duration'      => 26,
		'motion_rate'       => 5,
		'parallax_mode'     => 'spread',
		'parallax_strength' => 70,
	);
}

/** Merge helper that ignores empty strings/nulls from ACF but keeps real zeros. */
function tad_accent_merge( array $base, $incoming ) {
	if ( ! is_array( $incoming ) ) {
		return $base;
	}

	foreach ( $base as $key => $value ) {
		if ( array_key_exists( $key, $incoming ) && null !== $incoming[ $key ] && '' !== $incoming[ $key ] ) {
			$base[ $key ] = $incoming[ $key ];
		}
	}

	return $base;
}

/**
 * Resolve + sanitize the settings for one accent section.
 *
 * @param array|null $override The block's override group (or null / empty for "use site settings").
 */
function tad_accent_settings( $override = null ) {
	$s = tad_accent_defaults();

	if ( function_exists( 'get_field' ) ) {
		$s = tad_accent_merge( $s, get_field( 'accent_defaults', 'option' ) );
	}

	$s = tad_accent_merge( $s, $override );

	$tint = sanitize_hex_color( (string) $s['tint'] );

	$clean = array(
		'layers'            => max( 1, min( 6, (int) $s['layers'] ) ),
		'tint'              => $tint ? $tint : '#0a0518',
		'opacity'           => max( 0, min( 100, (float) $s['opacity'] ) ),
		'blur'              => max( 0, min( 80, (float) $s['blur'] ) ),
		'saturation'        => max( 50, min( 400, (float) $s['saturation'] ) ),
		'brightness'        => max( 30, min( 200, (float) $s['brightness'] ) ),
		'edge'              => max( 0, min( 100, (float) $s['edge'] ) ),
		'edge_width'        => max( 0, min( 12, (float) $s['edge_width'] ) ),
		'height'            => max( 60, min( 600, (float) $s['height'] ) ),
		'mobile_scale'      => max( 30, min( 100, (float) $s['mobile_scale'] ) ),
		'peak'              => max( 15, min( 95, (float) $s['peak'] ) ),
		'length'            => max( 600, min( 4000, (float) $s['length'] ) ),
		'complexity'        => max( 1, min( 4, (int) $s['complexity'] ) ),
		'seed'              => (int) $s['seed'],
		'spacing'           => max( -100, min( 200, (float) $s['spacing'] ) ),
		'fade'              => max( 10, min( 100, (float) $s['fade'] ) ),
		'animate'           => (bool) $s['animate'],
		'drift_duration'    => max( 20, min( 1200, (float) $s['drift_duration'] ) ),
		'drift_variance'    => max( 0, min( 90, (float) $s['drift_variance'] ) ),
		'bob'               => max( 0, min( 80, (float) $s['bob'] ) ),
		'bob_duration'      => max( 6, min( 120, (float) $s['bob_duration'] ) ),
		'motion_rate'       => max( 1, min( 60, (int) $s['motion_rate'] ) ),
		'parallax_mode'     => in_array( $s['parallax_mode'], array( 'none', 'spread', 'collapse', 'drift' ), true ) ? $s['parallax_mode'] : 'spread',
		'parallax_strength' => max( -600, min( 600, (float) $s['parallax_strength'] ) ),
	);

	return $clean;
}

/** Tiny deterministic PRNG (LCG) so a given seed always draws the same shapes. */
function tad_accent_rng( $seed ) {
	$state = abs( (int) $seed ) % 2147483647;

	if ( 0 === $state ) {
		$state = 1;
	}

	return function () use ( &$state ) {
		$state = ( $state * 1103515245 + 12345 ) % 2147483648;

		return $state / 2147483648;
	};
}

/**
 * One seamless wave tile as an SVG mask (data URI). The tile is a sum of
 * whole-number harmonics, so its left and right edges always meet.
 * ViewBox is 1000×100; CSS stretches it to the real size.
 *
 * @return string CSS url(...) value.
 */
function tad_accent_wave_mask( $seed, $tile_px, $peak_pct, $complexity ) {
	$rnd      = tad_accent_rng( $seed );
	$harmonic = array();

	for ( $k = 1; $k <= $complexity; $k++ ) {
		$harmonic[] = array(
			'k'     => $k,
			'amp'   => ( 0.55 + 0.45 * $rnd() ) / pow( $k, 0.9 ),
			'phase' => $rnd() * 2 * M_PI,
		);
	}

	$samples = max( 64, min( 180, (int) round( $tile_px / 14 ) ) );
	$values  = array();
	$max     = 0.0001;

	for ( $i = 0; $i < $samples; $i++ ) {
		$x = $i / $samples;
		$v = 0.0;

		foreach ( $harmonic as $h ) {
			$v += $h['amp'] * sin( 2 * M_PI * $h['k'] * $x + $h['phase'] );
		}

		$values[] = $v;
		$max      = max( $max, abs( $v ) );
	}

	$top   = 100 * ( 1 - $peak_pct / 100 );  // highest crest, in viewBox units.
	$depth = $peak_pct * 0.55;                // crest-to-trough travel.
	$path  = 'M0 100';

	foreach ( $values as $i => $v ) {
		$y     = $top + $depth * ( 0.5 + 0.5 * ( $v / $max ) );
		$path .= ' L' . round( 1000 * $i / $samples, 1 ) . ' ' . round( $y, 2 );
	}

	// Close the tile: last point repeats the first, so the seam is invisible.
	$first = $top + $depth * ( 0.5 + 0.5 * ( $values[0] / $max ) );
	$path .= ' L1000 ' . round( $first, 2 ) . ' L1000 100 Z';

	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="' . $path . '"/></svg>';

	return 'url(data:image/svg+xml;base64,' . base64_encode( $svg ) . ')';
}

/** Inline style for the section: all global knobs as CSS variables. */
function tad_accent_root_style( array $s ) {
	$a         = $s['opacity'] / 100;
	$body      = min( 0.92, 1 - pow( 1 - $a, $s['layers'] ) );
	$number    = static function ( $n, $precision = 3 ) {
		return rtrim( rtrim( number_format( (float) $n, $precision, '.', '' ), '0' ), '.' );
	};

	$vars = array(
		'--acc-tint'         => $s['tint'],
		'--acc-a'            => $number( $s['opacity'] ),
		'--acc-body-a'       => $number( $body * 100 ),
		'--acc-blur'         => $number( $s['blur'] ) . 'px',
		'--acc-sat'          => $number( $s['saturation'] / 100 ),
		'--acc-bright'       => $number( $s['brightness'] / 100 ),
		'--acc-edge-a'       => $number( $s['edge'] ),
		'--acc-edge-w'       => $number( $s['edge_width'] ) . 'px',
		'--acc-h'            => $number( $s['height'] ) . 'px',
		'--acc-mobile-scale' => $number( $s['mobile_scale'] / 100 ),
		'--acc-spacing'      => $number( $s['spacing'] ) . 'px',
		'--acc-strength'     => $number( $s['parallax_strength'] ) . 'px',
		'--acc-fade'         => $number( $s['fade'] ),
		'--acc-bob'          => $number( $s['bob'] ) . 'px',
	);

	$out = '';

	foreach ( $vars as $name => $value ) {
		$out .= $name . ':' . $value . ';';
	}

	return $out;
}

/**
 * Markup for one wavy edge (all layers).
 *
 * @param string $where 'top' or 'bottom'. The bottom edge is the top edge flipped by CSS
 *                      but drawn from different seeds so the two never mirror each other.
 */
function tad_accent_edge( $where, array $s ) {
	$n        = $s['layers'];
	$seed     = $s['seed'] * 7919 + ( 'bottom' === $where ? 5003 : 0 );
	$layers   = '';

	for ( $i = 0; $i < $n; $i++ ) {
		$rnd   = tad_accent_rng( $seed + $i * 104729 );
		$tile  = (int) round( $s['length'] * ( 0.82 + 0.11 * $i + 0.06 * $rnd() ) );
		$mask  = tad_accent_wave_mask( $seed + $i * 31337, $tile, $s['peak'], $s['complexity'] );
		$vari  = 1 + ( $s['drift_variance'] / 100 ) * ( $rnd() * 2 - 1 );
		$rate  = $s['motion_rate'];

		// Everything moves in small discrete steps on ONE shared tick grid (1/rate s), so
		// all layers redraw together ($rate times a second) instead of each on its own
		// clock. Durations and delays are whole numbers of ticks to keep them aligned.
		$dur_ticks   = max( $rate * 15, (int) round( $s['drift_duration'] * ( $tile / $s['length'] ) * $vari * $rate ) );
		$dur         = $dur_ticks / $rate;
		$delay       = (int) round( $rnd() * $dur_ticks ) / $rate;
		$bob_d       = max( 6, (int) round( $s['bob_duration'] * ( 0.7 + 0.6 * $rnd() ) ) ); // whole seconds.
		$bob_delay   = (int) round( $rnd() * $bob_d );
		$drift_steps = $dur_ticks;
		$bob_steps   = $bob_d; // one step per second (~1px), on the shared grid.
		$k     = $n > 1 ? $i / ( $n - 1 ) : 0;

		$style = sprintf(
			'--i:%1$d;--k:%2$s;--tile:%3$dpx;--dur:%4$ss;--drift-delay:-%5$ss;--drift-dir:%6$s;--bob-dur:%7$ss;--bob-delay:-%8$ss;--mask:%9$s;--drift-steps:%10$d;--bob-steps:%11$d;',
			$i,
			round( $k, 3 ),
			$tile,
			round( $dur, 3 ),
			round( $delay, 3 ),
			( $i % 2 ) ? 'reverse' : 'normal',
			$bob_d,
			$bob_delay,
			$mask,
			$drift_steps,
			$bob_steps
		);

		$layers .= '<div class="tad-accent__layer" style="' . esc_attr( $style ) . '"><div class="tad-accent__shape"></div></div>';
	}

	return '<div class="tad-accent__edge tad-accent__edge--' . esc_attr( $where ) . '" aria-hidden="true">' . $layers . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
