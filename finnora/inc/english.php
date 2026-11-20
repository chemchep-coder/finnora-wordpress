<?php
defined('ABSPATH') || exit;

function fin_english_map() {
    static $map;
    if ($map === null) {
        $map = json_decode(file_get_contents(get_template_directory() . '/inc/english-map.json'), true) ?: [];
        $map += [
            'Túi &amp; phụ kiện' => 'Bags & accessories',
            'Giấy &amp; sáng tạo' => 'Paper & stationery',
            'FINNORA Studio đưa nghệ thuật vào những món đồ hằng ngày. Đây là bản demo học tập về POD; sản phẩm, hình ảnh và giá mang tính minh họa.' => 'FINNORA Studio brings art into everyday life. This is a print-on-demand demonstration; products, images and prices are illustrative.',
            'Bản demo chưa có hộp thư chăm sóc khách hàng. Để góp ý, mở trang chủ → Đánh giá demo → điền phiếu → tải và tự gửi tệp cho người mời bạn.' => 'We would love to hear your thoughts on this demonstration. Use the contact form once the website owner has configured the contact email.',
            'Bản demo dự án POD. Hình ảnh và giá minh họa; chưa kết nối sản xuất hoặc thanh toán thật.' => 'A print-on-demand demonstration. Images and prices are illustrative; ordering depends on the store settings.',
            'Đặt đơn thử' => 'Demo checkout', 'Tài khoản' => 'My account',
            'Giá mẫu; chưa sản xuất hoặc giao hàng thật.' => 'Demo price; production and delivery are not connected.',
            'Demo shipping / Phí giao mẫu' => 'Demo shipping',
        ];
    }
    return $map;
}
function fin_english_text($text) { return is_string($text) ? strtr($text, fin_english_map()) : $text; }

// Explicit, repeatable migration. No prices, order records, URLs or IDs are replaced.
function fin_apply_english() {
    $map = fin_english_map(); $changed = ['posts' => 0, 'products' => 0, 'terms' => 0, 'settings' => 0];
    foreach (get_posts(['post_type' => ['page', 'post', 'nav_menu_item', 'attachment'], 'post_status' => ['publish', 'draft', 'private', 'inherit'], 'numberposts' => -1]) as $post) {
        $update = ['ID' => $post->ID];
        foreach (['post_title', 'post_content', 'post_excerpt'] as $field) {
            $new = fin_english_text($post->$field);
            if ($new !== $post->$field) $update[$field] = $new;
        }
        if (count($update) > 1) {
            if (!get_post_meta($post->ID, '_fin_before_english', true)) update_post_meta($post->ID, '_fin_before_english', ['title' => $post->post_title, 'content' => $post->post_content, 'excerpt' => $post->post_excerpt]);
            $result = wp_update_post(wp_slash($update), true);
            if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
            $changed['posts']++;
        }
        $alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
        if ($alt) update_post_meta($post->ID, '_wp_attachment_image_alt', fin_english_text($alt));
    }
    foreach (['category', 'post_tag', 'product_cat', 'product_tag'] as $taxonomy) {
        if (!taxonomy_exists($taxonomy)) continue;
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (is_wp_error($terms)) continue;
        foreach ($terms as $term) {
            $name = fin_english_text($term->name); $description = fin_english_text($term->description);
            if ($name !== $term->name || $description !== $term->description) {
                $result = wp_update_term($term->term_id, $taxonomy, ['name' => $name, 'description' => $description]);
                if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
                $changed['terms']++;
            }
        }
    }
    foreach (['fin_announcement','fin_hero_title','fin_hero_emphasis','fin_hero_description','fin_contact_location','fin_footer_note'] as $key) {
        $old = get_theme_mod($key, null);
        if (is_string($old) && ($new = fin_english_text($old)) !== $old) { set_theme_mod($key, $new); $changed['settings']++; }
    }
    if (function_exists('wc_get_products')) {
        foreach (wc_get_products(['limit' => -1, 'status' => ['publish','draft','private']]) as $product) {
            $touched = false;
            foreach (['name','description','short_description'] as $field) {
                $old = $product->{'get_' . $field}(); $new = fin_english_text($old);
                if ($new !== $old) { $product->{'set_' . $field}($new); $touched = true; }
            }
            $renamed = []; $product_attributes = $product->get_attributes();
            foreach ($product_attributes as $attribute_key => $attribute) {
                if ($attribute->is_taxonomy()) continue;
                $options = $attribute->get_options(); $new = array_map('fin_english_text', $options);
                if ($new !== $options) {
                    $renamed[sanitize_title($attribute->get_name())] = array_combine($options, $new);
                    $attribute = clone $attribute;
                    $attribute->set_options($new); $product_attributes[$attribute_key] = $attribute; $touched = true;
                }
            }
            if ($renamed) {
                $product->set_attributes($product_attributes);
                $defaults = $product->get_default_attributes();
                foreach ($renamed as $attribute => $values) if (isset($defaults[$attribute], $values[$defaults[$attribute]])) $defaults[$attribute] = $values[$defaults[$attribute]];
                $product->set_default_attributes($defaults);
            }
            if ($touched) {
                $product->save();
                foreach ($product->is_type('variable') ? $product->get_children() : [] as $id) {
                    $variation = wc_get_product($id); if (!$variation) continue;
                    $attributes = $variation->get_attributes();
                    foreach ($renamed as $attribute => $values) if (isset($attributes[$attribute], $values[$attributes[$attribute]])) $attributes[$attribute] = $values[$attributes[$attribute]];
                    $variation->set_attributes($attributes); $variation->save();
                }
                wc_delete_product_transients($product->get_id()); $changed['products']++;
            }
        }
        if (class_exists('WC_Shipping_Zones')) {
            $zones = array_map(fn($zone) => new WC_Shipping_Zone($zone['id']), WC_Shipping_Zones::get_zones());
            $zones[] = new WC_Shipping_Zone(0);
            foreach ($zones as $zone) foreach ($zone->get_shipping_methods() as $method) {
                $key = $method->get_instance_option_key(); $settings = get_option($key, []);
                if (is_array($settings) && isset($settings['title']) && is_string($settings['title'])) {
                    $settings['title'] = fin_english_text($settings['title']); update_option($key, $settings);
                }
            }
        }
    }
    update_option('WPLANG', 'en_US');
    update_option('fin_english_applied', ['version' => '2.2.0', 'counts' => $changed]);
    return $changed;
}
add_action('admin_post_fin_apply_english', function () {
    if (!current_user_can('manage_options')) wp_die('Permission denied.');
    check_admin_referer('fin_apply_english');
    try { fin_apply_english(); } catch (Throwable $error) { wp_die(esc_html($error->getMessage()), 'FINNORA', ['back_link' => true]); }
    wp_safe_redirect(admin_url('themes.php?page=fin-start&fin_english=1')); exit;
});
