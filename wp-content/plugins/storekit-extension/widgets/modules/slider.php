<?php
 /**
 * Sliders Widget
 *
 * This file is used to register and display the Layers - Slider widget.
 *
 * @package Layers
 * @since Layers 1.0.0
 */
if( !class_exists( 'Layers_WooCommerce_Slider_Widget' ) && class_exists( 'Layers_Widget' ) ) {
	class Layers_WooCommerce_Slider_Widget extends Layers_Widget {

		/**
		*  Widget construction
		*/
		function Layers_WooCommerce_Slider_Widget(){

			/**
			* Widget variables
			*
			* @param    varchar         $this->widget_id       Widget title
			* @param    varchar         $widget_id          Widget slug for use as an ID/classname
			* @param    varchar         $post_type          (optional) Post type for use in widget options
			* @param    varchar         $taxonomy           (optional) Taxonomy slug for use as an ID/classname
			* @param    array           $checkboxes     (optional) Array of checkbox names to be saved in this widget. Don't forget these please!
			*/
			$this->widget_title = __( 'Product Slider' , 'layers-storekit' );
			$this->widget_id = 'product-slider';
			$this->post_type = '';
			$this->taxonomy = '';
			$this->checkboxes = array(
					'show_slider_arrows',
					'show_slider_dots',
					'autoplay_slides'
				);

			/* Widget settings. */
			$widget_ops = array( 'classname' => 'obox-layers-' . $this->widget_id .'-widget', 'description' => __( 'This widget is used to show a Slider of your WooCommerce Products in Layers') . $this->widget_id . '.' );

			/* Widget control settings. */
			$control_ops = array( 'width' => LAYERS_WIDGET_WIDTH_LARGE, 'height' => NULL, 'id_base' => LAYERS_THEME_SLUG . '-widget-' . $this->widget_id );

			/* Create the widget. */
			parent::__construct( LAYERS_THEME_SLUG . '-widget-' . $this->widget_id , $this->widget_title, $widget_ops, $control_ops );

			/* Setup Widget Defaults */

			$this->defaults = array (
				'title' => NULL,
				'excerpt' => NULL,
				'autoheight_slides' => 'on',
				'slide_height' => '550',
				'show_slider_arrows' => 'on',
				'show_slider_dots' => 'on',
				'slider_arrow_color' => '#000',
				'animation_type' => 'slide'

			);

			$products = get_posts( 'post_type=product&posts_per_page=2' );
			$i = 0;

			$this->new_slide_defaults = array (
				'show_image' => 'on',
				'show_title' => 'on',
				'show_excerpt' => 'on',
				'show_price' => 'on',
				'show_buy_button' => 'on',
				'design' => array(
					'imagealign' => 'image-left',
					'imageratios' => NULL,
					'background' => array(
						'position' => 'center',
						'repeat' => 'no-repeat',
						'size' => 'cover'
					),
					'fonts' => array(
						'align' => 'text-left',
						'size' => 'large',
						'shadow' => ''
					)
				)
			);

			if( 0 < count( $products ) ) {
				foreach ( $products as $product ){
					$guid = rand( 0 , 1000 );
					$this->slide_defaults[$guid] = $this->new_slide_defaults;
					$this->slide_defaults[$guid][ 'product_id' ] = $product->ID;
					$this->default_slides[] = $guid;
					$this->defaults[ 'slides' ][ $guid ] = $this->slide_defaults[$guid];
				}

				$this->defaults[ 'slide_ids' ] = implode( ',' , $this->default_slides );
			}
		}

		/**
		* Enqueue Scripts
		*/
		function enqueue_scripts(){

			// Slider JS enqueue
			wp_enqueue_script(
				'layers-slider-js' ,
				get_template_directory_uri() . '/core/widgets/js/swiper.js',
				array( 'jquery' ),
				LAYERS_STOREKIT_VER
			); // Slider

			// Slider CSS enqueue
			wp_enqueue_style(
				'layers-slider' ,
				get_template_directory_uri() . '/core/widgets/css/swiper.css',
				array(),
				LAYERS_STOREKIT_VER
			); // Slider
		}

		/**
		*  Widget front end display
		*/
		function widget( $args, $instance ) {

			/**
			* First things first, lets make sure that WooCommerce is active
			*/
			if( !class_exists( 'WooCommerce' ) ) return;

			global $wp_customize, $product;

			// Backup Inline CSS
			$this->backup_inline_css();

			// Turn $args array into variables.
			extract( $args );

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$widget = wp_parse_args( $instance, $instance_defaults );

			// Enqueue Scipts when needed
			$this->enqueue_scripts();

			// Apply slider arrow color
			if( $this->check_and_return( $widget, 'slider_arrow_color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'color', array( 'selectors' => array( '.arrows a' ), 'color' => $this->check_and_return( $widget, 'slider_arrow_color' ) ) );
			if( $this->check_and_return( $widget, 'slider_arrow_color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'border', array( 'selectors' => array( 'span.swiper-pagination-switch' ), 'border' => array( 'color' => $this->check_and_return( $widget, 'slider_arrow_color' ) ) ) );
			if( $this->check_and_return( $widget, 'slider_arrow_color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'selectors' => array( 'span.swiper-pagination-switch' ), 'background' => array( 'color' => $this->check_and_return( $widget, 'slider_arrow_color' ) ) ) );
			if( $this->check_and_return( $widget, 'slider_arrow_color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'selectors' => array( 'span.swiper-pagination-switch.swiper-active-switch' ), 'background' => array( 'color' => 'transparent !important' ) ) );

			// Slider Class
			$slider_class = array();

			$slider_class[] = 'storekit-product-slider-widget';
			$slider_class[] = 'widget';
			$slider_class[] = 'row';
			$slider_class[] = 'slide';
			$slider_class[] = 'swiper-container';

			if( isset( $widget['design']['layout'] ) && '' != $widget['design']['layout'] ) {
				// Slider layout eg 'slider-layout-full-screen'
				$slider_class[] = 'slider-' . $widget['design']['layout'];
			}
			if( ! isset( $widget['design']['layout'] ) || ( isset( $widget['design']['layout'] ) && 'layout-full-screen' != $widget['design']['layout'] ) ) {
				// If slider is not full screen
				$slider_class[] = 'not-full-screen';
			}
			if( 1 == count( $widget[ 'slides' ] ) ) {
				// If only one slide
				$slider_class[] = 'single-slide';
			}

			$slider_class[] = $this->get_widget_layout_class( $widget );
			$slider_class[] = $this->check_and_return( $widget , 'design', 'advanced', 'customclass' );
			$slider_class[] = $this->get_widget_spacing_class( $widget );

			$slider_classes = implode( ' ', $slider_class );

			// Get slider height css
			$slider_height_css = '';
			if( 'layout-full-screen' != $this->check_and_return( $widget , 'design', 'layout' ) && FALSE == $this->check_and_return( $widget , 'autoheight_slides' ) && $this->check_and_return( $widget , 'slide_height' ) ) {
				$slider_height_css = 'height: ' . $widget['slide_height'] . 'px; ';
			} ?>
			<section class="<?php echo $slider_classes; ?>" id="<?php echo $widget_id; ?>" style="<?php echo esc_attr( $slider_height_css ); ?>" >
				<?php if( !empty( $widget[ 'slides' ] ) ) { ?>
					<?php if( 1 < count( $widget[ 'slides' ] ) && isset( $widget['show_slider_arrows'] ) ) { ?>
						 <div class="arrows">
							<a href="" class="l-left-arrow animate"></a>
							<a href="" class="l-right-arrow animate"></a>
						</div>
					<?php } ?>
					<div class="<?php echo $this->get_field_id( 'pages' ); ?> pages animate">
						<?php for( $i = 0; $i < count( $widget[ 'slides' ] ); $i++ ) { ?>
							<a href="" class="page animate <?php if( 0 == $i ) echo 'active'; ?>"></a>
						<?php } ?>
					</div>
					<div class="swiper-wrapper">
						<?php foreach ( explode( ',', $widget[ 'slide_ids' ] ) as $slide_key ) {

							// Make sure we've got a column going on here
							if( !isset( $widget[ 'slides' ][ $slide_key ] ) ) continue;

							// Setup the relevant slide
							$slide = $widget[ 'slides' ][ $slide_key ];

							// Set the background styling
							if( $this->check_and_return( $slide, 'design', 'background' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id . '-' . $slide_key , 'background', array( 'background' => $slide['design'][ 'background' ] ) );
							if( $this->check_and_return( $slide, 'design', 'fonts', 'color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id . '-' . $slide_key , 'color', array( 'selectors' => array( 'h3.heading', 'h3.heading a', 'div.excerpt', '.product.woocommerce', '.amount', 'del' ) , 'color' => $slide['design']['fonts'][ 'color' ] ) );
							if( $this->check_and_return( $slide, 'design', 'fonts', 'shadow' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id . '-' . $slide_key , 'text-shadow', array( 'selectors' => array( 'h3.heading', 'h3.heading a',  'div.excerpt' )  , 'text-shadow' => $slide['design']['fonts'][ 'shadow' ] ) );
							if( $this->check_and_return( $slide, 'design', 'buttons' ) ) layers_inline_button_styles( '#' . $widget_id . '-' . $slide_key , 'button', array( 'selectors' => array( '.section-title .button' ) ,'button' => $this->check_and_return( $slide, 'design', 'buttons' ) ) );

							// Set Image Sizes
							if( isset( $slide['design'][ 'imageratios' ] ) ){

									// Translate Image Ratio into something usable
									$image_ratio = layers_translate_image_ratios( $slide['design'][ 'imageratios' ] );
									$use_image_ratio = $image_ratio . '-medium';

							} else {
								$use_image_ratio = 'large';
							}

 							/**
							* Set Individual Slide CSS
							*/
							$widget_container_class = array();
							$widget_container_class[] = 'swiper-slide';
							$widget_container_class[] = $this->check_and_return( $slide , 'design',  'imagealign' );
							if( $this->check_and_return( $slide, 'design', 'background' , 'color' ) ) {
								if( 'dark' == layers_is_light_or_dark( $this->check_and_return( $slide, 'design', 'background' , 'color' ) ) ) {
									$widget_container_class[] = 'invert';
								}
							}
							if( isset( $slide['design']['fonts'][ 'align' ] ) && '' != $slide['design']['fonts'][ 'align' ] ) {
								$widget_container_class[] = $slide['design']['fonts'][ 'align' ];
							}
							$widget_container_class = implode( ' ', $widget_container_class );

							// Text Size
							$text_size = ( isset( $slide['design']['fonts'][ 'size' ] ) ) ? $slide['design']['fonts'][ 'size' ] : '' ;

							// Grab the product information
							if( isset( $slide[ 'product_id' ] ) ) {

								// Query the product
								$product = wc_get_product( $slide[ 'product_id' ] );

								// Check that this product is valid
								if( !is_object( $product ) ) continue;

								// Check that this post is an actual product
								if( 'product' !== get_post_type( $slide[ 'product_id' ] ) ) continue;

							} else{
								continue;
							} ?>

							<div class="<?php echo $widget_container_class; ?>" id="<?php echo $widget_id; ?>-<?php echo $slide_key; ?>" style="float: left;">
								<?php /**
								* Set Overlay CSS Classes
								*/
								$overlay_class = array();
								$overlay_class[] = 'overlay';
								if( isset( $slide['design'][ 'background' ][ 'darken' ] ) ) {
									$overlay_class[] = 'darken';
								}
								$overlay_class[] = 'content';
								$overlay_classes = implode( ' ', $overlay_class ); ?>

								<div class="<?php echo $overlay_classes ?>"  style="<?php echo esc_attr( $slider_height_css ); ?>" >
									<div class="container clearfix">
										<div class="copy-container">
											<div class="section-title <?php echo ( isset( $slide['design']['fonts'][ 'size' ] ) ? $slide['design']['fonts'][ 'size' ] : '' ); ?>">
												<?php if( $this->check_and_return( $slide , 'show_title' ) && '' != $product->post->post_title ) { ?>
													<h3 data-swiper-parallax="-100" class="heading">
														<a href="<?php echo get_permalink( $product->post->ID ); ?>">
															<?php echo $product->post->post_title; ?>
														</a>
													</h3>
												<?php } ?>
												<?php if( $this->check_and_return( $slide , 'show_excerpt' ) && '' != $product->post->post_excerpt ) { ?>
													<div data-swiper-parallax="-300" class="excerpt"><?php echo apply_filters( 'the_content', $product->post->post_excerpt ); ?></div>
												<?php } ?>
												<?php if( $this->check_and_return( $slide , 'show_price' ) ) { ?>
													<?php woocommerce_template_loop_price(); ?>
												<?php } ?>
												<?php if( $this->check_and_return( $slide, 'show_buy_button' ) ) { ?>
													<?php woocommerce_template_loop_add_to_cart(); ?>
												<?php } ?>
											</div>
										</div>
										<div class="image-container push-bottom-<?php echo $text_size ?>">
											<?php if( $this->check_and_return( $slide , 'show_image' ) ) {
												// Layers Featured Media
												echo layers_post_featured_media( array(
													'postid' => $product->post->ID,
													'wrap_class' => 'media-image-container ' . ( 'image-round' ==  $this->check_and_return( $slide, 'design',  'imageratios' ) ? 'image-rounded' : '' ),
													'size' => $use_image_ratio,
													'hide_href' => true
												) );
											}
											if( $this->check_and_return( $slide, 'show_sales_flash' ) ) {
												woocommerce_show_product_loop_sale_flash();
											} ?>
										</div>
									</div> <!-- .container -->
								</div> <!-- .overlay -->
							</div>
						<?php } // foreach slides ?>
					</div>
				<?php }
				// Print the Inline Styles for this Widget
				if( method_exists( $this, 'print_inline_css' ) )
					$this->print_inline_css();

				// Advanced Styling
				$this->apply_widget_advanced_styling( $widget_id, $widget );

				$swiper_js_obj = str_replace( '-' , '_' , $this->get_field_id( 'slider' ) );

				if( !empty( $widget[ 'slides' ] ) ) { ?>
					<script type="text/javacript">
						jQuery(function($){

							var <?php echo $swiper_js_obj; ?> = $('#<?php echo $widget_id; ?>').swiper({
								mode:'horizontal'
								,bulletClass: 'swiper-pagination-switch'
								,bulletActiveClass: 'swiper-active-switch swiper-visible-switch'
								,paginationClickable: true
								,watchActiveIndex: true
								<?php if( 'fade' ==  $this->check_and_return( $widget, 'animation_type' ) ) { ?>
									,effect: '<?php echo $widget['animation_type']; ?>'
									,noSwiping: true
								<?php } else if( 'parallax' ==  $this->check_and_return( $widget, 'animation_type' ) ) { ?>
									,speed: 700
									,parallax: true
								<?php } ?>
								<?php if( isset( $widget['show_slider_dots'] ) && ( !empty( $widget[ 'slides' ] ) && 1 < count( $widget[ 'slides' ] ) ) ) { ?>
									,pagination: '.<?php echo $this->get_field_id( 'pages' ); ?>'
								<?php } ?>
								<?php if( 1 < count( $widget[ 'slides' ] ) ) { ?>
									,loop: true
								<?php } else { ?>
									,loop: false
									,noSwiping: true
									,allowSwipeToPrev: false
									,allowSwipeToNext: false
								<?php } ?>
								<?php if( isset( $widget['autoplay_slides'] ) && isset( $widget['slide_time'] ) && is_numeric( $widget['slide_time'] ) ) {?>
									, autoplay: <?php echo ($widget['slide_time']*1000); ?>
								<?php }?>
								<?php if( isset( $wp_customize ) && $this->check_and_return( $widget, 'focus_slide' ) ) { ?>
									,initialSlide: <?php echo $this->check_and_return( $widget, 'focus_slide' ); ?>
								<?php } ?>
							});

							<?php if( 1 < count( $widget[ 'slides' ] ) ) { ?>
								// Allow keyboard control
								<?php echo $swiper_js_obj; ?>.enableKeyboardControl();
							<?php } // if > 1 slide ?>

							<?php if( TRUE == $this->check_and_return( $widget , 'autoheight_slides' ) ) { ?>
								layers_swiper_resize( <?php echo $swiper_js_obj; ?> );
								$(window).resize(function(){
									layers_swiper_resize( <?php echo $swiper_js_obj; ?> );
								});
							<?php } ?>

							$('#<?php echo $widget_id; ?>').find('.arrows a').on( 'click' , function(e){
								e.preventDefault();

								// "Hi Mom"
								$that = $(this);

								if( $that.hasClass( 'swiper-pagination-switch' ) ){
									// Anchors
									<?php echo $swiper_js_obj; ?>.slideTo( $that.index() );
								} else if( $that.hasClass( 'l-left-arrow' ) ){
									// Previous
									<?php echo $swiper_js_obj; ?>.slidePrev();
								} else if( $that.hasClass( 'l-right-arrow' ) ){
									// Next
									<?php echo $swiper_js_obj; ?>.slideNext();
								}

								return false;
							});

							<?php echo $swiper_js_obj; ?>.init();
						})
				 	</script>
				<?php } // if !empty( $widget->slides ) ?>
			</section>
		<?php
		}

		/**
		*  Widget update
		*/

		function update($new_instance, $old_instance) {

			if ( isset( $this->checkboxes ) ) {
				foreach( $this->checkboxes as $cb ) {
					if( isset( $old_instance[ $cb ] ) ) {
						$old_instance[ $cb ] = strip_tags( $new_instance[ $cb ] );
					}
				} // foreach checkboxes
			} // if checkboxes

			// Don't break the slider when
			if ( !isset( $new_instance['slides'] ) ) {
				$new_instance['slides'] = array();
			}

			return $new_instance;
		}

		/**
		*  Widget form
		*
		* We use regular HTML here, it makes reading the widget much easier than if we used just php to echo all the HTML out.
		*
		*/
		function form( $instance ){

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );
			extract( $instance, EXTR_SKIP );

			$design_bar_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_components' , array(
					'custom',
					'advanced'
				) );

			$design_bar_custom_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_custom_components' , array(
					'layout' => array(
						'icon-css' => 'icon-layout-fullwidth',
						'label' => __( 'Layout', 'layers-storekit' ),
						'wrapper-class' => 'layers-pop-menu-wrapper layers-small',
						'elements' => array(
							'layout' => array(
								'type' => 'select-icons',
								'label' => __( '' , 'layers-storekit' ),
								'name' => $this->get_field_name( 'design' ) . '[layout]' ,
								'id' => $this->get_field_id( 'design-layout' ) ,
								'value' => ( isset( $design['layout'] ) ) ? $design['layout'] : NULL,
								'options' => array(
									'layout-boxed' => __( 'Boxed' , 'layers-storekit' ),
									'layout-fullwidth' => __( 'Full Width' , 'layers-storekit' ),
									'layout-full-screen' => __( 'Full Screen' , 'layers-storekit' )
								)
							)
						)
					),
					'display' => array(
						'icon-css' => 'icon-slider',
						'label' => __( 'Slider', 'layers-storekit' ),
						'elements' => array(
								'autoheight_slides' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'autoheight_slides' ) ,
									'id' => $this->get_field_id( 'autoheight_slides' ) ,
									'value' => ( isset( $autoheight_slides ) ) ? $autoheight_slides : NULL,
									'label' => __( 'Auto Height Slides' , 'layers-storekit' ),
								),
								'slide_height' => array(
									'type' => 'number',
									'name' => $this->get_field_name( 'slide_height' ) ,
									'id' => $this->get_field_id( 'slide_height' ) ,
									'value' => ( isset( $slide_height ) ) ? $slide_height : NULL,
									'label' => __( 'Slider Height' , 'layers-storekit' ),
									'data' => array( 'show-if-selector' => '#' . $this->get_field_id( 'autoheight_slides' ), 'show-if-value' => 'false' ),
								),
								'show_slider_arrows' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_slider_arrows' ) ,
									'id' => $this->get_field_id( 'show_slider_arrows' ) ,
									'value' => ( isset( $show_slider_arrows ) ) ? $show_slider_arrows : NULL,
									'label' => __( 'Show Slider Arrows' , 'layers-storekit' )
								),
								'show_slider_dots' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_slider_dots' ) ,
									'id' => $this->get_field_id( 'show_slider_dots' ) ,
									'value' => ( isset( $show_slider_dots ) ) ? $show_slider_dots : NULL,
									'label' => __( 'Show Slider Dots' , 'layers-storekit' )
								),
								'slider_arrow_color' => array(
									'type' => 'color',
									'name' => $this->get_field_name( 'slider_arrow_color' ) ,
									'id' => $this->get_field_id( 'slider_arrow_color' ) ,
									'value' => ( isset( $slider_arrow_color ) ) ? $slider_arrow_color : NULL,
									'label' => __( 'Slider Controls Color' , 'layers-storekit' )
								),
								'animation_type' => array(
									'type' => 'select',
									'name' => $this->get_field_name( 'animation_type' ) ,
									'id' => $this->get_field_id( 'animation_type' ) ,
									'value' => ( isset(  $animation_type ) ) ?  $animation_type : 'slide',
									'label' => __( 'Animation Type' , 'layerswp' ),
									'options' => array(
										'slide' => __( 'Slide', 'layers_wp' ),
										'fade' => __( 'Fade', 'layers_wp' ),
										'parallax' => __( 'Parallax', 'layers_wp' ),
									)

								),
								'autoplay_slides' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'autoplay_slides' ) ,
									'id' => $this->get_field_id( 'autoplay_slides' ) ,
									'value' => ( isset( $autoplay_slides ) ) ? $autoplay_slides : NULL,
									'label' => __( 'Autoplay Slides' , 'layers-storekit' )
								),
								'slide_time' => array(
									'type' => 'number',
									'name' => $this->get_field_name( 'slide_time' ) ,
									'id' => $this->get_field_id( 'slide_time' ) ,
									'min' => 1,
									'max' => 100,
									'placeholder' => __( 'Time in seconds, eg. 2' , 'layers-storekit' ),
									'value' => ( isset( $slide_time ) ) ? $slide_time : NULL,
									'label' => __( 'Slide Interval' , 'layers-storekit' ),
									'data' => array( 'show-if-selector' => '#' . $this->get_field_id( 'autoplay_slides' ), 'show-if-value' => 'true' )
								)
							)
					)
				) );

			$this->design_bar(
				'side', // CSS Class Name
				array(
					'name' => $this->get_field_name( 'design' ),
					'id' => $this->get_field_id( 'design' ),
				), // Widget Object
				$instance, // Widget Values
				$design_bar_components, // Standard Components
				$design_bar_custom_components // Add-on Components
			); ?>
			<div class="layers-container-large layers-woocommerce-product-slider" id="layers-slide-widget-<?php echo esc_attr( $this->number ); ?>">

				<?php $this->form_elements()->header( array(
					'title' =>'Product Slider',
					'icon_class' =>'slider'
				) ); ?>

				<section class="layers-accordion-section layers-content">
					<p class="layers-form-item">
						<label><?php _e( 'Search for Products to Add' , 'layers-storekit' ); ?></label>
						<?php echo $this->form_elements()->input(
							array(
								'type' => 'hidden',
								'id' => $this->get_field_id( 'product_ids' ),
								'data' => array(
											'woocommerce-slide-product-ids' => $this->number,
											'placeholder' => __( 'eg. "Jersey"' , 'layers-storekit' ),
										),
							)
						);?>
					</p>
					<p class="layers-form-item layers-clearfix">
						<?php echo $this->form_elements()->input(
							array(
								'type' => 'button',
								'label' => __( 'Create Slide' , 'layers-storekit' ),
								'id' => $this->get_field_id( 'product_ids' ),
								'class' => 'layers-button btn-full btn-primary layers-woocommerce-add-product-widget-slide add-new-widget layers-hide',
								'data' => array(
											'input' => $this->get_field_id( 'product_ids' ),
											'number' => esc_attr( $this->number ),
											'label' => __( 'Create Slide' , 'layers-storekit' ),
											'plural-label' => __( 'Create Slides' , 'layers-storekit' ),
											'placeholder' => __( 'Search for Products to Add' , 'layers-storekit' ),
										),
							)
						);?>
					</p>

					<?php echo $this->form_elements()->input(
						array(
							'type' => 'hidden',
							'name' => $this->get_field_name( 'slide_ids' ) ,
							'id' => $this->get_field_id( 'slide_ids' ) ,
							'value' => ( isset( $slide_ids ) ) ? $slide_ids : NULL,
							'data' => array( 'woocommerce-slide-input' => $this->number )
						)
					);

					echo $this->form_elements()->input(
						array(
							'type' => 'hidden',
							'name' => $this->get_field_name( 'focus_slide' ) ,
							'id' => $this->get_field_name( 'focus_slide' ) ,
							'value' => ( isset( $focus_slide ) ) ? $focus_slide : NULL,
							'data' => array(
								'focus-slide' => 'true'
							)
						)
					); ?>

					<?php // If we have some slides, let's break out their IDs into an array
					if( isset( $slide_ids ) && '' != $slide_ids ) $slides = explode( ',' , $slide_ids ); ?>

					<ul data-woocommerce-slide-list="<?php echo esc_attr( $this->number ); ?>" class="layers-accordions layers-product-slider-accordian layers-accordions-sortable layers-sortable" data-id_base="<?php echo $this->id_base; ?>" data-number="<?php echo esc_attr( $this->number ); ?>">
						<?php if( isset( $slides ) && is_array( $slides ) ) { ?>
							<?php foreach( $slides as $slide ) {
								$this->slide_item( array(
											'id_base' => $this->id_base ,
											'number' => $this->number
										) ,
										$slide ,
										( isset( $instance[ 'slides' ][ $slide ] ) ) ? $instance[ 'slides' ][ $slide ] : NULL );
							} ?>
						<?php } ?>
					</ul>
				</section>

			</div>

		<?php } // Form

		function get_products(){
			$products = new WP_Query(array( 'post_type' => 'product' , 'posts_per_page' => 5 ) );

			$product_options = array();
			while( $products->have_posts() ) {
				global $post;
				$products->the_post();
				$product_options[] = $post->ID;
			}

			return $product_options;
		}

		function slide_item( $widget_details = array() , $slide_guid = NULL , $instance = NULL ){

			// $instance Defaults
			if( isset( $this->slide_defaults[$slide_guid] ) ) {
				$instance_defaults = $this->slide_defaults[$slide_guid];
			} else {
				$instance_defaults = $this->new_slide_defaults;
			}


			// Clear the defaults if they're not needed
			if( !empty( $instance ) ) $instance_defaults = array();

			$instance = wp_parse_args( $instance, $instance_defaults );
			extract( $instance, EXTR_SKIP );

			// If there is no GUID create one. There should always be one but this is a fallback
			if( ! isset( $slide_guid ) ) $slide_guid = rand( 1 , 1000 );

			// Get the details of the product we're querying
			if( isset( $product_id ) ) $product = get_post( $product_id );

			if( !is_object( $product ) ) return;

			if( 'product' !== get_post_type( $product_id ) && 'product_variation' !== get_post_type( $product_id ) ) return;

			// Turn the widget details into an object, it makes the code cleaner
			$widget_details = (object) $widget_details;

			// Set a count for each row
			if( !isset( $this->slide_item_count ) ) {
				$this->slide_item_count = 0;
			} else {
				$this->slide_item_count++;
			}?>
				<li class="layers-accordion-item <?php echo $this->slide_item_count; ?>" data-guid="<?php echo $slide_guid; ?>" data-product-id="<?php echo $product->ID; ?>">
					<a class="layers-accordion-title">
						<span>
							<?php _e( 'Slide' , 'layers-storekit' ); ?>
							<span class="layers-detail">
								<?php echo ( isset( $product ) ? ': ' . substr( stripslashes( strip_tags( $product->post_title ) ), 0 , 50 ) : NULL ); ?>
								<?php echo ( isset( $product ) && strlen( $product->post_title ) > 50 ? '...' : NULL ); ?>
							</span>
						</span>
					</a>
					<section class="layers-accordion-section layers-content">
						<?php $this->design_bar(
							'top', // CSS Class Name
							array(
								'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'design' ),
								'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'design' ),
								'number' => $widget_details->number,
								'show_trash' => true
							), // Widget Object
							$instance, // Widget Values
							array(
								'background',
								'imageratios',
								'imagealign' => array(
									'elements' => array(
										'imagealign' => array(
											'options' => array(
												'image-left' => __( 'Left', 'layerswp' ),
												'image-right' => __( 'Right', 'layerswp' ),
												'image-top' => __( 'Top', 'layerswp' ),
												'image-bottom' => __( 'Bottom', 'layerswp' ),
											),
										),
									),
								),
								'fonts',
								'custom',
								'buttons' => array(
										'elements' => array(
											'buttons-background-color' => array(
											'type' => 'color',
											'label' => __( 'Background Color', 'layerswp' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'design' ) .'[buttons][background-color]' ,
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'design' ) .'-buttons-background-color' ,
											'value' => ( isset( $design['buttons']['background-color'] ) ) ? $design['buttons']['background-color'] : NULL
										)
									),
									'elements_combine' => 'replace',
								),
							), // Standard Components
							array(
								array(
									'label' => __( 'Display' , 'layers-storekit' ),
									'icon-css' => 'icon-display',
									'elements' => array(
										array(
											'type' => 'checkbox',
											'label' => __( 'Show Featured Image' , 'layers-storekit' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_image' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_image' ),
											'value' => ( isset( $show_image ) ) ? $show_image : NULL
										),
										array(
											'type' => 'checkbox',
											'label' => __( 'Show Title' , 'layers-storekit' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_title' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_title' ),
											'value' => ( isset( $show_title ) ) ? $show_title : NULL
										),
										array(
											'type' => 'checkbox',
											'label' => __( 'Show Short Description' , 'layers-storekit' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_excerpt' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_excerpt' ),
											'value' => ( isset( $show_excerpt ) ) ? $show_excerpt : NULL
										),
										array(
											'type' => 'checkbox',
											'label' => __( 'Show Price' , 'layers-storekit' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_price' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_price' ),
											'value' => ( isset( $show_price ) ) ? $show_price : NULL
										),
										array(
											'type' => 'checkbox',
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_sales_flash' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_sales_flash' ),
											'value' => ( isset( $show_sales_flash ) ) ? $show_sales_flash : NULL,
											'label' => __( 'Show Sales Badge' , 'layers-storekit' )
										),
										array(
											'type' => 'checkbox',
											'label' => __( 'Show Button' , 'layers-storekit' ),
											'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'show_buy_button' ),
											'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'show_buy_button' ),
											'value' => ( isset( $show_buy_button ) ) ? $show_buy_button : NULL
										),
									)
								)
							)
						);

						echo $this->form_elements()->input(
							array(
								'type' => 'hidden',
								'name' => $this->get_custom_field_name( $widget_details, 'slides',  $slide_guid, 'product_id' ),
								'id' => $this->get_custom_field_id( $widget_details, 'slides',  $slide_guid, 'product_id' ),
								'value' => ( isset( $product_id ) ) ? $product_id : NULL
							)
						); ?>
					</section>
				</li>
		<?php }

	} // Class

	// Add our function to the widgets_init hook.
	 register_widget("Layers_WooCommerce_Slider_Widget");
}