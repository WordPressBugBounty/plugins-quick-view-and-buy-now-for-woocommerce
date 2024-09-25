<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Direct Purchase Feature Class.
 */
class DirectPurchase {

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
	 * Direct Purchase Fields.
	 *
	 * @var array
	 */
	private $fields = array();

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
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_direct_purchase_fields' ), 100, 1 );
	}

	/**
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$this->fields = array(
			array(
				'title' => esc_html__( 'Direct Purchase', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-direct-purchase-settings-title',
				'desc'  => $this->direct_purchase_description(),
			),
			array(
				'title'             => esc_html__( 'Enable Single Purchase', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'              => esc_html__( 'Enable Single Purchase by Category', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'                => Settings::$settings_name . '[direct_purchase][enable_by_category]',
				'type'              => 'multiselect',
				'class'             => 'input-select wc-category-search',
				'value'             => self::$settings['direct_purchase']['enable_by_category'],
				'name_keys'         => array( 'direct_purchase', 'enable_by_category' ),
				'options'           => array(),
				'custom_attributes' => array(
					'data-return_id' => 'id',
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
	 * Direct Purchase Explanation.
	 *
	 * @return string
	 */
	private function direct_purchase_description() {
		ob_start();
		?>
		<h5><?php esc_html_e( 'Direct single purchase allows purchasing a product alone in isolated way separated from other products.', 'quick-view-and-buy-now-for-woocommerce' ); ?><?php self::$core->pro_btn( '', 'Premium', '', '', false ); ?></h5>
		<?php
		return ob_get_clean();
	}

	/**
	 * Direct Purchase Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_direct_purchase_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['direct_purchase'] = $this->fields;
		return $fields;
	}

}
