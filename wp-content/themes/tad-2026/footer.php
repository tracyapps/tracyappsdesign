<?php
/**
 * Footer template.
 *
 * @package TAD
 */

$tad_motion   = tad_get_option( 'motion_toggle_position', 'footer' );
$tad_tagline  = tad_get_option( 'footer_tagline', '' );
$tad_else     = tad_get_option( 'footer_elsewhere', array() );
$tad_note     = tad_get_option( 'footer_note', '' );
$tad_else_ttl = tad_get_option( 'footer_elsewhere_title', 'elsewhere' );
$tad_exp_ttl  = tad_get_option( 'footer_explore_title', 'explore' );
?>
	</main>

	<footer class="site-footer" role="contentinfo">
		<div class="wrap">
			<div class="foot">
				<div>
					<?php tad_wordmark( 'wordmark--lg' ); ?>
					<?php if ( $tad_tagline ) : ?>
						<p class="foot__tagline"><?php echo esc_html( $tad_tagline ); ?></p>
					<?php endif; ?>
					<?php tad_social_links(); ?>
				</div>

				<nav aria-labelledby="foot-explore">
					<h2 id="foot-explore"><?php echo esc_html( $tad_exp_ttl ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-menu',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => 'tad_default_menu',
						)
					);
					?>
				</nav>

				<?php if ( ! empty( $tad_else ) ) : ?>
					<div>
						<h2 id="foot-elsewhere"><?php echo esc_html( $tad_else_ttl ); ?></h2>
						<ul aria-labelledby="foot-elsewhere">
							<?php foreach ( $tad_else as $row ) : ?>
								<?php
								if ( empty( $row['link']['url'] ) ) {
									continue;
								}
								list( $open, $close ) = tad_link_parts( $row['link'] );
								?>
								<li><?php echo $open . esc_html( ( $row['link']['title'] ?? '' ) ?: $row['link']['url'] ) . $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="foot__bottom">
				<p class="site-footer__copyright"><?php tad_copyright_text(); ?></p>
				<?php if ( $tad_note ) : ?>
					<p><?php echo esc_html( $tad_note ); ?></p>
				<?php endif; ?>
				<?php if ( in_array( $tad_motion, array( 'footer', 'both' ), true ) ) { tad_motion_toggle(); } ?>
			</div>
		</div>
	</footer>
</div>

<?php tad_the_svg_sprite(); ?>
<?php wp_footer(); ?>
</body>
</html>
