<?php
$page_id = get_queried_object_id();

$state = function_exists('viora_home_journey_get_data_for_front')
    ? viora_home_journey_get_data_for_front($page_id)
    : array('enabled' => 1, 'data' => array());

$enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
$journey_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

if ($enabled !== 1) {
    if (is_customize_preview()) {
?>
<section id="viora-home-journey" class="viora-journey" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

if (function_exists('viora_home_journey_sanitize_data')) {
    $journey_data = viora_home_journey_sanitize_data($journey_data);
}

$to_href = static function ($value) {
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return '';
    }

    if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
        return $value;
    }

    return home_url($value);
};

$media_url = static function ($node, $id_key, $url_keys) {
    $node = is_array($node) ? $node : array();

    $id = isset($node[$id_key]) ? absint($node[$id_key]) : 0;
    if ($id > 0) {
        $from_id = wp_get_attachment_image_url($id, 'full');
        if (is_string($from_id) && $from_id !== '') {
            return $from_id;
        }
    }

    foreach ($url_keys as $key) {
        if (isset($node[$key]) && is_string($node[$key]) && trim($node[$key]) !== '') {
            return $node[$key];
        }
    }

    return '';
};

$header = isset($journey_data['header']) && is_array($journey_data['header']) ? $journey_data['header'] : array();
$header_cta = isset($header['cta']) && is_array($header['cta']) ? $header['cta'] : array();

$header_title = isset($header['title']) && is_string($header['title']) ? trim($header['title']) : '';
$cta_text = isset($header_cta['text']) && is_string($header_cta['text']) ? trim($header_cta['text']) : '';
$cta_href_raw = isset($header_cta['url']) ? $header_cta['url'] : '';
$cta_href = $to_href($cta_href_raw);

$layout = isset($journey_data['layout']) && is_array($journey_data['layout']) ? $journey_data['layout'] : array();
$timeline = isset($layout['timeline']) && is_array($layout['timeline']) ? $layout['timeline'] : array();
$timeline_items_raw = isset($timeline['items']) && is_array($timeline['items']) ? $timeline['items'] : array();

$timeline_items = array();
foreach ($timeline_items_raw as $item) {
    if (!is_array($item)) {
        continue;
    }

    $year = isset($item['year']) && is_string($item['year']) ? trim($item['year']) : '';
    $title = isset($item['title']) && is_string($item['title']) ? trim($item['title']) : '';
    $description = isset($item['description']) && is_string($item['description']) ? trim($item['description']) : '';
    $icon = $media_url($item, 'icon_id', array('icon_url', 'icon'));
    $is_active = !empty($item['isActive']);

    if ($year === '' && $title === '' && $description === '' && $icon === '') {
        continue;
    }

    $timeline_items[] = array(
        'year' => $year,
        'title' => $title,
        'description' => $description,
        'icon' => $icon,
        'isActive' => $is_active,
    );
}

if (empty($timeline_items)) {
    if (is_customize_preview()) {
    ?>
<section id="viora-home-journey" class="viora-journey" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

$active_index = 0;
foreach ($timeline_items as $index => $item) {
    if (!empty($item['isActive'])) {
        $active_index = (int) $index;
        break;
    }
}

$visual = isset($layout['visual']) && is_array($layout['visual']) ? $layout['visual'] : array();
$visual_rings = isset($visual['rings']) && is_array($visual['rings']) ? $visual['rings'] : array();

$rocket_icon = $media_url($visual, 'rocketIcon_id', array('rocketIcon_url', 'rocketIcon'));
$show_ring_one = isset($visual_rings['first']) ? (bool) $visual_rings['first'] : true;
$show_ring_two = isset($visual_rings['second']) ? (bool) $visual_rings['second'] : true;
$show_flash = isset($visual['flash']) ? (bool) $visual['flash'] : true;

$has_header = ($header_title !== '' || ($cta_text !== '' && $cta_href !== ''));
$has_visual = ($rocket_icon !== '' || $show_ring_one || $show_ring_two || $show_flash);
?>

<section id="viora-home-journey" class="viora-journey" data-journey-section>
    <?php if (is_customize_preview()) : ?>
    <div class="viora-journey__hover-outline" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container viora-journey__container">
        <?php if ($has_header) : ?>
        <header class="viora-journey__header">
            <?php if ($header_title !== '') : ?>
            <h2 class="viora-journey__title"><?php echo esc_html($header_title); ?></h2>
            <?php endif; ?>
            <?php if ($cta_text !== '' && $cta_href !== '') : ?>
            <a class="viora-journey__cta" href="<?php echo esc_url($cta_href); ?>">
                <?php echo esc_html($cta_text); ?>
            </a>
            <?php endif; ?>
        </header>
        <?php endif; ?>

        <div class="viora-journey__layout" data-journey-layout>
            <div class="viora-journey__timeline" data-journey-timeline>
                <span class="viora-journey__line" aria-hidden="true"></span>
                <span class="viora-journey__progress" data-journey-progress aria-hidden="true"></span>
                <span class="viora-journey__indicator" data-journey-indicator aria-hidden="true"></span>

                <?php foreach ($timeline_items as $index => $item) :
                    $item_year = isset($item['year']) ? $item['year'] : '';
                    $item_title = isset($item['title']) ? $item['title'] : '';
                    $item_description = isset($item['description']) ? $item['description'] : '';
                    $item_icon = isset($item['icon']) ? $item['icon'] : '';
                    $item_is_active = ((int) $index === $active_index);
                ?>
                <article class="viora-journey__item<?php echo $item_is_active ? ' is-active' : ''; ?>" data-journey-item
                    data-year="<?php echo esc_attr($item_year); ?>" data-title="<?php echo esc_attr($item_title); ?>"
                    data-description="<?php echo esc_attr($item_description); ?>">
                    <div class="viora-journey__pin" aria-hidden="true">
                        <span class="viora-journey__pin-icon">
                            <?php if ($item_icon !== '') : ?>
                            <img src="<?php echo esc_url($item_icon); ?>" alt="" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </span>
                        <span class="viora-journey__point" data-journey-point></span>
                    </div>

                    <div class="viora-journey__content">
                        <?php if ($item_year !== '') : ?>
                        <p class="viora-journey__year"><?php echo esc_html($item_year); ?></p>
                        <?php endif; ?>
                        <?php if ($item_title !== '') : ?>
                        <h3 class="viora-journey__item-title"><?php echo esc_html($item_title); ?></h3>
                        <?php endif; ?>
                        <?php if ($item_description !== '') : ?>
                        <p class="viora-journey__description"><?php echo esc_html($item_description); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($has_visual) : ?>
            <aside class="viora-journey__visual" data-journey-visual aria-hidden="true">
                <div class="viora-journey__orbital" data-journey-orb>
                    <span class="viora-journey__rocket" data-journey-rocket>
                        <?php if ($rocket_icon !== '') : ?>
                        <img src="<?php echo esc_url($rocket_icon); ?>" alt="" loading="lazy" decoding="async">
                        <?php endif; ?>
                    </span>
                    <?php if ($show_ring_one) : ?>
                    <span class="viora-journey__alarm-ring viora-journey__alarm-ring--one" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php if ($show_ring_two) : ?>
                    <span class="viora-journey__alarm-ring viora-journey__alarm-ring--two" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php if ($show_flash) : ?>
                    <span class="viora-journey__alarm-flash" aria-hidden="true"></span>
                    <?php endif; ?>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>