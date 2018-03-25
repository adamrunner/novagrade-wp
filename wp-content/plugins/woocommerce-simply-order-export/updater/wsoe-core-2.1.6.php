<?php

$settings = WSOE()->settings;// Get plugin settings.

//If add-on is activated, schedule license check script
if( !empty($settings['extensions']['add-on']) ){

	$email_for_add_on = get_option( 'edd-updater-email', '' );

	if( !empty($email_for_add_on) ) {
		
		$cron_event =	wp_next_scheduled( 'wsoe_addon_license_check', array('email'=>$email_for_add_on));

		//Clear the scheduled event before	
		if( $cron_event ){
			wp_clear_scheduled_hook( 'wsoe_addon_license_check', array('email'=>$email_for_add_on) );
		}

		//Schedule next event after 1 minute the settings are saved.
		wp_schedule_event( strtotime("now"), 'daily', 'wsoe_addon_license_check', array('email'=>$email_for_add_on) );
		
	}
}

/**
 * If scheduler is installed and active, schedule event to check license.
 */
if( !empty($settings['extensions']['scheduler']) ){

	$scheduler_settings		=	get_option( 'wsoe_scheduler_options', '' );
	$email_for_scheduler	=	( is_array($scheduler_settings) && array_key_exists('export_update_email', $scheduler_settings) ) ? $scheduler_settings['export_update_email'] : '';

	if( !empty($email_for_scheduler) ) {
		
		$cron_event =	wp_next_scheduled( 'wsoe_scheduler_license_check', array('email'=>$email_for_scheduler));

		//Clear the scheduled event before.
		if( $cron_event ){
			wp_clear_scheduled_hook( 'wsoe_scheduler_license_check', array('email'=>$email_for_scheduler) );
		}

		//Schedule next event after 1 minute the settings are saved.
		wp_schedule_event( strtotime("now"), 'daily', 'wsoe_scheduler_license_check', array('email'=>$email_for_scheduler) );
		
	}
}