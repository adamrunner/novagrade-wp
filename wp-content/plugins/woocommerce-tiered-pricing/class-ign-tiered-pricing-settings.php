<?php 
/** ********************************************************

Copyright (c) 2013 - IgniteWoo.com - ALL RIGHTS RESERVED

************************************************************/

if ( !defined( 'ABSPATH' ) ) die;

if ( !class_exists( 'WC_Integration' ) ) 
	return;
	
class IgniteWoo_Tiered_Pricing_Settings extends WC_Integration {

	function __construct() {

			
		$this->id = 'ignitewoo_tiered_pricing';

		$this->method_title = __( 'Tiered Pricing', 'ignitewoo_tiered_pricing' );

		$this->method_description = sprintf( __( 'Adjust the settings and roles to suit your needs. <em>See the documentation at <a href="%s" target="_blank">IgniteWoo.com</a> for details. At the site navigate to WooCommerce Plugins -> Documentation</em>.', 'ignitewoo_tiered_pricing' ), 'http://ignitewoo.com' );
		
		$this->init_form_fields();

		$this->init_settings();

		add_action( 'woocommerce_update_options_integration_' . $this->id , array( &$this, 'process_admin_options') );
		
		add_action( 'wp_ajax_ign_get_uniqid', array( &$this, 'get_id' ) );

	}

	
	function init_form_fields() {
		

	}
	
	
	function get_id() { 
	
		die( json_encode( array( 'id' => uniqid() ) ) );
	
	}
	
	
	function admin_options() { 
		?>

		<h3><?php echo isset( $this->method_title ) ? $this->method_title : __( 'Settings', 'ignitewoo_tiered_pricing' ) ; ?></h3>

		<?php echo isset( $this->method_description ) ? wpautop( $this->method_description ) : ''; ?>

		<table class="form-table">
			<?php $this->min_settings() ?>
			<?php $this->generate_settings_html(); ?>
		</table>

		<div><input type="hidden" name="section" value="<?php echo $this->id; ?>" /></div>

		<?php
	}
	
	
	function min_settings() { 
		global $wp_roles, $woocommerce;

		if ( !isset( $wp_roles->roles ) )
			return;
			
		$settings = get_option( 'woocommerce_'. $this->id . '_settings' );
		
		$defaults = array(
				'show_regular_price' => '',
				'show_savings' => '',
				'show_regular_price_label' => __( 'Regularly', 'ignitewoo_tiered_pricing' ),
				'show_savings_label' => __( 'You Save', 'ignitewoo_tiered_pricing' ),
		);
		
		$settings = wp_parse_args( $settings, $defaults );
		
		?>
		<script>
		jQuery( document ).ready( function( $ ) { 
			$( "#ign_add_new_role" ).click( function() { 
			
				var uid = null;
				
				$.post( ajaxurl, { action:'ign_get_uniqid' }, function( data ) { 
				
					try { 
						var j = $.parseJSON( data );
						
						uid = j.id;
						
					} catch( err ) { 
				
						alert( '<?php _e( 'An error occurred. Try again.', 'ignitewoo_tiered_pricing' )?>');
						return false;
				
					}
				
					var html = '\
						<tr>\
						<td>\
							<label class="">\
							<input type="text" name="ignite_level_name[ignite_level_' + uid + ']" placeholder="<?php _e( 'Enter a role name','ignitewoo_tiered_pricing' )?>" value="">\
							</label>\
						</td>\
						<td>\
						</td>\
						</tr>\
					';
					
					$( '.roles_table' ).append( html );
					
					return false;
				
				});
				

			})
		})
		</script>
		
		<style>
			.help_tip.tiered { width: 16px; float: none !important; }
		</style>
		
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for="roles">
				<?php _e( 'Display Regular Price', 'ignitewoo_tiered_pricing' )?>
				</label>
			</th>
			<td class="forminp ign_roles">	
				<input type="checkbox" value="yes" name="ignitewoo_tiered_show_regular_price" <?php checked( $settings['show_regular_price'], 'yes', true ) ?>>  
				<input type="text" value="<?php echo $settings['show_regular_price_label'] ?>" name="ignitewoo_tiered_show_regular_price_label"> 
				<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Check this to display the regular price.', 'ignitewoo_tiered_pricing' )?>">
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for="roles">
				<?php echo sprintf( __( 'Display %s Savings', 'ignitewoo_tiered_pricing' ), get_woocommerce_currency_symbol() ) ?>
				</label>
			</th>
			<td class="forminp ign_roles">	
				<input type="checkbox" value="yes" name="ignitewoo_tiered_show_savings" <?php checked( $settings['show_savings'], 'yes', true ) ?>> 
				<input type="text" value="<?php echo $settings['show_savings_label'] ?>" name="ignitewoo_tiered_show_savings_label"> 
				<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Check this to display the amount being saved.', 'ignitewoo_tiered_pricing' )?>">
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row">
				<label for="roles">
				<?php _e( 'Pricing Roles', 'ignitewoo_tiered_pricing' )?>
				</label>
			</th>
			<td class="forminp ign_roles">	
				<table width="70%" class="roles_table">
					<tr>
						<th>
							<strong><?php _e( 'Role Name', 'ignitewoo_tiered_pricing' ) ?></strong>
						</th>
						<th>
							<strong><?php _e( 'Delete', 'ignitewoo_tiered_pricing' ) ?></strong>
							<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'When you delete a role then any users with that role will have their role changed to customer', 'ignitewoo_tiered_pricing' )?>">
						</th>
						<th>
							<strong><?php _e( 'Disable Taxes', 'ignitewoo_tiered_pricing' ) ?></strong>
							<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Disable taxes for shoppers with this role', 'ignitewoo_tiered_pricing' )?>">
						</th>
						<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) { ?>
						<th>
							<strong><?php _e( 'Allow Backorders', 'ignitewoo_tiered_pricing' ) ?></strong>
							<img class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Allow shoppers with this role to backorder an item when the item is not in stock', 'ignitewoo_tiered_pricing' )?>">
						</th>
						<?php } ?>
					</tr>
		<?php

		asort( $wp_roles->roles );

		foreach( $wp_roles->roles as $role => $data ) { 

			if ( 'ignite_level_' != substr( $role, 0, 13 ) )
				continue;
			?>
					<tr>
						<td>
							<label class="">
								<span><?php echo stripslashes( $data['name'] ) ?></span> 
								<br>
								<img style="vertical-align:middle; height: 12px; width: 12px" class="help_tip tiered" src="<?php echo $woocommerce->plugin_url() ?>/assets/images/help.png" data-tip="<?php _e( 'Meta key for importing prices with your CSV import tool of choice', 'ignitewoo_tiered_pricing' )?>">
								<small><?php _e( 'Meta key', 'ignitewoo_tiered_pricing' ) ?>: &nbsp;<?php echo $role ?></small>
							</label>
						</td>
						<td style="vertical-align:top">
							<input type="checkbox" value="<?php echo $role ?>" style="" id="ignite_level_<?php echo $role ?>" name="ignite_level_delete[<?php echo $role ?>]" class="input-text wide-input "> 
						</td>
						<td style="vertical-align:top">
							
							<select name="ignite_level_tax[<?php echo $role ?>]">
								<?php if ( !isset( $data['capabilities']['no_tax'] ) || true !== $data['capabilities']['no_tax'] ) $selected = 'selected="selected"'; else $selected = ''; ?>
								<option value="yes" <?php echo $selected ?>><?php _e( 'Taxable', 'ignitewoo_tiered_pricing' ) ?></option>
								<?php if ( isset( $data['capabilities']['no_tax'] ) &&  true === $data['capabilities']['no_tax'] ) $selected = 'selected="selected"'; else $selected = ''; ?>
								<option value="no" <?php echo $selected ?>><?php _e( 'Non-taxable', 'ignitewoo_tiered_pricing' ) ?></option>
							</select>
						</td>
						<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) { ?>
						<td style="vertical-align:top">
							<input type="checkbox" value="yes" style="" id="ignite_level_<?php echo $role ?>" name="ignite_level_backorders[<?php echo $role ?>]" class="input-text wide-input" <?php isset( $data['capabilities']['backorders'] ) ? checked( $data['capabilities']['backorders'], true, true ) : '' ?> > 
						</td>
						<?php } ?>
					</tr>
					

			<?php
		}
		?>
				</table>
			</td>
		</tr>
		
		<tr>
			<th></th>
			<td><button type="button" class="button" id="ign_add_new_role"><?php _e( 'Add New Role', 'ignitewoo_tiered_pricing' )?></button></td>
		<?php 
	}
	
	
	function process_admin_options() {
		global $wp_roles, $wpdb;

		if ( !isset( $wp_roles->roles ) )
			return;

		parent::process_admin_options();

		if ( !empty( $_POST['ignite_level_name' ] ) ) { 
		
			foreach( $_POST['ignite_level_name' ] as $key => $irole ) { 

				if ( '' == trim( $irole ) )
					continue;
				
				foreach( $wp_roles->roles as $role => $data ) 
					if ( $role == $irole )
						continue;
				
				add_role( $key , __( trim( $irole ), 'ignitewoo_tiered_pricing' ), array(
					'read' => true,
					'edit_posts' => false,
					'delete_posts' => false
				) );

				
			}
			
		}
		
		if ( !empty( $_POST['ignite_level_tax' ] ) ) { 
		
			foreach( $_POST['ignite_level_tax' ] as $key => $irole ) { 
							
				$role = get_role( $key );
				
				if ( 'no' == $irole ) 
					$role->add_cap( 'no_tax' );
				else
					$role->remove_cap( 'no_tax' );

			}
			
		}

		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '>=' ) ) {
			
			foreach( $wp_roles->roles as $role => $data ) { 

				if ( 'ignite_level_' != substr( $role, 0, 13 ) )
					continue;

				$role = get_role( $role );

				$role->remove_cap( 'backorders' );
		
			}
			
			if ( !empty( $_POST['ignite_level_backorders' ] ) ) { 
			
				foreach( $_POST['ignite_level_backorders' ] as $key => $irole ) { 
		
					$role = get_role( $key );

					if ( 'yes' == $irole ) 
						$role->add_cap( 'backorders' );
					else
						$role->remove_cap( 'backorders' );

				}
				
			}
		}
		
		if ( !empty( $_POST[ 'ignite_level_delete' ] ) ) { 
		
			$user_ids = $wpdb->get_results( 'select ID from ' . $wpdb->users . ' order by ID ASC', ARRAY_A );

			if ( !empty( $user_ids ) && !is_wp_error( $user_ids ) ) { 
				
				foreach( $_POST[ 'ignite_level_delete' ] as $key => $irole ) { 
					
					foreach ( ( array ) $user_ids as $user_id ) {

						$user = get_user_by( 'id', $user_id['ID'] );

						foreach ( ( array ) $user->roles as $role => $data ) {
						
							if ( $role == $irole ) { 

								$userdata = new WP_User( $user->data->ID );
								
								$userdata->remove_role( $irole );
								
								$userdata->add_role( 'customer' );
								
							}
							
						}
						
					}

					remove_role( $key );
					
				}
			}
		}
		

		$settings = get_option( 'woocommerce_'. $this->id . '_settings' );

		$settings['show_regular_price'] =  isset( $_POST['ignitewoo_tiered_show_regular_price'] ) ? $_POST['ignitewoo_tiered_show_regular_price'] : '';
		
		$settings['show_regular_price_label'] = isset( $_POST['ignitewoo_tiered_show_regular_price_label'] ) ? $_POST['ignitewoo_tiered_show_regular_price_label'] : '';
		
		$settings['show_savings_label'] = isset( $_POST['ignitewoo_tiered_show_savings_label'] ) ? $_POST['ignitewoo_tiered_show_savings_label'] : '';
		
		$settings['show_savings'] = isset( $_POST['ignitewoo_tiered_show_savings'] ) ? $_POST['ignitewoo_tiered_show_savings'] : '';
		
		update_option( 'woocommerce_'.  $this->id . '_settings', $settings );

		
	}

}
