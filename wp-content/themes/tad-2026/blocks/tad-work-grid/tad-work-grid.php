<?php
/**
 * Work grid block: selected projects from the Projects post type, laid out as a
 * content-aware masonry (each card picks its own image position and width). The grid
 * itself is full-bleed (spans the viewport); the heading and "see all" link stay contained.
 *
 * @package TAD
 */

$order      = tad_field( 'order', 'random' );
$count      = (int) tad_field( 'count', 9, true );
$picked_raw = (array) tad_field( 'picked', array() );
$picked     = array();

foreach ( $picked_raw as $item ) {
	$picked[] = is_object( $item ) ? (int) $item->ID : (int) $item;
}

$query = tad_projects_query(
	array(
		'order'      => in_array( $order, array( 'random', 'newest', 'manual' ), true ) ? $order : 'random',
		'count'      => $count,
		'categories' => array_map( 'intval', (array) tad_field( 'categories', array() ) ),
		'picked'     => $picked,
	)
);

$show_archive = tad_toggle( 'show_archive_link', true ) && tad_projects_detail_enabled();
$archive_url  = $show_archive ? get_post_type_archive_link( 'project' ) : '';
?>
<section <?php echo tad_section_attrs( $block, 'tad-work' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap">
		<?php tad_section_head( $block ); ?>
	</div>

	<?php if ( $query->have_posts() ) : ?>
		<?php /* Full-bleed: the grid breaks out of the contained .wrap and spans the viewport. */ ?>
		<div class="tad-work__grid">
			<ul class="project-grid" role="list" data-masonry>
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					tad_project_card( get_the_ID(), array( 'context' => 'grid', 'heading' => 'h3' ) );
				}
				wp_reset_postdata();
				?>
			</ul>
		</div>

		<?php if ( $archive_url ) : ?>
			<div class="wrap">
				<p class="project-grid__more">
					<a class="btn btn-secondary" href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( tad_field( 'archive_link_label', __( 'see all work', 'tad' ) ) ); ?></a>
				</p>
			</div>
		<?php endif; ?>
	<?php elseif ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) : ?>
		<div class="wrap">
			<p class="eyebrow"><?php esc_html_e( 'No projects yet. Add some under Projects in the admin menu.', 'tad' ); ?></p>
		</div>
	<?php endif; ?>
</section>
