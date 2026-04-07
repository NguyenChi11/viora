<?php
$enabled = isset($enabled) ? absint($enabled) : 1;
$banner_data = isset($banner_data) && is_array($banner_data) ? $banner_data : array();
wp_nonce_field('viora_home_banner_meta_save', 'viora_home_banner_meta_nonce');
?>

<div id="viora-home-banner-metabox-root" class="viora-home-banner-editor">
    <p>
        <label>
            <input type="checkbox" id="viora-home-banner-enabled" name="viora_home_banner_enabled" value="1"
                <?php checked($enabled, 1); ?>>
            <?php esc_html_e('Enable banner', 'viora'); ?>
        </label>
    </p>

    <div class="viora-home-banner-editor__help-toggle">
        <label>
            <input type="checkbox" class="viora-home-banner-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <input type="hidden" id="viora-home-banner-data-json" name="viora_home_banner_data_json"
        value="<?php echo esc_attr(wp_json_encode($banner_data)); ?>">

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Eyebrow', 'viora'); ?></h4>
        <div class="viora-media-field" data-id-path="eyebrow.icon_id" data-url-path="eyebrow.icon_url"
            data-fallback-path="eyebrow.icon">
            <input type="hidden" class="viora-media-id-field" data-path="eyebrow.icon_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
        <p><input type="text" class="regular-text viora-field" data-path="eyebrow.text"></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Title', 'viora'); ?></h4>
        <p><input type="text" class="regular-text viora-field" data-path="title.line1"></p>
        <p><input type="text" class="regular-text viora-field" data-path="title.highlight"></p>
        <p><input type="text" class="regular-text viora-field" data-path="title.line2"></p>
        <p><textarea rows="4" class="large-text viora-field" data-path="description"></textarea></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Actions', 'viora'); ?></h4>
        <p><input type="text" class="regular-text viora-field" data-path="actions.primary.text"></p>
        <p><input type="url" class="regular-text viora-field" data-path="actions.primary.url"></p>
        <div class="viora-media-field" data-id-path="actions.primary.icon_id" data-url-path="actions.primary.icon_url"
            data-fallback-path="actions.primary.icon">
            <input type="hidden" class="viora-media-id-field" data-path="actions.primary.icon_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
        <p><input type="text" class="regular-text viora-field" data-path="actions.secondary.text"></p>
        <p><input type="url" class="regular-text viora-field" data-path="actions.secondary.url"></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Trust', 'viora'); ?></h4>
        <p><input type="text" class="regular-text" data-avatar-input="1" data-help-path="trust.avatars"></p>
        <p><input type="text" class="regular-text viora-field" data-path="trust.text"></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Visual', 'viora'); ?></h4>
        <div class="viora-media-field" data-id-path="visual.mainImage_id" data-url-path="visual.mainImage_url"
            data-fallback-path="visual.mainImage">
            <input type="hidden" class="viora-media-id-field" data-path="visual.mainImage_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
        <div class="viora-media-field" data-id-path="visual.previewImage_id" data-url-path="visual.previewImage_url"
            data-fallback-path="visual.previewImage">
            <input type="hidden" class="viora-media-id-field" data-path="visual.previewImage_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Top Stat', 'viora'); ?></h4>
        <div class="viora-media-field" data-id-path="visual.stats.0.icon_id" data-url-path="visual.stats.0.icon_url"
            data-fallback-path="visual.stats.0.icon">
            <input type="hidden" class="viora-media-id-field" data-path="visual.stats.0.icon_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
        <p><input type="text" class="regular-text viora-field" data-path="visual.stats.0.value"></p>
        <p><input type="text" class="regular-text viora-field" data-path="visual.stats.0.label"></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Bottom Stat', 'viora'); ?></h4>
        <div class="viora-media-field" data-id-path="visual.stats.1.icon_id" data-url-path="visual.stats.1.icon_url"
            data-fallback-path="visual.stats.1.icon">
            <input type="hidden" class="viora-media-id-field" data-path="visual.stats.1.icon_id">
            <div class="viora-media-actions">
                <button type="button"
                    class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                <button type="button"
                    class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
            </div>
            <div class="viora-media-preview"></div>
        </div>
        <p><input type="text" class="regular-text viora-field" data-path="visual.stats.1.value"></p>
        <p><input type="text" class="regular-text viora-field" data-path="visual.stats.1.label"></p>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Other', 'viora'); ?></h4>
        <p><input type="text" class="regular-text viora-field" data-path="scrollHint"></p>
    </div>
</div>