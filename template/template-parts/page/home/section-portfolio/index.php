<?php
$page_id = get_queried_object_id();

$state = function_exists('viora_home_portfolio_get_data_for_front')
    ? viora_home_portfolio_get_data_for_front($page_id)
    : array('enabled' => 1, 'data' => array());

$enabled = isset($state['enabled']) ? absint($state['enabled']) : 1;
$portfolio_data = isset($state['data']) && is_array($state['data']) ? $state['data'] : array();

if ($enabled !== 1) {
    if (is_customize_preview()) {
?>
        <section id="viora-home-portfolio" class="viora-home-portfolio" data-no-fallback="1" style="display:none"></section>
    <?php
    }
    return;
}

if (function_exists('viora_home_portfolio_sanitize_data')) {
    $portfolio_data = viora_home_portfolio_sanitize_data($portfolio_data);
}

$section_title = isset($portfolio_data['title']) && is_string($portfolio_data['title']) && trim($portfolio_data['title']) !== ''
    ? $portfolio_data['title']
    : __('Our Portfolio', 'viora');

$portfolio_query = new WP_Query(array(
    'post_type'           => 'portfolio',
    'post_status'         => 'publish',
    'posts_per_page'      => 6,
    'ignore_sticky_posts' => true,
));

if (!$portfolio_query->have_posts()) {
    if (is_customize_preview()) {
    ?>
        <section id="viora-home-portfolio" class="viora-home-portfolio" data-no-fallback="1" style="display:none"></section>
<?php
    }
    return;
}
?>

<section id="viora-home-portfolio" class="viora-home-portfolio" data-aos="fade-up">
    <?php if (is_customize_preview()) : ?>
        <div class="viora-home-portfolio__hover-outline" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container viora-home-portfolio__container">
        <header class="viora-home-portfolio__header">
            <h2 class="viora-home-portfolio__title"><?php echo esc_html($section_title); ?></h2>
        </header>

        <div class="swiper viora-home-portfolio__slider" data-portfolio-slider>
            <div class="swiper-wrapper">
                <?php
                while ($portfolio_query->have_posts()) :
                    $portfolio_query->the_post();

                    $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    if (!is_string($thumb_url) || $thumb_url === '') {
                        $thumb_url = get_theme_file_uri('/assets/images/image_banner.png');
                    }

                    $kicker = get_the_excerpt();
                    if (!is_string($kicker) || trim($kicker) === '') {
                        $kicker = wp_strip_all_tags((string) get_the_content());
                    }
                    $kicker = wp_trim_words((string) $kicker, 4, '');
                    $kicker = strtoupper((string) $kicker);
                ?>
                    <article <?php post_class('swiper-slide viora-home-portfolio__slide'); ?>>
                        <a class="viora-home-portfolio__card-link" href="<?php the_permalink(); ?>"
                            aria-label="<?php echo esc_attr(get_the_title()); ?>">
                            <img class="viora-home-portfolio__card-image" src="<?php echo esc_url($thumb_url); ?>"
                                alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                            <div class="viora-home-portfolio__overlay"></div>
                            <div class="viora-home-portfolio__card-content">
                                <?php if ($kicker !== '') : ?>
                                    <p class="viora-home-portfolio__kicker"><?php echo esc_html($kicker); ?></p>
                                <?php endif; ?>
                                <h3 class="viora-home-portfolio__card-title"><?php the_title(); ?></h3>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<?php wp_reset_postdata(); ?>