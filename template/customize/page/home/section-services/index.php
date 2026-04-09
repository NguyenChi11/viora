<?php
$services_data = is_array($services_data) ? $services_data : array();
?>

<div id="viora-home-services-customizer-root" class="viora-home-services-editor">
    <input type="hidden" id="viora-home-services-initial-data"
        value="<?php echo esc_attr(wp_json_encode($services_data)); ?>">

    <input type="hidden" id="viora-home-services-data" value="<?php echo esc_attr(wp_json_encode($services_data)); ?>">

    <div class="viora-home-services-editor__top">
        <label class="viora-home-services-editor__toggle">
            <input type="checkbox" class="viora-home-services-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

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
                <button type="button" class="viora-toggle-service-card" data-card-index="__INDEX__"
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
                    <button type="button" class="button-link viora-toggle-features" data-card-index="__INDEX__"
                        aria-expanded="false">
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