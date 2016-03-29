/**
 * Slider Widget JS file
 *
 * This file contains functions relating to the Slider Widget
 *
 * @package Layers
 * @since Layers 1.0.0
 * Contents
 * 1 - Sortable items
 * 2 - Select2
 *
 * Author: Obox Themes
 * Author URI: http://www.oboxthemes.com/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

jQuery(document).ready(function($){
	var $select2_els = new Array();

	/**
	* 1 - Sortable items
	*/
	//layers_woocommerce_category_widget_select2();

	$(document).on ( 'widget-added' , function( e, widget ){

		$el = $(widget).find( '[data-woocommerce-column-category-ids]' )
		if( 'undefined' !== $el ) {
			layers_woocommerce_category_widget_select2( $el );
		}
	});

	/**
	* 2 - Select2
	*/
	function layers_woocommerce_category_widget_select2( $el ){

		$select2_els.push( $el );

		$el.select2({
			tags: false,
			width: '100%',
			initSelection : function (element, callback) {
				callback(element.data( 'terms' ));
			},
			ajax: {
				url: ajaxurl,
				//dataType: 'json',
				delay: 250,
				type: 'POST',
				data: function( term, page ) {

					var $existing_categories = $(this).select2('data');

					return {
						term: term,
						categories: $existing_categories,
						action: 'layers_woocommerce_category_widget_actions',
						widget_action: 'category-search',
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

}); //jQuery