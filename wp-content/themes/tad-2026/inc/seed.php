<?php
/**
 * Tools › Seed Home Page.
 *
 * One click creates a DRAFT "Home" page from seed/home.php (the aurora design's
 * copy, in the handoff order) and fills empty Site Options (header button, footer
 * text/links, contact recipient). It never overwrites existing content or options,
 * and never publishes or changes Settings › Reading.
 *
 * ACF blocks store their values inside the block comment, keyed by field name with a
 * matching "_name" => field_key reference. tad_seed_acf_data() writes that format
 * using the field definitions in acf-json/.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Field definitions (from acf-json) for one block, keyed by field name. */
function tad_seed_block_fields( $block_name ) {
	static $cache = array();

	if ( isset( $cache[ $block_name ] ) ) {
		return $cache[ $block_name ];
	}

	$cache[ $block_name ] = array();

	foreach ( (array) glob( TAD_PATH . 'acf-json/group_tad_block_*.json' ) as $file ) {
		$group = json_decode( (string) file_get_contents( $file ), true );

		if ( empty( $group['location'] ) ) {
			continue;
		}

		foreach ( $group['location'] as $rules ) {
			foreach ( $rules as $rule ) {
				if ( 'block' === ( $rule['param'] ?? '' ) && $block_name === ( $rule['value'] ?? '' ) ) {
					foreach ( $group['fields'] as $field ) {
						if ( ! empty( $field['name'] ) ) {
							$cache[ $block_name ][ $field['name'] ] = $field;
						}
					}
					break 3;
				}
			}
		}
	}

	return $cache[ $block_name ];
}

/** Flatten values into ACF's block data format (name + _name reference pairs). */
function tad_seed_acf_data( array $fields, array $values, $prefix = '' ) {
	$data = array();

	foreach ( $fields as $name => $field ) {
		if ( ! array_key_exists( $name, $values ) ) {
			continue;
		}

		$value = $values[ $name ];
		$key   = $prefix . $name;
		$type  = $field['type'] ?? 'text';

		if ( 'repeater' === $type ) {
			$rows = is_array( $value ) ? array_values( $value ) : array();
			$subs = array();

			foreach ( $field['sub_fields'] as $sub ) {
				if ( ! empty( $sub['name'] ) ) {
					$subs[ $sub['name'] ] = $sub;
				}
			}

			$data[ $key ]       = count( $rows );
			$data[ '_' . $key ] = $field['key'];

			foreach ( $rows as $i => $row ) {
				$data += tad_seed_acf_data( $subs, (array) $row, $key . '_' . $i . '_' );
			}

			continue;
		}

		if ( 'true_false' === $type ) {
			$value = $value ? 1 : 0;
		}

		$data[ $key ]       = $value;
		$data[ '_' . $key ] = $field['key'];
	}

	return $data;
}

/** One block definition from seed/home.php → a parsed-block array for serialize_blocks(). */
function tad_seed_block( array $def ) {
	$fields = tad_seed_block_fields( $def['name'] );
	$attrs  = array(
		'name' => $def['name'],
		'data' => tad_seed_acf_data( $fields, (array) ( $def['fields'] ?? array() ) ),
		'mode' => 'preview',
	);

	if ( ! empty( $def['anchor'] ) ) {
		$attrs['anchor'] = $def['anchor'];
	}

	$inner = array();

	foreach ( (array) ( $def['inner'] ?? array() ) as $child ) {
		$inner[] = tad_seed_block( $child );
	}

	return array(
		'blockName'    => $def['name'],
		'attrs'        => $attrs,
		'innerBlocks'  => $inner,
		'innerHTML'    => '',
		'innerContent' => $inner ? array_fill( 0, count( $inner ), null ) : array(),
	);
}

/** Whole page markup. */
function tad_seed_markup() {
	$defs   = require TAD_PATH . 'seed/home.php';
	$blocks = array();

	foreach ( $defs as $def ) {
		$blocks[] = tad_seed_block( $def );
	}

	return serialize_blocks( $blocks );
}

/** Fill Site Options that are still empty. Returns the option names it set. */
function tad_seed_options() {
	if ( ! function_exists( 'update_field' ) ) {
		return array();
	}

	$wanted = array(
		'header_cta'      => array(
			'title'  => 'start a project',
			'url'    => home_url( '/#contact' ),
			'target' => '',
		),
		'footer_tagline'  => 'Compelling, creative solutions for web, product, and games. Milwaukee, Wisconsin.',
		'footer_elsewhere' => array(
			array(
				'link' => array(
					'title'  => 'tapps.design',
					'url'    => 'https://tapps.design',
					'target' => '_blank',
				),
			),
			array(
				'link' => array(
					'title'  => 'schedule a call',
					'url'    => 'https://calendly.com/tapps',
					'target' => '_blank',
				),
			),
		),
		'footer_note'     => 'built with long shadows and short meetings.',
	);

	$set = array();

	foreach ( $wanted as $name => $value ) {
		$current = get_field( $name, 'option' );

		if ( empty( $current ) ) {
			update_field( $name, $value, 'option' );
			$set[] = $name;
		}
	}

	return $set;
}

/** Create the starter projects (seed/projects.php). Skips titles that already exist. */
function tad_seed_projects() {
	$keys = array();
	$file = TAD_PATH . 'acf-json/group_tad_project.json';
	$group = json_decode( (string) file_get_contents( $file ), true );

	foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
		if ( ! empty( $field['name'] ) ) {
			$keys[ $field['name'] ] = $field['key'];
		}
	}

	$made    = 0;
	$skipped = 0;
	$order   = 0;

	foreach ( (array) require TAD_PATH . 'seed/projects.php' as $def ) {
		$order++;

		if ( get_posts( array( 'post_type' => 'project', 'title' => $def['title'], 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
			$skipped++;
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'project',
				'post_status'  => 'publish',
				'post_title'   => $def['title'],
				'post_excerpt' => $def['excerpt'],
				'menu_order'   => $order,
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		$meta = array(
			'card_layout'        => $def['layout'],
			'card_size'          => $def['size'],
			'card_art'           => $def['art'],
			'card_art_ratio'     => $def['ratio'],
			'project_url'        => $def['url'],
			'project_link_label' => $def['label'] ?? '',
			'project_year'       => $def['year'] ?? '',
			'project_role'       => $def['role'] ?? '',
		);

		foreach ( $meta as $name => $value ) {
			update_post_meta( $post_id, $name, $value );

			if ( isset( $keys[ $name ] ) ) {
				update_post_meta( $post_id, '_' . $name, $keys[ $name ] );
			}
		}

		wp_set_object_terms( $post_id, array_map( 'trim', explode( ',', $def['tags'] ) ), 'project_tag' );
		$made++;
	}

	return array( $made, $skipped );
}

function tad_seed_menu() {
	add_management_page(
		__( 'Seed Home Page', 'tad' ),
		__( 'Seed Home Page', 'tad' ),
		'manage_options',
		'tad-seed-home',
		'tad_seed_screen'
	);
}
add_action( 'admin_menu', 'tad_seed_menu' );

function tad_seed_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="wrap"><h1>' . esc_html__( 'Seed Home Page', 'tad' ) . '</h1>';

	if ( ! tad_has_acf() ) {
		echo '<p>' . esc_html__( 'ACF Pro must be active.', 'tad' ) . '</p></div>';
		return;
	}

	if ( isset( $_POST['tad_seed_go'] ) && check_admin_referer( 'tad_seed_home' ) ) {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_title'   => 'Home (seeded)',
				'post_content' => wp_slash( tad_seed_markup() ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $post_id->get_error_message() ) . '</p></div>';
		} else {
			$set = tad_seed_options();

			echo '<div class="notice notice-success"><p>' . esc_html__( 'Created a draft page from the design’s starter content.', 'tad' ) . ' ';
			printf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $post_id ) ), esc_html__( 'Edit it', 'tad' ) );
			echo '</p>';

			if ( $set ) {
				echo '<p>' . esc_html( sprintf( /* translators: %s: option names */ __( 'Filled empty Site Options: %s.', 'tad' ), implode( ', ', $set ) ) ) . '</p>';
			}

			echo '</div><p>' . esc_html__( 'To use it as the home page: publish it, then Settings › Reading › “A static page”.', 'tad' ) . '</p>';
		}
	}

	if ( isset( $_POST['tad_seed_projects_go'] ) && check_admin_referer( 'tad_seed_projects' ) ) {
		list( $made, $skipped ) = tad_seed_projects();

		echo '<div class="notice notice-success"><p>' . esc_html( sprintf( /* translators: 1: created, 2: skipped */ __( 'Projects created: %1$d. Already existed (skipped): %2$d.', 'tad' ), $made, $skipped ) ) . ' ';
		printf( '<a href="%s">%s</a></p></div>', esc_url( admin_url( 'edit.php?post_type=project' ) ), esc_html__( 'View projects', 'tad' ) );
	}

	echo '<p>' . esc_html__( 'Creates a draft page containing every section block, pre-filled with the aurora design’s copy, and fills any empty Site Options. Nothing existing is overwritten or published.', 'tad' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'tad_seed_home' );
	echo '<p><button type="submit" name="tad_seed_go" value="1" class="button button-primary">' . esc_html__( 'Create draft home page', 'tad' ) . '</button></p>';
	echo '</form>';

	echo '<hr><h2>' . esc_html__( 'Starter projects', 'tad' ) . '</h2>';
	echo '<p>' . esc_html__( 'Creates the design’s portfolio cards as real Projects (published, with placeholder art and a mix of layouts and sizes). Projects that already exist by title are skipped. Add screenshots as each project’s “Screenshot / artwork”.', 'tad' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'tad_seed_projects' );
	echo '<p><button type="submit" name="tad_seed_projects_go" value="1" class="button">' . esc_html__( 'Create starter projects', 'tad' ) . '</button></p>';
	echo '</form></div>';
}
