<?php
/**
 * General Theme-Specific Functions.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */

add_action( 'init', 'tracyappsdesign_register_image_sizes', 5 );
/**
 * Register custom image sizes for the theme.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function tracyappsdesign_register_image_sizes() {
	// Set the 'post-thumbnail' size.
	set_post_thumbnail_size( 175, 130, true );

	// Add the 'tracyappsdesign-full' image size.
	add_image_size( 'tracyappsdesign-full', 1025, 500, true );
}

add_filter( 'excerpt_length', 'tracyappsdesign_excerpt_length' );
/**
 * Add a custom excerpt length.
 *
 * @since  1.0.0
 * @access public
 * @param  integer $length
 * @return integer
 */
function tracyappsdesign_excerpt_length( $length ) {
	return 60;
}

/**
 * get meta fields
 *
 * @param array $meta_key
 * @return array
 */
function tracyappsdesign_get_meta( $meta_key ) {
	return get_post_meta( get_the_ID(), $meta_key, true );
}

/**
 * function to loop through homepage section content.
 * @param $section_key
 * @return mixed
 */

function tracyappsdesign_loop_home_section( $section_key ) {
	require_once get_template_directory() . '/includes/vendor/Mobile_Detect.php';
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'desktop');

	$section_id = $section_key[ 'the_section_page' ];
	$section_data = get_post( $section_id );
	$section_color = get_post_meta( $section_id, 'page_bg_color', true );
	?>
	<section id='<?php esc_html_e( $section_data->post_name ); ?>' class='<?php if ( $deviceType == 'desktop' ) { echo 'full-height '; } ?><?php esc_html_e( $section_color ); ?> section'>
		<div class="frame">
			<div <?php hybrid_attr( 'site-inner' ); ?>>
				<?php if ( '' != $section_tagline = get_post_meta( $section_id, 'page_tagline', true ) ) {
					echo '<h2 class="page-title-top page-title"><span>' . esc_html( $section_key[ 'the_section_title' ] ) . '</span></h2>';
					echo '<span class="page-title-inside-text">' . esc_html( $section_tagline ) . '</span>';
					echo '<h2 class="page-title-bottom page-title"><span>' . esc_html( $section_key[ 'the_section_title' ] ) . '</span></h2>';

				} else {
					echo '<h2 class="page-title">' . esc_html( $section_key[ 'the_section_title' ] ) . '</h2>';
				} ?>
				<p><?php echo apply_filters('the_content', $section_data->post_content); ?></p>
			</div><!--#site-inner-->
		</div><!--/.frame-->
	</section>
	<?php
}

add_action( 'tha_entry_top', 'tracyappsdesign_do_sticky_banner' );
/**
 * Add markup for a sticky ribbon on sticky posts in archive views.
 *
 * @since   1.0.0
 * @return  void
 */
function tracyappsdesign_do_sticky_banner() {
	if ( is_singular() || ! is_sticky() ) {
		return;
	}
	?>
	<div class="corner-ribbon sticky">
		<p class="ribbon-content"><?php _e( 'Sticky', 'tracyappsdesign' ); ?></p>
	</div>
	<?php
}

/**
 * Display footer credits for the theme.
 *
 * @since      1.0.0
 * @return     void
 * @deprecated This was moved into the footer.php template and will be deleted.
 */
function tracyappsdesign_footer_creds() {}
