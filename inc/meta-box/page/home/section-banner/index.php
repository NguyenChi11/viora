<?php

if (!function_exists('viora_home_banner_meta_box_content')) {
    function viora_home_banner_meta_box_content($post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        $state = function_exists('viora_home_banner_get_data_for_front')
            ? viora_home_banner_get_data_for_front($post->ID)
            : array();

        $enabled = isset($state['enabled']) ? absint($state['enabled']) : null;
        $banner_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

        if ($enabled === null) {
            $enabled_meta = get_post_meta($post->ID, 'viora_home_banner_enabled', true);
            $enabled = $enabled_meta === '' ? 1 : absint($enabled_meta);
        }

        if (empty($banner_data)) {
            $meta_data = get_post_meta($post->ID, 'viora_home_banner_data', true);
            if (is_array($meta_data) && !empty($meta_data)) {
                $banner_data = $meta_data;
            }
        }

        if (empty($banner_data)) {
            $mod_data = get_theme_mod('viora_home_banner_data', array());
            if (is_array($mod_data) && !empty($mod_data)) {
                $banner_data = $mod_data;
            }
        }

        if (function_exists('viora_home_banner_sanitize_data')) {
            $banner_data = viora_home_banner_sanitize_data($banner_data);
        }

        $i18n = function_exists('viora_home_get_inline_i18n_data')
            ? viora_home_get_inline_i18n_data()
            : array();

        wp_enqueue_media();
        wp_enqueue_style(
            'viora-home-banner-meta-style',
            get_theme_file_uri('/template/meta-box/page/home/section-banner/style.css'),
            array(),
            null
        );
        wp_enqueue_script(
            'viora-home-banner-meta-script',
            get_theme_file_uri('/template/meta-box/page/home/section-banner/script.js'),
            array(),
            null,
            true
        );

        wp_add_inline_script(
            'viora-home-banner-meta-script',
            'window.vioraHomeBannerMetaData=' . wp_json_encode(array(
                'enabled' => $enabled,
                'data' => $banner_data,
                'i18n' => $i18n,
            )) . ';',
            'before'
        );

        include get_theme_file_path('/template/meta-box/page/home/section-banner/index.php');
    }
}

if (!function_exists('viora_save_home_banner_meta')) {
    function viora_save_home_banner_meta($post_id)
    {
        if (!isset($_POST['viora_home_banner_meta_nonce']) || !wp_verify_nonce($_POST['viora_home_banner_meta_nonce'], 'viora_home_banner_meta_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!function_exists('viora_home_banner_is_home_page_id') || !viora_home_banner_is_home_page_id($post_id)) {
            return;
        }

        $enabled = isset($_POST['viora_home_banner_enabled']) ? 1 : 0;
        $raw_json = isset($_POST['viora_home_banner_data_json']) ? wp_unslash($_POST['viora_home_banner_data_json']) : '';
        $data = json_decode($raw_json, true);
        if (!is_array($data)) {
            $data = array();
        }

        $data = function_exists('viora_home_banner_sanitize_data') ? viora_home_banner_sanitize_data($data) : $data;
        $data['enabled'] = ($enabled === 1);

        if (function_exists('viora_home_banner_sync_storage')) {
            viora_home_banner_sync_storage($enabled, $data, $post_id);
            return;
        }

        update_post_meta($post_id, 'viora_home_banner_data', $data);
        update_post_meta($post_id, 'viora_home_banner_enabled', $enabled);
        set_theme_mod('viora_home_banner_data', $data);
        set_theme_mod('viora_home_banner_enabled', $enabled);
    }
}
add_action('save_post_page', 'viora_save_home_banner_meta');
