<?php
$page_id = get_queried_object_id();

$state = function_exists('viora_home_banner_get_data_for_front')
    ? viora_home_banner_get_data_for_front($page_id)
    : array('enabled' => 1, 'data' => array());

$enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
$banner_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

if ($enabled !== 1) {
    if (is_customize_preview()) {
?>
<section id="viora-banner" class="viora-banner" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

if (!is_array($banner_data) || empty($banner_data)) {
    if (is_customize_preview()) {
    ?>
<section id="viora-banner" class="viora-banner" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}

if (function_exists('viora_home_banner_sanitize_data')) {
    $banner_data = viora_home_banner_sanitize_data($banner_data);
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

$eyebrow_node = isset($banner_data['eyebrow']) && is_array($banner_data['eyebrow']) ? $banner_data['eyebrow'] : array();
$eyebrow_icon = $media_url(
    $eyebrow_node,
    'icon_id',
    array('icon_url', 'icon')
);
$eyebrow_text = isset($banner_data['eyebrow']['text']) && is_string($banner_data['eyebrow']['text']) && $banner_data['eyebrow']['text'] !== ''
    ? $banner_data['eyebrow']['text']
    : '';

$title_line_1 = isset($banner_data['title']['line1']) && is_string($banner_data['title']['line1']) && $banner_data['title']['line1'] !== ''
    ? $banner_data['title']['line1']
    : '';
$title_highlight = isset($banner_data['title']['highlight']) && is_string($banner_data['title']['highlight']) && $banner_data['title']['highlight'] !== ''
    ? $banner_data['title']['highlight']
    : '';
$title_line_2 = isset($banner_data['title']['line2']) && is_string($banner_data['title']['line2']) && $banner_data['title']['line2'] !== ''
    ? $banner_data['title']['line2']
    : '';

$description = isset($banner_data['description']) && is_string($banner_data['description']) && $banner_data['description'] !== ''
    ? $banner_data['description']
    : '';

$primary_text = isset($banner_data['actions']['primary']['text']) && is_string($banner_data['actions']['primary']['text']) && $banner_data['actions']['primary']['text'] !== ''
    ? $banner_data['actions']['primary']['text']
    : '';
$primary_url = isset($banner_data['actions']['primary']['url'])
    ? $to_href($banner_data['actions']['primary']['url'])
    : '';
$primary_icon = $media_url(
    isset($banner_data['actions']['primary']) ? $banner_data['actions']['primary'] : array(),
    'icon_id',
    array('icon_url', 'icon')
);

$secondary_text = isset($banner_data['actions']['secondary']['text']) && is_string($banner_data['actions']['secondary']['text']) && $banner_data['actions']['secondary']['text'] !== ''
    ? $banner_data['actions']['secondary']['text']
    : '';
$secondary_url = isset($banner_data['actions']['secondary']['url'])
    ? $to_href($banner_data['actions']['secondary']['url'])
    : '';

$avatars = isset($banner_data['trust']['avatars']) && is_array($banner_data['trust']['avatars']) && !empty($banner_data['trust']['avatars'])
    ? array_values($banner_data['trust']['avatars'])
    : array();
$avatars = array_values(array_filter(array_map(static function ($avatar) {
    if (!is_scalar($avatar)) {
        return '';
    }
    return trim((string) $avatar);
}, $avatars), static function ($avatar) {
    return $avatar !== '';
}));
$trust_text = isset($banner_data['trust']['text']) && is_string($banner_data['trust']['text']) && $banner_data['trust']['text'] !== ''
    ? $banner_data['trust']['text']
    : '';

$visual_node = isset($banner_data['visual']) && is_array($banner_data['visual']) ? $banner_data['visual'] : array();
$main_image = $media_url(
    $visual_node,
    'mainImage_id',
    array('mainImage_url', 'mainImage')
);
$preview_image = $media_url(
    $visual_node,
    'previewImage_id',
    array('previewImage_url', 'previewImage')
);

$default_top_stat = array(
    'icon' => '',
    'value' => '',
    'label' => '',
    'tone' => 'violet',
    'depth' => 22,
);
$default_bottom_stat = array(
    'icon' => '',
    'value' => '',
    'label' => '',
    'tone' => 'mint',
    'depth' => 16,
);

$top_stat = $default_top_stat;
$bottom_stat = $default_bottom_stat;

if (isset($banner_data['visual']['stats']) && is_array($banner_data['visual']['stats'])) {
    foreach ($banner_data['visual']['stats'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $position = isset($item['position']) && is_string($item['position']) ? strtolower($item['position']) : '';
        $target = $position === 'bottom' ? 'bottom' : ($position === 'top' ? 'top' : '');
        if ($target === '') {
            continue;
        }

        $source = $target === 'top' ? $top_stat : $bottom_stat;
        $source['icon'] = $media_url($item, 'icon_id', array('icon_url', 'icon'));
        $source['value'] = isset($item['value']) && is_string($item['value']) && $item['value'] !== '' ? $item['value'] : $source['value'];
        $source['label'] = isset($item['label']) && is_string($item['label']) && $item['label'] !== '' ? $item['label'] : $source['label'];
        $source['tone'] = isset($item['tone']) && is_string($item['tone']) && $item['tone'] !== '' ? $item['tone'] : $source['tone'];
        $source['depth'] = isset($item['depth']) && is_numeric($item['depth']) ? (int) $item['depth'] : $source['depth'];

        if ($target === 'top') {
            $top_stat = $source;
        } else {
            $bottom_stat = $source;
        }
    }
}

$parallax_depth = isset($banner_data['visual']['parallaxDepth']) && is_numeric($banner_data['visual']['parallaxDepth'])
    ? (int) $banner_data['visual']['parallaxDepth']
    : 18;
$phone_depth = isset($banner_data['visual']['phoneDepth']) && is_numeric($banner_data['visual']['phoneDepth'])
    ? (int) $banner_data['visual']['phoneDepth']
    : 26;

$main_image_alt = trim($title_line_1 . ' ' . $title_highlight . ' ' . $title_line_2);

$scroll_hint = isset($banner_data['scrollHint']) && is_string($banner_data['scrollHint']) && $banner_data['scrollHint'] !== ''
    ? $banner_data['scrollHint']
    : '';

$has_eyebrow = ($eyebrow_icon !== '' || $eyebrow_text !== '');
$has_title = ($title_line_1 !== '' || $title_highlight !== '' || $title_line_2 !== '');
$has_description = ($description !== '');
$has_primary_action = ($primary_text !== '' && $primary_url !== '');
$has_secondary_action = ($secondary_text !== '' && $secondary_url !== '');
$has_actions = ($has_primary_action || $has_secondary_action);
$has_trust = (!empty($avatars) || $trust_text !== '');
$top_stat_has_content = ($top_stat['icon'] !== '' || $top_stat['value'] !== '' || $top_stat['label'] !== '');
$bottom_stat_has_content = ($bottom_stat['icon'] !== '' || $bottom_stat['value'] !== '' || $bottom_stat['label'] !== '');
$has_visual = ($main_image !== '' || $preview_image !== '' || $top_stat_has_content || $bottom_stat_has_content);
$has_scroll = ($scroll_hint !== '');
$socials_label = $trust_text;

if (!$has_eyebrow && !$has_title && !$has_description && !$has_actions && !$has_trust && !$has_visual && !$has_scroll) {
    if (is_customize_preview()) {
    ?>
<section id="viora-banner" class="viora-banner" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}
?>

<section class="viora-banner" id="viora-banner">
    <?php if (is_customize_preview()) : ?>
    <div class="viora-banner__hover-outline" aria-hidden="true"></div>
    <script>
    (function() {
        var root = document.getElementById('viora-banner');
        var button = root ? root.querySelector('.viora-banner__customize-button') : null;
        if (!button || !window.parent || !window.parent.wp || !window.parent.wp.customize) {
            return;
        }

        button.addEventListener('click', function() {
            window.parent.wp.customize.section('viora_home_banner_section').focus();
        });
    })();
    </script>
    <?php endif; ?>

    <div class="container viora-banner__container">
        <div class="viora-banner__layout">
            <div class="viora-banner__content">
                <?php if ($has_eyebrow) : ?>
                <span class="viora-banner__eyebrow" data-banner-anim="eyebrow">
                    <?php if ($eyebrow_icon !== '') : ?>
                    <span class="viora-banner__eyebrow-icon" aria-hidden="true">
                        <img src="<?php echo esc_url($eyebrow_icon); ?>" alt="" loading="lazy" decoding="async">
                    </span>
                    <?php endif; ?>
                    <?php if ($eyebrow_text !== '') : ?>
                    <p><?php echo esc_html($eyebrow_text); ?></p>
                    <?php endif; ?>
                </span>
                <?php endif; ?>

                <?php if ($has_title) : ?>
                <h1 class="viora-banner__title" data-banner-anim="title">
                    <?php if ($title_line_1 !== '') : ?>
                    <span
                        class="viora-banner__title-part viora-banner__title-line1"><?php echo esc_html($title_line_1); ?></span>
                    <?php endif; ?>
                    <?php if ($title_highlight !== '') : ?>
                    <span
                        class="viora-banner__title-part viora-banner__title-highlight"><?php echo esc_html($title_highlight); ?></span>
                    <?php endif; ?>
                    <?php if ($title_line_2 !== '') : ?>
                    <span
                        class="viora-banner__title-part viora-banner__title-line2"><?php echo esc_html($title_line_2); ?></span>
                    <?php endif; ?>
                </h1>
                <?php endif; ?>

                <?php if ($has_description) : ?>
                <p class="viora-banner__description" data-banner-anim="description">
                    <?php echo esc_html($description); ?>
                </p>
                <?php endif; ?>

                <?php if ($has_actions) : ?>
                <div class="viora-banner__actions" data-banner-anim="actions">
                    <?php if ($has_primary_action) : ?>
                    <a class="viora-banner__button viora-banner__button--primary"
                        href="<?php echo esc_url($primary_url); ?>">
                        <span><?php echo esc_html($primary_text); ?></span>
                        <?php if ($primary_icon !== '') : ?>
                        <span class="viora-banner__button-icon" aria-hidden="true">
                            <img src="<?php echo esc_url($primary_icon); ?>" alt="" loading="lazy" decoding="async">
                        </span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>

                    <?php if ($has_secondary_action) : ?>
                    <a class="viora-banner__button viora-banner__button--secondary"
                        href="<?php echo esc_url($secondary_url); ?>">
                        <?php echo esc_html($secondary_text); ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($has_trust) : ?>
                <div class="viora-banner__socials" data-banner-anim="socials"
                    <?php echo $socials_label !== '' ? ' aria-label="' . esc_attr($socials_label) . '"' : ''; ?>>
                    <div class="viora-banner__divider" data-banner-anim="trust"></div>

                    <div class="viora-banner__trust" data-banner-anim="trust">
                        <?php if (!empty($avatars)) : ?>
                        <div class="viora-banner__avatars" aria-hidden="true">
                            <?php foreach ($avatars as $index => $avatar) :
                                        $avatar_class = 'viora-banner__avatar';
                                        if ($index === count($avatars) - 1) {
                                            $avatar_class .= ' viora-banner__avatar--count';
                                        }
                                    ?>
                            <span
                                class="<?php echo esc_attr($avatar_class); ?>"><?php echo esc_html((string) $avatar); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($trust_text !== '') : ?>
                        <p><?php echo esc_html($trust_text); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($has_visual) : ?>
            <div class="viora-banner__visual-wrap" data-banner-anim="visual">
                <div class="viora-banner__visual" data-banner-parallax
                    data-depth="<?php echo esc_attr($parallax_depth); ?>">
                    <?php if ($main_image !== '') : ?>
                    <img class="viora-banner__laptop" src="<?php echo esc_url($main_image); ?>"
                        alt="<?php echo esc_attr($main_image_alt); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>

                    <?php if ($top_stat_has_content) : ?>
                    <article class="viora-banner__stat-card viora-banner__stat-card--top" data-banner-float
                        data-depth="<?php echo esc_attr($top_stat['depth']); ?>">
                        <?php if ($top_stat['icon'] !== '') : ?>
                        <span
                            class="viora-banner__stat-icon viora-banner__stat-icon--<?php echo esc_attr($top_stat['tone']); ?>">
                            <img src="<?php echo esc_url($top_stat['icon']); ?>" alt="" loading="lazy" decoding="async">
                        </span>
                        <?php endif; ?>
                        <div>
                            <?php if ($top_stat['value'] !== '') : ?>
                            <p class="viora-banner__stat-value"><?php echo esc_html($top_stat['value']); ?></p>
                            <?php endif; ?>
                            <?php if ($top_stat['label'] !== '') : ?>
                            <p class="viora-banner__stat-label"><?php echo esc_html($top_stat['label']); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endif; ?>

                    <?php if ($bottom_stat_has_content) : ?>
                    <article class="viora-banner__stat-card viora-banner__stat-card--bottom" data-banner-float
                        data-depth="<?php echo esc_attr($bottom_stat['depth']); ?>">
                        <?php if ($bottom_stat['icon'] !== '') : ?>
                        <span
                            class="viora-banner__stat-icon viora-banner__stat-icon--<?php echo esc_attr($bottom_stat['tone']); ?>">
                            <img src="<?php echo esc_url($bottom_stat['icon']); ?>" alt="" loading="lazy"
                                decoding="async">
                        </span>
                        <?php endif; ?>
                        <div>
                            <?php if ($bottom_stat['value'] !== '') : ?>
                            <p class="viora-banner__stat-value"><?php echo esc_html($bottom_stat['value']); ?></p>
                            <?php endif; ?>
                            <?php if ($bottom_stat['label'] !== '') : ?>
                            <p class="viora-banner__stat-label"><?php echo esc_html($bottom_stat['label']); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endif; ?>
                </div>

                <?php if ($preview_image !== '') : ?>
                <aside class="viora-banner__phone" data-banner-float data-depth="<?php echo esc_attr($phone_depth); ?>"
                    aria-hidden="true">
                    <img src="<?php echo esc_url($preview_image); ?>" alt="" loading="lazy" decoding="async">
                </aside>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($has_scroll) : ?>
        <div class="viora-banner__scroll" data-banner-anim="scroll" aria-hidden="true">
            <p><?php echo esc_html($scroll_hint); ?></p>
            <span class="viora-banner__scroll-mouse">
                <span class="viora-banner__scroll-dot"></span>
            </span>
        </div>
        <?php endif; ?>
    </div>
</section>