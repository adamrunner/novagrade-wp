<?php  /**
 * WooCommerce Product Widget
 *
 * This file is used to register and display the Layers - WooCommerce Product widget.
 *
 * @package Layers
 * @since Layers 1.0
 */
if( !class_exists( 'Layers_WooCommerce_Product_Widget' ) && class_exists( 'Layers_Widget' ) ) {
	class Layers_WooCommerce_Product_Widget extends Layers_Widget {

		/**
		* Widget variables
		*
		* @param    varchar         $widget_title       Widget title
		* @param    varchar         $widget_id          Widget slug for use as an ID/classname
		* @param    varchar         $post_type          (optional) WooCommerce Product type for use in widget options
		* @param    varchar         $taxonomy           (optional) Taxonomy slug for use as an ID/classname
		* @param    array           $checkboxes     (optional) Array of checkbox names to be saved in this widget. Don't forget these please!
		*/
		private $widget_title = 'Product List';
		private $widget_id = 'product';
		private $post_type = 'product';
		private $taxonomy = 'product_cat';
		public $checkboxes = array(
				'show_titles',
				'show_excerpts',
				'show_prices',
				'show_star_rating',
				'show_categories',
				'show_call_to_action'
			); // @TODO: Try make this more dynamic, or leave a different note reminding users to change this if they add/remove checkboxes
		/**
		*  Widget construction
		*/
		function Layers_WooCommerce_Product_Widget(){

			/* Widget settings. */
			$widget_ops = array( 'classname' => 'obox-layers-' . $this->widget_id .'-widget', 'description' => 'This widget is used to list your WooCommerce Products in Layers.' );

			/* Widget control settings. */
			$control_ops = array( 'width' => LAYERS_WIDGET_WIDTH_SMALL, 'height' => NULL, 'id_base' => LAYERS_THEME_SLUG . '-widget-' . $this->widget_id );

			/* Create the widget. */
			parent::__construct( LAYERS_THEME_SLUG . '-widget-' . $this->widget_id , $this->widget_title , $widget_ops, $control_ops );

			/* Setup Widget Defaults */
			$this->defaults = array (
				'title' => 'Latest WooCommerce Products',
				'excerpt' => 'Check out our incredible collection of products!',
				'text_style' => 'regular',
				'category' => 0,
				'show_images' => 'on',
				'show_titles' => 'on',
				'show_prices' => 'on',
				'show_excerpts' => 'on',
				'show_author' => 'on',
				'show_tags' => 'on',
				'show_categories' => 'on',
				'show_sales_flash' => 'on',
				'show_ratings' => 'on',
				'excerpt_length' => 200,
				'show_call_to_action' => 'on',
				'posts_per_page' => 6,
				'order' => NULL,
				'design' => array(
					'layout' => 'layout-boxed',
					'imageratios' => 'image-square',
					'textalign' => 'text-left',
					'liststyle' => 'list-grid',
					'columns' => '3',
					'gutter' => 'on',
					'background' => array(
						'position' => 'center',
						'repeat' => 'no-repeat'
					),
					'fonts' => array(
						'align' => 'text-left',
						'size' => 'medium',
						'color' => NULL,
						'shadow' => NULL
					)
				)
			);
		}

		/**
		*  Widget front end display
		*/
		function widget( $args, $instance ) {

			/**
			* First things first, lets make sure that WooCommerce is active
			*/
			if( !class_exists( 'WooCommerce' ) ) return;

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

			// Enqueue Masonry if need be
			if( 'list-masonry' == $this->check_and_return( $widget , 'design', 'liststyle' ) ) $this->enqueue_masonry();

			// Set the span class for each column
			if( 'list-list' == $widget['design'][ 'liststyle' ] ) {
				$col_count = 1;
				$span_class = 'span-12';
			} else {
				$col_count = str_ireplace('columns-', '', $widget['design'][ 'columns']  );
				$span_class = 'span-' . ( 12/ $col_count );
			}

			// Set the background & font styling
			if( $this->check_and_return( $widget, 'design', 'background' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'background' => $widget['design'][ 'background' ] ) );
			if( $this->check_and_return( $widget, 'design', 'fonts', 'color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'color', array( 'selectors' => array( '.section-title h3.heading' , '.section-title p.excerpt' ) , 'color' => $widget['design']['fonts'][ 'color' ] ) );
			if( $this->check_and_return( $widget, 'design', 'column-background-color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'selectors' => array( '.thumbnail:not(.with-overlay) .thumbnail-body' ) , 'background' => array( 'color' => $this->check_and_return( $widget, 'design', 'column-background-color' ) ) ) );
			if( $this->check_and_return( $widget, 'design', 'buttons' ) ) layers_inline_button_styles( '#' . $widget_id, 'button', array( 'selectors' => array( '.thumbnail-body a.button' ) ,'button' => $this->check_and_return( $widget, 'design', 'buttons' ) ) );

			// Set Image Sizes
			if( isset( $widget['design'][ 'imageratios' ] ) ){

				// Translate Image Ratio
				$image_ratio = layers_translate_image_ratios( $widget['design'][ 'imageratios' ] );

				if( 'layout-boxed' == $this->check_and_return( $widget , 'design', 'layout' ) && $col_count > 2 ){
					$imageratios = $image_ratio . '-medium';
				} elseif( 'layout-boxed' != $this->check_and_return( $widget , 'design', 'layout' ) && $col_count > 3 ){
					$imageratios = $image_ratio . '-large';
				} else {
					$imageratios = $image_ratio . '-large';
				}
			} else {
				$imageratios = 'large';
			}

			// Begin query arguments
			$query_args = array();

			if( get_query_var('paged') ) {
				$query_args[ 'paged' ] = get_query_var('paged') ;
			} else if ( get_query_var('page') ) {
				$query_args[ 'paged' ] = get_query_var('page');
			} else {
				$query_args[ 'paged' ] = 1;
			}

			$query_args[ 'post_type' ] = $this->post_type;
			$query_args[ 'posts_per_page' ] = $widget['posts_per_page'];
			if( isset( $widget['order'] ) ) {
				$decode_order = json_decode( $widget['order'] );
				foreach( $decode_order as $key => $value ){
					$query_args[ $key ] = $value;
				}
			}

			// Do the special taxonomy array()
			if( 'on-sale' == $this->check_and_return( $widget, 'filter_by' ) ){
				$product_ids_on_sale    = wc_get_product_ids_on_sale();
				$product_ids_on_sale[]  = 0;
				$query_args['post__in'] = $product_ids_on_sale;
			} else if( 'featured' == $this->check_and_return( $widget, 'filter_by' ) ){
				$query_args['meta_query'][] = array(
					'key'   => '_featured',
					'value' => 'yes'
				);
			} else {
				if( isset( $widget['category'] ) && '' != $widget['category'] && 0 != $widget['category'] ){
					$query_args['tax_query'] = array(
						array(
							"taxonomy" => $this->taxonomy,
							"field" => "id",
							"terms" => $widget['category']
						)
					);
				} elseif( !isset( $widget['hide_category_filter'] ) ) {
					$terms = get_terms( $this->taxonomy );
				} // if we haven't selected which category to show, let's load the $terms for use in the filter
			}

			// Do the WP_Query
			$post_query = new WP_Query( $query_args );


			// Set the meta to display
			global $post_meta_to_display;
			$post_meta_to_display = array();
			if( isset( $widget['show_dates'] ) ) $post_meta_to_display[] = 'date';
			if( isset( $widget['show_author'] ) ) $post_meta_to_display[] = 'author';
			if( isset( $widget['show_categories'] ) ) $post_meta_to_display[] = 'categories';
			if( isset( $widget['show_tags'] ) ) $post_meta_to_display[] = 'tags';

			/**
			* Generate the widget container class
			*/
			$widget_container_class = array();
			$widget_container_class[] = 'storekit-product-list-widget';
			$widget_container_class[] = 'widget';
			$widget_container_class[] = 'row';
			$widget_container_class[] = 'content-vertical-massive';
			$widget_container_class[] = $this->check_and_return( $widget , 'design', 'advanced', 'customclass' );
			$widget_container_class[] = $this->get_widget_spacing_class( $widget );
			$widget_container_class = implode( ' ', apply_filters( 'layers_woocommerce_product_widget_container_class' , $widget_container_class ) ); ?>

			<section class="<?php echo $widget_container_class; ?>" id="<?php echo $widget_id; ?>">
				<?php if( $this->check_and_return( $widget , 'title' ) || $this->check_and_return( $widget , 'excerpt' ) ) { ?>
					<div class="container clearfix">
						<?php /**
						* Generate the Section Title Classes
						*/
						$section_title_class = array();
						$section_title_class[] = 'section-title clearfix';
						$section_title_class[] = $this->check_and_return( $widget , 'design', 'fonts', 'size' );
						$section_title_class[] = $this->check_and_return( $widget , 'design', 'fonts', 'align' );
						$section_title_class[] = ( $this->check_and_return( $widget, 'design', 'background' , 'color' ) && 'dark' == layers_is_light_or_dark( $this->check_and_return( $widget, 'design', 'background' , 'color' ) ) ? 'invert' : '' );
						$section_title_class = implode( ' ', $section_title_class ); ?>
						<div class="<?php echo $section_title_class; ?>">
							<?php if( '' != $widget['title'] ) { ?>
								<h3 class="heading"><?php echo $widget['title']; ?></h3>
							<?php } ?>
							<?php if( '' != $widget['excerpt'] ) { ?>
								<div class="excerpt"><?php echo $widget['excerpt']; ?></div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
				<div class="row products <?php if( 'layout-boxed' == $this->check_and_return( $widget , 'design','layout' ) ) echo 'container'; ?> <?php echo $this->check_and_return( $widget , 'design', 'liststyle' ); ?>">
					<?php if( $post_query->have_posts() ) { ?>
						<?php while( $post_query->have_posts() ) {
							$post_query->the_post();
							global $post, $product;

							/**
							* Set Individual Column CSS
							*/
							$post_column_class = array();
							$post_column_class[] = 'layers-masonry-column thumbnail';
							$post_column_class[] = ( 'list-masonry' == $this->check_and_return( $widget, 'design', 'liststyle' ) ? 'no-gutter' : '' );
							$post_column_class[] = 'column' . ( 'on' != $this->check_and_return( $widget, 'design', 'gutter' ) ? '-flush' : '' );
							$post_column_class[] = $span_class;
							$post_column_class[] = ( 'overlay' == $this->check_and_return( $widget , 'text_style' ) ? 'with-overlay' : ''  ) ;
							$post_column_class[] = ( '' != $this->check_and_return( $widget, 'design', 'column-background-color' ) && 'dark' == layers_is_light_or_dark( $this->check_and_return( $widget, 'design', 'column-background-color' ) ) ? 'invert' : '' );
							$post_column_class = implode( ' ' , $post_column_class ); ?>

							<article class="<?php echo $post_column_class; ?>" data-cols="<?php echo $col_count; ?>">
								<?php if( $this->check_and_return( $widget, 'show_images' ) ) { ?>
									<div class="thumbnail-media <?php echo ( 'image-round' == $this->check_and_return( $widget, 'design', 'imageratios' ) ? ' image-rounded' : '' ); ?>">
										<a href="<?php the_permalink(); ?>">
											<?php echo woocommerce_get_product_thumbnail( $imageratios ); ?>
										</a>
										<?php if( $this->check_and_return( $widget, 'show_sales_flash' ) ) {
											woocommerce_show_product_loop_sale_flash();
										}
										if( $this->check_and_return( $widget, 'show_ratings' ) ) {
											woocommerce_template_loop_rating();
										} ?>
									</div>
								<?php } ?>
								<?php if( isset( $widget['show_titles'] ) || isset( $widget['show_excerpts'] ) || isset( $widget['show_prices'] ) ||  isset( $widget['show_call_to_action'] ) ) { ?>
									<div class="thumbnail-body">
										<div class="overlay">
											<?php if( isset( $widget['show_titles'] ) || isset( $widget['show_prices'] ) ) { ?>
												<header class="article-title">
													<?php if( isset( $widget['show_titles'] ) ) {  ?>
														<h4 class="heading"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
													<?php } ?>
													<?php if( isset( $widget['show_prices'] ) ) { ?>
														<?php woocommerce_template_loop_price(); ?>
													<?php } ?>
												</header>
											<?php } ?>
											<?php if( isset( $widget['show_excerpts'] ) ) {
												if( isset( $widget['excerpt_length'] ) && '' == $widget['excerpt_length'] ) {
													echo '<div class="excerpt">';
														the_content();
													echo '</div>';
												} else if( isset( $widget['excerpt_length'] ) && 0 != $widget['excerpt_length'] && strlen( get_the_excerpt() ) > $widget['excerpt_length'] ){
													echo '<p class="excerpt">' . substr( get_the_excerpt() , 0 , $widget['excerpt_length'] ) . '&#8230;</p>';
												} else if( '' != get_the_excerpt() ){
													echo '<p class="excerpt">' . get_the_excerpt() . '</p>';
												}
											}; ?>
											<?php if( ! ( isset( $widget['text_style'] ) && 'overlay' == $widget['text_style'] ) ) { ?>
												<?php if( 'post' == get_post_type() && !empty( $post_meta_to_display ) ) layers_post_meta( $post->ID, $post_meta_to_display );?>
											<?php } // Don't show meta if we have chosen overlay ?>
											<?php if( isset( $widget['show_call_to_action'] ) ) { ?>
												<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
											<?php } // show call to action ?>
										</div>
									</div>
								<?php } // if show titles || show excerpt ?>
							</article>
						<?php }; // while have_posts ?>
					<?php }; // if have_posts ?>
				</div>
				<?php if( isset( $widget['show_pagination'] ) ) { ?>
					<div class="row container">
						<?php layers_pagination( array( 'query' => $post_query ), 'div', 'pagination row span-12 text-center' ); ?>
					</div>
				<?php }

				if( 'list-masonry' == $this->check_and_return( $widget , 'design', 'liststyle' ) ) { ?>
					<script type="text/javacript">
						jQuery(function($){
							layers_masonry_settings[ '<?php echo $widget_id; ?>' ] = [{
									itemSelector: '.layers-masonry-column',
									gutter: <?php echo ( isset( $widget['design'][ 'gutter' ] ) ? 20 : 0 ); ?>
								}];

							$('#<?php echo $widget_id; ?>').find('.list-masonry').layers_masonry( layers_masonry_settings[ '<?php echo $widget_id; ?>' ][0] );
						});
					</script>
				<?php }

				// Print the Inline Styles for this Widget
				if( method_exists( $this, 'print_inline_css' ) )
					$this->print_inline_css();

				// Advanced Styling
				$this->apply_widget_advanced_styling( $widget_id, $widget ); ?>
			</section>

			<?php // Reset WP_Query
 			wp_reset_postdata();
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
			return $new_instance;
		}

		/**
		*  Widget form
		*
		* We use regulage HTML here, it makes reading the widget much easier than if we used just php to echo all the HTML out.
		*
		*/
		function form( $instance ){

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );

			extract( $instance, EXTR_SKIP ); ?>

			<!-- Form HTML Here -->
			<?php $this->design_bar(
				'side', // CSS Class Name
				array(
					'name' => $this->get_field_name( 'design' ),
					'id' => $this->get_field_id( 'design' ),
				), // Widget Object
				$instance, // Widget Values
				array(
					'layout',
					'fonts',
					'custom',
					'columns',
					'buttons' => array(
							'elements' => array(
								'buttons-background-color' => array(
								'type' => 'color',
								'label' => __( 'Background Color', 'layerswp' ),
								'name' => $this->get_layers_field_name( 'design', 'buttons', 'background-color' ),
								'id' => $this->get_layers_field_id( 'design', 'buttons', 'background-color' ),
								'value' => ( isset( $widget['design']['buttons']['background-color'] ) ) ? $this->values['design']['buttons']['background-color'] : NULL
							)
						),
						'elements_combine' => 'replace',
					),
					'imageratios',
					'background',
					'advanced'
				), // Standard Components
				array(
					'liststyle' => array(
						'icon-css' => ( isset( $design['liststyle'] ) && NULL != $design['liststyle'] ? 'icon-' . $design['liststyle'] : 'icon-list-masonry' ),
						'label' => __( 'List Style', 'layers-storekit' ),
						'wrapper-class' => 'layers-pop-menu-wrapper layers-small',
						'elements' => array(
								'liststyle' => array(
									'type' => 'select-icons',
									'name' => $this->get_custom_field_name( $this, 'design', 'liststyle' ),
									'id' => $this->get_custom_field_id( $this, 'design', 'liststyle' ),
									'value' => ( isset( $design['liststyle'] ) ) ? $design['liststyle'] : NULL,
									'options' => array(
									'list-grid' => __( 'Grid', 'layers-storekit' ),
									'list-masonry' => __( 'Masonry', 'layers-storekit' )
									)
								),
							)
					),
					'display' => array(
						'icon-css' => 'icon-display',
						'label' => 'Display',
						'elements' => array(
								'text_style' => array(
									'type' => 'select',
									'name' => $this->get_field_name( 'text_style' ) ,
									'id' => $this->get_field_id( 'text_style' ) ,
									'value' => ( isset( $text_style ) ) ? $text_style : NULL,
									'label' => __( 'Title &amp; Excerpt Position' , 'layers-storekit' ),
									'options' => array(
											'regular' => __( 'Regular' , 'layers-storekit' ),
											'overlay' => __( 'Overlay' , 'layers-storekit' )
									)
								),
								'show_images' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_images' ) ,
									'id' => $this->get_field_id( 'show_images' ) ,
									'value' => ( isset( $show_images ) ) ? $show_images : NULL,
									'label' => __( 'Show Feature Images' , 'layers-storekit' )
								),
								'show_titles' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_titles' ) ,
									'id' => $this->get_field_id( 'show_titles' ) ,
									'value' => ( isset( $show_titles ) ) ? $show_titles : NULL,
									'label' => __( 'Show Titles' , 'layers-storekit' )
								),
								'show_excerpts' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_excerpts' ) ,
									'id' => $this->get_field_id( 'show_excerpts' ) ,
									'value' => ( isset( $show_excerpts ) ) ? $show_excerpts : NULL,
									'label' => __( 'Show Short Description' , 'layers-storekit' )
								),
								'excerpt_length' => array(
									'type' => 'number',
									'name' => $this->get_field_name( 'excerpt_length' ) ,
									'id' => $this->get_field_id( 'excerpt_length' ) ,
									'min' => 0,
									'max' => 10000,
									'value' => ( isset( $excerpt_length ) ) ? $excerpt_length : NULL,
									'label' => __( 'Short Description Length' , 'layers-storekit' ),
									'data' => array( 'show-if-selector' => '#' . $this->get_field_id( 'show_excerpts' ), 'show-if-value' => 'true' )
								),
								'show_prices' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_prices' ) ,
									'id' => $this->get_field_id( 'show_prices' ) ,
									'value' => ( isset( $show_prices ) ) ? $show_prices : NULL,
									'label' => __( 'Show Prices' , 'layers-storekit' )
								),
								'show_sales_flash' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_sales_flash' ) ,
									'id' => $this->get_field_id( 'show_sales_flash' ) ,
									'value' => ( isset( $show_sales_flash ) ) ? $show_sales_flash : NULL,
									'label' => __( 'Show Sales Badges' , 'layers-storekit' )
								),
								'show_ratings' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_ratings' ) ,
									'id' => $this->get_field_id( 'show_ratings' ) ,
									'value' => ( isset( $show_ratings ) ) ? $show_ratings : NULL,
									'label' => __( 'Show Ratings' , 'layers-storekit' )
								),
								'show_call_to_action' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_call_to_action' ) ,
									'id' => $this->get_field_id( 'show_call_to_action' ) ,
									'value' => ( isset( $show_call_to_action ) ) ? $show_call_to_action : NULL,
									'label' => __( 'Show Buttons' , 'layers-storekit' )
								),
								'show_pagination' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_pagination' ) ,
									'id' => $this->get_field_id( 'show_pagination' ) ,
									'value' => ( isset( $show_pagination ) ) ? $show_pagination : NULL,
									'label' => __( 'Show Pagination' , 'layers-storekit' )
								),
							)
					)
				)
			); ?>
			<!-- Form HTML Here -->

			<div class="layers-container-large">

				<?php $this->form_elements()->header( array(
					'title' =>  __( 'WooCommerce Product' , 'layers-storekit' ),
					'icon_class' =>'post'
				) ); ?>

				<section class="layers-accordion-section layers-content">

					<div class="layers-row layers-push-bottom">
						<p class="layers-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'text',
									'name' => $this->get_field_name( 'title' ) ,
									'id' => $this->get_field_id( 'title' ) ,
									'placeholder' => __( 'Enter title here', 'layers-storekit' ),
									'value' => ( isset( $title ) ) ? $title : NULL ,
									'class' => 'layers-text layers-large'
								)
							); ?>
						</p>

						<p class="layers-form-item">
							<?php echo $this->form_elements()->input(
								array(
									'type' => ( 'rte' == sorekit_layers_form_type_exists( 'rte' ) ) ? 'rte' : 'textarea',
									'name' => $this->get_field_name( 'excerpt' ) ,
									'id' => $this->get_field_id( 'excerpt' ) ,
									'placeholder' => __( 'Short Excerpt', 'layers-storekit' ),
									'value' => ( isset( $excerpt ) ) ? $excerpt : NULL ,
									'class' => 'layers-textarea layers-large'
								)
							); ?>
						</p>
						<p class="layers-form-item">
							<label for="<?php echo $this->get_field_id( 'filter_by' ); ?>"><?php echo __( 'Filter By' , 'layers-storekit' ); ?></label>
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'select',
									'name' => $this->get_field_name( 'filter_by' ) ,
									'id' => $this->get_field_id( 'filter_by' ) ,
									'value' => ( isset( $category ) ) ? $category : NULL ,
									'options' => array(
										'' => __( 'Category', 'layerswp' ),
										'on-sale' => __( 'Items On Sale', 'layers-storekit' ),
										'featured' => __( 'Featured Items', 'layers-storekit' ),
									)
								)
							); ?>
						</p>
						<?php // Grab the terms as an array and loop 'em to generate the $options for the input
						$terms = get_terms( $this->taxonomy );
						if( !is_wp_error( $terms ) ) { ?>
							<p class="layers-form-item"  data-show-if-selector="#<?php echo $this->get_field_id( 'filter_by' ); ?>" data-show-if-value="">
								<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php echo __( 'Category to Display' , 'layers-storekit' ); ?></label>
								<?php $category_options[ 0 ] ="All";
								foreach ( $terms as $t ) $category_options[ $t->term_id ] = $t->name;
								echo $this->form_elements()->input(
									array(
										'type' => 'select',
										'name' => $this->get_field_name( 'category' ) ,
										'id' => $this->get_field_id( 'category' ) ,
										'placeholder' => __( 'Select a Category' , 'layers-storekit' ),
										'value' => ( isset( $category ) ) ? $category : NULL ,
										'options' => $category_options
									)
								); ?>
							</p>
						<?php } // if !is_wp_error ?>
						<p class="layers-form-item">
							<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php echo __( 'Number of products to show' , 'layers-storekit' ); ?></label>
							<?php $select_options[ '-1' ] = __( 'Show All' , 'layers-storekit' );
							$select_options = $this->form_elements()->get_incremental_options( $select_options , 1 , 20 , 1);
							echo $this->form_elements()->input(
								array(
									'type' => 'number',
									'name' => $this->get_field_name( 'posts_per_page' ) ,
									'id' => $this->get_field_id( 'posts_per_page' ) ,
									'value' => ( isset( $posts_per_page ) ) ? $posts_per_page : NULL ,
									'min' => '-1',
									'max' => '100'
								)
							); ?>
						</p>

						<p class="layers-form-item">
							<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php echo __( 'Sort by' , 'layers-storekit' ); ?></label>
							<?php echo $this->form_elements()->input(
								array(
									'type' => 'select',
									'name' => $this->get_field_name( 'order' ) ,
									'id' => $this->get_field_id( 'order' ) ,
									'value' => ( isset( $order ) ) ? $order : NULL ,
									'options' => $this->form_elements()->get_sort_options()
								)
							); ?>
						</p>
					</div>
				</section>

			</div>
		<?php } // Form
	} // Class

	// Add our function to the widgets_init hook.
	 register_widget("Layers_WooCommerce_Product_Widget");
}