<?php

if (!function_exists('viora_find_page_by_templates_or_slugs')) {
    function viora_find_page_by_templates_or_slugs($templates, $slugs)
    {
        $templates = is_array($templates) ? $templates : array();
        $slugs = is_array($slugs) ? $slugs : array();

        foreach ($templates as $template) {
            if (!is_string($template) || $template === '') {
                continue;
            }

            $pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => $template,
                'number' => 1,
            ));
            if (!empty($pages)) {
                return (int) $pages[0]->ID;
            }
        }

        foreach ($slugs as $slug) {
            if (!is_string($slug) || $slug === '') {
                continue;
            }

            $page = get_page_by_path($slug, OBJECT, 'page');
            if ($page) {
                return (int) $page->ID;
            }
        }

        return 0;
    }
}

if (!function_exists('viora_home_banner_normalize_input')) {
    function viora_home_banner_normalize_input($value)
    {
        if (is_string($value)) {
            $decoded = json_decode(wp_unslash($value), true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_object($value)) {
            $decoded_object = json_decode(wp_json_encode($value), true);
            $value = is_array($decoded_object) ? $decoded_object : array();
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = viora_home_banner_normalize_input($item);
            }
        }

        return $value;
    }
}

if (!function_exists('viora_home_banner_sanitize_data')) {
    function viora_home_banner_sanitize_data($value)
    {
        if (function_exists('viora_home_banner_normalize_input')) {
            $value = viora_home_banner_normalize_input($value);
        }

        if (!is_array($value)) {
            $value = array();
        }

        $pick = static function ($arr, $keys, $default = '') {
            if (!is_array($arr)) {
                return $default;
            }
            foreach ($keys as $k) {
                if (isset($arr[$k]) && is_string($arr[$k]) && trim($arr[$k]) !== '') {
                    return $arr[$k];
                }
            }
            return $default;
        };

        $to_int = static function ($v, $default = 0) {
            if (is_numeric($v)) {
                return (int) $v;
            }
            return (int) $default;
        };

        $eyebrow = isset($value['eyebrow']) && is_array($value['eyebrow']) ? $value['eyebrow'] : array();
        $title = isset($value['title']) && is_array($value['title']) ? $value['title'] : array();
        $actions = isset($value['actions']) && is_array($value['actions']) ? $value['actions'] : array();
        $primary = isset($actions['primary']) && is_array($actions['primary']) ? $actions['primary'] : array();
        $secondary = isset($actions['secondary']) && is_array($actions['secondary']) ? $actions['secondary'] : array();
        $trust = isset($value['trust']) && is_array($value['trust']) ? $value['trust'] : array();
        $visual = isset($value['visual']) && is_array($value['visual']) ? $value['visual'] : array();

        $avatars = isset($trust['avatars']) ? $trust['avatars'] : array();
        if (is_string($avatars)) {
            $avatars = explode(',', $avatars);
        }
        if (!is_array($avatars)) {
            $avatars = array();
        }
        $avatars = array_values(array_filter(array_map('sanitize_text_field', $avatars), static function ($item) {
            return $item !== '';
        }));

        $stats_raw = isset($visual['stats']) && is_array($visual['stats']) ? $visual['stats'] : array();
        $stats = array();
        foreach ($stats_raw as $idx => $stat) {
            if (!is_array($stat)) {
                continue;
            }
            $position = isset($stat['position']) ? strtolower((string) $stat['position']) : '';
            if ($position !== 'top' && $position !== 'bottom') {
                $position = ($idx === 1) ? 'bottom' : 'top';
            }

            $item = array(
                'icon_id' => absint(isset($stat['icon_id']) ? $stat['icon_id'] : 0),
                'icon_url' => esc_url_raw($pick($stat, array('icon_url', 'icon'), '')),
                'value' => sanitize_text_field(isset($stat['value']) ? $stat['value'] : ''),
                'label' => sanitize_text_field(isset($stat['label']) ? $stat['label'] : ''),
                'tone' => sanitize_key(isset($stat['tone']) ? $stat['tone'] : ''),
                'position' => $position,
                'depth' => $to_int(isset($stat['depth']) ? $stat['depth'] : 0, 0),
            );
            $item['icon'] = $item['icon_url'];

            if ($item['icon_url'] !== '' || $item['value'] !== '' || $item['label'] !== '') {
                $stats[] = $item;
            }
        }

        $sanitized = array(
            'enabled' => isset($value['enabled']) ? (bool) $value['enabled'] : true,
            'eyebrow' => array(
                'icon_id' => absint(isset($eyebrow['icon_id']) ? $eyebrow['icon_id'] : 0),
                'icon_url' => esc_url_raw($pick($eyebrow, array('icon_url', 'icon'), '')),
                'text' => sanitize_text_field(isset($eyebrow['text']) ? $eyebrow['text'] : ''),
            ),
            'title' => array(
                'line1' => sanitize_text_field(isset($title['line1']) ? $title['line1'] : ''),
                'highlight' => sanitize_text_field(isset($title['highlight']) ? $title['highlight'] : ''),
                'line2' => sanitize_text_field(isset($title['line2']) ? $title['line2'] : ''),
            ),
            'description' => sanitize_textarea_field(isset($value['description']) ? $value['description'] : ''),
            'actions' => array(
                'primary' => array(
                    'text' => sanitize_text_field(isset($primary['text']) ? $primary['text'] : ''),
                    'url' => esc_url_raw(isset($primary['url']) ? $primary['url'] : ''),
                    'icon_id' => absint(isset($primary['icon_id']) ? $primary['icon_id'] : 0),
                    'icon_url' => esc_url_raw($pick($primary, array('icon_url', 'icon'), '')),
                ),
                'secondary' => array(
                    'text' => sanitize_text_field(isset($secondary['text']) ? $secondary['text'] : ''),
                    'url' => esc_url_raw(isset($secondary['url']) ? $secondary['url'] : ''),
                ),
            ),
            'trust' => array(
                'avatars' => $avatars,
                'text' => sanitize_text_field(isset($trust['text']) ? $trust['text'] : ''),
            ),
            'visual' => array(
                'mainImage_id' => absint(isset($visual['mainImage_id']) ? $visual['mainImage_id'] : 0),
                'mainImage_url' => esc_url_raw($pick($visual, array('mainImage_url', 'mainImage'), '')),
                'previewImage_id' => absint(isset($visual['previewImage_id']) ? $visual['previewImage_id'] : 0),
                'previewImage_url' => esc_url_raw($pick($visual, array('previewImage_url', 'previewImage'), '')),
                'stats' => $stats,
                'parallaxDepth' => $to_int(isset($visual['parallaxDepth']) ? $visual['parallaxDepth'] : 18, 18),
                'phoneDepth' => $to_int(isset($visual['phoneDepth']) ? $visual['phoneDepth'] : 26, 26),
            ),
            'scrollHint' => sanitize_text_field(isset($value['scrollHint']) ? $value['scrollHint'] : ''),
        );

        $sanitized['eyebrow']['icon'] = $sanitized['eyebrow']['icon_url'];
        $sanitized['actions']['primary']['icon'] = $sanitized['actions']['primary']['icon_url'];
        $sanitized['visual']['mainImage'] = $sanitized['visual']['mainImage_url'];
        $sanitized['visual']['previewImage'] = $sanitized['visual']['previewImage_url'];
        foreach ($sanitized['visual']['stats'] as $index => $stat_item) {
            $sanitized['visual']['stats'][$index]['icon'] = isset($stat_item['icon_url']) ? $stat_item['icon_url'] : '';
        }

        return $sanitized;
    }
}

if (!function_exists('viora_home_banner_deep_merge')) {
    function viora_home_banner_deep_merge($base, $patch)
    {
        $base = is_array($base) ? $base : array();
        $patch = is_array($patch) ? $patch : array();

        $is_list = static function ($arr) {
            if (!is_array($arr) || $arr === array()) {
                return true;
            }

            return array_keys($arr) === range(0, count($arr) - 1);
        };

        foreach ($patch as $key => $patch_value) {
            if (is_array($patch_value)) {
                if ($is_list($patch_value)) {
                    $base[$key] = $patch_value;
                    continue;
                }

                $base_value = isset($base[$key]) && is_array($base[$key]) ? $base[$key] : array();
                $base[$key] = viora_home_banner_deep_merge($base_value, $patch_value);
                continue;
            }

            $base[$key] = $patch_value;
        }

        return $base;
    }
}

if (!function_exists('viora_home_banner_merge_with_current')) {
    function viora_home_banner_merge_with_current($incoming, $page_id = 0)
    {
        if (function_exists('viora_home_banner_normalize_input')) {
            $incoming = viora_home_banner_normalize_input($incoming);
        }

        $incoming = is_array($incoming) ? $incoming : array();

        $current = get_theme_mod('viora_home_banner_data', array());
        if (function_exists('viora_home_banner_normalize_input')) {
            $current = viora_home_banner_normalize_input($current);
        }
        if (!is_array($current) || empty($current)) {
            $state = function_exists('viora_home_banner_get_data_for_front')
                ? viora_home_banner_get_data_for_front($page_id)
                : array('data' => array());
            $current = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();
        }

        if (!is_array($current)) {
            $current = array();
        }

        return viora_home_banner_deep_merge($current, $incoming);
    }
}

if (!function_exists('viora_home_banner_get_default_data')) {
    function viora_home_banner_get_default_data()
    {
        return viora_home_banner_sanitize_data(array());
    }
}

if (!function_exists('viora_home_banner_is_home_page_id')) {
    function viora_home_banner_is_home_page_id($post_id)
    {
        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return false;
        }

        $template = get_page_template_slug($post_id);
        $front_id = (int) get_option('page_on_front');
        return ($template === 'home-page.php' || $front_id === $post_id);
    }
}

if (!function_exists('viora_home_banner_find_home_page_id')) {
    function viora_home_banner_find_home_page_id()
    {
        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_banner_is_home_page_id($front_id)) {
            return $front_id;
        }

        if (function_exists('viora_find_page_by_templates_or_slugs')) {
            return viora_find_page_by_templates_or_slugs(
                array('home-page.php'),
                array('home', 'trang-chu', 'homepage')
            );
        }

        return 0;
    }
}

if (!function_exists('viora_home_banner_collect_target_page_ids')) {
    function viora_home_banner_collect_target_page_ids($preferred_page_id = 0)
    {
        $targets = array();
        $preferred_page_id = absint($preferred_page_id);
        if ($preferred_page_id > 0 && viora_home_banner_is_home_page_id($preferred_page_id)) {
            $targets[] = $preferred_page_id;
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_banner_is_home_page_id($front_id)) {
            $targets[] = $front_id;
        }

        $home_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'home-page.php',
            'number' => -1,
            'fields' => 'ids',
        ));
        if (is_array($home_pages)) {
            foreach ($home_pages as $page_id) {
                $targets[] = absint($page_id);
            }
        }

        $targets = array_unique(array_filter(array_map('absint', $targets)));
        if (empty($targets)) {
            $fallback_id = viora_home_banner_find_home_page_id();
            if ($fallback_id > 0) {
                $targets[] = $fallback_id;
            }
        }

        return array_unique(array_filter(array_map('absint', $targets)));
    }
}

if (!function_exists('viora_home_banner_sync_storage')) {
    function viora_home_banner_sync_storage($enabled, $data, $preferred_page_id = 0)
    {
        $enabled = absint($enabled) === 1 ? 1 : 0;
        $data = viora_home_banner_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        $targets = viora_home_banner_collect_target_page_ids($preferred_page_id);
        $preferred_page_id = absint($preferred_page_id);
        if (empty($targets) && $preferred_page_id > 0) {
            $targets[] = $preferred_page_id;
        }

        foreach ($targets as $target_id) {
            update_post_meta($target_id, 'viora_home_banner_data', $data);
            update_post_meta($target_id, 'viora_home_banner_enabled', $enabled);
            clean_post_cache($target_id);
        }

        set_theme_mod('viora_home_banner_data', $data);
        set_theme_mod('viora_home_banner_enabled', $enabled);

        return $targets;
    }
}

if (!function_exists('viora_home_banner_get_data_for_front')) {
    function viora_home_banner_get_data_for_front($page_id = 0)
    {
        $page_id = absint($page_id);
        if ($page_id <= 0) {
            $queried_id = get_queried_object_id();
            if ($queried_id > 0) {
                $page_id = (int) $queried_id;
            }
        }
        if ($page_id <= 0) {
            $page_id = viora_home_banner_find_home_page_id();
        }

        $data = array();
        $enabled = null;
        if ($page_id > 0) {
            $meta_data = get_post_meta($page_id, 'viora_home_banner_data', true);
            if (is_array($meta_data)) {
                $data = $meta_data;
            }

            $meta_enabled = get_post_meta($page_id, 'viora_home_banner_enabled', true);
            if ($meta_enabled !== '') {
                $enabled = absint($meta_enabled);
            }
        }

        if (!is_array($data) || empty($data)) {
            $mod_data = get_theme_mod('viora_home_banner_data', array());
            if (function_exists('viora_home_banner_normalize_input')) {
                $mod_data = viora_home_banner_normalize_input($mod_data);
            }
            if (is_array($mod_data) && !empty($mod_data)) {
                $data = $mod_data;
            }
        }

        if ($enabled === null) {
            $enabled = absint(get_theme_mod('viora_home_banner_enabled', isset($data['enabled']) ? (int) $data['enabled'] : 1));
        }

        if (is_customize_preview()) {
            $preview_data = get_theme_mod('viora_home_banner_data', array());
            if (function_exists('viora_home_banner_normalize_input')) {
                $preview_data = viora_home_banner_normalize_input($preview_data);
            }
            if (is_array($preview_data) && !empty($preview_data)) {
                $data = $preview_data;
            }
            $enabled = absint(get_theme_mod('viora_home_banner_enabled', $enabled));
        }

        $data = viora_home_banner_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        return array(
            'page_id' => $page_id,
            'enabled' => $enabled,
            'data' => $data,
        );
    }
}
