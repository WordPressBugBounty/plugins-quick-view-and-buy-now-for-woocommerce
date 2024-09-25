<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Redirects To Checkout Class.
 */
class Checkout {

	/**
	 * Core Object
	 *
	 * @var object
	 */
	public static $core;

	/**
	 * Plugin Info
	 *
	 * @var object
	 */
	public static $plugin_info;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	private static $settings;

	/**
	 * Checkout Fields.
	 *
	 * @var array
	 */
	private $checkout_fields = array();

	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param object $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		self::$core        = $core;
		self::$plugin_info = $plugin_info;
		self::$settings    = Settings::get_main_settings();

		$this->hooks();
	}


	/**
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$this->checkout_fields = array(
			array(
				'title' => esc_html__( 'Checkout', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-checkout-settings-title',
			),
			array(
				'type' => self::$plugin_info['name'] . '-checkout-hidden-input',
			),
			array(
				'title'     => esc_html__( 'Cart in Checkout', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Show cart table in checkout page', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Customer will be able to update cart contents in the checkout page', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[checkout][show_cart_on_checkout]',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'value'     => self::$settings['checkout']['show_cart_on_checkout'],
				'name_keys' => array( 'checkout', 'show_cart_on_checkout' ),
			),
			array(
				'title'     => esc_html__( 'After Checkout Redirect', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Force custom redirect after checkout for orders that don\'t require payment.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '[checkout][redirect_after_checkout_without_payment]',
				'type'      => 'single_select_page_with_search',
				'class'     => 'wc-page-search',
				'css'       => 'min-width:300px;',
				'args'      => array(
					'exclude' => array(),
				),
				'value'     => self::$settings['checkout']['redirect_after_checkout_without_payment'],
				'name_keys' => array( 'checkout', 'redirect_after_checkout_without_payment' ),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			array(
				'title'     => esc_html__( 'After Checkout Redirect 2', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Force custom redirect after checkout for orders after payment.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '[checkout][redirect_after_checkout_with_payment]',
				'type'      => 'single_select_page_with_search',
				'class'     => 'wc-page-search',
				'css'       => 'min-width:300px;',
				'args'      => array(
					'exclude' => array(),
				),
				'value'     => self::$settings['checkout']['redirect_after_checkout_with_payment'],
				'name_keys' => array( 'checkout', 'redirect_after_checkout_with_payment' ),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
		);
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'woocommerce_before_checkout_form', array( $this, 'adjust_checkout_page' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'cart_table_in_checkout' ) );
		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_checkout_fields' ), 100, 1 );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-checkout-hidden-input', array( $this, 'checkout_settings_hidden_input' ) );
	}


	/**
	 * Adjust the Checkout page handler.
	 *
	 * @return void
	 */
	public function adjust_checkout_page() {
		if ( Settings::show_cart_in_checkout() ) {
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		}
	}

	/**
	 * Checkout Settings Hidden Input.
	 *
	 * @param array $field
	 * @return void
	 */
	public function checkout_settings_hidden_input( $field ) {
		?>
		<input type="hidden" value="1" name="<?php echo esc_attr( Settings::$settings_name ); ?>[]" />
		<?php
	}

	/**
	 * Front Assets for Checkout.
	 *
	 * @return void
	 */
	public function front_assets() {
		if ( is_checkout() && Settings::show_cart_in_checkout() ) {
			wp_enqueue_script( 'wc-cart' );
		}
	}

	/**
	 * Add Cart Table in Checkout Page.
	 *
	 * @return void
	 */
	public function cart_table_in_checkout() {
		if ( Settings::show_cart_in_checkout() ) :
			echo \WC_Shortcodes::cart();
		endif;
	}


	/**
	 * Checkout Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_checkout_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['checkout'] = $this->checkout_fields;
		return $fields;
	}
}
