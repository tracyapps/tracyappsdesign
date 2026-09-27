<?php
/**
 * Hero block.
 *
 * @package TAD
 */

$badge      = tad_field( 'badge_text' );
$pulse      = tad_toggle( 'badge_pulse', true );
$tag        = 'h2' === tad_field( 'heading_tag', 'h1' ) ? 'h2' : 'h1';
$heading    = tad_field( 'heading' );
$lead       = tad_field( 'lead' );
$primary    = tad_field( 'primary_button', array() );
$secondary  = tad_field( 'secondary_button', array() );
$stats      = (array) tad_field( 'stats', array() );
$show_stack = tad_toggle( 'show_cluster', true );
$flip       = tad_toggle( 'flip_layout', false );
$cards      = $show_stack ? array_slice( (array) tad_field( 'cards', array() ), 0, 3 ) : array();
$chips      = $show_stack ? array_slice( (array) tad_field( 'chips', array() ), 0, 3 ) : array();
$has_stack  = $show_stack && ( $cards || $chips );
$title_id   = tad_block_dom_id( $block );

$classes = 'tad-hero';
$classes .= $has_stack ? '' : ' tad-hero--no-cluster';
$classes .= $flip ? ' tad-hero--flip' : '';

$extra = $heading ? array( 'aria-labelledby' => $title_id ) : array();
?>
<section <?php echo tad_block_attrs( $block, $classes, $extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap">
		<div class="tad-hero__grid">
			<div class="tad-hero__copy">
				<?php if ( $badge ) : ?>
					<span class="hero__badge">
						<?php if ( $pulse ) : ?><span class="hero__pulse" aria-hidden="true"></span><?php endif; ?>
						<?php echo esc_html( $badge ); ?>
					</span>
				<?php endif; ?>

				<?php if ( $heading ) : ?>
					<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> id="<?php echo esc_attr( $title_id ); ?>" class="tad-hero__title"><?php echo tad_gradient_text( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php endif; ?>

				<?php if ( $lead ) : ?>
					<p class="tad-hero__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $primary['url'] ) || ! empty( $secondary['url'] ) ) : ?>
					<div class="tad-hero__cta">
						<?php tad_link_button( $primary, 'btn btn-primary' ); ?>
						<?php tad_link_button( $secondary, 'btn btn-secondary' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $stats ) : ?>
					<dl class="tad-hero__stats">
						<?php foreach ( $stats as $stat ) : ?>
							<div class="hero__stat">
								<dt class="screen-reader-text"><?php echo esc_html( $stat['label'] ?? '' ); ?></dt>
								<dd style="margin:0"><strong><?php echo esc_html( $stat['value'] ?? '' ); ?></strong><span aria-hidden="true"><?php echo esc_html( $stat['label'] ?? '' ); ?></span></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</div>

			<?php if ( $has_stack ) : ?>
				<div class="cluster tad-hero__cluster" aria-hidden="true">
					<?php foreach ( $cards as $i => $card ) : ?>
						<figure class="cluster__card cluster__card--<?php echo (int) ( $i + 1 ); ?>" data-parallax="<?php echo esc_attr( $card['depth'] ?? 14 ); ?>" data-base-rotate="<?php echo esc_attr( $card['tilt'] ?? 0 ); ?>" style="<?php echo esc_attr( 'transform:rotate(' . (float) ( $card['tilt'] ?? 0 ) . 'deg)' ); ?>">
							<?php echo tad_art( $card['art'] ?? 'papr', (int) ( $card['image'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php if ( ! empty( $card['caption'] ) ) : ?>
								<figcaption><?php echo esc_html( $card['caption'] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>

					<?php if ( $cards ) : ?><span class="cluster__tape"></span><?php endif; ?>

					<?php foreach ( $chips as $i => $chip ) : ?>
						<?php if ( empty( $chip['text'] ) ) { continue; } ?>
						<span class="cluster__chip cluster__chip--<?php echo (int) ( $i + 1 ); ?>" data-parallax="<?php echo esc_attr( $chip['depth'] ?? 24 ); ?>"><?php echo esc_html( $chip['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
