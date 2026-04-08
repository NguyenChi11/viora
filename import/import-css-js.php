<?php
$version = WP_DEBUG ? time() : wp_get_theme()->get('Version');
if (!defined('THEME_VERSION')) {
    define('THEME_VERSION', $version);
}

function viora_add_preconnects($hints, $relation_type)
{
    if ($relation_type === 'preconnect') {
        $hints[] = array(
            'href'        => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous',
        );
        $hints[] = array(
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $hints;
}
add_filter('wp_resource_hints', 'viora_add_preconnects', 10, 2);

function viora_enqueue_lib()
{
    wp_enqueue_style('viora-font-quicksand', 'https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap', array(), THEME_VERSION);
    wp_enqueue_style('viora-font-inter', 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap', array(), THEME_VERSION);
    wp_enqueue_style('viora-font-barlow', 'https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap', array(), THEME_VERSION);
    wp_enqueue_style('viora-font-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap', array(), THEME_VERSION);
    wp_enqueue_style('viora-font-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap', array(), THEME_VERSION);
    wp_enqueue_style('viora-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), THEME_VERSION);

    wp_enqueue_style('viora-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), THEME_VERSION);
    wp_enqueue_script('viora-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), THEME_VERSION, true);

    wp_enqueue_script('viora-gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true);
}
add_action('wp_enqueue_scripts', 'viora_enqueue_lib', 1000);

function viora_enqueue_custom_assets()
{
    $version = WP_DEBUG ? time() : wp_get_theme()->get('Version');

    $is_home_context = (
        is_front_page()
        || is_page_template('home-page.php')
        || is_customize_preview()
    );

    $is_portfolio_single = is_singular('portfolio');

    $is_aos_context = (
        is_front_page()
        || is_page_template('home-page.php')
        || is_singular()
        || is_404()
    );

    $asset_map = array(
        array(
            'type'      => 'style',
            'handle'    => 'viora-reset-style',
            'src'       => get_theme_file_uri('/assets/css/_reset.css'),
            'deps'      => array(),
            'ver'       => filemtime(get_theme_file_path('/assets/css/_reset.css')),
            'in_footer' => false,
            'condition' => file_exists(get_theme_file_path('/assets/css/_reset.css')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-style',
            'src'       => get_stylesheet_uri(),
            'deps'      => array('viora-reset-style'),
            'ver'       => $version,
            'in_footer' => false,
            'condition' => true,
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-global-style',
            'src'       => get_theme_file_uri('/assets/css/global.css'),
            'deps'      => array(),
            'ver'       => $version,
            'in_footer' => false,
            'condition' => file_exists(get_theme_file_path('/assets/css/global.css')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-home-banner-style',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-banner/style.css'),
            'deps'      => array('viora-global-style'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-banner/style.css')),
            'in_footer' => false,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-banner/style.css')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-home-banner-script',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-banner/script.js'),
            'deps'      => is_customize_preview() ? array('viora-gsap', 'customize-preview') : array('viora-gsap'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-banner/script.js')),
            'in_footer' => true,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-banner/script.js')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-home-service-style',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-service/style.css'),
            'deps'      => array('viora-global-style'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-service/style.css')),
            'in_footer' => false,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-service/style.css')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-home-portfolio-style',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-potfolio/style.css'),
            'deps'      => array('viora-global-style'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-potfolio/style.css')),
            'in_footer' => false,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-potfolio/style.css')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-portfolio-single-style',
            'src'       => get_theme_file_uri('/template/template-parts/single/portfolio/style.css'),
            'deps'      => array('viora-global-style'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/single/portfolio/style.css')),
            'in_footer' => false,
            'condition' => $is_portfolio_single && file_exists(get_theme_file_path('/template/template-parts/single/portfolio/style.css')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-home-service-script',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-service/script.js'),
            'deps'      => array('viora-gsap', 'viora-swiper'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-service/script.js')),
            'in_footer' => true,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-service/script.js')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-home-portfolio-script',
            'src'       => get_theme_file_uri('/template/template-parts/page/home/section-potfolio/script.js'),
            'deps'      => array('viora-gsap', 'viora-swiper'),
            'ver'       => filemtime(get_theme_file_path('/template/template-parts/page/home/section-potfolio/script.js')),
            'in_footer' => true,
            'condition' => $is_home_context && file_exists(get_theme_file_path('/template/template-parts/page/home/section-potfolio/script.js')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-aos',
            'src'       => 'https://unpkg.com/aos@2.3.4/dist/aos.css',
            'deps'      => array(),
            'ver'       => '2.3.4',
            'in_footer' => false,
            'condition' => $is_aos_context,
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-aos',
            'src'       => 'https://unpkg.com/aos@2.3.4/dist/aos.js',
            'deps'      => array(),
            'ver'       => '2.3.4',
            'in_footer' => true,
            'condition' => $is_aos_context,
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-aos-init',
            'src'       => get_theme_file_uri('/assets/js/aos-init.js'),
            'deps'      => array('viora-aos'),
            'ver'       => $version,
            'in_footer' => true,
            'condition' => $is_aos_context && file_exists(get_theme_file_path('/assets/js/aos-init.js')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-header-style',
            'src'       => get_theme_file_uri('/template/template-parts/header/styles.css'),
            'deps'      => array('viora-style'),
            'ver'       => $version,
            'in_footer' => false,
            'condition' => file_exists(get_theme_file_path('/template/template-parts/header/styles.css')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-header-data',
            'src'       => get_theme_file_uri('/assets/data/header-data.js'),
            'deps'      => array(),
            'ver'       => $version,
            'in_footer' => true,
            'condition' => file_exists(get_theme_file_path('/assets/data/header-data.js')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-header-script',
            'src'       => get_theme_file_uri('/template/template-parts/header/scripts.js'),
            'deps'      => array('viora-header-data'),
            'ver'       => $version,
            'in_footer' => true,
            'condition' => file_exists(get_theme_file_path('/template/template-parts/header/scripts.js')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-cart',
            'src'       => get_theme_file_uri('/assets/js/cart.js'),
            'deps'      => array(),
            'ver'       => $version,
            'in_footer' => true,
            'condition' => class_exists('WooCommerce') && file_exists(get_theme_file_path('/assets/js/cart.js')),
        ),
        array(
            'type'      => 'style',
            'handle'    => 'viora-cart-dropdown-style',
            'src'       => get_theme_file_uri('/template/template-parts/header/cart/style.css'),
            'deps'      => array(),
            'ver'       => $version,
            'in_footer' => false,
            'condition' => class_exists('WooCommerce') && file_exists(get_theme_file_path('/template/template-parts/header/cart/style.css')),
        ),
        array(
            'type'      => 'script',
            'handle'    => 'viora-cart-dropdown-script',
            'src'       => get_theme_file_uri('/template/template-parts/header/cart/script.js'),
            'deps'      => array('viora-cart'),
            'ver'       => $version,
            'in_footer' => true,
            'condition' => class_exists('WooCommerce') && file_exists(get_theme_file_path('/template/template-parts/header/cart/script.js')),
        ),
    );

    foreach ($asset_map as $asset) {
        if (isset($asset['condition']) && !$asset['condition']) {
            continue;
        }

        $deps = isset($asset['deps']) ? $asset['deps'] : array();
        $ver = isset($asset['ver']) ? $asset['ver'] : $version;
        $in_footer = isset($asset['in_footer']) ? $asset['in_footer'] : false;

        if ($asset['type'] === 'style') {
            wp_enqueue_style($asset['handle'], $asset['src'], $deps, $ver);
        }

        if ($asset['type'] === 'script') {
            wp_enqueue_script($asset['handle'], $asset['src'], $deps, $ver, $in_footer);
        }
    }
}
add_action('wp_enqueue_scripts', 'viora_enqueue_custom_assets', 1001);

function viora_customize_controls_scripts()
{
    $path = get_theme_file_path('/assets/js/customizer-section-focus.js');
    if (!file_exists($path)) {
        return;
    }

    $version = WP_DEBUG ? time() : wp_get_theme()->get('Version');
    wp_enqueue_script(
        'viora-customizer-section-focus',
        get_theme_file_uri('/assets/js/customizer-section-focus.js'),
        array('customize-controls'),
        $version,
        true
    );
}
add_action('customize_controls_enqueue_scripts', 'viora_customize_controls_scripts');

function viora_customize_preview_scripts()
{
    $path = get_theme_file_path('/assets/js/customizer-preview-outline.js');
    if (!file_exists($path)) {
        return;
    }

    $version = WP_DEBUG ? time() : wp_get_theme()->get('Version');
    wp_enqueue_script(
        'viora-customizer-preview-outline',
        get_theme_file_uri('/assets/js/customizer-preview-outline.js'),
        array('customize-preview'),
        $version,
        true
    );
}
add_action('customize_preview_init', 'viora_customize_preview_scripts');
