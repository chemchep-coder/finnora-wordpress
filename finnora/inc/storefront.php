<?php
defined('ABSPATH') || exit;

function fin_setting_text($name, $default) { return get_theme_mod($name, $default); }
function fin_checkbox($value) { return (bool) $value; }
function fin_home_count($value) { $value = absint($value); return in_array($value, [4,8,12], true) ? $value : 8; }
// Browsers submit empty number inputs; WooCommerce treats an empty max_price as zero.
add_action('init', function () {
    foreach (['min_price','max_price'] as $key) {
        if (!array_key_exists($key, $_GET)) continue;
        $value = is_string($_GET[$key]) ? trim(wp_unslash($_GET[$key])) : '';
        if ($value === '' || !is_numeric($value) || (float) $value < 0) unset($_GET[$key], $_REQUEST[$key]);
    }
}, 1);
add_action('customize_register', function ($customizer) {
    $customizer->add_section('fin_shopping', ['title' => 'FINNORA — Shopping layout', 'priority' => 31]);
    $fields = [
        'fin_home_category_title' => ['Design topic section heading', 'Find your everyday favourite.', 'text'],
        'fin_home_collection_title' => ['Product section heading', 'Explore the collection', 'text'],
        'fin_home_collection_intro' => ['Product section description', 'Artful pieces for days out, desk corners and spaces that feel like you.', 'textarea'],
        'fin_home_process_title' => ['Shopping guide heading', 'From a favourite to your everyday.', 'text'],
        'fin_home_step1_title' => ['Step 1 heading', 'Find your piece', 'text'],
        'fin_home_step1_text' => ['Step 1 description', 'Browse by category or search for something you love.', 'textarea'],
        'fin_home_step2_title' => ['Step 2 heading', 'Make your selection', 'text'],
        'fin_home_step2_text' => ['Step 2 description', 'Choose the available size and colour. For clothing, explore the optional 2D preview.', 'textarea'],
        'fin_home_step3_title' => ['Step 3 heading', 'Review your bag', 'text'],
        'fin_home_step3_text' => ['Step 3 description', 'Check your choices before continuing. This storefront currently uses demo checkout.', 'textarea'],
        'fin_product_delivery' => ['Delivery information', 'This demo is not connected to production or shipping. Delivery estimates will be available when the store opens.', 'textarea'],
        'fin_product_care' => ['Care information', 'Follow the care label on the actual product. Materials and printing methods may require different care.', 'textarea'],
        'fin_product_returns' => ['Returns information', 'A returns policy has not been activated for this demonstration. Contact the store before placing a real order.', 'textarea'],
    ];
    foreach ($fields as $id => [$label, $default, $type]) {
        $customizer->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_textarea_field']);
        $customizer->add_control($id, ['label' => $label, 'section' => 'fin_shopping', 'type' => $type]);
    }
    foreach (['fin_show_categories' => ['Show design topic cards on the homepage', true], 'fin_show_process' => ['Show Choose–Personalize–Preview on the homepage', true], 'fin_show_studio' => ['Show the studio story section', true], 'fin_show_journal' => ['Show journal stories on the homepage', true], 'fin_shop_showroom' => ['Show display shelf and discovery rail above shop results', false]] as $id => [$label, $default]) {
        $customizer->add_setting($id, ['default' => $default, 'sanitize_callback' => 'fin_checkbox']);
        $customizer->add_control($id, ['label' => $label, 'section' => 'fin_shopping', 'type' => 'checkbox']);
    }
    $customizer->add_setting('fin_home_product_count', ['default' => 8, 'sanitize_callback' => 'fin_home_count']);
    $customizer->add_control('fin_home_product_count', ['label' => 'Number of homepage products', 'section' => 'fin_shopping', 'type' => 'select', 'choices' => [4 => '4 products', 8 => '8 products', 12 => '12 products']]);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('fin-shopping', get_template_directory_uri() . '/assets/shopping.css', ['finnora-studio'], filemtime(get_template_directory() . '/assets/shopping.css'));
}, 25);

function fin_home_categories() {
    $cards = [];
    $images = ['wear' => 'colour-tee', 'accessories' => 'canvas-tote', 'home' => 'art-cushion', 'art' => 'art-poster', 'stationery' => 'studio-notebook', 'drinkware' => 'paint-mug', 'tech' => 'phone-case'];
    if (taxonomy_exists('product_cat')) {
        $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 8, 'exclude' => [absint(get_option('default_product_cat'))]]);
        if (!is_wp_error($terms)) foreach ($terms as $term) {
            $image = wp_get_attachment_image_url(get_term_meta($term->term_id, 'thumbnail_id', true), 'medium_large');
            if (!$image && function_exists('wc_get_products')) {
                $products = wc_get_products(['status' => 'publish', 'visibility' => 'visible', 'category' => [$term->slug], 'limit' => 1]);
                if ($products) $image = wp_get_attachment_image_url($products[0]->get_image_id(), 'medium_large');
            }
            $cards[] = ['name' => $term->name, 'url' => get_term_link($term), 'image' => $image ?: fin_image($images[$term->slug] ?? 'studio-scene'), 'count' => $term->count];
        }
    }
    if (!$cards) foreach (fin_categories() as $slug => $name) $cards[] = ['name' => $name, 'url' => add_query_arg('fin_cat', $slug, fin_page_url('shop')), 'image' => fin_image($images[$slug]), 'count' => null];
    ?><section class="fin-category-section container"><div class="section-heading"><div><h2><?php echo esc_html(fin_setting_text('fin_home_category_title', 'Find your everyday favourite.')); ?></h2></div><a class="text-link" href="<?php echo esc_url(fin_shop_url()); ?>">Explore all products ↗</a></div><div class="fin-category-cards"><?php foreach ($cards as $card) : ?>
    <a href="<?php echo esc_url($card['url']); ?>"><div><img src="<?php echo esc_url($card['image']); ?>" width="360" height="360" loading="lazy" alt=""></div><span><strong><?php echo esc_html($card['name']); ?></strong><span aria-hidden="true">↗</span></span><?php if ($card['count'] !== null) : ?><small><?php echo esc_html($card['count']); ?> <?php echo (int) $card['count'] === 1 ? 'piece' : 'pieces'; ?></small><?php endif; ?></a>
    <?php endforeach; ?></div></section><?php
}
function fin_shopping_guide() {
    $steps = [1 => ['Find your piece','Browse by category or search for something you love.'],2 => ['Make your selection','Choose the available size and colour. For clothing, explore the optional 2D preview.'],3 => ['Review your bag','Check your choices before continuing. This storefront currently uses demo checkout.']];
    ?><section class="container fin-shopping-guide"><h2><?php echo esc_html(fin_setting_text('fin_home_process_title', 'From a favourite to your everyday.')); ?></h2><div><?php foreach ($steps as $n => [$title, $text]) : ?><article><span>0<?php echo esc_html($n); ?></span><h3><?php echo esc_html(fin_setting_text('fin_home_step'.$n.'_title', $title)); ?></h3><p><?php echo esc_html(fin_setting_text('fin_home_step'.$n.'_text', $text)); ?></p></article><?php endforeach; ?></div></section><?php
}
function fin_is_product_archive() { return function_exists('is_shop') && (is_shop() || is_product_taxonomy()); }
function fin_shop_filters() {
    if (!fin_is_product_archive()) return;
    $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => [absint(get_option('default_product_cat'))]]);
    $category = sanitize_title(fin_input('fin_category'));
    if (!$category && is_product_category()) { $term = get_queried_object(); $category = $term->slug ?? ''; }
    $min = fin_input('min_price'); $max = fin_input('max_price');
    ?><div class="fin-shop-layout"><aside class="fin-shop-sidebar"><details open><summary>Filter Products</summary><form class="fin-shop-filter-form" method="get" action="<?php echo esc_url(fin_shop_url()); ?>">
    <?php foreach(fin_filter_context() as $key=>$value): ?><input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>"><?php endforeach; ?>
    <label for="fin-filter-category">Product type</label><select id="fin-filter-category" name="fin_category"><option value="">All products</option><?php if (!is_wp_error($terms)) foreach ($terms as $term) : ?><option value="<?php echo esc_attr($term->slug); ?>" <?php selected($category, $term->slug); ?>><?php $parent_name='';if($term->parent)foreach($terms as $parent_term)if($parent_term->term_id===$term->parent){$parent_name=$parent_term->name.' / ';break;}echo esc_html($parent_name.$term->name); ?></option><?php endforeach; ?></select>
    <fieldset><legend>Price range (<?php echo esc_html(get_woocommerce_currency()); ?>)</legend><div class="fin-price-range"><label>From<input type="number" name="min_price" min="0" step="0.01" inputmode="decimal" value="<?php echo esc_attr(is_numeric($min) ? $min : ''); ?>" placeholder="0"></label><label>To<input type="number" name="max_price" min="0" step="0.01" inputmode="decimal" value="<?php echo esc_attr(is_numeric($max) ? $max : ''); ?>" placeholder="Any"></label></div></fieldset>
    <?php if ($order = sanitize_key(fin_input('orderby'))) : ?><input type="hidden" name="orderby" value="<?php echo esc_attr($order); ?>"><?php endif; ?>
    <button class="button" type="submit">Apply Filters</button><a class="text-link" href="<?php echo esc_url(add_query_arg(fin_filter_context(),fin_shop_url())); ?>">Clear Filters</a></form></details><p class="fin-filter-help">Filter by product type and price. Open a product to see available colors and sizes.</p></aside><div class="fin-shop-results"><?php
}
// Start outside the loop so the filter remains reachable when there are zero results.
add_action('woocommerce_before_main_content', function () { if (fin_is_product_archive()) $GLOBALS['fin_filter_shell'] = false; }, 15);
add_action('woocommerce_before_shop_loop', function () { fin_shop_filters(); $GLOBALS['fin_filter_shell'] = true; }, 4);
add_action('woocommerce_after_shop_loop', function () { if (!empty($GLOBALS['fin_filter_shell'])) { echo '</div></div>'; $GLOBALS['fin_filter_shell'] = false; } }, 50);
add_action('woocommerce_no_products_found', function () { if (fin_is_product_archive()) { remove_action('woocommerce_no_products_found', 'wc_no_products_found', 10); fin_shop_filters(); echo '<p>No pieces match these filters. Try another category or widen the price range.</p></div></div>'; } }, 5);
add_action('woocommerce_product_query', function ($query) {
    $category = sanitize_title(fin_input('fin_category'));
    if (!$category || !term_exists($category, 'product_cat')) return;
    $tax_query = $query->get('tax_query') ?: [];
    $tax_query[] = ['taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $category];
    $query->set('tax_query', $tax_query);
});
function fin_filter_context() {
    $context=[];
    $search=sanitize_text_field(fin_input('s'));
    if($search!==''){$context['s']=$search;$context['post_type']='product';}
    $collection=fin_input('fin_collection');
    if(in_array($collection,['gifts','home-gifts'],true))$context['fin_collection']=$collection;
    return $context;
}
add_filter('woocommerce_product_single_add_to_cart_text', fn($text) => 'Add to Bag');
add_action('woocommerce_single_product_summary', function () {
    ?><div class="fin-product-guidance"><?php foreach (['delivery' => ['Delivery','This demo is not connected to production or shipping. Delivery estimates will be available when the store opens.'],'care' => ['Product care','Follow the care label on the actual product. Materials and printing methods may require different care.'],'returns' => ['Returns','A returns policy has not been activated for this demonstration. Contact the store before placing a real order.']] as $key => [$title,$default]) : ?><details><summary><?php echo esc_html($title); ?></summary><p><?php echo esc_html(fin_setting_text('fin_product_'.$key,$default)); ?></p></details><?php endforeach; ?><a class="text-link" href="<?php echo esc_url(fin_page_url('contact')); ?>">Need a hand? Contact us ↗</a></div><?php
}, 45);
add_filter('woocommerce_product_add_to_cart_text', function ($text, $product) {
    if($product->is_type('variable'))return 'Choose Options';
    if($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock())return 'Add to Bag';
    return $text;
}, 10, 2);
