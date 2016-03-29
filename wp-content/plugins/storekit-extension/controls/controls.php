<?php /**
 * Layers WooCommerce Controls
 *
 * This file is used to configure the Controls in the Customizer for Layers WooCommerce
 *
 * @package Layers
 * @since Layers 1.0
 */

class Layers_Controls_WooCommerce {

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

		// Modify Customizer Panels
		add_filter( 'layers_customizer_panels', array( $this, 'modify_customizer_panels' ) );

		// Modify Customizer Sections
		add_filter( 'layers_customizer_sections', array( $this, 'modify_customizer_sections' ) );

		// Modify Customizer Control
		add_filter( 'layers_customizer_controls', array( $this, 'modify_customizer_controls' ) );

		// Remove unused sections
		add_action( 'customize_register', array( $this, 'remove_sections' ) );

		// Apply Customizations
		add_action( 'wp', array( $this, 'apply_customizer_customizations' ), 90 );
		add_filter( 'loop_shop_per_page', array( $this, 'apply_products_per_page_customization' ), 100 );

	}

	/**
	* Modify Customizer Panels
	*/
	public function remove_sections(){
		global $wp_customize;

		$wp_customize->remove_section( 'layers-woocommerce-sidebars' );
	}

	/**
	* Modify Customizer Panels
	*/

	public function modify_customizer_panels( $panels ) {

		// Change title from WooCommerce to StoreKit
		$panels['woocommerce'] = array_merge( $panels['woocommerce'], array( 'title' => __( 'StoreKit' , 'layers-storekit' ) ) );

		return $panels;
	}

	/**
	* Modify Customizer Sections
	*/

	public function modify_customizer_sections( $sections ) {

		$sections['woocommerce-site'] = array(
			'title'    =>__( 'General', 'layers-storekit' ),
			'panel'    => 'woocommerce',
		);

		$sections['woocommerce-product-list'] = array(
			'title'    =>__( 'Product List', 'layers-storekit' ),
			'panel'    => 'woocommerce',
		);

		$sections['woocommerce-product-page'] = array(
			'title'    =>__( 'Product Page', 'layers-storekit' ),
			'panel'    => 'woocommerce',
		);



		return $sections;
	}

	/**
	* Modify Customizer Controls
	*/

	public function modify_customizer_controls( $controls ){

		// Site General - Controls
		$controls['woocommerce-site'] = array(

			'woocommerce-site-cart-heading' => array(
				'type'  => 'layers-heading',
				'label'    => __( 'Menu Cart' , 'layers-storekit' ),
				'description'    => __( 'Choose what display in your menu cart' , 'layers-storekit' ),
			),
			'woocommerce-show-site-cart-heading' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Menu Cart' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-show-menu-cart-icon' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Cart Icon' , 'layers-storekit' ),
				'default'	=> TRUE,
				'linked' => array(
						'show-if-selector' => '#layers-woocommerce-show-site-cart-heading',
						'show-if-value' => 'true'
					)
			),
			'woocommerce-show-menu-cart-amount' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Amount' , 'layers-storekit' ),
				'default'	=> TRUE,
				'linked' => array(
						'show-if-selector' => '#layers-woocommerce-show-site-cart-heading',
						'show-if-value' => 'true'
					)
			),
			'woocommerce-show-menu-cart-products' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Count' , 'layers-storekit' ),
				'default'	=> FALSE,
				'linked' => array(
						'show-if-selector' => '#layers-woocommerce-show-site-cart-heading',
						'show-if-value' => 'true'
					)
			),
			'woocommerce-show-menu-cart-mini-cart' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Mini Cart Dropdown' , 'layers-storekit' ),
				'default'	=> FALSE,
				'linked' => array(
						'show-if-selector' => '#layers-woocommerce-show-site-cart-heading',
						'show-if-value' => 'true'
					)
			),

			'woocommerce-seperator-1' => array(
				'type'        => 'layers-seperator',
			),
			'woocommerce-header-heading' => array(
				'type'        => 'layers-heading',
				'label'       => __( 'Header' , 'layers-storekit' ),
			),
			'woocommerce-header-category-image' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Category Image in Header' , 'layers-storekit' ),
				'description' => sprintf(
					__( 'Show associated category image in the header. %s' , 'layers-storekit' ),
					'<a href="' . admin_url( 'edit-tags.php?taxonomy=product_cat&post_type=product' ) . '">' . __( 'Customize images here.' , 'layers-storekit' ) . '</a>'
				),
				'default'	=> FALSE,
				//'active_callback' => array( $this, 'check_category_images' ),
			),
			'woocommerce-header-category-size' => array(
				'type'     => 'layers-select',
				'label'    => __( 'Header Size' , 'layers-storekit' ),
				'default' => 'small',
				'choices' => array(
					'small' => __( 'Small' , 'layers-storekit' ),
					'medium' => __( 'Medium' , 'layers-storekit' ),
					'large' => __( 'Large' , 'layers-storekit' ),
				),
				'linked'    => array(
						'show-if-selector' => "#layers-woocommerce-header-category-image",
						'show-if-value' => "true",
				)
			),
			'woocommerce-header-category-excerpt' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Category Descriptions' , 'layers-storekit' ),
				'description' => __( 'Show associated category descriptions in the page title.' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
		);

		// Product List - Controls
		$controls['woocommerce-product-list'] = array(

			'woocommerce-product-list-sidebar-heading' => array(
				'type'  => 'layers-heading',
				'label'    => __( 'Sidebar(s)' , 'layers-storekit' ),
			),
			'archive-left-woocommerce-sidebar' => array_merge(
				$controls['woocommerce-sidebars']['archive-left-woocommerce-sidebar'], // Use existing Controls
				array()
			),
			'archive-right-woocommerce-sidebar' => array_merge(
				$controls['woocommerce-sidebars']['archive-right-woocommerce-sidebar'], // Use existing Controls
				array()
			),

			'woocommerce-product-list-seperator-1' => array(
				'type'        => 'layers-seperator',
			),

			'woocommerce-product-list-display-heading' => array(
				'type'        => 'layers-heading',
				'label'       => __( 'List' , 'layers-storekit' ),
				'description' => __( 'These settings allow you to choose what to display on your product list page.' , 'layers-storekit' ),
			),
			'woocommerce-product-list-products-per-row' => array(
				'type'     => 'layers-select',
				'label'    => __( 'Products Per Row' , 'layers-storekit' ),
				'default' => 3,
				'sanitize_callback' => 'layers_sanitize_number',
				'choices' => array(
					'1' => __( '1' , 'layers-storekit' ),
					'2' => __( '2' , 'layers-storekit' ),
					'3' => __( '3' , 'layers-storekit' ),
					'4' => __( '4' , 'layers-storekit' ),
				)
			),
			'woocommerce-product-list-products-per-page' => array(
				'type'     => 'layers-number',
				'label'    => __( 'Products Per Page' , 'layers-storekit' ),
				'default'  => 12,
				'sanitize_callback' => 'layers_sanitize_number',
			),
			'woocommerce-product-list-products-count' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Count' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-products-per-page-dropdown' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( '"Products Per Page" Dropdown' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-list-products-sorting' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Sorting' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-sale-flash' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Sale Badges' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-add-to-cart-button' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Add To Cart Buttons' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-prices' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Prices' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-ratings' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Ratings' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-meta' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Meta' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-list-stock' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Stock Count' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-list-new-badges' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( '"New" Badges' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-list-new-badges-time' => array(
				'type'		  => 'layers-number',
				'label'		  => __( '"New" Badge Period' , 'layers-storekit' ),
				'description' => __( 'Number of days to show the "New" badge' , 'layers-storekit' ),
				'default'	  => 30,
				'linked'      => array(
						'show-if-selector' => "#layers-woocommerce-product-list-new-badges",
						'show-if-value' => "true",
				)
			),

			'woocommerce-product-list-seperator-2' => array(
				'type'        => 'layers-seperator',
			),

			'woocommerce-product-list-image-heading' => array(
				'type'        => 'layers-heading',
				'label'       => __( 'Display Product Images', 'layers-storekit' ),
				//'description' => __( 'These settings are related to the product image.' , 'layers-storekit' ),
			),
			'woocommerce-product-list-thumbnails' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Image Flip' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-list-thumbnails-flip' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Thumbnail Flip' , 'layers-storekit' ),
				'description' => __( 'Choose this option to flip between the products featured image and the first image in the products gallery.' , 'layers-storekit' ),
				'default'	=> FALSE,
				'linked'    => array(
						'show-if-selector' => "#layers-woocommerce-product-list-thumbnails",
						'show-if-value' => "true",
				)
			),
		);

		// Product Page - Controls
		$controls['woocommerce-product-page'] = array(

			'woocommerce-product-page-sidebar-heading' => array(
				'type'  => 'layers-heading',
				'label'    => __( 'Sidebar(s)' , 'layers-storekit' ),
			),
			'single-left-woocommerce-sidebar' => array_merge(
				$controls['woocommerce-sidebars']['single-left-woocommerce-sidebar'], // Use existing Controls
				array()
			),
			'single-right-woocommerce-sidebar' => array_merge(
				$controls['woocommerce-sidebars']['single-right-woocommerce-sidebar'], // Use existing Controls
				array()
			),
			'woocommerce-product-page-seperator-1' => array(
				'type'        => 'layers-seperator',
			),
			'woocommerce-product-page-layout' => array(
				'type'    => 'layers-select-icons',
				'label'   => __( 'Layout' , 'layers-storekit' ),
				'default' => 'advanced-layout-left',
				'choices' => array(
					'advanced-layout-left'  => __( 'Content Right', 'layers-storekit' ),
					'advanced-layout-right' => __( 'Content Left', 'layers-storekit' ),
				),
				'class' => 'heading-label',
			),
			'woocommerce-product-page-seperator-2' => array(
				'type'        => 'layers-seperator',
			),
			'woocommerce-product-page-display-heading' => array(
				'type'  => 'layers-heading',
				'label' => __( 'Display' , 'layers-storekit' ),
				'description' => __( 'These settings allow you to choose what to display on your single product page.' , 'layers-storekit' ),
			),
			'woocommerce-product-page-title' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Title' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-excerpt' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Excerpt' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-sale-flash' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Sale Badges' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-add-to-cart-button' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Add To Cart Buttons' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-prices' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Prices' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-ratings' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Ratings' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-meta' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Product Meta' , 'layers-storekit' ),
				'default'	=> TRUE,
			),
			'woocommerce-product-page-stock' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Stock Count' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-page-new-badges' => array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( '"New" Badge' , 'layers-storekit' ),
				'default'	=> FALSE,
			),
			'woocommerce-product-page-new-badges-time' => array(
				'type'		  => 'layers-number',
				'label'		  => __( '"New" Badge Period' , 'layers-storekit' ),
				'description' => __( 'Number of days to show the "New" badge' , 'layers-storekit' ),
				'default'	  => 30,
				'linked'    => array(
						'show-if-selector' => "#layers-woocommerce-product-page-new-badges",
						'show-if-value' => "true",
				)
			),

		);

		/*
		// Product Category Images - Disabled.
		$product_categories = get_terms( 'product_cat', array( 'hide_empty' => FALSE, ));

		foreach ( $product_categories as $product_category ) {
			$new_controls[ 'woocommerce-header-category-image-' . $product_category->term_id ] = array(
				'type'		=> 'layers-checkbox',
				'label'		=> __( 'Choose the image for: ', 'layers-storekit' ) . $product_category->name,
				'default'	=> FALSE,
			);
		}

		$controls['woocommerce-product-list'] = array_merge( $new_controls, $controls['woocommerce-product-list'] );
		*/

		// Deregsiter the existing Sidebar Controls now that they are added to the new Storekit Controls
		$controls[ 'woocommerce-sidebars' ] = array();

		return $controls;
	}

	// Apply Customizations
	function apply_customizer_customizations() {

		if( !function_exists( 'layers_get_theme_mod' ) ) return;

		/*
		 * WooCommerce Product Archive
		 */

		if ( FALSE != layers_get_theme_mod( 'woocommerce-header-category-image' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'woocommerce_category_header_background_image' ) );
			add_filter( 'layers_title_container_class', array( $this, 'woocommerce_category_header_background_image_class' ) );
		}

		// Post List Columns
		if ( FALSE != layers_get_theme_mod( 'woocommerce-product-list-products-per-row' ) ) {
			add_filter( 'post_class' , array( $this, 'apply_post_list_column_selection' ), 60 );
			add_filter( 'product_cat_class' , array( $this, 'apply_post_list_column_selection' ), 60 );
		}

		// Result Count
		if ( FALSE === layers_get_theme_mod( 'woocommerce-product-list-products-count' ) ) {
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		}

		// Per Page Dropdown
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-list-products-per-page-dropdown' ) ) {
			add_action( 'woocommerce_before_shop_loop', array( $this, 'product_perpage_dropdown' ), 30 );
		}

		// Sale flash
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-sale-flash' ) ) {
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		}

		// Product Ordering
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-products-sorting' ) ) {
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		}

		// Add to cart button
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-add-to-cart-button' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		}

		// Price
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-prices' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		}

		// Rating
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-ratings' ) ) {
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		}

		// New Badge
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-list-new-badges' ) ) {
			add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'show_product_loop_new_badge' ), 11 );
		}

		// Stock
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-list-stock' ) ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'show_product_stock' ), 30 );
		}

		// Categories
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-list-meta' ) ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'apply_show_product_categories' ), 30 );
		}

		// Thumbnail
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-list-thumbnails' ) ) {
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		}

		// Thumbnail Flip
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-list-thumbnails-flip' ) ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woocommerce_template_loop_product_thumbnail_flip' ), 11 );
		}

		/*
		 * WooCommerce Product Single
		 */


		// Title
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-title' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// Excerpt
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-excerpt' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}

		// Sale flash
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-sale-flash' ) ) {
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		}

		// Add to cart button
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-add-to-cart-button' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
			remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
			remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
			remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
		}

		// Price
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-prices' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		// Rating
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-ratings' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		}

		// New Badge
		if ( TRUE == layers_get_theme_mod( 'woocommerce-product-page-new-badges' ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'show_product_loop_new_badge' ), 6 );
		}

		// Categories
		if ( FALSE == layers_get_theme_mod( 'woocommerce-product-page-meta' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		}

		// Stock
		if ( TRUE != layers_get_theme_mod( 'woocommerce-product-page-stock' ) ) {
			add_filter( 'woocommerce_stock_html', '__return_empty_string' );
		}

	}

	// Advanced Active Callback functionality - disabled
	function customize_active_controls( $arg1, $arg2 ) {

		if ( isset( $arg2->id ) && 0 === strpos( $arg2->id, 'layers-woocommerce-header-category-image-' ) ) {
			$cat_id = str_replace( 'layers-woocommerce-header-category-image-', '', $arg2->id );

			if ( is_product_category( $cat_id ) )
				return TRUE;
			else
				return FALSE;
		}

		return $arg1;
	}


	function apply_post_list_column_selection( $classes ) {

		if( !is_shop() && !is_product_tag() && !is_product_category() ) return $classes;

		if( layers_get_theme_mod( 'woocommerce-product-list-products-per-row' ) ){

			foreach( $classes as $key => $this_class ){
				if( FALSE !== strpos( $this_class, 'span-' ) ){
					unset( $classes[ $key ] );
				}
			}

			$span_class = round( 12 / (int) layers_get_theme_mod( 'woocommerce-product-list-products-per-row' ) );

			$classes[] = 'span-' . $span_class;
		}

		return $classes;
	}


	// Apply Per Page
	function apply_products_per_page_customization() {

		if( !function_exists( 'layers_get_theme_mod' ) ) return;

		$per_page = layers_get_theme_mod( 'woocommerce-product-list-products-per-page' );

		if ( isset( $_COOKIE['per_page'] ) ) {
			$per_page = $_COOKIE['per_page'];
		}
		if ( isset( $_POST['per_page'] ) ) {
			setcookie( 'per_page', $_POST['per_page'], time()+1209600, '/' );
			$per_page = $_POST['per_page'];
		}
		return $per_page;
	}

	// Per Page Dropdown
	function product_perpage_dropdown() {

		if( !function_exists( 'layers_get_theme_mod' ) ) return;

		$per_page = layers_get_theme_mod( 'woocommerce-product-list-products-per-page' );

		if ( isset( $_REQUEST['per_page'] ) ) {
			$woo_per_page = $_REQUEST['per_page'];
		} elseif ( ! isset( $_REQUEST['per_page'] ) && isset( $_COOKIE['per_page'] ) ) {
			$woo_per_page = $_COOKIE['per_page'];
		} else {
			$woo_per_page = $per_page;
		}

		// set action URL
		if ( is_shop() ) {
			$url = get_permalink( wc_get_page_id( 'shop' ) );
		} elseif ( is_product_category() ) {
			global $wp_query;
			$cat = $wp_query->get_queried_object();
			$url = get_term_link( $cat );
		} elseif ( is_product_tag() ) {
			global $wp_query;
			$tag = $wp_query->get_queried_object();
			$url = get_term_link( $tag );
		}

		// add querystring to URL if set
		if ( $_SERVER['QUERY_STRING'] != '' ) {
			$url .= '?' . $_SERVER['QUERY_STRING'];
		}

		?>
		<form class="woocommerce-ordering" method="post" action="<?php echo $url; ?>">
			<select name="per_page" class="per_page" onchange="this.form.submit()">
				<?php
					$x = 1;
					while ( $x <= 5 ) {
						$value 		= $per_page * $x;
						$selected 	= selected( $woo_per_page, $value, false );
						$label 		= __( "{$value} per page", 'woocommerce-product-archive-customiser' );
						echo "<option value='{$value}' {$selected}>{$label}</option>";
						$x++;
					}
				?>
			</select>
		</form>
		<?php
	}

	// Display the New Badge
	function show_product_loop_new_badge() {

		if( !function_exists( 'layers_get_theme_mod' ) ) return;

		$postdate 		= get_the_time( 'Y-m-d' ); // Post date
		$postdatestamp 	= strtotime( $postdate ); // Timestamped post date
		$newness 		= ( is_archive() ) ? layers_get_theme_mod( 'woocommerce-product-list-new-badges-time' ) : layers_get_theme_mod( 'woocommerce-product-page-new-badges-time' ) ; // Newness in days as defined by option

		if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) { // If the product was published within the newness time frame display the new badge
			?>
			<span class="new-badge"><?php _e( 'New', 'layers-storekit' ); ?></span>
			<?php
		}
	}

	// Display Meta
	function apply_show_product_categories() {
		global $post;

		$categories = '';
		$the_categories = get_the_terms( $post->ID , 'product_cat' );

		// If there are no categories, skip to the next case
		if( ! $the_categories ) return;

		foreach ( $the_categories as $category ){
			$categories[] = ' <a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s", LAYERS_THEME_SLUG ), $category->name ) ) . '">'.$category->name.'</a>';
		}
		$categories = implode( __( ', ' , 'layers-storekit' ), $categories );
		?>
		<footer class="meta-info">
			<p>
				<span class="meta-item meta-category"><i class="l-folder-open-o"></i>
					<?php echo $categories; ?>
				</span>
			</p>
		</footer>
		<?php
	}

	// Display Stock Count
	function show_product_stock() {
		global $product;

		$availability      = $product->get_availability();
		$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';

		echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
	}

	// Add product thumbnail flip
	function woocommerce_template_loop_product_thumbnail_flip() {

		global $product, $woocommerce, $post;

		$size = 'shop_catalog';

		$attachment_ids = array();

		if ( has_post_thumbnail() ) {
			$attachment_ids[] = get_post_thumbnail_id( $post->ID );
		}

		$gallery_ids = $product->get_gallery_attachment_ids();

		if( !empty( $gallery_ids ) ) {
			$attachment_ids = array_merge( $gallery_ids, $attachment_ids );
		}

		if ( 2 <= count( $attachment_ids ) ) {
			foreach ( $attachment_ids as $attachment_id ) {

				$img = wp_get_attachment_image_src( $attachment_id, $size );
				?>
				<div class="thumbnail-flip-slide-temp" data-product-img-src="<?php echo esc_attr( $img[0] ); ?>"></div>
				<?php

				// echo wp_get_attachment_image( $attachment_id, 'shop_catalog', '', $attr = array(
				//  	'class' => 'thumbnail-flip-slide attachment-shop-catalog'
				// ) );
			}
		}
	}

	// Header Background Image
	function woocommerce_category_header_background_image() {

		global $wp_query;

		if ( !is_product_category() ) return false;

		$cat = $wp_query->get_queried_object();
		$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
		$image_src = wp_get_attachment_url( $thumbnail_id );

		if ( $image_src ) {

			layers_inline_styles( '.title-container.storekit-category-header-image', 'background', array(
				'background' => array(
					'position' => 'center center',
					'repeat' => 'no-repeat',
					'image' => $thumbnail_id,
					'stretch' => TRUE,
				)
			) );

			// echo '<img src="' . $image_src . '" alt="" />';
		}
	}
	function woocommerce_category_header_background_image_class( $class ) {

		if( !function_exists( 'layers_get_theme_mod' ) ) return;

		global $wp_query;

		if ( !is_product_category() ) return $class;

		$cat = $wp_query->get_queried_object();
		$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
		$image_src = wp_get_attachment_url( $thumbnail_id );

		foreach( $class as $key => $classes ){
			if( 'invert' == $classes ){
				unset( $class[ $key ] );
			}
		}

		$class[] = 'invert';

		if ( $image_src ) {

			// Add necessary class to the 'title-container'.
			$class[] = 'storekit-category-header-image';

			if ( 'small' == layers_get_theme_mod( 'woocommerce-header-category-size' ) ) {
				$class[] = 'large';
			}
			elseif ( 'medium' == layers_get_theme_mod( 'woocommerce-header-category-size' ) ) {
				$class[] = 'extra-large';
			}
			elseif ( 'large' == layers_get_theme_mod( 'woocommerce-header-category-size' ) ) {
				$class[] = 'massive';
			}
		}

		return $class;
	}

}

// Initialize
Layers_Controls_WooCommerce::get_instance();