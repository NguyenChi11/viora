<?php

/**
 * Viora - Link Picker Customizer
 *
 * Registers the Link Picker section, settings, control,
 * and related enqueue hooks.
 */

if (!class_exists('Viora_Customize_Button_Control') && class_exists('WP_Customize_Control')) {
    class Viora_Customize_Button_Control extends WP_Customize_Control
    {
        public $type        = 'viora_button';
        public $button_url  = '';
        public $button_text = '';

        public function render_content()
        {
            if (empty($this->button_url)) {
                echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
                echo '<p>' . esc_html__('Could not find a Home page using template home-page.php.', 'viora') . '</p>';
                return;
            }

            echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
            if (!empty($this->description)) {
                echo '<p class="description">' . esc_html($this->description) . '</p>';
            }

            $text = $this->button_text
                ? $this->button_text
                : __('Open edit page', 'viora');

            echo '<a class="button button-primary" href="' . esc_url($this->button_url) . '" target="_blank" rel="noopener">'
                . esc_html($text)
                . '</a>';
        }
    }
}

if (!class_exists('Viora_Link_List_Control') && class_exists('WP_Customize_Control')) {
    class Viora_Link_List_Control extends WP_Customize_Control
    {
        public $type = 'viora_link_list';

        public function render_content()
        {
            echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
            if (!empty($this->description)) {
                echo '<p class="description">' . esc_html($this->description) . '</p>';
            }

            include get_theme_file_path('/template/customize/link-picker/index.php');
        }
    }
}

if (!class_exists('Viora_Choose_Link_Control') && class_exists('WP_Customize_Control')) {
    class Viora_Choose_Link_Control extends WP_Customize_Control
    {
        public $type = 'viora_choose_link';
        public $url_setting = '';
        public $title_setting = '';
        public $target_setting = '';
        public $return_section = '';

        public function render_content()
        {
            echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
            if (!empty($this->description)) {
                echo '<p class="description">' . esc_html($this->description) . '</p>';
            }

            $return_section = $this->return_section ? $this->return_section : $this->section;

            echo '<button type="button" class="button button-secondary viora-choose-link-control"'
                . ' data-url-setting="' . esc_attr($this->url_setting) . '"'
                . ' data-title-setting="' . esc_attr($this->title_setting) . '"'
                . ' data-target-setting="' . esc_attr($this->target_setting) . '"'
                . ' data-return-section="' . esc_attr($return_section) . '">'
                . esc_html__('Choose Link', 'viora')
                . '</button>';
        }
    }
}

function viora_link_picker_customize_register($wp_customize)
{
    if (!($wp_customize instanceof WP_Customize_Manager)) {
        return;
    }

    $wp_customize->add_section('viora_link_picker_section', array(
        'title'    => __('Link Picker', 'viora'),
        'priority' => 26,
    ));

    $wp_customize->add_setting('viora_link_picker_dummy', array(
        'default'           => '',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    if (class_exists('Viora_Link_List_Control')) {
        $wp_customize->add_control(new Viora_Link_List_Control($wp_customize, 'viora_link_picker_dummy', array(
            'label'       => __('Link Picker', 'viora'),
            'description' => __('List of pages and posts to select as links.', 'viora'),
            'section'     => 'viora_link_picker_section',
        )));
    }
}
add_action('customize_register', 'viora_link_picker_customize_register');

function viora_link_picker_enqueue_assets()
{
    wp_enqueue_style(
        'viora-link-picker-style',
        get_theme_file_uri('/template/customize/link-picker/style.css'),
        array(),
        null
    );
    wp_enqueue_script(
        'viora-link-picker-script',
        get_theme_file_uri('/template/customize/link-picker/script.js'),
        array('customize-controls'),
        null,
        true
    );

    $i18n = array(
        'noResults' => __('No results found.', 'viora'),
        'select' => __('Select', 'viora'),
        'loading' => __('Loading...', 'viora'),
        'directOpenNotice' => __('Link Picker is used from other sections. Please use the "Choose Link" button in the relevant tab to pick a link.', 'viora'),
    );
    wp_add_inline_script(
        'viora-link-picker-script',
        'window.vioraLinkPickerI18n = ' . wp_json_encode($i18n) . ';window.vioraLinkPickerI18n = window.vioraLinkPickerI18n;',
        'before'
    );
}
add_action('customize_controls_enqueue_scripts', 'viora_link_picker_enqueue_assets');
