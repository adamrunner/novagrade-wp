<?php
//Block direct access
if( !defined('ABSPATH') ){
	exit ();
}

if( !class_exists('WSOE_License_Verify_Notify') ){

	/**
	 * Verifies user's license and if it is expired, notify them using admin notice.
	 */
	class WSOE_License_Verify_Notify {

		/**
		 *
		 * @var string Download slug.
		 */
		public $slug;

		/**
		 *
		 * @var string Endpoint to request to.
		 */
		public $endpoint;
		
		/**
		 * Option name to set/get for corresponding extension.
		 */
		public $option_to_set;
		
		/**
		 * File ID
		 */
		public $file_id;
		
		/**
		 * Hook name for license check
		 */
		public $hook_name;
		
		/**
		 * To track some local/conditional checks.
		 */
		static $tracker = array();

		/**
		 * Constructor - Adds necessary hooks.
		 * Also assigns enpoints and slug for download.
		 */
		function __construct( $options ) {

			$this->endpoint = $options['endpoint'];
			$this->slug = $options['slug'];
			$this->option_to_set = $options['option_name'];
			$this->hook_name = (string)$options['hook'];
			$this->file_id = (int)$options['file_id'];

			add_action( $this->hook_name, array($this, 'check_license') );
			add_action( 'woocommerce_settings_saved', array($this, 'schedule_license_check_event'), 9 );
			add_action( 'admin_notices', array($this, 'license_notice') );
			add_action( 'admin_footer', array($this, 'dismiss_notice_event'), 99 );
			add_action( 'wp_ajax_wsoe_addon_expired_dismiss', array($this, 'wsoe_addon_expired_dismiss') );
			add_action( 'wp_ajax_wsoe_scheduler_expired_dismiss', array($this, 'wsoe_scheduler_expired_dismiss') );
		}

		function check_license( $email ) {

			if( !empty($email) ) {

				// Get the remote info
				$ping_url = trailingslashit($this->endpoint) . "?wsoe_license_check=true";
				$ping_url .= "&email=" . urlencode( $email );
				$ping_url .= "&slug=" . urlencode( $this->slug );

				$request = wp_remote_post( $ping_url );

				if( (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) && is_serialized($request['body']) ){

					$info = @unserialize($request['body']);

					if( $info->exists && $info->expired ) {
						update_option( $this->option_to_set, $info, false );
					}elseif( $info->exists && ( $info->expired == false ) ){
						delete_option( $this->option_to_set ); // Delete notice
					}
				}
			}
		}

		/**
		 * Show license related notice.
		 */
		function license_notice() {

			$is_expired = get_option( $this->option_to_set, false );

			if( $is_expired ){

				ob_start();

				if( $this->file_id == 1 ){
					include trailingslashit(WSOE_BASE) .'views/html-notice-addon-license.php';
				}else{
					include trailingslashit(WSOE_BASE) .'views/html-notice-scheduler-license.php';
				}

				echo ob_get_clean();
			}
		}

		/**
		 * If updater email is entered, schedule event.
		 */
		function schedule_license_check_event() {

			$prev_email =	$this->get_updater_email( true, false );
			$new_email	=	$this->get_updater_email( false, true );
			$cron_event =	wp_next_scheduled( $this->hook_name, array('email'=>$prev_email));

			$create_cron = ( ( empty($cron_event) || strcasecmp($prev_email, $new_email) != 0 ) && !empty($new_email) ) ? true : false;

			if( $create_cron ) {

				//Unschedule the event first, then only create new one.
				wp_clear_scheduled_hook( $this->hook_name, array('email'=>$prev_email) );

				//Schedule next event after 1 minute the settings are saved.
				wp_schedule_event( strtotime("now"), 'daily', $this->hook_name, array('email'=>$new_email) );
			}
		}

		/**
		 * Get updater email for addon/scheduler.
		 * 
		 * @param bool $prev Fetches old updater email.
		 * @param bool $new  Fetches newly updater email.
		 */
		private function get_updater_email( $prev = true, $new = false ){

			//If old email is to be retrieved.
			if( $prev ){

				$email = '';

				if( $this->file_id == 1 ){
					$email = get_option( 'edd-updater-email', '' );
				}else{
					$scheduler_settings = get_option( 'wsoe_scheduler_options', '' );
					$email = ( is_array($scheduler_settings) && array_key_exists('export_update_email', $scheduler_settings) ) ? $scheduler_settings['export_update_email'] : '';
				}
				
				return $email;
			}

			// If newly updated email is to be retrieved.
			if( $new ) {
				return ($this->file_id == 1) ? ( empty($_POST['edd_updater_email']) ? '' : $_POST['edd_updater_email'] ) : ( empty($_POST['wsoe_scheduler_update_email']) ? '' : $_POST['wsoe_scheduler_update_email'] );
			}				
		}

		/**
		 * Dismisses the add-on related license notice.
		 */
		function wsoe_addon_expired_dismiss() {
			delete_option( 'wsoe-addon-expired' );
		}

		/**
		 * Dismisses the scheduler related license notice.
		 */
		function wsoe_scheduler_expired_dismiss(){
			delete_option( 'wsoe-scheduler-expired' );
		}

		function dismiss_notice_event(){
			
			if( empty( self::$tracker['ran_dismiss_notice_event'] ) ){?>
				<script type="text/javascript">
					/**
					 * Dismiss license related notice.
					 */
					jQuery('body').on( 'click', '.wsoe-addon-expired .notice-dismiss, .wsoe-scheduler-expired .notice-dismiss', function() {

						var action = jQuery(this).parent().hasClass('wsoe-addon-expired') ? 'wsoe_addon_expired_dismiss' : 'wsoe_scheduler_expired_dismiss';

						console.log(action);
						jQuery.ajax({
							url: ajaxurl,
							data: {
								action: action
							},
							success:function(response){
								//console.log(response);
							}
						})

					});
				</script><?php
				self::$tracker['ran_dismiss_notice_event'] = true;
			}
		}
	}

	/**
	 * Function to initialise class.
	 */
	function wsoe_license_verify_notify() {

		$settings = WSOE()->settings;

		if( !empty($settings['extensions']['add-on']) ){
			//Add-on related options
			$options = array(
							'endpoint'=>'http://sharethingz.com',
							'slug'=>'woocommerce-simply-order-export-add-on',
							'option_name'=>'wsoe-addon-expired',
							'hook'=>'wsoe_addon_license_check',
							'file_id'=> 1,
							);
			new WSOE_License_Verify_Notify($options);
		}

		if( !empty($settings['extensions']['scheduler']) ){
			//Scheduler related options
			$options = array(
							'endpoint'=>'http://sharethingz.com',
							'slug'=>'wsoe-scheduler-logger',
							'option_name'=>'wsoe-scheduler-expired',
							'hook'=>'wsoe_scheduler_license_check',
							'file_id'=>0
							);
			new WSOE_License_Verify_Notify($options);
		}

	}
	add_action( 'init', 'wsoe_license_verify_notify', 12 );
}