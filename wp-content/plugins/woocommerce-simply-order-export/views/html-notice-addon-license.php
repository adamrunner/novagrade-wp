<div class="notice notice-info <?php echo ($this->file_id ==1) ? 'wsoe-addon-expired' : 'wsoe-scheduler-expired'; ?> is-dismissible">
	<p><?php _e( sprintf('<strong>WooCommerce Simply Order Export Add-on:</strong> Your license has been expired and you no longer would be able to receive updates. Please renew your license!'), 'woocommerce-simply-order-export-add-on'); ?></p>
	<p>
		<a target="_blank" href="<?php echo !empty($is_expired->renew_link) ? $is_expired->renew_link : '#';?>" class="button button-primary"><?php _e( 'Renew my license', 'woocommerce-simply-order-export-add-on'); ?></a>
		<a target="_blank" href="<?php echo !empty($is_expired->renew_doc_link) ? $is_expired->renew_doc_link : '#';?>" class="button"><?php _e( 'How to renew license?', 'woocommerce-simply-order-export-add-on'); ?></a>
	</p>
</div>