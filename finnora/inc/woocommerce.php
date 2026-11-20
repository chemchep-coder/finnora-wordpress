<?php
defined('ABSPATH') || exit;
// Keep WooCommerce's current templates/hooks for variation, stock, taxes and cart behavior.
add_action('after_setup_theme', function () {
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    add_action('woocommerce_before_main_content', function () { echo '<div class="container wc-page section-top">'; }, 10);
    add_action('woocommerce_after_main_content', function () { echo '</div>'; }, 10);
});
add_filter('loop_shop_columns', fn() => 4);
add_filter('loop_shop_per_page', fn() => 12);
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    if (function_exists('WC') && WC()->cart) $fragments['span.fin-cart-count'] = '<span class="fin-cart-count">' . esc_html(WC()->cart->get_cart_contents_count()) . '</span>';
    return $fragments;
});
add_action('woocommerce_before_shop_loop', function () {
    $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 12]);
    if (is_wp_error($terms)) return;
    echo '<nav class="filter-chips" aria-label="Product categories"><a href="' . esc_url(fin_shop_url()) . '">All products</a>';
    foreach ($terms as $term) echo '<a ' . (is_tax('product_cat', $term->term_id) ? 'aria-current="page" ' : '') . 'href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
    echo '</nav>';
}, 5);
// Keep product archive headers concise; category descriptions remain editable in admin.
add_action('wp', function () {
    remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
});
// Direct link to the first product grid for compact mobile browsing.
add_filter('woocommerce_product_loop_start', function ($html) {
    static $first = true;
    if ($first) { $first = false; return preg_replace('/<ul\b/', '<ul id="fin-product-grid"', $html, 1); }
    return $html;
});
add_action('wp', function () { remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20); });
