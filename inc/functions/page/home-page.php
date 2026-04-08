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

if (!function_exists('viora_home_services_normalize_input')) {
    function viora_home_services_normalize_input($value)
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
                $value[$key] = viora_home_services_normalize_input($item);
            }
        }

        return $value;
    }
}

if (!function_exists('viora_home_services_sanitize_data')) {
    function viora_home_services_sanitize_data($value)
    {
        if (function_exists('viora_home_services_normalize_input')) {
            $value = viora_home_services_normalize_input($value);
        }

        if (!is_array($value)) {
            $value = array();
        }

        $is_list = static function ($arr) {
            if (!is_array($arr) || $arr === array()) {
                return true;
            }

            return array_keys($arr) === range(0, count($arr) - 1);
        };

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

        $normalize_features = static function ($features) {
            if (is_string($features)) {
                $features = explode(',', $features);
            }
            if (!is_array($features)) {
                return array();
            }

            $features = array_values(array_filter(array_map('sanitize_text_field', $features), static function ($feature) {
                return $feature !== '';
            }));

            return array_slice($features, 0, 8);
        };

        $raw_items = array();
        if ($is_list($value)) {
            $raw_items = $value;
        } elseif (isset($value['items']) && is_array($value['items'])) {
            $raw_items = $value['items'];
        }

        $items = array();
        foreach ($raw_items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $sanitized_item = array(
                'icon_id' => absint(isset($item['icon_id']) ? $item['icon_id'] : 0),
                'iconImage_url' => esc_url_raw($pick($item, array('iconImage_url', 'iconImage', 'icon_url'), '')),
                'title' => sanitize_text_field(isset($item['title']) ? $item['title'] : ''),
                'description' => sanitize_textarea_field(isset($item['description']) ? $item['description'] : ''),
                'features' => $normalize_features(isset($item['features']) ? $item['features'] : array()),
            );
            $sanitized_item['iconImage'] = $sanitized_item['iconImage_url'];

            if ($sanitized_item['title'] !== '' || $sanitized_item['description'] !== '' || $sanitized_item['iconImage_url'] !== '' || !empty($sanitized_item['features'])) {
                $items[] = $sanitized_item;
            }
        }

        return array(
            'enabled' => isset($value['enabled']) ? (bool) $value['enabled'] : true,
            'eyebrow' => sanitize_text_field(isset($value['eyebrow']) ? $value['eyebrow'] : 'CAPABILITIES'),
            'title' => sanitize_text_field(isset($value['title']) ? $value['title'] : 'Services we offer to grow your business.'),
            'items' => $items,
        );
    }
}

if (!function_exists('viora_home_services_deep_merge')) {
    function viora_home_services_deep_merge($base, $patch)
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
                $base[$key] = viora_home_services_deep_merge($base_value, $patch_value);
                continue;
            }

            $base[$key] = $patch_value;
        }

        return $base;
    }
}

if (!function_exists('viora_home_services_merge_with_current')) {
    function viora_home_services_merge_with_current($incoming, $page_id = 0)
    {
        if (function_exists('viora_home_services_normalize_input')) {
            $incoming = viora_home_services_normalize_input($incoming);
        }

        $incoming = is_array($incoming) ? $incoming : array();

        $current = get_theme_mod('viora_home_services_data', array());
        if (function_exists('viora_home_services_normalize_input')) {
            $current = viora_home_services_normalize_input($current);
        }
        if (!is_array($current) || empty($current)) {
            $state = function_exists('viora_home_services_get_data_for_front')
                ? viora_home_services_get_data_for_front($page_id)
                : array('data' => array());
            $current = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();
        }

        if (!is_array($current)) {
            $current = array();
        }

        return viora_home_services_deep_merge($current, $incoming);
    }
}

if (!function_exists('viora_home_services_get_default_data')) {
    function viora_home_services_get_default_data()
    {
        return viora_home_services_sanitize_data(array());
    }
}

if (!function_exists('viora_home_services_is_home_page_id')) {
    function viora_home_services_is_home_page_id($post_id)
    {
        if (function_exists('viora_home_banner_is_home_page_id')) {
            return viora_home_banner_is_home_page_id($post_id);
        }

        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return false;
        }

        $template = get_page_template_slug($post_id);
        $front_id = (int) get_option('page_on_front');
        return ($template === 'home-page.php' || $front_id === $post_id);
    }
}

if (!function_exists('viora_home_services_find_home_page_id')) {
    function viora_home_services_find_home_page_id()
    {
        if (function_exists('viora_home_banner_find_home_page_id')) {
            return viora_home_banner_find_home_page_id();
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_services_is_home_page_id($front_id)) {
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

if (!function_exists('viora_home_services_collect_target_page_ids')) {
    function viora_home_services_collect_target_page_ids($preferred_page_id = 0)
    {
        if (function_exists('viora_home_banner_collect_target_page_ids')) {
            return viora_home_banner_collect_target_page_ids($preferred_page_id);
        }

        $targets = array();
        $preferred_page_id = absint($preferred_page_id);
        if ($preferred_page_id > 0 && viora_home_services_is_home_page_id($preferred_page_id)) {
            $targets[] = $preferred_page_id;
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_services_is_home_page_id($front_id)) {
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
            $fallback_id = viora_home_services_find_home_page_id();
            if ($fallback_id > 0) {
                $targets[] = $fallback_id;
            }
        }

        return array_unique(array_filter(array_map('absint', $targets)));
    }
}

if (!function_exists('viora_home_services_sync_storage')) {
    function viora_home_services_sync_storage($enabled, $data, $preferred_page_id = 0)
    {
        $enabled = absint($enabled) === 1 ? 1 : 0;
        $data = viora_home_services_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        $targets = viora_home_services_collect_target_page_ids($preferred_page_id);
        $preferred_page_id = absint($preferred_page_id);
        if (empty($targets) && $preferred_page_id > 0) {
            $targets[] = $preferred_page_id;
        }

        foreach ($targets as $target_id) {
            update_post_meta($target_id, 'viora_home_services_data', $data);
            update_post_meta($target_id, 'viora_home_services_enabled', $enabled);
            clean_post_cache($target_id);
        }

        set_theme_mod('viora_home_services_data', $data);
        set_theme_mod('viora_home_services_enabled', $enabled);

        return $targets;
    }
}

if (!function_exists('viora_home_services_get_data_for_front')) {
    function viora_home_services_get_data_for_front($page_id = 0)
    {
        $page_id = absint($page_id);
        if ($page_id <= 0) {
            $queried_id = get_queried_object_id();
            if ($queried_id > 0) {
                $page_id = (int) $queried_id;
            }
        }
        if ($page_id <= 0) {
            $page_id = viora_home_services_find_home_page_id();
        }

        $data = array();
        $enabled = null;
        if ($page_id > 0) {
            $meta_data = get_post_meta($page_id, 'viora_home_services_data', true);
            if (is_array($meta_data)) {
                $data = $meta_data;
            }

            $meta_enabled = get_post_meta($page_id, 'viora_home_services_enabled', true);
            if ($meta_enabled !== '') {
                $enabled = absint($meta_enabled);
            }
        }

        if (!is_array($data) || empty($data)) {
            $mod_data = get_theme_mod('viora_home_services_data', array());
            if (function_exists('viora_home_services_normalize_input')) {
                $mod_data = viora_home_services_normalize_input($mod_data);
            }
            if (is_array($mod_data) && !empty($mod_data)) {
                $data = $mod_data;
            }
        }

        if ((!is_array($data) || empty($data)) && function_exists('viora_import_parse_js')) {
            $demo_items = viora_import_parse_js('/assets/data/page/home/services.js', 'vioraHomeServicesData');
            if (is_array($demo_items) && !empty($demo_items)) {
                $data = array(
                    'enabled' => true,
                    'eyebrow' => 'CAPABILITIES',
                    'title' => 'Services we offer to grow your business.',
                    'items' => $demo_items,
                );
            }
        }

        if ($enabled === null) {
            $enabled = absint(get_theme_mod('viora_home_services_enabled', isset($data['enabled']) ? (int) $data['enabled'] : 1));
        }

        if (is_customize_preview()) {
            $preview_data = get_theme_mod('viora_home_services_data', array());
            if (function_exists('viora_home_services_normalize_input')) {
                $preview_data = viora_home_services_normalize_input($preview_data);
            }
            if (is_array($preview_data) && !empty($preview_data)) {
                $data = $preview_data;
            }
            $enabled = absint(get_theme_mod('viora_home_services_enabled', $enabled));
        }

        $data = viora_home_services_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        return array(
            'page_id' => $page_id,
            'enabled' => $enabled,
            'data' => $data,
        );
    }
}

if (!function_exists('viora_home_portfolio_normalize_input')) {
    function viora_home_portfolio_normalize_input($value)
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
                $value[$key] = viora_home_portfolio_normalize_input($item);
            }
        }

        return $value;
    }
}

if (!function_exists('viora_home_portfolio_sanitize_data')) {
    function viora_home_portfolio_sanitize_data($value)
    {
        if (function_exists('viora_home_portfolio_normalize_input')) {
            $value = viora_home_portfolio_normalize_input($value);
        }

        if (!is_array($value)) {
            $value = array();
        }

        return array(
            'enabled' => isset($value['enabled']) ? (bool) $value['enabled'] : true,
            'title' => sanitize_text_field(isset($value['title']) ? $value['title'] : __('Our Portfolio', 'viora')),
        );
    }
}

if (!function_exists('viora_home_portfolio_deep_merge')) {
    function viora_home_portfolio_deep_merge($base, $patch)
    {
        $base = is_array($base) ? $base : array();
        $patch = is_array($patch) ? $patch : array();

        foreach ($patch as $key => $patch_value) {
            if (is_array($patch_value)) {
                $base_value = isset($base[$key]) && is_array($base[$key]) ? $base[$key] : array();
                $base[$key] = viora_home_portfolio_deep_merge($base_value, $patch_value);
                continue;
            }

            $base[$key] = $patch_value;
        }

        return $base;
    }
}

if (!function_exists('viora_home_portfolio_merge_with_current')) {
    function viora_home_portfolio_merge_with_current($incoming, $page_id = 0)
    {
        if (function_exists('viora_home_portfolio_normalize_input')) {
            $incoming = viora_home_portfolio_normalize_input($incoming);
        }

        $incoming = is_array($incoming) ? $incoming : array();

        $current = get_theme_mod('viora_home_portfolio_data', array());
        if (function_exists('viora_home_portfolio_normalize_input')) {
            $current = viora_home_portfolio_normalize_input($current);
        }

        if (!is_array($current) || empty($current)) {
            $state = function_exists('viora_home_portfolio_get_data_for_front')
                ? viora_home_portfolio_get_data_for_front($page_id)
                : array('data' => array());
            $current = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();
        }

        if (!is_array($current)) {
            $current = array();
        }

        return viora_home_portfolio_deep_merge($current, $incoming);
    }
}

if (!function_exists('viora_home_portfolio_get_default_data')) {
    function viora_home_portfolio_get_default_data()
    {
        return viora_home_portfolio_sanitize_data(array());
    }
}

if (!function_exists('viora_home_portfolio_is_home_page_id')) {
    function viora_home_portfolio_is_home_page_id($post_id)
    {
        if (function_exists('viora_home_banner_is_home_page_id')) {
            return viora_home_banner_is_home_page_id($post_id);
        }

        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return false;
        }

        $template = get_page_template_slug($post_id);
        $front_id = (int) get_option('page_on_front');
        return ($template === 'home-page.php' || $front_id === $post_id);
    }
}

if (!function_exists('viora_home_portfolio_find_home_page_id')) {
    function viora_home_portfolio_find_home_page_id()
    {
        if (function_exists('viora_home_banner_find_home_page_id')) {
            return viora_home_banner_find_home_page_id();
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_portfolio_is_home_page_id($front_id)) {
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

if (!function_exists('viora_home_portfolio_collect_target_page_ids')) {
    function viora_home_portfolio_collect_target_page_ids($preferred_page_id = 0)
    {
        if (function_exists('viora_home_banner_collect_target_page_ids')) {
            return viora_home_banner_collect_target_page_ids($preferred_page_id);
        }

        $targets = array();
        $preferred_page_id = absint($preferred_page_id);
        if ($preferred_page_id > 0 && viora_home_portfolio_is_home_page_id($preferred_page_id)) {
            $targets[] = $preferred_page_id;
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_portfolio_is_home_page_id($front_id)) {
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
            $fallback_id = viora_home_portfolio_find_home_page_id();
            if ($fallback_id > 0) {
                $targets[] = $fallback_id;
            }
        }

        return array_unique(array_filter(array_map('absint', $targets)));
    }
}

if (!function_exists('viora_home_portfolio_sync_storage')) {
    function viora_home_portfolio_sync_storage($enabled, $data, $preferred_page_id = 0)
    {
        $enabled = absint($enabled) === 1 ? 1 : 0;
        $data = viora_home_portfolio_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        $targets = viora_home_portfolio_collect_target_page_ids($preferred_page_id);
        $preferred_page_id = absint($preferred_page_id);
        if (empty($targets) && $preferred_page_id > 0) {
            $targets[] = $preferred_page_id;
        }

        foreach ($targets as $target_id) {
            update_post_meta($target_id, 'viora_home_portfolio_data', $data);
            update_post_meta($target_id, 'viora_home_portfolio_enabled', $enabled);
            clean_post_cache($target_id);
        }

        set_theme_mod('viora_home_portfolio_data', $data);
        set_theme_mod('viora_home_portfolio_enabled', $enabled);

        return $targets;
    }
}

if (!function_exists('viora_home_portfolio_get_data_for_front')) {
    function viora_home_portfolio_get_data_for_front($page_id = 0)
    {
        $page_id = absint($page_id);
        if ($page_id <= 0) {
            $queried_id = get_queried_object_id();
            if ($queried_id > 0) {
                $page_id = (int) $queried_id;
            }
        }
        if ($page_id <= 0) {
            $page_id = viora_home_portfolio_find_home_page_id();
        }

        $data = array();
        $enabled = null;
        if ($page_id > 0) {
            $meta_data = get_post_meta($page_id, 'viora_home_portfolio_data', true);
            if (is_array($meta_data)) {
                $data = $meta_data;
            }

            $meta_enabled = get_post_meta($page_id, 'viora_home_portfolio_enabled', true);
            if ($meta_enabled !== '') {
                $enabled = absint($meta_enabled);
            }
        }

        if (!is_array($data) || empty($data)) {
            $mod_data = get_theme_mod('viora_home_portfolio_data', array());
            if (function_exists('viora_home_portfolio_normalize_input')) {
                $mod_data = viora_home_portfolio_normalize_input($mod_data);
            }
            if (is_array($mod_data) && !empty($mod_data)) {
                $data = $mod_data;
            }
        }

        if ((!is_array($data) || empty($data)) && function_exists('viora_import_parse_js')) {
            $demo_data = viora_import_parse_js('/assets/data/page/home/portfolio.js', 'vioraHomePortfolioData');
            if (is_array($demo_data) && !empty($demo_data)) {
                $data = $demo_data;
            }
        }

        if ($enabled === null) {
            $enabled = absint(get_theme_mod('viora_home_portfolio_enabled', isset($data['enabled']) ? (int) $data['enabled'] : 1));
        }

        if (is_customize_preview()) {
            $preview_data = get_theme_mod('viora_home_portfolio_data', array());
            if (function_exists('viora_home_portfolio_normalize_input')) {
                $preview_data = viora_home_portfolio_normalize_input($preview_data);
            }
            if (is_array($preview_data) && !empty($preview_data)) {
                $data = $preview_data;
            }
            $enabled = absint(get_theme_mod('viora_home_portfolio_enabled', $enabled));
        }

        $data = viora_home_portfolio_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        return array(
            'page_id' => $page_id,
            'enabled' => $enabled,
            'data' => $data,
        );
    }
}

if (!function_exists('viora_home_journey_default_data')) {
    function viora_home_journey_default_data()
    {
        return array(
            'enabled' => true,
            'header' => array(
                'title' => __('Our Journey', 'viora'),
                'cta' => array(
                    'text' => __('Explore about us', 'viora'),
                    'url' => '/about-us',
                ),
            ),
            'layout' => array(
                'timeline' => array(
                    'items' => array(
                        array(
                            'year' => '2018',
                            'title' => __('The Genesis', 'viora'),
                            'description' => __('Founded with a vision to merge art and technology. We started as a small team of 3 dreamers in a tiny studio.', 'viora'),
                            'icon' => '/wp-content/themes/Viora/assets/images/icon/service_1.png',
                            'isActive' => true,
                        ),
                        array(
                            'year' => '2020',
                            'title' => __('Global Expansion', 'viora'),
                            'description' => __('Scaled our operations globally, partnering with Fortune 500 companies and winning our first international design awards.', 'viora'),
                            'icon' => '/wp-content/themes/Viora/assets/images/icon/service_2.png',
                            'isActive' => false,
                        ),
                        array(
                            'year' => '2025',
                            'title' => __('Innovation Hub', 'viora'),
                            'description' => __('Pioneering AI-driven user experiences and sustainable digital ecosystems for the next generation of the web.', 'viora'),
                            'icon' => '/wp-content/themes/Viora/assets/images/icon/service_3.png',
                            'isActive' => false,
                        ),
                    ),
                ),
                'visual' => array(
                    'rocketIcon' => '/wp-content/themes/Viora/assets/images/icon/service_1.png',
                    'rings' => array(
                        'first' => true,
                        'second' => true,
                    ),
                    'flash' => true,
                ),
            ),
        );
    }
}

if (!function_exists('viora_home_journey_normalize_input')) {
    function viora_home_journey_normalize_input($value)
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
                $value[$key] = viora_home_journey_normalize_input($item);
            }
        }

        return $value;
    }
}

if (!function_exists('viora_home_journey_sanitize_data')) {
    function viora_home_journey_sanitize_data($value)
    {
        if (function_exists('viora_home_journey_normalize_input')) {
            $value = viora_home_journey_normalize_input($value);
        }

        if (!is_array($value)) {
            $value = array();
        }

        $defaults = function_exists('viora_home_journey_default_data')
            ? viora_home_journey_default_data()
            : array();

        $is_list = static function ($arr) {
            if (!is_array($arr) || $arr === array()) {
                return true;
            }

            return array_keys($arr) === range(0, count($arr) - 1);
        };

        $pick = static function ($arr, $keys, $default = '') {
            if (!is_array($arr)) {
                return $default;
            }

            foreach ($keys as $key) {
                if (isset($arr[$key]) && is_string($arr[$key]) && trim($arr[$key]) !== '') {
                    return $arr[$key];
                }
            }

            return $default;
        };

        $default_header = isset($defaults['header']) && is_array($defaults['header']) ? $defaults['header'] : array();
        $default_cta = isset($default_header['cta']) && is_array($default_header['cta']) ? $default_header['cta'] : array();
        $default_layout = isset($defaults['layout']) && is_array($defaults['layout']) ? $defaults['layout'] : array();
        $default_timeline = isset($default_layout['timeline']) && is_array($default_layout['timeline']) ? $default_layout['timeline'] : array();
        $default_visual = isset($default_layout['visual']) && is_array($default_layout['visual']) ? $default_layout['visual'] : array();
        $default_items = isset($default_timeline['items']) && is_array($default_timeline['items']) ? $default_timeline['items'] : array();
        $default_rings = isset($default_visual['rings']) && is_array($default_visual['rings']) ? $default_visual['rings'] : array();

        $header = isset($value['header']) && is_array($value['header']) ? $value['header'] : array();
        $header_cta = isset($header['cta']) && is_array($header['cta']) ? $header['cta'] : array();

        $layout = isset($value['layout']) && is_array($value['layout']) ? $value['layout'] : array();
        $timeline = isset($layout['timeline']) && is_array($layout['timeline']) ? $layout['timeline'] : array();
        $visual = isset($layout['visual']) && is_array($layout['visual']) ? $layout['visual'] : array();
        $rings = isset($visual['rings']) && is_array($visual['rings']) ? $visual['rings'] : array();

        $items_raw = array();
        if ($is_list($value)) {
            $items_raw = $value;
        } elseif (isset($timeline['items']) && is_array($timeline['items'])) {
            $items_raw = $timeline['items'];
        }

        $items = array();
        foreach ($items_raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $sanitized_item = array(
                'year' => sanitize_text_field(isset($item['year']) ? $item['year'] : ''),
                'title' => sanitize_text_field(isset($item['title']) ? $item['title'] : ''),
                'description' => sanitize_textarea_field(isset($item['description']) ? $item['description'] : ''),
                'icon_id' => absint(isset($item['icon_id']) ? $item['icon_id'] : 0),
                'icon_url' => esc_url_raw($pick($item, array('icon_url', 'icon'), '')),
                'isActive' => !empty($item['isActive']),
            );
            $sanitized_item['icon'] = $sanitized_item['icon_url'];

            if ($sanitized_item['year'] !== '' || $sanitized_item['title'] !== '' || $sanitized_item['description'] !== '' || $sanitized_item['icon_url'] !== '') {
                $items[] = $sanitized_item;
            }
        }

        if (empty($items) && !empty($default_items)) {
            foreach ($default_items as $default_item) {
                if (!is_array($default_item)) {
                    continue;
                }

                $fallback_item = array(
                    'year' => sanitize_text_field(isset($default_item['year']) ? $default_item['year'] : ''),
                    'title' => sanitize_text_field(isset($default_item['title']) ? $default_item['title'] : ''),
                    'description' => sanitize_textarea_field(isset($default_item['description']) ? $default_item['description'] : ''),
                    'icon_id' => absint(isset($default_item['icon_id']) ? $default_item['icon_id'] : 0),
                    'icon_url' => esc_url_raw($pick($default_item, array('icon_url', 'icon'), '')),
                    'isActive' => !empty($default_item['isActive']),
                );
                $fallback_item['icon'] = $fallback_item['icon_url'];
                $items[] = $fallback_item;
            }
        }

        $active_index = -1;
        foreach ($items as $index => $item) {
            if (!empty($item['isActive'])) {
                $active_index = $index;
                break;
            }
        }
        if ($active_index < 0 && !empty($items)) {
            $active_index = 0;
        }
        foreach ($items as $index => $item) {
            $items[$index]['isActive'] = ($index === $active_index);
        }

        $rocket_icon_url = esc_url_raw($pick($visual, array('rocketIcon_url', 'rocketIcon'), $pick($default_visual, array('rocketIcon_url', 'rocketIcon'), '')));

        $sanitized = array(
            'enabled' => isset($value['enabled']) ? (bool) $value['enabled'] : true,
            'header' => array(
                'title' => sanitize_text_field(isset($header['title']) ? $header['title'] : (isset($default_header['title']) ? $default_header['title'] : '')),
                'cta' => array(
                    'text' => sanitize_text_field(isset($header_cta['text']) ? $header_cta['text'] : (isset($default_cta['text']) ? $default_cta['text'] : '')),
                    'url' => esc_url_raw(isset($header_cta['url']) ? $header_cta['url'] : (isset($default_cta['url']) ? $default_cta['url'] : '')),
                ),
            ),
            'layout' => array(
                'timeline' => array(
                    'items' => array_slice($items, 0, 12),
                ),
                'visual' => array(
                    'rocketIcon_id' => absint(isset($visual['rocketIcon_id']) ? $visual['rocketIcon_id'] : 0),
                    'rocketIcon_url' => $rocket_icon_url,
                    'rings' => array(
                        'first' => isset($rings['first']) ? (bool) $rings['first'] : !empty($default_rings['first']),
                        'second' => isset($rings['second']) ? (bool) $rings['second'] : !empty($default_rings['second']),
                    ),
                    'flash' => isset($visual['flash']) ? (bool) $visual['flash'] : !empty($default_visual['flash']),
                ),
            ),
        );

        $sanitized['layout']['visual']['rocketIcon'] = $sanitized['layout']['visual']['rocketIcon_url'];

        return $sanitized;
    }
}

if (!function_exists('viora_home_journey_deep_merge')) {
    function viora_home_journey_deep_merge($base, $patch)
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
                $base[$key] = viora_home_journey_deep_merge($base_value, $patch_value);
                continue;
            }

            $base[$key] = $patch_value;
        }

        return $base;
    }
}

if (!function_exists('viora_home_journey_merge_with_current')) {
    function viora_home_journey_merge_with_current($incoming, $page_id = 0)
    {
        if (function_exists('viora_home_journey_normalize_input')) {
            $incoming = viora_home_journey_normalize_input($incoming);
        }

        $incoming = is_array($incoming) ? $incoming : array();

        $current = get_theme_mod('viora_home_journey_data', array());
        if (function_exists('viora_home_journey_normalize_input')) {
            $current = viora_home_journey_normalize_input($current);
        }

        if (!is_array($current) || empty($current)) {
            $state = function_exists('viora_home_journey_get_data_for_front')
                ? viora_home_journey_get_data_for_front($page_id)
                : array('data' => array());
            $current = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();
        }

        if (!is_array($current)) {
            $current = array();
        }

        return viora_home_journey_deep_merge($current, $incoming);
    }
}

if (!function_exists('viora_home_journey_get_default_data')) {
    function viora_home_journey_get_default_data()
    {
        $defaults = function_exists('viora_home_journey_default_data')
            ? viora_home_journey_default_data()
            : array();

        return viora_home_journey_sanitize_data($defaults);
    }
}

if (!function_exists('viora_home_journey_is_home_page_id')) {
    function viora_home_journey_is_home_page_id($post_id)
    {
        if (function_exists('viora_home_banner_is_home_page_id')) {
            return viora_home_banner_is_home_page_id($post_id);
        }

        $post_id = absint($post_id);
        if ($post_id <= 0) {
            return false;
        }

        $template = get_page_template_slug($post_id);
        $front_id = (int) get_option('page_on_front');
        return ($template === 'home-page.php' || $front_id === $post_id);
    }
}

if (!function_exists('viora_home_journey_find_home_page_id')) {
    function viora_home_journey_find_home_page_id()
    {
        if (function_exists('viora_home_banner_find_home_page_id')) {
            return viora_home_banner_find_home_page_id();
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_journey_is_home_page_id($front_id)) {
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

if (!function_exists('viora_home_journey_collect_target_page_ids')) {
    function viora_home_journey_collect_target_page_ids($preferred_page_id = 0)
    {
        if (function_exists('viora_home_banner_collect_target_page_ids')) {
            return viora_home_banner_collect_target_page_ids($preferred_page_id);
        }

        $targets = array();
        $preferred_page_id = absint($preferred_page_id);
        if ($preferred_page_id > 0 && viora_home_journey_is_home_page_id($preferred_page_id)) {
            $targets[] = $preferred_page_id;
        }

        $front_id = (int) get_option('page_on_front');
        if ($front_id > 0 && viora_home_journey_is_home_page_id($front_id)) {
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
            $fallback_id = viora_home_journey_find_home_page_id();
            if ($fallback_id > 0) {
                $targets[] = $fallback_id;
            }
        }

        return array_unique(array_filter(array_map('absint', $targets)));
    }
}

if (!function_exists('viora_home_journey_sync_storage')) {
    function viora_home_journey_sync_storage($enabled, $data, $preferred_page_id = 0)
    {
        $enabled = absint($enabled) === 1 ? 1 : 0;
        $data = viora_home_journey_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        $targets = viora_home_journey_collect_target_page_ids($preferred_page_id);
        $preferred_page_id = absint($preferred_page_id);
        if (empty($targets) && $preferred_page_id > 0) {
            $targets[] = $preferred_page_id;
        }

        foreach ($targets as $target_id) {
            update_post_meta($target_id, 'viora_home_journey_data', $data);
            update_post_meta($target_id, 'viora_home_journey_enabled', $enabled);
            clean_post_cache($target_id);
        }

        set_theme_mod('viora_home_journey_data', $data);
        set_theme_mod('viora_home_journey_enabled', $enabled);

        return $targets;
    }
}

if (!function_exists('viora_home_journey_get_data_for_front')) {
    function viora_home_journey_get_data_for_front($page_id = 0)
    {
        $page_id = absint($page_id);
        if ($page_id <= 0) {
            $queried_id = get_queried_object_id();
            if ($queried_id > 0) {
                $page_id = (int) $queried_id;
            }
        }
        if ($page_id <= 0) {
            $page_id = viora_home_journey_find_home_page_id();
        }

        $data = array();
        $enabled = null;
        if ($page_id > 0) {
            $meta_data = get_post_meta($page_id, 'viora_home_journey_data', true);
            if (is_array($meta_data)) {
                $data = $meta_data;
            }

            $meta_enabled = get_post_meta($page_id, 'viora_home_journey_enabled', true);
            if ($meta_enabled !== '') {
                $enabled = absint($meta_enabled);
            }
        }

        if (!is_array($data) || empty($data)) {
            $mod_data = get_theme_mod('viora_home_journey_data', array());
            if (function_exists('viora_home_journey_normalize_input')) {
                $mod_data = viora_home_journey_normalize_input($mod_data);
            }
            if (is_array($mod_data) && !empty($mod_data)) {
                $data = $mod_data;
            }
        }

        if ((!is_array($data) || empty($data)) && function_exists('viora_import_parse_js')) {
            $demo_data = viora_import_parse_js('/assets/data/page/home/journey.js', 'homeJourneyData');
            if (is_array($demo_data) && !empty($demo_data)) {
                $data = $demo_data;
            }
        }

        if ($enabled === null) {
            $enabled = absint(get_theme_mod('viora_home_journey_enabled', isset($data['enabled']) ? (int) $data['enabled'] : 1));
        }

        if (is_customize_preview()) {
            $preview_data = get_theme_mod('viora_home_journey_data', array());
            if (function_exists('viora_home_journey_normalize_input')) {
                $preview_data = viora_home_journey_normalize_input($preview_data);
            }
            if (is_array($preview_data) && !empty($preview_data)) {
                $data = $preview_data;
            }
            $enabled = absint(get_theme_mod('viora_home_journey_enabled', $enabled));
        }

        $data = viora_home_journey_sanitize_data($data);
        $data['enabled'] = ($enabled === 1);

        return array(
            'page_id' => $page_id,
            'enabled' => $enabled,
            'data' => $data,
        );
    }
}
