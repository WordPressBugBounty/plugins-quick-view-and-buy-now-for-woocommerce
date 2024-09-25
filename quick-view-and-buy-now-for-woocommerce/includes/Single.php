<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;
use GPLSCore\GPLS_PLUGIN_ARCW\AddToCart;

/**
 * Single Product Class.
 */
class Single {

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
	 * Auto redirect to checkout Option Name.
	 *
	 * @var string
	 */
	private static $option_name = '';

	/**
	 * Default Settings.
	 *
	 * @var array
	 */
	private static $default_settings = array(
		'loop_quantity_input'         => 'no',
		'quick_view'                  => 'inherit',
		'single_buy_now'              => 'inherit',
		'hide_add_to_cart_loop'       => 'no',
		'hide_add_to_cart_single'     => 'no',
		'hide_add_to_cart_popup'      => 'no',
		'add_to_cart_text'            => '',
		'quick_view_text'             => '',
		'buy_now_text'                => '',
		'buy_now_custom_redirect'     => '',
		'add_to_cart_custom_redirect' => '',
		'auto_redirect_to_checkout'   => false,
	);

	/**
	 * Main Settings.
	 *
	 * @var array
	 */
	private static $main_settings;

	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param object $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		self::$core          = $core;
		self::$plugin_info   = $plugin_info;
		self::$option_name   = self::$plugin_info['name'] . '-single-settings-option';
		self::$main_settings = Settings::get_main_settings();
		$this->hooks();
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'woocommerce_product_data_panels', array( $this, 'quick_view_and_buy_now_option' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'single_product_settings_tab' ), 100, 1 );
		add_filter( self::$plugin_info['name'] . '-localize-vars-arr', array( $this, 'localize_single_product_params' ), 100, 1 );
		// Filter redirect URL after add to cart for other plugin.
		add_filter( self::$plugin_info['general_prefix'] . '-redirect-after-ajax-add-to-cart', array( $this, 'get_custom_redirect_after_add_to_cart' ), 99, 2 );
	}

	/**
	 * Single Product Settings Tab.
	 *
	 * @param array $tabs Settings Tabs.
	 * @return array
	 */
	public function single_product_settings_tab( $tabs ) {
		$tabs[ self::$plugin_info['name'] . '-settings_product_data' ] = array(
			'label'    => esc_html__( 'Quick View and Buy Now [Premium]', 'quick-view-and-buy-now-for-woocommerce' ),
			'target'   => self::$plugin_info['name'] . '-settings_product_data',
			'class'    => array(),
			'priority' => 80,
		);
		return $tabs;
	}

	/**
	 * Localize Single Product JS params.
	 *
	 * @param array $localize_arr Localize Array.
	 * @return array
	 */
	public function localize_single_product_params( $localize_arr ) {
		if ( is_product() && ! Popup::is_quick_view_popup_request() ) {
			global $wp_query;
			$product_id  = $wp_query->get_queried_object_id();
			$product_obj = wc_get_product( $product_id );
			if ( ! is_null( $product_obj ) && is_object( $product_obj ) ) {
				$localize_arr['single_product_params'] = array(
					'add_to_cart_status' => self::add_to_cart_status( $product_obj, 'single' ),
				);
			}
		}
		return $localize_arr;
	}

	/**
	 * Add A checkbox for auto redirect to Checkout page for this product.
	 *
	 * @return void
	 */
	public function quick_view_and_buy_now_option() {
		global $post, $thspostid, $product_object;
		?>
		<div id="<?php echo esc_attr( self::$plugin_info['name'] . '-settings_product_data' ); ?>" class="panel woocommerce_options_panel">
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . '-premium-version' ); ?>">
				<h3 style="margin-left:15px;"><span><?php esc_html_e( 'These features are part of the premium version', '' ); ?></span> <span><?php self::$core->pro_btn( '', 'premium', '', 'display:inline-block;padding: 5px 10px;vertical-align: middle;border-radius: 5px;' ); ?></span></h3>
			</div>
			<?php
			if ( 'simple' === $product_object->get_type() ) :
				?>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . '-loop-quantity-input' ); ?>">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'                => self::$option_name . '[loop_quantity_input]',
						'default'           => 'no',
						'class'             => self::$plugin_info['classes_prefix'] . '-loop-quantity-input-field',
						'value'             => 'no',
						'description'       => esc_html__( 'Display Quantity Input field for the product in shop and archive pages ( works with refresh and ajax add to cart ).', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( 'Loop Quantity Input', 'quick-view-and-buy-now-for-woocommerce' ),
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>
			</div>
			<?php endif; ?>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . '-quick-view-button' ); ?>">
				<?php
				woocommerce_wp_radio(
					array(
						'id'                => self::$option_name . '[quick_view]',
						'options'           => array(
							'enable'  => esc_html__( 'Enable', 'quick-view-and-buy-now-for-woocommerce' ),
							'disable' => esc_html__( 'Disable', 'quick-view-and-buy-now-for-woocommerce' ),
							'inherit' => esc_html__( 'Follow General Settings', 'quick-view-and-buy-now-for-woocommerce' ),
						),
						'class'             => 'disabled',
						'value'             => 'inherit',
						'description'       => esc_html__( 'Enable / Disable "Quick View" Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( '"Quick View" Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'default'           => 'inherit',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>
			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . 'buy-now-button' ); ?>">
				<?php
				woocommerce_wp_radio(
					array(
						'id'                => self::$option_name . '[single_buy_now]',
						'options'           => array(
							'enable'  => esc_html__( 'Enable', 'quick-view-and-buy-now-for-woocommerce' ),
							'disable' => esc_html__( 'Disable', 'quick-view-and-buy-now-for-woocommerce' ),
							'inherit' => esc_html__( 'Follow General Settings', 'quick-view-and-buy-now-for-woocommerce' ),
						),
						'class'             => 'disabled',
						'value'             => 'inherit',
						'description'       => esc_html__( 'Enable / Disable "Buy Now" Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( '"Buy Now" Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'default'           => 'inherit',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>
			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . 'buttons-manipulate' ); ?>">
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'                => self::$option_name . '[hide_add_to_cart_loop]',
						'value'             => 'no',
						'class'             => self::$plugin_info['classes_prefix'] . '-hide-add-to-cart-loop-field',
						'description'       => esc_html__( 'Remove "Add to cart" Button in shop and archive pages', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( 'Shop Add to cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'                => self::$option_name . '[hide_add_to_cart_popup]',
						'value'             => 'no',
						'description'       => esc_html__( 'Remove "Add to cart" Button in Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( 'Popup Add to cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'                => self::$option_name . '[hide_add_to_cart_single]',
						'value'             => 'no',
						'description'       => esc_html__( 'Remove "Add to cart" Button in single product page', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( 'Single Add to cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>
			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . 'buttons-custom-text' ); ?>">
				<?php
				woocommerce_wp_text_input(
					array(
						'id'                => self::$option_name . '[add_to_cart_text]',
						'value'             => '',
						'description'       => esc_html__( 'Custom "Add to Cart" Button Title', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( '"Add to cart" Text' ),
						'type'              => 'text',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => self::$option_name . '[quick_view_text]',
						'value'             => '',
						'description'       => esc_html__( 'Custom "Quick View" Button Title', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( '"Quick View" Text', 'quick-view-and-buy-now-for-woocommerce' ),
						'type'              => 'text',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'                => self::$option_name . '[buy_now_text]',
						'value'             => '',
						'description'       => esc_html__( 'Custom "Buy Now" Button Title', 'quick-view-and-buy-now-for-woocommerce' ),
						'label'             => esc_html__( '"Buy Now" Text', 'quick-view-and-buy-now-for-woocommerce' ),
						'type'              => 'text',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>

			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['name'] . 'custom-redirects' ); ?>">
				<?php
				woocommerce_wp_text_input(
					array(
						'label'             => esc_html__( '"Buy Now" Redirect', 'quick-view-and-buy-now-for-woocommerce' ),
						'id'                => self::$option_name . '[buy_now_custom_redirect]',
						'value'             => '',
						'description'       => esc_html__( 'Custom redirect after "Buy Now"', 'quick-view-and-buy-now-for-woocommerce' ),
						'type'              => 'url',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				woocommerce_wp_text_input(
					array(
						'label'             => esc_html__( '"Add to cart" Redirect', 'quick-view-and-buy-now-for-woocommerce' ),
						'id'                => self::$option_name . '[add_to_cart_custom_redirect]',
						'value'             => '',
						'description'       => esc_html__( 'Custom redirect after "Add to cart"', 'quick-view-and-buy-now-for-woocommerce' ),
						'type'              => 'url',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
					)
				);
				?>
			</div>
		</div>
		<script>
			// Remove "Add to Cart" in archive pages and Loop Quantity Input field cant be checked together.
			( function($){
				$( '.<?php echo esc_attr( self::$plugin_info['classes_prefix'] ); ?>-loop-quantity-input-field' ).on( 'change', function(e) {
					if ( this.checked ) {
						$( '.<?php echo esc_attr( self::$plugin_info['classes_prefix'] ); ?>-hide-add-to-cart-loop-field' ).prop( 'checked', false );
					}
				});
				$( '.<?php echo esc_attr( self::$plugin_info['classes_prefix'] ); ?>-hide-add-to-cart-loop-field' ).on( 'change', function(e) {
					if ( this.checked ) {
						$( '.<?php echo esc_attr( self::$plugin_info['classes_prefix'] ); ?>-loop-quantity-input-field' ).prop( 'checked', false );
					}
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Filter Add to Cart Redirect Link for "Single Ajax Add to Cart For WooCommerce" Plugin.
	 *
	 * @param string $redirect_url  Redirect after add-to-cart.
	 * @param object $product       Product Object.
	 * @return string
	 */
	public function get_custom_redirect_after_add_to_cart( $redirect_url, $product ) {
		$custom_add_to_cart_redirect_link = self::custom_add_to_cart_redirect_link( $product );
		if ( $custom_add_to_cart_redirect_link ) {
			$redirect_url = $custom_add_to_cart_redirect_link;
		}
		return $redirect_url;
	}

	/**
	 * Check if hide add to cart button of a product.
	 *
	 * @param object $product Product Object.
	 * @param string $context Product Context [ loop - single - popup ].
	 * @return boolean
	 */
	public static function hide_add_to_cart( $product, $context ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings[ 'hide_add_to_cart_' . $context ] ) && 'yes' === $settings[ 'hide_add_to_cart_' . $context ] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Add to cart Button status.
	 *
	 * @param object $product Product Object.
	 * @param string $context Product Context.
	 * @return string
	 */
	public static function add_to_cart_status( $product, $context ) {
		if ( AddToCart::is_a_product_in_grouped() || Popup::is_variable_popup_in_grouped_popup_request() || Popup::is_variable_popup_in_grouped_single_request() ) {
			return 'visible';
		}
		$is_hidden = self::hide_add_to_cart( $product, $context );
		if ( $is_hidden ) {
			return 'hidden';
		} else {
			return 'visible';
		}
	}

	/**
	 * Get Product Quick View Button Text.
	 *
	 * @param object $product product Object.
	 * @return string
	 */
	public static function custom_add_to_cart_text( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['add_to_cart_text'] ) ) {
			return $settings['add_to_cart_text'];
		} else {
			return '';
		}
	}

	/**
	 * Check if show Quantity Input in loop for simple product.
	 *
	 * @param object $product Product Object.
	 * @return boolean
	 */
	public static function show_quantity_input_in_loop_for_simple_product( $product ) {
		if ( 'simple' !== $product->get_type() ) {
			return false;
		}

		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['loop_quantity_input'] ) && ( 'yes' === $settings['loop_quantity_input'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get product custom Add to cart redirect link.
	 *
	 * @param object $product Product Object.
	 * @return string|false
	 */
	public static function custom_add_to_cart_redirect_link( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['add_to_cart_custom_redirect'] ) ) {
			return $settings['add_to_cart_custom_redirect'];
		} else {
			return false;
		}
	}

	/**
	 * Get Product Quick View Button Text.
	 *
	 * @param object $product product Object.
	 * @return string
	 */
	public static function quick_view_text( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['quick_view_text'] ) ) {
			return $settings['quick_view_text'];
		} else {
			return self::$main_settings['quick_view']['quick_view_text'];
		}
	}

	/**
	 * Get Product Buy Now Position.
	 *
	 * @param object $product Product Object.
	 * @return string
	 */
	public static function quick_view_position( $product ) {
		return self::$main_settings['quick_view']['quick_view_position'];
	}

	/**
	 * Check if quick view button is enabled for a product.
	 *
	 * @param object $product product Object.
	 * @return boolean
	 */
	public static function is_quick_view_button_enabled( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( 'disable' === $settings['quick_view'] ) {
			return false;
		} elseif ( 'enable' === $settings['quick_view'] ) {
			return true;
		} else {
			return Settings::is_quick_view_global_enabled( $product );
		}
	}

	/**
	 * Quick View Button HTML.
	 *
	 * @param object $product Product Object.
	 * @param array  $args     args array.
	 *  $param bool  $echo     echo the HTML.
	 * @return void|string
	 */
	public static function quick_view_button( $product, $args, $echo = false ) {
		if ( ! $echo ) :
			ob_start();
		endif;
		if ( ! empty( $args['class'] ) ) {
			$args['class'] = explode( ' ', $args['class'] );
			foreach ( $args['class'] as $index => $class_name ) {
				if ( 'ajax_add_to_cart' === $class_name ) {
					unset( $args['class'][ $index ] );
				}
			}
			$args['class'] = implode( ' ', $args['class'] );
		}
		if ( ! empty( $args['attributes'] ) ) {
			unset( $args['attributes']['href'], $args['attributes']['rel'] );
		}
		// Quick View Button Shortcode.
		if ( ! empty( $GLOBALS[ self::$plugin_info['name'] . '-quick-view-button-shortcode' ] ) ) {
			?>
		<button data-product_type="<?php echo esc_attr( $product->get_type() ); ?>" class="<?php echo ( esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ) . ' ' . esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-btn' ) ); ?>" <?php echo ( isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '' ); ?> ><?php echo esc_html( self::quick_view_text( $product ) ); ?></button>
			<?php
			// Not different Quick View popup.
		} elseif ( ! Popup::is_different_quick_view() ) {
			?>
		<button data-product_type="<?php echo esc_attr( $product->get_type() ); ?>" class="<?php echo ( esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ) . ' ' . esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-btn' ) ); ?>" <?php echo ( isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '' ); ?> ><?php echo esc_html( self::quick_view_text( $product ) ); ?></button>
			<?php
		}
		if ( ! $echo ) :
			return ob_get_clean();
		endif;
	}

	/**
	 * Get Product Buy Now Redirect Link.
	 *
	 * @param object $product Product Object.
	 * @return string|false
	 */
	public static function buy_now_redirect_link( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['buy_now_custom_redirect'] ) ) {
			return wp_http_validate_url( $settings['buy_now_custom_redirect'] );
		} else {
			$main_buy_now_redirect_link = self::$main_settings['buy_now']['redirect_after'];
			if ( 'cart' === $main_buy_now_redirect_link ) {
				return wc_get_cart_url();
			} elseif ( 'checkout' === $main_buy_now_redirect_link ) {
				return wc_get_checkout_url();
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Hide Buy Now Button.
	 *
	 * @param object $product Product Object.
	 * @return boolean
	 */
	public static function is_buy_now_button_enabled( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['single_buy_now'] ) ) {
			if ( 'enable' === $settings['single_buy_now'] ) {
				return true;
			} elseif ( 'disable' === $settings['single_buy_now'] ) {
				return false;
			} elseif ( 'inherit' === $settings['single_buy_now'] ) {
				$product_type = $product->get_type();
				if ( ! empty( self::$main_settings['buy_now'][ 'enable_by_product_type_' . $product_type ] ) && 'yes' === self::$main_settings['buy_now'][ 'enable_by_product_type_' . $product_type ] ) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Get Product Buy Now Button Text.
	 *
	 * @param object $product product Object.
	 * @return string
	 */
	public static function buy_now_text( $product ) {
		$settings = self::get_settings( $product->get_id() );
		if ( ! empty( $settings['buy_now_text'] ) ) {
			return $settings['buy_now_text'];
		} else {
			return self::$main_settings['buy_now']['buy_now_text'];
		}
	}

	/**
	 * Get Product Buy Now Position.
	 *
	 * @param object $product Product Object.
	 * @return string
	 */
	public static function buy_now_position( $product ) {
		return self::$main_settings['buy_now']['buy_now_position'];
	}

	/**
	 * Buy Now Button HTML.
	 *
	 * @param object $product Product Object.
	 * @param array  $args     args array.
	 *  $param bool  $echo     echo the HTML.
	 * @return void|string
	 */
	public static function buy_now_button( $product, $args, $echo = false ) {
		if ( ! $echo ) :
			ob_start();
		endif;

		$styles         = '';
		$btn_color      = self::$main_settings['buy_now']['color'];
		$btn_bg         = self::$main_settings['buy_now']['bg'];


		if ( ! empty( $btn_color ) ) {
			$styles .= 'color: ' . $btn_color . ';';
		}
		if ( ! empty( $btn_bg ) ) {
			$styles .= 'background-color: ' . $btn_bg . ';';
		}

		if ( ! empty( $styles ) ) {
			$args['attributes']          = $args['attributes'] ?? array();
			$args['attributes']['style'] = ! empty( $args['attributes']['style'] ) ? $args['attributes']['style'] . $styles : $styles;
		}

		$ajax_buy_now   = ( 'yes' === self::$main_settings['buy_now']['buy_now_ajax'] );
		$args['class']  = isset( $args['class'] ) ? $args['class'] : 'button';
		$args['class'] .= ( $ajax_buy_now ? ' ' . self::$plugin_info['classes_prefix'] . '-buy-now-ajax ' : '' );
		?>
		<button <?php echo ( 'simple' === $product->get_type() ? 'value="' . absint( esc_attr( $product->get_id() ) ) . '"' : '' ); ?> class="<?php echo esc_attr( $args['class'] ); ?>" <?php echo ( isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '' ); ?> ><?php echo esc_html( self::buy_now_text( $product ) ); ?></button>
		<?php
		if ( ! $echo ) :
			return ob_get_clean();
		endif;
	}

	/**
	 * Get Settings.
	 *
	 * @param int $product_id Product ID.
	 * @return array
	 */
	public static function get_settings( $product_id ) {
		$settings = get_post_meta( $product_id, self::$option_name, true );
		if ( ! $settings ) {
			return self::$default_settings;
		} else {
			return array_merge( self::$default_settings, $settings );
		}
	}

	/**
	 * Update Settings.
	 *
	 * @param int   $product_id  Product ID.
	 * @param array $settings    New Settings Array.
	 * @return void
	 */
	public static function update_settings( $product_id, $settings ) {
		update_post_meta( $product_id, self::$option_name, $settings );
	}
}
