/**
* Slider Widget JS file
*
* This file contains functions relating to the Slider Widget
 *
 * @package Layers
 * @since Layers 1.0.0
 * Contents
 * 1 - Slider JS Triggers
 * 2 - Select2
 * 3 - Sortable items
 * 4 - Slide Removal & Additions
 * 5 - Slider Focus
 *
 * Author: Obox Themes
 * Author URI: http://www.oboxthemes.com/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

jQuery(document).ready(function($){

	var customizer_api = wp.customize;
	var $select2_els = new Array();

	/**
	* 1 - Sortable items
	*/

	layers_woocommerce_slider_widget_sorable();

	$(document).on ( 'widget-added' , function( e, widget ){

		$el = $(widget).find( '[data-woocommerce-slide-product-ids]' )
		if( 'undefined' !== $el ) {
			layers_woocommerce_slider_widget_select2( $el );
		}
		layers_woocommerce_slider_widget_sorable();

	});

	/**
	* 2 - Select2
	*/
	function layers_woocommerce_slider_widget_select2( $el ){

		$select2_els.push( $el );

		$el.select2({
			tags: false,
			width: '100%',
			ajax: {
				url: ajaxurl,
				//dataType: 'json',
				delay: 250,
				type: 'POST',
				data: function( term, page ) {

					// Look for existing products and exclude them from our search
					var $existing_products = $(this).select2('data');
					$(this).parent().siblings('ul[data-woocommerce-slide-list]').find( 'li.layers-accordion-item' ).each( function(){
						$existing_products.push( { 'id': $(this).data( 'product-id') });
					});

					return {
						term: term,
						products: $existing_products,
						action: 'layers_woocommerce_slider_widget_actions',
						widget_action: 'product-search',
						nonce: layers_woocommerce_params.product_widget_nonce
					};

				},
				results: function( data, page ) {

					var terms = [];
					if ( data ) {
						$.each( data, function( id, text ) {
							terms.push( { id: id, text: text } );
						});
					}

					return { results: terms };
				},
  				escapeMarkup: function (markup) { return markup; },
				formatResult: function( data ) {
					return '<div class="selected-option" data-id="' + data.id + '">' + $.parseHTML( data.text.troString().replace( '&ndash;' , '-' ) ) + '</div>';
				},
				cache: true,
			},
			minimumInputLength: 0
		}).on( 'change' , function(){
			$data = $(this).select2('data');
			$widget = $(this).closest( '.widget' );
			$button = $widget.find( '.layers-woocommerce-add-product-widget-slide' );

			if( 0 == $data.length ){
				// Fade out Add button
				$button.addClass( 'layers-hide' );
			} else {
				// Fade in Add button
				$button.removeClass( 'layers-hide' );

				if( 1 == $data.length ){
					$button.text( $button.data( 'label' ) );
				} else {
					$button.text( $button.data( 'plural-label' ) );
				}
			}
			//$el.closest( '.widget' ).find( '.layers-woocommerce-add-product-widget-slide' ).parent().removeClass( 'layers-soft-hide' );
		});


	}

	$( document ).on( 'click widget-close', function(e) {
		var eventTarget = $(e.target);
		// close any pop-ups that arent the target of the current click
		if( 0 == eventTarget.closest('.select2-container').length ){
			$.each( $select2_els, function(){
				$(this).select2( 'close' );
			});
		}
	});

	/**
	* 3 - Sortable items
	*/

	function layers_woocommerce_slider_widget_sorable(){

		var $product_slide_lists = $( 'ul[data-woocommerce-slide-list]' );

		$product_slide_lists.sortable({
			placeholder: "layers-sortable-drop",
			handle: ".layers-accordion-title",
			stop: function(e , li){
				// Banner UL, looking up from our current target
				$slideList = li.item.closest( 'ul' );

				// Set focus slide
				$widget = li.item.closest( '.widget' );
				$slide_index = li.item.index();
				$slide_guid = li.item.data( 'guid' );
				layers_set_slide_index( $widget, $slide_index, $slide_guid );

				// Banners <input>
				$slideInput = $( 'input[data-woocommerce-slide-input=' + $slideList.data( 'number' ) + ']' );

				// Apply new slide order
				$product_slide_guids = [];
				$slideList.find( 'li.layers-accordion-item' ).each(function(){
					$product_slide_guids.push( $(this).data( 'guid' ) );
				});

				// Trigger change for ajax save
				$slideInput.val( $product_slide_guids.join() ).layers_trigger_change();
			}
		});
	};

	/**
	* 4 - Slide Removal & Additions
	*/

	$(document).on( 'click' , 'ul[data-woocommerce-slide-list] .icon-trash' , function(e){
		e.preventDefault();

		// "Hi Mom"
		var $trash_button = $(this);

		// Confirmation message @TODO: Make JS confirmation module
		var $remove_slide = confirm( sliderwidgeti18n.confirm_message );

		if( false === $remove_slide ) return;

		// Banner UL
		$slideList = $( 'ul[data-woocommerce-slide-list=' + $trash_button.data( 'number' ) + ']' );

		// Banners <input>
		$slideInput = $( 'input[data-woocommerce-slide-input=' + $trash_button.data( 'number' ) + ']' );

		// Remove this slide
		$trash_button.closest( '.layers-accordion-item' ).remove();

		// Curate slide IDs
		$product_slide_guids = [];

		$slideList.find( 'li.layers-accordion-item' ).each(function(){
			$product_slide_guids.push( $(this).data( 'guid' ) );
		});

		// Trigger change for ajax save
		$slideInput.val( $product_slide_guids.join() ).layers_trigger_change();
	});

	$(document).on( 'click' , 'button.layers-woocommerce-add-product-widget-slide' , function(e){
		e.preventDefault();

		// "Hi Mom"
		$button = $(this);
		
		// Add loading class to Add button.
		$button.addClass('layers-loading-button');

		// Create the list selector
		$slideListId = 'ul[data-woocommerce-slide-list="' + + $button.data( 'number' ) + '"]';

		$input = $( '#' + $button.data( 'input' ) );

		// Banner UL
		$slideList = $( $slideListId );

		$slideInput = $( 'input[data-woocommerce-slide-input=' + $button.data( 'number' ) + ']' );

		$product_ids = [];

		if( $input.select2('data') ) {
			$.each( $input.select2('data'), function( key, data) {
				$product_ids.push( data.id );
			});
		}

		if( $product_ids.length == 0 ) {
			return false;
		}

		$post_data ={
				action: 'layers_woocommerce_slider_widget_actions',
				widget_action: 'bulk-add',
				product_ids: $product_ids.join(),
				id_base: $slideList.data( 'id_base' ),
				number: $slideList.data( 'number' ),
				nonce: layers_woocommerce_params.product_widget_nonce
			};

		$.post(
			ajaxurl,
			$post_data,
			function(data){

				// Clear the input
				$input.select2('data', null);

				// Hide Add button until search again, delay so animations don't crossover.
				setTimeout(function() {
					$button.addClass( 'layers-hide' );
				}, 200 );
				
				// Remove Add button loading class
				$button.removeClass('layers-loading-button');

				// Set slide
				$slide = $(data);
				$slideList.find('li.layers-accordion-item').removeClass('open').find( '.layers-content' ).slideUp();

				// Append module HTML
				$slideList.append($slide);

				// Add Open Class to slide
				$new_slide_i = 0;
				$open_slide_guid = 0;

				$slide.each(function(){
					if( 0 == $new_slide_i ){
						$(this).addClass('open');
						$open_slide_guid = $(this).data( 'guid' );
					} else {
						$(this).removeClass('open').find( '.layers-content' ).slideUp();
					}
					$new_slide_i++;
				});

				if( !$open_slide_guid ) $open_slide_guid = $slide.first().data( 'guid' );

				$product_slide_guids = [];

				$slideList.find( 'li.layers-accordion-item' ).each(function(){

					$product_slide_guids.push( $(this).data( 'guid' ) );

					if( $open_slide_guid == $(this).data( 'guid' ) ){
						 $slide_index = $(this).index();
						 $slide_guid = $(this).data( 'guid' );
					}
				});

				// Set focus slide
				$widget = $slideList.closest( '.widget' );
				layers_set_slide_index( $widget, $slide_index, $slide_guid );


				// Trigger change for ajax save
				$slideInput.val( $product_slide_guids.join() ).layers_trigger_change();

				// Trigger color selectors
				$widget.find('.layers-color-selector').wpColorPicker();
			}
		) // $.post
	});

	/**
	* 4 - Slider Focus
	*/
	$(document).on( 'focus click' , 'ul[data-woocommerce-slide-list] li a.layers-accordion-title', function(e){

		// Set focus slide
		$widget = $(this).closest( '.widget' );
		$li = $(this).parent();

		if( undefined !== $li.data('guid') ){
			$slide_index = $li.index();
			$slide_guid = $li.data('guid');
			layers_set_slide_index( $widget, $slide_index, $slide_guid );
		}
	});

	function layers_set_slide_index( $widget, $slide_index, $slide_guid ){
		if( undefined !== $widget ){
			$widget.find( 'input[data-focus-slide="true"]' ).val( $slide_index );
		}
	}

}); //jQuery