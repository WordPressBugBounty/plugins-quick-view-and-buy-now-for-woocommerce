<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW\Quantity;

/**
 * Quantity Input 1 Class.
 */
class Quantity_Input_1 extends QuantityBase {

	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param object $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		parent::__construct( $core, $plugin_info );
	}


	/**
	 * Set Quantity Input Wrapper Class.
	 *
	 * @return void
	 */
	protected function set_wrapper_class() {
		$this->wrapper_class .= ' ' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-wrapper-1';
	}

	/**
	 * Set Quantity Input Class.
	 *
	 * @return void
	 */
	protected function set_quantity_class() {

	}

	/**
	 * Plus Button.
	 *
	 * @return void
	 */
	public function plus_field() {
		?>
		<input type="button" value="+" class="<?php echo esc_attr( $this->plus_class ); ?>">
		<?php
	}

	/**
	 * Minus Button.
	 *
	 * @return void
	 */
	public function minus_field() {
		?>
		<input type="button" value="-" class="<?php echo esc_attr( $this->minus_class ); ?>">
		<?php
	}
}
