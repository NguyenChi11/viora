<?php
$logo_id = (int) get_theme_mod('header_logo', 0);
$title = (string) get_theme_mod('viora_header_title', '');
if ($title === '') {
    $title = (string) get_theme_mod('header_text', '');
}
if (!is_scalar($title) || trim((string) $title) === '') {
    $title = (string) get_bloginfo('name');
}

$description = (string) get_theme_mod('viora_header_description', '');
if ($description === '') {
    $description = (string) get_theme_mod('header_description', '');
}

$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
if (!$logo_url) {
    $logo_url = get_theme_file_uri('/assets/images/logo.png');
}

$cart_url = home_url('/cart/');
if (class_exists('WooCommerce')) {
    $cart_page_id = (int) get_option('woocommerce_cart_page_id');
    if ($cart_page_id > 0) {
        $maybe_cart_url = get_permalink($cart_page_id);
        if (is_string($maybe_cart_url) && $maybe_cart_url !== '') {
            $cart_url = $maybe_cart_url;
        }
    }
}

$cart_count = 0;
if (isset($GLOBALS['woocommerce']) && isset($GLOBALS['woocommerce']->cart) && $GLOBALS['woocommerce']->cart) {
    $cart_count = (int) $GLOBALS['woocommerce']->cart->get_cart_contents_count();
}

$contact_page = get_page_by_path('contact-us');
if (!$contact_page) {
    $contact_page = get_page_by_path('contact');
}
$contact_url = $contact_page ? get_permalink($contact_page->ID) : home_url('/contact-us/');
?>

<header id="masthead" class="site-header">
    <div class="site-header__inner">
        <div class="site-header__inner--left">
            <a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>"
                aria-label="<?php esc_attr_e('Back to home', 'viora'); ?>">
                <span class="site-brand__logo-wrap">
                    <img class="site-brand__logo" src="<?php echo esc_url($logo_url); ?>"
                        alt="<?php echo esc_attr($title); ?>">
                </span>
                <span class="site-brand__text"><?php echo esc_html($title); ?></span>
            </a>

            <button class="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-sidebar">
                <span class="mobile-menu-toggle__bar"></span>
                <span class="mobile-menu-toggle__bar"></span>
                <span class="mobile-menu-toggle__bar"></span>
            </button>

            <nav id="site-navigation" class="main-navigation"
                aria-label="<?php esc_attr_e('Primary menu', 'viora'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>
        </div>

        <div class="site-header__actions">
            <a href="<?php echo esc_url($cart_url); ?>" class="header-cart-button"
                aria-label="<?php esc_attr_e('View cart', 'viora'); ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M2 3.25h2.1l1.45 9.35a2 2 0 0 0 1.98 1.7h9.95a2 2 0 0 0 1.95-1.53l1.29-5.18H6.08"
                        fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="1.8" />
                    <circle cx="9.1" cy="19.2" r="1.55" fill="currentColor" />
                    <circle cx="17" cy="19.2" r="1.55" fill="currentColor" />
                </svg>
                <span
                    class="header-cart-count<?php echo $cart_count === 0 ? ' header-cart-count--hidden' : ''; ?>"><?php echo esc_html($cart_count); ?></span>
            </a>

            <a href="<?php echo esc_url($contact_url); ?>"
                class="header-contact-btn"><?php esc_html_e('Contact Now', 'viora'); ?></a>
        </div>
    </div>

    <aside id="mobile-sidebar" class="mobile-sidebar" aria-hidden="true">
        <div class="mobile-sidebar__header">
            <span class="mobile-sidebar__title"><?php esc_html_e('Menu', 'viora'); ?></span>
            <button class="mobile-sidebar-close"
                aria-label="<?php esc_attr_e('Close menu', 'viora'); ?>">&times;</button>
        </div>
        <div class="mobile-sidebar__content">
            <nav class="mobile-navigation" aria-label="<?php esc_attr_e('Mobile menu', 'viora'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'mobile-primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>
            <div class="mobile-sidebar__actions">
                <a href="<?php echo esc_url($cart_url); ?>"
                    class="mobile-cart-link"><?php esc_html_e('View Cart', 'viora'); ?></a>
                <a href="<?php echo esc_url($contact_url); ?>"
                    class="header-contact-btn"><?php esc_html_e('Contact Now', 'viora'); ?></a>
            </div>
        </div>
    </aside>
    <div class="mobile-sidebar-backdrop"></div>
</header>