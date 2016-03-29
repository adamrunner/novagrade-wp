<?php  /**
 * Content Widget
 *
 * This file is used to register and display the Layers - Content widget.
 *
 * @package Layers
 * @since Layers 1.0.0
 */
if( !class_exists( 'Layers_WooCommerce_Category_Widget' ) && class_exists( 'Layers_Widget' ) ) {
	class Layers_WooCommerce_Category_Widget extends Layers_Widget {

		/**
		*  Widget construction
		*/
		function Layers_WooCommerce_Category_Widget(){

			/**
			* Widget variables
			*
			* @param    string          $widget_title       Widget title
			* @param    string          $widget_id          Widget slug for use as an ID/classname
			* @param    string          $post_type          (optional) Post type for use in widget options
			* @param    string          $taxonomy           (optional) Taxonomy slug for use as an ID/classname
			* @param    array           $checkboxes     (optional) Array of checkbox names to be saved in this widget. Don't forget these please!
			*/
			$this->widget_title = __( 'Product Categories' , 'layers-storekit' );
			$this->widget_id = 'product-categories';
			$this->post_type = '';
			$this->taxonomy = 'product_cat';
			$this->checkboxes = array();

			/* Widget settings. */
			$widget_ops = array( 'classname' => 'obox-layers-' . $this->widget_id .'-widget', 'description' => __( 'This widget is used to display your WooCommerce Product Categories in Layers.') );

			/* Widget control settings. */
			$control_ops = array( 'width' => LAYERS_WIDGET_WIDTH_LARGE, 'height' => NULL, 'id_base' => LAYERS_THEME_SLUG . '-widget-' . $this->widget_id );

			/* Create the widget. */
			parent::__construct( 'layers-widget-' . $this->widget_id , $this->widget_title, $widget_ops, $control_ops );

			/* Setup Widget Defaults */
			$this->defaults = array (
				'title' => __( 'Product Categories', 'layers-storekit' ),
				'show_media' => 'on',
				'show_titles' => 'on',
				'show_excerpts' => 'on',
				'excerpt_length' => 200,
				'show_call_to_action' => 'on',
				'call_to_action' => __( 'View Category', 'layers-storekit' ),
				'show_call_to_action' => 'on',
				'call_to_action' => __( 'View Category' , 'layers-storekit' ),
				'design' => array(
					'layout' => 'layout-boxed',
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
				),
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

			// Set defaults
			$this->set_category_defaults( $instance );

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$widget = wp_parse_args( $instance, $instance_defaults );

			// Enqueue Masonry if need be
			if( 'list-masonry' == $this->check_and_return( $widget , 'design', 'liststyle' ) ) $this->enqueue_masonry();

			// Set the span class for each column
			if( isset( $widget['design'][ 'columns']  ) ) {
				$col_count = str_ireplace('columns-', '', $widget['design'][ 'columns']  );
				$span_class = 'span-' . ( 12/ $col_count );
			} else {
				$col_count = 3;
				$span_class = 'span-4';
			}

			// Set Image Sizes
			if( isset( $widget['design'][ 'imageratios' ] ) ){

				// Translate Image Ratio
				$image_ratio = layers_translate_image_ratios( $widget['design'][ 'imageratios' ] );

				if( 'layout-boxed' == $this->check_and_return( $widget , 'design', 'layout' ) && $col_count > 2 ){
					$use_image_ratio = $image_ratio . '-medium';
				} elseif( 'layout-boxed' != $this->check_and_return( $widget , 'design', 'layout' ) && $col_count > 3 ){
					$use_image_ratio = $image_ratio . '-large';
				} else {
					$use_image_ratio = $image_ratio . '-large';
				}
			} else {
				$use_image_ratio = 'large';
			}

			// Set the background & font styling
			if( $this->check_and_return( $widget, 'design', 'background' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'background' => $widget['design'][ 'background' ] ) );
			if( $this->check_and_return( $widget, 'design', 'fonts', 'color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'color', array( 'selectors' => array( '.section-title h3.heading' , '.section-title p.excerpt' ) , 'color' => $widget['design']['fonts'][ 'color' ] ) );
			if( $this->check_and_return( $widget, 'design', 'column-background-color' ) ) $this->inline_css .= layers_inline_styles( '#' . $widget_id, 'background', array( 'selectors' => array( '.thumbnail:not(.with-overlay) .thumbnail-body' ) , 'background' => array( 'color' => $this->check_and_return( $widget, 'design', 'column-background-color' ) ) ) );
			if( $this->check_and_return( $widget, 'design', 'buttons' ) ) layers_inline_button_styles( '#' . $widget_id, 'button', array( 'selectors' => array( '.thumbnail-body a.button' ) ,'button' => $this->check_and_return( $widget, 'design', 'buttons' ) ) );

			/**
			* Generate the widget container class
			*/
			$widget_container_class = array();
			$widget_container_class[] = 'storekit-product-category-widget';
			$widget_container_class[] = 'widget';
			$widget_container_class[] = 'row';
			$widget_container_class[] = 'content-vertical-massive';
			$widget_container_class[] = $this->check_and_return( $widget , 'design', 'advanced', 'customclass' );
			$widget_container_class[] = $this->get_widget_spacing_class( $widget );
			$widget_container_class = implode( ' ', apply_filters( 'layers_woocommerce_category_widget_container_class' , $widget_container_class ) ); ?>

			<section class="<?php echo $widget_container_class; ?>" id="<?php echo $widget_id; ?>">
				<?php if( '' != $this->check_and_return( $widget , 'title' ) ||'' != $this->check_and_return( $widget , 'excerpt' ) ) { ?>
					<div class="container">
						<div class="section-title <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'size' ); ?> <?php echo $this->check_and_return( $widget , 'design', 'fonts', 'align' ); ?> clearfix">
							<?php if( isset( $widget['title'] ) && '' != $widget['title'] ) { ?>
								<h3 class="heading"><?php echo esc_html( $widget['title'] ); ?></h3>
							<?php } ?>
							<?php if( isset( $widget['excerpt'] ) && '' != $widget['excerpt'] ) { ?>
								<div class="excerpt"><?php echo $widget['excerpt']; ?></div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>

				<div class="row <?php echo $this->get_widget_layout_class( $widget ); ?> <?php echo $this->check_and_return( $widget , 'design', 'liststyle' ); ?>">
					<?php // Set total width so that we can apply .last to the final container
					$total_width = 0;

					if( isset( $widget[ 'category_ids' ] ) ) {
						foreach ( explode( ',', $widget[ 'category_ids' ] ) as $category_id ) {

							$term = get_term( $category_id, $this->taxonomy );

							if( is_wp_error( $term ) || !is_object( $term ) ) continue;

							// Set Featured Media
							$featureimage = get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true );

							$media = layers_get_feature_media(
								$featureimage ,
								$use_image_ratio
							);

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

								<?php // Layers Featured Media
								if( isset( $widget['show_media'] ) ) { ?>
									<div class="<?php echo 'thumbnail-media' .  ( ( isset( $widget['design'][ 'imageratios' ] ) && 'image-round' == $widget['design'][ 'imageratios' ] ) ? ' image-rounded' : '' ); ?>">
										<a href="<?php echo get_term_link( $term->term_id, $this->taxonomy ); ?>">
											<?php echo $media; ?>
										</a>
									</div>
								<?php } // if Show Media ?>

								<?php if( isset( $widget['show_titles'] ) || isset( $widget['show_excerpts'] ) || isset( $widget['show_call_to_action'] ) ) { ?>
									<div class="thumbnail-body">
										<div class="overlay">
											<?php if( isset( $widget['show_titles'] ) ) { ?>
												<header class="article-title">
													<h4 class="heading">
														<a href="<?php echo get_term_link( $term->term_id, $this->taxonomy ); ?>">
															<?php echo $term->name; ?>
														</a>
													</h4>
												</header>
											<?php } ?>
											<?php if( isset( $widget['show_excerpts'] ) ) {
												if( isset( $widget['excerpt_length'] ) && '' == $widget['excerpt_length'] ) {
													echo '<div class="excerpt">';
														echo apply_filters( 'the_content', $term->description );
													echo '</div>';
												} else if( isset( $widget['excerpt_length'] ) && 0 != $widget['excerpt_length'] && strlen( $term->description ) > $widget['excerpt_length'] ){
													echo '<p class="excerpt">' . substr( $term->description , 0 , $widget['excerpt_length'] ) . '&#8230;</p>';
												} else if( '' != $term->description ){
													echo '<p class="excerpt">' . $term->description . '</p>';
												}
											}; ?>
											<?php if( $this->check_and_return( $widget, 'show_product_count' ) ) { ?>
												<footer class="meta-info">
													<p>
														<span class="meta-item meta-date"><i class="l-shopping-cart"></i><?php _e( 'Products:' , 'layers-storekit' );?> <?php echo $term->count; ?></span>
													</p>
												</footer>
											<?php } ?>
											<?php if( isset( $widget['show_call_to_action'] ) && $this->check_and_return( $widget , 'call_to_action' ) ) { ?>
												<a href="<?php echo get_term_link( $term->term_id, $this->taxonomy ); ?>" class="button"><?php echo $widget['call_to_action']; ?></a>
											<?php } // show call to action ?>
										</div>
									</div>
								<?php } // if show titles || show excerpt ?>
							</article>
						<?php } // Foreach Categories
					} // If Category IDs exist?>
				</div>

				<?php if( 'list-masonry' == $this->check_and_return( $widget , 'design', 'liststyle' ) ) { ?>
					<script type="text/javacript">
						jQuery(function($){
							layers_masonry_settings[ '<?php echo $widget_id; ?>' ] = [{
									itemSelector: '.layers-masonry-column',
									layoutMode: 'masonry',
									gutter: <?php echo ( isset( $widget['design'][ 'gutter' ] ) ? 20 : 0 ); ?>
								}];

							$('#<?php echo $widget_id; ?>').find('.list-masonry').layers_masonry( layers_masonry_settings[ '<?php echo $widget_id; ?>' ][0] );
						});
					</script>
				<?php } // masonry trigger

				// Print the Inline Styles for this Widget
				if( method_exists( $this, 'print_inline_css' ) )
					$this->print_inline_css();

				// Advanced Styling
				$this->apply_widget_advanced_styling( $widget_id, $widget ); ?>
			</section>
		<?php }

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
		*
		* Set Category Defaults
		*
		*/
		function set_category_defaults( $instance ){
			if( empty( $instance ) ){
				$terms = get_terms( 'product_cat', array( 'number' => 3, 'hide_empty' => false ) );
				if( !is_wp_error( $terms ) ){
					foreach( $terms as $t ){
						$default_terms[] = $t->term_id;
					}
					$this->defaults[ 'category_ids' ] = implode( ',', $default_terms );
				}
			}
		}

		/**
		*  Widget form
		*
		* We use regular HTML here, it makes reading the widget much easier than if we used just php to echo all the HTML out.
		*
		*/
		function form( $instance ){

			// Set defaults
			$this->set_category_defaults( $instance );

			// $instance Defaults
			$instance_defaults = $this->defaults;

			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();

			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );
			extract( $instance, EXTR_SKIP );

			$design_bar_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_components' , array(
					'layout',
					'fonts',
					'custom',
					'columns',
					'buttons',
					'imageratios',
					'background',
					'advanced'
				) );

			$design_bar_custom_components = apply_filters( 'layers_' . $this->widget_id . '_widget_design_bar_custom_components' , array(
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
						'label' => __( 'Display', 'layers-storekit' ),
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
								'show_media' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_media' ) ,
									'id' => $this->get_field_id( 'show_media' ) ,
									'value' => ( isset( $show_media ) ) ? $show_media : NULL,
									'label' => __( 'Show Featured Images' , 'layers-storekit' )
								),
								'show_titles' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_titles' ) ,
									'id' => $this->get_field_id( 'show_titles' ) ,
									'value' => ( isset( $show_titles ) ) ? $show_titles : NULL,
									'label' => __( 'Show Category Titles' , 'layers-storekit' )
								),
								'show_product_count' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_product_count' ) ,
									'id' => $this->get_field_id( 'show_product_count' ) ,
									'value' => ( isset( $show_product_count ) ) ? $show_product_count : NULL,
									'label' => __( 'Show Product Count' , 'layers-storekit' )
								),
								'show_excerpts' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_excerpts' ) ,
									'id' => $this->get_field_id( 'show_excerpts' ) ,
									'value' => ( isset( $show_excerpts ) ) ? $show_excerpts : NULL,
									'label' => __( 'Show Category Description' , 'layers-storekit' )
								),
								'excerpt_length' => array(
									'type' => 'number',
									'name' => $this->get_field_name( 'excerpt_length' ) ,
									'id' => $this->get_field_id( 'excerpt_length' ) ,
									'min' => 0,
									'max' => 10000,
									'value' => ( isset( $excerpt_length ) ) ? $excerpt_length : NULL,
									'label' => __( 'Category Description Length' , 'layers-storekit' ),
									'data' => array( 'show-if-selector' => '#' . $this->get_field_id( 'show_excerpts' ), 'show-if-value' => 'true' )
								),
								'show_call_to_action' => array(
									'type' => 'checkbox',
									'name' => $this->get_field_name( 'show_call_to_action' ) ,
									'id' => $this->get_field_id( 'show_call_to_action' ) ,
									'value' => ( isset( $show_call_to_action ) ) ? $show_call_to_action : NULL,
									'label' => __( 'Show Button' , 'layers-storekit' )
								),
								'call_to_action' => array(
									'type' => 'text',
									'name' => $this->get_field_name( 'call_to_action' ) ,
									'id' => $this->get_field_id( 'call_to_action' ) ,
									'value' => ( isset( $call_to_action ) ) ? $call_to_action : NULL,
									'label' => __( 'Button Text' , 'layers-storekit' ),
									'data' => array( 'show-if-selector' => '#' . $this->get_field_id( 'show_call_to_action' ), 'show-if-value' => 'true' )
								),
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
				$design_bar_custom_components
			); ?>
			<div class="layers-container-large" id="layers-column-widget-<?php echo $this->number; ?>">
				<?php $this->form_elements()->header( array(
					'title' =>'Product Categories',
					'icon_class' =>'text'
				) ); ?>

				<section class="layers-accordion-section layers-content">

					<p class="layers-form-item">
						<?php echo $this->form_elements()->input(
							array(
								'type' => 'text',
								'name' => $this->get_field_name( 'title' ) ,
								'id' => $this->get_field_id( 'title' ) ,
								'placeholder' => __( 'Enter title here' , 'layers-storekit' ),
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
								'placeholder' => __( 'Short Excerpt' , 'layers-storekit' ),
								'value' => ( isset( $excerpt ) ) ? $excerpt : NULL ,
								'class' => 'layers-textarea layers-large'
							)
						); ?>
					</p>

					<?php if( isset( $category_ids ) && '' != $category_ids ){
						foreach ( explode( ',', $category_ids ) as $category_id ) {

							$term = get_term( $category_id, $this->taxonomy );

							$select_terms[] = array( 'id' => $term->term_id, 'text' => esc_attr( $term->name ) );
						}
					} ?>

					<p class="layers-form-item">
						<label><?php _e( 'Select Which Categories to Display' , 'layers-storekit' ); ?></label>
						<?php echo $this->form_elements()->input(
							array(
								'type' => 'hidden',
								'name' => $this->get_field_name( 'category_ids' ),
								'id' => $this->get_field_id( 'category_ids' ),
								'data' => array(
											'woocommerce-column-category-ids' => $this->number,
											'placeholder' => __( 'eg. "T-Shirts"' , 'layers-storekit' ),
											'terms' => ( isset( $select_terms ) ? json_encode( $select_terms ) : false )
										),
								'value' => ( isset( $category_ids ) ? $category_ids : NULL )
							)
						);?>
					</p>

				</section>
			</div>

		<?php } // Form
	} // Class

	// Add our function to the widgets_init hook.
	register_widget("Layers_WooCommerce_Category_Widget");
}