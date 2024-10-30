<?php
/*
Plugin Name: C4D WooCommerce Product Bundles
Plugin URI: http://coffee4dev.com/woocommerce-product-bundle/
Description: C4D Woocommerce Product Bundle enables the efficient creation of a variety of product promotion bundles, powerful and ease-of-use to increase conversion rates.
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-woo-bundle
Domain Path: /languages/
Version: 1.1.2
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !in_array( 
  'woocommerce/woocommerce.php', 
   get_option( 'active_plugins' ) 
)  ) return;

define('C4DWOOBUNDLE_PLUGIN_URI', plugins_url('', __FILE__));
register_activation_hook( __FILE__, 'c4d_woo_bundle_plugin_activation' );

include_once (dirname(__FILE__). '/includes/default.php');

include_once (dirname(__FILE__). '/includes/product-type.php');

include_once (dirname(__FILE__). '/includes/tab.php');

include_once (dirname(__FILE__). '/includes/products.php');

include_once (dirname(__FILE__). '/includes/frontend.php');

include_once (dirname(__FILE__). '/includes/datas.php');