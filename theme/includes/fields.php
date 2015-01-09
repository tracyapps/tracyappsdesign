<?php
/**
 * CPT and custom meta fields.
 *
 * @package     TracyAppsDesignLLC
 * @subpackage  HybridCore
 * @copyright   Copyright (c) 2014, Flagship, LLC
 * @license     GPL-2.0+
 * @link        https://flagshipwp.com/
 * @since       1.0.0
 */
$dir = dirname( __FILE__ ) . '/vendor/';

// --------------------------------- CPT

// Supporting code
require_once $dir . 'Taxonomy_Core.php';
require_once $dir . 'CPT_Core.php';

/**
 * register work CPT
 */

class Work_CPT extends CPT_Core {
	public function __construct() {
		// Register the cpt
		parent::__construct(
			array( __( 'Work', 'tracyappsdesign' ), __( 'Example work', 'tracyappsdesign' ), 'work' ),
			array(
				'supports' => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon' => 'dashicons-media-interactive',
			)
		);
	}
}
new Work_CPT();


// --------------------------------- field manager


// Supporting code
require_once( $dir . 'class-fm-tracyappsdesign-data-structures.php' );


/**
 * meta fields just for home page
 */

if ( class_exists( 'Fieldmanager_Field' ) ) :
	function tad_homepage_meta_fields() {
		// Only display these fields for the homepage
		$page_id = false;
		if ( ! empty( $_GET['post'] ) ) {
			$page_id = intval( $_GET['post'] );
		} elseif ( ! empty( $_POST['post_ID'] ) ) {
			$page_id = intval( $_POST['post_ID'] );
		}
		if ( $page_id ) {
			$front_page = intval( get_option( 'page_on_front' ) );
			if ( $front_page == $page_id ) {

				$datasource_page = new Fieldmanager_Datasource_Post( array(
					'query_args' => array( 'post_type' => 'page' ),
					'use_ajax' => false
				) );

				$fm = new Fieldmanager_Group( array(
						'name'			=> 'homepage_onepage_content',
						'limit'			=> 0,
						'add_more_label' => 'Add another page',
						'sortable'		=> true,
						'label'			=> 'Section',
						'label_macro'	=> array( 'Section: %s', 'the_section_title' ),
						'collapsible'	=> true,
						'children'		=> array(
							'the_section_title'	=> new Fieldmanager_textfield( 'Section title' ),
							'the_section_page'	=> new Fieldmanager_Autocomplete( 'Search pages', array( 'datasource' => $datasource_page ) ),
						),
					)
				);

				$fm->add_meta_box( 'Homepage sections', 'page' );

			}
			else {

				$fm = new Fieldmanager_Radios( false, array(
						'name'			=> 'page_bg_color',
						'default_value'	=> 'off-black',
						'options'		=> array( 'red', 'orange', 'yellow', 'green', 'seafoam', 'light-blue', 'grey-blue', 'purple', 'off-black', 'light-fog' ),
					)
				);

				$fm->add_meta_box( 'Background color', 'page' );

			}
		}
	}
	add_action( 'fm_post_page', 'tad_homepage_meta_fields' );

endif;