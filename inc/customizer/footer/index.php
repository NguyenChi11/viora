<?php

if (!function_exists('viora_customize_register_footer')) {
    function viora_customize_register_footer($wp_customize)
    {
        if (!($wp_customize instanceof WP_Customize_Manager)) {
            return;
        }

        $wp_customize->add_section('viora_footer_section', array(
            'title'    => __('Footer', 'viora'),
            'priority' => 40,
        ));

        $wp_customize->add_setting('viora_footer_policy_text', array(
            'default'           => __('Policy', 'viora'),
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('viora_footer_policy_text', array(
            'label'   => __('Policy Text', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting('viora_footer_policy_link_url', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control('viora_footer_policy_link_url', array(
            'label'   => __('Policy URL', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'url',
        ));

        $wp_customize->add_setting('viora_footer_policy_link_title', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('viora_footer_policy_link_title', array(
            'label'   => __('Policy Link Label', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting('viora_footer_policy_link_new_tab', array(
            'default'           => 0,
            'transport'         => 'postMessage',
            'sanitize_callback' => 'absint',
        ));
        $wp_customize->add_control('viora_footer_policy_link_new_tab', array(
            'label'   => __('Open policy link in new tab', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'checkbox',
        ));

        $wp_customize->add_setting('viora_footer_policy_choose_link_dummy', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        if (class_exists('Viora_Choose_Link_Control')) {
            $wp_customize->add_control(new Viora_Choose_Link_Control($wp_customize, 'viora_footer_policy_choose_link_dummy', array(
                'label'          => __('Choose Policy Link', 'viora'),
                'description'    => __('Pick policy URL and label from existing content.', 'viora'),
                'section'        => 'viora_footer_section',
                'url_setting'    => 'viora_footer_policy_link_url',
                'title_setting'  => 'viora_footer_policy_link_title',
                'target_setting' => 'viora_footer_policy_link_new_tab',
                'return_section' => 'viora_footer_section',
            )));
        }

        $wp_customize->add_setting('viora_footer_service_text', array(
            'default'           => __('Service', 'viora'),
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('viora_footer_service_text', array(
            'label'   => __('Service Text', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting('viora_footer_service_link_url', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control('viora_footer_service_link_url', array(
            'label'   => __('Service URL', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'url',
        ));

        $wp_customize->add_setting('viora_footer_service_link_title', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        $wp_customize->add_control('viora_footer_service_link_title', array(
            'label'   => __('Service Link Label', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'text',
        ));

        $wp_customize->add_setting('viora_footer_service_link_new_tab', array(
            'default'           => 0,
            'transport'         => 'postMessage',
            'sanitize_callback' => 'absint',
        ));
        $wp_customize->add_control('viora_footer_service_link_new_tab', array(
            'label'   => __('Open service link in new tab', 'viora'),
            'section' => 'viora_footer_section',
            'type'    => 'checkbox',
        ));

        $wp_customize->add_setting('viora_footer_service_choose_link_dummy', array(
            'default'           => '',
            'transport'         => 'postMessage',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        if (class_exists('Viora_Choose_Link_Control')) {
            $wp_customize->add_control(new Viora_Choose_Link_Control($wp_customize, 'viora_footer_service_choose_link_dummy', array(
                'label'          => __('Choose Service Link', 'viora'),
                'description'    => __('Pick service URL and label from existing content.', 'viora'),
                'section'        => 'viora_footer_section',
                'url_setting'    => 'viora_footer_service_link_url',
                'title_setting'  => 'viora_footer_service_link_title',
                'target_setting' => 'viora_footer_service_link_new_tab',
                'return_section' => 'viora_footer_section',
            )));
        }
    }
}
add_action('customize_register', 'viora_customize_register_footer');
