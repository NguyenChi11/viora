<?php
$page_id = get_queried_object_id();

$state = function_exists('viora_home_client_get_data_for_front')
    ? viora_home_client_get_data_for_front($page_id)
    : array('enabled' => 1, 'data' => array());

$enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
$client_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

if ($enabled !== 1) {
    if (is_customize_preview()) {
?>
        <section id="viora-home-client" class="viora-home-client" data-no-fallback="1" style="display:none"></section>
    <?php
    }
    return;
}

if (!is_array($client_data) || empty($client_data)) {
    if (is_customize_preview()) {
    ?>
        <section id="viora-home-client" class="viora-home-client" data-no-fallback="1" style="display:none"></section>
    <?php
    }
    return;
}

if (function_exists('viora_home_client_sanitize_data')) {
    $client_data = viora_home_client_sanitize_data($client_data);
}

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

$heading = isset($client_data['heading']) && is_array($client_data['heading']) ? $client_data['heading'] : array();
$heading_kicker = isset($heading['kicker']) && is_string($heading['kicker']) ? trim($heading['kicker']) : '';
$heading_title = isset($heading['title']) && is_string($heading['title']) ? trim($heading['title']) : '';
$heading_lede = isset($heading['lede']) && is_string($heading['lede']) ? trim($heading['lede']) : '';

$client_testimonials = isset($client_data['testimonials']) && is_array($client_data['testimonials']) ? $client_data['testimonials'] : array();
$client_testimonials = array_values(array_filter(array_map(static function ($item) use ($media_url) {
    if (!is_array($item)) {
        return null;
    }

    $quote = isset($item['quote']) && is_string($item['quote']) ? trim($item['quote']) : '';
    $name = isset($item['name']) && is_string($item['name']) ? trim($item['name']) : '';
    $role = isset($item['role']) && is_string($item['role']) ? trim($item['role']) : '';
    $avatar = $media_url($item, 'avatar_id', array('avatar_url', 'avatar'));

    if ($quote === '' && $name === '' && $role === '' && $avatar === '') {
        return null;
    }

    if ($quote === '' || $name === '' || $avatar === '') {
        return null;
    }

    return array(
        'quote' => $quote,
        'name' => $name,
        'role' => $role,
        'avatar' => $avatar,
    );
}, $client_testimonials), static function ($item) {
    return is_array($item) && !empty($item);
}));

if (empty($client_testimonials)) {
    if (is_customize_preview()) {
    ?>
        <section id="viora-home-client" class="viora-home-client" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

$slider_payload = wp_json_encode($client_testimonials);
?>

<section id="viora-home-client" class="viora-home-client" data-aos="fade-up"
    data-client-items="<?php echo esc_attr($slider_payload); ?>">
    <div class="container viora-home-client__container" data-client-stage>
        <div class="viora-home-client__panel">
            <div class="viora-home-client__heading">
                <p class="viora-home-client__kicker" <?php echo $heading_kicker === '' ? ' style="display:none"' : ''; ?>><?php echo esc_html($heading_kicker); ?></p>
                <h2 class="viora-home-client__title" <?php echo $heading_title === '' ? ' style="display:none"' : ''; ?>><?php echo esc_html($heading_title); ?></h2>
                <p class="viora-home-client__lede" <?php echo $heading_lede === '' ? ' style="display:none"' : ''; ?>><?php echo esc_html($heading_lede); ?></p>
            </div>

            <div class="viora-home-client__slider-shell">
                <button type="button" class="viora-home-client__nav viora-home-client__nav--prev swiper-navigation"
                    data-client-prev aria-label="<?php esc_attr_e('Previous testimonial', 'viora'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M15.5 4.5L8 12l7.5 7.5" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <div class="swiper viora-home-client__slider" data-client-slider>
                    <div class="swiper-wrapper" data-client-wrapper></div>
                </div>

                <button type="button" class="viora-home-client__nav viora-home-client__nav--next swiper-navigation"
                    data-client-next aria-label="<?php esc_attr_e('Next testimonial', 'viora'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M8.5 4.5L16 12l-7.5 7.5" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>

                <div class="viora-home-client__pagination" data-client-pagination></div>
            </div>
        </div>
    </div>
</section>