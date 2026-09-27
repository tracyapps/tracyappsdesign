<?php
/**
 * Comments are intentionally quiet by default.
 *
 * @package TAD
 */

if ( ! tad_feature_enabled( 'comments' ) ) {
	return;
}

comment_form();
