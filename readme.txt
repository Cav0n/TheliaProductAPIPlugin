=== Thelia Plugin ===
Contributors: fbernard
Tags: thelia, ecommerce
Requires at least: 5.1
Tested up to: 5.4
Requires PHP: 7.0
Stable tag: 1.0.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Add a link between your Thelia shop and your Wordpress blog
 
== Description ==
 
This plugin is designed to add products from your Thelia shop on blog posts in you WordPress blog.

Add your products on your WordPress blog to add more visibility to your shop !
 
== Installation ==

1. Upload `thelia-plugin.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your API key, URL, and CSS in Settings > Thelia Product API
4. Add products in any of your posts with the shortcode "[thelia-product ref=###]" where "###" is the reference of the
product you want to add
 
== Frequently Asked Questions ==
 
= Where do I find my API key / URL ? =
 
In your Thelia dashboard > Modules > Modules classic > Product API > Configuration

= Hum... products doesn't look like products on my Thelia shop... =

This means that you have a CSS issue in the plugin. Be sure to copy and paste the CSS style of 'SingleProduct' from 
your Thelia shop to the Thelia WordPress plugin settings (Settings > Thelia Product API).

== Screenshots ==
 
1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot
 
== Changelog ==
= 1.0.7 =
* Getting original image url from api (for best image quality)

= 1.0.6 =
* Products link open in new tab by default.

= 1.0.5 =
* Added 'new-tab' attribute to open product page in new tab.

= 1.0.4 =
* Fixed currency of default price not showing

= 1.0.3 =
* Fixed main image to show REAL main image

= 1.0.2 =
* Updated API Key setting hint

= 1.0.1 =
* Updated plugin documentation (readme.txt)

= 1.0.0 =
* First Version of the plugins