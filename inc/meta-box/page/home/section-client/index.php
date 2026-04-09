<?php

if (!function_exists('viora_home_client_meta_box_content')) {
	function viora_home_client_meta_box_content($post)
	{
		if (!($post instanceof WP_Post)) {
			return;
		}

		$state = function_exists('viora_home_client_get_data_for_front')
			? viora_home_client_get_data_for_front($post->ID)
			: array();

		$enabled = isset($state['enabled']) ? absint($state['enabled']) : null;
		$client_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

		if ($enabled === null) {
			$enabled_meta = get_post_meta($post->ID, 'viora_home_client_enabled', true);
			$enabled = $enabled_meta === '' ? 1 : absint($enabled_meta);
		}

		if (empty($client_data)) {
			$meta_data = get_post_meta($post->ID, 'viora_home_client_data', true);
			if (is_array($meta_data) && !empty($meta_data)) {
				$client_data = $meta_data;
			}
		}

		if (empty($client_data)) {
			$mod_data = get_theme_mod('viora_home_client_data', array());
			if (is_array($mod_data) && !empty($mod_data)) {
				$client_data = $mod_data;
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

		if (function_exists('viora_home_client_sanitize_data')) {
			$client_data = viora_home_client_sanitize_data($client_data);
		}

		$i18n = function_exists('viora_home_get_inline_i18n_data')
			? viora_home_get_inline_i18n_data()
			: array();

		$style_path = get_theme_file_path('/template/meta-box/page/home/section-client/style.css');
		$style_ver = file_exists($style_path) ? filemtime($style_path) : null;

		$script_path = get_theme_file_path('/template/meta-box/page/home/section-client/script.js');
		$script_ver = file_exists($script_path) ? filemtime($script_path) : null;

		wp_enqueue_media();
		wp_enqueue_style(
			'viora-home-client-meta-style',
			get_theme_file_uri('/template/meta-box/page/home/section-client/style.css'),
			array(),
			$style_ver
		);
		wp_enqueue_script(
			'viora-home-client-meta-script',
			get_theme_file_uri('/template/meta-box/page/home/section-client/script.js'),
			array(),
			$script_ver,
			true
		);

		wp_add_inline_script(
			'viora-home-client-meta-script',
			'window.vioraHomeClientMetaData=' . wp_json_encode(array(
				'enabled' => $enabled,
				'data' => $client_data,
				'i18n' => $i18n,
			)) . ';',
			'before'
		);

		include get_theme_file_path('/template/meta-box/page/home/section-client/index.php');
	}
}

if (!function_exists('viora_save_home_client_meta')) {
	function viora_save_home_client_meta($post_id)
	{
		if (!isset($_POST['viora_home_client_meta_nonce']) || !wp_verify_nonce($_POST['viora_home_client_meta_nonce'], 'viora_home_client_meta_save')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (!function_exists('viora_home_client_is_home_page_id') || !viora_home_client_is_home_page_id($post_id)) {
			return;
		}

		$enabled = isset($_POST['viora_home_client_enabled']) ? 1 : 0;
		$raw_json = isset($_POST['viora_home_client_data_json']) ? wp_unslash($_POST['viora_home_client_data_json']) : '';
		$data = json_decode($raw_json, true);
		if (!is_array($data)) {
			$data = array();
		}

		$data = function_exists('viora_home_client_sanitize_data') ? viora_home_client_sanitize_data($data) : $data;
		$data['enabled'] = ($enabled === 1);

		if (function_exists('viora_home_client_sync_storage')) {
			viora_home_client_sync_storage($enabled, $data, $post_id);
			return;
		}

		update_post_meta($post_id, 'viora_home_client_data', $data);
		update_post_meta($post_id, 'viora_home_client_enabled', $enabled);
		set_theme_mod('viora_home_client_data', $data);
		set_theme_mod('viora_home_client_enabled', $enabled);
	}
}
add_action('save_post_page', 'viora_save_home_client_meta');
