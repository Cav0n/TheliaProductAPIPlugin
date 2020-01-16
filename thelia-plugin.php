<?php
/**
* Plugin Name: Thelia Product API
* Plugin URI: https://github.com/Cav0n/ProductAPI
* Description: Show products informations from your Thelia directly on your WordPress blog
* Version: 0.2.0
* Author: Open Studio
* Author URI: https://www.openstudio.fr/
**/

// SHORTCODE CALLBACK FUNCTION
function thelia_get_product($atts) 
{
	$api_token = get_option("thelia_api_token"); // Get API token in plugin's options
	$api_key = get_option("thelia_api_key"); // Get API key in plugin's options
	$api_url = get_option("thelia_api_url"); // Get API URL in plugin's options
	$api_lang = get_option("thelia_api_lang"); // Get API lang code
	$api_country_tax = get_option("thelia_country_tax"); // Get country alpha 3 iso code for taxes
	$api_css = get_option("thelia_api_css");

	if (null === $api_lang) $api_lang = 'fr_FR'; // If no lang found in plugin's options, set it to french
	if (null === $api_country_tax) $api_country_tax = 'FRA';

	if(filter_var($api_url, FILTER_VALIDATE_URL)){ // Check if API URL is a real URL
		$product_refs = explode(';',$atts['ref']); // Get product reference from url attributes

		$html = '<div style="display:flex">';

		foreach($product_refs as $product_ref){
			// ****** THELIA API CLIENT ******

			require('vendor/autoload.php'); // Necessary to load Thelia API Client
			$client = new Thelia\Api\Client\Client($api_token, $api_key, $api_url, null, null); // Calling API with api_token, api_key and api_url
			list($status, $data) = $client->doGet(null, $product_ref . '/' . $api_country_tax); // Do GET method on API with the product ref

			// *******************************


			// See the JSON result to understand the data below
			$product = $data['Product'];
			$productI18ns = $product['ProductI18ns'];
			$productSaleElements = $product['ProductSaleElements'];

			$title = $productI18ns[$api_lang]['Title'];
			$description = $productI18ns[$api_lang]['Description'];
			$mainImage = $product['Images'][0];
			$price = number_format($productSaleElements[0]['Prices']['price'], 2, '.', ' ');
			$isInPromo = $productSaleElements[0]['Prices']['promo'];

			if($isInPromo){
				$originalPrice = number_format($productSaleElements[0]['Prices']['original_price'], 2, '.', ' ');
				$discount = ceil( (($originalPrice - $price) / $originalPrice) * 100 );
			}

			// ****** HTML GENERATION ******

			$html .= "<style type='text/css'>$api_css</style>";

			$html .= '<article class="SingleProduct">';

			$html .= '<a class="SingleProduct__image" href="#">';
				$html .= '<img src="'. $mainImage['image_url'] .'" style="width:100%;">';
			$html .= '</a>';

			$html .= '<div>';
				$html .= '<div class="SingleProduct__info">';
					$html .= "<h3 class='SingleProduct__title'><a href='#'><span>$title</span></a></h3>";
				$html .= '</div>';

				$html .= '<div class="SingleProduct__price">';

					if($isInPromo){
						$html .= "<span class='price'>$price €</span>";
						$html .= "<span class='old-price'>$originalPrice €</span>";
						$html .= "<span class='discount'>". $discount ." %</span>";
					} else {
						$html .= "<span class='price'>$price</span>";
					}

				$html .= '</div>';
				
				$html .= '<div class="SingleProduct__rating">';
					$html .= '<div class="Rating d-flex">';
						$html .= '<span class="Rating-text">4/5</span>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';

			$html .= '</article>';

			// *****************************
		
			
		}
		$html .= '</div>';
		return $html;
	}
	else {
		return "<p style='color:red'>L'URL DE L'API THELIA N'EST PAS VALIDE !</p>"; // Showing error message instead of product
	}
	
}

add_shortcode('thelia-product', 'thelia_get_product');

// PLUGIN SETTINGS
function thelia_register_settings() 
{
	add_option( 'thelia_api_token', '');
	register_setting( 'thelia_options_group', 'thelia_api_token', 'myplugin_callback' );

	add_option( 'thelia_api_key', '');
	register_setting( 'thelia_options_group', 'thelia_api_key', 'myplugin_callback' );

	add_option( 'thelia_api_url', '');
	register_setting( 'thelia_options_group', 'thelia_api_url', 'myplugin_callback' );

	add_option( 'thelia_api_lang', 'fr_FR');
	register_setting( 'thelia_options_group', 'thelia_api_lang', 'myplugin_callback' );

	add_option( 'thelia_country_tax', 'FRA');
	register_setting( 'thelia_options_group', 'thelia_country_tax', 'myplugin_callback' );

	add_option( 'thelia_api_css', '');
	register_setting( 'thelia_options_group', 'thelia_api_css', 'myplugin_callback' );

}

add_action( 'admin_init', 'thelia_register_settings' );

// PLUGIN SETTINGS LINK
function thelia_register_options_page()
{
	add_options_page('Paramètres Thelia', 'Thelia Product API', 'manage_options', 'thelia', 'thelia_options_page');
}

add_action('admin_menu', 'thelia_register_options_page');

// PLUGIN SETTINGS PAGE
function thelia_options_page()
{
	?>
	<div>
		<?php screen_icon(); ?>
		<h2>Thelia Product API</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'thelia_options_group' ); ?>

			<h3>Token de l'API</h3>
			<p>Pour trouver le token de l'API, dans votre panneau d'administration Thelia : Configuration -> Paramètres Systèmes -> Configuration de L'API -> Clé d'API</p>
			<p>Exemple de token d'API : <strong>F696D28F1B0334423815575A4</strong></p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_token">Token</label></th>
					<td><input type="text" id="thelia_api_token" name="thelia_api_token" value="<?php echo get_option('thelia_api_token'); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<h3>Clé de l'API</h3>
			<p>Pour trouver la clé de l'API, dans votre panneau d'administration Thelia : Configuration -> Paramètres Systèmes -> Configuration de L'API -> télécharger</p>
			<p>Ouvrez le fichier téléchargé avec un éditeur de texte. La chaine de caractère est votre clé d'API.</p>
			<p>Exemple de clé d'API : <strong>196897755CA0F58E918BC7C270B631CC5192F3374614BC7A</strong></p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_key">Clé</label></th>
					<td><input type="text" id="thelia_api_key" name="thelia_api_key" value="<?php echo get_option('thelia_api_key'); ?>" class="regular-text" /></td>
				</tr>
			</table>
			
			<h3>URL de l'API</h3>
			<p>Normalement il s'agit de <strong>http://[URL DE VOTRE SITE]/api/product/ref</strong>.</p>
			<p>[URL DE VOTRE SITE] est l'URL de votre Thelia.</p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_url">URL</label></th>
					<td><input type="text" id="thelia_api_url" name="thelia_api_url" value="<?php echo get_option('thelia_api_url'); ?>" class="regular-text" /></td>
				</tr>
			</table>			

			<h3>Langue de l'API</h3>
			<p>Indiquez la langue de l'API (cela modifiera la langue du titre des produits par exemple).</p>
			<p>Si rien n'est indiqué la langue par défaut sera le français (fr_FR).</p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_lang">Code de la langue</label></th>
					<td><input type="text" id="thelia_api_lang" name="thelia_api_lang" value="<?php echo get_option('thelia_api_lang'); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<h3>Pays des taxes</h3>
			<p>Indiquez le pays pour les produits (pour calculer automatiquement les taxes, par exemple la TVA).</p>
			<p>Le code du pays doit être sous forme ISO alpha-3. Pour une liste des codes par pays <a href='https://www.iban.com/country-codes' target="_blank">cliquez ici.</a></p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_country_tax">Code de la langue</label></th>
					<td><input type="text" id="thelia_country_tax" name="thelia_country_tax" value="<?php echo get_option('thelia_country_tax'); ?>" class="regular-text" /></td>
				</tr>
			</table>

			<h3>CSS du plugin</h3>
			<p>Collez ici le CSS que vous souhaitez appliquer aux produits.</p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_css">CSS</label></th>
					<td><textarea type="text" id="thelia_api_css" name="thelia_api_css" class="regular-text" ><?php echo get_option('thelia_api_css'); ?></textarea></td>
				</tr>
			</table>

			<?php  submit_button(); ?>

		</form>
	</div>
	<?php
}


