<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW\Quantity;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Quantity Input Base Class.
 */
abstract class QuantityBase {

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
	protected static $settings;

	/**
	 * Minus Button Class.
	 *
	 * @var string
	 */
	protected $minus_class;

	/**
	 * Plus Button CLass.
	 *
	 * @var string
	 */
	protected $plus_class;

	/**
	 * Quantity Wrapper Class.
	 *
	 * @var string
	 */
	protected $wrapper_class;

	/**
	 * Quantity Input Class.
	 *
	 * @var string
	 */
	protected $input_class;

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

		$this->setup();
	}

	/**
	 * Setup Quantity Input.
	 *
	 * @return void
	 */
	private function setup() {
		$this->wrapper_class = self::$plugin_info['classes_prefix'] . '-custom-quantity-input-wrapper';
		$this->minus_class   = self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-minus';
		$this->plus_class    = self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-plus';
		$this->input_class   = self::$plugin_info['classes_prefix'] . '-custom-quantity-input';

		$this->set_wrapper_class();
		$this->set_quantity_class();
	}

	/**
	 * Get Quantity Wrapper Class.
	 *
	 * @return string
	 */
	public function get_wrapper_class() {
		return $this->wrapper_class;
	}

	/**
	 * Get Quantity Class.
	 *
	 * @return string
	 */
	public function get_quantity_class() {
		return $this->input_class;
	}

	/**
	 * Set Quantity Wrapper Class.
	 *
	 * @return void
	 */
	abstract protected function set_wrapper_class();

	/**
	 * Set Quantity Input Field CLass.
	 *
	 * @return void
	 */
	abstract protected function set_quantity_class();

	/**
	 * Plus Button Field.
	 *
	 * @return void
	 */
	abstract public function plus_field();

	/**
	 * Minus Button Field.
	 *
	 * @return void
	 */
	abstract public function minus_field();

}
