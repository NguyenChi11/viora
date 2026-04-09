<?php

if (!class_exists('Viora_Home_Client_Control') && class_exists('WP_Customize_Control')) {
	class Viora_Home_Client_Control extends WP_Customize_Control
	{
		public $type = 'viora_home_client';

		public function render_content()
		{
			$client_data = $this->value();
			if (is_string($client_data) && $client_data !== '') {
				$decoded = json_decode(wp_unslash($client_data), true);
				if (is_array($decoded)) {
					$client_data = $decoded;
				}
			}

			$client_data = is_array($client_data) ? $client_data : array();
			if (empty($client_data) && function_exists('viora_home_client_get_data_for_front')) {
				$state = viora_home_client_get_data_for_front();
				if (isset($state['data']) && is_array($state['data'])) {
					$client_data = $state['data'];
				}
			}

			if (
				function_exists('viora_home_client_has_testimonials')
				&& !viora_home_client_has_testimonials($client_data)
				&& function_exists('viora_import_parse_js')
			) {
				$demo_data = viora_import_parse_js('/assets/data/page/home/client.js', 'homeClientData');
				if (is_array($demo_data) && !empty($demo_data)) {
					$client_data = function_exists('viora_home_client_sanitize_data')
						? viora_home_client_sanitize_data($demo_data)
						: $demo_data;
				}
			}

			if (empty($client_data) && function_exists('viora_home_client_get_default_data')) {
				$client_data = viora_home_client_get_default_data();
			}

			echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
			if (!empty($this->description)) {
				echo '<p class="description">' . esc_html($this->description) . '</p>';
			}

			include get_theme_file_path('/template/customize/page/home/section-client/index.php');
		}
	}
}

if (!function_exists('viora_home_client_customize_register')) {
	function viora_home_client_customize_register($wp_customize)
	{
		if (!($wp_customize instanceof WP_Customize_Manager)) {
			return;
		}

		$state = function_exists('viora_home_client_get_data_for_front')
			? viora_home_client_get_data_for_front()
			: array('enabled' => 1, 'data' => array());

		if (
			isset($state['data'])
			&& is_array($state['data'])
			&& function_exists('viora_home_client_has_testimonials')
			&& !viora_home_client_has_testimonials($state['data'])
			&& function_exists('viora_import_parse_js')
		) {
			$demo_data = viora_import_parse_js('/assets/data/page/home/client.js', 'homeClientData');
			if (is_array($demo_data) && !empty($demo_data)) {
				$state['data'] = function_exists('viora_home_client_sanitize_data')
					? viora_home_client_sanitize_data($demo_data)
					: $demo_data;
			}
		}

		$default_enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
		$default_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

		$wp_customize->add_section('viora_home_client_section', array(
			'title' => __('Home Page: Client', 'viora'),
			'priority' => 24,
		));

		$wp_customize->add_setting('viora_home_client_enabled', array(
			'default' => $default_enabled,
			'type' => 'theme_mod',
			'transport' => 'postMessage',
			'sanitize_callback' => 'absint',
		));
		$wp_customize->add_control('viora_home_client_enabled', array(
			'label' => __('Enable client section', 'viora'),
			'section' => 'viora_home_client_section',
			'type' => 'checkbox',
		));

		$wp_customize->add_setting('viora_home_client_data', array(
			'default' => $default_data,
			'type' => 'theme_mod',
			'transport' => 'postMessage',
			'sanitize_callback' => 'viora_home_client_customize_sanitize_data',
		));

		if (class_exists('Viora_Home_Client_Control')) {
			$wp_customize->add_control(new Viora_Home_Client_Control($wp_customize, 'viora_home_client_data', array(
				'label' => __('Client content', 'viora'),
				'description' => __('Edit heading and testimonial slides.', 'viora'),
				'section' => 'viora_home_client_section',
			)));
		}

		if (isset($wp_customize->selective_refresh)) {
			$wp_customize->selective_refresh->add_partial('viora_home_client_enabled_partial', array(
				'selector' => '#viora-home-client',
				'settings' => array('viora_home_client_enabled', 'viora_home_client_data'),
				'container_inclusive' => true,
				'fallback_refresh' => false,
				'render_callback' => function () {
					ob_start();
					get_template_part('template/template-parts/page/home/section-client/index');
					return ob_get_clean();
				},
			));
		}
	}
}
add_action('customize_register', 'viora_home_client_customize_register');

if (!function_exists('viora_home_client_customize_sanitize_data')) {
	function viora_home_client_customize_sanitize_data($value)
	{
		if (function_exists('viora_home_client_merge_with_current')) {
			$value = viora_home_client_merge_with_current($value, 0);
		}

		if (function_exists('viora_home_client_sanitize_data')) {
			return viora_home_client_sanitize_data($value);
		}

		return is_array($value) ? $value : array();
	}
}

if (!function_exists('viora_home_client_sync_customizer_to_meta')) {
	function viora_home_client_sync_customizer_to_meta($wp_customize_manager)
	{
		if (!($wp_customize_manager instanceof WP_Customize_Manager)) {
			return;
		}

		$enabled_setting = $wp_customize_manager->get_setting('viora_home_client_enabled');
		$enabled_post = ($enabled_setting instanceof WP_Customize_Setting) ? $enabled_setting->post_value() : null;
		if (is_wp_error($enabled_post) || $enabled_post === null) {
			$enabled = absint(get_theme_mod('viora_home_client_enabled', 1));
		} else {
			$enabled = absint($enabled_post);
		}

		$data_setting = $wp_customize_manager->get_setting('viora_home_client_data');
		$data_post = ($data_setting instanceof WP_Customize_Setting) ? $data_setting->post_value() : null;
		if (is_wp_error($data_post) || $data_post === null) {
			$data = get_theme_mod('viora_home_client_data', array());
		} else {
			$data = $data_post;
		}

		if (function_exists('viora_home_client_merge_with_current')) {
			$data = viora_home_client_merge_with_current($data, 0);
		}

		$data = function_exists('viora_home_client_sanitize_data')
			? viora_home_client_sanitize_data($data)
			: (is_array($data) ? $data : array());

		$preferred_page = function_exists('viora_home_client_find_home_page_id')
			? absint(viora_home_client_find_home_page_id())
			: 0;
		if ($preferred_page <= 0) {
			$preferred_page = (int) get_option('page_on_front');
		}

		if (function_exists('viora_home_client_sync_storage')) {
			viora_home_client_sync_storage($enabled, $data, $preferred_page);
			return;
		}

		set_theme_mod('viora_home_client_enabled', $enabled);
		set_theme_mod('viora_home_client_data', $data);
	}
}
add_action('customize_save_after', 'viora_home_client_sync_customizer_to_meta');

if (!function_exists('viora_home_client_customize_controls_enqueue')) {
	function viora_home_client_customize_controls_enqueue()
	{
		wp_enqueue_media();

		$style_path = get_theme_file_path('/template/customize/page/home/section-client/style.css');
		$style_ver = file_exists($style_path) ? filemtime($style_path) : null;

		$script_path = get_theme_file_path('/template/customize/page/home/section-client/script.js');
		$script_ver = file_exists($script_path) ? filemtime($script_path) : null;

		wp_enqueue_style(
			'viora-home-client-customize-style',
			get_theme_file_uri('/template/customize/page/home/section-client/style.css'),
			array(),
			$style_ver
		);

		wp_enqueue_script(
			'viora-home-client-customize-script',
			get_theme_file_uri('/template/customize/page/home/section-client/script.js'),
			array('customize-controls'),
			$script_ver,
			true
		);

		if (function_exists('viora_home_add_inline_i18n')) {
			viora_home_add_inline_i18n('viora-home-client-customize-script');
		}
	}
}
add_action('customize_controls_enqueue_scripts', 'viora_home_client_customize_controls_enqueue');
