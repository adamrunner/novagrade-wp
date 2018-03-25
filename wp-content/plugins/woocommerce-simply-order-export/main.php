<?php
/**
 * Plugin Name: WooCommerce Simply Order Export
 * Plugin URI: https://wordpress.org/plugins/woocommerce-simply-order-export/
 * Description: Downloads order details in csv format
 * Version: 2.1.9
 * Author: Ankit Gade
 * Author URI: http://sharethingz.com
 * License: GPL2
 */

if( !defined('ABSPATH') ){
	exit;
}

/**
 * To include plugins related functions.
 */
if( !function_exists( 'get_plugin_data' ) ){
	require_once ABSPATH. 'wp-admin/includes/plugin.php';
}

/**
 * Check if WooCommerce is already activated.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ( -1 !== version_compare( get_plugin_data( WP_PLUGIN_DIR.'/woocommerce/woocommerce.php' )['Version'], '3.0.0' ) ) ) {

	class WooCommerce_simply_order_export {

		/**
		 * @var string
		 */
		public $version = '2.1.9';
		
		/**
		 * Plugin Settings
		 */
		public $settings;

		/**
		 * Instance of the class.
		 */
		static $instance = null;

		/**
		 * Returns the current instance of the class
		 */
		static function instance() {

			if( is_null(self::$instance) ){
				self::$instance = new self();
			}

			return self::$instance;
		}
		
		/**
		 * Constructor
		 */
		function __construct() {

			register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );
			$this->define_constants();
			$this->includes();
			add_action( 'init', array($this, 'init') );
		}

		/**
		 * Fires at 'init' hook
		 */
		function init() {

			$this->load_plugin_textdomain();
			$this->set_variables();
			$this->instantiate();
			$this->settings = $this->wsoe_settings();

			/**
			 * Call notice function, only if this is the first time plugin has been installed.
			 */
			wsoe_call_notices_func();
		}

		/**
		 * Load locale
		 */
		function load_plugin_textdomain() {

			load_plugin_textdomain( 'woocommerce-simply-order-export', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
		}

		/**
		 * Sets the variables
		 */
		function __set( $name, $value ) {

			/**
			 * Check for valid names
			 */
			if( in_array( $name, array( 'wpg_order_export', 'wpg_order_columns' ) ) ){				
				$GLOBALS[$name] = $value;
			}
		}

		/**
		 * Define all constants
		 */
		function define_constants() {

			define('WSOE_BASENAME', plugin_basename(__FILE__));
			define('WSOE_BASE', plugin_dir_path(__FILE__));
			define( 'WSOE_URL', plugins_url('', __FILE__) ); /* plugin url */
			define( 'WSOE_CSS', WSOE_URL. "/assets/css/" ); /* Define all necessary variables first */
			define( 'WSOE_JS',  WSOE_URL. "/assets/js/" );
			define( 'WSOE_IMG',  WSOE_URL. "/assets/img/" );
		}

		/**
		 * Set necessary variables.
		 */
		function set_variables() {

			$this->wpg_order_columns = apply_filters( 'wpg_order_columns', array(
												'wc_settings_tab_order_id'=>__( 'Order ID', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_name'=>__( 'Customer Name', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_product_name'=>__( 'Product Name', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_product_quantity'=>__( 'Product Quantity', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_product_variation'=>__( 'Variation details', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_amount'=> __( 'Order Amount', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_email'=> __( 'Customer Email', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_phone'=>__( 'Phone Number', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_order_status'=>__( 'Order Status', 'woocommerce-simply-order-export' )
											)
										);
		}

		/**
		 * Include helper classes
		 */
		function includes() {
			// Includes PHP files located in 'lib' and 'classes' folder
			foreach( array_merge( glob ( dirname(__FILE__). "/lib/*.php" ), glob ( dirname(__FILE__). "/classes/*.php" ) ) as $lib_filename ) {
				require_once( $lib_filename );
			}
		}

		/**
		 * Runs when plugin is activated.
		 */
		static function install() {

			ob_start();

			global $wpg_order_columns;

			$wpg_order_columns = is_array($wpg_order_columns) ? $wpg_order_columns : array();

			foreach( $wpg_order_columns as $key=>$val ){

				$option = get_option( $key, null );
				if( empty( $option ) ) {
					update_option($key, 'yes');
				}
			}

			ob_end_clean();
		}

		/**
		 * Instantiate necessary classes.
		 */
		function instantiate() {

			$this->wpg_order_export = new wpg_order_export();
		}

		/**
		 * Settings for the plugin.
		 */
		public function wsoe_settings() {

			$default_settings = array( 'wsoe_export_filename'=>'', 'wsoe_order_statuses'=> array(), 'wsoe_delimiter'=>',', 'wsoe_fix_chars'=>0  );
			$extensions = array( 'add-on'=>false, 'scheduler'=>false );

			/**
			 * Fill up the settings.
			 */
			$plugin_settings = get_option( 'wsoe_advanced_settings_core', array() );
			$plugin_settings = wp_parse_args( $plugin_settings, $default_settings );

			/**
			 * Check if add-on plugin is installed
			 */
			if( class_exists('wsoe_addon') ){
				$extensions['add-on'] = true;
			}

			/**
			 * Check if scheduler plugin is installed
			 */
			if( class_exists('wsoe_schedular') ){
				$extensions['scheduler'] = true;
			}

			return apply_filters( 'wsoe_settings', array( 'plugin_settings'=> $plugin_settings, 'extensions'=>$extensions ) );
		}
		
		/**
		 * Flushes the setting and fills up with the new values.
		 */
		public function flush_settings() {
			$this->settings = $this->wsoe_settings();
		}

	}

	/**
	 * Instantiate the class
	 * @return Object
	 */
	function WSOE() {
		return WooCommerce_simply_order_export::instance();
	}

	WSOE();

}else{
	
	/**
	 * Display notice to install base plugin in order to make this add-on plugin functional.
	 * @since 2.9
	 */
	function wsoe_install_older_version() {?>

		<div class="notice notice-warning">
			<p><?php
				_e( sprintf( '<strong>WooCommerce Simply Order Export:</strong> This plugin is compatible with WooCommerce 3.0.0 and higher, you are using '
						. 'WooCommerce %s, please update WooCommerce to latest version (recommended) OR '
						. 'downgrade this plugin to version 2.1.6 (not recommended)', WC()->version), 
							'woocommerce-simply-order-export-add-on' ); ?>
			</p>
		</div><?php

	}
	add_action( 'admin_notices', 'wsoe_install_older_version' );
}
