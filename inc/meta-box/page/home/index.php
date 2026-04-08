<?php

require get_template_directory() . '/inc/meta-box/page/home/section-banner/index.php';
require get_template_directory() . '/inc/meta-box/page/home/section-services/index.php';
require get_template_directory() . '/inc/meta-box/page/home/section-portfolio/index.php';

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

        $tabs_style_path = get_theme_file_path('/template/meta-box/page/home/section-tabs/style.css');
        if (file_exists($tabs_style_path)) {
            wp_enqueue_style(
                'viora-home-meta-tabs-style',
                get_theme_file_uri('/template/meta-box/page/home/section-tabs/style.css'),
                array(),
                filemtime($tabs_style_path)
            );
        }

        $tabs_script_path = get_theme_file_path('/template/meta-box/page/home/section-tabs/script.js');
        if (file_exists($tabs_script_path)) {
            wp_enqueue_script(
                'viora-home-meta-tabs-script',
                get_theme_file_uri('/template/meta-box/page/home/section-tabs/script.js'),
                array(),
                filemtime($tabs_script_path),
                true
            );
        }

        echo '<div class="viora-home-admin-tabs" role="tablist" aria-label="' . esc_attr__('Home sections', 'viora') . '">';
        echo '<button type="button" class="button viora-home-admin-tab is-active" data-target="viora-home-panel-banner" role="tab" aria-selected="true">' . esc_html__('Banner', 'viora') . '</button>';
        echo '<button type="button" class="button viora-home-admin-tab" data-target="viora-home-panel-services" role="tab" aria-selected="false">' . esc_html__('Services', 'viora') . '</button>';
        echo '<button type="button" class="button viora-home-admin-tab" data-target="viora-home-panel-portfolio" role="tab" aria-selected="false">' . esc_html__('Portfolio', 'viora') . '</button>';
        echo '</div>';

        echo '<div class="viora-home-admin-panels">';
        echo '<div id="viora-home-panel-banner" class="viora-home-admin-panel is-active" role="tabpanel">';

        if (function_exists('viora_home_banner_meta_box_content')) {
            viora_home_banner_meta_box_content($post);
        }

        echo '</div>';
        echo '<div id="viora-home-panel-services" class="viora-home-admin-panel" role="tabpanel" hidden>';

        if (function_exists('viora_home_services_meta_box_content')) {
            viora_home_services_meta_box_content($post);
        }

        echo '</div>';
        echo '<div id="viora-home-panel-portfolio" class="viora-home-admin-panel" role="tabpanel" hidden>';

        if (function_exists('viora_home_portfolio_meta_box_content')) {
            viora_home_portfolio_meta_box_content($post);
        }

        echo '</div>';
        echo '</div>';
    }
}
