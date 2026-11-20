<?php
get_header();
do_action('woocommerce_before_main_content');
if (!is_singular('product')) {
    add_filter('woocommerce_show_page_title', '__return_false');
    ?><header class="page-intro wc-intro"><h1><?php if (is_shop() && in_array(fin_input('fin_collection'),['gifts','home-gifts'],true)) : ?>Home &amp; Gifts<?php elseif (is_shop()) : ?>Christmas clothing &amp; gifts<?php else : echo esc_html(woocommerce_page_title(false)); endif; ?></h1></header><?php
}
woocommerce_content();
do_action('woocommerce_after_main_content');
get_footer();
