<?php
defined('ABSPATH') || exit;
function fin_holiday_reindeer(){echo '<span class="fin-reindeer-mini" aria-hidden="true"><img src="'.esc_url(get_template_directory_uri().'/assets/characters/reindeer-gift.svg').'" alt="" width="120" height="140" loading="lazy"></span>';}
add_action('woocommerce_before_shop_loop_item_title','fin_holiday_reindeer',20);
add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('fin-holiday-characters',get_template_directory_uri().'/assets/holiday-characters.css',['fin-designs'],filemtime(get_template_directory().'/assets/holiday-characters.css'));
    wp_enqueue_script('fin-holiday-characters',get_template_directory_uri().'/assets/holiday-characters.js',[],filemtime(get_template_directory().'/assets/holiday-characters.js'),true);
},50);
