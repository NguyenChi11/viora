<?php

if (!function_exists('viora_home_client_attach_media_ids')) {
	function viora_home_client_attach_media_ids($data)
	{
		if (!is_array($data) || !function_exists('viora_import_image_id')) {
			return $data;
		}

		$testimonials = isset($data['testimonials']) && is_array($data['testimonials']) ? $data['testimonials'] : array();
		$processed = array();

		foreach ($testimonials as $item) {
			$item = is_array($item) ? $item : array();

			$url = '';
			foreach (array('avatar_url', 'avatar') as $key) {
				if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
					$url = trim($item[$key]);
					break;
				}
			}

			$id = absint(isset($item['avatar_id']) ? $item['avatar_id'] : 0);
			if ($id <= 0 && $url !== '') {
				$id = viora_import_image_id($url);
			}

			$item['avatar_id'] = $id;
			$item['avatar_url'] = $url;
			$item['avatar'] = $url;

			$processed[] = $item;
		}

		$data['testimonials'] = $processed;
		return $data;
	}
}

if (!function_exists('viora_import_home_client_demo')) {
	function viora_import_home_client_demo()
	{
		$home_id = function_exists('viora_home_client_find_home_page_id')
			? viora_home_client_find_home_page_id()
			: (function_exists('viora_home_banner_find_home_page_id')
				? viora_home_banner_find_home_page_id()
				: (int) get_option('page_on_front'));

		if ($home_id <= 0) {
			return;
		}

		$existing = get_post_meta($home_id, 'viora_home_client_data', true);
		if (is_array($existing) && !empty($existing)) {
			return;
		}

		$data = function_exists('viora_import_parse_js')
			? viora_import_parse_js('/assets/data/page/home/client.js', 'homeClientData')
			: array();

		if (!is_array($data) || empty($data)) {
			return;
		}

		if (function_exists('viora_home_client_sanitize_data')) {
			$data = viora_home_client_sanitize_data($data);
		}

		$data = viora_home_client_attach_media_ids($data);
		$enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;

		if (function_exists('viora_home_client_sync_storage')) {
			viora_home_client_sync_storage($enabled, $data, $home_id);
			return;
		}

		update_post_meta($home_id, 'viora_home_client_data', $data);
		update_post_meta($home_id, 'viora_home_client_enabled', $enabled);
		set_theme_mod('viora_home_client_data', $data);
		set_theme_mod('viora_home_client_enabled', $enabled);
	}
}
