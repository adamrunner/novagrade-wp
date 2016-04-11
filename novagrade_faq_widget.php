<?php
/**
 * Novagrade FAQ Widget
 *
 * This file is used to register and display the Novagrade FAQ widget.
 *
 * @package Layers
 * @since Layers 1.0.0
 */
if( !class_exists( 'Novagrade_Faq_Widget' ) ) {

class Novagrade_Faq_Widget extends Layers_Widget {

        /**
        *  1 - Widget construction
        */
        function Novagrade_Faq_Widget(){
            $this->widget_title = __( 'FAQ' , 'layerswp' );
            $this->widget_id = 'novagrade-faq';
            $this->post_type = '';
            $this->taxonomy = '';
            $this->checkboxes = array();
            $widget_ops = array(
                'classname' => 'obox-layers-' . $this->widget_id .'-widget',
                'description' => __('This widget is used to display your FAQ', 'layerswp')
            );

            $control_ops = array(
                'width' => '660',
                'height' => NULL,
                'id_base' => 'layers-widget-' . $this->widget_id
            );

            /* Setup Widget Defaults */
            $this->defaults = array (
            	'title' => __( 'All of the FAQ', 'layerswp' ),
            	'excerpt' => __( 'Stay up to date with all our latest news and launches. Only the best quality makes it onto our blog!', 'layerswp' ),
            	'text_style' => 'overlay',
            	'category' => 0,
            	'design' => array(
            		'layout' => 'layout-boxed',
            		'imageratios' => 'image-square',
            		'textalign' => 'text-left',
            		'liststyle' => 'list-msonry',
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
        *  2 - Widget form
        *
        * We use regulage HTML here, it makes reading the widget much easier
        * than if we used just php to echo all the HTML out.
        *
        */
        function form( $instance ){
            $instance_defaults = $this->defaults;

            //If we have information in this widget already, ignore the defaults
            if( !empty( $instance ) ) $instance_defaults = array();

            $widget = wp_parse_args($instance, $instance_defaults);

            $design_bar_components = apply_filters(
                'layers_' . $this->widget_id . '_widget_design_bar_components',
                array(
                    'layout',
                    'fonts',
                    'custom',
                    'columns',
                    'liststyle',
                    'imageratios',
                    'background',
                    'advanced'        
                );
        );
        } // Form

        /**
        *  3 - Widget update
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
        *  4 - Widget front end display
        */
        function widget( $args, $instance ) {

        }

    } // Class

    // Register our widget
    register_widget("Novagrade_Faq_Widget");
}
