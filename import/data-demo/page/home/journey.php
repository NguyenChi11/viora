<?php

if (!function_exists('viora_home_journey_attach_media_ids')) {
    function viora_home_journey_attach_media_ids($data)
    {
        if (!is_array($data) || !function_exists('viora_import_image_id')) {
            return $data;
        }

        $layout = isset($data['layout']) && is_array($data['layout']) ? $data['layout'] : array();
        $timeline = isset($layout['timeline']) && is_array($layout['timeline']) ? $layout['timeline'] : array();
        $items = isset($timeline['items']) && is_array($timeline['items']) ? $timeline['items'] : array();
        $processed_items = array();

        foreach ($items as $item) {
            $item = is_array($item) ? $item : array();
            $url = '';
            $url_keys = array('icon_url', 'icon');
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
            $item['icon_url'] = $url;
            $item['icon'] = $url;
            $processed_items[] = $item;
        }

        $timeline['items'] = $processed_items;
        $layout['timeline'] = $timeline;

        $visual = isset($layout['visual']) && is_array($layout['visual']) ? $layout['visual'] : array();
        $rocket_url = '';
        foreach (array('rocketIcon_url', 'rocketIcon') as $key) {
            if (isset($visual[$key]) && is_string($visual[$key]) && trim($visual[$key]) !== '') {
                $rocket_url = trim($visual[$key]);
                break;
            }
        }

        $rocket_id = absint(isset($visual['rocketIcon_id']) ? $visual['rocketIcon_id'] : 0);
        if ($rocket_id <= 0 && $rocket_url !== '') {
            $rocket_id = viora_import_image_id($rocket_url);
        }

        $visual['rocketIcon_id'] = $rocket_id;
        $visual['rocketIcon_url'] = $rocket_url;
        $visual['rocketIcon'] = $rocket_url;

        $layout['visual'] = $visual;
        $data['layout'] = $layout;

        return $data;
    }
}

if (!function_exists('viora_import_home_journey_demo')) {
    function viora_import_home_journey_demo()
    {
        $home_id = function_exists('viora_home_journey_find_home_page_id')
            ? viora_home_journey_find_home_page_id()
            : (function_exists('viora_home_banner_find_home_page_id')
                ? viora_home_banner_find_home_page_id()
                : (int) get_option('page_on_front'));

        if ($home_id <= 0) {
            return;
        }

        $existing = get_post_meta($home_id, 'viora_home_journey_data', true);
        if (is_array($existing) && !empty($existing)) {
            return;
        }

        $data = function_exists('viora_import_parse_js')
            ? viora_import_parse_js('/assets/data/page/home/journey.js', 'homeJourneyData')
            : array();

        if (!is_array($data) || empty($data)) {
            return;
        }

        if (function_exists('viora_home_journey_sanitize_data')) {
            $data = viora_home_journey_sanitize_data($data);
        }

        $data = viora_home_journey_attach_media_ids($data);
        $enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;

        if (function_exists('viora_home_journey_sync_storage')) {
            viora_home_journey_sync_storage($enabled, $data, $home_id);
            return;
        }

        update_post_meta($home_id, 'viora_home_journey_data', $data);
        update_post_meta($home_id, 'viora_home_journey_enabled', $enabled);
        set_theme_mod('viora_home_journey_data', $data);
        set_theme_mod('viora_home_journey_enabled', $enabled);
    }
}
