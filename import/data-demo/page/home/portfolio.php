<?php

if (!function_exists('viora_import_home_portfolio_demo')) {
    function viora_import_home_portfolio_demo()
    {
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
            return;
        }

        $data = function_exists('viora_import_parse_js')
            ? viora_import_parse_js('/assets/data/page/home/portfolio.js', 'vioraHomePortfolioData')
            : array();

        if (!is_array($data) || empty($data)) {
            return;
        }

        if (function_exists('viora_home_portfolio_sanitize_data')) {
            $data = viora_home_portfolio_sanitize_data($data);
        }

        $enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;
        if (function_exists('viora_home_portfolio_sync_storage')) {
            viora_home_portfolio_sync_storage($enabled, $data, $home_id);
            return;
        }

        update_post_meta($home_id, 'viora_home_portfolio_data', $data);
        update_post_meta($home_id, 'viora_home_portfolio_enabled', $enabled);
        set_theme_mod('viora_home_portfolio_data', $data);
        set_theme_mod('viora_home_portfolio_enabled', $enabled);
    }
}
