<?php
defined('ABSPATH') || exit;

// Concept stock must not advertise availability as if a supplier has confirmed it.
function fin_is_unverified_concept($product) {
    if (!$product) return false;
    if ($product->is_type('variation') && function_exists('wc_get_product')) $product = wc_get_product($product->get_parent_id());
    return $product && str_starts_with((string)$product->get_sku(), 'FIN-') && $product->get_meta('_fin_supplier_verified') !== 'yes';
}
add_filter('woocommerce_structured_data_product', function($data, $product) {
    if (fin_is_unverified_concept($product)) return [];
    if (empty($data['image']) && preg_match('/src=["\x27]([^"\x27]+)["\x27]/', $product->get_image('full'), $match)) $data['image'] = esc_url_raw(html_entity_decode($match[1], ENT_QUOTES, 'UTF-8'));
    return $data;
}, 99, 2);
add_filter('wp_robots', function($robots) {
    if (function_exists('is_product') && is_product() && fin_is_unverified_concept(wc_get_product(get_queried_object_id()))) {
        unset($robots['index']); $robots['noindex'] = true; $robots['follow'] = true;
    }
    return $robots;
});
add_action('woocommerce_product_options_general_product_data', function() {
    woocommerce_wp_checkbox(['id'=>'_fin_supplier_verified','label'=>'Supplier verified','description'=>'Enable only after confirming the physical product, material, measurements, print, price and fulfillment. This does not establish Merchant Center eligibility.']);
});
add_action('woocommerce_admin_process_product_object', function($product) {
    $product->update_meta_data('_fin_supplier_verified', isset($_POST['_fin_supplier_verified']) ? 'yes' : 'no');
});
function fin_policy_links() {
    $links=[];
    foreach (['shipping'=>'Shipping & delivery','returns'=>'Returns & refunds','terms'=>'Terms & conditions','privacy'=>'Privacy policy'] as $key=>$label) {
        $id=absint(get_theme_mod('fin_policy_'.$key, $key==='privacy'?get_option('wp_page_for_privacy_policy'):0));
        if($id && get_post_status($id)==='publish') $links[$label]=get_permalink($id);
    }
    return $links;
}
add_action('customize_register', function($c) {
    $c->add_section('fin_merchant', ['title'=>'FINNORA — Store policies','description'=>'Select completed, accurate policies. Do not publish placeholder terms or invent shipping/return promises.','priority'=>32]);
    foreach (['shipping'=>'Shipping & delivery','returns'=>'Returns & refunds','terms'=>'Terms & conditions','privacy'=>'Privacy policy'] as $key=>$label) {
        $c->add_setting('fin_policy_'.$key,['default'=>0,'sanitize_callback'=>'absint']);
        $c->add_control('fin_policy_'.$key,['section'=>'fin_merchant','label'=>$label,'type'=>'dropdown-pages']);
    }
    $c->remove_section('fin_demo_feedback_section');
}, 99);
add_action('admin_menu', function() {
    add_theme_page('Store readiness','Store readiness','manage_options','fin-store-readiness',function() {
        ?><div class="wrap"><h1>FINNORA — Store readiness</h1><p>This is a preparation checklist, not a Google approval certificate. Submit only real, purchasable products after testing production, payment and shipping.</p><ul>
        <li>Support email: <?php echo get_theme_mod('fin_contact_email')?'Configured — verify message delivery':'Missing'; ?></li>
        <li>Published linked policies: <?php echo count(fin_policy_links()); ?> / 4 — review their actual contents.</li>
        <li>Configure policies: Appearance → Customize → FINNORA — Store policies.</li>
        <li>Verify each product with its supplier, then Products → Edit → Product data → General → Supplier verified.</li>
        <li>Unverified FIN- concept products: noindex and no Product/Offer structured data. These settings do not automatically exclude products from third-party feeds; exclude concepts in your feed integration too.</li>
        <li>Confirm exact countries, USD pricing where appropriate, processing/transit times, shipping fees, returns and tax settings.</li>
        <li>Configure and test a real payment method, guest checkout and confirmation email. Remove the demo-checkout plugin only when replacing it with tested live settings.</li>
        <li>Merchant Center: verify/claim the real WordPress domain; match business identity, policies, availability, prices and each size/color variation.</li>
        <li>AI product images need accurate product depiction and embedded DigitalSourceType metadata; check uploaded/resized/CDN versions preserve it.</li>
        <li>Do not invent GTIN, reviews, addresses, shipping promises or supplier claims.</li>
        </ul></div><?php
    });
});
