<?php
/**
 * Header template.
 *
 * @package TAD
 */

$tad_cta    = tad_get_option( 'header_cta', array() );
$tad_motion = tad_get_option( 'motion_toggle_position', 'footer' );

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		/* Runs before first paint: enables JS-only styles and restores a saved "pause motion" choice. */
		(function () {
			var d = document.documentElement;
			d.classList.add('js');
			try { if (localStorage.getItem('tad-motion') === 'paused') d.dataset.motion = 'paused'; } catch (e) {}
		})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'dir-aurora' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#site-main"><?php esc_html_e( 'Skip to content', 'tad' ); ?></a>

<?php if ( tad_get_option( 'bg_orbs', 1 ) ) : ?>
	<div class="aurora-bg<?php echo tad_get_option( 'bg_grain', 1 ) ? '' : ' no-grain'; ?>" aria-hidden="true">
		<span class="aurora-blob aurora-blob--1"></span>
		<span class="aurora-blob aurora-blob--2"></span>
		<span class="aurora-blob aurora-blob--3"></span>
	</div>
<?php endif; ?>
<?php if ( tad_get_option( 'bg_spotlight', 1 ) ) : ?>
	<div class="spotlight" aria-hidden="true"></div>
<?php endif; ?>

<div class="site-shell">
	<header class="site-header" role="banner">
		<div class="site-header__inner wrap">
			<div class="site-branding">
				<?php tad_wordmark( 'wordmark--sm' ); ?>
			</div>

			<button class="menu-toggle" type="button" data-nav-toggle aria-controls="primary-navigation" aria-expanded="false" data-label-open="<?php esc_attr_e( 'menu', 'tad' ); ?>" data-label-close="<?php esc_attr_e( 'close', 'tad' ); ?>">
				<span data-nav-label><?php esc_html_e( 'menu', 'tad' ); ?></span>
			</button>

			<nav class="primary-navigation" id="primary-navigation" data-nav-panel aria-label="<?php esc_attr_e( 'Primary', 'tad' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => 'tad_default_menu',
					)
				);
				?>
				<?php tad_link_button( $tad_cta, 'btn btn-primary' ); ?>
				<?php if ( in_array( $tad_motion, array( 'header', 'both' ), true ) ) { tad_motion_toggle(); } ?>
			</nav>
		</div>
	</header>

	<main id="site-main" class="site-main">
