<?php
function viora_import_header_demo()
{
    $logo_id = (int) get_theme_mod('header_logo', 0);
    $title = (string) get_theme_mod('viora_header_title', '');
    if ($title === '') {
        $title = (string) get_theme_mod('header_text', '');
    }
    if ($logo_id || $title !== '') {
        return;
    }
    if (function_exists('viora_import_parse_js')) {
        $data = viora_import_parse_js('/assets/data/header-data.js', 'headerData');
        if (is_array($data)) {
            if (isset($data['logo']) && $data['logo']) {
                if (function_exists('viora_import_image_id')) {
                    $img_id = viora_import_image_id($data['logo']);
                    if ($img_id) {
                        set_theme_mod('header_logo', (int)$img_id);
                    }
                }
            }
            if (isset($data['title'])) {
                $t = (string)$data['title'];
                if ($t !== '') {
                    set_theme_mod('viora_header_title', $t);
                    remove_theme_mod('header_text');
                }
            }
        }
    }
}