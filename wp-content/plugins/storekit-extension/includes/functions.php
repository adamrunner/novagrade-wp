<?php
/**
 * Helper Functions for this plugin.
 *
 */

/**
 * Used to check if a form type exists - e.g. rte would not exist if is an older verion of Layers.
 *
 * @param  string $type type of field to check if exists e.g. 'rte'
 * @return bool
 */
function sorekit_layers_form_type_exists( $type ) {
	
	$form_elements = new Layers_Form_Elements();
	
	ob_start();
	
	$form_elements->input( array(
		'type'        => $type, // e.g. 'rte'
		'name'        => 'temp-name',
		'id'          => 'temp-id',
		'value'       => 'temp-value',
	) );
	
	$result = ob_get_clean();
	
	$result = trim( $result );
	
	return ( bool )( 0 !== strpos( $result, '<input type="hidden"' ) );
}

?>