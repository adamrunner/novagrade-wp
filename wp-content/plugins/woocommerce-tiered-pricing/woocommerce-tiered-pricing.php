<?php
/*
Plugin Name: WooCommerce Tiered Pricing
Plugin URI:  http://ignitewoo.com
Description: Allows you to set price tiers for products and variations based on user roles.
Version: 2.4.10
Author: IgniteWoo.com
Author URI: http://ignitewoo.com
Email: support@ignitewoo.com
*/



/**
* Required functions
*/
if ( ! function_exists( 'ignitewoo_queue_update' ) )
	require_once( dirname( __FILE__ ) . '/ignitewoo_updater/ignitewoo_update_api.php' );

$this_plugin_base = plugin_basename( __FILE__ );

add_action( "after_plugin_row_" . $this_plugin_base, 'ignite_plugin_update_row', 1, 2 );


/**
* Plugin updates
*/
ignitewoo_queue_update( plugin_basename( __FILE__ ), '8dc1e758206277c3c321a21b20afe8c3', '5821' );



class woocommerce_tiered_pricing { 

	var $roles;

	function __construct() {

		add_action( 'init', array( &$this, 'load_plugin_textdomain' ) );

		add_action( 'init', array( &$this, 'init' ), 1 );

		// this lets the plugin adjust the price so it shows up in the cart on the fly
		// triggers when someone clicks "Add to Cart" from a product page
		add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_item_from_session' ), -1, 1 );

		// this helps with mini carts such as the one in the ShelfLife theme 
		// gets accurate pricing into the session before theme displays it on the screen
		// helps when "add to cart" does not redirect to cart page immediately
		// Deprecate this? 
		// add_action('woocommerce_before_calculate_totals', array( &$this, 'predisplay_calculate_and_set_session'), 9999999, 1 );

		add_filter( 'woocommerce_get_price', array( &$this, 'maybe_return_price' ), 999, 2 );
		add_filter( 'woocommerce_get_variation_price', array( &$this, 'maybe_return_var_price' ), 999, 4 );
		add_filter( 'woocommerce_get_variation_regular_price', array( &$this, 'maybe_return_var_price' ), 999, 4 );

		// ensure sale price is always empty for tier role buyers so that the price display doesn't include
		// a marked out regular retail price
		add_filter( 'woocommerce_get_price_html', array( &$this, 'maybe_get_tier_price_html' ), 999, 2 );
		
		// Backorders
		add_filter( 'woocommerce_product_backorders_allowed', array( &$this, 'maybe_allow_backorders' ), 999, 2 );

		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( &$this, 'bulk_edit' ) );
		
		add_action( 'woocommerce_product_after_variable_attributes', array( &$this, 'add_variable_attributes'), 1, 3 );
		
		add_action( 'woocommerce_product_options_pricing', array( &$this, 'add_simple_price' ), 1 );
		
		add_filter( 'option_woocommerce_calc_taxes', array( &$this, 'override_tax_setting' ), 9999, 1 );
		
		// adjust taxes to zero in cart and checkout
		add_filter( 'woocommerce_get_cart_tax', array( &$this, 'get_cart_tax' ), 1, 1 );
		add_filter( 'woocommerce_calculate_totals', array( &$this, 'calculate_totals' ), 999, 1 );
		add_filter( 'option_woocommerce_calc_taxes', array( &$this, 'override_tax_setting' ), 9999, 1 );
		
		// WC 2.4 and newer: 
		add_action( 'woocommerce_ajax_save_product_variations', array( &$this, 'ajax_process_product_meta_variable' ), 5 );

	}
	
	/*
	function predisplay_calculate_and_set_session( $stuff = '' ) { 
		global $woocommerce;

		if ( WC()->cart->cart_contents )
			return;

		foreach( WC()->cart->cart_contents as $key => $item ) { 

			$item_data = array();
			$item_data = $item['data'];

			// call our internal function to see if wholesale prices need to be set
			$item_data = $this->add_cart_item( $item );

		}
	
		// Set session data
		$_SESSION['cart'] = $woocommerce->cart->cart_contents;
		$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
		$_SESSION['cart_contents_total'] = $woocommerce->cart->cart_contents_total;
		$_SESSION['cart_contents_weight'] = $woocommerce->cart->cart_contents_weight;
		$_SESSION['cart_contents_count'] = $woocommerce->cart->cart_contents_count;
		$_SESSION['cart_contents_tax'] = $woocommerce->cart->cart_contents_tax;
		$_SESSION['total'] = $woocommerce->cart->total;
		$_SESSION['subtotal'] = $woocommerce->cart->subtotal;
		$_SESSION['subtotal_ex_tax'] = $woocommerce->cart->subtotal_ex_tax;
		$_SESSION['tax_total'] = $woocommerce->cart->tax_total;
		$_SESSION['shipping_taxes'] = $woocommerce->cart->shipping_taxes;
		$_SESSION['taxes'] = $woocommerce->cart->taxes;
		$_SESSION['discount_cart'] = $woocommerce->cart->discount_cart;
		$_SESSION['discount_total'] = $woocommerce->cart->discount_total;
		$_SESSION['shipping_total'] = $woocommerce->cart->shipping_total;
		$_SESSION['shipping_tax_total'] = $woocommerce->cart->shipping_tax_total;
		$_SESSION['shipping_label'] = isset( $woocommerce->cart->shipping_label ) ? $woocommerce->cart->shipping_label : '';

	}
	*/
	
	function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'ignitewoo_tiered_pricing' );

		load_textdomain( 'ignitewoo_tiered_pricing', WP_LANG_DIR.'/woocommerce/ignitewoo_tiered_pricing-'.$locale.'.mo' );

		$plugin_rel_path = apply_filters( 'ignitewoo_translation_file_rel_path', dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		load_plugin_textdomain( 'ignitewoo_tiered_pricing', false, $plugin_rel_path );

	}

	
	// add the new role, same as 'customer' role with a different name actually
	// add actions
	function init() { 

		@session_start();
		
		add_action( 'woocommerce_process_product_meta_simple', array( &$this, 'process_product_meta' ), 1, 1 );

		add_action( 'woocommerce_process_product_meta_variable', array( &$this, 'process_product_meta_variable' ), 999, 1 );
/**
		// Regular price displays, before variations are selected by a buyer
		add_filter( 'woocommerce_grouped_price_html', array( &$this, 'maybe_return_wholesale_price' ), 1, 2 );
		add_filter( 'woocommerce_variable_price_html', array( &$this, 'maybe_return_wholesale_price' ), 1, 2 );
**/
		// Javscript related
		
		add_filter( 'woocommerce_variation_sale_price_html', array( &$this, 'maybe_return_variation_price' ), 1, 2 );
		add_filter( 'woocommerce_variation_price_html', array( &$this, 'maybe_return_variation_price' ), 1, 2 );
		add_filter( 'woocommerce_variable_empty_price_html', array( &$this, 'maybe_return_variation_price_empty' ), 999, 2 );

		add_filter( 'woocommerce_product_is_visible', array( &$this, 'variation_is_visible' ), 99999, 2 );

		add_filter( 'woocommerce_available_variation', array( &$this, 'maybe_adjust_variations' ), 1, 3 );

		add_filter( 'woocommerce_is_purchasable', array( &$this, 'is_purchasable' ), 1, 2 );

		add_filter( 'woocommerce_sale_price_html', array( &$this, 'maybe_return_wholesale_price' ), 1, 2 );
		add_filter( 'woocommerce_price_html', array( &$this, 'maybe_return_wholesale_price' ), 1, 2 );
		add_filter( 'woocommerce_empty_price_html', array( &$this, 'maybe_return_wholesale_price' ), 1, 2 );

		add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_item_from_session' ), 999, 1 );

		$this->settings = get_option( 'woocommerce_ignitewoo_tiered_pricing_settings' );

		$defaults = array(
				'show_regular_price' => '',
				'show_savings' => '',
				'show_regular_price_label' => __( 'Regularly', 'ignitewoo_tiered_pricing' ),
				'show_savings_label' => __( 'You Save', 'ignitewoo_tiered_pricing' ),
		);
		
		$this->settings = wp_parse_args( $this->settings, $defaults );
		
		// Force the cart to recalculate so that the first item add into a the cart widgets results in the correct subtotal
		add_action( 'wp_ajax_woocommerce_get_refreshed_fragments', array( &$this, 'get_refreshed_fragments' ), -1 );
		add_action( 'wp_ajax_nopriv_woocommerce_get_refreshed_fragments', array( &$this, 'get_refreshed_fragments' ), -1 );
		add_action( 'wc_ajax_get_refreshed_fragments', array( &$this, 'get_refreshed_fragments' ), -1 );

	}
	
	function get_refreshed_fragments() { 
		WC()->cart->calculate_totals();
	}
	
	function format_price( $price = '' ) {
	
		if ( function_exists( 'wc_price' ) )
			return wc_price( $price );
		else 
			return woocommerce_price( $price );
	}
	
	function override_tax_setting( $setting = '' ) { 
		global $current_user, $ignitewoo_remove_tax;

		if ( !$current_user ) 
			$current_user = get_currentuserinfo();

		if ( current_user_can( 'no_tax' ) ) 
			return false;

		return $setting; 

	}

	function calculate_totals() { 

		global $current_user, $woocommerce, $ignitewoo_remove_tax;

		if ( false == $ignitewoo_remove_tax ) return;

		if ( !$current_user ) 
			$current_user = get_currentuserinfo();

		if ( current_user_can( 'no_tax' ) ) {

			foreach ( $woocommerce->cart->cart_contents as &$line_item ) { 

				$line_item['line_tax'] = 0;
				$line_item['line_subtotal_tax'] = 0;

			}

			$woocommerce->cart->tax_total = 0;
			$woocommerce->cart->shipping_tax_total = 0;
			$woocommerce->cart->taxes = array();
			$woocommerce->cart->shipping_taxes = array();

		}

	}
	
	
	
	function get_cart_tax( $amount ) { 
		global $current_user, $woocommerce, $ignitewoo_remove_tax;

		if ( false == $ignitewoo_remove_tax ) return $amount;

		if ( !$current_user ) 
			$current_user = get_currentuserinfo();

		if ( current_user_can( 'no_tax' ) ) 
			return 0;

		return $amount;

	}

	function get_item_from_session( $item_data = '' ) { 
		global $current_user, $woocommerce;

		if ( !$current_user ) 
			$current_user = get_currentuserinfo();
			
		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
			
		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) )
				continue;

			$_product = get_product( $item_data['product_id'] ); 

			if ( isset( $item_data['variation_id'] ) && 'variable' == $_product->product_type ) 
				$level_price = get_post_meta( $item_data['variation_id' ], '_' . $role . '_price', true );

			else if ( 'simple' == $_product->product_type || 'external' == $_product->product_type )
				$level_price = get_post_meta( $item_data['product_id' ], '_' . $role . '_price', true );


			else // all other product types - possibly incompatible with custom product types added by other plugins\
				$level_price = get_post_meta( $item_data['product_id' ], '_' . $role . '_price', true );

			if ( $level_price ) { 

				$item_data['data']->price = $level_price;
				
				$item_data['data']->regular_price = $level_price;


			}

		}

		return $item_data;

	}

	// Returns unformated price
	function maybe_return_tier_price_for_addons( $price, $pid ) { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
		
		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) ) 
				continue;

			$price = get_post_meta( $pid, '_' . $role . '_price', true );
		}

		if ( empty( $price ) )
			return 0;
		else 
			return $price; 
	
	}
	
	function maybe_return_wholesale_price( $price, $_product ) { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;

		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) ) 
				continue;

			$vtype = 'variable';

			if ( $_product->is_type('grouped') ) { 

				$min_price = '';
				$max_price = '';

				foreach ( $_product->get_children() as $child_id ) { 

					$child_price = get_post_meta( $child_id, '_' . $role . '_price', true );

					if ( !$child_price ) 
						continue;

					if ( $child_price < $min_price || $min_price == '' ) $min_price = $child_price;

					if ( $child_price > $max_price || $max_price == '' ) $max_price = $child_price;

				}


				$price = '<span class="from">' . __('From:', 'ignitewoo_tiered_pricing') . ' </span>' . $this->format_price( $min_price );

			} elseif ( $_product->is_type( $vtype ) ) {

				$wprice_min = get_post_meta( $_product->id, 'min_variation_' . $role . '_price', true );
				
				$wprice_max = get_post_meta( $_product->id, 'max_variation_' . $role . '_price', true );

				if ( $wprice_min !== $wprice_max )
					$price = '<span class="from">' . __( 'From:', 'ignitewoo_tiered_pricing') . $wprice_min . ' </span>';

				if ( !empty( $wprice_min ) && !empty( $wprice_max ) && $wprice_min == $wprice_max ) 
					return $price;

				else if ( !empty( $wprice_min ) )
					$price = '<span class="from">' . __( 'From:', 'ignitewoo_tiered_pricing') . ' ' . $this->format_price( $wprice_min ) . ' </span>';
					
				else { 
				
					$wprice_min = get_post_meta( $_product->id, '_min_variation_regular_price', true );
					
					$wprice_max = get_post_meta( $_product->id, '_max_variation_regular_price', true );
				
					if ( $wprice_min !== $wprice_max )
						$price = '<span class="from">' . __( 'From:', 'ignitewoo_tiered_pricing') . $wprice_min . ' </span>';

					if (  !empty( $wprice_min ) && !empty( $wprice_max ) && $wprice_min == $wprice_max ) 
						return $price;
					
					else if ( !empty( $wprice_min ) )
						$price = '<span class="from">' . __( 'From:', 'ignitewoo_tiered_pricing') . ' ' . $this->format_price( $wprice_min ) . ' </span>';

				}

			} else { 

				$wprice_min = get_post_meta( $_product->id, '_' . $role . '_price', true );
					
				if ( isset( $wprice_min ) && $wprice_min > 0 )
					$price = $this->format_price( $wprice_min );

				elseif ( '' === $wprice_min ) {
				
					$price = get_post_meta( $_product->id, '_price', true );
					if ( !empty( $price ) )
						$price = $this->format_price( $price ); 
						
				} elseif ( 0 == $wprice_min ) 
					$price = __( 'Free!', 'ignitewoo_tiered_pricing' );
				
				if ( !empty( $wprice_min ) && 'yes' == $this->settings['show_regular_price'] || 'yes' == $this->settings['show_savings'] ) { 
				
					$rprice = get_post_meta( $_product->id, '_regular_price', true );

					if ( empty( $wprice_min ) )
						continue; 
						
					if ( floatval( $rprice ) > floatval( $wprice_min ) && 'yes' == $this->settings['show_regular_price'] ) 
						$price .= '<br><span class="normal_price">' . $this->settings['show_regular_price_label'] . ' ' . $this->format_price( $rprice ) . '</span>';
					
					$savings = ( floatval( $rprice ) - floatval( $wprice_min ) );
					
					if ( ( $savings < $rprice ) && 'yes' == $this->settings['show_savings'] ) 
						$price .= '<br><span class="normal_price savings">' . $this->settings['show_savings_label'] . ' ' . $this->format_price( $savings ) . '</span>';
						
				}
			}

		}

		return $price; 

	}


	function is_purchasable( $purchasable, $_product ) { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 
			
		$this->get_roles();

		if ( empty( $this->roles ) )
			return $purchasable;

		foreach( $this->roles as $role => $name ) {

			if ( !current_user_can( $role ) )
				continue;

			$is_variation = $_product->is_type( 'variation' );

			if ( !$is_variation ) 
				$is_variation = $_product->is_type( 'variable' );

			if ( $is_variation  ) { 
			
				// Variable products
				if ( !isset( $_product->variation_id ) )
					return $purchasable;

				$price = get_post_meta( $_product->variation_id, 'min_variation_' . $role . '_price', true );

				if ( !isset( $price ) )
					return $purchasable;

			} else { 
			
				// Simple products
				$price = get_post_meta( $_product->id, '_' . $role . '_price', false );

				if ( !empty( $price ) )
					return true;
				else 
					return $purchasable;
					
					
			}
		}
		
		return $purchasable;

	}


	function maybe_allow_backorders( $allow, $product_id ) { 
	
		$this->get_roles();

		if ( empty( $this->roles ) )
			return $purchasable;

		foreach( $this->roles as $role => $name ) {

			if ( !current_user_can( $role ) )
				continue;
		
			if ( current_user_can( 'backorders' ) )
				return true;
		}
		
		return $allow; 
	
	}
	
	
	function maybe_get_tier_price_html( $price_html, $_product ) { 

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return $price_html;
			
		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) )
				continue;

			if ( isset( $_product->product_type ) && 'variable' == $_product->product_type ) {
			
				$min = $this->maybe_return_var_price( $price = null, $_product, 'min', false );
				$max = $this->maybe_return_var_price( $price = null, $_product, 'max', false );
				
				if ( $min == $max ) { 
				
					$price_html = '<p class="price"><span class="amount">' . wc_price( $min )  . '</span></p>';
					
				} else { 
				
					$price_html = '<p class="price"><span class="amount">' . wc_price( $min )  . ' </span>&ndash;<span class="amount">' . wc_price( $max ) . '</span></p>';
				}
				
				return $price_html;
			
			} else { // non-variable products 
			
				$pos = strpos( $price_html, '</del>' );
				
				if ( $pos !== false ) 
					$price_html = trim( substr( $price_html, $pos, strlen( $price_html ) ) );
				
				$price_html = str_replace( array( '<ins>', '</ins>'), '', $price_html );
				
				return $price_html;
			
			}
			
		}
		
		return $price_html;
	}
	
	
	function maybe_return_price( $price = '', $_product ) { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return $price;

		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) )
				continue;

			if ( isset( $_product->variation_id ) ) {

				//if ( isset( $_product->variation_id ) ) 
					$wholesale = get_post_meta( $_product->variation_id, '_' . $role . '_price', true );
				//else 
				//	$wholesale = '';

				if ( intval( $wholesale ) > 0 && version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
					$_product->product_custom_fields[ '_' . $role . '_price' ] = array( $wholesale );


				if ( isset( $_product->product_custom_fields[ '_' . $role . '_price' ] ) && is_array( $_product->product_custom_fields[ '_' . $role . '_price'] ) && $_product->product_custom_fields[ '_' . $role . '_price'][0] > 0 ) {

					if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
					$price = $_product->product_custom_fields[ '_' . $role . '_price'][0];

				} elseif ( $_product->price === '' ) 

					$price = '';

				elseif ($_product->price == 0 ) 

					$price = __( 'Free!', 'ignitewoo_tiered_pricing' );

				return $price; 

			}

			$tier_price = get_post_meta( $_product->id, '_' . $role . '_price', true );
			
			if ( empty( $tier_price ) ) 
				return $price;
			else 
				return $tier_price;
				
			//$rprice = get_post_meta( $_product->id, '_' . $role . '_price', true );

			//if ( !empty( $rprice ) )
			//	return $rprice;
		}

		return $price;
		

	}

	
	function maybe_return_var_price( $price, $_variation, $min_or_max, $display ) { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo();
			
		if ( empty( $this->roles ) )
			return $price; 
			
		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) )
				continue;

			$low_price = 999999999;
			
			$price_high = 0;
			
			$cid = null; 
			
			foreach ( $_variation->get_children() as $child_id ) {

				$p = floatval( get_post_meta( $child_id, '_' . $role . '_price', true ) );

				if ( empty( $p ) ) 
					$p = floatval( get_post_meta( $child_id, '_price', true ) );

				if ( 'max' == $min_or_max && ( $p > (float)$price_high )  ) { 

					$price_high = $p;
					$cid = $child_id;
				}
				
				if ( 'min' == $min_or_max && ( $p < (float)$low_price )  ) { 

					$low_price = $p;
					$cid = $child_id;
				}
			}

			$variation_id = $cid;

			if ( $display ) {
			
				$variation = $_variation->get_child( $variation_id );

				if ( $variation ) {
				
					$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
					
					$price = $tax_display_mode == 'incl' ? $variation->get_price_including_tax() : $variation->get_price_excluding_tax();
				
				} else {
				
					$price = '';
				
				}
				
			} else {
			
				$price = get_post_meta( $variation_id, '_' . $role . '_price', true );

				if ( empty( $price ) ) 
					$price = floatval( get_post_meta( $variation_id, '_price', true ) );
			
			}
			
			break;
			
		}
//var_dump( $min_or_max, $price );
		return $price;
	
	}
	
	
	function maybe_adjust_variations( $variation = '', $obj = '' , $variation_obj  = '') { 
		global $current_user;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 
			
		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
			
		foreach( $this->roles as $role => $name ) {
		
			if ( !current_user_can( $role ) ) { 
				continue;

			}

			$price = get_post_meta( $variation_obj->variation_id, '_' . $role . '_price', true );

			$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
			
			$price = $tax_display_mode == 'incl' ? $variation_obj->get_price_including_tax( 1, $price ) : $variation_obj->get_price_excluding_tax( 1, $price );
			
			
			//$price = number_format( $price, absint( get_option( '$this->format_price_num_decimals' ) ), get_option( '$this->format_price_decimal_sep' ), get_option( '$this->format_price_thousand_sep' ) );

			$variation['price_html'] = '<span class="price">' . $this->format_price( $price ) . '</span>';

			if ( ( 'yes' == $this->settings['show_regular_price'] || 'yes' == $this->settings['show_savings'] ) ) { 
	
				$reg_price = get_post_meta( $variation['variation_id'], '_regular_price', true );

				$role_price = get_post_meta( $variation['variation_id'], '_' . $role . '_price', true );

				if ( ( floatval( $role_price ) < floatval( $reg_price ) ) && 'yes' == $this->settings['show_regular_price'] ) 
					$variation['price_html']  .= '<br><span class="price normal_price">' . $this->settings['show_regular_price_label'] . ' <span class="amount">' . $this->format_price( $reg_price ) . '</span></span>';
				
				$savings = ( floatval( $reg_price ) - floatval( $role_price ) );

				if ( $savings < $reg_price && 'yes' == $this->settings['show_savings'] ) 
					$variation['price_html']  .= '<br><span class="price normal_price savings">' . $this->settings['show_savings_label'] . ' <span class="amount">' . $this->format_price( $savings ) . '</span></span>';
					
			}


		}
		
		return $variation;

	}


	// For WooCommerce 2.x flow, to ensure product is visible as long as a role price is set
	function variation_is_visible( $visible, $vid ) {
		global $product;

		if ( !isset( $product->children ) || count( $product->children ) <= 0 )
			return $visible;

		$variation = new ign_tieried_dummy_variation();

		$variation->variation_id = $vid;

		$res = $this->maybe_return_variation_price( 'xxxxx', $variation );

		if ( !isset( $res ) || empty( $res ) || '' == $res )
			$res = false;
		else
			$res = true;

		return $res;
	}


	// Runs during the woocommerce_variable_empty_price_html filter call, used here in this way for debugging purposes
	// This is used for WooCommerce 2.x compatibility
	function maybe_return_variation_price_empty( $price, $_product ) {
		global $product;

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
			
		foreach( $this->roles as $role => $name ) { 
			
			if ( !current_user_can( $role  ) )
				continue;

			$min_variation_wholesale_price = get_post_meta( $_product->id, 'min_variation_' . $role . '_price' , true );
			
			$max_variation_wholesale_price = get_post_meta( $_product->id, 'max_variation_' . $role . '_price', true );

			if ( $min_variation_wholesale_price !== $max_variation_wholesale_price )
				$price = '<span class="from">' . __( 'From:', 'ignitewoo_tiered_pricing') . ' ' .  $this->format_price( $min_variation_wholesale_price ) . ' </span>';
				
			else 
				$price = '<span class="from">' . $this->format_price( $min_variation_wholesale_price ) . ' </span>';
		}
		
		return $price;

	}


	// Handles getting prices for variable products
	// Used by woocommerce_variable_add_to_cart() function to generate Javascript vars that are later 
	// automatically injected on the public facing side into a single product page.
	// This price is then displayed when someone selected a variation in a dropdown
	function maybe_return_variation_price( $price, $_product ) {
		global $current_user, $product; // parent product object - global

		// Sometimes this hook runs when the price is empty but wholesale price is not, 
		// So check for that and handle returning a price for archive page view
		// $attrs = $_product->get_attributes();
		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return $price;
		
		$is_variation = $_product->is_type( 'variation' );

		if ( !$is_variation )
			$is_variation = $_product->is_type( 'variable' );


		if ( !isset( $_product->variation_id ) && !$is_variation ) 
			    return $price;

		if ( !isset( $current_user->ID ) ) 
			$current_user = get_currentuserinfo(); 

				
		foreach( $this->roles as $role => $name ) { 
		
			if ( $is_variation && current_user_can( $role ) ) { 

				$price = $this->format_price( get_post_meta( $_product->variation_id, '_' . $role . '_price', true ) );

				return $price;

			}
		}
		
		foreach( $this->roles as $role => $name ) { 
		
			if ( current_user_can( $role ) )  { 

				$wholesale = get_post_meta( $_product->variation_id, '_' . $role . '_price', true );

				if ( intval( $wholesale ) > 0 && version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
					$product->product_custom_fields[ '_' . $role . '_price'] = array( $wholesale );

				if ( is_array( $product->product_custom_fields[ '_' . $role . '_price' ] ) && $product->product_custom_fields[ '_' . $role . '_price'][0] > 0 ) {

					if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
						$price = $this->format_price( $product->product_custom_fields[ '_' . $role . '_price'][0] );

				} elseif ( $product->price === '' ) 

					$price = '';

				elseif ($product->price == 0 ) 

					$price = __( 'Free!', 'ignitewoo_tiered_pricing' );

			} 

		}
		
		return $price;

	}


	// process simple product meta
	function process_product_meta( $post_id, $post = '' ) {

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
		
		foreach( $this->roles as $role => $name ) { 

			if ( !empty( $_POST[ $role . '_price'] ) && '' !==  stripslashes( $_POST[ $role . '_price'] ) )
				update_post_meta( $post_id, '_' . $role . '_price', stripslashes( $_POST[ $role . '_price' ] ) );
			else
				delete_post_meta( $post_id, '_' . $role . '_price' );

		}

	}
	

	// process variable product meta
	function process_product_meta_variable( $post_id ) {

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;

		$variable_post_ids = $_POST['variable_post_id'];
		
		if ( empty( $variable_post_ids ) )
			return;
		
		foreach( $this->roles as $role => $name ) { 

			foreach( $variable_post_ids as $key => $id ) { 
			
				if ( empty( $id ) || absint( $id ) <= 0 ) 
					continue;
				
				//if ( '' == $_POST[ $role .  '_price' ][ $key ] )
				//	continue;
					
				update_post_meta( $id, '_' . $role . '_price', $_POST[ $role .  '_price' ][ $key ] );

			}

		}

		$post_parent = $post_id;
		
		$children = get_posts( array(
				    'post_parent' 	=> $post_parent,
				    'posts_per_page'=> -1,
				    'post_type' 	=> 'product_variation',
				    'fields' 		=> 'ids'
			    ) );

		if ( $children ) {

			foreach( $this->roles as $role => $name ) { 
			
				$lowest_price = '';

				$highest_price = '';
			
				foreach ( $children as $child ) {
			
					$child_price = get_post_meta( $child, '_' . $role . '_price', true );

					if ( is_null( $child_price ) ) continue;
		
					// Low price
					if ( !is_numeric( $lowest_price ) || $child_price < $lowest_price ) $lowest_price = $child_price;

					
					// High price
					if ( $child_price > $highest_price )
						$highest_price = $child_price;
				}
				
				update_post_meta( $post_parent, '_' . $role . '_price', $lowest_price );
				
				update_post_meta( $post_parent, 'min_variation_' . $role . '_price' , $lowest_price );
				
				update_post_meta( $post_parent, 'max_variation_' . $role . '_price', $highest_price );

			}


		}
		
	}
	
	function ajax_process_product_meta_variable() {

		$product_id = absint( $_POST['product_id'] );

		$this->process_product_meta_variable( $product_id );
 
		
	}
	
	
	function bulk_edit() { 
			
		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
			
		foreach( $this->roles as $role => $name ) { 
		
			?>
			
			<option value="<?php echo $role ?>_price"><?php _e( $name . ' Price', 'ignitewoo_tiered_pricing' ); ?></option>

			<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) { ?>
			<script>
			
			jQuery('select#field_to_edit').bind( '<?php echo $role ?>_price', function( event ) {
				var bulk_edit  = jQuery( 'select#field_to_edit' ).val(),
				checkbox,
				answer,
				value;
				
				value = window.prompt( woocommerce_admin_meta_boxes_variations.i18n_enter_a_value );

				jQuery( ':input[name^="' + bulk_edit + '"]').not('[name*="dates"]').val( value ).change();
			});
			
			</script>
			
			<?php }
		
		}
		

	}
	
	
	function add_variable_attributes( $loop, $variation_data, $variation ) { 
		
		
		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
		
		foreach( $this->roles as $role => $name ) { 
		
			if ( empty( $variation_data['variation_post_id'] ) && !empty( $variation->ID ) )
				$id = $variation->ID;
			else 
				$id = $variation_data['variation_post_id'];
				
			$wprice = get_post_meta( $id, '_' . $role . '_price', true );

			if ( false === $wprice )
				$wprice = '';
		
			if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<=' ) ) { 
			?>
			<tr>
				<td>
			<?php } ?>
			
					<p class="form-row form-row-full">
					<label><?php echo $name; echo ' ('.get_woocommerce_currency_symbol().')'; ?> <a class="tips" data-tip="<?php _e( 'Enter the price for ', 'ignitewoo_tiered_pricing' ); echo $name ?>" href="#">[?]</a></label>
					<input class="<?php echo $role ?>_price wc_input_price" type="text" size="99" name="<?php echo $role ?>_price[<?php echo $loop; ?>]" value="<?php echo $wprice ?>" step="any" min="0" placeholder="<?php _e( 'Set price ( optional )', 'ignitewoo_tiered_pricing' ) ?>"/>
					</p>
			<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3', '<=' ) ) { ?>
				</td>
			</tr>
			<?php }
		}
	}
	
	function add_simple_price() { 
		global $thepostid;

		$this->get_roles();
		
		if ( empty( $this->roles ) )
			return;
			
		foreach( $this->roles as $role => $name ) { 
		
			$wprice = get_post_meta( $thepostid, '_' . $role . '_price', true );

			woocommerce_wp_text_input( array( 'id' => $role . '_price', 'class' => 'wc_input_price short', 'label' => $name . ' (' . get_woocommerce_currency_symbol() . ')', 'description' => '', 'type' => 'text', 'custom_attributes' => array(
						'step' 	=> 'any',
						'min'	=> '0'
					), 'value' => $wprice ) );
					
		}

	}

	
	function get_roles() {
		global $wp_roles; 
		
		if ( !empty( $this->roles ) )
			return;
			
		if ( class_exists( 'WP_Roles' ) ) 
		    if ( !isset( $wp_roles ) ) 
			$wp_roles = new WP_Roles();  
	
		foreach( $wp_roles->roles as $role => $data ) 
			if ( 'ignite_level_' == substr( $role, 0, 13 ) )
				$this->roles[ $role ] = $data['name'];

	}
	
	
}

$woocommerce_tiered_pricing = new woocommerce_tiered_pricing();

class ign_tieried_dummy_variation {

	function is_type() {
		return true;
	}

}

add_action( 'plugins_loaded', 'ign_tiered_init', 1 );

function ign_tiered_init() { 

	require_once( dirname( __FILE__ ) . '/class-ign-tiered-pricing-settings.php' );

	add_action( 'woocommerce_integrations', 'ignitewoo_tiered_pricing_init'  );
}


function ignitewoo_tiered_pricing_init( $integrations ) {

	$integrations[] = 'IgniteWoo_Tiered_Pricing_Settings';
	
	return $integrations;
}
