<?php  /**
 * Layers WooCommerce Class
 *
 * This file is used to modify any WooCommerce related filtes, hooks & modifiers
 *
 * @package Layers
 * @since Layers 1.0
 */

class Layers_WooCommerce {

	private static $instance;

	/**
	*  Get Instance creates a singleton class that's cached to stop duplicate instances
	*/
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	*  Construct empty on purpose
	*/

	private function __construct() {}

	/**
	*  Init behaves like, and replaces, construct
	*/

	public function init(){

		// Run the version checker
		$this->layers_version_check();

		// Check for Layers as well as WooCommerce
		add_action( 'admin_notices', array( $this, 'activate_woo_commerce_admin_notice' ) );

		if( FALSE !== $this->update_required || 'layerswp' != get_template() ) return;

		// Send the user to onboarding upon activation
		register_activation_hook( LAYERS_STOREKIT_FILE , array( $this, 'activate' ) );

		// Include helper functions
		require_once( LAYERS_STOREKIT_DIR . 'includes/functions.php' );

		// Include HTML / CSS filters for Layers
		require_once( LAYERS_STOREKIT_DIR . 'includes/layers-filters.php' );

		// Include Global Customizer Controls
		require_once( LAYERS_STOREKIT_DIR . 'controls/controls.php' );

		// Add Template locations
		add_filter( 'layers_template_locations' , array( $this, 'add_template_locations' ) );

		// Add WooCommerce Specific Sidebars & Widgets
		add_action( 'widgets_init' , array( $this, 'register_widgets' ), 100 );

		// Add Styles & Scripts
		add_filter( 'wp_enqueue_scripts' , array( $this , 'enqueue_scripts' ) );
		add_filter( 'customize_controls_enqueue_scripts' , array( $this , 'admin_enqueue_scripts' ) );
	}

	/**
	* Activate WooCommerce admin notice
	*/
	public function activate_woo_commerce_admin_notice(){
		global $blog_id;
		$themes = wp_get_themes( $blog_id );
		if( 'layerswp' !== get_template() ){ ?>
			<div class="updated is-dismissible notice">
				<p><?php _e( sprintf( "Layers is required to use StoreKit. <a href=\"%s\" target=\"_blank\">Click here</a> to get it.", ( isset( $themes['layerswp'] ) ? admin_url( 'themes.php?s=layerswp' ) : "http://www.layerswp.com" ) ), 'layers-storekit' ); ?></p>
			</div>
		<?php } else if( FALSE !== $this->update_required ) { ?>
			<div class="updated is-dismissible notice">
				<p><?php _e( sprintf( "StoreKit requires Layers Version ". $this->update_required .". <a href=\"%s\" target=\"_blank\">Click here</a> to get the Layers Updater.", "http://www.layerswp.com/download/layers-updater" ), 'layers-storekit' ); ?></p>
			</div>
		<?php } else if( !class_exists( 'WooCommerce' ) ) { ?>
			<div class="updated is-dismissible notice">
				<p><?php _e( sprintf( "StoreKit requires the WooCommerce plugin. <a href=\"%s\" target=\"_blank\">Click here</a> to get WooCommerce.", admin_url( 'plugins.php?s=woocommerce' ) ), 'layers-storekit' ); ?></p>
			</div>
	<?php }
	}

	/**
	* Layers Min Version Checker
	*/
	public function layers_version_check(){

		$layers_meta = wp_get_theme( 'layerswp' );

		if( version_compare( $layers_meta->get( 'Version' ), LAYERS_STOREKIT_REQUIRED_VERSION, '<' ) ){
			$this->update_required = LAYERS_STOREKIT_REQUIRED_VERSION;
		} else {
			$this->update_required = FALSE;
		}
	}


	/**
	* Set Activation Transient
	*/
	public function active(){
		set_transient( 'layers_woocommerce_activated', 1, 30 );
	}

	/**
	*  Enqueue WooCommerce Scripts & Styles
	*/

	public function add_template_locations( $template_locations ){

		$template_locations[] = LAYERS_STOREKIT_DIR . 'templates';

		return $template_locations;
	}

	/**
	*  Enqueue Admin Scripts & Styles
	*/

	public function admin_enqueue_scripts(){

		wp_enqueue_script(
			LAYERS_STOREKIT_SLUG . '-admin',
			LAYERS_STOREKIT_URI . 'assets/js/admin.js',
			array( 'jquery' ),
			LAYERS_STOREKIT_VER,
			true
		); // WooCommerce CSS

		// Localize Scripts
		wp_localize_script(
			LAYERS_STOREKIT_SLUG . '-admin' ,
			"layers_woocommerce_params",
			array(
				'product_widget_nonce' => wp_create_nonce( 'layers-woocommerce-product-widget' )
			)
		);

		wp_enqueue_script(
			LAYERS_STOREKIT_SLUG . '-product-slider',
			LAYERS_STOREKIT_URI . 'assets/js/product-slider.js',
			array( 'jquery' ),
			LAYERS_STOREKIT_VER
		); // WooCommerce CSS

		wp_enqueue_script(
			LAYERS_STOREKIT_SLUG . '-product-category',
			LAYERS_STOREKIT_URI . 'assets/js/product-category.js',
			array( 'jquery' ),
			LAYERS_STOREKIT_VER
		); // WooCommerce CSS

		wp_enqueue_script(
			LAYERS_STOREKIT_SLUG . '-select2',
			LAYERS_STOREKIT_URI . 'assets/js/select2.js',
			array( 'jquery' ),
			LAYERS_STOREKIT_VER
		); // WooCommerce CSS

		wp_enqueue_style(
			LAYERS_STOREKIT_SLUG . '-select2',
			LAYERS_STOREKIT_URI . 'assets/css/select2.css',
			array(),
			LAYERS_STOREKIT_VER
		); // Admin CSS

		wp_enqueue_style(
			LAYERS_STOREKIT_SLUG . '-admin',
			LAYERS_STOREKIT_URI . 'assets/css/admin.css',
			array(),
			LAYERS_STOREKIT_VER
		); // Admin CSS

	}

	/**
	*  Enqueue Front End Scripts & Styles
	*/

	public function enqueue_scripts(){

		wp_enqueue_style(
			LAYERS_STOREKIT_SLUG . '-woocommerce',
			LAYERS_STOREKIT_URI . 'assets/css/woocommerce.css',
			array( 'layers-woocommerce' ),
			LAYERS_STOREKIT_VER
		); // WooCommerce CSS

		wp_enqueue_script(
			LAYERS_STOREKIT_SLUG . '-frontend',
			LAYERS_STOREKIT_URI . 'assets/js/woocommerce.js',
			array( 'jquery' ),
			LAYERS_STOREKIT_VER,
			TRUE
		); // WooCommerce JS

		wp_localize_script(
			LAYERS_STOREKIT_SLUG . '-woocommerce',
			'layers_woocommerce',
			array(
				'ajaxurl' => admin_url( "admin-ajax.php" ),
				'nonce' => wp_create_nonce( 'layers-woocommerce' ),
			)
		);
	}

	/**
	* Register WooCommerce Widgets
	*/
	function register_widgets(){
		require_once LAYERS_STOREKIT_DIR . 'widgets/ajax.php';
		require_once LAYERS_STOREKIT_DIR . 'widgets/modules/product.php';
		require_once LAYERS_STOREKIT_DIR . 'widgets/modules/slider.php';
		require_once LAYERS_STOREKIT_DIR . 'widgets/modules/category.php';
	}

}
