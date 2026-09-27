<?php
/**
 * Built-in contact form handler (no plugin needed).
 *
 * Posts to admin-post.php. Protection: honeypot field, minimum fill time,
 * per-IP rate limit, strict validation, header-injection-safe wp_mail().
 * Recipient + success copy live in Site Options › Contact form.
 *
 * Prefer Formidable / another form plugin? Set the block's "Form" to
 * "Form plugin shortcode" and paste its shortcode.
 *
 * @package TAD
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tad_contact_error_message( $code ) {
	$messages = array(
		'invalid' => __( 'Please check the highlighted fields and try again.', 'tad' ),
		'spam'    => __( 'That looked automated, so it was not sent. If you are a person, please try again in a moment.', 'tad' ),
		'limit'   => __( 'That is a lot of messages in a short time. Please try again later.', 'tad' ),
		'mail'    => __( 'The message could not be sent. Please try again, or use the phone or scheduling link.', 'tad' ),
	);

	return $messages[ $code ] ?? $messages['invalid'];
}

function tad_contact_finish( $ok, $code = '' ) {
	$ajax = ! empty( $_POST['ajax'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $ajax ) {
		if ( $ok ) {
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => tad_contact_error_message( $code ) ), 400 );
	}

	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'tad-sent', 'tad-error' ), $back );
	$back = $ok ? add_query_arg( 'tad-sent', '1', $back ) : add_query_arg( 'tad-error', $code, $back );

	$anchor = isset( $_POST['return_anchor'] ) ? sanitize_html_class( wp_unslash( $_POST['return_anchor'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $anchor ) {
		$back .= '#' . $anchor;
	}

	wp_safe_redirect( $back );
	exit;
}

function tad_handle_contact() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	// Public form: no nonce (it would break on cached pages). Honeypot + timing + rate limit instead.

	if ( ! empty( $_POST['website'] ) ) {
		tad_contact_finish( false, 'spam' );
	}

	$started = isset( $_POST['tad_ts'] ) ? (int) $_POST['tad_ts'] : 0;

	if ( $started && ( time() - $started ) < 3 ) {
		tad_contact_finish( false, 'spam' );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'tad_contact_' . md5( $ip );
	$hit = (int) get_transient( $key );

	if ( $hit >= 5 ) {
		tad_contact_finish( false, 'limit' );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$topic   = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	// phpcs:enable

	if ( strlen( $name ) < 1 || strlen( $name ) > 120 || ! is_email( $email ) || strlen( $message ) < 3 || strlen( $message ) > 8000 ) {
		tad_contact_finish( false, 'invalid' );
	}

	$to      = sanitize_email( tad_get_option( 'contact_recipient', '' ) );
	$to      = $to ? $to : get_option( 'admin_email' );
	$prefix  = tad_get_option( 'contact_subject_prefix', '[' . wp_parse_url( home_url(), PHP_URL_HOST ) . ']' );
	$subject = trim( $prefix . ' ' . $name . ( $topic ? ' — ' . $topic : '' ) );
	$subject = str_replace( array( "\r", "\n" ), ' ', $subject );

	$body  = "Name: {$name}\nEmail: {$email}\n";
	$body .= $topic ? "Topic: {$topic}\n" : '';
	$body .= "\n{$message}\n\n--\nSent from " . home_url( '/' ) . "\n";

	$safe_name = trim( preg_replace( '/[^\p{L}\p{N} \'.-]/u', '', $name ) );
	$headers   = array( 'Reply-To: ' . ( $safe_name ? $safe_name . ' <' . $email . '>' : $email ) );

	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	if ( ! wp_mail( $to, $subject, $body, $headers ) ) {
		tad_contact_finish( false, 'mail' );
	}

	tad_contact_finish( true );
}
add_action( 'admin_post_nopriv_tad_contact', 'tad_handle_contact' );
add_action( 'admin_post_tad_contact', 'tad_handle_contact' );
