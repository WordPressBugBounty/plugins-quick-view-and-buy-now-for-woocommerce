<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Quantity\Quantity_Input_1;
use GPLSCore\GPLS_PLUGIN_ARCW\Quantity\Quantity_Input_2;
use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Handle Quantity Input.
 */
class QuantityInput {

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
	 * Quantity Field.
	 *
	 * @var Quantity
	 */
	private $quantity_field;

	/**
	 * Quantity Input Fields.
	 *
	 * @var array
	 */
	private $quantity_input_fields = array();

	/**
	 * Custom Quantity Input Preview Check.
	 *
	 * @var string
	 */
	private static $custom_quantity_input_preview_check;

	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param object $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		self::$core                                = $core;
		self::$plugin_info                         = $plugin_info;
		self::$settings                            = Settings::get_main_settings();
		self::$custom_quantity_input_preview_check = self::$plugin_info['name'] . '-custom-quantity-input-preview';
		$this->hooks();
	}

	/**
	 * Check is preview.
	 *
	 * @return boolean
	 */
	private static function is_preview() {
		return ( ! empty( $GLOBALS[ self::$custom_quantity_input_preview_check ] ) );
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'woocommerce_before_quantity_input_field', array( $this, 'custom_quantity_input_minus_button' ) );
		add_action( 'woocommerce_after_quantity_input_field', array( $this, 'custom_quantity_input_plus_button' ), 10 );
		add_filter( 'woocommerce_quantity_input_classes', array( $this, 'add_our_class_to_quantity_input' ), 100, 2 );
		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_quantity_input_fields' ), 100, 1 );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-quantity-input-type-settings-field', array( $this, 'quantity_input_type_settings_field' ) );
	}

	/**
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$this->quantity_input_fields = array(
			array(
				'title' => esc_html__( 'Quantity input settings', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-quick-view-settings-quantity-input-title',
			),
			array(
				'type'      => self::$plugin_info['name'] . '-quantity-input-type-settings-field',
				'title'     => esc_html__( 'Quantity input type', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Use custom quantity input style', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '-quantity-type',
				'class'     => self::$plugin_info['classes_prefix'] . '-quantity-input-type',
				'default'   => 1,
				'options'   => array(
					1 => esc_html__( 'Type 1', 'quick-view-and-buy-now-for-woocommerce' ),
					2 => esc_html__( 'Type 2', 'quick-view-and-buy-now-for-woocommerce' ),
				),
				'value'     => 1,
			),
			array(
				'title'     => esc_html__( 'Loop Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Enable the custom quantity input for Buy Now button in shop and archive pages.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '-quantity-loop_buy_now-',
				'type'      => 'checkbox',
				'default'   => 'checkout',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'value'     => 'no',
			),
			array(
				'title'     => esc_html__( 'Quick View popup', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Enable the custom quantity input in quick view popup.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '-quantity-quick_view_popup',
				'type'      => 'checkbox',
				'default'   => 'checkout',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'value'     => 'no',
			),
			array(
				'title'     => esc_html__( 'Single', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Enable the custom quantity input in single product page.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '-quantity-single',
				'type'      => 'checkbox',
				'default'   => 'checkout',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'value'     => 'no',
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
			array(
				'title' => esc_html__( 'Styles', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'  => esc_html__( 'Style the quantity input', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-quantity-input-styles-title',
			),
			array(
				'title'     => esc_html__( 'Plus Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Plus button custom color', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'      => 'text',
				'css'       => 'width:100px;',
				'id'        => Settings::$settings_name . '-quantity-plus_color',
				'class'     => self::$plugin_info['classes_prefix'] . '-color-picker ' . self::$plugin_info['classes_prefix'] . '-quantity-input-style',
				'custom_attributes' => array(
					'data-css' => 'color',
					'data-target' => '.' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-plus',
					'data-default' => '#000',
					'data-value'   => '#000',
				),
				'value'     => 'no',
			),
			array(
				'desc'      => esc_html__( 'Plus button custom background color', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'      => 'text',
				'css'       => 'width:100px;',
				'id'        => Settings::$settings_name . '-quantity-plus_bg',
				'class'     => self::$plugin_info['classes_prefix'] . '-color-picker ' . self::$plugin_info['classes_prefix'] . '-quantity-input-style',
				'custom_attributes' => array(
					'data-css' => 'background-color',
					'data-target' => '.' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-plus',
					'data-default' => '#EEE',
					'data-value'   => '#EEE',
				),
				'value'     => 'no',
			),
			array(
				'title'     => esc_html__( 'Minus Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Minus button custom color', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'      => 'text',
				'css'       => 'width:100px;',
				'id'        => Settings::$settings_name . '-quantity-minus_color',
				'class'     => self::$plugin_info['classes_prefix'] . '-color-picker ' . self::$plugin_info['classes_prefix'] . '-quantity-input-style',
				'custom_attributes' => array(
					'data-css' => 'color',
					'data-target' => '.' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-minus',
					'data-default' => '#000',
					'data-value'   => '#000',

				),
				'value'     => 'no',
			),
			array(
				'desc'      => esc_html__( 'Minus button custom background color', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'      => 'text',
				'css'       => 'width:100px;',
				'id'        => Settings::$settings_name . '-quantity-minus_bg',
				'class'     => self::$plugin_info['classes_prefix'] . '-color-picker ' . self::$plugin_info['classes_prefix'] . '-quantity-input-style',
				'custom_attributes' => array(
					'data-css' => 'background-color',
					'data-target' => '.' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-button-minus',
					'data-default' => '#EEE',
					'data-value'   => '#EEE',
				),
				'value'     => 'no',
			),

			array(
				'name' => '',
				'type' => 'sectionend',
			),
		);

	}

	/**
	 * Minus button for custom Quantity Input.
	 *
	 * @return void
	 */
	public function custom_quantity_input_minus_button() {
		if ( ! self::is_preview() ) {
			return;
		}
		$this->quantity_field->minus_field();
	}


	/**
	 * Plus Button for Custom Quantity Input.
	 *
	 * @return void
	 */
	public function custom_quantity_input_plus_button() {
		if ( ! self::is_preview() ) {
			return;
		}
		$this->quantity_field->plus_field();
	}

	/**
	 * Add Screen Loader Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_quantity_input_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['quantity'] = $this->quantity_input_fields;
		return $fields;
	}

	/**
	 * Quantity Input Type Settings Field.
	 *
	 * @param array $value
	 * @return void
	 */
	public function quantity_input_type_settings_field( $value ) {
		$field_description = \WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$option_value      = Settings::get_setting( 'quantity', 'type' );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<select
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					>
					<?php
					foreach ( $value['options'] as $key => $val ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $option_value, (string) $key ); ?> ><?php echo esc_html( $val ); ?></option>
						<?php
					}
					?>
				</select> <?php echo $description; // WPCS: XSS ok. ?>
				<?php
				foreach ( $value['options'] as $key => $val ) {
					$this->custom_quantity_input_preview( $key );
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add Our Classes to Quantity Input.
	 *
	 * @param array  $classes_arr CLasses Array.
	 * @param object $product    Product Object.
	 * @return array
	 */
	public function add_our_class_to_quantity_input( $classes_arr, $product ) {
		$classes_arr[] = self::$plugin_info['classes_prefix'] . '-quantity-input';
		if ( AddToCart::is_a_variation_in_grouped( $product ) ) {
			$classes_arr[] = self::$plugin_info['classes_prefix'] . '-variation-in-grouped-input';
		}
		return $classes_arr;
	}

	/**
	 * Get Custom Quantity Input HTML for Preview.
	 *
	 * @param integer $index
	 * @return void
	 */
	private function custom_quantity_input_preview( $index = 1 ) {
		// Set Preview check.
		$GLOBALS[ self::$custom_quantity_input_preview_check ] = true;

		// Setup Quantity Input CLass.
		$class_name           = __NAMESPACE__ . '\Quantity\Quantity_Input_' . $index;
		$this->quantity_field = new $class_name( self::$core, self::$plugin_info );
		$option_value         = 1;

		$defaults = array(
			'main_class'   => ( $option_value != $index ? 'hidden' : '' ) . ' ' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-preview-wrapper ' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-wrapper ' . self::$plugin_info['classes_prefix'] . '-custom-quantity-input-wrapper-' . $index,
			'input_id'     => uniqid( 'quantity_' ),
			'input_name'   => 'quantity',
			'input_value'  => '1',
			'classes'      => array( 'input-text', 'qty', 'text', $this->quantity_field->get_quantity_class() ),
			'max_value'    => -1,
			'min_value'    => 1,
			'step'         => 1,
			'pattern'      => '[0-9]*',
			'inputmode'    => 'numeric',
			'autocomplete' => 'off',
			'placeholder'  => '',
		);

		wc_get_template(
			'default-quantity-input.php',
			array(
				'plugin_info' => self::$plugin_info,
				'args'        => $defaults,
			),
			self::$plugin_info['path'] . 'templates/global',
			self::$plugin_info['path'] . 'templates/global/'
		);

		// Clear Check.
		unset( $GLOBALS[ self::$custom_quantity_input_preview_check ] );
	}

}
