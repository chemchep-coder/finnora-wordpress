<?php
defined('ABSPATH') || exit;
function fin_design_topics() {
    return ['funny-quotes'=>['Funny & Quotes','A little humor. A lot of personality.'], 'people-memories'=>['People & Memories','Keep a person, a moment or a memory close.'], 'pets-animals'=>['Pets & Animals','Their personality, made into something personal.'], 'hobbies-identity'=>['Hobbies & Identity','For the things that make you, you.'], 'art-patterns'=>['Art & Patterns','Color, texture and a fresh point of view.'], 'holidays-celebrations'=>['Holidays & Celebrations','Make a special day feel a little more yours.']];
}
add_action('init',function(){
    register_post_type('fin_design',['labels'=>['name'=>'Design Library','singular_name'=>'Design','add_new_item'=>'Add a design','edit_item'=>'Edit design'],'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-art','supports'=>['title','editor','thumbnail'],'capability_type'=>'post','map_meta_cap'=>true]);
    foreach(['fin_topic'=>'Topics','fin_style'=>'Styles','fin_personalization'=>'Personalization'] as $taxonomy=>$label) register_taxonomy($taxonomy,'fin_design',['label'=>$label,'public'=>false,'show_ui'=>true,'show_admin_column'=>true,'hierarchical'=>$taxonomy==='fin_topic']);
});
function fin_design_page_url($type='designs') {
    $slugs=['designs'=>'explore-designs','upload'=>'upload-your-photo','christmas'=>'christmas-collection'];
    $page=get_page_by_path($slugs[$type]??$slugs['designs']);
    return $page?get_permalink($page):home_url('/'.($slugs[$type]??$slugs['designs']).'/');
}
function fin_design_record($post) {
    if(!$post || $post->post_type!=='fin_design' || $post->post_status!=='publish') return null;
    return ['id'=>$post->ID,'slug'=>$post->post_name,'name'=>$post->post_title,'description'=>wp_strip_all_tags($post->post_content),'image'=>get_the_post_thumbnail_url($post,'large')?:fin_design_art_url(get_post_meta($post->ID,'_fin_art',true)),'art'=>get_post_meta($post->ID,'_fin_art',true),'photo'=>(bool)get_post_meta($post->ID,'_fin_photo',true),'text'=>(bool)get_post_meta($post->ID,'_fin_text',true),'christmas'=>(bool)get_post_meta($post->ID,'_fin_christmas',true),'products'=>array_values(array_filter(array_map('sanitize_key',explode(',',get_post_meta($post->ID,'_fin_products',true)))) )];
}
function fin_design_url($design){return add_query_arg('design',$design['slug'],fin_design_page_url());}
function fin_design_term_slugs($id,$tax){$terms=wp_get_post_terms($id,$tax,['fields'=>'slugs']);return is_wp_error($terms)?[]:$terms;}
function fin_design_records($seasonal=false){$posts=get_posts(['post_type'=>'fin_design','post_status'=>'publish','numberposts'=>-1,'orderby'=>'menu_order title','order'=>'ASC']);$out=[];foreach($posts as $p){$d=fin_design_record($p);if($d&&(!$seasonal||$d['christmas']))$out[]=$d;}return $out;}
function fin_design_card($design){?><article class="fin-design-card"><a href="<?php echo esc_url(fin_design_url($design)); ?>"><div class="fin-design-art"><?php fin_holiday_reindeer(); ?><img src="<?php echo esc_url($design['image']); ?>" alt="<?php echo esc_attr($design['name'].' artwork'); ?>" width="600" height="600" loading="lazy"></div><h3><?php echo esc_html($design['name']); ?></h3><p><?php echo $design['photo']?'Add your photo'.($design['text']?' & text':''):($design['text']?'Add your own text':'Ready-to-use artwork'); ?></p><span>View design ↗</span></a></article><?php }
function fin_home_design_topics(){
    $cover=['funny-quotes'=>'social-battery','people-memories'=>'photo-story','pets-animals'=>'pet-monarch','hobbies-identity'=>'weekend-reader','art-patterns'=>'evergreen-lines','holidays-celebrations'=>'gingerbread-wishes'];
    ?><section id="find-your-design" class="container fin-topic-section"><div class="section-heading"><div><h2><?php echo esc_html(fin_setting_text('fin_home_category_title','Find your design.')); ?></h2><p>Start with what you would like to express.</p></div><a class="text-link" href="<?php echo esc_url(fin_design_page_url()); ?>">Explore all designs ↗</a></div><div class="fin-topic-grid"><?php foreach(fin_design_topics() as $slug=>[$name,$description]):?><a class="fin-topic-card" href="<?php echo esc_url(add_query_arg('topic',$slug,fin_design_page_url())); ?>"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/designs/'.$cover[$slug].'.svg'); ?>" alt="" width="600" height="600" loading="lazy"><div><h3><?php echo esc_html($name); ?></h3><p><?php echo esc_html($description); ?></p><span>Explore ↗</span></div></a><?php endforeach; ?></div></section><?php
}
function fin_home_christmas_designs(){?><section class="container fin-season-designs"><div class="section-heading"><div><h2>Christmas, your way.</h2><p>One season. A little more of your personality.</p></div><a class="text-link" href="<?php echo esc_url(fin_design_page_url('christmas')); ?>">Christmas Collection ↗</a></div><div class="fin-design-grid"><?php foreach(array_slice(fin_design_records(true),0,4) as $design)fin_design_card($design); ?></div></section><?php }
function fin_design_products($design){
    $out=[];if(!function_exists('wc_get_product_id_by_sku'))return $out;
    foreach($design['products'] as $slug){$p=wc_get_product(wc_get_product_id_by_sku('FIN-'.$slug));if(!$p||!$p->is_purchasable()||$p->get_status()!=='publish')continue;
        $vars=$p->is_type('variable')?$p->get_available_variations():[];$options=[];
        foreach($vars as $v){if(!$v['is_purchasable']||!$v['is_in_stock']||!$v['variation_is_active'])continue;$options[]=['id'=>$v['variation_id'],'attributes'=>$v['attributes'],'label'=>implode(' / ',array_values($v['attributes'])),'price'=>(float)$v['display_price'],'priceText'=>html_entity_decode(wp_strip_all_tags(wc_price($v['display_price'])),ENT_QUOTES,'UTF-8')];}
        if($p->is_type('variable')&&!$options)continue;
        $out[]=['id'=>$p->get_id(),'slug'=>$slug,'name'=>$p->get_name(),'priceText'=>html_entity_decode(wp_strip_all_tags(wc_price($p->get_price())),ENT_QUOTES,'UTF-8'),'price'=>(float)$p->get_price(),'options'=>$options];
    }return $out;
}
function fin_seed_design_library(){
    $topic_ids=[];foreach(fin_design_topics() as $slug=>[$name]){$term=get_term_by('slug',$slug,'fin_topic');$result=$term?['term_id'=>$term->term_id]:wp_insert_term($name,'fin_topic',['slug'=>$slug]);if(!is_wp_error($result))$topic_ids[$slug]=(int)$result['term_id'];}
    $styles=['typography'=>'Typography','minimal'=>'Minimal','doodle'=>'Doodle','collage'=>'Collage','portrait-art'=>'Portrait frame','retro'=>'Retro','line-art'=>'Line Art','pixel'=>'Pixel','botanical'=>'Botanical','abstract'=>'Abstract','patterns'=>'Patterns','cartoon'=>'Cartoon'];
    $style_ids=[];foreach($styles as $slug=>$name){$term=get_term_by('slug',$slug,'fin_style');$r=$term?['term_id'=>$term->term_id]:wp_insert_term($name,'fin_style',['slug'=>$slug]);if(!is_wp_error($r))$style_ids[$slug]=(int)$r['term_id'];}
    $cap_ids=[];foreach(['add-text'=>'Add Text','upload-photo'=>'Upload Photo','ready-to-use'=>'Ready to Use'] as $slug=>$name){$term=get_term_by('slug',$slug,'fin_personalization');$r=$term?['term_id'=>$term->term_id]:wp_insert_term($name,'fin_personalization',['slug'=>$slug]);if(!is_wp_error($r))$cap_ids[$slug]=(int)$r['term_id'];}
    $records=json_decode(file_get_contents(get_template_directory().'/inc/design-seed.json'),true);$created=[];
    foreach($records as $index=>$d){$existing=get_page_by_path($d['slug'],OBJECT,'fin_design');if($existing)continue;$id=wp_insert_post(['post_type'=>'fin_design','post_status'=>'publish','post_title'=>$d['name'],'post_name'=>$d['slug'],'post_content'=>$d['description'],'menu_order'=>$index],true);if(is_wp_error($id))continue;
        foreach(['_fin_art'=>$d['slug'],'_fin_products'=>implode(',',$d['products']),'_fin_photo'=>$d['photo']?'1':'','_fin_text'=>$d['text']?'1':'','_fin_christmas'=>$d['christmas']?'1':'','_fin_design_seed'=>'1'] as $key=>$value)update_post_meta($id,$key,$value);
        wp_set_object_terms($id,array_values(array_intersect_key($topic_ids,array_flip($d['topics']))),'fin_topic');
        wp_set_object_terms($id,array_values(array_intersect_key($style_ids,array_flip($d['styles']))),'fin_style');
        $caps=[];if($d['photo'])$caps[]=$cap_ids['upload-photo'];if($d['text'])$caps[]=$cap_ids['add-text'];if(!$caps)$caps[]=$cap_ids['ready-to-use'];wp_set_object_terms($id,$caps,'fin_personalization');$created[]=$id;
    }
    foreach(['explore-designs'=>['Explore Designs','page-designs.php'],'upload-your-photo'=>['Upload Your Photo','page-upload.php'],'christmas-collection'=>['Christmas Collection','page-christmas.php']] as $slug=>[$name,$template]){if(!get_page_by_path($slug))wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$name,'post_name'=>$slug,'meta_input'=>['_wp_page_template'=>$template,'_fin_owned'=>'1']]);}
    return $created;
}
add_action('after_switch_theme','fin_seed_design_library',30);
add_action('admin_menu',function(){add_submenu_page('edit.php?post_type=fin_design','Design Library setup','Setup & guidance','manage_options','fin-design-setup',function(){?><div class="wrap"><h1>FINNORA Design Library</h1><p>Manage designs, Topics, Styles and Personalization using the Design Library menu. Each design has its own artwork and compatible product mapping. Bundled designs are original illustrative demos.</p><p>The preview is a manual 2D composition, not AI or a print-production file. Photo drafts stay in the customer browser until added to the bag; saved order assets expire after seven days.</p><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="fin_seed_designs"><?php wp_nonce_field('fin_seed_designs'); ?><button class="button button-primary">Add missing sample designs and pages</button></form><p>Existing records are never overwritten by this action.</p></div><?php });});
add_action('admin_post_fin_seed_designs',function(){if(!current_user_can('manage_options'))wp_die('Not allowed');check_admin_referer('fin_seed_designs');fin_seed_design_library();wp_safe_redirect(admin_url('edit.php?post_type=fin_design'));exit;});
add_action('add_meta_boxes',function(){add_meta_box('fin-design-settings','Design configuration',function($post){wp_nonce_field('fin_design_admin','fin_design_admin_nonce');foreach(['_fin_photo'=>'Allow a customer photo','_fin_text'=>'Allow a name or message','_fin_christmas'=>'Include in Christmas Collection'] as $key=>$label){echo '<p><label><input type="checkbox" name="'.esc_attr($key).'" value="1" '.checked(get_post_meta($post->ID,$key,true),'1',false).'> '.esc_html($label).'</label></p>';}echo '<p><label>Compatible FINNORA product slugs (comma-separated)<br><input style="width:100%" name="_fin_products" value="'.esc_attr(get_post_meta($post->ID,'_fin_products',true)).'"></label></p><p><label>Bundled artwork slug<br><input name="_fin_art" value="'.esc_attr(get_post_meta($post->ID,'_fin_art',true)).'"></label></p><p>Use a Featured image for gallery artwork. Topics, styles and personalization tags are separate taxonomies. Check every product mapping and print area before real sales.</p>';},'fin_design');});
add_action('save_post_fin_design',function($id){if(!isset($_POST['fin_design_admin_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fin_design_admin_nonce'])),'fin_design_admin')||!current_user_can('edit_post',$id)||wp_is_post_revision($id))return;foreach(['_fin_photo','_fin_text','_fin_christmas'] as $key)update_post_meta($id,$key,isset($_POST[$key])?'1':'');foreach(['_fin_products','_fin_art'] as $key)if(isset($_POST[$key])&&is_string($_POST[$key]))update_post_meta($id,$key,sanitize_text_field(wp_unslash($_POST[$key])));});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('fin-designs',get_template_directory_uri().'/assets/designs.css',['fin-breathing-room'],filemtime(get_template_directory().'/assets/designs.css'));if(is_page_template('page-designs.php')||is_page_template('page-christmas.php')||is_page_template('page-upload.php'))wp_enqueue_script('fin-design-builder',get_template_directory_uri().'/assets/design-builder.js',[],filemtime(get_template_directory().'/assets/design-builder.js'),true);},45);

add_action('customize_register',function($c){
    foreach(['fin_home_collection_title','fin_home_collection_intro','fin_home_process_title','fin_home_product_count','fin_home_step1_title','fin_home_step1_text','fin_home_step2_title','fin_home_step2_text','fin_home_step3_title','fin_home_step3_text'] as $key)$c->remove_control($key);
},60);
add_action('save_post_fin_design',function($id){
    if(!isset($_POST['fin_design_admin_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fin_design_admin_nonce'])),'fin_design_admin')||!current_user_can('edit_post',$id)||wp_is_post_revision($id))return;
    $slugs=[];if(get_post_meta($id,'_fin_photo',true))$slugs[]='upload-photo';if(get_post_meta($id,'_fin_text',true))$slugs[]='add-text';if(!$slugs)$slugs[]='ready-to-use';
    $ids=[];foreach($slugs as $slug){$term=get_term_by('slug',$slug,'fin_personalization');if($term)$ids[]=(int)$term->term_id;}wp_set_object_terms($id,$ids,'fin_personalization');
},20);
