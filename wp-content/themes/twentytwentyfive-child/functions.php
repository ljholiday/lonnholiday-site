<?php

function lonnholiday_child_theme_styles() {
    wp_enqueue_style(
        'twentytwentyfive-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'twentytwentyfive-child-style',
        get_stylesheet_uri(),
        array('twentytwentyfive-style'),
        wp_get_theme()->get('Version')
    );
}

add_action('wp_enqueue_scripts', 'lonnholiday_child_theme_styles');


function lonn_register_art_post_type() {

    $labels = array(
        'name'               => 'Art',
        'singular_name'      => 'Artwork',
        'menu_name'          => 'Art',
        'add_new'            => 'Add Artwork',
        'add_new_item'       => 'Add New Artwork',
        'edit_item'          => 'Edit Artwork',
        'new_item'           => 'New Artwork',
        'view_item'          => 'View Artwork',
        'search_items'       => 'Search Art',
        'not_found'          => 'No artwork found',
        'not_found_in_trash' => 'No artwork found in Trash'
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-art',
        'supports'      => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt'
        ),
        'has_archive'   => true,
        'rewrite'       => array(
            'slug' => 'art'
        ),
        'show_in_rest'  => true
    );

    register_post_type('art', $args);
}

add_action('init', 'lonn_register_art_post_type');
