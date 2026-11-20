<?php
define('ABSPATH',__DIR__);
$filters=[];$actions=[];$current=null;
function add_filter($name,$fn,...$args){global $filters;$filters[$name]=$fn;}
function add_action($name,$fn,...$args){global $actions;$actions[$name]=$fn;}
function is_product(){return true;}
function get_queried_object_id(){return 1;}
function wc_get_product($id){global $current;return $current;}
function esc_url_raw($url){return $url;}
class TestProduct {
 function __construct(public $sku='FIN-test',public $verified='no',public $variation=false){}
 function is_type($type){return $this->variation&&$type==='variation';}
 function get_parent_id(){return 1;}
 function get_sku(){return $this->sku;}
 function get_meta($key){return $this->verified;}
 function get_image($size){return '<img src="https://example.com/product.webp">';}
}
require dirname(__DIR__).'/finnora/inc/merchant-readiness.php';
function check($ok,$message){if(!$ok)throw new Exception($message);}
$current=new TestProduct;
check(fin_is_unverified_concept($current),'Concept must require verification');
check($filters['woocommerce_structured_data_product'](['offers'=>['availability'=>'InStock']],$current)===[],'Concept Offer must not be advertised');
check(($filters['wp_robots']([])['noindex']??false)===true,'Concept page must be noindex');
$variation=new TestProduct('FIN-variation','yes',true);
check(fin_is_unverified_concept($variation),'Variation must inherit parent verification');
$current->verified='yes';
$schema=$filters['woocommerce_structured_data_product'](['offers'=>['price'=>'42']],$current);
check(($schema['offers']['price']??null)==='42','Verified offer must be retained');
check(($schema['image']??null)==='https://example.com/product.webp','Verified image must be included');
check(!isset($filters['wp_robots']([])['noindex']),'Verified product must not be blocked');
check(!fin_is_unverified_concept(new TestProduct('OTHER-SKU')),'Unrelated merchant products must not be blocked');
echo "PASS: concept/verified schema, robots, image and variation inheritance\n";
