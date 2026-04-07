<?php

if (!function_exists('viora_home_get_inline_i18n_data')) {
    function viora_home_get_inline_i18n_data()
    {
        return array(
            'selectImage' => __('Select image', 'viora'),
            'useImage' => __('Use image', 'viora'),
            'removeImage' => __('Remove image', 'viora'),
            'noImageSelected' => __('No image selected', 'viora'),
            'helpModeLabel' => __('Help mode', 'viora'),
            'helpModeDescription' => __('Show field hints for content editors.', 'viora'),
            'eyebrowText' => __('Eyebrow text', 'viora'),
            'line1' => __('Line 1', 'viora'),
            'highlight' => __('Highlight', 'viora'),
            'line2' => __('Line 2', 'viora'),
            'description' => __('Description', 'viora'),
            'primaryText' => __('Primary button text', 'viora'),
            'primaryUrl' => __('Primary button URL', 'viora'),
            'secondaryText' => __('Secondary button text', 'viora'),
            'secondaryUrl' => __('Secondary button URL', 'viora'),
            'avatars' => __('Avatars', 'viora'),
            'trustText' => __('Trust text', 'viora'),
            'mainImage' => __('Main image', 'viora'),
            'previewImage' => __('Preview image', 'viora'),
            'scrollHint' => __('Scroll hint', 'viora'),
            'helpHints' => array(
                'eyebrow.icon_url' => __('Enter icon URL or choose an image from media.', 'viora'),
                'eyebrow.text' => __('Short eyebrow text above the title.', 'viora'),
                'title.line1' => __('First line of the main heading.', 'viora'),
                'title.highlight' => __('Highlighted word or phrase in the title.', 'viora'),
                'title.line2' => __('Second line of the main heading.', 'viora'),
                'description' => __('Short supporting paragraph under the title.', 'viora'),
                'actions.primary.text' => __('Label for the primary button.', 'viora'),
                'actions.primary.url' => __('Destination URL for the primary button.', 'viora'),
                'actions.primary.icon_url' => __('Optional icon URL for the primary button.', 'viora'),
                'actions.secondary.text' => __('Label for the secondary button.', 'viora'),
                'actions.secondary.url' => __('Destination URL for the secondary button.', 'viora'),
                'trust.avatars' => __('Comma-separated short avatar labels.', 'viora'),
                'trust.text' => __('Trust message shown near avatars.', 'viora'),
                'visual.mainImage_url' => __('Main banner image URL.', 'viora'),
                'visual.previewImage_url' => __('Preview/phone image URL.', 'viora'),
                'visual.stats.0.icon_url' => __('Top stat icon URL.', 'viora'),
                'visual.stats.0.value' => __('Top stat value.', 'viora'),
                'visual.stats.0.label' => __('Top stat label.', 'viora'),
                'visual.stats.1.icon_url' => __('Bottom stat icon URL.', 'viora'),
                'visual.stats.1.value' => __('Bottom stat value.', 'viora'),
                'visual.stats.1.label' => __('Bottom stat label.', 'viora'),
                'scrollHint' => __('Scroll hint text at the bottom of the banner.', 'viora'),
            ),
        );
    }
}

if (!function_exists('viora_home_add_inline_i18n')) {
    function viora_home_add_inline_i18n($handle)
    {
        if (!is_string($handle) || $handle === '') {
            return;
        }

        $data = viora_home_get_inline_i18n_data();
        $js = 'window.vioraHomeI18n = ' . wp_json_encode($data) . ';';
        wp_add_inline_script($handle, $js, 'before');
    }
}

require get_template_directory() . '/inc/customizer/home-page/section-banner/index.php';
