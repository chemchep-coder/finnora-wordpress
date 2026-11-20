<?php
defined('ABSPATH') || exit;
add_action('customize_register', function ($c) {
    foreach (['fin_support_phone'=>['Support phone number','sanitize_text_field'],'fin_support_chat'=>['Chat link (WhatsApp, Messenger or your support page)','esc_url_raw']] as $key=>[$label,$sanitize]) {
        $c->add_setting($key,['default'=>'','sanitize_callback'=>$sanitize]);
        $c->add_control($key,['section'=>'fin_studio','label'=>$label,'type'=>'text']);
    }
});
add_action('wp_enqueue_scripts',function(){wp_enqueue_script('fin-help-modal',get_template_directory_uri().'/assets/help-modal.js',[],filemtime(get_template_directory().'/assets/help-modal.js'),true);});
add_action('wp_footer',function(){
    $phone=get_theme_mod('fin_support_phone','');$chat=get_theme_mod('fin_support_chat','');$email=sanitize_email(get_theme_mod('fin_contact_email',''));
    ?><button type="button" class="fin-tree-help" aria-label="Open Help & Support" aria-haspopup="dialog" aria-controls="fin-help-dialog"><svg viewBox="0 0 64 64" width="42" height="42" aria-hidden="true"><path d="M28 48h8v12h-8z" fill="#93623D" stroke="#fff" stroke-width="2"/><path d="M32 6 19 25h6L12 40h7L8 53h48L45 40h7L39 25h6Z" fill="#32804D" stroke="#fff" stroke-width="2" stroke-linejoin="round"/><path d="m32 1 2 5 5 .5-4 3 1 5-4-3-4 3 1-5-4-3 5-.5Z" fill="#FFE5A3"/><circle cx="25" cy="31" r="3" fill="#D83238"/><circle cx="38" cy="40" r="3" fill="#D83238"/><circle cx="24" cy="46" r="2.5" fill="#fff"/><circle cx="34" cy="21" r="2" fill="#fff"/></svg></button>
    <dialog id="fin-help-dialog" class="fin-help-dialog" aria-labelledby="fin-help-dialog-title"><button type="button" class="fin-help-close" aria-label="Close Help & Support">×</button><h2 id="fin-help-dialog-title">Help &amp; Support</h2><p>How can we help you today?</p><div class="fin-help-options">
    <?php if($chat): ?><a href="<?php echo esc_url($chat); ?>"><strong>Chat with us ↗</strong><span>Open our chat channel</span></a><?php endif; ?>
    <?php if($phone): ?><a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$phone)); ?>"><strong>Call us</strong><span><?php echo esc_html($phone); ?></span></a><?php endif; ?>
    <?php if($email): ?><a href="mailto:<?php echo esc_attr($email); ?>"><strong>Email us</strong><span><?php echo esc_html($email); ?></span></a><?php endif; ?>
    <a href="<?php echo esc_url(fin_page_url('contact')); ?>"><strong>Visit the Help Centre ↗</strong><span>Delivery, product care, returns and enquiries</span></a></div></dialog><?php
},25);
function fin_support_icon($name) {
    $paths = ['home'=>'<path d="m3 10 9-7 9 7v11h-6v-7H9v7H3Z"/>','shop'=>'<path d="M4 9h16v12H4zM3 9l2-6h14l2 6M8 3v6m8-6v6"/>','help'=>'<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 5m0 3h.01"/>','bag'=>'<path d="M5 7h14l2 14H3ZM8 8V6a4 4 0 0 1 8 0v2"/>','delivery'=>'<path d="M2 5h12v12H2zm12 5h4l4 4v3h-8"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>','care'=>'<path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5Z"/>','returns'=>'<path d="M4 9h10a6 6 0 0 1 0 12M4 9l5-5M4 9l5 5"/>'];
    return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">' . ($paths[$name] ?? $paths['help']) . '</svg>';
}
function fin_home_support() {
    ?><section class="fin-help-center container" aria-labelledby="fin-help-heading"><div class="section-heading"><div><h2 id="fin-help-heading">A little help, whenever you need it.</h2><p>Find practical answers before choosing your next piece.</p></div><a class="text-link" href="<?php echo esc_url(fin_page_url('contact')); ?>">Contact & support ↗</a></div><div class="fin-help-grid"><?php
    foreach ([['delivery','Delivery','Delivery information','Explore how delivery will work. This demo has no connected shipping service.'],['care','Product care','Product care guidance','Check the care label and product details for your chosen piece.'],['returns','Returns','Returns information','A returns policy is not active for this demonstration.'],['help','Contact','Contact the studio','Visit our contact page for enquiries and website feedback.']] as [$icon,$label,$title,$copy]) : ?>
    <a href="<?php echo esc_url(fin_page_url('contact') . '#support-' . $icon); ?>" class="fin-help-card"><span class="fin-help-symbol"><?php echo fin_support_icon($icon); ?><span><?php echo esc_html($label); ?></span></span><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($copy); ?></p><span class="fin-help-more">Learn more ↗</span></a>
    <?php endforeach; ?></div></section><?php
}
add_action('wp_footer', function () {
    $bag = function_exists('wc_get_cart_url') ? wc_get_cart_url() : fin_shop_url();
    $links = [['home','Home',home_url('/'),is_front_page()],['shop','Shop',fin_shop_url(),function_exists('is_woocommerce') && is_woocommerce()],['help','Help',fin_page_url('contact'),fin_active('contact')],['bag','Bag',$bag,function_exists('is_cart')&&is_cart()]];
    ?><nav class="fin-mobile-dock" aria-label="Quick navigation"><?php foreach($links as [$icon,$label,$url,$active]) : ?><a href="<?php echo esc_url($url); ?>" <?php if($active) echo 'aria-current="page"'; ?>><?php echo fin_support_icon($icon); ?><span><?php echo esc_html($label); ?><?php if($icon==='bag' && function_exists('WC') && WC()->cart) : ?> <span class="fin-cart-count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span><?php endif; ?></span></a><?php endforeach; ?></nav><?php
}, 20);
