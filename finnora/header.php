<?php defined('ABSPATH') || exit; ?><!doctype html>
<html class="no-js" <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><script>document.documentElement.classList.remove("no-js");</script><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="site-header"><div class="header-row container">
    <div class="logo-wrap"><?php if (has_custom_logo()) the_custom_logo(); else { ?><a class="wordmark" href="<?php echo esc_url(home_url('/')); ?>" rel="home">FINNORA<small>STUDIO</small></a><?php } ?></div>
    <button class="menu-toggle icon-button" aria-label="Open main menu" aria-controls="primary-navigation" aria-expanded="false">☰ <span>Menu</span></button>
    <nav id="primary-navigation" class="primary-navigation" aria-label="Main navigation"><?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => 'fin_editorial_navigation', 'depth' => 2]); ?></nav>
    <div class="header-actions"><button class="search-toggle icon-button" aria-expanded="false" aria-controls="header-search" aria-label="Open search"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="10" cy="10" r="6.5"/><path d="m15 15 6 6"/></svg></button><a class="bag-link" href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : fin_page_url('shop')); ?>" aria-label="<?php echo function_exists('WC') ? 'Shopping bag' : 'Explore the collection'; ?>"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M5 7h14l2 15H3L5 7ZM8 7V5a4 4 0 0 1 8 0v2"/></svg><?php if (function_exists('WC') && WC()->cart) : ?><span class="fin-cart-count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span><?php endif; ?><span class="fin-icon-label">Bag</span></a></div>
</div><div id="header-search" class="header-search fin-search-pinned" data-fin-pinned><div class="container"><?php get_search_form(); ?></div></div></header>
<main id="main" tabindex="-1">
