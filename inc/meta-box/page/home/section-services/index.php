<?php

if (!function_exists('viora_home_services_meta_box_content')) {
    function viora_home_services_meta_box_content($post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        $state = function_exists('viora_home_services_get_data_for_front')
            ? viora_home_services_get_data_for_front($post->ID)
            : array();

        $enabled = isset($state['enabled']) ? absint($state['enabled']) : null;
        $services_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

        if ($enabled === null) {
            $enabled_meta = get_post_meta($post->ID, 'viora_home_services_enabled', true);
            $enabled = $enabled_meta === '' ? 1 : absint($enabled_meta);
        }

        if (empty($services_data)) {
            $meta_data = get_post_meta($post->ID, 'viora_home_services_data', true);
            if (is_array($meta_data) && !empty($meta_data)) {
                $services_data = $meta_data;
            }
        }

        if (empty($services_data)) {
            $mod_data = get_theme_mod('viora_home_services_data', array());
            if (is_array($mod_data) && !empty($mod_data)) {
                $services_data = $mod_data;
            }
        }

        if (function_exists('viora_home_services_sanitize_data')) {
            $services_data = viora_home_services_sanitize_data($services_data);
        }

        $i18n = function_exists('viora_home_get_inline_i18n_data')
            ? viora_home_get_inline_i18n_data()
            : array();

        wp_enqueue_media();
        wp_enqueue_style(
            'viora-home-services-meta-style',
            get_theme_file_uri('/template/meta-box/page/home/section-services/style.css'),
            array(),
            null
        );
        wp_enqueue_script(
            'viora-home-services-meta-script',
            get_theme_file_uri('/template/meta-box/page/home/section-services/script.js'),
            array(),
            null,
            true
        );

        wp_add_inline_script(
            'viora-home-services-meta-script',
            'window.vioraHomeServicesMetaData=' . wp_json_encode(array(
                'enabled' => $enabled,
                'data' => $services_data,
                'i18n' => $i18n,
            )) . ';',
            'before'
        );

        include get_theme_file_path('/template/meta-box/page/home/section-services/index.php');
    }
}

if (!function_exists('viora_save_home_services_meta')) {
    function viora_save_home_services_meta($post_id)
    {
        if (!isset($_POST['viora_home_services_meta_nonce']) || !wp_verify_nonce($_POST['viora_home_services_meta_nonce'], 'viora_home_services_meta_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!function_exists('viora_home_services_is_home_page_id') || !viora_home_services_is_home_page_id($post_id)) {
            return;
        }

        $enabled = isset($_POST['viora_home_services_enabled']) ? 1 : 0;
        $raw_json = isset($_POST['viora_home_services_data_json']) ? wp_unslash($_POST['viora_home_services_data_json']) : '';
        $data = json_decode($raw_json, true);
        if (!is_array($data)) {
            $data = array();
        }

        $data = function_exists('viora_home_services_sanitize_data') ? viora_home_services_sanitize_data($data) : $data;
        $data['enabled'] = ($enabled === 1);

        if (function_exists('viora_home_services_sync_storage')) {
            viora_home_services_sync_storage($enabled, $data, $post_id);
            return;
        }

        update_post_meta($post_id, 'viora_home_services_data', $data);
        update_post_meta($post_id, 'viora_home_services_enabled', $enabled);
        set_theme_mod('viora_home_services_data', $data);
        set_theme_mod('viora_home_services_enabled', $enabled);
    }
}
add_action('save_post_page', 'viora_save_home_services_meta');
