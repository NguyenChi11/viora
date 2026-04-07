<?php

require get_template_directory() . '/inc/meta-box/page/home/section-banner/index.php';

if (!function_exists('viora_home_group_meta_box_add')) {
    function viora_home_group_meta_box_add($post_type, $post)
    {
        if ($post_type !== 'page' || !($post instanceof WP_Post)) {
            return;
        }

        if (!function_exists('viora_home_banner_is_home_page_id') || !viora_home_banner_is_home_page_id($post->ID)) {
            return;
        }

        add_meta_box(
            'viora_home_group',
            esc_html__('Home Page', 'viora'),
            'viora_home_group_meta_box_render',
            'page',
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'viora_home_group_meta_box_add', 10, 2);

if (!function_exists('viora_home_group_meta_box_render')) {
    function viora_home_group_meta_box_render($post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if (!function_exists('viora_home_banner_is_home_page_id') || !viora_home_banner_is_home_page_id($post->ID)) {
            return;
        }

        echo '<script>window.vioraHomeAdminI18n=' . wp_json_encode(array(
            'selectImage' => __('Select image', 'viora'),
            'useImage' => __('Use image', 'viora'),
            'removeImage' => __('Remove image', 'viora'),
            'noImageSelected' => __('No image selected', 'viora'),
        )) . ';</script>';

        if (function_exists('viora_home_banner_meta_box_content')) {
            viora_home_banner_meta_box_content($post);
        }
    }
}
