<?php
$client_data = is_array($client_data) ? $client_data : array();
?>

<div id="viora-home-client-customizer-root" class="viora-home-client-editor">
	<input type="hidden" id="viora-home-client-initial-data"
		value="<?php echo esc_attr(wp_json_encode($client_data)); ?>">

	<input type="hidden" id="viora-home-client-data" value="<?php echo esc_attr(wp_json_encode($client_data)); ?>">

	<div class="viora-home-client-editor__top">
		<label class="viora-home-client-editor__toggle">
			<input type="checkbox" class="viora-home-client-help-toggle" value="1">
			<?php esc_html_e('Help mode', 'viora'); ?>
		</label>
	</div>

	<div class="viora-home-client-editor__section">
		<h4><?php esc_html_e('Section Header', 'viora'); ?></h4>
		<div class="viora-control">
			<label class="viora-field-label"><?php esc_html_e('Kicker', 'viora'); ?></label>
			<input type="text" class="regular-text viora-client-field" data-path="heading.kicker" data-help-path="heading.kicker">
		</div>
		<div class="viora-control">
			<label class="viora-field-label"><?php esc_html_e('Title', 'viora'); ?></label>
			<input type="text" class="regular-text viora-client-field" data-path="heading.title" data-help-path="heading.title">
		</div>
		<div class="viora-control">
			<label class="viora-field-label"><?php esc_html_e('Lead text', 'viora'); ?></label>
			<textarea rows="3" class="large-text viora-client-field" data-path="heading.lede" data-help-path="heading.lede"></textarea>
		</div>
	</div>

	<div class="viora-home-client-editor__section">
		<h4><?php esc_html_e('Testimonials', 'viora'); ?></h4>
		<div class="viora-client-cards-grid" data-client-cards-list></div>
		<div class="viora-client-actions">
			<button type="button" class="button button-secondary viora-add-client-card">
				<?php esc_html_e('Add Testimonial', 'viora'); ?>
			</button>
		</div>
	</div>

	<template id="viora-client-card-template">
		<div class="viora-client-card" data-card-index="__INDEX__">
			<div class="viora-client-card__head">
				<button type="button" class="viora-toggle-client-card" data-card-index="__INDEX__" aria-expanded="true">
					<span class="viora-client-card__title"></span>
					<span class="viora-client-card__chevron" aria-hidden="true"></span>
				</button>
				<button type="button" class="button-link-delete viora-remove-client-card"><?php esc_html_e('Remove', 'viora'); ?></button>
			</div>

			<div class="viora-client-card__body">
				<div class="viora-control">
					<label class="viora-field-label"><?php esc_html_e('Avatar', 'viora'); ?></label>
					<div class="viora-media-field" data-id-path="testimonials.__INDEX__.avatar_id"
						data-url-path="testimonials.__INDEX__.avatar_url"
						data-fallback-path="testimonials.__INDEX__.avatar">
						<input type="hidden" class="viora-media-id-field" data-path="testimonials.__INDEX__.avatar_id">
						<div class="viora-media-actions">
							<button type="button" class="button viora-select-media"><?php esc_html_e('Select image', 'viora'); ?></button>
							<button type="button" class="button viora-remove-media"><?php esc_html_e('Remove image', 'viora'); ?></button>
						</div>
						<div class="viora-media-preview"></div>
					</div>
				</div>

				<div class="viora-control">
					<label class="viora-field-label"><?php esc_html_e('Quote', 'viora'); ?></label>
					<textarea rows="4" class="large-text viora-client-field" data-path="testimonials.__INDEX__.quote" data-help-path="testimonials.quote"></textarea>
				</div>

				<div class="viora-control">
					<label class="viora-field-label"><?php esc_html_e('Name', 'viora'); ?></label>
					<input type="text" class="regular-text viora-client-field" data-path="testimonials.__INDEX__.name" data-help-path="testimonials.name">
				</div>

				<div class="viora-control">
					<label class="viora-field-label"><?php esc_html_e('Role', 'viora'); ?></label>
					<input type="text" class="regular-text viora-client-field" data-path="testimonials.__INDEX__.role" data-help-path="testimonials.role">
				</div>
			</div>
		</div>
	</template>
</div>