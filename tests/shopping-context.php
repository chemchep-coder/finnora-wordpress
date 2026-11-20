<?php
define('ABSPATH',__DIR__);
$actions=[];$filters=[];$inputs=[];$mods=[];
function add_action($name,$callback,...$args){global $actions;$actions[$name][]=$callback;}
function add_filter($name,$callback,...$args){global $filters;$filters[$name][]=$callback;}
function fin_input($key){global $inputs;return $inputs[$key]??'';}
function sanitize_text_field($value){return trim(strip_tags($value));}
function sanitize_title($value){return preg_replace('/[^a-z0-9-]/','',$value);}
function get_theme_mod($key,$default=''){global $mods;return $mods[$key]??$default;}
require dirname(__DIR__).'/finnora/inc/home-editorial.php';
require dirname(__DIR__).'/finnora/inc/storefront.php';
function check($condition,$message){if(!$condition)throw new Exception($message);}
class TestQuery {public $values=[];function get($key){return $this->values[$key]??null;}function set($key,$value){$this->values[$key]=$value;}}
$inputs=['s'=>'  jersey  ','fin_collection'=>'home-gifts','min_price'=>'10'];
check(fin_filter_context()===['s'=>'jersey','post_type'=>'product','fin_collection'=>'home-gifts'],'Search/collection should survive filter submissions and clear links');
$query=new TestQuery;$query->set('tax_query',[['taxonomy'=>'product_visibility']]);
foreach($actions['woocommerce_product_query'] as $callback)$callback($query);
check(count($query->get('tax_query'))===2,'Existing visibility constraints must survive');
check($query->get('tax_query')[1]['terms']===['drinkware','home','art','stationery'],'Home & Gifts must not silently include clothing/accessories');
$inputs=['fin_collection'=>'unknown'];check(fin_filter_context()===[],'Unsupported collections should not propagate');
$query=new TestQuery;foreach($actions['woocommerce_product_query'] as $callback)$callback($query);check(!$query->get('tax_query'),'Unknown collection should not alter products');
$mods=['heading'=>'Old heading'];check(fin_editorial_copy('heading','New heading',['Old heading'])==='New heading','Bundled legacy copy should upgrade');
$mods=['heading'=>'My custom heading'];check(fin_editorial_copy('heading','New heading',['Old heading'])==='My custom heading','Owner copy must not be overwritten');
echo "PASS: filter context, collection boundaries, query preservation and owner copy\n";
