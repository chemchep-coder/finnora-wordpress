<?php
defined('ABSPATH') || exit;
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;
    $rows = ['Product type' => $product->is_type('variable') ? 'Choose your preferred options below' : 'Single product'];
    foreach ($product->get_attributes() as $attribute) {
        $value = $product->get_attribute($attribute->get_name());
        if ($value) $rows[wc_attribute_label($attribute->get_name())] = $value;
    }
    if ($product->has_weight()) $rows['Weight'] = wc_format_weight($product->get_weight());
    if ($product->has_dimensions()) $rows['Dimensions'] = wc_format_dimensions($product->get_dimensions(false));
    $rows['Customization'] = in_array(preg_replace('/^FIN-/', '', $product->get_sku()), fin_pod_2026_slugs(), true) ? 'Browse the compatible designs below for available personalization.' : 'Illustrative concept artwork; confirm personalization options before ordering.';
    $rows['Product imagery'] = 'AI-generated illustrative mockup; final print and colors may vary.';
    $rows['Materials & finish'] = 'Supplier specifications and physical samples have not been verified for this demo.';
    echo '<details class="fin-product-specifications"><summary>Product details &amp; materials</summary><dl>';
    foreach ($rows as $label => $value) echo '<div><dt>'.esc_html($label).'</dt><dd>'.esc_html(wp_strip_all_tags($value)).'</dd></div>';
    echo '</dl></details>';
}, 42);
