<?php
$enabled = isset($enabled) ? absint($enabled) : 1;
$portfolio_data = isset($portfolio_data) && is_array($portfolio_data) ? $portfolio_data : array();
wp_nonce_field('viora_home_portfolio_meta_save', 'viora_home_portfolio_meta_nonce');
?>

<div id="viora-home-portfolio-metabox-root" class="viora-home-portfolio-editor">
    <div class="viora-home-portfolio-editor__top">
        <label class="viora-home-portfolio-editor__toggle">
            <input type="checkbox" id="viora-home-portfolio-enabled" name="viora_home_portfolio_enabled" value="1"
                <?php checked($enabled, 1); ?>>
            <?php esc_html_e('Enable portfolio', 'viora'); ?>
        </label>

        <label class="viora-home-portfolio-editor__toggle">
            <input type="checkbox" class="viora-home-portfolio-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <input type="hidden" id="viora-home-portfolio-data-json" name="viora_home_portfolio_data_json"
        value="<?php echo esc_attr(wp_json_encode($portfolio_data)); ?>">

    <div class="viora-home-portfolio-editor__section">
        <h4><?php esc_html_e('Section Header', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
            <input type="text" class="regular-text viora-portfolio-field" data-path="title" data-help-path="portfolio.title">
        </div>
    </div>
</div>