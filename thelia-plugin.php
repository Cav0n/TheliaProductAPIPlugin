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
	$api_url = get_option("thelia_api_url");

	if(filter_var($api_url, FILTER_VALIDATE_URL)){
		$product_ref = $atts['ref'];
	
		// ****** TEST ********

		require('vendor/autoload.php');
		$client = new Thelia\Api\Client\Client("F696D28F1B0334423815575A4", "196897755CA0F58E918BC7C270B631CC5192F3374614BC7A", "http://classic-ride/module/productAPI/search/");
		list($status, $data) = $client->doGet($product_ref, 1);

		// ********************

		$product = apiCall($api_url, $product_ref);

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
	else {
		return "<p style='color:red'>L'URL DE L'API THELIA N'EST PAS VALIDE !</p>";
	}
	
}

add_shortcode('thelia-product', 'thelia_get_product');

function thelia_register_settings() {
	add_option( 'thelia_api_url', '');
	register_setting( 'thelia_options_group', 'thelia_api_url', 'myplugin_callback' );

	add_option( 'thelia_api_key', '');
	register_setting( 'thelia_options_group', 'thelia_api_key', 'myplugin_callback' );
}
add_action( 'admin_init', 'thelia_register_settings' );

function thelia_register_options_page() {
	add_options_page('Paramètres Thelia', 'Thelia Product API', 'manage_options', 'thelia', 'thelia_options_page');
  }
add_action('admin_menu', 'thelia_register_options_page');

function thelia_options_page()
{
	?>
	<div>
		<?php screen_icon(); ?>
		<h2>Thelia Product API</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'thelia_options_group' ); ?>
			<h3>URL de l'API</h3>
			<p>Normalement il s'agit de http://[URL DE VOTRE SITE]/admin/module/productapi/search?q=</p>
			<table class='form-table'>
			<tr valign="top">
				<th scope="row"><label for="thelia_api_url">URL</label></th>
				<td><input type="text" id="thelia_api_url" name="thelia_api_url" value="<?php echo get_option('thelia_api_url'); ?>" class="regular-text" /></td>
			</tr>
			</table>

			<h3>Clé de l'API</h3>
			<p>Pour trouver la clé de l'API, dans votre panneau d'administration Thelia : Configuration -> Paramètres Systèmes -> Configuration de L'API -> Clé d'API</p>
			<table class='form-table'>
			<tr valign="top">
				<th scope="row"><label for="thelia_api_key">Clé</label></th>
				<td><input type="text" id="thelia_api_key" name="thelia_api_key" value="<?php echo get_option('thelia_api_key'); ?>" class="regular-text" /></td>
			</tr>
			</table>
			<?php  submit_button(); ?>
		</form>
	</div>
	<?php
}

function apiCall($api_url, $ref)
{
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

	$json = file_get_contents($api_url . $ref, false, $context);

	$product = json_decode($json)->product;

	return $product;
}



