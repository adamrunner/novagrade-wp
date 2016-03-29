/**
 * Layers WooCommerce Extension JS file
 *
 * This file contains all theme JS functions, from height matching to button toggling
 *
 * @package Layers
 * @since Layers 1.0
 * Contents
 * 1 - Cart toggle
 * 2 - Cart Ajax Refresh
*/
jQuery(function($) {

	/**
	* 1 - Cart toggle
	*/

	$(document).on( 'click' , '.header-cart'  , function(e){

		var eventTarget = $(e.target);

 		if( 'A' == eventTarget[0].tagName ) return true;

		if( 0 == $(this).find( '.header-mini-cart' ).length ){
			$( '#off-canvas-right' ).toggleClass( 'open' );
			e.preventDefault();
		}
	});

	$( document ).on( 'click', function(e) {
		var eventTarget = $(e.target);

		if( 0 == eventTarget.closest('.header-cart').length ){
			$( '.header-mini-cart.open' ).removeClass( 'open' );
		}

	});

	/**
	 * 2 - Thumbnail Flip
	 *
	 * Flip the thumbnail image on the product list page.
	 */

	// Wait for all image thumbails to load first - so don't clog bandwidth with the new flip images when they are created.
	imagesLoaded( '.products', function(){

		// Find all the flip images and init their parent containers.
		$('.thumbnail-flip-slide-temp').each( function(){

			var container = $(this).parents('.img-wrap');

			// Check if ths is already done.
			if ( !container.hasClass( 'layers-storekit-thumbnail-flip' ) ){

				// Mark this as having been done.
				container.addClass( 'layers-storekit-thumbnail-flip' )

				// Init the flip slider.
				init_flip_slider( container );
			}

		});

	});

	function init_flip_slider( container ){

		// Hold the original image
		var original_image = container.find( 'img' );

		// Move original image bottom of dom stack so subsequent images are underneath other elemnts in img-wrap
		container.prepend( original_image );

		// Collect newly generated images - so we can check if they are loaded later.
		var new_images = [];

		// Convert the 'data' in the div's to img's
		$( container ).find( '.thumbnail-flip-slide-temp' ).each( function( index, el ) {

			// Convert/create the new image.
			var img = new Image();
			img.src = $( this ).attr( 'data-product-img-src' );
			$( img ).addClass( 'thumbnail-flip-slide' );

			// Collect the images.
			new_images.push( img );

			// Remove the used div
			$( this ).remove();
		});

		// Wait for this one slider of images to load, then add them to the page, and init them.
		imagesLoaded( new_images, function( instance ) {

			$( new_images ).each( function( index, el ) {

				//console.log( 'loaded-' + index + ': ' + $( el ).attr('src') );
				$(el).insertAfter( original_image );
			});

			// Go to the first slide.
			go_to_flip_slide( container, 0 );

			// Hover switches between first and second slide.
			container.hover(
				function(){
					go_to_flip_slide( container, 1 );
				},
				function(){
					go_to_flip_slide( container, 0 );
				}
			);
		});
	}

	function go_to_flip_slide( container, destination ) {

		// Get all the slides.
		var slides = container.find( '.thumbnail-flip-slide' );

		slides.each( function( index, element ){

			var slide = $(element);

			slide.removeClass( 'prev-slide current-slide next-slide' );

			// if ( destination > index  ( destination - 1 ) != index ){
			// 	slide.addClass( 'early-slide' );
			// }

			// Previous Slide Slide
			//if ( ( destination - 1 ) == index ){
			if ( destination > index ){
				slide.addClass( 'prev-slide' );
			}
			// Destination Slide
			if ( destination == index ){
				slide.addClass( 'current-slide' );
			}
			// Next Slide
			//if ( ( destination + 1 ) == index ){
			if ( destination < index ){
				slide.addClass( 'next-slide' );
			}

			// if ( destination > index  ( destination - 1 ) != index ){
			// 	slide.addClass( 'late-slide' );
			// }
		});
	}

}(jQuery));

