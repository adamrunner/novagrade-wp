<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Stamps_Settings class
 */
class WC_Stamps_Settings {

	const SETTINGS_NAMESPACE = 'stamps';

	/**
	 * Get the setting fields
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return array $setting_fields
	 */
	private static function get_fields() {
		$setting_fields = array(
			'account' => array(
				'name' => __( 'Stamps.com Account', 'woocommerce-shipping-stamps' ),
				'type' => 'title',
				'desc' => __( 'Input your Stamps.com account details so that the plugin can make requests on your behalf.', 'woocommerce-shipping-stamps' ),
				'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_account'
			),
			'stamps_useranme'   => array(
				'name'        => __( 'Username', 'woocommerce-shipping-stamps' ),
				'type'        => 'text',
				'desc'        => __( 'Use your Stamps.com credentials.', 'woocommerce-shipping-stamps' ),
				'id'          => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_username',
				'default'     => '',
			),
			'stamps_password'   => array(
				'name'        => __( 'Password', 'woocommerce-shipping-stamps' ),
				'type'        => 'password',
				'desc'        => __( 'Use your Stamps.com credentials.', 'woocommerce-shipping-stamps' ),
				'id'          => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_password',
				'default'     => '',
			),
			'logging'   => array(
				'name'    => __( 'Enable Request Logging', 'woocommerce-shipping-stamps' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Enables logging, used for debugging.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_logging',
				'default' => 'no',
			),
			'account_end'   => array(
				'type' => 'sectionend'
			),
			'auto_funding' => array(
				'name' => __( 'Automatic Funding', 'woocommerce-shipping-stamps' ),
				'type' => 'title',
				'desc' => __( 'These settings let you automatically purchase postage when your balance reaches a certain threshold.', 'woocommerce-shipping-stamps' ),
				'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_auto_funding'
			),
			'shipping_date'   => array(
				'name'    => __( 'Default Shipping Date', 'woocommerce-shipping-stamps' ),
				'type'    => 'select',
				'desc'    => __( 'Specifies the default shipping date when printing a label.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_shipping_date',
				'default' => '1',
				'options' => array(
					'0' => __( 'Same Day', 'woocommerce-shipping-stamps' ),
					'1'  => __( 'Next Day', 'woocommerce-shipping-stamps' ),
					'2'  => __( 'Two Days Later', 'woocommerce-shipping-stamps' ),
					'3'  => __( 'Three Days Later', 'woocommerce-shipping-stamps' ),
				)
			),
			'threshold'   => array(
				'name'        => __( 'Threshold', 'woocommerce-shipping-stamps' ),
				'placeholder' => __( 'n/a', 'woocommerce-shipping-stamps' ),
				'type'        => 'text',
				'desc'        => __( 'Top up when balance goes below this amount. Leave blank to disable.', 'woocommerce-shipping-stamps' ),
				'id'          => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_top_up_threshold',
				'default'     => '',
			),
			'purchase_amount'   => array(
				'name'        => __( 'Purchase Amount', 'woocommerce-shipping-stamps' ),
				'placeholder' => __( '0', 'woocommerce-shipping-stamps' ),
				'type'        => 'text',
				'desc'        => __( 'Purchase this much postage when the threshold is reached. Enter whole amount (integer) in dollars. E.g. <code>100</code>.', 'woocommerce-shipping-stamps' ),
				'id'          => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_purchase_amount',
				'default'     => '100',
			),
			'auto_funding_end'   => array(
				'type' => 'sectionend'
			),
			'labels' => array(
				'name' => __( 'Label Settings', 'woocommerce-shipping-stamps' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_labels'
			),
			'image_type'   => array(
				'name'    => __( 'Image Type', 'woocommerce-shipping-stamps' ),
				'type'    => 'select',
				'desc'    => __( 'Specifies the image type for the returned label.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_image_type',
				'default' => 'Pdf',
				'options' => array(
					'Auto' => __( 'Default format; PNG for domestic, PDF for international', 'woocommerce-shipping-stamps' ),
					'Epl'  => __( 'EPL', 'woocommerce-shipping-stamps' ),
					'Gif'  => __( 'GIF', 'woocommerce-shipping-stamps' ),
					'Jpg'  => __( 'JPG', 'woocommerce-shipping-stamps' ),
					'Pdf'  => __( 'PDF', 'woocommerce-shipping-stamps' ),
					'Png'  => __( 'PNG', 'woocommerce-shipping-stamps' ),
					'Zpl'  => __( 'ZPL', 'woocommerce-shipping-stamps' ),
				)
			),
			'paper_size'   => array(
				'name'    => __( 'Paper Size (PDF Labels only)', 'woocommerce-shipping-stamps' ),
				'type'    => 'select',
				'desc'    => __( 'Specifies the page size of PDF labels. This value only applies to PDF.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_paper_size',
				'default' => 'Default',
				'options' => array(
					'Default'     => __( 'Use default page size.', 'woocommerce-shipping-stamps' ),
					'Letter85x11' => __( 'Use letter page size.', 'woocommerce-shipping-stamps' ),
					'LabelSize'   => __( 'The page size is same as label size.', 'woocommerce-shipping-stamps' )
				)
			),
			'print_layout'   => array(
				'name'    => __( 'Print Layout (PDF Labels only)', 'woocommerce-shipping-stamps' ),
				'type'    => 'select',
				'desc'    => __( 'Specifies the print layout for labels.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_print_layout',
				'default' => '',
				'options' => array(
					'Normal'      => __( 'Default', 'woocommerce-shipping-stamps' ),
					'NormalLeft'  => __( '4x6 label generated on the left side of the page.', 'woocommerce-shipping-stamps' ),
					'NormalRight' => __( '4x6 label generated on the right side of the page.', 'woocommerce-shipping-stamps' ),
					'Normal4X6'   => __( '4x6 label generated on a 4x6 page.', 'woocommerce-shipping-stamps' ),
					'Normal6X4'   => __( '6x4 label generated on a 6x4 page.', 'woocommerce-shipping-stamps' ),
					'Normal75X2'  => __( '7.5x2 label generated on a 7.5x2 page.', 'woocommerce-shipping-stamps' ),
					'Normal4X675' => __( '4x6 3⁄4 doc-tab will be generated.', 'woocommerce-shipping-stamps' )
				)
			),
			'sample_only'   => array(
				'name'    => __( 'Create Samples Only', 'woocommerce-shipping-stamps' ),
				'type'    => 'checkbox',
				'desc'    => __( 'This will create sample labels which cannot be used for posting items. No payments will be taken.', 'woocommerce-shipping-stamps' ),
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_sample_only',
				'default' => 'yes',
			),
			'labels_end'   => array(
				'type' => 'sectionend'
			),
			'shipping_address' => array(
				'name' => __( 'Shipping Return Address', 'woocommerce-shipping-stamps' ),
				'type' => 'title',
				'desc' => __( 'This address is used for the "from" address when getting rates from Stamps.com.', 'woocommerce-shipping-stamps' ),
				'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_shipping_address'
			),
			'full_name'   => array(
				'name'    => __( 'Full Name', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_full_name',
				'default' => '',
			),
			'company'    => array(
				'name'    => __( 'Company', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_company',
				'default' => '',
			),
			'address_1'    => array(
				'name'    => __( 'Address Line 1', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_address_1',
				'default' => '',
			),
			'address_2'    => array(
				'name'    => __( 'Address Line 2', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_address_2',
				'default' => '',
			),
			'city'    => array(
				'name'    => __( 'City', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_city',
				'default' => '',
			),
			'state'    => array(
				'name'    => __( 'State', 'woocommerce-shipping-stamps' ),
				'type'    => 'select',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_state',
				'default' => '',
				'options' => WC()->countries->get_states( "US" )
			),
			'zip'    => array(
				'name'    => __( 'ZIP Code', 'woocommerce-shipping-stamps' ),
				'type'    => 'number',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_zip',
				'default' => '',
				'custom_attributes' => array(
					'maxlength' => 5,
					'max'       => 99999
				)
			),
			'phone'    => array(
				'name'    => __( 'Phone Number', 'woocommerce-shipping-stamps' ),
				'type'    => 'text',
				'desc'    => '',
				'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_phone',
				'default' => '',
			),
			'shipping_address_end'   => array(
				'type' => 'sectionend'
			)
		);

		/**
		 * Filter: 'wc_settings_tab_anti_fraud' - Allow altering extension setting fields
		 *
		 * @api array $setting_fields The fields
		 */

		return apply_filters( 'wc_settings_tab_' . self::SETTINGS_NAMESPACE, $setting_fields );
	}

	/**
	 * Get an option set in our settings tab
	 *
	 * @param $key
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return String
	 */
	public static function get_option( $key ) {
		$fields = self::get_fields();

		return apply_filters( 'wc_option_' . $key, get_option( 'wc_settings_' . self::SETTINGS_NAMESPACE . '_' . $key, ( ( isset( $fields[ $key ] ) ) ? $fields[ $key ] : '' ) ) );
	}

	/**
	 * Setup the WooCommerce settings
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'add_settings_tab' ), 70 );
		add_action( 'woocommerce_settings_tabs_' . self::SETTINGS_NAMESPACE, array( __CLASS__, 'tab_content' ) );
		add_action( 'woocommerce_update_options_' . self::SETTINGS_NAMESPACE, array( __CLASS__, 'update_settings' ) );
	}

	/**
	 * Add a settings tab to the settings page
	 *
	 * @param array $settings_tabs
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs[ self::SETTINGS_NAMESPACE ] = __( 'Stamps.com', 'woocommerce-shipping-stamps' );
		return $settings_tabs;
	}

	/**
	 * Output the tab content
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public static function tab_content() {
		if ( get_option( 'wc_settings_stamps_username' ) && get_option( 'wc_settings_stamps_password' ) && ! get_option( 'wc_settings_stamps_zip' ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'Shipping Return Address: Zip code is a required field. Please enter it on the %sStamps.com settings page%s.' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=stamps' ) ) . '">', '</a>' ) . '</p></div>';
		}
		woocommerce_admin_fields( self::get_fields() );
	}

	/**
	 * Update the settings
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public static function update_settings() {
		woocommerce_update_options( self::get_fields() );
	}
}

WC_Stamps_Settings::init();