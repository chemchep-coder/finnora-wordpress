<?php
defined('ABSPATH') || exit;
// Add only missing pages. Existing content, menus, checkout and store settings are retained.
function fin_create_pages() {
    $pages = ['about' => ['About FINNORA', 'page-about.php'], 'contact' => ['Contact', 'page-contact.php'], 'journal' => ['Journal', 'home.php'], 'shop' => ['Collection', 'page-shop.php']];
    $ids = get_option('fin_studio_pages', []);
    foreach ($pages as $key => [$title, $template]) {
        if (!empty($ids[$key]) && get_post_status($ids[$key])) continue;
        // A dedicated slug prevents hijacking existing pages named about/contact/shop.
        $existing = get_page_by_path('finnora-' . $key);
        if ($existing) { $ids[$key] = $existing->ID; continue; }
        $id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => 'finnora-' . $key, 'meta_input' => ['_wp_page_template' => $template, '_fin_owned' => '1']], true);
        if (!is_wp_error($id)) $ids[$key] = $id;
    }
    update_option('fin_studio_pages', $ids);
    return $ids;
}
add_action('after_switch_theme', 'fin_create_pages');
add_action('admin_menu', function () { add_theme_page('FINNORA setup', 'FINNORA setup', 'edit_theme_options', 'fin-start', 'fin_setup_screen'); });
add_action('admin_notices', function () {
    if (current_user_can('edit_theme_options') && !get_option('fin_studio_seen')) echo '<div class="notice notice-info"><p>FINNORA is ready. <a href="' . esc_url(admin_url('themes.php?page=fin-start')) . '">Open setup and sample content</a>.</p></div>';
});
function fin_setup_screen() {
    if (!current_user_can('edit_theme_options')) return;
    update_option('fin_studio_seen', 1);
    ?><div class="wrap"><h1>FINNORA Studio — Setup</h1><p>Use Appearance → Customize → FINNORA to edit the homepage, contact details and colours. Use Products and Posts to manage store and journal content.</p>
    <h2>Sample content</h2><p>Import three stories and nine sample products with variations when WooCommerce is active. Existing products, content, prices, payment gateways, shipping and tax settings are retained. Sample products can be purchased through any gateways already enabled on this site; use a test site before selling.</p>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="fin_import_demo"><?php wp_nonce_field('fin_import_demo'); ?><button class="button button-primary">Import FINNORA sample content</button></form>
    <?php if (isset($_GET['fin_imported'])) echo '<p>Sample content is ready. Repeating the import does not duplicate existing samples.</p>'; ?>
    <h2>English storefront</h2><p>Apply English copy to recognised FINNORA demo text, page titles, categories, product descriptions and colour labels. The site language changes to English. Custom text that is not part of the demo is kept for your review. Existing product IDs, prices, images, stock and order records are preserved. Back up the site before applying.</p>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="fin_apply_english"><?php wp_nonce_field('fin_apply_english'); ?><button class="button">Apply English storefront copy</button></form>
    <?php if (isset($_GET['fin_english'])) echo '<p>English storefront copy applied. Clear any page cache and review the public site.</p>'; ?>
    <h2>Product experiences</h2><p>Products → Edit → Product data → General → FINNORA experience: choose a display shelf, discovery rail, 2D outfit preview or standard product page. The outfit preview uses a transparent garment image and manual position controls; it does not predict fit or track the body.</p></div><?php
}
function fin_import_image($name) {
    $existing = get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_fin_asset', 'meta_value' => $name, 'fields' => 'ids', 'numberposts' => 1]);
    if ($existing) return $existing[0];
    $source = get_template_directory() . '/assets/images/' . sanitize_file_name($name) . '.webp';
    if (!is_file($source)) return 0;
    $upload = wp_upload_bits('finnora-' . $name . '.webp', null, file_get_contents($source));
    if (!empty($upload['error'])) throw new RuntimeException('Unable to import image: ' . $upload['error']);
    $id = wp_insert_attachment(['post_title' => ucwords(str_replace('-', ' ', $name)), 'post_mime_type' => 'image/webp', 'post_status' => 'inherit'], $upload['file'], 0, true);
    if (is_wp_error($id)) throw new RuntimeException($id->get_error_message());
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    update_post_meta($id, '_fin_asset', $name);
    return $id;
}
function fin_run_import() {
    $ids = fin_create_pages();
    foreach (fin_story_data() as $slug => $story) {
        if (get_page_by_path($slug, OBJECT, 'post')) continue;
        $term = term_exists($story['tag'], 'category');
        if (!$term) $term = wp_insert_term($story['tag'], 'category');
        $post = ['post_type' => 'post', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $story['title'], 'post_content' => $story['body'], 'post_excerpt' => $story['excerpt'], 'meta_input' => ['_fin_owned' => '1']];
        if (!is_wp_error($term)) $post['post_category'] = [(int) $term['term_id']];
        $id = wp_insert_post($post, true);
        if (!is_wp_error($id)) { $image = fin_import_image($story['image']); if ($image) set_post_thumbnail($id, $image); }
    }
    if (class_exists('WC_Product_Variable')) {
        foreach (fin_catalog() as $item) {
            $existing_id = wc_get_product_id_by_sku('FIN-' . $item['id']);
            if ($existing_id && (!get_post_meta($existing_id, '_fin_owned', true) || get_post_meta($existing_id, '_fin_import_complete', true))) continue;
            $product = $existing_id ? new WC_Product_Variable($existing_id) : new WC_Product_Variable();
            $product->set_name($item['vi']); $product->set_slug($item['id']); $product->set_sku('FIN-' . $item['id']);
            $product->set_status('publish'); $product->set_description('<p>' . esc_html($item['descVi']) . '</p><p>A FINNORA demonstration product. Please check a physical sample before production.</p>');
            $product->set_short_description($item['descVi']); $product->set_menu_order($item['i']);
            $attrs = [];
            foreach (['Size' => $item['sizes'], 'Color' => $item['colorsVi']] as $name => $values) {
                $attr = new WC_Product_Attribute(); $attr->set_name($name); $attr->set_options($values); $attr->set_visible(true); $attr->set_variation(true); $attrs[] = $attr;
            }
            $product->set_attributes($attrs); $product->set_default_attributes(['size' => $item['sizes'][0], 'color' => $item['colorsVi'][0]]);
            $product->update_meta_data('_fin_owned', '1');
            $term = term_exists($item['cat'], 'product_cat');
            if (!$term) $term = wp_insert_term(fin_categories()[$item['cat']], 'product_cat', ['slug' => $item['cat']]);
            if (!is_wp_error($term)) $product->set_category_ids([(int) $term['term_id']]);
            $image = fin_import_image($item['id']); if ($image) $product->set_image_id($image);
            $id = $product->save();
            foreach ($item['sizes'] as $size) foreach ($item['colorsVi'] as $color) {
                $variation_id = WC_Data_Store::load('product')->find_matching_product_variation($product, ['attribute_size' => $size, 'attribute_color' => $color]);
                $variation = $variation_id ? new WC_Product_Variation($variation_id) : new WC_Product_Variation(); $variation->set_parent_id($id); $variation->set_attributes(['size' => $size, 'color' => $color]); $variation->set_regular_price((string) $item['price']); $variation->set_stock_status('instock'); $variation->save();
            }
            WC_Product_Variable::sync($id); update_post_meta($id, '_fin_import_complete', '1');
        }
        $woo_pages = ['shop' => ['Shop', ''], 'cart' => ['Shopping bag', '[woocommerce_cart]'], 'checkout' => ['Checkout', '[woocommerce_checkout]'], 'myaccount' => ['My account', '[woocommerce_my_account]']];
        foreach ($woo_pages as $key => [$title, $content]) {
            if (wc_get_page_id($key) > 0 && get_post_status(wc_get_page_id($key)) === 'publish') continue;
            $id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'finnora-wc-' . $key, 'post_title' => $title, 'post_content' => $content], true);
            if (!is_wp_error($id)) update_option('woocommerce_' . $key . '_page_id', $id);
        }
    }
    update_option('fin_studio_imported', 1);
    flush_rewrite_rules(false);
}
add_action('admin_post_fin_import_demo', function () {
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    check_admin_referer('fin_import_demo');
    try { fin_run_import(); } catch (Throwable $error) { wp_die('The import was not completed. Existing imported items are retained; you can try again. Error: ' . esc_html($error->getMessage()), 'FINNORA', ['back_link' => true]); }
    wp_safe_redirect(admin_url('themes.php?page=fin-start&fin_imported=1')); exit;
});
