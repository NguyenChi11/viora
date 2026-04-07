<?php
if (!defined('_S_VERSION')) {
    define('_S_VERSION', '1.0.0');
}

function viora_setup()
{
    load_theme_textdomain('viora', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('custom-logo', array(
        'height'      => 120,
        'width'       => 240,
        'flex-width'  => true,
        'flex-height' => true,
    ));
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    register_nav_menus(array(
        'menu-1' => __('Primary Menu', 'viora'),
    ));
}
add_action('after_setup_theme', 'viora_setup');

function viora_require_file($relative_path)
{
    $path = get_theme_file_path($relative_path);
    if (file_exists($path)) {
        require_once $path;
    }
}

viora_require_file('/import/import-css-js.php');
viora_require_file('/inc/functions/demo-function.php');
viora_require_file('/inc/customizer/link-picker/index.php');
viora_require_file('/inc/functions/header-function.php');
viora_require_file('/inc/functions/page/home-page.php');
viora_require_file('/inc/customizer/header/index.php');
viora_require_file('/inc/customizer/footer/index.php');
viora_require_file('/inc/customizer/home-page/index.php');
viora_require_file('/inc/meta-box/page/home/index.php');
