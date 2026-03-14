<?php
/**
 * Admin submissions page.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class AdminSubmissions {
    public static function register_admin_page() {
        add_menu_page(
            'Contact Submissions',
            'Contact Submissions',
            'manage_options',
            'scb-submissions',
            array( __CLASS__, 'render' ),
            'dashicons-email',
            80
        );
    }

    public static function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized user' );
        }

        // Respect storage toggle by hiding admin data views when disabled.
        if ( ! Settings::is_storage_enabled() ) {
            echo '<div class="wrap"><h1>Contact Submissions</h1><p>Storage is disabled.</p></div>';
            return;
        }

        $rows = Storage::fetch_submissions();

        echo '<div class="wrap">';
        echo '<h1>Contact Submissions</h1>';

        if ( isset( $_GET['scb-cleared'] ) && sanitize_text_field( wp_unslash( $_GET['scb-cleared'] ) ) === '1' ) {
            echo '<div class="notice notice-success"><p>Submissions cleared.</p></div>';
        }

        // CSV export.
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-bottom:1em;">';
        echo '<input type="hidden" name="action" value="scb_export_submissions">';
        wp_nonce_field( 'scb_export_submissions' );
        echo '<input type="submit" class="button button-primary" value="Download CSV">';
        echo '</form>';

        // Destructive clear action.
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-bottom:1em;">';
        echo '<input type="hidden" name="action" value="scb_clear_submissions">';
        wp_nonce_field( 'scb_clear_submissions' );
        echo '<input type="submit" class="button button-secondary" value="Delete All" onclick="return confirm(\'Are you sure?\')">';
        echo '</form>';

        if ( empty( $rows ) ) {
            echo '<p>No submissions yet.</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Source</th><th>Submitted</th></tr></thead>';
        echo '<tbody>';

        foreach ( $rows as $row ) {
            echo '<tr>';
            echo '<td>' . esc_html( $row['name'] ) . '</td>';
            echo '<td>' . esc_html( $row['email'] ) . '</td>';
            echo '<td>' . esc_html( $row['subject'] ) . '</td>';
            echo '<td>' . esc_html( $row['message'] ) . '</td>';
            echo '<td>' . ( empty( $row['source_url'] ) ? '' : '<a href="' . esc_url( $row['source_url'] ) . '" target="_blank" rel="noopener">View</a>' ) . '</td>';
            echo '<td>' . esc_html( $row['created_at'] ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }
}
