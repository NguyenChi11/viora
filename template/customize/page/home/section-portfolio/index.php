<?php
$portfolio_data = is_array($portfolio_data) ? $portfolio_data : array();
?>

<div id="viora-home-portfolio-customizer-root" class="viora-home-portfolio-editor">
    <input type="hidden" id="viora-home-portfolio-initial-data"
        value="<?php echo esc_attr(wp_json_encode($portfolio_data)); ?>">

    <input type="hidden" id="viora-home-portfolio-data" value="<?php echo esc_attr(wp_json_encode($portfolio_data)); ?>">

    <div class="viora-home-portfolio-editor__top">
        <label class="viora-home-portfolio-editor__toggle">
            <input type="checkbox" class="viora-home-portfolio-help-toggle" value="1">
            <?php esc_html_e('Help mode', 'viora'); ?>
        </label>
    </div>

    <div class="viora-home-portfolio-editor__section">
        <h4><?php esc_html_e('Section Header', 'viora'); ?></h4>
        <div class="viora-control">
            <label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
            <input type="text" class="regular-text viora-portfolio-field" data-path="title" data-help-path="portfolio.title">
        </div>
    </div>
</div>