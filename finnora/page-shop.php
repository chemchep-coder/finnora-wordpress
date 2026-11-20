<?php /* Template Name: FINNORA — Collection */
get_header(); $filter = sanitize_key(fin_input('fin_cat')); if (!isset(fin_categories()[$filter])) $filter = '';
?>
<section class="container section-top"><?php fin_breadcrumb('Products'); ?><header class="page-intro"><h1>Shop All Products</h1><p><?php echo esc_html($filter ? fin_categories()[$filter] : 'Browse clothing, accessories and gifts by product type.'); ?></p></header><nav class="filter-chips" aria-label="Product categories"><a <?php if (!$filter) echo 'aria-current="page"'; ?> href="<?php echo esc_url(fin_page_url('shop')); ?>">All</a><?php foreach (fin_categories() as $slug => $name) : ?><a <?php if ($filter === $slug) echo 'aria-current="page"'; ?> href="<?php echo esc_url(add_query_arg('fin_cat', $slug, fin_page_url('shop'))); ?>"><?php echo esc_html($name); ?></a><?php endforeach; ?></nav>
<?php
$args = ['status' => 'publish', 'limit' => 12, 'paginate' => true, 'page' => max(1, absint(fin_input('fin_pg'))), 'visibility' => 'visible'];
if ($filter) $args['category'] = [$filter];
$result = function_exists('wc_get_products') ? wc_get_products($args) : null;
if ($result && $result->products) {
    echo '<div class="collection-count">' . esc_html($result->total) . ' products</div><div class="woocommerce fin-products">'; woocommerce_product_loop_start();
    foreach ($result->products as $p) { $GLOBALS['post'] = get_post($p->get_id()); setup_postdata($GLOBALS['post']); $GLOBALS['product'] = $p; wc_get_template_part('content', 'product'); }
    woocommerce_product_loop_end(); echo '</div>'; wp_reset_postdata();
    echo '<nav class="pagination" aria-label="Pagination">' . wp_kses_post((string) paginate_links(['base' => add_query_arg('fin_pg', '%#%'), 'format' => '', 'current' => $args['page'], 'total' => $result->max_num_pages])) . '</nav>';
} else {
    $has_products = function_exists('wc_get_products') && wc_get_products(['status' => 'publish', 'limit' => 1]);
    if ($has_products) echo '<p>There are no products in this collection yet.</p>';
    else {
        $items = array_filter(fin_catalog(), fn($item) => !$filter || $filter === $item['cat']);
        foreach (['shelf', 'rail'] as $mode) fin_showroom(array_map('fin_experience_demo_item', array_filter($items, fn($item) => fin_demo_experience_mode($item) === $mode)), $mode);
        echo '<p class="catalog-note">Demo collection · Prices in USD · Not available to order</p><div class="product-grid">'; foreach ($items as $item) fin_product_card($item); echo '</div>';
    }
}
?></section><?php get_footer(); ?>
