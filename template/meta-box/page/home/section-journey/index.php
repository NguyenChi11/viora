<?php
$enabled = isset($enabled) ? absint($enabled) : 1;
$journey_data = isset($journey_data) && is_array($journey_data) ? $journey_data : array();
wp_nonce_field('viora_home_journey_meta_save', 'viora_home_journey_meta_nonce');
?>

<div id="viora-home-journey-metabox-root" class="viora-home-journey-editor">
    <div class="viora-home-journey-editor__top">
        <label class="viora-home-journey-editor__toggle">
            <input type="checkbox" id="viora-home-journey-enabled" name="viora_home_journey_enabled" value="1"
                <?php checked($enabled, 1); ?>>
            <?php esc_html_e('Enable journey', 'viora'); ?>
        </label>

        <label class="viora-home-journey-editor__toggle">
            <input type="checkbox" class="viora-home-journey-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <input type="hidden" id="viora-home-journey-data-json" name="viora_home_journey_data_json"
        value="<?php echo esc_attr(wp_json_encode($journey_data)); ?>">

    <div class="viora-home-journey-editor__section">
        <h4><?php esc_html_e('Section Header', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
            <input type="text" class="regular-text viora-journey-field" data-path="header.title" data-help-path="header.title">
        </div>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('CTA Text', 'viora'); ?></label>
            <input type="text" class="regular-text viora-journey-field" data-path="header.cta.text" data-help-path="header.cta.text">
        </div>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('CTA URL', 'viora'); ?></label>
            <div class="viora-link-row">
                <input type="url" class="regular-text viora-journey-field" data-path="header.cta.url" data-help-path="header.cta.url">
                <button type="button" class="button viora-journey-choose-link"
                    data-url-path="header.cta.url"
                    data-title-path="header.cta.text"><?php esc_html_e('Choose Link', 'viora'); ?></button>
            </div>
        </div>
    </div>

    <div class="viora-home-journey-editor__section">
        <h4><?php esc_html_e('Timeline Items', 'viora'); ?></h4>
        <?php for ($i = 0; $i < 5; $i++) : ?>
            <div class="viora-journey-card" data-item-index="<?php echo esc_attr($i); ?>">
                <div class="viora-journey-card__head">
                    <button type="button" class="button-link viora-toggle-journey-card" data-card-index="<?php echo esc_attr($i); ?>" aria-expanded="true">
                        <span class="viora-journey-card__title"><?php echo esc_html(sprintf(__('Item %d', 'viora'), $i + 1)); ?></span>
                        <span class="viora-journey-card__chevron" aria-hidden="true"></span>
                    </button>
                </div>

                <div class="viora-journey-card__body">
                    <div class="viora-control">
                        <label class="viora-field-label"><?php esc_html_e('Year', 'viora'); ?></label>
                        <input type="text" class="regular-text viora-journey-field" data-path="layout.timeline.items.<?php echo esc_attr($i); ?>.year"
                            data-help-path="layout.timeline.items.0.year">
                    </div>

                    <div class="viora-control">
                        <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
                        <input type="text" class="regular-text viora-journey-field" data-path="layout.timeline.items.<?php echo esc_attr($i); ?>.title"
                            data-help-path="layout.timeline.items.0.title">
                    </div>

                    <div class="viora-control">
                        <label class="viora-field-label"><?php esc_html_e('Description', 'viora'); ?></label>
                        <textarea rows="3" class="large-text viora-journey-field"
                            data-path="layout.timeline.items.<?php echo esc_attr($i); ?>.description"
                            data-help-path="layout.timeline.items.0.description"></textarea>
                    </div>

                    <div class="viora-control">
                        <label class="viora-field-label"><?php esc_html_e('Icon', 'viora'); ?></label>
                        <div class="viora-media-field" data-id-path="layout.timeline.items.<?php echo esc_attr($i); ?>.icon_id"
                            data-url-path="layout.timeline.items.<?php echo esc_attr($i); ?>.icon_url"
                            data-fallback-path="layout.timeline.items.<?php echo esc_attr($i); ?>.icon">
                            <input type="hidden" class="viora-media-id-field" data-path="layout.timeline.items.<?php echo esc_attr($i); ?>.icon_id">
                            <div class="viora-media-actions">
                                <button type="button" class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                                <button type="button" class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
                            </div>
                            <div class="viora-media-preview"></div>
                        </div>
                    </div>

                    <label class="viora-home-journey-editor__toggle">
                        <input type="checkbox" class="viora-journey-field viora-journey-active"
                            data-path="layout.timeline.items.<?php echo esc_attr($i); ?>.isActive">
                        <?php esc_html_e('Set as active item', 'viora'); ?>
                    </label>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="viora-home-journey-editor__section">
        <h4><?php esc_html_e('Visual', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Rocket Icon', 'viora'); ?></label>
            <div class="viora-media-field" data-id-path="layout.visual.rocketIcon_id"
                data-url-path="layout.visual.rocketIcon_url"
                data-fallback-path="layout.visual.rocketIcon">
                <input type="hidden" class="viora-media-id-field" data-path="layout.visual.rocketIcon_id">
                <div class="viora-media-actions">
                    <button type="button" class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
                    <button type="button" class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
                </div>
                <div class="viora-media-preview"></div>
            </div>
        </div>

        <label class="viora-home-journey-editor__toggle">
            <input type="checkbox" class="viora-journey-field" data-path="layout.visual.rings.first" data-help-path="layout.visual.rings.first">
            <?php esc_html_e('Show first ring', 'viora'); ?>
        </label>
        <label class="viora-home-journey-editor__toggle">
            <input type="checkbox" class="viora-journey-field" data-path="layout.visual.rings.second" data-help-path="layout.visual.rings.second">
            <?php esc_html_e('Show second ring', 'viora'); ?>
        </label>
        <label class="viora-home-journey-editor__toggle">
            <input type="checkbox" class="viora-journey-field" data-path="layout.visual.flash" data-help-path="layout.visual.flash">
            <?php esc_html_e('Show flash effect', 'viora'); ?>
        </label>
    </div>
</div>