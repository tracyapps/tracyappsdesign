<?php
/**
 * Shared body for the project archive (/work/) and the project tag/category archives.
 * Layout (masonry grid or list/detail) comes from Site Options › Projects.
 *
 * @package TAD
 */

$layout   = tad_projects_archive_layout();
$is_tax   = is_tax( array( 'project_category', 'project_tag' ) );
$title    = $is_tax ? single_term_title( '', false ) : ( get_option( 'options_projects_archive_title' ) ?: __( 'work', 'tad' ) );
$intro    = $is_tax ? term_description() : wpautop( esc_html( (string) get_option( 'options_projects_archive_intro', '' ) ) );
$archive  = get_post_type_archive_link( 'project' );
$terms    = get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => true ) );
$terms    = is_array( $terms ) ? $terms : array();
$switcher = (bool) get_option( 'options_projects_view_switcher', 0 );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$keep_view = isset( $_GET['view'] ) ? array( 'view' => $layout ) : array();
?>
<div class="wrap project-archive">
	<header class="project-archive__head">
		<?php if ( $is_tax ) : ?>
			<p class="eyebrow">
				<a href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( '← all work', 'tad' ); ?></a>
				· <?php echo esc_html( is_tax( 'project_tag' ) ? __( 'tag', 'tad' ) : __( 'category', 'tad' ) ); ?>
			</p>
		<?php endif; ?>
		<h1 class="page-title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?>
			<div class="page-tagline"><?php echo wp_kses_post( $intro ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( $terms || $switcher ) : ?>
		<div class="project-archive__tools">
			<?php if ( $terms ) : ?>
				<nav class="project-filter" aria-label="<?php esc_attr_e( 'Project categories', 'tad' ); ?>">
					<ul role="list">
						<li><a class="chip chip--link" href="<?php echo esc_url( $archive ); ?>"<?php echo is_post_type_archive( 'project' ) ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'all', 'tad' ); ?></a></li>
						<?php foreach ( $terms as $term ) : ?>
							<li><a class="chip chip--link" href="<?php echo esc_url( get_term_link( $term ) ); ?>"<?php echo is_tax( 'project_category', $term->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $term->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>

			<?php if ( $switcher ) : ?>
				<nav class="project-view" aria-label="<?php esc_attr_e( 'Layout', 'tad' ); ?>">
					<a class="chip chip--link" href="<?php echo esc_url( add_query_arg( 'view', 'grid' ) ); ?>"<?php echo 'grid' === $layout ? ' aria-current="true"' : ''; ?>><?php esc_html_e( 'grid', 'tad' ); ?></a>
					<a class="chip chip--link" href="<?php echo esc_url( add_query_arg( 'view', 'list' ) ); ?>"<?php echo 'list' === $layout ? ' aria-current="true"' : ''; ?>><?php esc_html_e( 'list', 'tad' ); ?></a>
				</nav>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php if ( 'list' === $layout ) : ?>
			<ol class="project-list" role="list">
				<?php
				while ( have_posts() ) {
					the_post();
					tad_project_card( get_the_ID(), array( 'context' => 'list', 'heading' => 'h2' ) );
				}
				?>
			</ol>
		<?php else : ?>
			<ul class="project-grid project-grid--uniform" role="list" data-masonry>
				<?php
				while ( have_posts() ) {
					the_post();
					tad_project_card( get_the_ID(), array( 'context' => 'uniform', 'heading' => 'h2' ) );
				}
				?>
			</ul>
		<?php endif; ?>

		<?php
		the_posts_pagination(
			array(
				'mid_size'           => 1,
				'prev_text'          => __( 'newer', 'tad' ),
				'next_text'          => __( 'older', 'tad' ),
				'screen_reader_text' => __( 'Projects navigation', 'tad' ),
				'add_args'           => $keep_view,
			)
		);
		?>
	<?php else : ?>
		<p class="page-tagline"><?php esc_html_e( 'Nothing here yet.', 'tad' ); ?></p>
	<?php endif; ?>
</div>
