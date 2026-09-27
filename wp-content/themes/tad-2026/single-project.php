<?php
/**
 * Single project (only reachable while Site Options › Projects › detail pages is ON).
 *
 * @package TAD
 */

get_header();

while ( have_posts() ) :
	the_post();

	$p        = tad_project_data( get_the_ID() );
	$archive  = get_post_type_archive_link( 'project' );
	$related  = tad_project_related_ids( get_the_ID(), (int) get_option( 'options_projects_related_count', 3 ) );
	$prev     = get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );
	$alt      = $p['image_id'] ? trim( (string) get_post_meta( $p['image_id'], '_wp_attachment_image_alt', true ) ) : '';
	$has_meta = $p['year'] || $p['role'] || $p['client'] || $p['categories'];
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'project-single' ); ?>>
		<header class="wrap project-single__head">
			<p class="eyebrow"><a href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( '← all work', 'tad' ); ?></a></p>
			<h1 class="page-title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="page-tagline"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<?php if ( $p['tags'] ) : ?>
				<ul class="project-card__tags" role="list">
					<?php foreach ( $p['tags'] as $term ) : ?>
						<li><a class="chip chip--link" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $p['url'] ) : ?>
				<p class="project-single__cta">
					<a class="btn btn-primary" href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( tad_project_link_label( $p ) ); ?> <?php echo tad_icon_external(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo tad_new_tab_notice(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</p>
			<?php endif; ?>
		</header>

		<div class="wrap project-single__stage">
			<?php if ( $p['image_id'] || $p['art'] ) : ?>
				<div class="project-card project-card--ctx-single project-card--top" data-tilt>
					<div class="project-card__media">
						<div class="project-card__frame" data-tilt-frame>
							<div class="project-card__img">
								<?php
								if ( $p['image_id'] ) {
									echo wp_get_attachment_image( $p['image_id'], 'full', false, array( 'alt' => $alt, 'decoding' => 'async', 'sizes' => '(min-width: 1100px) 1100px, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo '<div class="art art--' . esc_attr( $p['art'] ) . '" style="aspect-ratio:' . esc_attr( $p['art_ratio'] ) . '" aria-hidden="true"></div>';
								}
								?>
							</div>
							<span class="project-card__gloss" aria-hidden="true"></span>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $has_meta ) : ?>
				<dl class="project-single__meta">
					<?php if ( $p['client'] ) : ?>
						<div><dt><?php esc_html_e( 'client', 'tad' ); ?></dt><dd><?php echo esc_html( $p['client'] ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $p['role'] ) : ?>
						<div><dt><?php esc_html_e( 'role', 'tad' ); ?></dt><dd><?php echo esc_html( $p['role'] ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $p['year'] ) : ?>
						<div><dt><?php esc_html_e( 'year', 'tad' ); ?></dt><dd><?php echo esc_html( $p['year'] ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $p['categories'] ) : ?>
						<div>
							<dt><?php esc_html_e( 'filed under', 'tad' ); ?></dt>
							<dd>
								<?php
								$links = array();
								foreach ( $p['categories'] as $term ) {
									$links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
								}
								echo implode( ', ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>
		</div>

		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="wrap">
				<div class="entry-content flow project-single__content">
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $prev || $next ) : ?>
			<nav class="wrap project-pager" aria-label="<?php esc_attr_e( 'Project navigation', 'tad' ); ?>">
				<?php if ( $prev ) : ?>
					<a class="project-pager__link project-pager__link--prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev"><span class="eyebrow"><?php esc_html_e( '← previous', 'tad' ); ?></span><span><?php echo esc_html( get_the_title( $prev ) ); ?></span></a>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<a class="project-pager__link project-pager__link--next" href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next"><span class="eyebrow"><?php esc_html_e( 'next →', 'tad' ); ?></span><span><?php echo esc_html( get_the_title( $next ) ); ?></span></a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<?php if ( $related ) : ?>
			<section class="wrap project-related" aria-labelledby="project-related-title">
				<h2 id="project-related-title" class="project-related__title"><?php esc_html_e( 'similar projects', 'tad' ); ?></h2>
				<ul class="project-grid project-grid--uniform project-grid--related" role="list" data-masonry>
					<?php
					foreach ( $related as $related_id ) {
						tad_project_card( $related_id, array( 'context' => 'uniform', 'heading' => 'h3' ) );
					}
					?>
				</ul>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
