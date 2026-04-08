<?php

if (!class_exists('Viora_Home_Journey_Control') && class_exists('WP_Customize_Control')) {
    class Viora_Home_Journey_Control extends WP_Customize_Control
    {
        public $type = 'viora_home_journey';

        public function render_content()
        {
            $journey_data = $this->value();
            if (is_string($journey_data) && $journey_data !== '') {
                $decoded = json_decode(wp_unslash($journey_data), true);
                if (is_array($decoded)) {
                    $journey_data = $decoded;
                }
            }

            $journey_data = is_array($journey_data) ? $journey_data : array();
            if (empty($journey_data) && function_exists('viora_home_journey_get_data_for_front')) {
                $state = viora_home_journey_get_data_for_front();
                if (isset($state['data']) && is_array($state['data'])) {
                    $journey_data = $state['data'];
                }
            }

            if (empty($journey_data) && function_exists('viora_home_journey_get_default_data')) {
                $journey_data = viora_home_journey_get_default_data();
            }

            echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
            if (!empty($this->description)) {
                echo '<p class="description">' . esc_html($this->description) . '</p>';
            }

            include get_theme_file_path('/template/customize/page/home/section-journey/index.php');
        }
    }
}

if (!function_exists('viora_home_journey_customize_register')) {
    function viora_home_journey_customize_register($wp_customize)
    {
        if (!($wp_customize instanceof WP_Customize_Manager)) {
            return;
        }

        $state = function_exists('viora_home_journey_get_data_for_front')
            ? viora_home_journey_get_data_for_front()
            : array('enabled' => 1, 'data' => array());
        $default_enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
        $default_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

        $wp_customize->add_section('viora_home_journey_section', array(
            'title' => __('Home Page: Journey', 'viora'),
            'priority' => 23,
        ));

        $wp_customize->add_setting('viora_home_journey_enabled', array(
            'default' => $default_enabled,
            'type' => 'theme_mod',
            'transport' => 'postMessage',
            'sanitize_callback' => 'absint',
        ));
        $wp_customize->add_control('viora_home_journey_enabled', array(
            'label' => __('Enable journey', 'viora'),
            'section' => 'viora_home_journey_section',
            'type' => 'checkbox',
        ));

        $wp_customize->add_setting('viora_home_journey_data', array(
            'default' => $default_data,
            'type' => 'theme_mod',
            'transport' => 'postMessage',
            'sanitize_callback' => 'viora_home_journey_customize_sanitize_data',
        ));

        if (class_exists('Viora_Home_Journey_Control')) {
            $wp_customize->add_control(new Viora_Home_Journey_Control($wp_customize, 'viora_home_journey_data', array(
                'label' => __('Journey content', 'viora'),
                'description' => __('Edit journey header, timeline, and visual content.', 'viora'),
                'section' => 'viora_home_journey_section',
            )));
        }

        if (isset($wp_customize->selective_refresh)) {
            $wp_customize->selective_refresh->add_partial('viora_home_journey_enabled_partial', array(
                'selector' => '#viora-home-journey',
                'settings' => array('viora_home_journey_enabled'),
                'container_inclusive' => true,
                'fallback_refresh' => false,
                'render_callback' => function () {
                    ob_start();
                    get_template_part('template/template-parts/page/home/section-journey/index');
                    return ob_get_clean();
                },
            ));
        }
    }
}
add_action('customize_register', 'viora_home_journey_customize_register');

if (!function_exists('viora_home_journey_customize_sanitize_data')) {
    function viora_home_journey_customize_sanitize_data($value)
    {
        if (function_exists('viora_home_journey_merge_with_current')) {
            $value = viora_home_journey_merge_with_current($value, 0);
        }

        if (function_exists('viora_home_journey_sanitize_data')) {
            return viora_home_journey_sanitize_data($value);
        }

        return is_array($value) ? $value : array();
    }
}

if (!function_exists('viora_home_journey_sync_customizer_to_meta')) {
    function viora_home_journey_sync_customizer_to_meta($wp_customize_manager)
    {
        if (!($wp_customize_manager instanceof WP_Customize_Manager)) {
            return;
        }

        $enabled_setting = $wp_customize_manager->get_setting('viora_home_journey_enabled');
        $enabled_post = ($enabled_setting instanceof WP_Customize_Setting) ? $enabled_setting->post_value() : null;
        if (is_wp_error($enabled_post) || $enabled_post === null) {
            $enabled = absint(get_theme_mod('viora_home_journey_enabled', 1));
        } else {
            $enabled = absint($enabled_post);
        }

        $data_setting = $wp_customize_manager->get_setting('viora_home_journey_data');
        $data_post = ($data_setting instanceof WP_Customize_Setting) ? $data_setting->post_value() : null;
        if (is_wp_error($data_post) || $data_post === null) {
            $data = get_theme_mod('viora_home_journey_data', array());
        } else {
            $data = $data_post;
        }

        if (function_exists('viora_home_journey_merge_with_current')) {
            $data = viora_home_journey_merge_with_current($data, 0);
        }

        $data = function_exists('viora_home_journey_sanitize_data')
            ? viora_home_journey_sanitize_data($data)
            : (is_array($data) ? $data : array());

        $preferred_page = function_exists('viora_home_journey_find_home_page_id')
            ? absint(viora_home_journey_find_home_page_id())
            : 0;
        if ($preferred_page <= 0) {
            $preferred_page = (int) get_option('page_on_front');
        }

        if (function_exists('viora_home_journey_sync_storage')) {
            viora_home_journey_sync_storage($enabled, $data, $preferred_page);
            return;
        }

        set_theme_mod('viora_home_journey_enabled', $enabled);
        set_theme_mod('viora_home_journey_data', $data);
    }
}
add_action('customize_save_after', 'viora_home_journey_sync_customizer_to_meta');

if (!function_exists('viora_home_journey_customize_controls_enqueue')) {
    function viora_home_journey_customize_controls_enqueue()
    {
        wp_enqueue_media();

        wp_enqueue_style(
            'viora-home-journey-customize-style',
            get_theme_file_uri('/template/customize/page/home/section-journey/style.css'),
            array(),
            null
        );

        wp_enqueue_script(
            'viora-home-journey-customize-script',
            get_theme_file_uri('/template/customize/page/home/section-journey/script.js'),
            array('customize-controls'),
            null,
            true
        );

        if (function_exists('viora_home_add_inline_i18n')) {
            viora_home_add_inline_i18n('viora-home-journey-customize-script');
        }
    }
}
add_action('customize_controls_enqueue_scripts', 'viora_home_journey_customize_controls_enqueue');
