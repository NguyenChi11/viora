<?php
$page_id = get_queried_object_id();

$state = function_exists('viora_home_services_get_data_for_front')
    ? viora_home_services_get_data_for_front($page_id)
    : array('enabled' => 1, 'data' => array());

$enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
$services_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

if ($enabled !== 1) {
    if (is_customize_preview()) {
?>
<section id="viora-service" class="viora-service" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

if (function_exists('viora_home_services_sanitize_data')) {
    $services_data = viora_home_services_sanitize_data($services_data);
}

$eyebrow = isset($services_data['eyebrow']) && is_string($services_data['eyebrow'])
    ? trim($services_data['eyebrow'])
    : '';
$title = isset($services_data['title']) && is_string($services_data['title'])
    ? trim($services_data['title'])
    : '';
$items = isset($services_data['items']) && is_array($services_data['items'])
    ? array_values($services_data['items'])
    : array();

$items = array_values(array_filter($items, static function ($item) {
    return is_array($item)
        && (
            (isset($item['title']) && is_string($item['title']) && trim($item['title']) !== '')
            || (isset($item['description']) && is_string($item['description']) && trim($item['description']) !== '')
            || (isset($item['iconImage_url']) && is_string($item['iconImage_url']) && trim($item['iconImage_url']) !== '')
            || (isset($item['features']) && is_array($item['features']) && !empty($item['features']))
        );
}));

if (empty($items)) {
    if (is_customize_preview()) {
    ?>
<section id="viora-service" class="viora-service" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

$frontend_state = array(
    'enabled' => true,
    'eyebrow' => $eyebrow,
    'title' => $title,
    'items' => $items,
);
?>

<script>
window.vioraHomeServicesState = <?php echo wp_json_encode($frontend_state); ?>;
window.vioraHomeServicesData = <?php echo wp_json_encode($items); ?>;
</script>

<section class="viora-service" id="viora-service" data-aos="fade-up">
    <?php if (is_customize_preview()) : ?>
    <div class="viora-service__hover-outline" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container viora-service__container">
        <?php if ($eyebrow !== '') : ?>
        <p class="viora-service__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php else : ?>
        <p class="viora-service__eyebrow" style="display:none"></p>
        <?php endif; ?>

        <?php if ($title !== '') : ?>
        <h2 class="viora-service__title"><?php echo esc_html($title); ?></h2>
        <?php else : ?>
        <h2 class="viora-service__title" style="display:none"></h2>
        <?php endif; ?>

        <div class="swiper viora-service__slider" data-service-slider>
            <div class="swiper-wrapper" data-service-wrapper></div>
            <div class="viora-service__pagination" aria-label="Service slides pagination"></div>
        </div>
    </div>
</section>