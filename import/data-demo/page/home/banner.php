<?php

if (!function_exists('viora_home_banner_attach_media_ids')) {
    function viora_home_banner_attach_media_ids($data)
    {
        if (!is_array($data) || !function_exists('viora_import_image_id')) {
            return $data;
        }

        $set_media = static function ($node, $id_key, $url_keys) {
            $node = is_array($node) ? $node : array();
            $url = '';
            foreach ($url_keys as $key) {
                if (isset($node[$key]) && is_string($node[$key]) && trim($node[$key]) !== '') {
                    $url = trim($node[$key]);
                    break;
                }
            }

            $id = absint(isset($node[$id_key]) ? $node[$id_key] : 0);
            if ($id <= 0 && $url !== '') {
                $id = viora_import_image_id($url);
            }

            $node[$id_key] = $id;
            if (in_array('icon_url', $url_keys, true)) {
                $node['icon_url'] = $url;
                $node['icon'] = $url;
            }
            if (in_array('mainImage_url', $url_keys, true)) {
                $node['mainImage_url'] = $url;
                $node['mainImage'] = $url;
            }
            if (in_array('previewImage_url', $url_keys, true)) {
                $node['previewImage_url'] = $url;
                $node['previewImage'] = $url;
            }

            return $node;
        };

        $data['eyebrow'] = $set_media(isset($data['eyebrow']) ? $data['eyebrow'] : array(), 'icon_id', array('icon_url', 'icon'));

        $actions = isset($data['actions']) && is_array($data['actions']) ? $data['actions'] : array();
        $primary = isset($actions['primary']) ? $actions['primary'] : array();
        $actions['primary'] = $set_media($primary, 'icon_id', array('icon_url', 'icon'));
        $data['actions'] = $actions;

        $visual = isset($data['visual']) && is_array($data['visual']) ? $data['visual'] : array();
        $visual = $set_media($visual, 'mainImage_id', array('mainImage_url', 'mainImage'));
        $visual = $set_media($visual, 'previewImage_id', array('previewImage_url', 'previewImage'));

        $stats = isset($visual['stats']) && is_array($visual['stats']) ? $visual['stats'] : array();
        $processed_stats = array();
        foreach ($stats as $stat) {
            $processed_stats[] = $set_media(is_array($stat) ? $stat : array(), 'icon_id', array('icon_url', 'icon'));
        }
        $visual['stats'] = $processed_stats;
        $data['visual'] = $visual;

        return $data;
    }
}

if (!function_exists('viora_import_home_banner_demo')) {
    function viora_import_home_banner_demo()
    {
        $home_id = function_exists('viora_home_banner_find_home_page_id')
            ? viora_home_banner_find_home_page_id()
            : (int) get_option('page_on_front');

        if ($home_id <= 0) {
            return;
        }

        $existing = get_post_meta($home_id, 'viora_home_banner_data', true);
        if (is_array($existing) && !empty($existing)) {
            return;
        }

        $data = function_exists('viora_import_parse_js')
            ? viora_import_parse_js('/assets/data/page/home/banner.js', 'homeBannerData')
            : array();
        if (!is_array($data) || empty($data)) {
            return;
        }

        if (function_exists('viora_home_banner_sanitize_data')) {
            $data = viora_home_banner_sanitize_data($data);
        }
        $data = viora_home_banner_attach_media_ids($data);

        $enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;
        if (function_exists('viora_home_banner_sync_storage')) {
            viora_home_banner_sync_storage($enabled, $data, $home_id);
            return;
        }

        update_post_meta($home_id, 'viora_home_banner_data', $data);
        update_post_meta($home_id, 'viora_home_banner_enabled', $enabled);
        set_theme_mod('viora_home_banner_data', $data);
        set_theme_mod('viora_home_banner_enabled', $enabled);
    }
}
