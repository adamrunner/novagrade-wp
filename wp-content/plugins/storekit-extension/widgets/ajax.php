<?php  /**
 * WooCommerce Widget Ajax
 *
 * This file is used to fetch, using Ajax, and display different parts of the Layers WooCommerce widgets
 *
 * @package Layers WooCommerce
 * @since Layers WooCommerce 1.0.0
 */

if( !class_exists( 'Layers_WooCommerce_Widget_Ajax' ) ) {

	class Layers_WooCommerce_Widget_Ajax {

		private static $instance;

		/**
		*  Initiator
		*/

		public static function get_instance(){
			if ( ! isset( self::$instance ) ) {
				self::$instance = new Layers_Custom_Meta();
			}
			return self::$instance;
		}

		/**
		*  Constructor
		*/

		public function __construct() {
		}

		public function init() {
			add_action( 'wp_ajax_layers_woocommerce_slider_widget_actions', array( $this, 'slider_widget_actions' ) );
			add_action( 'wp_ajax_layers_woocommerce_category_widget_actions', array( $this, 'category_widget_actions' ) );
		}

		function category_widget_actions(){

			if( !check_ajax_referer( 'layers-woocommerce-product-widget', 'nonce', false ) ) die( 'You threw a Nonce exception' ); // Nonce

			if( !class_exists( 'Layers_WooCommerce_Category_Widget' ) ) return;

			$widget = new Layers_WooCommerce_Category_Widget();

			if( 'category-search' == $_POST[ 'widget_action'] ) {

				$term = (string) stripslashes( $_POST['term'] );

				if( isset( $_POST['categories'] ) && !empty( $_POST['categories'] ) ) {
					foreach( $_POST['categories'] as $category ){
						$exclude[] = $category['id'];
					}
				} else {
					$exclude = false;
				}

				$args = array(
					'search' => $term,
					'exclude' => $exclude,
					'hide_empty' => FALSE
				);

				$terms = get_terms( array( 'product_cat' ), $args );

				$found_categories = array();

				if ( $terms ) {
					foreach ( $terms as $term ) {
						$found_categories[ $term->term_id ] = html_entity_decode( $term->name );
					}
				}

				wp_send_json( $found_categories );

			} else if( 'bulk-add' == $_POST[ 'widget_action'] ) {

				// Get the previous element's column data

				if( isset( $_POST[ 'category_ids' ] ) && '' != ( $_POST[ 'category_ids' ] ) ) {
					foreach( explode( ',', $_POST[ 'category_ids' ] ) as $category_id ) {

						// Let's go ahead and add our defaults along with the product ID into an array to pass to the slide_item() function
						$instance = array_merge( $widget->column_defaults, array( 'category_id' => $category_id ) );

						$widget->column_item( array( 'id_base' => $_POST[ 'id_base' ] , 'number' => $_POST[ 'number' ] ), NULL, $instance );
					}
				}

			}
			die();
		}
		function slider_widget_actions(){

			if( !check_ajax_referer( 'layers-woocommerce-product-widget', 'nonce', false ) ) die( 'You threw a Nonce exception' ); // Nonce

			if( !class_exists( 'Layers_WooCommerce_Slider_Widget' ) ) return;

			$widget = new Layers_WooCommerce_Slider_Widget();

			if( 'product-search' == $_POST[ 'widget_action'] ) {

				$term = (string) stripslashes( $_POST['term'] );

				if( isset( $_POST['products'] ) && !empty( $_POST['products'] ) ) {
					foreach( $_POST['products'] as $product ){
						$exclude[] = $product['id'];
					}
				} else {
					$exclude = false;
				}

				$args = array(
					'post_type'      => array( 'product', 'product_variation' ),
					'posts_per_page' => ( '' == $term ? 5 : -1 ),
					'post_status'    => 'publish',
					'order'          => 'ASC',
					'orderby'        => 'parent title',
					'post__not_in'   => $exclude,
					's' => $term
				);


				$posts = get_posts( $args );
				$found_products = array();

				if ( $posts ) {
					foreach ( $posts as $post ) {
						$product = wc_get_product( $post->ID );
						$found_products[ $post->ID ] = strip_tags( html_entity_decode( str_replace( '&ndash;' , '-', $product->get_formatted_name() ) ) );
					}
				}

				wp_send_json( $found_products );

			} else if( 'bulk-add' == $_POST[ 'widget_action'] ) {

				// Get the previous element's column data

				if( isset( $_POST[ 'product_ids' ] ) && '' != ( $_POST[ 'product_ids' ] ) ) {

					foreach( explode( ',', $_POST[ 'product_ids' ] ) as $product_id ) {

						// Let's go ahead and add our defaults along with the product ID into an array to pass to the slide_item() function
						$instance = array_merge( $widget->new_slide_defaults, array( 'product_id' => $product_id ) );
						$widget->slide_item( array( 'id_base' => $_POST[ 'id_base' ] , 'number' => $_POST[ 'number' ] ), NULL, $instance );
					}
				}

			} else if( 'add' == $_POST[ 'widget_action'] ) {

				// Get the previous element's column data
				parse_str(
					urldecode( stripslashes( $_POST[ 'instance' ] ) ),
					$data
				);

				// Get the previous element's column data
				if( isset( $data[ 'widget-' . $_POST[ 'id_base' ] ] ) && isset( $_POST[ 'last_guid' ] ) && is_numeric( $_POST[ 'last_guid' ] ) ) {
					$instance = $data[ 'widget-' . $_POST[ 'id_base' ] ][ $_POST[ 'number' ] ][ 'slides' ][ $_POST[ 'last_guid' ] ];
				} else {
					$instance = NULL;
				}


				// Get the previous element's column data
				$widget->slide_item( array( 'id_base' => $_POST[ 'id_base' ] , 'number' => $_POST[ 'number' ] ), NULL, $instance );
			}
			die();
		}
	}

	function layers_woocommerce_register_widget_ajax(){
		$widget_ajax = new Layers_WooCommerce_Widget_Ajax();
		$widget_ajax->init();
	}
	add_action( 'init' , 'layers_woocommerce_register_widget_ajax' );
} // if class_exists