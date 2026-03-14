<?php
/**
 * Handles contact form submissions.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class SubmissionHandler {
    const MIN_SUBMIT_SECONDS = 3;

    public static function register() {
        add_action( 'admin_post_scb_submit', array( __CLASS__, 'handle' ) );
        add_action( 'admin_post_nopriv_scb_submit', array( __CLASS__, 'handle' ) );
        add_action( 'admin_post_scb_export_submissions', array( __CLASS__, 'export_csv' ) );
        add_action( 'admin_post_scb_clear_submissions', array( __CLASS__, 'clear_submissions' ) );
    }

    public static function handle() {
        $redirect = self::get_redirect_target();

        // Core CSRF protection.
        if ( ! isset( $_POST['scb_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scb_nonce'] ) ), 'scb_submit' ) ) {
            self::redirect_with_status( $redirect, 'error', array( 'general' ) );
        }

        // Basic spam defenses: honeypot + minimum time.
        $honeypot = isset( $_POST['scb_hp'] ) ? sanitize_text_field( wp_unslash( $_POST['scb_hp'] ) ) : '';
        if ( ! empty( $honeypot ) ) {
            self::redirect_with_status( $redirect, 'error', array( 'general' ) );
        }

        $timestamp = isset( $_POST['scb_ts'] ) ? intval( $_POST['scb_ts'] ) : 0;
        if ( $timestamp <= 0 || ( time() - $timestamp ) < self::MIN_SUBMIT_SECONDS ) {
            self::redirect_with_status( $redirect, 'error', array( 'general' ) );
        }

        // Collect raw input for validation.
        $raw_name    = isset( $_POST['scb_name'] ) ? wp_unslash( $_POST['scb_name'] ) : '';
        $raw_email   = isset( $_POST['scb_email'] ) ? wp_unslash( $_POST['scb_email'] ) : '';
        $raw_subject = isset( $_POST['scb_subject'] ) ? wp_unslash( $_POST['scb_subject'] ) : '';
        $raw_message = isset( $_POST['scb_message'] ) ? wp_unslash( $_POST['scb_message'] ) : '';
        $raw_source  = isset( $_POST['scb_source'] ) ? wp_unslash( $_POST['scb_source'] ) : '';

        $name    = self::sanitize_plain_text( $raw_name, 100 );
        $email   = sanitize_email( $raw_email );
        $subject = self::sanitize_plain_text( $raw_subject, 150 );
        $message = self::sanitize_message( $raw_message, 2000 );
        $source  = esc_url_raw( $raw_source );

        $errors = array();
        if ( empty( $name ) ) {
            $errors[] = 'name_required';
        }
        if ( empty( $email ) ) {
            $errors[] = 'email_required';
        } elseif ( ! is_email( $email ) ) {
            $errors[] = 'email_invalid';
        }
        if ( empty( $message ) ) {
            $errors[] = 'message_required';
        }

        if ( ! empty( $errors ) ) {
            self::redirect_with_status( $redirect, 'error', $errors );
        }

        // Compose a fallback subject if none was provided.
        $email_subject = $subject;
        if ( empty( $email_subject ) ) {
            $email_subject = '[Site Contact] Message from ' . $name;
        }

        $mail_sent = self::send_admin_email( $name, $email, $email_subject, $subject, $message, $source );

        // Optional confirmation email to the visitor.
        if ( $mail_sent && Settings::is_confirmation_enabled() ) {
            self::send_confirmation_email( $name, $email );
        }

        // Optional storage for audit purposes.
        if ( $mail_sent && Settings::is_storage_enabled() ) {
            Storage::insert_submission( array(
                'name'       => $name,
                'email'      => $email,
                'subject'    => $subject,
                'message'    => $message,
                'source_url' => $source,
            ) );
        }

        self::redirect_with_status( $redirect, $mail_sent ? 'success' : 'error' );
    }

    private static function send_admin_email( $name, $email, $mail_subject, $submitted_subject, $message, $source ) {
        $recipient = Settings::get_recipient_email();
        $sender    = Settings::get_sender_name();
        $host      = wp_parse_url( home_url(), PHP_URL_HOST );

        $from_email = $host ? 'no-reply@' . $host : $recipient;
        $headers    = array(
            'From: ' . $sender . ' <' . $from_email . '>',
            'Reply-To: ' . $name . ' <' . $email . '>',
            'Content-Type: text/plain; charset=UTF-8',
        );

        $lines = array(
            'Name: ' . $name,
            'Email: ' . $email,
        );

        if ( ! empty( $submitted_subject ) ) {
            $lines[] = 'Subject: ' . $submitted_subject;
        }

        if ( ! empty( $source ) ) {
            $lines[] = 'Source: ' . $source;
        }

        $lines[] = 'Message:';
        $lines[] = $message;

        $body = implode( "\n", $lines );

        return wp_mail( $recipient, $mail_subject, $body, $headers );
    }

    private static function send_confirmation_email( $name, $email ) {
        $subject_template = Settings::get_confirmation_subject();
        $body_template    = Settings::get_confirmation_body();

        $replacements = array(
            '{name}'      => $name,
            '{site_name}' => get_bloginfo( 'name' ),
        );

        $subject = strtr( $subject_template, $replacements );
        $body    = strtr( $body_template, $replacements );

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

        wp_mail( $email, $subject, $body, $headers );
    }

    private static function sanitize_plain_text( $value, $max_length ) {
        $value = sanitize_text_field( $value );
        if ( strlen( $value ) > $max_length ) {
            $value = substr( $value, 0, $max_length );
        }
        return trim( $value );
    }

    private static function sanitize_message( $value, $max_length ) {
        $value = sanitize_textarea_field( $value );
        if ( strlen( $value ) > $max_length ) {
            $value = substr( $value, 0, $max_length );
        }
        return trim( $value );
    }

    private static function get_redirect_target() {
        $referer = wp_get_referer();
        if ( $referer ) {
            return $referer;
        }
        return home_url( '/' );
    }

    private static function redirect_with_status( $url, $status, $errors = array() ) {
        $url = add_query_arg( 'scb_status', $status, $url );
        if ( ! empty( $errors ) ) {
            $url = add_query_arg( 'scb_errors', implode( ',', $errors ), $url );
        }
        wp_safe_redirect( $url );
        exit;
    }

    public static function export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized user' );
        }

        if ( ! Settings::is_storage_enabled() ) {
            wp_die( 'Storage is disabled.' );
        }

        check_admin_referer( 'scb_export_submissions' );

        $rows = Storage::fetch_submissions();

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="contact_submissions.csv"' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array( 'Name', 'Email', 'Subject', 'Message', 'Source', 'Submitted At' ) );
        foreach ( $rows as $row ) {
            fputcsv( $output, array(
                $row['name'],
                $row['email'],
                $row['subject'],
                $row['message'],
                $row['source_url'],
                $row['created_at'],
            ) );
        }
        fclose( $output );
        exit;
    }

    public static function clear_submissions() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized user' );
        }

        if ( ! Settings::is_storage_enabled() ) {
            wp_die( 'Storage is disabled.' );
        }

        check_admin_referer( 'scb_clear_submissions' );

        Storage::clear_submissions();

        wp_safe_redirect( admin_url( 'admin.php?page=scb-submissions&scb-cleared=1' ) );
        exit;
    }
}
