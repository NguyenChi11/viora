<?php
function viora_customize_register_header($wp_customize)
{
    $wp_customize->add_section('viora_header_section', array(
        'title'    => __('Header', 'viora'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('header_logo', array(
        'default'           => 0,
        'transport'         => 'postMessage',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'header_logo', array(
        'label'       => __('Logo', 'viora'),
        'section'     => 'viora_header_section',
        'description' => __('Please use a square logo for best display.', 'viora'),
        'mime_type'   => 'image',
    )));

    $wp_customize->add_setting('viora_header_title', array(
        'default'           => '',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('viora_header_title', array(
        'label'   => __('Title', 'viora'),
        'section' => 'viora_header_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('viora_header_description', array(
        'default'           => '',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));

    $wp_customize->add_control('viora_header_description', array(
        'label'   => __('Description', 'viora'),
        'section' => 'viora_header_section',
        'type'    => 'textarea',
    ));

    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial('header_logo', array(
            'selector'        => '.site-brand__logo-wrap',
            'settings'        => array('header_logo'),
            'render_callback' => function () {
                $logo_id = (int) get_theme_mod('header_logo', 0);
                $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : get_theme_file_uri('/assets/images/logo.png');
                return '<img class="site-brand__logo" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
            },
        ));
    }
}
add_action('customize_register', 'viora_customize_register_header');

function viora_header_print_i18n($handle)
{
    $i18n = array(
        'mediaTitle' => __('Select Header Logo', 'viora'),
        'useImage'   => __('Use Image', 'viora'),
    );

    wp_add_inline_script(
        $handle,
        'window.vioraHeaderI18n = ' . wp_json_encode($i18n) . ';',
        'before'
    );
}

function viora_header_customize_preview_js()
{
    wp_enqueue_script(
        'viora-header-customize-script',
        get_theme_file_uri('/template/customize/header/script.js'),
        array('customize-preview'),
        filemtime(get_theme_file_path('/template/customize/header/script.js')),
        true
    );

    viora_header_print_i18n('viora-header-customize-script');
}
add_action('customize_preview_init', 'viora_header_customize_preview_js');

function viora_header_admin_menu()
{
    add_theme_page(
        __('Header', 'viora'),
        __('Header', 'viora'),
        'edit_theme_options',
        'viora-header',
        'viora_header_admin_page'
    );
}
add_action('admin_menu', 'viora_header_admin_menu');

function viora_header_admin_enqueue($hook)
{
    if ($hook !== 'appearance_page_viora-header') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'viora-header-admin-style',
        get_theme_file_uri('/template/customize/header/style.css'),
        array(),
        filemtime(get_theme_file_path('/template/customize/header/style.css'))
    );
    wp_enqueue_script(
        'viora-header-admin-script',
        get_theme_file_uri('/template/customize/header/script.js'),
        array('jquery'),
        filemtime(get_theme_file_path('/template/customize/header/script.js')),
        true
    );

    viora_header_print_i18n('viora-header-admin-script');
}
add_action('admin_enqueue_scripts', 'viora_header_admin_enqueue');

function viora_header_admin_page()
{
    $logo_id = (int) get_theme_mod('header_logo', 0);
    $text = (string) get_theme_mod('viora_header_title', '');
    if ($text === '') {
        $text = (string) get_theme_mod('header_text', '');
    }
    $desc = (string) get_theme_mod('viora_header_description', '');
    if ($desc === '') {
        $desc = (string) get_theme_mod('header_description', '');
    }
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';

    include get_theme_file_path('/template/customize/header/index.php');
}

function viora_handle_header_save()
{
    if (!current_user_can('edit_theme_options')) {
        wp_die(esc_html__('Not allowed.', 'viora'));
    }

    check_admin_referer('viora_header_save');

    $logo_raw = isset($_POST['header_logo']) ? wp_unslash($_POST['header_logo']) : '';
    $logo = absint($logo_raw);
    $text = isset($_POST['viora_header_title']) ? sanitize_text_field(wp_unslash($_POST['viora_header_title'])) : '';
    $desc = isset($_POST['viora_header_description']) ? sanitize_textarea_field(wp_unslash($_POST['viora_header_description'])) : '';

    if ($logo_raw === '' || $logo === 0) {
        remove_theme_mod('header_logo');
    } else {
        set_theme_mod('header_logo', $logo);
    }

    if ($text === '') {
        remove_theme_mod('viora_header_title');
        remove_theme_mod('header_text');
    } else {
        set_theme_mod('viora_header_title', $text);
        remove_theme_mod('header_text');
    }

    if ($desc === '') {
        remove_theme_mod('viora_header_description');
        remove_theme_mod('header_description');
    } else {
        set_theme_mod('viora_header_description', $desc);
        remove_theme_mod('header_description');
    }

    wp_safe_redirect(admin_url('themes.php?page=viora-header&updated=1'));
    exit;
}
add_action('admin_post_viora_save_header', 'viora_handle_header_save');
