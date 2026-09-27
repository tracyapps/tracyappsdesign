<?php
/**
 * Contact block: intro + quick options + built-in form (or a form-plugin shortcode).
 *
 * @package TAD
 */

$mode       = 'shortcode' === tad_field( 'form_mode', 'builtin' ) ? 'shortcode' : 'builtin';
$shortcode  = trim( (string) tad_field( 'shortcode' ) );
$aside_head = tad_field( 'aside_heading' );
$aside_text = tad_field( 'aside_text' );
$aside_link = tad_field( 'aside_link', array() );
$phone_text = tad_field( 'phone_text' );
$phone_num  = preg_replace( '/[^0-9+]/', '', (string) tad_field( 'phone_number' ) );
$show_topic = tad_toggle( 'show_topic', true );
$topics     = array_filter( array_map( static function ( $row ) { return trim( (string) ( $row['label'] ?? '' ) ); }, (array) tad_field( 'topics', array() ) ) );
$note       = tad_field( 'form_note' );
$uid        = tad_block_dom_id( $block, 'f' );

$status_sent  = isset( $_GET['tad-sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$status_error = isset( $_GET['tad-error'] ) ? sanitize_key( wp_unslash( $_GET['tad-error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$success_title = tad_get_option( 'contact_success_title', __( 'thanks — that’s in the pile.', 'tad' ) );
$success_text  = tad_get_option( 'contact_success_text', __( 'Your message is on its way. You’ll hear back soon.', 'tad' ) );

$has_aside = $aside_head || $aside_text || ! empty( $aside_link['url'] ) || ( $phone_text && $phone_num );
$title_id  = '';
?>
<section <?php echo tad_section_attrs( $block, 'tad-contact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wrap" data-contact>
		<?php $title_id = tad_section_head( $block ); ?>

		<div class="contact__card glass reveal">
			<?php if ( $has_aside ) : ?>
				<div class="contact__aside">
					<?php if ( $aside_head ) : ?>
						<h3><?php echo esc_html( $aside_head ); ?></h3>
					<?php endif; ?>
					<?php if ( $aside_text ) : ?>
						<p><?php echo esc_html( $aside_text ); ?></p>
					<?php endif; ?>
					<div class="contact__ways">
						<?php tad_link_button( $aside_link, 'btn btn-secondary' ); ?>
						<?php if ( $phone_text && $phone_num ) : ?>
							<a class="contact__way" href="tel:<?php echo esc_attr( $phone_num ); ?>"><?php echo esc_html( $phone_text ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="contact__main">
				<?php if ( 'shortcode' === $mode ) : ?>
					<div class="tad-contact__embed">
						<?php echo $shortcode ? do_shortcode( $shortcode ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<?php if ( $status_error ) : ?>
						<p class="form-status form-status--error" role="alert">
							<?php echo esc_html( tad_contact_error_message( $status_error ) ); ?>
						</p>
					<?php endif; ?>

					<form class="contact__form" data-contact-form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate<?php echo $status_sent ? ' hidden' : ''; ?> <?php echo $note ? 'aria-describedby="' . esc_attr( $uid ) . '-note"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<input type="hidden" name="action" value="tad_contact">
						<input type="hidden" name="return_anchor" value="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
						<input type="hidden" name="tad_ts" value="<?php echo esc_attr( (string) time() ); ?>">

						<div class="form__hp" aria-hidden="true">
							<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'tad' ); ?></label>
							<input id="<?php echo esc_attr( $uid ); ?>-website" type="text" name="website" tabindex="-1" autocomplete="off">
						</div>

						<div class="form__row form__row--2">
							<div class="field">
								<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'name', 'tad' ); ?> <span class="req" aria-hidden="true">*</span></label>
								<input id="<?php echo esc_attr( $uid ); ?>-name" name="name" type="text" autocomplete="name" required aria-required="true" data-required data-label="<?php esc_attr_e( 'your name', 'tad' ); ?>">
								<span class="field__error" aria-live="polite"></span>
							</div>
							<div class="field">
								<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'email', 'tad' ); ?> <span class="req" aria-hidden="true">*</span></label>
								<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email" required aria-required="true" data-required data-label="<?php esc_attr_e( 'your email', 'tad' ); ?>">
								<span class="field__error" aria-live="polite"></span>
							</div>
						</div>

						<?php if ( $show_topic && $topics ) : ?>
							<div class="field">
								<label for="<?php echo esc_attr( $uid ); ?>-topic"><?php echo esc_html( tad_field( 'topic_label', __( 'what do you need?', 'tad' ) ) ); ?></label>
								<select id="<?php echo esc_attr( $uid ); ?>-topic" name="topic">
									<?php foreach ( $topics as $topic ) : ?>
										<option value="<?php echo esc_attr( $topic ); ?>"><?php echo esc_html( $topic ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<div class="field">
							<label for="<?php echo esc_attr( $uid ); ?>-message"><?php echo esc_html( tad_field( 'message_label', __( 'the details', 'tad' ) ) ); ?> <span class="req" aria-hidden="true">*</span></label>
							<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" required aria-required="true" data-required data-label="<?php esc_attr_e( 'a few details', 'tad' ); ?>"></textarea>
							<span class="field__error" aria-live="polite"></span>
						</div>

						<div class="form__submit">
							<button class="btn btn-primary" type="submit"><?php echo esc_html( tad_field( 'button_label', __( 'send it over', 'tad' ) ) ); ?></button>
							<?php if ( $note ) : ?>
								<p class="form__note" id="<?php echo esc_attr( $uid ); ?>-note"><?php echo esc_html( $note ); ?></p>
							<?php endif; ?>
						</div>

						<p class="screen-reader-text" data-form-live aria-live="polite"></p>
						<p class="form-status form-status--error" data-form-error role="alert" hidden></p>
					</form>

					<div class="form-status" data-form-success role="status"<?php echo $status_sent ? '' : ' hidden'; ?>>
						<h3><?php echo esc_html( $success_title ); ?></h3>
						<p><?php echo esc_html( $success_text ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
