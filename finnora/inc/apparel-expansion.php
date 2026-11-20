<?php
defined('ABSPATH') || exit;
add_action('admin_init',function(){
 if(!current_user_can('manage_woocommerce')||!class_exists('WC_Product_Variable')||get_option('fin_apparel_expansion')==='1')return;
 $entries=json_decode(file_get_contents(__DIR__.'/apparel-expansion.json'),true);
 foreach($entries as [$slug,$name,$fit,$price,$shape]){
 if(wc_get_product_id_by_sku('FIN-'.$slug))continue;
 $p=new WC_Product_Variable();$p->set_name($name);$p->set_slug($slug);$p->set_sku('FIN-'.$slug);$p->set_status('publish');$p->set_catalog_visibility('visible');$p->set_short_description('A festive '.$fit.' style with original Christmas artwork. Illustrative demo — fabric, supplier measurements and production are not yet confirmed.');$p->set_description('Choose your color and size. These are concept products for exploring the FINNORA collection; illustrated images are not photographs of manufactured garments. Please confirm supplier specifications before ordering.');
 $attrs=[];foreach(['Color'=>['Forest green','Christmas red'],'Size'=>['S','M','L','XL','2XL']] as $label=>$options){$a=new WC_Product_Attribute();$a->set_name($label);$a->set_options($options);$a->set_visible(true);$a->set_variation(true);$attrs[]=$a;}$p->set_attributes($attrs);$p->update_meta_data('_fin_experience','off');$cat=get_term_by('slug','wear','product_cat');if($cat)$p->set_category_ids([$cat->term_id]);$id=$p->save();
 foreach(['Forest green','Christmas red'] as $color)foreach(['S','M','L','XL','2XL'] as $size){$v=new WC_Product_Variation();$v->set_parent_id($id);$v->set_attributes(['color'=>$color,'size'=>$size]);$v->set_regular_price($price);$v->set_stock_status('instock');$v->save();}WC_Product_Variable::sync($id);wc_delete_product_transients($id);
 }
 update_option('fin_apparel_expansion','1',false);
});
add_filter('woocommerce_product_get_image',function($image,$p){foreach(json_decode(file_get_contents(__DIR__.'/apparel-expansion.json'),true) as $entry)if($p->get_sku()==='FIN-'.$entry[0])return '<img src="'.esc_url(get_template_directory_uri().'/assets/pod-2026/'.$entry[0].'-photo.webp').'" alt="'.esc_attr($p->get_name().' — illustrative demo').'" width="800" height="800" loading="lazy">';return $image;},20,2);
add_filter('woocommerce_single_product_image_thumbnail_html',function($html,$attachment){global $product;if(!$product)return $html;foreach(json_decode(file_get_contents(__DIR__.'/apparel-expansion.json'),true) as $entry)if($product->get_sku()==='FIN-'.$entry[0])return '<div class="woocommerce-product-gallery__image">'.$product->get_image('large').'</div>';return $html;},20,2);
add_filter('woocommerce_output_related_products_args',function($args){$args['posts_per_page']=4;$args['columns']=4;return $args;});
add_filter('woocommerce_related_products',function($ids,$product_id,$args){
 $selected=[];
 foreach(['womens-holiday-vneck','mens-winter-longsleeve','unisex-christmas-hoodie','womens-festive-crop','mens-santa-raglan','unisex-oversized-tee'] as $slug){$id=wc_get_product_id_by_sku('FIN-'.$slug);if($id&&$id!==$product_id)$selected[]=$id;if(count($selected)===4)break;}
 $p=wc_get_product($product_id);if($p&&has_term('wear','product_cat',$product_id)&&count($selected))return $selected;return $ids;
},20,3);
