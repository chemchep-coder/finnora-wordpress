<?php
defined('ABSPATH')||exit;
function fin_holiday_2026_records(){return json_decode(file_get_contents(get_template_directory().'/inc/holiday-designs.json'),true);}
function fin_holiday_2026_moods(){ $m=[];foreach(fin_holiday_2026_records() as $d)$m[$d['mood']]=$d['moodName'];return $m; }
add_action('init',function(){register_taxonomy('fin_holiday_mood','fin_design',['label'=>'Christmas moods','public'=>false,'show_ui'=>true,'show_admin_column'=>true]);},11);
function fin_design_art_url($slug){$slug=sanitize_file_name($slug);$extension=is_file(get_template_directory().'/assets/designs/'.$slug.'.webp')?'webp':'svg';return get_template_directory_uri().'/assets/designs/'.$slug.'.'.$extension;}
function fin_seed_holiday_2026(){
 $created=[];foreach(fin_holiday_2026_records() as $i=>$d){$existing=get_page_by_path($d['slug'],OBJECT,'fin_design');if($existing)continue;
 $id=wp_insert_post(['post_type'=>'fin_design','post_status'=>'publish','post_title'=>$d['name'],'post_name'=>$d['slug'],'post_content'=>$d['description'],'menu_order'=>$i-18],true);if(is_wp_error($id))continue;
 foreach(['_fin_art'=>$d['slug'],'_fin_products'=>implode(',',$d['products']),'_fin_photo'=>'','_fin_text'=>'1','_fin_christmas'=>'1','_fin_design_seed'=>'holiday-2026'] as $key=>$value)update_post_meta($id,$key,$value);
 foreach(['fin_topic'=>$d['topics'],'fin_style'=>$d['styles'],'fin_personalization'=>['add-text'],'fin_holiday_mood'=>[$d['mood']]] as $tax=>$slugs){$ids=[];foreach($slugs as $slug){$term=get_term_by('slug',$slug,$tax);if(!$term){$r=wp_insert_term($tax==='fin_holiday_mood'?$d['moodName']:ucwords(str_replace('-',' ',$slug)),$tax,['slug'=>$slug]);if(is_wp_error($r))continue;$ids[]=(int)$r['term_id'];}else $ids[]=(int)$term->term_id;}wp_set_object_terms($id,$ids,$tax);}$created[]=$id;
 }return $created;
}
add_action('admin_init',function(){if(current_user_can('manage_options')&&get_option('fin_holiday_catalog_version')!=='2026-18'){fin_seed_holiday_2026();update_option('fin_holiday_catalog_version','2026-18',false);}});
add_action('woocommerce_after_single_product_summary',function(){global $product;if(!$product)return;$slug=preg_replace('/^FIN-/','',$product->get_sku());$choices=array_filter(fin_holiday_2026_records(),fn($d)=>in_array($slug,$d['products'],true));if(!$choices)return;
 echo '<section class="fin-product-design-choices"><h2>Six ways to make it yours.</h2><p>Choose a holiday design for this product, then add your own words and review the preview.</p><div class="fin-design-grid">';foreach($choices as $d){$post=get_page_by_path($d['slug'],OBJECT,'fin_design');if(!$post)continue;$record=fin_design_record($post);$url=add_query_arg('product',$slug,fin_design_url($record));echo '<article class="fin-design-card"><a href="'.esc_url($url).'"><div class="fin-design-art"><img src="'.esc_url($record['image']).'" alt="'.esc_attr($record['name'].' artwork').'" width="800" height="800" loading="lazy"></div><h3>'.esc_html($record['name']).'</h3><p>'.esc_html($d['moodName']).'</p><span>Choose this design ↗</span></a></article>'; }echo '</div><p class="fin-design-disclosure">Illustrative artwork and schematic previews. No production-ready print file is generated.</p></section>';
},3);
