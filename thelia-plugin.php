<?php
/**
* Plugin Name: Thelia Product API
* Plugin URI: https://github.com/Cav0n/ProductAPI
* Description: Show products informations from your Thelia directly on your WordPress blog
* Version: 1.0.6
* Author: Open Studio
* Author URI: https://www.openstudio.fr/
**/

// SHORTCODE CALLBACK FUNCTION
function thelia_get_product($atts) 
{
	thelia_prefix_enqueue();

	$api_key = get_option("thelia_api_key"); // Get API key in plugin's options
	$api_url = get_option("thelia_api_url"); // Get API URL in plugin's options
	$api_lang = get_option("thelia_api_lang"); // Get API lang code
	$api_country_tax = get_option("thelia_country_tax"); // Get country alpha 3 iso code for taxes
	$api_css = get_option("thelia_api_css");
	$openInNewTab = 'target="_blank" rel="noopener noreferrer"';

	if (null === $api_lang) $api_lang = 'fr_FR'; // If no lang found in plugin's options, set it to french
	if (null === $api_country_tax) $api_country_tax = 'FRA';

	if(filter_var($api_url, FILTER_VALIDATE_URL)){ // Check if API URL is a real URL
		$product_refs = explode(';',$atts['ref']); // Get product reference from url attributes

		if (isset($atts['new-tab']) && 'false' === $atts['new-tab']) { // If 'new-tab' attribute is set in shortcode and false, links will open in current tab
			$openInNewTab = null;
		}

		$html = '<div class="productApiContainer row justify-content-center justify-content-md-start" style="max-width:100% !important;">';
		$html .= "<style type='text/css'>$api_css</style>";

		foreach($product_refs as $product_ref){
			// ****** API CLIENT ******
			$hash = sha1($product_ref . $api_lang . $api_country_tax . $api_key);

			$url = $api_url . '?ref=' . $product_ref . '&hash=' . $hash;
			if(null !== $api_lang) $url .= '&lang=' . $api_lang;
			if(null !== $api_country_tax) $url .= '&country=' . $api_country_tax;

			try{
				list($data, $httpcode) = thelia_api_client($url);
			} catch(\Exception $e){
				return "<p class='text-danger'>Aucun produit avec la référence : <b>$product_ref</b> !!</p>";
			}
			// *******************************

			// See the JSON result to understand the data below
			$product = $data['Product'];
			$productI18ns = $product['ProductI18ns'];
			$productSaleElements = $product['ProductSaleElements'];
			$firstPseKey = array_keys($productSaleElements)[0];

			$url = $product['URL'];
			$title = $productI18ns[$api_lang]['Title'];
			$description = $productI18ns[$api_lang]['Description'];

			$mainImage = null;
			foreach ($product['Images'] as $image) {
                if (null == $mainImage) {
			        $mainImage = $image;
			        continue;
                }

			    if ($image['position'] < $mainImage['position']) {
			        $mainImage = $image;
                }
            }

			$price = number_format($productSaleElements[$firstPseKey]['Prices']['price'], 2, '.', ' ');
			$isInPromo = $productSaleElements[$firstPseKey]['Prices']['promo'];

			if($isInPromo){
				$originalPrice = number_format($productSaleElements[$firstPseKey]['Prices']['original_price'], 2, '.', ' ');
				$discount = ceil( (($originalPrice - $price) / $originalPrice) * 100 );
			}

			// ****** HTML GENERATION ******

			$html .= '<article class="SingleProduct col-6 col-md-4 col-lg-3 p-0">';

			$html .= "<a class='SingleProduct__image' href='$url' $openInNewTab>";
				$html .= '<img src="'. $mainImage['image_url'] .'" style="width:100%;">';
			$html .= '</a>';

			$html .= '<div>';
				$html .= '<div class="SingleProduct__info">';
					$html .= "<h3 class='SingleProduct__title'><a href='$url' $openInNewTab><span>$title</span></a></h3>";
				$html .= '</div>';

				$html .= '<div class="SingleProduct__price">';

					if($isInPromo){
						$html .= "<span class='price'>$price €</span>";
						$html .= "<span class='old-price'>$originalPrice €</span>";
						$html .= "<span class='discount'> $discount %</span>";
					} else {
						$html .= "<span class='price'>$price €</span>";
					}

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

function thelia_api_client($url)
{
	$wp_request_headers = array(
		'Authorization' => 'Basic ' . base64_encode( 'admin:admin1234!!' )
	);
	
	$wp_request_url = $url;
	
	$wp_get_product_response = wp_remote_request(
		$wp_request_url,
		array(
			'method'    => 'GET',
			'headers'   => $wp_request_headers
		)
	);

	$json = wp_remote_retrieve_body($wp_get_product_response);
	$httpcode = wp_remote_retrieve_response_code($wp_get_product_response);

	if ($httpcode >= 400) {
		throw new \Exception("Impossible de trouver le produit.");
	}

	return array(json_decode($json, true), $httpcode);
}

add_shortcode('thelia-product', 'thelia_get_product');

// PLUGIN SETTINGS
function thelia_register_settings() 
{
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

			<h3>Clé de l'API</h3>
			<p>Pour trouver la clé de l'API, dans votre panneau d'administration Thelia : Modules -> ProductAPI -> Paramètres du modules (icone de clé à molette)</p>
			<p>Exemple de clé d'API : <strong>ExRtVQjUCCBApuN4s4fPEQ6i5yggYvm2</strong></p>
			<table class='form-table'>
				<tr valign="top">
					<th scope="row"><label for="thelia_api_key">Clé</label></th>
					<td><input type="text" id="thelia_api_key" name="thelia_api_key" value="<?php echo get_option('thelia_api_key'); ?>" class="regular-text" /></td>
				</tr>
			</table>
			
			<h3>URL de l'API</h3>
			<p>Vous pouvez trouver l'URL de l'API dans votre panneau d'administration Thelia : Modules -> ProductAPI -> Paramètres du module (icone de clé à molette)</p>
			<p>Normalement il s'agit de <strong>http://[URL DE VOTRE SITE]/api/product</strong>.</p>
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
					<td><textarea type="text" id="thelia_api_css" name="thelia_api_css" class="regular-text" rows="20"><?php echo get_option('thelia_api_css'); ?></textarea></td>
				</tr>
			</table>

			<?php  submit_button(); ?>

		</form>
	</div>
	<?php
}

function thelia_prefix_enqueue() 
{
    // CSS
    wp_register_style('prefix_bootstrap', plugins_url('css/bootstrap.css',__FILE__ ));
    wp_enqueue_style('prefix_bootstrap');
}