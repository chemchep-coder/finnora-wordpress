<?php
defined('ABSPATH') || exit;
function fin_editorial_copy($key,$default,$legacy=[]){$value=get_theme_mod($key,'');return !$value||in_array($value,$legacy,true)?$default:$value;}
function fin_editorial_product($slug){return function_exists('wc_get_product_id_by_sku')?wc_get_product(wc_get_product_id_by_sku('FIN-'.$slug)):false;}
function fin_editorial_category($slug){$term=get_term_by('slug',$slug,'product_cat');return $term&&!is_wp_error($term)?get_term_link($term):fin_shop_url();}
function fin_editorial_photo($slug){$slug=sanitize_file_name($slug);foreach(['-photo.webp','-scene.webp'] as $suffix)if(is_file(get_template_directory().'/assets/pod-2026/'.$slug.$suffix))return get_template_directory_uri().'/assets/pod-2026/'.$slug.$suffix;return get_template_directory_uri().'/assets/images/christmas-studio.webp';}
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('fin-brand-editorial',get_template_directory_uri().'/assets/brand-editorial.css',['fin-christmas-surfaces'],filemtime(get_template_directory().'/assets/brand-editorial.css'));if(is_front_page()){wp_enqueue_script('fin-home-editorial',get_template_directory_uri().'/assets/home-editorial.js',[],filemtime(get_template_directory().'/assets/home-editorial.js'),true);wp_enqueue_style('fin-home-editorial',get_template_directory_uri().'/assets/home-editorial.css',['fin-christmas-surfaces'],filemtime(get_template_directory().'/assets/home-editorial.css'));}},80);
add_action('customize_register',function($c){$c->remove_control('fin_hero_image');foreach(['fin_home_edit_title'=>['Featured selection title','Explore the Christmas Collection'],'fin_home_routes_title'=>['Shopping routes title','Shop by Category'],'fin_home_personal_title'=>['Personalization section title','Make Their Gift Personal']] as $key=>[$label,$default]){$c->add_setting($key,['default'=>$default,'sanitize_callback'=>'sanitize_text_field']);$c->add_control($key,['label'=>$label,'section'=>'fin_studio','type'=>'text']);}});

add_action('woocommerce_product_query',function($query){$collection=fin_input('fin_collection');$groups=['gifts'=>['accessories','drinkware','home','art','tech','stationery'],'home-gifts'=>['drinkware','home','art','stationery']];if(!isset($groups[$collection]))return;$tax=$query->get('tax_query')?:[];$tax[]=['taxonomy'=>'product_cat','field'=>'slug','terms'=>$groups[$collection],'operator'=>'IN'];$query->set('tax_query',$tax);},15);
function fin_editorial_product_hint($product){
 $color=$product->get_attribute('Color');$slug=preg_replace('/^FIN-/','',$product->get_sku());
 if(in_array($slug,['paint-mug','campfire-enamel-mug'],true))return 'Drinkware';
 if(in_array($slug,['snowday-sling-bag','holly-zip-pouch'],true))return 'Bags & accessories';
 return $color ? wp_strip_all_tags($color) : 'Clothing';
}
function fin_editorial_nav_current($name){
 if($name==='Clothing')return function_exists('is_product_category') && is_product_category(['wear','gen-z-edit','fashion-tops','jerseys','layers','bottoms']);
 if($name==='Accessories')return function_exists('is_product_category') && is_product_category(['accessories','tech']);
 if($name==='Home & Gifts')return (function_exists('is_shop') && is_shop() && in_array(fin_input('fin_collection'),['gifts','home-gifts'],true)) || (function_exists('is_product_category') && is_product_category(['drinkware','home','art','stationery']));
 if($name==='Personalize')return function_exists('fin_active') && fin_active('designs');
 if($name==='Our story')return function_exists('fin_active') && fin_active('about');
 return $name==='Help' && function_exists('fin_active') && fin_active('contact');
}
function fin_editorial_navigation(){
 echo '<ul class="menu"><li class="has-dropdown"><a href="'.esc_url(fin_editorial_category('wear')).'" '.(fin_editorial_nav_current('Clothing')?'aria-current="page"':'').'>Clothing</a><button type="button" class="submenu-toggle" aria-label="Open clothing categories" aria-expanded="false"></button><ul class="sub-menu">';
 foreach(['gen-z-edit'=>'Streetwear Edit','fashion-tops'=>'Fashion Tops','jerseys'=>'Jerseys','layers'=>'Layers','bottoms'=>'Bottoms'] as $slug=>$name)echo '<li><a href="'.esc_url(fin_editorial_category($slug)).'">'.esc_html($name).'</a></li>';
 echo '</ul></li>';
 foreach([['Accessories',fin_editorial_category('accessories')],['Home & Gifts',add_query_arg('fin_collection','home-gifts',fin_shop_url())],['Personalize',fin_design_page_url()]] as [$name,$url])echo '<li><a href="'.esc_url($url).'" '.(fin_editorial_nav_current($name)?'aria-current="page"':'').'>'.esc_html($name).'</a></li>';
 echo '</ul>';
}
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('fin-storefront-refinement',get_template_directory_uri().'/assets/storefront-refinement.css',['fin-brand-editorial'],filemtime(get_template_directory().'/assets/storefront-refinement.css'));},90);
