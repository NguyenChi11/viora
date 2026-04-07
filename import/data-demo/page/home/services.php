<?php

if (!function_exists('viora_home_services_attach_media_ids')) {
    function viora_home_services_attach_media_ids($data)
    {
        if (!is_array($data) || !function_exists('viora_import_image_id')) {
            return $data;
        }

        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
        $processed_items = array();

        foreach ($items as $item) {
            $item = is_array($item) ? $item : array();

            $url = '';
            $url_keys = array('iconImage_url', 'iconImage', 'icon_url');
            foreach ($url_keys as $key) {
                if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
                    $url = trim($item[$key]);
                    break;
                }
            }

            $id = absint(isset($item['icon_id']) ? $item['icon_id'] : 0);
            if ($id <= 0 && $url !== '') {
                $id = viora_import_image_id($url);
            }

            $item['icon_id'] = $id;
            $item['iconImage_url'] = $url;
            $item['iconImage'] = $url;

            $processed_items[] = $item;
        }

        $data['items'] = $processed_items;
        return $data;
    }
}

if (!function_exists('viora_import_home_services_demo')) {
    function viora_import_home_services_demo()
    {
        $home_id = function_exists('viora_home_services_find_home_page_id')
            ? viora_home_services_find_home_page_id()
            : (function_exists('viora_home_banner_find_home_page_id')
                ? viora_home_banner_find_home_page_id()
                : (int) get_option('page_on_front'));

        if ($home_id <= 0) {
            return;
        }

        $existing = get_post_meta($home_id, 'viora_home_services_data', true);
        if (is_array($existing) && !empty($existing)) {
            return;
        }

        $raw_items = function_exists('viora_import_parse_js')
            ? viora_import_parse_js('/assets/data/page/home/services.js', 'vioraHomeServicesData')
            : array();

        if (!is_array($raw_items) || empty($raw_items)) {
            return;
        }

        $data = array(
            'enabled' => true,
            'eyebrow' => 'CAPABILITIES',
            'title' => 'Services we offer to grow your business.',
            'items' => $raw_items,
        );

        if (function_exists('viora_home_services_sanitize_data')) {
            $data = viora_home_services_sanitize_data($data);
        }

        $data = viora_home_services_attach_media_ids($data);
        $enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;

        if (function_exists('viora_home_services_sync_storage')) {
            viora_home_services_sync_storage($enabled, $data, $home_id);
            return;
        }

        update_post_meta($home_id, 'viora_home_services_data', $data);
        update_post_meta($home_id, 'viora_home_services_enabled', $enabled);
        set_theme_mod('viora_home_services_data', $data);
        set_theme_mod('viora_home_services_enabled', $enabled);
    }
}
