<?php
/**
 * Plugin settings and admin UI.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class Settings {
    const OPTION_RECIPIENT_EMAIL     = 'scb_recipient_email';
    const OPTION_SENDER_NAME         = 'scb_sender_name';
    const OPTION_SUCCESS_MESSAGE     = 'scb_success_message';
    const OPTION_FAILURE_MESSAGE     = 'scb_failure_message';
    const OPTION_STORE_SUBMISSIONS   = 'scb_store_submissions';
    const OPTION_SEND_CONFIRMATION   = 'scb_send_confirmation';
    const OPTION_CONFIRM_SUBJECT     = 'scb_confirmation_subject';
    const OPTION_CONFIRM_BODY        = 'scb_confirmation_body';

    public static function register() {
        // Core delivery settings.
        register_setting( 'scb_settings', self::OPTION_RECIPIENT_EMAIL, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => get_option( 'admin_email' ),
        ) );

        register_setting( 'scb_settings', self::OPTION_SENDER_NAME, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => get_bloginfo( 'name' ),
        ) );

        // UX feedback messages.
        register_setting( 'scb_settings', self::OPTION_SUCCESS_MESSAGE, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Thanks! Your message has been sent.',
        ) );

        register_setting( 'scb_settings', self::OPTION_FAILURE_MESSAGE, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'Sorry, there was a problem sending your message.',
        ) );

        // Operational toggles.
        register_setting( 'scb_settings', self::OPTION_STORE_SUBMISSIONS, array(
            'type'              => 'string',
            'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
            'default'           => '0',
        ) );

        register_setting( 'scb_settings', self::OPTION_SEND_CONFIRMATION, array(
            'type'              => 'string',
            'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
            'default'           => '0',
        ) );

        // Confirmation email template settings.
        register_setting( 'scb_settings', self::OPTION_CONFIRM_SUBJECT, array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'We received your message',
        ) );

        register_setting( 'scb_settings', self::OPTION_CONFIRM_BODY, array(
            'type'              => 'string',
            'sanitize_callback' => array( __CLASS__, 'sanitize_confirmation_body' ),
            'default'           => "Hi {name},\n\nThanks for reaching out. We have received your message and will get back to you soon.\n\n{site_name}",
        ) );
    }

    public static function register_admin_page() {
        add_options_page(
            'Contact Form Settings',
            'Contact Form',
            'manage_options',
            'scb-settings',
            array( __CLASS__, 'render_settings_page' )
        );
    }

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized user' );
        }

        ?>
        <div class="wrap">
            <h1>Contact Form Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'scb_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="scb_recipient_email">Recipient Email</label></th>
                        <td>
                            <input type="email" id="scb_recipient_email" name="<?php echo esc_attr( self::OPTION_RECIPIENT_EMAIL ); ?>" value="<?php echo esc_attr( self::get_recipient_email() ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scb_sender_name">Sender Name</label></th>
                        <td>
                            <input type="text" id="scb_sender_name" name="<?php echo esc_attr( self::OPTION_SENDER_NAME ); ?>" value="<?php echo esc_attr( self::get_sender_name() ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scb_success_message">Success Message</label></th>
                        <td>
                            <input type="text" id="scb_success_message" name="<?php echo esc_attr( self::OPTION_SUCCESS_MESSAGE ); ?>" value="<?php echo esc_attr( self::get_success_message() ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scb_failure_message">Failure Message</label></th>
                        <td>
                            <input type="text" id="scb_failure_message" name="<?php echo esc_attr( self::OPTION_FAILURE_MESSAGE ); ?>" value="<?php echo esc_attr( self::get_failure_message() ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Store Submissions</th>
                        <td>
                            <label for="scb_store_submissions">
                                <input type="checkbox" id="scb_store_submissions" name="<?php echo esc_attr( self::OPTION_STORE_SUBMISSIONS ); ?>" value="1" <?php checked( self::is_storage_enabled() ); ?>>
                                Enable database storage
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Send Confirmation Email</th>
                        <td>
                            <label for="scb_send_confirmation">
                                <input type="checkbox" id="scb_send_confirmation" name="<?php echo esc_attr( self::OPTION_SEND_CONFIRMATION ); ?>" value="1" <?php checked( self::is_confirmation_enabled() ); ?>>
                                Send a confirmation email to the visitor
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scb_confirmation_subject">Confirmation Subject</label></th>
                        <td>
                            <input type="text" id="scb_confirmation_subject" name="<?php echo esc_attr( self::OPTION_CONFIRM_SUBJECT ); ?>" value="<?php echo esc_attr( self::get_confirmation_subject() ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scb_confirmation_body">Confirmation Body</label></th>
                        <td>
                            <textarea id="scb_confirmation_body" name="<?php echo esc_attr( self::OPTION_CONFIRM_BODY ); ?>" rows="6" class="large-text code"><?php echo esc_textarea( self::get_confirmation_body() ); ?></textarea>
                            <p class="description">Available placeholders: {name}, {site_name}</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function sanitize_checkbox( $value ) {
        return $value === '1' ? '1' : '0';
    }

    public static function sanitize_confirmation_body( $value ) {
        return sanitize_textarea_field( $value );
    }

    public static function get_recipient_email() {
        $email = get_option( self::OPTION_RECIPIENT_EMAIL, get_option( 'admin_email' ) );
        return $email ? $email : get_option( 'admin_email' );
    }

    public static function get_sender_name() {
        $name = get_option( self::OPTION_SENDER_NAME, get_bloginfo( 'name' ) );
        return $name ? $name : get_bloginfo( 'name' );
    }

    public static function get_success_message() {
        return get_option( self::OPTION_SUCCESS_MESSAGE, 'Thanks! Your message has been sent.' );
    }

    public static function get_failure_message() {
        return get_option( self::OPTION_FAILURE_MESSAGE, 'Sorry, there was a problem sending your message.' );
    }

    public static function is_storage_enabled() {
        return get_option( self::OPTION_STORE_SUBMISSIONS, '0' ) === '1';
    }

    public static function is_confirmation_enabled() {
        return get_option( self::OPTION_SEND_CONFIRMATION, '0' ) === '1';
    }

    public static function get_confirmation_subject() {
        return get_option( self::OPTION_CONFIRM_SUBJECT, 'We received your message' );
    }

    public static function get_confirmation_body() {
        return get_option( self::OPTION_CONFIRM_BODY, "Hi {name},\n\nThanks for reaching out. We have received your message and will get back to you soon.\n\n{site_name}" );
    }
}
