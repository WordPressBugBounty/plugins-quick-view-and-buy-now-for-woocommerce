<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;
use GPLSCore\GPLS_PLUGIN_ARCW\Single;
use GPLSCore\GPLS_PLUGIN_ARCW\AddToCart;

/**
 * BuyNow Feature Class.
 */
class Buynow {

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
	 * Buy Now Settings Fields.
	 *
	 * @var array
	 */
	private $buy_now_fields = array();


	/**
	 * Product Types for Buy Now Button.
	 *
	 * @var array
	 */
	protected static $loop_buy_now_product_types = array(
		'simple'      => 'Simple',
		'yith_bundle' => 'YITH Bundle',
	);

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
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		// Set "Buy Now" Button before or after Add to Cart Button in Single - Popup Product Page.
		add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'buy_now_before_add_to_cart_in_single_product' ), PHP_INT_MAX );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'buy_now_before_add_to_cart_in_grouped_and_external_product' ), PHP_INT_MAX );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'buy_now_after_add_to_cart_in_single_product' ), 1 );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'buy_now_before_add_to_cart_yith_product_bundle_in_single_product' ), 11 );

		// Clear product in grouped GLobal after grouped products list.
		add_action( 'woocommerce_grouped_product_list_after', array( $this, 'clear_grouped_products_list_checks' ), PHP_INT_MAX, 3 );

		// Buy Now Button Shortcode.
		add_action( 'init', array( $this, 'buy_now_button_shortcode' ) );

		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_buy_now_fields' ), 100, 1 );

	}

	/**
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$product_types        = wc_get_product_types();
		$this->buy_now_fields = array(
			array(
				'title' => esc_html__( 'Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'  => esc_html__( '"Buy Now" Button in "Quick View" popup and single product page', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-buy-now-settings-title',
			),
		);

		unset( $product_types['external'] );
		foreach ( $product_types as $product_type_key => $product_key_label ) {
			$custom_attrs = array();
			if ( 'yith_bundle' === $product_type_key ) {
				$product_key_label        = 'YITH Product Bundle';
				$custom_attrs['disabled'] = 'disabled';
			}
			$field_arr = array(
				'desc'              => sprintf( esc_html__( '%1$s ( %2$s )', 'quick-view-and-buy-now-for-woocommerce' ), ( 'yith_bundle' === $product_type_key ? 'YITH Product Bundles' : $product_key_label ), $product_type_key ) . ( 'yith_bundle' === $product_type_key ? self::$core->pro_btn( '', 'Premium', '', '', true ) : '' ),
				'desc_tip'          => sprintf( esc_html__( 'Enable / Disable "Buy Now" button for %s products', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label ),
				'id'                => Settings::$settings_name . '[buy_now][enable_by_product_type_' . $product_type_key . ']',
				'type'              => 'checkbox',
				'checkboxgroup'     => ( array_key_first( $product_types ) === $product_type_key ) ? 'start' : ( array_key_last( $product_types ) === $product_type_key ? 'end' : '' ),
				'custom_attributes' => $custom_attrs,
				'class'             => 'input-checkbox',
				'value'             => 'yith_bundle' === $product_type_key ? 'no' : self::$settings['buy_now'][ 'enable_by_product_type_' . $product_type_key ],
				'name_keys'         => array( 'buy_now', 'enable_by_product_type_' . $product_type_key ),
			);

			if ( array_key_first( $product_types ) === $product_type_key ) {
				$field_arr['title'] = esc_html__( 'Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' );
			}
			$this->buy_now_fields[] = $field_arr;
		}

		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Ajax Buy Now', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'  => esc_html__( 'Disable this option in case "Buy Now" doesn\'t work with your current theme/plugins. Buy Now will use the regular form submit', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Use AJAX for Buy Now Button.', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => Settings::$settings_name . '[buy_now][buy_now_ajax]',
			'type'      => 'checkbox',
			'class'     => 'input-checkbox',
			'value'     => self::$settings['buy_now']['buy_now_ajax'],
			'name_keys' => array( 'buy_now', 'buy_now_ajax' ),
		);
		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Buy Now Title', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => Settings::$settings_name . '[buy_now][buy_now_text]',
			'type'      => 'text',
			'class'     => 'input-text',
			'default'   => esc_html__( 'Buy Now', 'quick-view-and-buy-now-for-woocommerce' ),
			'value'     => self::$settings['buy_now']['buy_now_text'],
			'name_keys' => array( 'buy_now', 'buy_now_text' ),
		);
		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Buy Now Button Position', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => Settings::$settings_name . '[buy_now][buy_now_position]',
			'type'      => 'select',
			'default'   => 'before',
			'options'   => array(
				'before' => esc_html__( 'Before Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'after'  => esc_html__( 'after Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			'value'     => self::$settings['buy_now']['buy_now_position'],
			'name_keys' => array( 'buy_now', 'buy_now_position' ),
		);
		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Redirect to', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'  => esc_html__( 'Redirect the customer after Buy Now', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => Settings::$settings_name . '[buy_now][redirect_after]',
			'type'      => 'select',
			'default'   => 'checkout',
			'options'   => array(
				'cart'     => esc_html__( 'Cart', 'quick-view-and-buy-now-for-woocommerce' ),
				'checkout' => esc_html__( 'Checkout', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			'value'     => self::$settings['buy_now']['redirect_after'],
			'name_keys' => array( 'buy_now', 'redirect_after' ),
		);
		$this->buy_now_fields[] = array(
			'type' => self::$plugin_info['name'] . '-buy-now-button-shortcode-details',
		);
		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Text Color', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Button Text Color', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->new_keyword( 'New', true ),
			'id'         => Settings::$settings_name . '[buy_now][color]',
			'type'      => 'text',
			'class'     => 'wp-color-picker',
			'default'   => '',
			'css'       => 'width:6em;',
			'value'     => self::$settings['buy_now']['color'],
			'name_keys' => array( 'buy_now', 'color' ),
		);
		$this->buy_now_fields[] = array(
			'title'     => esc_html__( 'Background Color', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Button Background Color', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->new_keyword( 'New', true ),
			'id'        => Settings::$settings_name . '[buy_now][bg]',
			'type'      => 'text',
			'class'     => 'wp-color-picker',
			'default'   => '',
			'css'       => 'width:6em;',
			'value'     => self::$settings['buy_now']['bg'],
			'name_keys' => array( 'buy_now', 'bg' ),
		);
		$this->buy_now_fields[] = array(
			'name' => '',
			'type' => 'sectionend',
		);
		$this->buy_now_fields[] = array(
			'title' => esc_html__( 'Loop Buy Now', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'  => esc_html__( '"Buy Now" Button in shop and archive pages', 'quick-view-and-buy-now-for-woocommerce' ),
			'type'  => 'title',
			'id'    => self::$plugin_info['name'] . '-buy-now-settings-title',
		);

		foreach ( self::$loop_buy_now_product_types as $product_type_key => $product_key_label ) {
			$field_arr = array(
				'desc'              => sprintf( esc_html__( '%1$s ( %2$s )', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label, $product_type_key ),
				'desc_tip'          => sprintf( esc_html__( 'Enable / Disable "Buy Now" button for %s products in shop and archive pages', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'                => Settings::$settings_name . '-loop_buy_now-product_type_' . $product_type_key,
				'type'              => 'checkbox',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'checkboxgroup'     => ( array_key_first( $product_types ) === $product_type_key ) ? 'start' : ( array_key_last( $product_types ) === $product_type_key ? 'end' : '' ),
				'class'             => 'input-checkbox',
				'value' => 'no',
			);

			if ( array_key_first( $product_types ) === $product_type_key ) {
				$field_arr['title'] = esc_html__( 'Loop Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' );
			}
			$this->buy_now_fields[] = $field_arr;
		}

		$this->buy_now_fields[] = array(
			'title'             => esc_html__( 'Hide Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'          => esc_html__( 'Hide add to cart button and show "Buy Now" button only', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
			'id'                => Settings::$settings_name . '-loop_buy_now-hide_add_to_cart',
			'type'              => 'checkbox',
			'class'             => 'input-checkbox',
			'custom_attributes' => array(
				'disabled' => 'disabled',
			),
			'value' => 'no',
		);
		$this->buy_now_fields[] = array(
			'name' => '',
			'type' => 'sectionend',
		);
	}

	/**
	 * Buy Now Button Shortcode Register.
	 *
	 * @return void
	 */
	public function buy_now_button_shortcode() {
		add_shortcode( self::$plugin_info['classes_prefix'] . '-buy-now-button', array( $this, 'buy_now_button_shortcode_content' ) );
	}

	/**
	 * Buy Now Button Shortcode HTML.
	 *
	 * @return string
	 */
	public function buy_now_button_shortcode_content( $attrs ) {
		global $product;

		$product_id = 0;

		if ( is_null( $product ) || is_wp_error( $product ) ) {
			return;
		}

		if ( is_a( $product, '\WC_Product' ) ) {
			$product_id = $product->get_id();
		}

		$params = array(
			'class'      => 'single_add_to_cart_button single_buy_now button alt ' . self::$plugin_info['classes_prefix'] . '-buy-now ' . self::$plugin_info['classes_prefix'] . '-' . $product->get_type() . '-buy-now ' . self::$plugin_info['classes_prefix'] . '-buy-now-button-shortcode',
			'attributes' => array(
				'name'            => self::$plugin_info['name'] . '-buy-now',
				'type'            => 'button',
				'data-product_id' => $product_id,
			),
		);
		ob_start();
		Single::buy_now_button(
			$product,
			$params,
			true
		);
		return ob_get_clean();
	}

	/**
	 * Clear the "Product in Grouped" checks after the list.
	 *
	 * @param array  $grouped_product_cols
	 * @param array  $quantities_required
	 * @param object $_product
	 * @return void
	 */
	public function clear_grouped_products_list_checks( $grouped_product_cols, $quantities_required, $_product ) {
		unset( $_POST[ self::$plugin_info['name'] . '-variation-in-grouped' ] );
		unset( $_POST[ self::$plugin_info['name'] . '-product-in-grouped' ] );
	}

	/**
	 * Add Buy Now Button before add to cart button.
	 *
	 * @return void
	 */
	public function buy_now_before_add_to_cart_in_single_product() {
		global $product;

		if ( is_null( $product ) || is_wp_error( $product ) ) {
			return;
		}

		// Skip external and grouped.
		if ( 'external' === $product->get_type() || 'grouped' === $product->get_type() ) {
			return;
		}

		if ( ! Popup::is_variable_popup_in_grouped_popup_request() && ! AddToCart::is_variable_in_grouped_single_request() && ! AddToCart::is_a_product_in_grouped() && Single::is_buy_now_button_enabled( $product ) && 'before' === Single::buy_now_position( $product ) ) {
			Single::buy_now_button(
				$product,
				array(
					'class'      => 'single_add_to_cart_button single_buy_now button alt ' . wc_wp_theme_get_element_class_name( 'button' ) . ' ' . self::$plugin_info['classes_prefix'] . '-buy-now ' . self::$plugin_info['classes_prefix'] . '-' . $product->get_type() . '-buy-now',
					'attributes' => array(
						'name'            => self::$plugin_info['name'] . '-buy-now',
						'type'            => 'button',
						'data-product_id' => $product->get_id(),
					),
				),
				true
			);
		}
	}

	/**
	 * Add Buy Now Button Before add to cart button [ Grouped - External ].
	 *
	 * @return void
	 */
	public function buy_now_before_add_to_cart_in_grouped_and_external_product() {
		global $product, $post;
		$_product = $product;

		if ( is_null( $product ) || is_wp_error( $product ) ) {
			return;
		}

		if ( 'grouped' === $_product->get_type() ) {

			if ( ! Popup::is_variable_popup_in_grouped_popup_request() && ! AddToCart::is_variable_in_grouped_single_request() && ! AddToCart::is_a_product_in_grouped() && Single::is_buy_now_button_enabled( $_product ) && 'before' === Single::buy_now_position( $_product ) ) {
				Single::buy_now_button(
					$_product,
					array(
						'class'      => 'single_add_to_cart_button single_buy_now button alt ' . wc_wp_theme_get_element_class_name( 'button' ) . ' ' . self::$plugin_info['classes_prefix'] . '-buy-now ' . self::$plugin_info['classes_prefix'] . '-' . $_product->get_type() . '-buy-now',
						'attributes' => array(
							'name'            => self::$plugin_info['name'] . '-buy-now',
							'type'            => 'button',
							'data-product_id' => $_product->get_id(),
						),
					),
					true
				);
			}
		}
	}

	/**
	 * Add Buy Now Button after add to cart button.
	 *
	 * @return void
	 */
	public function buy_now_after_add_to_cart_in_single_product() {
		global $product;

		if ( is_null( $product ) || is_wp_error( $product ) ) {
			return;
		}

		// SKip External product.
		if ( 'external' === $product->get_type() ) {
			return;
		}

		if ( ! Popup::is_variable_popup_in_grouped_popup_request() && ! AddToCart::is_variable_in_grouped_single_request() && ! AddToCart::is_a_product_in_grouped() && Single::is_buy_now_button_enabled( $product ) && 'after' === Single::buy_now_position( $product ) ) {

			Single::buy_now_button(
				$product,
				array(
					'class'      => 'single_add_to_cart_button single_buy_now button alt ' . wc_wp_theme_get_element_class_name( 'button' ) . ' ' . self::$plugin_info['classes_prefix'] . '-buy-now ' . self::$plugin_info['classes_prefix'] . '-' . $product->get_type() . '-buy-now',
					'attributes' => array(
						'name'            => self::$plugin_info['name'] . '-buy-now',
						'type'            => 'button',
						'data-product_id' => $product->get_id(),
					),
				),
				true
			);
		}
	}

	/**
	 * Buy Now Button before Add To Cart in single product [ YITH Bundle Product ].
	 *
	 * Place the BuyNow button just after quantity input due to failed action hook do_action( 'woocommerce_after_add_to_cart_quantity' ) in their add-to-cart template.
	 *
	 * @return void
	 */
	public function buy_now_before_add_to_cart_yith_product_bundle_in_single_product() {

		if ( is_plugin_active( 'yith-woocommerce-product-bundles/init.php' ) ) {
			global $product;

			if ( is_null( $product ) || is_wp_error( $product ) ) {
				return;
			}

			if ( ! is_singular( $product ) ) {
				return;
			}

			if ( 'yith_bundle' !== $product->get_type() ) {
				return;
			}

			$this->buy_now_before_add_to_cart_in_single_product();
		}
	}

	/**
	 * Get Loop Buy Now Product Types.
	 *
	 * @return array
	 */
	public static function get_loop_buy_now_product_types() {
		return self::$loop_buy_now_product_types;
	}

	/**
	 * Buy Now Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_buy_now_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['buy_now'] = $this->buy_now_fields;
		return $fields;
	}

}
