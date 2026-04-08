<?php
function viora_import_parse_js($rel_file, $const_name)
{
    $path = get_theme_file_path($rel_file);
    if (!file_exists($path)) {
        return array();
    }
    $s = file_get_contents($path);
    if (!is_string($s) || $s === '') {
        return array();
    }
    $re = '/const\s+' . preg_quote($const_name, '/') . '\s*=\s*(\{[\s\S]*?\}|\[[\s\S]*?\])\s*;/m';
    if (!preg_match($re, $s, $m)) {
        return array();
    }
    $obj = $m[1];
    $obj = rtrim($obj, ';');
    $json = preg_replace('/([,{]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', $obj);
    $json = preg_replace('/,\s*]/', ']', $json);
    $json = preg_replace('/,\s*}/', '}', $json);
    $data = json_decode($json, true);
    return is_array($data) ? $data : array();
}

function viora_import_image_id($url)
{
    if (!is_string($url) || $url === '') {
        return 0;
    }
    $src = viora_import_resolve_theme_path($url);
    $exist = viora_import_find_attachment_by_source($src);
    if ($exist) {
        return $exist;
    }
    return viora_import_copy_to_uploads($src);
}

function viora_import_resolve_theme_path($url)
{
    if (!is_string($url) || $url === '') {
        return get_theme_file_path('/');
    }

    $path_part = $url;
    if (preg_match('#^https?://#i', $url)) {
        $parsed = parse_url($url);
        if (is_array($parsed) && isset($parsed['path']) && is_string($parsed['path']) && $parsed['path'] !== '') {
            $path_part = $parsed['path'];
        }
    }

    $theme_dir = function_exists('get_template') ? (string) get_template() : '';
    $rel = $path_part;
    if ($theme_dir !== '') {
        $pattern_current = '#^/wp-content/themes/' . preg_quote($theme_dir, '#') . '#';
        $rel = preg_replace($pattern_current, '', $rel);
    }
    if ($rel === $path_part) {
        // Back-compat with old demo data that used /wp-content/themes/viora/...
        $rel = preg_replace('#^/wp-content/themes/viora#', '', $rel);
    }

    $rel = '/' . ltrim($rel, '/');
    return get_theme_file_path($rel);
}

function viora_import_find_attachment_by_source($src)
{
    $q = new WP_Query(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'meta_query' => array(
            array('key' => 'viora_source_file', 'value' => $src, 'compare' => '='),
        ),
        'fields' => 'ids',
        'no_found_rows' => true,
    ));
    if ($q->have_posts()) {
        $ids = $q->posts;
        return isset($ids[0]) ? (int)$ids[0] : 0;
    }
    return 0;
}
function viora_import_copy_to_uploads($src_path)
{
    if (!file_exists($src_path)) {
        return 0;
    }
    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir']) . 'viora-imports';
    if (!is_dir($base)) {
        wp_mkdir_p($base);
    }
    $name = basename($src_path);
    $dest = trailingslashit($base) . $name;
    $i = 1;
    while (file_exists($dest)) {
        $pi = pathinfo($name);
        $alt = $pi['filename'] . '-' . $i . (isset($pi['extension']) ? '.' . $pi['extension'] : '');
        $dest = trailingslashit($base) . $alt;
        $i++;
    }
    if (!copy($src_path, $dest)) {
        return 0;
    }
    $ft = wp_check_filetype($dest, null);
    $att = array(
        'post_mime_type' => $ft['type'],
        'post_title' => sanitize_file_name(basename($dest)),
        'post_content' => '',
        'post_status' => 'inherit',
    );
    $attach_id = wp_insert_attachment($att, $dest);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $meta = wp_generate_attachment_metadata($attach_id, $dest);
    wp_update_attachment_metadata($attach_id, $meta);
    update_post_meta($attach_id, 'viora_source_file', $src_path);
    return (int)$attach_id;
}

function viora_maybe_import_home_banner_demo_once()
{
    if (get_option('viora_home_banner_demo_imported') === '1') {
        return;
    }

    $home_id = function_exists('viora_home_banner_find_home_page_id')
        ? viora_home_banner_find_home_page_id()
        : (int) get_option('page_on_front');
    if ($home_id <= 0) {
        return;
    }

    $existing = get_post_meta($home_id, 'viora_home_banner_data', true);
    if (is_array($existing) && !empty($existing)) {
        update_option('viora_home_banner_demo_imported', '1');
        return;
    }

    $file = get_theme_file_path('/import/data-demo/page/home/banner.php');
    if (file_exists($file)) {
        require_once $file;
        if (function_exists('viora_import_home_banner_demo')) {
            viora_import_home_banner_demo();
        }
    }

    $after = get_post_meta($home_id, 'viora_home_banner_data', true);
    if (is_array($after) && !empty($after)) {
        update_option('viora_home_banner_demo_imported', '1');
    }
}

function viora_admin_maybe_import_home_banner_demo_once()
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

    $template = get_page_template_slug($post_id);
    $front_id = (int) get_option('page_on_front');
    if ($template !== 'home-page.php' && $post_id !== $front_id) {
        return;
    }

    viora_maybe_import_home_banner_demo_once();
}
add_action('current_screen', 'viora_admin_maybe_import_home_banner_demo_once');

add_action('after_switch_theme', 'viora_maybe_import_home_banner_demo_once', 20);

function viora_maybe_import_home_services_demo_once()
{
    if (get_option('viora_home_services_demo_imported') === '1') {
        return;
    }

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
        update_option('viora_home_services_demo_imported', '1');
        return;
    }

    $file = get_theme_file_path('/import/data-demo/page/home/services.php');
    if (file_exists($file)) {
        require_once $file;
        if (function_exists('viora_import_home_services_demo')) {
            viora_import_home_services_demo();
        }
    }

    $after = get_post_meta($home_id, 'viora_home_services_data', true);
    if (is_array($after) && !empty($after)) {
        update_option('viora_home_services_demo_imported', '1');
    }
}

function viora_admin_maybe_import_home_services_demo_once()
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

    $template = get_page_template_slug($post_id);
    $front_id = (int) get_option('page_on_front');
    if ($template !== 'home-page.php' && $post_id !== $front_id) {
        return;
    }

    viora_maybe_import_home_services_demo_once();
}
add_action('current_screen', 'viora_admin_maybe_import_home_services_demo_once');

add_action('after_switch_theme', 'viora_maybe_import_home_services_demo_once', 20);

function viora_maybe_import_home_journey_demo_once()
{
    if (get_option('viora_home_journey_demo_imported') === '1') {
        return;
    }

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
        update_option('viora_home_journey_demo_imported', '1');
        return;
    }

    $file = get_theme_file_path('/import/data-demo/page/home/journey.php');
    if (file_exists($file)) {
        require_once $file;
        if (function_exists('viora_import_home_journey_demo')) {
            viora_import_home_journey_demo();
        }
    }

    $after = get_post_meta($home_id, 'viora_home_journey_data', true);
    if (is_array($after) && !empty($after)) {
        update_option('viora_home_journey_demo_imported', '1');
    }
}

function viora_admin_maybe_import_home_journey_demo_once()
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

    $template = get_page_template_slug($post_id);
    $front_id = (int) get_option('page_on_front');
    if ($template !== 'home-page.php' && $post_id !== $front_id) {
        return;
    }

    viora_maybe_import_home_journey_demo_once();
}
add_action('current_screen', 'viora_admin_maybe_import_home_journey_demo_once');

add_action('after_switch_theme', 'viora_maybe_import_home_journey_demo_once', 20);

function viora_maybe_import_home_portfolio_demo_once()
{
    if (get_option('viora_home_portfolio_demo_imported') === '1') {
        return;
    }

    $home_id = function_exists('viora_home_portfolio_find_home_page_id')
        ? viora_home_portfolio_find_home_page_id()
        : (function_exists('viora_home_banner_find_home_page_id')
            ? viora_home_banner_find_home_page_id()
            : (int) get_option('page_on_front'));
    if ($home_id <= 0) {
        return;
    }

    $existing = get_post_meta($home_id, 'viora_home_portfolio_data', true);
    if (is_array($existing) && !empty($existing)) {
        update_option('viora_home_portfolio_demo_imported', '1');
        return;
    }

    $file = get_theme_file_path('/import/data-demo/page/home/portfolio.php');
    if (file_exists($file)) {
        require_once $file;
        if (function_exists('viora_import_home_portfolio_demo')) {
            viora_import_home_portfolio_demo();
        }
    }

    $after = get_post_meta($home_id, 'viora_home_portfolio_data', true);
    if (is_array($after) && !empty($after)) {
        update_option('viora_home_portfolio_demo_imported', '1');
    }
}

function viora_admin_maybe_import_home_portfolio_demo_once()
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

    $template = get_page_template_slug($post_id);
    $front_id = (int) get_option('page_on_front');
    if ($template !== 'home-page.php' && $post_id !== $front_id) {
        return;
    }

    viora_maybe_import_home_portfolio_demo_once();
}
add_action('current_screen', 'viora_admin_maybe_import_home_portfolio_demo_once');

add_action('after_switch_theme', 'viora_maybe_import_home_portfolio_demo_once', 20);
