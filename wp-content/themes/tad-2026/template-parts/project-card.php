<?php
/**
 * One project card. Used by the Work Grid block, the archives and "similar projects".
 *
 * $args: post_id, context ('grid' = masonry with per-card layout/size | 'uniform' = same
 *        width | 'list' = list/detail row), heading (h2|h3).
 *
 * Links: with detail pages ON the title links to the project page and the external site
 * (if any) gets its own "visit" link. With detail pages OFF the title links straight to
 * the external site; with no URL the card is plain (no link at all). Every link that opens
 * a new tab shows an icon AND tells screen readers.
 *
 * @package TAD
 */

$p       = tad_project_data( $args['post_id'] );
$context = $args['context'] ?? 'grid';
$heading = in_array( $args['heading'] ?? 'h3', array( 'h2', 'h3', 'h4' ), true ) ? $args['heading'] : 'h3';
$is_row  = 'list' === $context;
$layout  = 'grid' === $context ? $p['layout'] : 'top';
$size    = 'grid' === $context ? $p['size'] : 'standard';

// Primary (whole-card) link.
$primary = '';
$primary_new_tab = false;

if ( $p['permalink'] ) {
	$primary = $p['permalink'];
} elseif ( $p['url'] ) {
	$primary         = $p['url'];
	$primary_new_tab = true;
}

$classes = array( 'project-card', 'project-card--' . ( $is_row ? 'row' : $layout ), 'project-card--' . $size, 'project-card--ctx-' . $context );

if ( $primary ) {
	$classes[] = 'has-link';
}

// Media: featured image, else the CSS-art fallback.
$alt   = $p['image_id'] ? trim( (string) get_post_meta( $p['image_id'], '_wp_attachment_image_alt', true ) ) : '';
// Matches the column counts in work.css (full-bleed upper bound; smaller in a .wrap).
$span  = 'grid' === $context && ( 'top' !== $layout || 'standard' !== $size ) ? 2 : 1;
$sizes = 1 === $span
	? '(min-width: 3040px) 17vw, (min-width: 2240px) 20vw, (min-width: 1664px) 25vw, (min-width: 1152px) 34vw, (min-width: 576px) 50vw, 100vw'
	: '(min-width: 3040px) 34vw, (min-width: 2240px) 40vw, (min-width: 1664px) 75vw, (min-width: 1152px) 67vw, 100vw';

if ( $is_row ) {
	$sizes = '(min-width: 900px) 380px, (min-width: 620px) 40vw, 100vw';
}

$media = '';

if ( $p['image_id'] ) {
	$media = wp_get_attachment_image(
		$p['image_id'],
		'large',
		false,
		array(
			'alt'      => $alt,
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => $sizes,
		)
	);
} else {
	$art   = $p['art'] && array_key_exists( $p['art'], tad_art_choices() ) ? $p['art'] : 'grid';
	$media = '<div class="art art--' . esc_attr( $art ) . '" style="aspect-ratio:' . esc_attr( $p['art_ratio'] ) . '" aria-hidden="true"></div>';
}
?>
<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?> reveal" data-tilt>
	<article class="project-card__inner glass">
		<div class="project-card__glow" aria-hidden="true"></div>

		<div class="project-card__media">
			<div class="project-card__frame" data-tilt-frame>
				<div class="project-card__img"><?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<span class="project-card__gloss" aria-hidden="true"></span>
			</div>
		</div>

		<div class="project-card__body">
			<?php if ( $p['tags'] ) : ?>
				<ul class="project-card__tags" role="list">
					<?php foreach ( $p['tags'] as $term ) : ?>
						<li>
							<?php if ( $p['permalink'] ) : ?>
								<a class="chip chip--link" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
							<?php else : ?>
								<span class="chip"><?php echo esc_html( $term->name ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<<?php echo esc_attr( $heading ); ?> class="project-card__title">
				<?php if ( $primary ) : ?>
					<a class="project-card__link" href="<?php echo esc_url( $primary ); ?>"<?php echo $primary_new_tab ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $p['title'] ); ?><?php
					if ( $primary_new_tab ) {
						echo ' ' . tad_icon_external() . tad_new_tab_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?></a>
				<?php else : ?>
					<?php echo esc_html( $p['title'] ); ?>
				<?php endif; ?>
			</<?php echo esc_attr( $heading ); ?>>

			<?php if ( $p['excerpt'] ) : ?>
				<p class="project-card__excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
			<?php endif; ?>

			<?php if ( $is_row && ( $p['year'] || $p['role'] || $p['categories'] ) ) : ?>
				<ul class="project-card__meta" role="list">
					<?php if ( $p['categories'] ) : ?>
						<li><?php echo esc_html( implode( ', ', wp_list_pluck( $p['categories'], 'name' ) ) ); ?></li>
					<?php endif; ?>
					<?php if ( $p['role'] ) : ?>
						<li><?php echo esc_html( $p['role'] ); ?></li>
					<?php endif; ?>
					<?php if ( $p['year'] ) : ?>
						<li><?php echo esc_html( $p['year'] ); ?></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $p['permalink'] && $p['url'] ) : ?>
				<p class="project-card__actions">
					<a class="project-card__visit" href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( tad_project_link_label( $p ) ); ?> <?php echo tad_icon_external(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo tad_new_tab_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</p>
			<?php endif; ?>
		</div>
	</article>
</li>
