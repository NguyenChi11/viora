<?php

if (!defined('ABSPATH')) {
    exit;
}

function viora_register_portfolio_post_type()
{
    $labels = array(
        'name'                  => __('Portfolios', 'viora'),
        'singular_name'         => __('Portfolio', 'viora'),
        'menu_name'             => __('Portfolio', 'viora'),
        'name_admin_bar'        => __('Portfolio', 'viora'),
        'add_new'               => __('Add New', 'viora'),
        'add_new_item'          => __('Add New Portfolio', 'viora'),
        'new_item'              => __('New Portfolio', 'viora'),
        'edit_item'             => __('Edit Portfolio', 'viora'),
        'view_item'             => __('View Portfolio', 'viora'),
        'all_items'             => __('All Portfolios', 'viora'),
        'search_items'          => __('Search Portfolios', 'viora'),
        'parent_item_colon'     => __('Parent Portfolio:', 'viora'),
        'not_found'             => __('No portfolios found.', 'viora'),
        'not_found_in_trash'    => __('No portfolios found in Trash.', 'viora'),
        'featured_image'        => __('Featured Image', 'viora'),
        'set_featured_image'    => __('Set featured image', 'viora'),
        'remove_featured_image' => __('Remove featured image', 'viora'),
        'use_featured_image'    => __('Use as featured image', 'viora'),
        'archives'              => __('Portfolio Archives', 'viora'),
        'items_list'            => __('Portfolio list', 'viora'),
        'items_list_navigation' => __('Portfolio list navigation', 'viora'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'portfolio'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
        'publicly_queryable' => true,
    );

    register_post_type('portfolio', $args);
}
add_action('init', 'viora_register_portfolio_post_type');
