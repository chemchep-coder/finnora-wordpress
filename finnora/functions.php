<?php
defined('ABSPATH') || exit;
require_once get_template_directory() . '/inc/core.php';
require_once get_template_directory() . '/inc/merchant-readiness.php';
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/contact.php';
require_once get_template_directory() . '/inc/woocommerce.php';
require_once get_template_directory() . '/inc/experience.php';
require_once get_template_directory() . '/inc/english.php';
require_once get_template_directory() . '/inc/storefront.php';
require_once get_template_directory() . '/inc/palette.php';
require_once get_template_directory() . '/inc/support.php';

require_once get_template_directory() . '/inc/reviews.php';

require_once get_template_directory() . '/inc/design-library.php';
require_once get_template_directory() . '/inc/design-cart.php';

require_once get_template_directory() . '/inc/holiday-characters.php';

add_action('wp_enqueue_scripts',function(){wp_enqueue_style('fin-christmas-surfaces',get_template_directory_uri().'/assets/christmas-surfaces.css',['fin-feedback-feed','fin-designs'],filemtime(get_template_directory().'/assets/christmas-surfaces.css'));},60);

require_once get_template_directory() . '/inc/pod-imagery.php';

require_once get_template_directory() . '/inc/holiday-designs.php';

require_once get_template_directory() . '/inc/product-details.php';

require_once get_template_directory() . '/inc/sizing.php';

require_once get_template_directory() . '/inc/apparel-expansion.php';

require_once get_template_directory() . '/inc/genz-apparel.php';

require_once get_template_directory() . '/inc/category-expansion.php';

require_once get_template_directory() . '/inc/home-editorial.php';
