<?php
defined('ABSPATH') || exit;
function fin_design_private_dir(){return dirname(untrailingslashit(ABSPATH)).'/finnora-private-designs';}
function fin_design_asset_save($bytes,$mime){
    $dir=fin_design_private_dir();if(!wp_mkdir_p($dir))return false;
    $root=wp_normalize_path(trailingslashit(ABSPATH));if(str_starts_with(wp_normalize_path($dir).'/',$root))return false;
    $token=bin2hex(random_bytes(24));$path=$dir.'/'.$token;
    if(file_put_contents($path,$bytes,LOCK_EX)===false)return false;
    @chmod($path,0600);
    $owner=hash('sha256',WC()->session->get_customer_id());
    set_transient('fin_asset_'.$token,['path'=>$path,'mime'=>$mime,'owner'=>$owner,'user'=>get_current_user_id()],7*DAY_IN_SECONDS);
    wp_schedule_single_event(time()+7*DAY_IN_SECONDS,'fin_design_cleanup',[$token,$path]);
    return $token;
}
add_action('fin_design_cleanup',function($token,$path){if(preg_match('/^[a-f0-9]{48}$/',$token)&&$path===fin_design_private_dir().'/'.$token){wp_delete_file($path);delete_transient('fin_asset_'.$token);}},10,2);
function fin_design_asset_url($token){return add_query_arg(['action'=>'fin_design_asset','token'=>$token],admin_url('admin-post.php'));}
function fin_design_serve_asset(){
    if(function_exists('WC') && !WC()->session)wc_load_cart();
    $token=isset($_GET['token'])&&is_string($_GET['token'])?sanitize_key(wp_unslash($_GET['token'])):'';
    $asset=preg_match('/^[a-f0-9]{48}$/',$token)?get_transient('fin_asset_'.$token):false;
    $allowed=$asset&&((function_exists('WC')&&WC()->session&&hash_equals($asset['owner'],hash('sha256',WC()->session->get_customer_id())))||($asset['user']&&get_current_user_id()===$asset['user'])||current_user_can('manage_woocommerce'));
    if(!$allowed||!is_file($asset['path'])){status_header(404);exit;}
    nocache_headers();header('X-Content-Type-Options: nosniff');header('Content-Type: '.$asset['mime']);header('Content-Disposition: inline; filename="finnora-design-preview"');readfile($asset['path']);exit;
}
add_action('admin_post_fin_design_asset','fin_design_serve_asset');add_action('admin_post_nopriv_fin_design_asset','fin_design_serve_asset');
function fin_design_add_to_cart(){
    if(!function_exists('WC'))wp_die('WooCommerce is required.');
    if(!WC()->cart)wc_load_cart();
    WC()->cart->get_cart();
    $get=function($key){return isset($_POST[$key])&&is_string($_POST[$key])?sanitize_text_field(wp_unslash($_POST[$key])):'';};
    $design=fin_design_record(get_post(absint($get('fin_design_id'))));
    $return=$get('fin_return')==='upload'?fin_design_page_url('upload'):($design?fin_design_url($design):fin_design_page_url());
    $fail=function($reason)use($return){wp_safe_redirect(add_query_arg('design_error',$reason,$return));exit;};
    if(!wp_verify_nonce($get('fin_design_nonce'),'fin_design_add')||!$design)$fail('invalid');
    $layout=$get('fin_layout');if($layout&&$get('fin_return')==='upload'){
        if(!in_array($layout,['photo-story','together-at-christmas'],true))$fail('invalid');
        $design=fin_design_record(get_page_by_path($layout,OBJECT,'fin_design'));if(!$design)$fail('invalid');
    }
    $pid=absint($get('fin_product_id'));$variation=absint($get('fin_variation_id'));$product=wc_get_product($pid);$compatible=array_column(fin_design_products($design),'id');
    if(!$product||!in_array($pid,$compatible,true)||!$product->is_purchasable()||!$product->is_in_stock())$fail('stock');
    $attributes=[];
    if($product->is_type('variable')){$v=wc_get_product($variation);if(!$v||!$v->is_type('variation')||$v->get_parent_id()!==$pid||!$v->is_purchasable()||!$v->is_in_stock()||!$v->variation_is_active())$fail('stock');$attributes=$v->get_variation_attributes();}
    elseif($variation)$fail('invalid');
    $qty=filter_var($get('quantity'),FILTER_VALIDATE_INT);if(!$qty||$qty<1||$qty>20)$fail('invalid');
    $message=$design['text']?$get('fin_message'):'';if(mb_strlen($message)>60)$fail('invalid');
    $edit_key=sanitize_key($get('fin_edit_key'));
    if($edit_key){$existing=WC()->cart->get_cart_item($edit_key);if(!$existing||empty($existing['fin_design'])||(int)$existing['fin_design']['id']!==(int)$design['id'])$fail('invalid');}
    if($get('fin_confirm')!=='1')$fail('preview');
    $preview=isset($_POST['fin_preview'])&&is_string($_POST['fin_preview'])?wp_unslash($_POST['fin_preview']):'';
    if(strlen($preview)>2500000||!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#',$preview,$match))$fail('preview');
    $preview_bytes=base64_decode($match[1],true);$info=$preview_bytes?@getimagesizefromstring($preview_bytes):false;
    if(!$info||$info['mime']!=='image/png'||$info[0]>1200||$info[1]>1200)$fail('preview');
    $photo_bytes=null;$photo_mime='';
    if($design['photo']){
        $file=$_FILES['fin_photo']??null;
        if(!$file||!isset($file['error'],$file['tmp_name'],$file['size'])||is_array($file['error'])||!is_string($file['tmp_name'])||$file['error']!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name'])||$file['size']>8*1024*1024)$fail('photo');
        $photo_info=@getimagesize($file['tmp_name']);if(!$photo_info||!in_array($photo_info['mime'],['image/jpeg','image/png','image/webp'],true)||$photo_info[0]*$photo_info[1]>24000000)$fail('photo');
        $photo_bytes=file_get_contents($file['tmp_name']);$photo_mime=$photo_info['mime'];
    }
    WC()->session->set_customer_session_cookie(true);
    $token=fin_design_asset_save($preview_bytes,'image/png');if(!$token)$fail('storage');
    $photo_token=$photo_bytes?fin_design_asset_save($photo_bytes,$photo_mime):'';if($photo_bytes&&!$photo_token)$fail('storage');
    $look=in_array($get('fin_look'),['original','monochrome'],true)?$get('fin_look'):'original';
    $config=['id'=>$design['id'],'name'=>$design['name'],'text'=>$message,'look'=>$look,'preview'=>$token,'photo'=>$photo_token,'zoom'=>max(1,min(3,(float)$get('fin_zoom'))),'crop_x'=>max(-100,min(100,(int)$get('fin_crop_x'))),'crop_y'=>max(-100,min(100,(int)$get('fin_crop_y'))),'draft'=>preg_match('/^[a-z0-9_-]{1,80}$/',$get('fin_design_draft'))?$get('fin_design_draft'):''];
    $key=WC()->cart->add_to_cart($pid,$qty,$variation,$attributes,['fin_design'=>$config]);if(!$key)$fail('stock');
    if($edit_key && $edit_key!==$key)WC()->cart->remove_cart_item($edit_key);
    wp_safe_redirect(wc_get_cart_url());exit;
}
add_action('admin_post_fin_design_add','fin_design_add_to_cart');add_action('admin_post_nopriv_fin_design_add','fin_design_add_to_cart');
add_filter('woocommerce_get_item_data',function($data,$item){if(empty($item['fin_design']))return $data;$d=$item['fin_design'];$data[]=['key'=>'Design','value'=>$d['name']];if($d['text'])$data[]=['key'=>'Personalization','value'=>$d['text']];if($d['photo'])$data[]=['key'=>'Photo look','value'=>$d['look']==='monochrome'?'Black & white':'Original photo'];$data[]=['key'=>'Preview','display'=>'<a href="'.esc_url(fin_design_asset_url($d['preview'])).'" target="_blank" rel="noopener">View saved design ↗</a>','value'=>'Illustrative preview'];if($d['draft'])$data[]=['key'=>'Edit','display'=>'<a href="'.esc_url(add_query_arg(['design'=>get_post_field('post_name',$d['id']),'draft'=>$d['draft'],'edit_item'=>$item['key']],fin_design_page_url())).'">Open saved browser draft ↗</a>','value'=>'Browser draft'];return $data;},10,2);
add_filter('woocommerce_cart_item_thumbnail',function($html,$item){if(empty($item['fin_design']))return $html;return '<img src="'.esc_url(fin_design_asset_url($item['fin_design']['preview'])).'" width="100" height="100" alt="Saved personalized design preview">';},10,2);
add_action('woocommerce_checkout_create_order_line_item',function($item,$key,$values){if(empty($values['fin_design']))return;$d=$values['fin_design'];$item->add_meta_data('Design',$d['name']);if($d['text'])$item->add_meta_data('Personalization',$d['text']);if($d['photo'])$item->add_meta_data('Photo look',$d['look']);$item->add_meta_data('_fin_design_configuration',$d);$item->add_meta_data('Preview status','Illustrative demo; no print-production file generated.');},10,3);
add_action('woocommerce_after_order_itemmeta',function($item_id,$item){
    if(!current_user_can('manage_woocommerce')||!is_a($item,'WC_Order_Item_Product'))return;$d=$item->get_meta('_fin_design_configuration',true);if(!$d)return;
    echo '<p><strong>FINNORA design assets</strong><br>';
    foreach(['preview'=>'Saved preview','photo'=>'Original photo'] as $key=>$label){if(empty($d[$key]))continue;if(get_transient('fin_asset_'.$d[$key]))echo '<a href="'.esc_url(fin_design_asset_url($d[$key])).'" target="_blank" rel="noopener">'.esc_html($label).' ↗</a><br>';else echo esc_html($label.': retention period ended.').'<br>';}
    echo 'Illustrative demo assets, retained for 7 days. No production-ready print file.</p>';
},10,2);
