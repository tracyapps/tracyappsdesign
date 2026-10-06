<?php
/** Run with wp eval-file. Exercises the real WordPress editor policy and rendering. */
global $checks, $failures;
$checks = 0;
$failures = array();
function tad_editor_check( $condition, $message ) {
	global $checks, $failures;
	++$checks;
	if ( ! $condition ) {
		$failures[] = $message;
	}
}
// Use plugin names without activating scheduling services or contacting providers.
$registry = WP_Block_Type_Registry::get_instance();
$temporary = array();
foreach ( array( 'meet-with-me/button', 'meet-with-me/cards', 'meet-with-me/booking-form', 'example-plugin/widget' ) as $name ) {
	if ( ! $registry->is_registered( $name ) ) {
		register_block_type( $name );
		$temporary[] = $name;
	}
}
$context = new WP_Block_Editor_Context();
$allowed = tad_allowed_block_types( true, $context );
foreach ( array( 'core/group', 'core/columns', 'core/column', 'core/media-text', 'meet-with-me/button', 'meet-with-me/cards', 'meet-with-me/booking-form', 'example-plugin/widget' ) as $name ) {
	tad_editor_check( in_array( $name, $allowed, true ), 'Editor must allow ' . $name );
}
$restricted = array( 'core/paragraph', 'meet-with-me/booking-form' );
tad_editor_check( $restricted === tad_allowed_block_types( $restricted, $context ), 'Respect an upstream restricted list' );
tad_editor_check( false === tad_allowed_block_types( false, $context ), 'Respect an upstream disabled inserter' );
tad_editor_check( ! in_array( 'core/site-title', $allowed, true ), 'Keep site-template blocks out of content editing' );
$settings = WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
tad_editor_check( ! empty( $settings['spacing']['padding'] ), 'Enable padding controls' );
tad_editor_check( ! empty( $settings['spacing']['margin'] ), 'Enable margin controls' );
$source = file_get_contents( get_template_directory() . '/blocks/tad-accent-section/tad-accent-section.php' );
tad_editor_check( ! str_contains( $source, 'allowedBlocks=' ), 'Accent Section must inherit the editor policy' );
// Native support styles must reach the ACF frontend wrapper alongside glass variables.
$accent = render_block( array(
	'blockName'    => 'acf/tad-accent-section',
	'attrs'        => array(
		'name'  => 'acf/tad-accent-section',
		'data'  => array( 'top_transition' => 0, 'bottom_transition' => 0 ),
		'style' => array( 'spacing' => array(
			'padding' => array( 'top' => 'var:preset|spacing|lg', 'right' => '24px' ),
			'margin'  => array( 'bottom' => '32px' ),
		) ),
	),
	'innerBlocks'  => array(),
	'innerHTML'    => '<p>Nested content survives.</p>',
	'innerContent' => array( '<p>Nested content survives.</p>' ),
) );
foreach ( array( 'padding-top:var(--wp--preset--spacing--lg)', 'padding-right:24px', 'margin-bottom:32px', '--acc-tint:', 'Nested content survives.' ) as $expected ) {
	tad_editor_check( str_contains( $accent, $expected ), 'Accent rendering includes ' . $expected );
}
$preview_attrs = tad_block_attrs( array( 'anchor' => 'booking' ), 'tad-accent', array( 'style' => '--acc-tint:#000', 'data-acc' => '' ), true );
tad_editor_check( str_contains( $preview_attrs, 'style="--acc-tint:#000"' ) && str_contains( $preview_attrs, 'id="booking"' ), 'Editor preview preserves glass styling and anchor' );
tad_editor_check( ! str_contains( $preview_attrs, 'padding-top:' ), 'Editor preview leaves native spacing to the ACF wrapper' );
$styles = WP_Block_Styles_Registry::get_instance()->get_registered_styles_for_block( 'core/group' );
foreach ( array( 'tad-panel', 'tad-reading' ) as $name ) {
	tad_editor_check( in_array( $name, array_column( $styles, 'name' ), true ), 'Group style is registered: ' . $name );
}
// Check the page controls through saved ACF fields on an isolated draft.
$fixture = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Theme integration fixture' ) );
global $post;
$previous_post = $post;
$post = get_post( $fixture );
try {
	tad_editor_check( 'entry layout' === tad_page_layout_classes(), 'Existing pages keep their default layout' );
	foreach ( array( 'reading', 'compact' ) as $width ) {
		update_field( 'field_tad_page_width', $width, $fixture );
		update_field( 'field_tad_page_spacing', 'roomy', $fixture );
		tad_editor_check( str_contains( tad_page_layout_classes(), 'entry--width-' . $width ), 'Saved page width is rendered: ' . $width );
		tad_editor_check( str_contains( tad_page_layout_classes(), 'entry--space-roomy' ), 'Saved page spacing is rendered' );
	}
	update_field( 'field_tad_page_width', 'invalid', $fixture );
	update_field( 'field_tad_page_spacing', 'invalid', $fixture );
	tad_editor_check( 'entry layout' === tad_page_layout_classes(), 'Unknown page choices fall back safely' );
} finally {
	$post = $previous_post;
	wp_delete_post( $fixture, true );
}
foreach ( $temporary as $name ) {
	unregister_block_type( $name );
}
if ( $failures ) {
	WP_CLI::error( implode( "\n", $failures ) );
}
WP_CLI::success( "$checks editor integration checks passed." );
