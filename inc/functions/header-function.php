<?php
function viora_maybe_import_header_demo_once()
{
    $initialized = (int) get_theme_mod('viora_header_demo_initialized', 0);
    if ($initialized === 1) {
        return;
    }

    $logo = (int) get_theme_mod('header_logo', 0);
    $title = trim((string) get_theme_mod('viora_header_title', ''));
    if ($title === '') {
        $title = trim((string) get_theme_mod('header_text', ''));
    }

    // If user/theme already has data, mark as initialized and skip demo import.
    if ($logo > 0 || $title !== '') {
        set_theme_mod('viora_header_demo_initialized', 1);
        return;
    }

    $header_demo_file = get_theme_file_path('/import/data-demo/header-demo.php');
    if (file_exists($header_demo_file)) {
        require_once $header_demo_file;
        if (function_exists('viora_import_header_demo')) {
            viora_import_header_demo();
        }
    }

    set_theme_mod('viora_header_demo_initialized', 1);
}

function viora_admin_maybe_import_header()
{
    if (!is_admin()) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'post') {
        return;
    }

    $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
    if ($post_id <= 0 || get_post_type($post_id) !== 'page') {
        return;
    }

    $front_id = (int) get_option('page_on_front');
    $tpl = (string) get_page_template_slug($post_id);
    if ($post_id !== $front_id && $tpl !== 'home-page.php') {
        return;
    }

    viora_maybe_import_header_demo_once();
}
add_action('current_screen', 'viora_admin_maybe_import_header');
add_action('after_switch_theme', 'viora_maybe_import_header_demo_once');
