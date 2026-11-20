<?php
defined('ABSPATH') || exit;
function fin_category_expansion(){static $rows;return $rows??=json_decode(file_get_contents(__DIR__.'/category-expansion.json'),true);}
function fin_category_expansion_record($product){$sku=$product->get_sku();if($product->is_type('variation')){$parent=wc_get_product($product->get_parent_id());$sku=$parent?$parent->get_sku():$sku;}foreach(fin_category_expansion() as $row)if($sku==='FIN-'.$row['slug'])return $row;return null;}
add_action('admin_init',function(){
 if(!current_user_can('manage_woocommerce')||!class_exists('WC_Product_Variable')||get_option('fin_category_expansion')==='1')return;
 foreach(fin_categories() as $slug=>$name)if(!get_term_by('slug',$slug,'product_cat'))wp_insert_term($name,'product_cat',['slug'=>$slug]);
 $wear=get_term_by('slug','wear','product_cat');
 foreach(['bottoms'=>'Bottoms','jerseys'=>'Jerseys','layers'=>'Layers','fashion-tops'=>'Fashion Tops','gen-z-edit'=>'Gen Z Edit'] as $slug=>$name)if(!get_term_by('slug',$slug,'product_cat'))wp_insert_term($name,'product_cat',['slug'=>$slug,'parent'=>$wear?$wear->term_id:0]);
 $clothing=['bottoms','jerseys','layers','fashion-tops'];$colors=['snowbreak-wideleg-shorts'=>'Stone','midnight-satin-pants'=>'Forest green','holiday-hoops-tank'=>'Christmas red / white','northstar-baseball-jersey'=>'White / forest trim','snowleague-hockey-jersey'=>'Forest green / red','cloud-fleece-zip-vest'=>'Ivory / forest green','winterwave-windbreaker'=>'Forest green / white','ribbon-halter-top'=>'Christmas red'];
 foreach(fin_category_expansion() as $row){
 $slug=$row['slug'];if(wc_get_product_id_by_sku('FIN-'.$slug))continue;$apparel=in_array($row['cat'],$clothing,true);$p=$apparel?new WC_Product_Variable():new WC_Product_Simple();$p->set_name($row['name']);$p->set_slug($slug);$p->set_sku('FIN-'.$slug);$p->set_status('publish');$p->set_catalog_visibility('visible');
 $p->set_short_description(($apparel?'A new holiday silhouette':'An original Christmas design on an everyday favourite').'. Material concept: '.$row['material'].'. Illustrative demo; supplier specifications are not verified.');
 $p->set_description('<p>'.esc_html($row['name']).' brings a different shape, purpose and original design to the FINNORA Christmas collection.</p><p>AI-generated product mockup. Material, dimensions, print process and compatibility are concepts pending supplier confirmation and physical sample checks. Photos do not prove manufactured quality. Production and delivery are not connected.</p>');
 $attrs=[];$options=$apparel?['Color'=>[[$colors[$slug]],true],'Size'=>[['XS','S','M','L','XL'],true],'Material concept'=>[[$row['material']],false]]:['Material concept'=>[[$row['material']],false],'Design'=>[['Shown Christmas artwork'],false]];
 foreach($options as $label=>[$values,$variation]){$a=new WC_Product_Attribute();$a->set_name($label);$a->set_options($values);$a->set_visible(true);$a->set_variation($variation);$attrs[]=$a;}$p->set_attributes($attrs);$p->update_meta_data('_fin_experience','off');$p->update_meta_data('_fin_catalog_demo','yes');$cats=[];$cat=get_term_by('slug',$row['cat'],'product_cat');if($cat)$cats[]=$cat->term_id;
 if($apparel)foreach(['wear','gen-z-edit'] as $extra){$term=get_term_by('slug',$extra,'product_cat');if($term)$cats[]=$term->term_id;}$p->set_category_ids($cats);
 if(!$apparel){$p->set_regular_price($row['price']);$p->set_stock_status('instock');}$id=$p->save();
 if($apparel){foreach(['XS','S','M','L','XL'] as $size){$v=new WC_Product_Variation();$v->set_parent_id($id);$v->set_attributes(['color'=>$colors[$slug],'size'=>$size]);$v->set_regular_price($row['price']);$v->set_stock_status('instock');$v->save();}WC_Product_Variable::sync($id);}wc_delete_product_transients($id);
 }
 update_option('fin_category_expansion','1',false);
});
function fin_category_expansion_image($p){$row=fin_category_expansion_record($p);return $row?'<img src="'.esc_url(get_template_directory_uri().'/assets/pod-2026/'.$row['slug'].'-photo.webp').'" alt="'.esc_attr($row['name'].' — AI-generated illustrative demo').'" width="900" height="900" loading="lazy">':'';}
add_filter('woocommerce_product_get_image',function($html,$p){return fin_category_expansion_image($p)?:$html;},40,2);
add_filter('woocommerce_single_product_image_thumbnail_html',function($html,$attachment){global $product;if(!$product||!fin_category_expansion_record($product))return $html;return '<div class="woocommerce-product-gallery__image">'.fin_category_expansion_image($product).'</div>';},40,2);
add_action('woocommerce_after_shop_loop_item_title',function(){global $product;if(!$product||!($row=fin_category_expansion_record($product)))return;echo '<p class="fin-fabric-note">'.esc_html($row['material'].' · concept').'</p>';},5);
add_filter('woocommerce_related_products',function($ids,$product_id,$args){
 if(has_term('wear','product_cat',$product_id))return $ids;
 $terms=wp_get_post_terms($product_id,'product_cat',['fields'=>'slugs']);if(is_wp_error($terms)||!$terms)return $ids;
 $related=wc_get_products(['status'=>'publish','visibility'=>'visible','category'=>$terms,'exclude'=>[$product_id],'limit'=>4,'return'=>'ids']);
 if(count($related)<4){$complements=['accessories'=>['stationery','tech'],'drinkware'=>['home','stationery'],'home'=>['art','drinkware'],'art'=>['home','stationery'],'tech'=>['stationery','accessories'],'stationery'=>['tech','accessories']];$other=$complements[$terms[0]]??[];if($other)$related=array_merge($related,wc_get_products(['status'=>'publish','visibility'=>'visible','category'=>$other,'exclude'=>array_merge([$product_id],$related),'limit'=>4-count($related),'return'=>'ids']));}
 return count($related)?array_slice(array_unique($related),0,4):$ids;
},40,3);
