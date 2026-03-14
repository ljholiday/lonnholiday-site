<?php
/**
 * Plugin Name: Simple Contact Block
 * Description: A Gutenberg contact form block with validated submissions and optional storage.
 * Version: 1.0.0
 */
/**
 * Plugin bootstrap for SimpleContactBlock.
 *
 * Package: SimpleContactBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SCB_PLUGIN_FILE', __FILE__ );
define( 'SCB_PLUGIN_DIR', __DIR__ );

// PSR-4 style autoloader for the SimpleContactBlock namespace.
spl_autoload_register( function ( $class ) {
    $prefix   = 'SimpleContactBlock\\';
    $base_dir = __DIR__ . '/src/';

    if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, strlen( $prefix ) );
    $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

// Initialize plugin hooks.
SimpleContactBlock\Plugin::init();
