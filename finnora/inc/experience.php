<?php
defined('ABSPATH') || exit;

function fin_experience_modes() {
    return ['auto' => 'Automatic', 'shelf' => 'Display shelf', 'rail' => 'Discovery rail', 'try' => '2D outfit preview', 'off' => 'Standard product page'];
}
add_action('woocommerce_product_options_general_product_data', function () {
    woocommerce_wp_select(['id' => '_fin_experience', 'label' => 'FINNORA experience', 'options' => fin_experience_modes(), 'description' => 'Choose automatically from the sample product or clothing category.']);
    woocommerce_wp_text_input(['id' => '_fin_try_image', 'label' => 'Transparent garment image URL', 'type' => 'url', 'description' => 'For the 2D outfit preview. Paste a transparent PNG/WebP URL from the Media Library.']);
});
add_action('woocommerce_admin_process_product_object', function ($product) {
    if (isset($_POST['_fin_experience']) && is_string($_POST['_fin_experience'])) {
        $mode = sanitize_key(wp_unslash($_POST['_fin_experience']));
        if (isset(fin_experience_modes()[$mode])) $product->update_meta_data('_fin_experience', $mode);
    }
    if (isset($_POST['_fin_try_image']) && is_string($_POST['_fin_try_image'])) $product->update_meta_data('_fin_try_image', esc_url_raw(wp_unslash($_POST['_fin_try_image']), ['http', 'https']));
});
function fin_experience_mode($product) {
    $mode = $product->get_meta('_fin_experience');
    if ($mode && $mode !== 'auto' && isset(fin_experience_modes()[$mode])) return $mode;
    $sku = $product->get_sku();
    if (in_array($sku, ['FIN-colour-tee', 'FIN-studio-sweat'], true) || has_term('wear', 'product_cat', $product->get_id())) return 'try';
    return in_array($sku, ['FIN-phone-case', 'FIN-studio-notebook', 'FIN-sticker-sheet'], true) ? 'rail' : 'shelf';
}
function fin_demo_experience_mode($item) {
    if ($item['cat'] === 'wear') return 'try';
    return in_array($item['id'], ['phone-case', 'studio-notebook', 'sticker-sheet'], true) ? 'rail' : 'shelf';
}
function fin_experience_item($product) {
    return ['name' => $product->get_name(), 'url' => $product->get_permalink(), 'image' => wp_get_attachment_image_url($product->get_image_id(), 'large') ?: wc_placeholder_img_src('large'), 'price' => trim(wp_strip_all_tags(html_entity_decode($product->get_price_html(), ENT_QUOTES, 'UTF-8')))];
}
function fin_experience_demo_item($item) {
    return ['name' => $item['vi'], 'url' => fin_product_url($item), 'image' => fin_image($item['id']), 'price' => '$' . number_format_i18n($item['price'], 2) . ' · demo price'];
}
function fin_showroom($items, $mode) {
    if (!$items) return;
    $id = wp_unique_id('fin-room-'); $first = reset($items);
    $is_rail = $mode === 'rail';
    ?><section class="fin-showroom fin-showroom--<?php echo esc_attr($mode); ?>" data-fin-room aria-labelledby="<?php echo esc_attr($id); ?>">
        <header class="fin-room-head"><div><h2 id="<?php echo esc_attr($id); ?>"><?php echo $is_rail ? 'Find your next favourite' : 'On the FINNORA shelf'; ?></h2></div><p><?php echo $is_rail ? 'Browse the rail and select a piece, or use the arrows.' : 'Select a piece for a closer look.'; ?></p></header>
        <div class="fin-room-layout"><div class="fin-room-controls"><div class="fin-room-items" data-fin-items><?php foreach (array_values($items) as $index => $item) : ?>
            <a class="fin-room-item" href="<?php echo esc_url($item['url']); ?>" data-fin-item data-image="<?php echo esc_url($item['image']); ?>" data-price="<?php echo esc_attr($item['price']); ?>" <?php if (!$index) echo 'aria-current="true"'; ?>><img src="<?php echo esc_url($item['image']); ?>" width="180" height="180" loading="lazy" alt=""><span><?php echo esc_html($item['name']); ?></span></a>
        <?php endforeach; ?></div><div class="fin-room-arrows" hidden><button type="button" data-fin-step="-1" aria-label="Previous piece">←</button><button type="button" data-fin-step="1" aria-label="Next piece">→</button></div></div>
        <div class="fin-room-stage"><img data-fin-feature src="<?php echo esc_url($first['image']); ?>" width="450" height="450" alt="" loading="lazy"><div class="fin-room-caption"><div role="status"><h3 data-fin-name><?php echo esc_html($first['name']); ?></h3><p data-fin-price><?php echo esc_html($first['price']); ?></p></div><a class="text-link" data-fin-link href="<?php echo esc_url($first['url']); ?>">View product ↗</a></div></div></div>
    </section><?php
}
function fin_try_on($name, $overlay) {
    if (!$overlay) return;
    $id = wp_unique_id('fin-try-'); ?>
    <div class="fin-try" data-fin-try data-fin-sizing="<?php echo esc_attr(wp_json_encode(fin_us_reference_sizes(str_contains(strtolower($name),'sweat')))); ?>"><button type="button" class="button button-outline" data-fin-open hidden>Preview on you · 2D ↗</button>
    <dialog aria-labelledby="<?php echo esc_attr($id); ?>"><div class="fin-try-head"><div><h2 id="<?php echo esc_attr($id); ?>">Try <?php echo esc_html($name); ?></h2></div><button type="button" data-fin-close aria-label="Close">×</button></div>
    <p>Use your camera or choose a photo, then adjust the garment position. This manual 2D preview does not track your body or predict fit. Calibrate the scale to a known garment in your photo; changing size then changes its relative width and length using the reference chart. Photos and video stay on your device.</p>
    <div class="fin-try-options" data-fin-try-options></div><p data-fin-size-status role="status">Select a size to compare its proportions. This is an illustrative reference, not a fit prediction.</p><div class="fin-try-actions"><button type="button" class="button" data-fin-camera>Start camera</button><label>Choose a photo<input type="file" accept="image/jpeg,image/png,image/webp" data-fin-file></label></div>
    <p data-fin-status role="status">Start the camera or choose a photo to begin.</p><div class="fin-try-layout"><div class="fin-try-stage"><video muted playsinline hidden></video><img data-fin-photo alt="Your outfit preview photo" hidden><img data-fin-overlay src="<?php echo esc_url($overlay); ?>" alt="" hidden></div>
    <div class="fin-try-sliders"><label>Horizontal position<input type="range" min="5" max="95" value="50" data-fin-range="x"></label><label>Vertical position<input type="range" min="5" max="95" value="57" data-fin-range="y"></label><label>Scale<input type="range" min="15" max="120" value="52" data-fin-range="w"></label></div></div></dialog></div><?php
}
function fin_try_asset($id) {
    return in_array($id, ['colour-tee', 'studio-sweat'], true) ? get_template_directory_uri() . '/assets/images/try-' . $id . '.svg' : '';
}
add_action('wp_enqueue_scripts', function () {
    if (!is_singular('product') && !is_page_template('page-shop.php') && !fin_qv('fin_product') && fin_qv('fin_page') !== 'shop' && !(function_exists('is_woocommerce') && is_woocommerce())) return;
    $dir = get_template_directory(); $uri = get_template_directory_uri();
    wp_enqueue_style('fin-experience', $uri . '/assets/experience.css', ['finnora-studio'], filemtime($dir . '/assets/experience.css'));
    wp_enqueue_script('fin-experience', $uri . '/assets/experience.js', [], filemtime($dir . '/assets/experience.js'), true);
}, 20);
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product || fin_experience_mode($product) !== 'try') return;
    $sku = $product->get_sku();
    $overlay = $product->get_meta('_fin_try_image') ?: (str_starts_with($sku, 'FIN-') ? fin_try_asset(substr($sku, 4)) : '');
    fin_try_on($product->get_name(), $overlay);
}, 29);
add_action('woocommerce_after_single_product_summary', function () {
    global $product;
    if (!$product || !in_array($mode = fin_experience_mode($product), ['shelf', 'rail'], true)) return;
    $args = ['status' => 'publish', 'visibility' => 'visible', 'limit' => 6, 'exclude' => [$product->get_id()]];
    $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
    if ($categories && !is_wp_error($categories)) $args['category'] = $categories;
    $items = [fin_experience_item($product)];
    foreach (wc_get_products($args) as $p) if (fin_experience_mode($p) === $mode) $items[] = fin_experience_item($p);
    fin_showroom($items, $mode);
}, 15);
add_action('woocommerce_before_shop_loop', function () {
    if (!is_shop() || is_search() || is_paged() || !get_theme_mod('fin_shop_showroom', false)) return;
    global $wp_query;
    $rooms = ['shelf' => [], 'rail' => []];
    foreach ($wp_query->posts as $post) {
        $product = wc_get_product($post->ID);
        if (!$product || !$product->is_visible()) continue;
        $mode = fin_experience_mode($product);
        if (isset($rooms[$mode]) && count($rooms[$mode]) < 6) $rooms[$mode][] = fin_experience_item($product);
    }
    foreach ($rooms as $mode => $items) fin_showroom($items, $mode);
}, 6);
