<?php
$banner_data = is_array($banner_data) ? $banner_data : array();
?>

<div id="viora-home-banner-customizer-root" class="viora-home-banner-editor">
    <input type="hidden" id="viora-home-banner-initial-data"
        value="<?php echo esc_attr(wp_json_encode($banner_data)); ?>">

    <input type="hidden" id="viora-home-banner-data" value="<?php echo esc_attr(wp_json_encode($banner_data)); ?>">

    <div class="viora-home-banner-editor__top">
        <label class="viora-home-banner-editor__toggle">
            <input type="checkbox" class="viora-home-banner-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Eyebrow', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Icon', 'viora'); ?></label>
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
        </div>

        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Text', 'viora'); ?></label>
            <input type="text" class="regular-text viora-field" data-path="eyebrow.text">
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Title', 'viora'); ?></h4>
        <div class="viora-grid-2">
            <div class="viora-control">
                <label class="viora-field-label"><?php esc_html_e('Line 1', 'viora'); ?></label>
                <input type="text" class="regular-text viora-field" data-path="title.line1">
            </div>
            <div class="viora-control">
                <label class="viora-field-label"><?php esc_html_e('Highlight', 'viora'); ?></label>
                <input type="text" class="regular-text viora-field" data-path="title.highlight">
            </div>
            <div class="viora-control viora-control--full">
                <label class="viora-field-label"><?php esc_html_e('Line 2', 'viora'); ?></label>
                <input type="text" class="regular-text viora-field" data-path="title.line2">
            </div>
            <div class="viora-control viora-control--full">
                <label class="viora-field-label"><?php esc_html_e('Description', 'viora'); ?></label>
                <textarea rows="4" class="large-text viora-field" data-path="description"></textarea>
            </div>
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Actions', 'viora'); ?></h4>
        <div class="viora-action-grid">
            <div class="viora-action-card">
                <h5><?php esc_html_e('Primary Button', 'viora'); ?></h5>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Button text', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="actions.primary.text">
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Button URL', 'viora'); ?></label>
                    <div class="viora-link-row">
                        <input type="url" class="regular-text viora-field" data-path="actions.primary.url">
                        <button type="button" class="button viora-banner-choose-link"
                            data-url-path="actions.primary.url"
                            data-title-path="actions.primary.text"><?php esc_html_e('Choose Link', 'viora'); ?></button>
                    </div>
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Button icon', 'viora'); ?></label>
                    <div class="viora-media-field" data-id-path="actions.primary.icon_id"
                        data-url-path="actions.primary.icon_url" data-fallback-path="actions.primary.icon">
                        <input type="hidden" class="viora-media-id-field" data-path="actions.primary.icon_id">
                        <div class="viora-media-actions">
                            <button type="button"
                                class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                            <button type="button"
                                class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
                        </div>
                        <div class="viora-media-preview"></div>
                    </div>
                </div>
            </div>

            <div class="viora-action-card">
                <h5><?php esc_html_e('Secondary Button', 'viora'); ?></h5>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Button text', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="actions.secondary.text">
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Button URL', 'viora'); ?></label>
                    <div class="viora-link-row">
                        <input type="url" class="regular-text viora-field" data-path="actions.secondary.url">
                        <button type="button" class="button viora-banner-choose-link"
                            data-url-path="actions.secondary.url"
                            data-title-path="actions.secondary.text"><?php esc_html_e('Choose Link', 'viora'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Trust', 'viora'); ?></h4>
        <div class="viora-grid-2">
            <div class="viora-control">
                <label class="viora-field-label"><?php esc_html_e('Avatars', 'viora'); ?></label>
                <input type="text" class="regular-text" data-avatar-input="1" data-help-path="trust.avatars">
            </div>
            <div class="viora-control">
                <label class="viora-field-label"><?php esc_html_e('Text', 'viora'); ?></label>
                <input type="text" class="regular-text viora-field" data-path="trust.text">
            </div>
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Visual', 'viora'); ?></h4>
        <div class="viora-action-grid">
            <div class="viora-action-card">
                <h5><?php esc_html_e('Main Image', 'viora'); ?></h5>
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
            </div>

            <div class="viora-action-card">
                <h5><?php esc_html_e('Preview Image', 'viora'); ?></h5>
                <div class="viora-media-field" data-id-path="visual.previewImage_id"
                    data-url-path="visual.previewImage_url" data-fallback-path="visual.previewImage">
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
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Stats', 'viora'); ?></h4>
        <div class="viora-action-grid">
            <div class="viora-action-card">
                <h5><?php esc_html_e('Top Stat', 'viora'); ?></h5>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Icon', 'viora'); ?></label>
                    <div class="viora-media-field" data-id-path="visual.stats.0.icon_id"
                        data-url-path="visual.stats.0.icon_url" data-fallback-path="visual.stats.0.icon">
                        <input type="hidden" class="viora-media-id-field" data-path="visual.stats.0.icon_id">
                        <div class="viora-media-actions">
                            <button type="button"
                                class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                            <button type="button"
                                class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
                        </div>
                        <div class="viora-media-preview"></div>
                    </div>
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Value', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="visual.stats.0.value">
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Label', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="visual.stats.0.label">
                </div>
            </div>

            <div class="viora-action-card">
                <h5><?php esc_html_e('Bottom Stat', 'viora'); ?></h5>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Icon', 'viora'); ?></label>
                    <div class="viora-media-field" data-id-path="visual.stats.1.icon_id"
                        data-url-path="visual.stats.1.icon_url" data-fallback-path="visual.stats.1.icon">
                        <input type="hidden" class="viora-media-id-field" data-path="visual.stats.1.icon_id">
                        <div class="viora-media-actions">
                            <button type="button"
                                class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                            <button type="button"
                                class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
                        </div>
                        <div class="viora-media-preview"></div>
                    </div>
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Value', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="visual.stats.1.value">
                </div>
                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Label', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-field" data-path="visual.stats.1.label">
                </div>
            </div>
        </div>
    </div>

    <div class="viora-home-banner-editor__section">
        <h4><?php esc_html_e('Other', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Scroll hint', 'viora'); ?></label>
            <input type="text" class="regular-text viora-field" data-path="scrollHint">
        </div>
    </div>
</div>