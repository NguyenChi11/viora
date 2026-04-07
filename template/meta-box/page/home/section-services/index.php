<?php
$enabled = isset($enabled) ? absint($enabled) : 1;
$services_data = isset($services_data) && is_array($services_data) ? $services_data : array();
wp_nonce_field('viora_home_services_meta_save', 'viora_home_services_meta_nonce');
?>

<div id="viora-home-services-metabox-root" class="viora-home-services-editor">
    <div class="viora-home-services-editor__top">
        <label class="viora-home-services-editor__toggle">
            <input type="checkbox" id="viora-home-services-enabled" name="viora_home_services_enabled" value="1"
                <?php checked($enabled, 1); ?>>
            <?php esc_html_e('Enable services', 'viora'); ?>
        </label>

        <label class="viora-home-services-editor__toggle">
            <input type="checkbox" class="viora-home-services-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <input type="hidden" id="viora-home-services-data-json" name="viora_home_services_data_json"
        value="<?php echo esc_attr(wp_json_encode($services_data)); ?>">

    <div class="viora-home-services-editor__section">
        <h4><?php esc_html_e('Section Header', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Eyebrow', 'viora'); ?></label>
            <input type="text" class="regular-text viora-services-field" data-path="eyebrow">
        </div>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
            <input type="text" class="regular-text viora-services-field" data-path="title">
        </div>
    </div>

    <div class="viora-home-services-editor__section">
        <h4><?php esc_html_e('Service Cards', 'viora'); ?></h4>
        <div class="viora-services-cards-grid" data-services-cards-list></div>
        <div class="viora-services-actions">
            <button type="button" class="button button-secondary viora-add-service-card">
                <?php esc_html_e('Add Service Card', 'viora'); ?>
            </button>
        </div>
    </div>

    <template id="viora-services-card-template">
        <div class="viora-services-card" data-card-index="__INDEX__">
            <div class="viora-services-card__head">
                <button type="button" class="button-link viora-toggle-service-card" data-card-index="__INDEX__"
                    aria-expanded="true">
                    <span class="viora-services-card__title"></span>
                    <span class="viora-services-card__chevron" aria-hidden="true"></span>
                </button>
                <button type="button"
                    class="button-link-delete viora-remove-service-card"><?php esc_html_e('Remove', 'viora'); ?></button>
            </div>

            <div class="viora-services-card__body">

                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Icon image', 'viora'); ?></label>
                    <div class="viora-media-field" data-id-path="items.__INDEX__.icon_id"
                        data-url-path="items.__INDEX__.iconImage_url" data-fallback-path="items.__INDEX__.iconImage">
                        <input type="hidden" class="viora-media-id-field" data-path="items.__INDEX__.icon_id">
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
                    <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
                    <input type="text" class="regular-text viora-services-field" data-path="items.__INDEX__.title">
                </div>

                <div class="viora-control">
                    <label class="viora-field-label"><?php esc_html_e('Description', 'viora'); ?></label>
                    <textarea rows="3" class="large-text viora-services-field"
                        data-path="items.__INDEX__.description"></textarea>
                </div>

                <div class="viora-control viora-features-control" data-features-control
                    data-path="items.__INDEX__.features" data-help-path="items.__INDEX__.features">
                    <button type="button" class="button-link viora-toggle-features" data-card-index="__INDEX__" aria-expanded="false">
                        <span class="viora-features-label"><?php esc_html_e('Features', 'viora'); ?></span>
                        <span class="viora-features-meta">
                            <span class="viora-features-count">0</span>
                            <span class="viora-features-chevron" aria-hidden="true"></span>
                        </span>
                    </button>
                    <div class="viora-features-body" hidden>
                        <div class="viora-feature-options" data-features-list></div>
                        <button type="button" class="button button-secondary viora-add-feature-option">
                            <?php esc_html_e('Add option', 'viora'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>