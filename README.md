# Thelia Product API plugin

A WordPress plugin to add a shortcode that permit to get product from thelia based website with only the product's reference. 

## Installation

* Copy the plugin into ```<your_wordpress_root>/wp_content/plugins/``` directory.
* Activate it in your WordPress administration panel

## Usage

Write the shortcode [thelia-product ref={THE_PRODUCT_REFERENCE}], it will show automatically the product in your wordpress blog post.
You can show multiple products by writing mutliple ref separate by a ";".
Exemple : [thelia-product ref=150054CN;238003;156066C]

### Input arguments

|Argument |Description |
|---      |--- |
|**ref** | The reference of the product |
