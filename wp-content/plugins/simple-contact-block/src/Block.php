<?php
/**
 * Block registration and render callback.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class Block {
    const BLOCK_NAME = 'scb/contact-form';

    public static function register() {
        wp_register_script(
            'scb-block-js',
            plugins_url( 'block.js', SCB_PLUGIN_FILE ),
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-components' ),
            filemtime( SCB_PLUGIN_DIR . '/block.js' )
        );

        wp_register_style(
            'scb-contact-form',
            plugins_url( 'assets/contact-form.css', SCB_PLUGIN_FILE ),
            array(),
            filemtime( SCB_PLUGIN_DIR . '/assets/contact-form.css' )
        );

        register_block_type(
            self::BLOCK_NAME,
            array(
                'editor_script'   => 'scb-block-js',
                'style'           => 'scb-contact-form',
                'editor_style'    => 'scb-contact-form',
                'render_callback' => array( __CLASS__, 'render' ),
                'attributes'      => array(
                    'buttonText' => array(
                        'type'    => 'string',
                        'default' => 'Send Message',
                    ),
                    'showSubject' => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                ),
            )
        );
    }

    public static function render( $attributes ) {
        $button_text = isset( $attributes['buttonText'] ) ? esc_html( $attributes['buttonText'] ) : 'Send Message';
        $show_subject = ! isset( $attributes['showSubject'] ) || (bool) $attributes['showSubject'];

        // Resolve status messaging from the redirect flow.
        $status = isset( $_GET['scb_status'] ) ? sanitize_text_field( wp_unslash( $_GET['scb_status'] ) ) : '';
        $message = '';
        $message_class = '';
        $error_items = array();

        if ( $status === 'success' ) {
            $message = Settings::get_success_message();
            $message_class = 'scb-status scb-status--success';
        } elseif ( $status === 'error' ) {
            $message = Settings::get_failure_message();
            $message_class = 'scb-status scb-status--error';

            $errors_param = isset( $_GET['scb_errors'] ) ? sanitize_text_field( wp_unslash( $_GET['scb_errors'] ) ) : '';
            if ( ! empty( $errors_param ) ) {
                $codes = array_filter( array_map( 'trim', explode( ',', $errors_param ) ) );
                $messages = array(
                    'general'          => 'We could not verify your submission. Please try again.',
                    'name_required'    => 'Name is required.',
                    'email_required'   => 'Email is required.',
                    'email_invalid'    => 'Email must be a valid address.',
                    'message_required' => 'Message is required.',
                );

                foreach ( $codes as $code ) {
                    if ( isset( $messages[ $code ] ) ) {
                        $error_items[] = $messages[ $code ];
                    }
                }
            }
        }

        // Capture a source URL to include in admin notifications.
        $source_url = '';
        if ( function_exists( 'get_permalink' ) ) {
            $source_url = get_permalink();
        }
        if ( empty( $source_url ) ) {
            $source_url = wp_get_referer();
        }

        ob_start();
        ?>
        <div class="scb-block">
            <?php if ( ! empty( $message ) ) : ?>
                <div class="<?php echo esc_attr( $message_class ); ?>" role="alert">
                    <p><?php echo esc_html( $message ); ?></p>
                    <?php if ( ! empty( $error_items ) ) : ?>
                        <ul class="scb-errors">
                            <?php foreach ( $error_items as $error_item ) : ?>
                                <li><?php echo esc_html( $error_item ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <form class="scb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="scb_submit">
                <?php wp_nonce_field( 'scb_submit', 'scb_nonce' ); ?>
                <input type="hidden" name="scb_hp" value="">
                <input type="hidden" name="scb_ts" value="<?php echo esc_attr( time() ); ?>">
                <input type="hidden" name="scb_source" value="<?php echo esc_url( $source_url ); ?>">

                <div class="scb-field">
                    <label class="scb-label" for="scb-name">Name</label>
                    <input class="scb-input" type="text" id="scb-name" name="scb_name" required maxlength="100">
                </div>

                <div class="scb-field">
                    <label class="scb-label" for="scb-email">Email</label>
                    <input class="scb-input" type="email" id="scb-email" name="scb_email" required maxlength="254">
                </div>

                <?php if ( $show_subject ) : ?>
                    <div class="scb-field">
                        <label class="scb-label" for="scb-subject">Subject</label>
                        <input class="scb-input" type="text" id="scb-subject" name="scb_subject" maxlength="150">
                    </div>
                <?php endif; ?>

                <div class="scb-field">
                    <label class="scb-label" for="scb-message">Message</label>
                    <textarea class="scb-textarea" id="scb-message" name="scb_message" required rows="6" maxlength="2000"></textarea>
                </div>

                <div class="scb-actions">
                    <button class="scb-button" type="submit"><?php echo esc_html( $button_text ); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
