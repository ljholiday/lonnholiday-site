<?php
/**
 * Uninstall cleanup for SimpleContactBlock.
 *
 * Package: SimpleContactBlock
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'scb_submissions';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

delete_option( 'scb_recipient_email' );
delete_option( 'scb_sender_name' );
delete_option( 'scb_success_message' );
delete_option( 'scb_failure_message' );
delete_option( 'scb_store_submissions' );
delete_option( 'scb_send_confirmation' );
delete_option( 'scb_confirmation_subject' );
delete_option( 'scb_confirmation_body' );
delete_option( 'scb_table_version' );
