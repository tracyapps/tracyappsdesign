<?php
/**
 * Search form.
 *
 * @package TAD
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="search-form__label" for="search-field"><?php esc_html_e( 'Search', 'tad' ); ?></label>
	<div class="search-form__controls">
		<input id="search-field" class="search-form__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" required>
		<button class="search-form__button" type="submit"><?php esc_html_e( 'Search', 'tad' ); ?></button>
	</div>
</form>
