<?php
/**
* Plugin Name: Thelia Product API
* Plugin URI: https://github.com/Cav0n/ProductAPI
* Description: Show products informations from your Thelia directly on your WordPress blog
* Version: 0.1
* Author: Open Studio
* Author URI: https://www.openstudio.fr/
**/

function thelia_get_product($atts) {

	$api_url = 'http://classic-ride/admin/module/productapi/search?q=';

	$product_ref = $atts['ref'];

	// HACK TO GET ADMIN ACCESS WITH THE COOKIE
	$opts = [
		"http" => [
			"method" => "GET",
			"header" => "Accept-language: en\r\n" .
				"Cookie: PHPSESSID=eeu80i8j37iu0uv7bvevpsl5j4\r\n"
		]
	];
	// ----------------------------------------

	$context = stream_context_create($opts);

	$json = file_get_contents($api_url . $product_ref, false, $context);

	$product = json_decode($json)->product;

	$html = '<h2>' . $product->title . '</h2>';
	$html .= '<p>'. $product->description .'</p>';
	foreach($product->declinaisons as $decli){
		$html .= '<div style="display:flex">';
		$html .= '<p>'. $decli->title .' - '. $decli->attribute .' : </p>';
		$html .= '<p>'. number_format($decli->price, 2, ".", " ") .'€</p>';
		$html .= '</div>';
	}

	 
    return $html;
}

add_shortcode('thelia-product', 'thelia_get_product');