<?php
/**
 * Submission storage helpers.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class Storage {
    const TABLE_VERSION = '1.0.0';
    const OPTION_TABLE_VERSION = 'scb_table_version';

    public static function maybe_install_enabled() {
        if ( get_option( 'scb_store_submissions', '0' ) !== '1' ) {
            return;
        }

        if ( get_option( self::OPTION_TABLE_VERSION ) === self::TABLE_VERSION ) {
            return;
        }

        self::install();
    }

    public static function install() {
        global $wpdb;

        // Schema setup for optional submission storage.
        $table_name = self::table_name();
        $charset    = $wpdb->get_charset_collate();

        $schema = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(254) NOT NULL,
            subject varchar(150) NULL,
            message text NOT NULL,
            source_url text NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $schema );

        update_option( self::OPTION_TABLE_VERSION, self::TABLE_VERSION );
    }

    public static function insert_submission( $data ) {
        global $wpdb;

        $table_name = self::table_name();

        // Persist the submission in the custom table.
        $wpdb->insert(
            $table_name,
            array(
                'name'       => $data['name'],
                'email'      => $data['email'],
                'subject'    => $data['subject'],
                'message'    => $data['message'],
                'source_url' => $data['source_url'],
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%s' )
        );
    }

    public static function fetch_submissions( $limit = 200 ) {
        global $wpdb;

        // Admin list fetch with a conservative limit.
        $table_name = self::table_name();
        $limit      = absint( $limit );

        $query = $wpdb->prepare(
            "SELECT id, name, email, subject, message, source_url, created_at FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        );

        return $wpdb->get_results( $query, ARRAY_A );
    }

    public static function clear_submissions() {
        global $wpdb;

        $table_name = self::table_name();

        $wpdb->query( "TRUNCATE TABLE {$table_name}" );
    }

    public static function table_name() {
        global $wpdb;

        return $wpdb->prefix . 'scb_submissions';
    }
}
