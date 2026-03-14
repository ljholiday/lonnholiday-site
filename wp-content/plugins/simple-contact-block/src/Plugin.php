<?php
/**
 * Plugin wiring and bootstrap hooks.
 *
 * Package: SimpleContactBlock
 */

namespace SimpleContactBlock;

class Plugin {
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_block' ) );
        add_action( 'init', array( __CLASS__, 'register_settings' ) );
        add_action( 'init', array( __CLASS__, 'register_submission_handlers' ) );
        add_action( 'init', array( __CLASS__, 'maybe_install_storage' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_pages' ) );
    }

    public static function register_block() {
        Block::register();
    }

    public static function register_settings() {
        Settings::register();
    }

    public static function register_submission_handlers() {
        SubmissionHandler::register();
    }

    public static function maybe_install_storage() {
        Storage::maybe_install_enabled();
    }

    public static function register_admin_pages() {
        Settings::register_admin_page();

        if ( Settings::is_storage_enabled() ) {
            AdminSubmissions::register_admin_page();
        }
    }
}
