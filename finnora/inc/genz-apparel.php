<?php
defined('ABSPATH') || exit;
function fin_genz_catalog(){static $rows;return $rows??=json_decode(file_get_contents(__DIR__.'/genz-apparel.json'),true);}
function fin_genz_record($product){$sku=$product->get_sku();if($product->is_type('variation')){$parent=wc_get_product($product->get_parent_id());$sku=$parent?$parent->get_sku():$sku;}foreach(fin_genz_catalog() as $row)if($sku==='FIN-'.$row[0])return $row;return null;}
add_action('admin_init',function(){
 if(!current_user_can('manage_woocommerce')||!class_exists('WC_Product_Variable')||get_option('fin_genz_catalog')==='1')return;
 $wear=get_term_by('slug','wear','product_cat');$parent=$wear?$wear->term_id:0;
 $get_cat=function($name,$slug)use($parent){$term=get_term_by('slug',$slug,'product_cat');if($term)return $term->term_id;$created=wp_insert_term($name,'product_cat',['slug'=>$slug,'parent'=>$parent]);return is_wp_error($created)?0:$created['term_id'];};
 $edit=$get_cat('Gen Z Edit','gen-z-edit');
 foreach(fin_genz_catalog() as [$slug,$name,$fabric,$fit,$color,$price,$group,$finish,$copy]){
 if(wc_get_product_id_by_sku('FIN-'.$slug))continue;
 $p=new WC_Product_Variable();$p->set_name($name);$p->set_slug($slug);$p->set_sku('FIN-'.$slug);$p->set_status('publish');$p->set_catalog_visibility('visible');
 $p->set_short_description($copy.' Fabric concept: '.$fabric.'.');
 $p->set_description('<p>'.esc_html($copy).'</p><p>Material and fit are design concepts, not verified supplier specifications. The print method is proposed and requires material-specific sampling, wash tests and supplier approval. AI-generated product imagery is illustrative. This demo is not connected to manufacturing or delivery.</p>');
 $attrs=[];foreach(['Color'=>[[$color],true],'Size'=>[['XS','S','M','L','XL'],true],'Fabric concept'=>[[$fabric],false],'Fit concept'=>[[$fit],false],'Print / finish concept'=>[[$finish],false]] as $label=>[$options,$variation]){$a=new WC_Product_Attribute();$a->set_name($label);$a->set_options($options);$a->set_visible(true);$a->set_variation($variation);$attrs[]=$a;}
 $p->set_attributes($attrs);$p->update_meta_data('_fin_experience','off');$p->update_meta_data('_fin_genz_demo','yes');$cats=array_filter([$parent,$edit,$get_cat($group,sanitize_title($group))]);$p->set_category_ids($cats);$id=$p->save();
 foreach(['XS','S','M','L','XL'] as $size){$v=new WC_Product_Variation();$v->set_parent_id($id);$v->set_attributes(['color'=>$color,'size'=>$size]);$v->set_regular_price($price);$v->set_stock_status('instock');$v->save();}
 WC_Product_Variable::sync($id);wc_delete_product_transients($id);
 }
 update_option('fin_genz_catalog','1',false);
});
function fin_genz_image($p){$row=fin_genz_record($p);return $row?'<img src="'.esc_url(get_template_directory_uri().'/assets/pod-2026/'.$row[0].'-photo.webp').'" alt="'.esc_attr($row[1].' — AI-generated illustrative fabric concept').'" width="900" height="900" loading="lazy">':'';}
add_filter('woocommerce_product_get_image',function($html,$p){return fin_genz_image($p)?:$html;},30,2);
add_filter('woocommerce_single_product_image_thumbnail_html',function($html,$attachment){global $product;if(!$product||!fin_genz_record($product))return $html;return '<div class="woocommerce-product-gallery__image">'.fin_genz_image($product).'</div>';},30,2);
add_filter('woocommerce_related_products',function($ids,$product_id,$args){if(!has_term('wear','product_cat',$product_id))return $ids;$slugs=['afterhours-rib-baby-tee','snowclub-mesh-jersey','evergreen-ripstop-cargo','frostline-denim-vest','midnight-satin-shirt','ribbon-pleat-mini','starlight-drape-tank','snowline-track-jacket'];$current=wc_get_product($product_id);if($current){$index=array_search(preg_replace('/^FIN-/','',$current->get_sku()),$slugs,true);if($index!==false)$slugs=array_merge(array_slice($slugs,$index+1),array_slice($slugs,0,$index+1));}$out=[];foreach($slugs as $slug){$id=wc_get_product_id_by_sku('FIN-'.$slug);if($id&&$id!==$product_id)$out[]=$id;if(count($out)===4)break;}return $out?:$ids;},30,3);
add_action('woocommerce_after_shop_loop_item_title',function(){global $product;if(!$product||!($row=fin_genz_record($product)))return;echo '<p class="fin-fabric-note">'.esc_html($row[2].' · fabric concept').'</p>';},5);
