<?php

if( !defined('ABSPATH') ) {
	exit;
}

if( !class_exists('order_export_process') ) {

	class order_export_process {

		/**
		 * Delimiter for CSV file
		 */
		static $delimiter;

		/**
		 * Tells which fields to export.
		 * 
		 * Also reset orders of fields according to which they were added
		 * in plugin.
		 * 
		 * Refer this support link: https://wordpress.org/support/topic/change-order-5?replies=7#post-6818741
		 */
		static function export_options() {

			global $wpg_order_columns;

			$settings = wpg_order_export::get_settings_fields();
			$settings = apply_filters( 'wpg_export_options_settings', $settings );

			$fields = array();

			$setting_order = array();

			if( is_array( $settings ) ) {

				foreach( $settings as $setting ) {

					if( !is_array( $setting ) || empty($setting['id']) ){
						continue;
					}

					array_push( $setting_order, $setting['id'] );
				}

				$new_order = array();

				foreach( $setting_order as $_setting ) {

					if( empty( $wpg_order_columns[$_setting] ) ){
						continue;
					}

					$new_order[$_setting] = $wpg_order_columns[$_setting];
				}

				$wpg_order_columns = $new_order;
			}

			foreach( $wpg_order_columns as $key=>$val ) {

				$retireve = get_option( $key, 'no' );
				$fields[$key] = ( strtolower($retireve) === 'yes' ) ? true : false;
			}

			return $fields;
		}

		/**
		 * Returns order details
		 */
		static function get_orders() {

			$fields		=	self::export_options();
			$fields		=	array_filter( $fields, 'wsoe_array_filter' );
			$headings	=	self::csv_heading($fields);

			$delimiter	=	( empty( $_POST['wpg_delimiter'] ) || ( gettype( $_POST['wpg_delimiter'] ) !== 'string' ) ) ? ',' : $_POST['wpg_delimiter'][0];

			/**
			 * Filter : wpg_delimiter
			 * Filters the delimiter for exported csv file. Override user defined
			 * delimiter by using this filter.
			 */
			self::$delimiter = apply_filters( 'wpg_delimiter', $delimiter );

			$iterator	=	0;
			$chunk_size	=	apply_filters( 'wsoe_chunk_size', 20 );

			@set_time_limit(0);
			do{

				/* Check which order statuses to export. */
				$order_statuses	=	( !empty( $_POST['order_status'] ) && is_array( $_POST['order_status'] ) ) ? $_POST['order_status'] : array_keys( wc_get_order_statuses() );

				$args = array(
							'post_type'=>'shop_order',
							'posts_per_page'=> $chunk_size,
							'post_status'=> apply_filters( 'wpg_order_statuses', $order_statuses ),
							'offset' => ($chunk_size*$iterator)
						);

				$args['date_query'] = array(
										array(
											'after'=>  $_POST['start_date'],
											'before'=> $_POST['end_date'],
											'inclusive' => true )
									);

				$args = apply_filters( 'wsoe_query_args', $args );

				$orders = new WP_Query( $args );

				if( $orders->have_posts() ) {

					/**
					 * Do these operations for only first iteration.
					 */
					if( !$iterator ){

						/**
						 * This will be file pointer
						 */
						$csv_file = self::create_csv_file();

						if( empty($csv_file) ) {
							return new WP_Error( 'not_writable', __( 'Unable to create csv file, upload folder not writable', 'woocommerce-simply-order-export' ) );
						}

						fputcsv( $csv_file, $headings, self::$delimiter );
					
					}

					/**
					 * Loop over each order
					 */
					while( $orders->have_posts() ) {

						$csv_values = array();

						$orders->the_post();

						$order_details = new WC_Order( get_the_ID() );

						$items = $order_details->get_items();

						foreach( $items as $item_id => $item ) {
							$csv_values = array();
							self::add_fields_diff_row( $fields, $csv_values, $order_details, $item_id, $item );
							fputcsv( $csv_file, $csv_values, self::$delimiter );
						}

					}
					wp_reset_postdata();

				}elseif( empty($iterator) ){ // If there are no orders at first place, send error.

					return new WP_Error( 'no_orders', __( 'No orders for specified duration.', 'woocommerce-simply-order-export' ) );
				}
				
				++$iterator;

			}while( ( $iterator < $orders->max_num_pages ) );
		}

		/**
		 * 
		 * @param type $fields Fields to retrieve.
		 * @param type $csv_values CSV Pointer.
		 * @param type $order_details Order object.
		 * @param type $item_id Line item ID.
		 * @param type $current_item Current iterated line item.
		 */
		static function add_fields_diff_row( $fields, &$csv_values, $order_details, $item_id = null, $current_item = null ) {

			/**
			 * Loop over fields and add value for corresponding field.
			 */
			foreach( $fields as $key =>$field ) {

				switch ( $key ) {

					/**
					 * Check if we need order ID.
					 */
					case 'wc_settings_tab_order_id':
						array_push( $csv_values, $order_details->get_id() );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					/**
					 * Check if we need customer name.
					 */
					case 'wc_settings_tab_customer_name':
						array_push( $csv_values, self::customer_name( get_the_ID() ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					/**
					 * Check if we need product name.
					 */
					case 'wc_settings_tab_product_name':
						array_push( $csv_values, $current_item->get_product()->get_name() );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					/**
					 * Check if we need product quantity.
					 * Product quantity will only be exported if user has selected product name to be exported.
					 * 
					 * If product name is not selected, this column will be filled with just dashes. ;)
					 */
					case 'wc_settings_tab_product_quantity':
						array_push( $csv_values, $current_item->get_quantity() );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;
					
					/**
					 * Check if we need product variations
					 */
					case 'wc_settings_tab_product_variation':
						array_push( $csv_values, self::get_product_variation( $item_id, $order_details, $current_item ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $current_item->get_product()->get_id(), $current_item ) );
					break;

					/**
					 * Check if we need order amount.
					 */
					case 'wc_settings_tab_amount':
						array_push( $csv_values, wsoe_formatted_price( $order_details->get_total(), $order_details ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;
							
					/**
					 * Check if we need customer email.
					 */
					case 'wc_settings_tab_customer_email':
						array_push( $csv_values, self::customer_meta( get_the_ID(), '_billing_email' ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					/**
					 * Check if we need customer phone.
					 */
					case 'wc_settings_tab_customer_phone':
						array_push( $csv_values, self::customer_meta( get_the_ID(), '_billing_phone' ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					/**
					 * Check if we need order status.
					 */
					case 'wc_settings_tab_order_status':
						array_push( $csv_values, ucwords($order_details->get_status()) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;

					default :
						/**
						 * Add values to CSV.
						 * 
						 * @param array $csv_values Array of csv values, callback function should accept this argument by reference.
						 * 
						 * @param Object $order_details WC_Order object
						 * 
						 * @param String $key Current key in loop.
						 * 
						 */
						do_action_ref_array( 'wpg_add_values_to_csv', array( &$csv_values, $order_details, $key, $fields, $item_id, $current_item ) );
						do_action_ref_array( 'wsoe_after_value_'.$key, array( &$csv_values, $order_details, $item_id, $current_item ) );
					break;
				}
			}

		}

		/**
		 * Returns customer related meta.
		 * Basically it is just get_post_meta() function wrapper.
		 */
		static function customer_meta( $order_id , $meta = '' ) {

			if( empty( $order_id ) || empty( $meta ) )
				return '';

			return get_post_meta( $order_id, $meta, true );
		}

		static function get_product_variation ( $line_item_id, $order_details, $current_item ) {

			//$order_item = new WC_Order_Item( $line_item_id );
			//$metadata =  $order_item->get_meta_data();

			$metadata = $current_item->get_meta_data();

			$exclude_meta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
				'_qty',
				'_tax_class',
				'_product_id',
				'_variation_id',
				'_line_subtotal',
				'_line_subtotal_tax',
				'_line_total',
				'_line_tax',
			) );

			$variation_details = array();

			foreach( $metadata as $k => $meta ) {

				if( in_array( $meta->key, $exclude_meta ) ){
					continue;
				}

				// Skip serialised meta
				if ( is_serialized( $meta->value ) ) {
					continue;
				}

				// Get attribute data
				if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta->key ) ) ) {

					$term               = get_term_by( 'slug', $meta->value, wc_sanitize_taxonomy_name( $meta->key ) );
					$meta->key			= wc_attribute_label( wc_sanitize_taxonomy_name( $meta->key ) );
					$meta->value		= isset( $term->name ) ? $term->name : $meta->value;

				}else {

					$_product	=	$current_item->get_product();
					$meta->key	=	apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta->key, $_product ), $meta->key );
				}

				array_push( $variation_details, wp_kses_post( urldecode( $meta->key ) ) .': '.wp_kses_post( urldecode( $meta->value ) ) );
			}

			return $variation_details = implode( ' | ', $variation_details );
		}

		/**
		 * Returns customer name for particular order
		 * @param type $order_id
		 * @return string
		 */
		static function customer_name( $order_id ) {

			if( empty( $order_id ) ){
				return '';
			}

			$firstname = get_post_meta( $order_id, '_billing_first_name', true );
			$lastname  = get_post_meta( $order_id, '_billing_last_name', true );

			return trim( $firstname.' '. $lastname );			
		}

		/**
		 * Makes first row for csv
		 */
		static function csv_heading( $fields ) {

			if( !is_array( $fields ) ){
				return false;
			}

			global $wpg_order_columns;
			$headings = array();

			foreach( $fields as $key=>$val ) {

				if( $val === true && array_key_exists( $key, $wpg_order_columns ) ){
					array_push( $headings, $wpg_order_columns[$key] );
					// By using this we can add heading for keys which are not sanitized.
					do_action_ref_array( 'wsoe_after_heading_'.$key, array( &$headings ) );
				}
			}

			return $headings;

		}

		/**
		 * Creates csv file in upload directory.
		 */
		static function create_csv_file() {

			$csv_filename = empty($_POST['woo_soe_csv_name']) ? 'order_export.csv' : sanitize_file_name($_POST['woo_soe_csv_name']) .'.csv';
			$new_filename = wp_unique_filename( trailingslashit( wsoe_upload_dir() ), $csv_filename );

			$csv_file = fopen( trailingslashit( wsoe_upload_dir() ) . $new_filename, 'w+' );

			do_action( 'wsoe_file_created', $_POST['start_date'], $_POST['end_date'], $new_filename, $csv_file );

			/**
			 * Save file name in global for later use.
			 */
			$GLOBALS['wsoe_filename'] = str_replace( '.csv', '', $new_filename );
			return $csv_file;
		}
	}
}