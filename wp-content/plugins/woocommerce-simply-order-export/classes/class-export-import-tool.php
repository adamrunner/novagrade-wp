<?php

//Prevent direct access.
if( !defined('ABSPATH') ){
	exit;
}

/**
 * Class for exporting and importing settings.
 */

class Export_import {

	/**
	 * Class constructor
	 */
	function __construct() {
		//Export Hooks
		add_action( 'advanced_options_end', array( $this, 'export_import_button' ), 20 );
		add_action( 'admin_init', array($this, 'generate_settings_file'), 10 );
		add_action( 'admin_init', array($this, 'wsoe_download_settings_file'), 12 );
		add_action( 'admin_notices', array($this, 'wsoe_settings_downloaded_success') );
		
		//Import hooks
		add_action( 'load-woocommerce_page_wc-settings', array( $this, 'wsoe_start_op_buffer' ), 8 );
		add_action( 'woocommerce_settings_saved', array( $this, 'wsoe_handle_upload' ), 8 );
		add_filter( 'upload_mimes', function( $t ){ $t['json'] = 'application/json'; return $t; } );
	}

	/**
	 * Settings exported admin message
	 */
	function wsoe_settings_downloaded_success() {

		if( !session_id() )
			session_start();

		if( !empty($_SESSION['wsoe_setting_export_notice']) ){

			$url = esc_url(add_query_arg(
								array(
									'page'=>'wc-settings',
									'tab'=>'order_export'
								),
								admin_url('admin.php')
					)); ?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( sprintf( 'Settings exported successfully. <a target="_blank" href="%s">Click here</a> to download the settings file.', $url ), 'woocommerce-simply-order-export' ); ?></p>
			</div><?php
			
			unset( $_SESSION['wsoe_setting_export_notice'] );
		}
	}

	/**
	 * Insert Export button on settings page.
	 */
	function export_import_button() {

		$url = esc_url(add_query_arg(
					array(
							'page'=>'wc-settings',
							'tab'=>'order_export',
							'wsoe_nonce'=>  wp_create_nonce('wsoe_export_settings'),
							'wsoe_export_settings'=>1 ),
						admin_url('admin.php')
					)); ?>

		<tr>
			<th colspan="2">
			<h3><?php _e( 'Export/Import plugin settings', 'woocommerce-simply-order-export' ) ?></h3>
			</th>
		</tr>

		<tr>
			<th>
				<?php _e( 'Export plugin settings', 'woocommerce-simply-order-export') ?>
				<img class="help_tip" data-tip="<?php _e('Export order export plugin settings.', 'woocommerce-simply-order-export') ?>" src="<?php echo WSOE_IMG; ?>help.png" height="16" width="16">
			</th>

			<td>
				<a class="button" href="<?php echo $url; ?>"><?php _e( 'Export Settings', 'woocommerce-simply-order-export' ); ?></a>
			</td>
		</tr>

		<tr>
			<th>
				<?php _e( 'Import plugin settings', 'woocommerce-simply-order-export') ?>
				<img class="help_tip" data-tip="<?php _e('Import order export plugin settings.', 'woocommerce-simply-order-export') ?>" src="<?php echo WSOE_IMG; ?>help.png" height="16" width="16">
			</th>

			<td>
				<input type="file" name="wsoe_import_settings" value="" />
				<input type="submit" class="button" name="wsoe_import_submit" value="Import settings" />
			</td>
		</tr><?php
	}

	/**
	 * Dodownload/Export settings file in json format.
	 */
	function generate_settings_file() {

		$referrer	=	wp_get_referer();
		$nonce		=	empty($_GET['wsoe_nonce']) ? '' : $_GET['wsoe_nonce'];

		if( !empty($referrer) && !empty($_GET['wsoe_nonce']) && !empty($_GET['wsoe_export_settings']) && wp_verify_nonce( $nonce, 'wsoe_export_settings' ) ){

			$export_array = array();

			$redirect_to = remove_query_arg( array('wsoe_nonce', 'wsoe_export_settings', 'wsoe_settings_downloaded'),  wp_get_referer());
			$redirect_to = esc_url(add_query_arg( array( 'wsoe_settings_downloaded'=>1 ), $redirect_to ));

			$keys_to_fetch = apply_filters( 'wsoe_export_keys', array( 'wsoe_advanced_settings_core' ) );

			foreach( $keys_to_fetch as $key ) {
				$val = get_option($key);
				$export_array[$key] = $val;
			}

			$contents = json_encode($export_array);

			if( !session_id() )
				session_start ();

			$_SESSION['wsoe_setting_export_content']	=	$contents;
			$_SESSION['wsoe_setting_export_notice']		=	true;

			wp_safe_redirect($redirect_to);
			die;
		}
	}

	/**
	 * Download settings file.
	 */
	function wsoe_download_settings_file() {

		if( !session_id() )
			session_start ();

		if( !empty($_SESSION['wsoe_setting_export_content']) && !isset($_SESSION['wsoe_setting_export_notice']) ) {

			$contents = $_SESSION['wsoe_setting_export_content'];
			unset($_SESSION['wsoe_setting_export_content']);
			$charset = get_option('blog_charset');
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Description: File Transfer');
			header('Content-Encoding: '. $charset);
			header('Content-type: text/json; charset='. $charset);
			header("Content-Disposition: attachment; filename=wsoe.json");
			header("Expires: 0");
			header("Pragma: public");

			$fh = @fopen( 'php://output', 'w' );
			fwrite( $fh, $contents );
			fclose($fh);
			exit();
		}

	}
	
	/**
	 * Start output buffer
	 */
	function wsoe_start_op_buffer() {

		if( (!empty( $_POST ) && !empty( $_GET['tab'] ) && $_GET['tab'] == 'order_export') && 
			( !empty( $_POST['wsoe_import_submit'] ) && !empty( $_FILES['wsoe_import_settings']['size'] ) ) 
		) {
			//Check if output buffering is on already
			if( 0 == ob_get_level() ){
				ob_start();
			}
		}
	}
	
	/**
	 * wrapper for wp_handle_upload
	 */
	function wsoe_handle_upload(){

		if( (!empty( $_POST ) && !empty( $_GET['tab'] ) && $_GET['tab'] == 'order_export') && 
			( !empty( $_POST['wsoe_import_submit'] ) && !empty( $_FILES['wsoe_import_settings']['size'] )
				&& $_FILES['wsoe_import_settings']['size'] < wp_max_upload_size() )
		){
			$upload_return = wp_handle_upload( $_FILES['wsoe_import_settings'], array( 'test_form'=>false, 'test_type'=>false, 'ext'=>'json', 'type'=>'application/json' ) );

			if( !empty( $upload_return ) && !isset($upload_return['error']) ) {

				$file_contents = file_get_contents($upload_return['file']);
				$file_contents = json_decode( $file_contents, TRUE );

				if( NULL !== $file_contents && is_array($file_contents) ) {
					foreach( $file_contents as $key=>$val ) {
						update_option( $key, $val, 'no');
					}
				}

				// Check if reorder array is set, then accordingly set fields.
				if( !empty($file_contents['wpg_reorder']) && is_array($file_contents['wpg_reorder']) ){
					foreach( $file_contents['wpg_reorder'] as $key=>$val ) {
						update_option( $val, 'yes', 'yes' );
					}
				}
			}

			$url = esc_url(add_query_arg(array(	'page'=>'wc-settings','tab'=>'order_export'),admin_url('admin.php')));

			wp_redirect($url);
			die;
		}
	}
}

new export_import();