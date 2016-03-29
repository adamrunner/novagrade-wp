<?php /*
 * Plugin Name: StoreKit - WooCommerce for Layers
 * Version: 1.8
 * Plugin URI: http://www.oboxthemes.com
 * Description: Turbo charge WooCommerce with StoreKit and Layers. StoreKit combines awesome WooCommerce enhancements you and your users will love.
 * Author: Obox
 * Author URI: http://www.oboxthemes.com/
 * Requires at least: 4.0
 * Tested up to: 4.1
 * Layers Plugin: True
 * Layers Required Version: 1.1.0
 *
 * Text Domain: layers-storekit
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Obox
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( defined( 'SCRIPT_DEBUG' ) && TRUE == SCRIPT_DEBUG ) {
	define( 'LAYERS_STOREKIT_VER', rand( 0 , 100 ) );
} else {
	define( 'LAYERS_STOREKIT_VER', '1.8' );
}

define( 'LAYERS_STOREKIT_SLUG' , 'layers-storekit' );
define( 'LAYERS_STOREKIT_REQUIRED_VERSION' , '1.2.13' );
define( 'LAYERS_STOREKIT_DIR' , trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'LAYERS_STOREKIT_URI' , trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'LAYERS_STOREKIT_FILE' , trailingslashit( __FILE__ ) );

// Load plugin class files
require_once( 'includes/class-woocommerce.php' );

if( !function_exists( 'layers_woocommerce_init' ) ) {
	// Instantiate Plugin
	function layers_woocommerce_init() {

		global $layers_woocommerce;

		$layers_woocommerce = Layers_WooCommerce::get_instance();

		// Localization
		load_plugin_textdomain( LAYERS_STOREKIT_SLUG, FALSE, dirname( plugin_basename( __FILE__ ) ) . "/lang/" );
	} // layers_woocommerce_init

	add_action( "plugins_loaded", "layers_woocommerce_init" );
}