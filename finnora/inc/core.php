<?php
defined('ABSPATH') || exit;
add_action('after_setup_theme', function () {
    load_theme_textdomain('finnora-studio', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_editor_style('assets/editor.css');
    register_nav_menus(['primary' => 'FINNORA primary menu', 'footer' => 'FINNORA footer menu']);
});
add_action('wp_enqueue_scripts', function () {
    $dir = get_template_directory();
    wp_enqueue_style('finnora-studio', get_template_directory_uri() . '/assets/theme.css', [], filemtime($dir . '/assets/theme.css'));
    wp_enqueue_script('finnora-studio', get_template_directory_uri() . '/assets/theme.js', [], filemtime($dir . '/assets/theme.js'), true);
    if (is_singular() && comments_open() && get_option('thread_comments')) wp_enqueue_script('comment-reply');
});
function fin_image($name) { return get_template_directory_uri() . '/assets/images/' . sanitize_file_name($name) . '.webp'; }
function fin_input($key) { return isset($_GET[$key]) && is_string($_GET[$key]) ? wp_unslash($_GET[$key]) : ''; }
function fin_qv($key) { $v = get_query_var($key); return is_string($v) ? $v : ''; }
function fin_page_url($key) {
    $ids = get_option('fin_studio_pages', []);
    if (!empty($ids[$key]) && get_post_status($ids[$key]) === 'publish') return get_permalink($ids[$key]);
    return add_query_arg('fin_page', $key, home_url('/'));
}
function fin_shop_url() {
    return function_exists('wc_get_page_id') && wc_get_page_id('shop') > 0 && get_post_status(wc_get_page_id('shop')) === 'publish' ? get_permalink(wc_get_page_id('shop')) : fin_page_url('shop');
}
function fin_catalog() {
    static $data;
    if ($data === null) $data = json_decode(file_get_contents(get_template_directory() . '/inc/catalog.json'), true) ?: [];
    return $data;
}
function fin_categories() { return ['wear' => 'Clothing', 'accessories' => 'Bags & Accessories', 'drinkware' => 'Drinkware', 'home' => 'Home & Living', 'art' => 'Wall Art', 'tech' => 'Tech Accessories', 'stationery' => 'Stationery & Stickers']; }
function fin_demo_product() {
    $slug = sanitize_key(fin_qv('fin_product'));
    foreach (fin_catalog() as $item) if ($item['id'] === $slug) return $item;
    return null;
}
function fin_product_url($item) { return add_query_arg('fin_product', $item['id'], fin_page_url('shop')); }
function fin_story_data() { return json_decode(file_get_contents(get_template_directory() . '/inc/stories-en.json'), true) ?: []; }
function fin_story_url($slug) { return add_query_arg('fin_story', $slug, fin_page_url('journal')); }
add_filter('query_vars', function ($vars) { return array_merge($vars, ['fin_product', 'fin_story', 'fin_page']); });
add_filter('template_include', function ($template) {
    $file = '';
    if (fin_qv('fin_product')) $file = fin_demo_product() ? 'single-demo-product.php' : '404.php';
    elseif (fin_qv('fin_story')) $file = isset(fin_story_data()[sanitize_key(fin_qv('fin_story'))]) ? 'single-demo-story.php' : '404.php';
    elseif (fin_qv('fin_page')) {
        $map = ['about' => 'page-about.php', 'contact' => 'page-contact.php', 'journal' => 'home.php', 'shop' => 'page-shop.php'];
        $file = $map[sanitize_key(fin_qv('fin_page'))] ?? '404.php';
    }
    if ($file === '404.php') { global $wp_query; $wp_query->set_404(); status_header(404); }
    return $file ? get_template_directory() . '/' . $file : $template;
}, 99);
add_filter('pre_get_document_title', function ($title) {
    if ($p = fin_demo_product()) return $p['vi'] . ' — FINNORA';
    $slug = sanitize_key(fin_qv('fin_story'));
    if (isset(fin_story_data()[$slug])) return fin_story_data()[$slug]['title'] . ' — FINNORA';
    return $title;
});
function fin_breadcrumb($current) { return; }
function fin_product_card($item) { ?>
    <article class="product-card"><a class="product-photo" href="<?php echo esc_url(fin_product_url($item)); ?>"><img src="<?php echo esc_url(fin_image($item['id'])); ?>" width="420" height="420" loading="lazy" alt="<?php echo esc_attr($item['vi']); ?>"><span class="photo-link">Discover the piece ↗</span></a><div class="card-meta"><span><?php echo esc_html(fin_categories()[$item['cat']]); ?></span><span>FINNORA OBJECTS</span></div><h3><a href="<?php echo esc_url(fin_product_url($item)); ?>"><?php echo esc_html($item['vi']); ?></a></h3><div class="price-row"><span><?php echo esc_html('$' . number_format_i18n($item['price'], 2)); ?> <small>USD · demo price</small></span><a href="<?php echo esc_url(fin_product_url($item)); ?>" aria-label="<?php echo esc_attr('View ' . $item['vi']); ?>">↗</a></div></article>
<?php }
function fin_products($limit = 8) {
    $items = function_exists('wc_get_products') ? wc_get_products(['status' => 'publish', 'limit' => $limit, 'orderby' => 'menu_order', 'order' => 'ASC', 'visibility' => 'visible']) : [];
    if ($items) {
        echo '<div class="woocommerce fin-products">'; woocommerce_product_loop_start();
        foreach ($items as $p) { $GLOBALS['post'] = get_post($p->get_id()); setup_postdata($GLOBALS['post']); $GLOBALS['product'] = $p; wc_get_template_part('content', 'product'); }
        woocommerce_product_loop_end(); echo '</div>'; wp_reset_postdata();
    } else { echo '<div class="product-grid">'; foreach (array_slice(fin_catalog(), 0, $limit) as $item) fin_product_card($item); echo '</div>'; }
}
function fin_story_card($slug, $story) { ?>
    <article class="story-card"><a class="story-photo" href="<?php echo esc_url(fin_story_url($slug)); ?>"><img src="<?php echo esc_url(fin_image($story['image'])); ?>" alt="" loading="lazy" width="640" height="480"></a><div class="card-meta"><span><?php echo esc_html($story['tag']); ?></span><span>FINNORA Journal</span></div><h3><a href="<?php echo esc_url(fin_story_url($slug)); ?>"><?php echo esc_html($story['title']); ?></a></h3><p><?php echo esc_html($story['excerpt']); ?></p><a class="text-link" href="<?php echo esc_url(fin_story_url($slug)); ?>">Read the story ↗</a></article>
<?php }
function fin_journal($limit = 3) {
    $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => $limit, 'ignore_sticky_posts' => true, 'post__not_in' => is_singular('post') ? [get_queried_object_id()] : []]);
    echo '<div class="story-grid">';
    $count = 0;
    if ($query->have_posts()) { while ($query->have_posts()) { $query->the_post(); get_template_part('template-parts/post-card'); $count++; } wp_reset_postdata(); }
    if ($count < $limit && !get_option('fin_studio_imported')) foreach (array_slice(fin_story_data(), 0, $limit - $count, true) as $slug => $story) fin_story_card($slug, $story);
    echo '</div>';
}
function fin_active($key) {
    if(in_array($key,['designs','upload','christmas'],true)){return is_page(['designs'=>'explore-designs','upload'=>'upload-your-photo','christmas'=>'christmas-collection'][$key]);}
    if (fin_qv('fin_story')) return $key === 'journal';
    if (fin_qv('fin_product')) return $key === 'shop';
    if (fin_qv('fin_page')) return fin_qv('fin_page') === $key;
    if (is_front_page()) return false;
    if ($key === 'shop' && function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout())) return true;
    $ids = get_option('fin_studio_pages', []);
    if ($key === 'journal' && (is_home() || is_singular('post') || is_category() || is_tag() || is_author() || is_date())) return true;
    return isset($ids[$key]) && is_page($ids[$key]);
}
function fin_fallback_menu() {
    $links = ['designs' => 'Explore Designs', 'upload' => 'Upload Your Photo', 'christmas' => 'Christmas Collection', 'shop' => 'Products', 'about' => 'About FINNORA', 'journal' => 'Stories & Guides', 'contact' => 'Contact & Support'];
    echo '<ul class="menu">';
    foreach ($links as $key => $label) {
        $url = in_array($key, ['designs','upload','christmas'], true) ? fin_design_page_url($key) : ($key === 'shop' ? fin_shop_url() : fin_page_url($key));
        echo '<li' . ($key === 'shop' ? ' class="has-dropdown"' : '') . '><a ' . (fin_active($key) ? 'aria-current="page" ' : '') . 'href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
        if ($key === 'shop') {
            echo '<button class="submenu-toggle" aria-label="Open product categories" aria-expanded="false">⌄</button><ul class="sub-menu">';
            $terms = taxonomy_exists('product_cat') ? get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0, 'number' => 12]) : [];
            $genz = get_term_by('slug', 'gen-z-edit', 'product_cat');
            if ($genz && $genz->count && is_array($terms)) $terms[] = $genz;
            if ($terms && !is_wp_error($terms)) foreach ($terms as $term) echo '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a></li>';
            else foreach (fin_categories() as $slug => $name) echo '<li><a href="' . esc_url(add_query_arg('fin_cat', $slug, fin_page_url('shop'))) . '">' . esc_html($name) . '</a></li>';
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

// Keep the primary navigation labels consistent for custom WordPress menus too.
add_filter('wp_nav_menu_objects', function ($items, $args) {
    if (($args->theme_location ?? '') !== 'primary') return $items;
    foreach ($items as $item) {
        if (in_array($item->title, ['Journal', 'Studio journal'], true)) $item->title = 'Stories & Guides';
        if ($item->title === 'Contact') $item->title = 'Contact & Support';
    }
    return $items;
}, 10, 2);
