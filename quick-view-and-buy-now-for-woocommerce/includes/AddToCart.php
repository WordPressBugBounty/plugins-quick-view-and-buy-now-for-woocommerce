<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;
use GPLSCore\GPLS_PLUGIN_ARCW\Single;
use GPLSCore\GPLS_PLUGIN_ARCW\Popup;
/**
 * Redirects To Checkout Class.
 */
class AddToCart {

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
	 * Latest Grouped Product Items Column ID.
	 *
	 * @var string
	 */
	public $latest_grouped_product_column_id = '';

	/**
	 * Ajax Add to Cart Actions.
	 *
	 * @var array
	 */
	public static $ajax_add_to_cart_actions = array( 'add-to-cart', 'buy-now' );

	/**
	 * Available Product Types.
	 *
	 * @var array
	 */
	public static $available_product_types = array( 'simple', 'variable', 'variation', 'grouped' );

	/**
	 * Core Product Types.
	 *
	 * @var array
	 */
	private static $core_types = array( 'simple', 'variable', 'grouped' );

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
	private $add_to_cart_fields = array();

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

		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'add_to_cart_button_adjusting' ), PHP_INT_MAX, 3 );
		add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'update_add_to_cart_args' ), PHP_INT_MAX, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ), PHP_INT_MAX );

		// Ajax Add to cart Product.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-ajax_add_to_cart_action', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_' . self::$plugin_info['name'] . '-ajax_add_to_cart_action', array( $this, 'ajax_add_to_cart' ) );

		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_product_type_hidden_input' ), 1 );

		add_action( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_redirect_handle' ), PHP_INT_MAX, 2 );

		// Filter "Add to cart" Button custom text.
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_custom_text' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'single_add_to_cart_custom_text' ), PHP_INT_MAX, 2 );

		// Add context to localize vars.
		add_filter( self::$plugin_info['name'] . '-localize-vars-arr', array( $this, 'get_current_woocommerce_context' ), 100, 1 );

		// redirect the add to cart of grouped product to our custom add to cart function.
		add_filter( 'woocommerce_add_to_cart_handler', array( $this, 'use_custom_handler_for_add_to_cart_grouped' ), PHP_INT_MAX, 2 );

		// use our handler for grouped add to cart.
		add_action( 'woocommerce_add_to_cart_handler_' . self::$plugin_info['name'] . '-our-add-to-cart-grouped', array( $this, 'add_to_cart_non_ajax_handler_grouped' ), 100, 1 );

		// Populate the custom grouped handler.
		add_filter( 'woocommerce-custom-' . self::$plugin_info['general_prefix'] . '-add-to-cart-grouped', array( $this, 'add_to_cart_handler_grouped' ), 100, 1 );

		// Update Grouped Add To Cart Form after select Variable options.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-update-grouped-add-to-cart-form', array( $this, 'ajax_update_grouped_add_to_cart_form' ) );
		add_action( 'wp_ajax_nopriv_' . self::$plugin_info['name'] . '-update-grouped-add-to-cart-form', array( $this, 'ajax_update_grouped_add_to_cart_form' ) );

		// Add chosen attributes to variations in grouped products.
		add_action( 'woocommerce_grouped_product_list_after_price', array( $this, 'chosen_attributes_inputs_in_grouped_product' ), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_grouped_product_list_column_quantity', array( $this, 'chosen_attributes_inputs_in_grouped_product_clear' ), PHP_INT_MAX, 2 );

		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_add_to_cart_fields' ), 100, 1 );

		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-add-to-cart-settings-title', array( $this, 'add_to_cart_title_notice' ) );

		add_filter( 'wc_add_to_cart_message_html', array( $this, 'adjust_added_to_cart_msg' ), PHP_INT_MAX, 3 );
		add_filter( 'woocommerce_get_script_data', array( $this, 'replace_redirect_to_cart_with_checkout' ), PHP_INT_MAX, 2 );

	}

	/**
	 * Add Clear field to variations in grouped products.
	 *
	 * @param object $_grouped_child_product
	 * @return string
	 */
	public function chosen_attributes_inputs_in_grouped_product_clear( $value, $_grouped_child_product ) {
		if ( $_grouped_child_product->is_type( 'variation' ) && ! empty( $_POST[ self::$plugin_info['name'] . '-variation-in-grouped' ] ) ) {
			ob_start();
			?>
			<a class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-variation-in-grouped-reset-variations reset_variations' ); ?>" data-product_id="<?php echo esc_attr( $_grouped_child_product->get_id() ); ?>" href="#" style="visibility: visible;"><?php esc_html_e( 'Clear', 'woocommerce' ); ?></a>
			<?php
			if ( str_ends_with( $value, '</td>' ) ) {
				$value  = rtrim( $value, '</td>' );
				$value .= ob_get_clean() . '</td>';
			} else {
				$value .= ob_get_clean();
			}
		}

		return $value;
	}

	/**
	 * Add Chosen Attributes fields to variations in grouped products.
	 *
	 * @param object $grouped_child_id
	 * @return void
	 */
	public function chosen_attributes_inputs_in_grouped_product( $_grouped_child_product ) {
		if ( $_grouped_child_product->is_type( 'variation' ) && ! empty( $_POST[ self::$plugin_info['name'] . '-variation-in-grouped' ] ) && ! empty( $_POST[ self::$plugin_info['name'] . '-variation-chosen-attributes[' . $_grouped_child_product->get_id() . ']' ] ) ) {
			$attributes = wp_unslash( $_POST[ self::$plugin_info['name'] . '-variation-chosen-attributes[' . $_grouped_child_product->get_id() . ']' ] );
			foreach ( $attributes as $key => $value ) {
				?>
				<input type="hidden" name="<?php echo esc_attr( self::$plugin_info['name'] . '-variable-selected-options-in-grouped[' . $_grouped_child_product->get_id() . '][]' ); ?>" value="<?php echo esc_attr( $key . '|' . $value['value'] ); ?>" >
				<?php
			}
		}
	}

	/**
	 * Ajax Update the Grouped Product Popup Quick View  Form.
	 */
	public function ajax_update_grouped_add_to_cart_form() {
		if ( empty( $_POST['groupedProductID'] ) || empty( $_POST['groupedVariables'] ) ) {
			wp_send_json(
				array(
					'status' => false,
				)
			);
		}
		$grouped_id        = wp_unslash( $_POST['groupedProductID'] );
		$grouped_variables = wp_unslash( $_POST['groupedVariables'] );
		$other_quantities  = ! empty( $_POST['otherQtys'] ) ? wp_unslash( $_POST['otherQtys'] ) : array();

		foreach ( $grouped_variables as $variation_id => $variation_data ) {
			$variation_id = absint( sanitize_text_field( $variation_id ) );
			foreach ( $variation_data['chosenAttributes'] as $attribute_key => $attribute_arr ) {
				$attribute_key                                        = sanitize_text_field( $attribute_key );
				$attribute_arr['value']                               = sanitize_text_field( $attribute_arr['value'] );
				$variation_data['chosenAttributes'][ $attribute_key ] = $attribute_arr;
			}
			$grouped_variables[ $variation_id ] = $variation_data;
		}

		global $product, $post;

		$post         = get_post( $grouped_id );
		$product      = wc_get_product( $grouped_id );
		$children_ids = array_unique( array_merge( $product->get_children(), array_keys( $grouped_variables ) ) );
		$products     = array_filter( array_map( 'wc_get_product', $children_ids ), 'wc_products_array_filter_visible_grouped' );

		foreach ( $grouped_variables as $variation_id => $variation_data ) {
			$_POST[ self::$plugin_info['name'] . '-variation-chosen-attributes[' . $variation_id . ']' ] = $variation_data['chosenAttributes'];
			$_POST['quantity'][ $variation_id ] = $variation_data['quantity'];
		}

		foreach ( $other_quantities as $_product_id => $qty ) {
			$_product_id                       = absint( sanitize_text_field( $_product_id ) );
			$qty                               = absint( sanitize_text_field( $qty ) );
			$_POST['quantity'][ $_product_id ] = $qty;
		}

		$_POST[ self::$plugin_info['name'] . '-grouped-product-id' ]   = $grouped_id;
		$_POST[ self::$plugin_info['name'] . '-product-in-grouped' ]   = true;
		$_POST[ self::$plugin_info['name'] . '-variation-in-grouped' ] = true;

		ob_start();
		wc_get_template(
			'single-product/add-to-cart/grouped.php',
			array(
				'grouped_product'    => $product,
				'grouped_products'   => $products,
				'quantites_required' => false,
			)
		);
		$result = ob_get_clean();
		wp_send_json(
			array(
				'status' => true,
				'result' => $result,
			)
		);
	}


	/**
	 * Front End Assets.
	 *
	 * @return void
	 */
	public function front_assets() {

		wp_enqueue_style( self::$plugin_info['name'] . '-animate-css', self::$plugin_info['url'] . 'core/assets/libs/animate.min.css', array(), self::$plugin_info['version'], 'all' );
		wp_enqueue_style( self::$plugin_info['name'] . '-front-styles', self::$plugin_info['url'] . 'assets/dist/css/front/front-styles.min.css', array(), self::$plugin_info['version'], 'all' );

		if ( ! wp_script_is( 'jquery' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		if ( ! wp_script_is( 'zoom' ) ) {
			wp_enqueue_script( 'zoom' );
		}

		if ( ! wp_script_is( 'flexslider' ) ) {
			wp_enqueue_script( 'flexslider' );
		}

		if ( ! wp_script_is( 'photoswipe-ui-default' ) ) {
			wp_enqueue_script( 'photoswipe-ui-default' );
		}
		if ( ! wp_script_is( 'photoswipe-default-skin' ) ) {
			wp_enqueue_style( 'photoswipe-default-skin' );
		}

		add_action( 'wp_footer', array( $this, 'include_woocommerce_photoswipe' ) );

		if ( ! wp_script_is( 'wc-add-to-cart-variation' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}

		wp_enqueue_script( self::$plugin_info['name'] . '-front-sweetalert', self::$core->core_assets_lib( 'sweetalert2', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
		wp_enqueue_script( self::$plugin_info['name'] . '-popup-single-product', self::$plugin_info['url'] . 'assets/dist/js/front/popup-single-product.min.js', array( 'jquery', 'flexslider' ), self::$plugin_info['version'], true );
		wp_enqueue_script( self::$plugin_info['name'] . '-main-front-js', self::$plugin_info['url'] . 'assets/dist/js/front/actions.min.js', array( 'wp-i18n' ), self::$plugin_info['version'], true );

		$localize_arr = array(
			'prefix'                          => self::$plugin_info['name'],
			'checkout_url'                    => wc_get_checkout_url(),
			'spinner'                         => admin_url( 'images/spinner-2x.gif' ),
			'ajax_url'                        => admin_url( 'admin-ajax.php' ),
			'updateGroupedCartFormAction'     => self::$plugin_info['name'] . '-update-grouped-add-to-cart-form',
			'nonce'                           => wp_create_nonce( self::$plugin_info['name'] . '-arcw-main-nonce' ),
			'popup_show_animation_class'      => '',
			'popup_hide_animation_class'      => '',
			'wc_add_to_cart_variation_params' => array(
				'i18n_no_matching_variations_text' => esc_html__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
				'i18n_make_a_selection_text'       => esc_html__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
				'i18n_unavailable_text'            => esc_html__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
			),
			'wc_single_product_params'        => array(
				'i18n_required_rating_text' => esc_html__( 'Please select a rating', 'woocommerce' ),
				'review_rating_required'    => wc_review_ratings_required() ? 'yes' : 'no',
				'flexslider'                => apply_filters(
					'woocommerce_single_product_carousel_options',
					array(
						'rtl'            => is_rtl(),
						'animation'      => 'slide',
						'smoothHeight'   => true,
						'directionNav'   => false,
						'controlNav'     => 'thumbnails',
						'slideshow'      => false,
						'animationSpeed' => 500,
						'animationLoop'  => false, // Breaks photoswipe pagination if true.
						'allowOneSlide'  => false,
					)
				),
				'zoom_enabled'              => true,
				'zoom_options'              => array(),
				'photoswipe_enabled'        => true,
				'photoswipe_options'        => array(
					'shareEl'               => false,
					'closeOnScroll'         => false,
					'history'               => false,
					'hideAnimationDuration' => 0,
					'showAnimationDuration' => 0,
				),
				'flexslider_enabled'        => true,
			),
			'wc_add_to_cart_params'           => array(
				'ajax_url'                => WC()->ajax_url(),
				'wc_ajax_url'             => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'i18n_view_cart'          => esc_html__( 'View cart', 'woocommerce' ),
				'cart_url'                => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
				'is_cart'                 => is_cart(),
				'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
			),
			'labels'                          => array(
				'clear' => esc_html__( 'Clear', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			'icons'                           => array(
				'close' => self::$plugin_info['url'] . 'assets/dist/images/close.png',
				'prev'  => self::$plugin_info['url'] . 'assets/dist/images/prev.png',
			),
		);

		$localize_arr = apply_filters( self::$plugin_info['name'] . '-localize-vars-arr', $localize_arr );

		wp_localize_script(
			self::$plugin_info['name'] . '-main-front-js',
			str_replace( '-', '_', self::$plugin_info['name'] . '-localize-vars' ),
			$localize_arr
		);

		CustomCSS::print_custom_css();
	}

	/**
	 * Force photoswipe for popups.
	 *
	 * @return void
	 */
	public function include_woocommerce_photoswipe() {
		if ( ! current_theme_supports( 'wc-product-gallery-lightbox' ) || ! is_product() ) {
			wc_get_template( 'single-product/photoswipe.php' );
		}
	}

	/**
	 * Add current context to the localize array.
	 *
	 * @return array
	 */
	public function get_current_woocommerce_context( $localize_arr ) {
		global $wp_query;

		$context = '';
		if ( is_product() ) {
			$context = 'single';

			if ( $wp_query && is_object( $wp_query ) && ! is_wp_error( $wp_query ) ) {
				$product_id      = $wp_query->get_queried_object_id();
				$queried_product = wc_get_product( $product_id );
				if ( is_object( $queried_product ) && ! is_wp_error( $queried_product ) ) {
					$single_context                     = 'single-' . $queried_product->get_type();
					$localize_arr['woo_single_context'] = $single_context;
				}
			}
		} elseif ( is_shop() || is_product_taxonomy() ) {
			$context = 'loop';
		} elseif ( is_checkout() ) {
			$context = 'checkout';
		} elseif ( is_cart() ) {
			$context = 'cart';
		} elseif ( is_account_page() ) {
			$context = 'account';
		}

		$localize_arr['woo_context'] = $context;
		return $localize_arr;
	}

	/**
	 * Display Quantity Input for simple products in loop.
	 *
	 * @return string
	 */
	public function quantity_input_for_simple_product_in_loop() {
		global $product;

		if ( ! Single::show_quantity_input_in_loop_for_simple_product( $product ) ) {
			return '';
		}

		if ( Single::hide_add_to_cart( $product, 'loop' ) ) {
			return '';
		}
		return $this->simple_loop_quantity_input_for_add_to_cart( $product );
	}

	/**
	 * Add Quantity Input for simple products in shop and archive pages.
	 *
	 * @param object $product Product Object.
	 * @param string $custom_classes custom classes.
	 * @param string $custom_main_class custom main class.
	 * @return string
	 */
	private function simple_loop_quantity_input_for_add_to_cart( $product, $custom_classes = '', $custom_main_class = '', $return = true ) {
		$args = array(
			'main_class'   => empty( $custom_main_class ) ? self::$plugin_info['classes_prefix'] . '-simple-loop-quantity' : $custom_main_class,
			'input_id'     => uniqid( 'quantity_' ),
			'input_name'   => 'quantity',
			'input_value'  => $product->get_min_purchase_quantity(),
			'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text', empty( $custom_classes ) ? self::$plugin_info['classes_prefix'] . '-loop-quantity-input' : $custom_classes ), $product ),
			'min_value'    => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
			'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
			'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
			'product_name' => $product ? $product->get_title() : '',
			'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
		);
		if ( $return ) {
			ob_start();
		}
		wc_get_template(
			'default-quantity-input.php',
			array(
				'product_obj' => $product,
				'plugin_info' => self::$plugin_info,
				'args'        => $args,
			),
			self::$plugin_info['path'] . 'templates/global',
			self::$plugin_info['path'] . 'templates/global/'
		);
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Custom "Add to cart" Button Text.
	 *
	 * @param string $add_to_cart_text  Add to Cart Button Text.
	 * @param object $product           Product Object.
	 * @return string
	 */
	public function add_to_cart_custom_text( $add_to_cart_text, $product ) {
		if ( ! $product->is_purchasable() ) {
			return $add_to_cart_text;
		}

		$custom_text = Single::custom_add_to_cart_text( $product );
		if ( ! empty( $custom_text ) ) {
			return sprintf(
				/* translators: 1: Custom Add to cart Button text. */
				esc_html__( '%s', 'quick-view-and-buy-now-for-woocommerce' ),
				$custom_text
			);
		}
		$global_text = $this->get_global_custom_add_to_cart_text( 'variable' === $product->get_type() );
		if ( ! empty( $global_text ) ) {
			return sprintf(
				/* translators: 1: Custom Add to cart Button text. */
				esc_html__( '%s', 'quick-view-and-buy-now-for-woocommerce' ),
				$global_text
			);
		}
		return $add_to_cart_text;
	}

	/**
	 * Custom Single "Add to cart" Button Text.
	 *
	 * @param string $single_add_to_cart_text   Single Add to cart Button Text.
	 * @param object $product                   Product Object.
	 * @return string
	 */
	public function single_add_to_cart_custom_text( $single_add_to_cart_text, $product ) {
		// Override the single "Add to cart" button text for variable product popup in grouped product.
		if ( Popup::is_variable_popup_in_grouped_popup_request() || self::is_variable_in_grouped_single_request() ) {
			return esc_html__( 'Select', 'woocommerce' );
		}

		$custom_text = Single::custom_add_to_cart_text( $product );
		if ( ! empty( $custom_text ) ) {
			return sprintf(
				/* translators: 1: Custom Add to cart Button text. */
				esc_html__( '%s', 'quick-view-and-buy-now-for-woocommerce' ),
				$custom_text
			);
		}

		$global_text = $this->get_global_custom_add_to_cart_text();
		if ( ! empty( $global_text ) ) {
			return sprintf(
				/* translators: 1: Custom Add to cart Button text. */
				esc_html__( '%s', 'quick-view-and-buy-now-for-woocommerce' ),
				$global_text
			);
		}

		return $single_add_to_cart_text;
	}

	/**
	 * Change the Add To Cart Button Text for Variable Product popup in grouped products.
	 *
	 * @param string $add_to_cart_text
	 * @param object $product
	 * @return string
	 */
	public function custom_add_to_cart_button_text_for_variable_in_grouped( $add_to_cart_text, $product ) {
		return esc_html__( 'Select', 'woocommerce' );
	}

	/**
	 * Add variable Product Options in the table list.
	 *
	 * @param object $grouped_product_child Grouped Product Object.
	 * @return void
	 */
	public function add_variable_product_item_options_in_grouped_product( $grouped_product_child ) {
		if ( 'variable' !== $grouped_product_child->get_type() ) {
			return;
		}
		?>
		<tr class="grouped-variable-product-options">
			<td colspan="20">
				<div>
					<?php
					$get_variations       = count( $grouped_product_child->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $grouped_product_child );
					$available_variations = $get_variations ? $grouped_product_child->get_available_variations() : false;
					$attributes           = $grouped_product_child->get_variation_attributes();
					$attribute_keys       = array_keys( $attributes );
					if ( empty( $available_variations ) && false !== $available_variations ) :
						?>
						<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', esc_html__( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
					<?php else : ?>
						<table class="variations" cellspacing="0">
							<tbody>
								<?php foreach ( $attributes as $attribute_name => $options ) : ?>
									<tr>
										<td class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></td>
										<td class="value">
											<?php
												wc_dropdown_variation_attribute_options(
													array(
														'options'   => $options,
														'attribute' => $attribute_name,
														'product'   => $grouped_product_child,
													)
												);
												echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Adjust Added To Cart Message.
	 *
	 * @param string  $msg
	 * @param array   $products
	 * @param boolean $show_qty
	 * @return string
	 */
	public function adjust_added_to_cart_msg( $msg, $products, $show_qty ) {
		// Replace View Cart.
		if ( 'yes' === Settings::get_setting( 'add_to_cart', 'checkout_in_add_to_cart_msg' ) ) {
			$msg = str_replace( wc_get_page_permalink( 'cart' ), wc_get_page_permalink( 'checkout' ), $msg );
			$msg = str_replace( esc_html__( 'View cart', 'woocommerce' ), esc_html__( 'Checkout', 'woocommerce' ), $msg );

			// already redirecting to checkout?, remove.
			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) && 'yes' === Settings::get_setting( 'add_to_cart', 'replace_redirect_to_cart_with_checkout' ) ) {
				$msg = '';
			}
		}

		// Replace Continue Shopping.
		if ( 'yes' === Settings::get_setting( 'add_to_cart', 'replace_continue_shopping_with_checkout' ) ) {
			$return_to = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink( 'shop' ) );
			$msg       = str_replace( $return_to, wc_get_page_permalink( 'checkout' ), $msg );
			$msg       = str_replace( esc_html__( 'Continue shopping', 'woocommerce' ), esc_html__( 'Checkout', 'woocommerce' ), $msg );

			// already redirecting to checkout?, remove.
			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) && 'yes' === Settings::get_setting( 'add_to_cart', 'replace_redirect_to_cart_with_checkout' ) ) {
				$msg = '';
			}
		}

		return $msg;
	}

	/**
	 * Replace redirect to cart with checkout.
	 *
	 * @param array  $params
	 * @param string $handle
	 * @return array
	 */
	public function replace_redirect_to_cart_with_checkout( $params, $handle ) {
		if ( 'wc-add-to-cart' === $handle && 'yes' === Settings::get_setting( 'add_to_cart', 'replace_redirect_to_cart_with_checkout' ) ) {
			$params['i18n_view_cart'] = esc_html__( 'Checkout', 'woocommerce' );
			$params['cart_url']       = wc_get_checkout_url();
		}
		return $params;
	}

	/**
	 * Adjust the Add to cart and add "Quick View" Button.
	 *
	 * @param string $add_to_cart_button_html Add TO Cart HTML.
	 * @param object $product   Product Object.
	 * @param array  $args       Add TO Cart Button Args Array.
	 *
	 * @return string
	 */
	public function add_to_cart_button_adjusting( $add_to_cart_button_html, $product, $args ) {
		global $product, $wp_query;

		// Variable Product inside Grouped.
		if ( self::is_a_product_in_grouped( 'variable' ) ) {
			$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
			$available_variations = $get_variations ? $product->get_available_variations() : false;
			// no variations available, abort!.
			if ( empty( $available_variations ) && false !== $available_variations ) {
				return '';
			}
			// Add custom class to "select options" button of variable product in a grouped product.
			$args['class']          .= ' ' . self::$plugin_info['classes_prefix'] . '-variable-in-grouped-product-select-options';
			$add_to_cart_button_html = $this->update_add_to_cart_button( $product, $args );
			$result                  = $add_to_cart_button_html;

			ob_start();
			?>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-variable-quantity-wrapper' ); ?>" style="display: none;">
				<?php
				woocommerce_quantity_input(
					array(
						'input_value' => 0,
						'classes'     => array( 'input-text', 'qty', 'text', self::$plugin_info['classes_prefix'] . '-quantity-input', 'grouped-variable-options-quantity' ),
						'input_name'  => esc_attr( 'quantity[' . $product->get_id() . ']' ),
					),
					$product
				);
				?>
			</div>
			<?php
			$result .= ob_get_clean();

			return $result;
		}
		// Other types of products in grouped product.
		if ( self::is_a_product_in_grouped() ) {
			return $add_to_cart_button_html;
		}

		// Quick View Button.
		if ( Settings::is_quick_view_global_enabled( $product ) ) :
			ob_start();
			Single::quick_view_button( $product, $args, true );
			$quick_view_button = ob_get_clean();
			if ( 'before' === Single::quick_view_position( $product ) ) {
				$add_to_cart_button_html = $quick_view_button . $add_to_cart_button_html;
			} elseif ( 'after' === Single::quick_view_position( $product ) ) {
				$add_to_cart_button_html .= $quick_view_button;
			}
		endif;

		return $add_to_cart_button_html;

	}

	/**
	 * Update Add to Cart Button.
	 *
	 * @param object $product Product Object.
	 * @param array  $args Add to cart Button Args Array.
	 * @return string
	 */
	private function update_add_to_cart_button( $product, $args ) {
		// prevent ajax add to cart class "ajax_add_to_cart" if the product has a custom redirect after add to cart.
		$custom_redirect_link = Single::custom_add_to_cart_redirect_link( $product );
		if ( $custom_redirect_link ) {
			$args['class'] = explode( ' ', $args['class'] );
			foreach ( $args['class'] as $index => $class_name ) {
				if ( 'ajax_add_to_cart' === $class_name ) {
					unset( $args['class'][ $index ] );
				}
			}
			$args['class'] = implode( ' ', $args['class'] );
		}

		// Return Add to cart link HTML.
		return sprintf(
			'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
			esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
			esc_html( $product->add_to_cart_text() )
		);
	}

	/**
	 * Update Add To Cart Args.
	 *
	 * @param array  $args
	 * @param object $product
	 * @return array
	 */
	public function update_add_to_cart_args( $args, $product ) {
		// Add To Cart Button of Variable Product in grouped product.
		if ( self::is_a_product_in_grouped( 'variable' ) ) {
			$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
			$available_variations = $get_variations ? $product->get_available_variations() : false;
			// no variations available, abort!.
			if ( empty( $available_variations ) && false !== $available_variations ) {
				return '';
			}
			global $wp_query;
			// Add custom class to "select options" button of variable product in a grouped product.
			$args['class']                                .= ' ' . self::$plugin_info['classes_prefix'] . '-variable-in-grouped-product-select-options';
			$args['attributes']['data-grouped_product_id'] = ( ! empty( $_POST[ self::$plugin_info['name'] . '-grouped-product-id' ] ) ? absint( wp_unslash( $_POST[ self::$plugin_info['name'] . '-grouped-product-id' ] ) ) : $wp_query->get_queried_object_id() );
		} else {
			// prevent ajax add to cart class "ajax_add_to_cart" if the product has a custom redirect after add to cart.
			$custom_redirect_link = Single::custom_add_to_cart_redirect_link( $product );
			if ( $custom_redirect_link ) {
				$args['class'] = preg_split( '/\s+/', $args['class'], -1, \PREG_SPLIT_NO_EMPTY );
				foreach ( $args['class'] as $index => $class_name ) {
					if ( 'ajax_add_to_cart' === $class_name ) {
						unset( $args['class'][ $index ] );
					}
				}
				$args['class'] = implode( ' ', $args['class'] );
			}
		}

		return $args;
	}

	/**
	 * Add Product Type hidden input after add to cart submit button.
	 *
	 * @return void
	 */
	public function add_product_type_hidden_input() {
		global $product;
		$context = Popup::is_quick_view_popup_request() ? 'popup' : 'single';
		// Return if its a single external product.
		if ( 'external' === $product->get_type() ) {
			return;
		}
		$main_settings        = Settings::get_main_settings();
		$disable_ajax_buy_now = ( 'no' === $main_settings['buy_now']['buy_now_ajax'] );
		?>
		<input type="hidden" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-context' ); ?>" name="<?php echo esc_attr( self::$plugin_info['name'] . '-context' ); ?>" value="<?php echo esc_attr( $context ); ?>">
		<input type="hidden" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-product-type' ); ?>" name="<?php echo esc_attr( self::$plugin_info['name'] . '-product-type' ); ?>" value="<?php echo esc_attr( $product->get_type() ); ?>">
		<?php
		if ( $disable_ajax_buy_now || ( ! in_array( $product->get_type(), self::$core_types ) ) ) :
			?>
			<input type="hidden" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-regular-buy-now-request' ); ?>" value="1" name="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-regular-buy-now-request' ); ?>" />
			<?php
		endif;
	}

	/**
	 * Check if a product loop in a grouped single product.
	 *
	 * @return boolean
	 */
	public static function is_a_product_in_grouped( $product_type = false ) {
		global $product, $wp_query;
		$main_product = wc_get_product( $wp_query->get_queried_object_id() );

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-product-in-grouped' ] ) ) {
			return true;
		}

		if ( ! $main_product || ! $product ) {
			return false;
		}

		// not single grouped product.
		if ( 'grouped' !== $main_product->get_type() ) {
			return false;
		}

		// Single Product Page.
		if ( is_string( $product ) ) {
			return false;
		}

		// The main single product loop.
		if ( $main_product->get_id() === $product->get_id() ) {
			return false;
		}

		// check if product is one of the grouped children.
		if ( ! in_array( $product->get_id(), $main_product->get_children() ) ) {
			return false;
		}

		// Variable popup in grouped popup.
		if ( 'variable' === $product_type && ( Popup::is_variable_popup_in_grouped_popup_request() || self::is_variable_in_grouped_single_request() ) ) {
			return true;
		}

		// Check if the grouped loop has finished after passing by grouped products children action.
		if ( did_action( 'woocommerce_grouped_product_list_after' ) ) {
			return false;
		}

		// Last check against the product type.
		if ( $product_type ) {
			if ( $product_type === $product->get_type() ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * Check if a variation productin in a grouped product.
	 *
	 * @return boolean
	 */
	public static function is_a_variation_in_grouped( $product ) {
		return ( ( 'variation' === $product->get_type() ) && wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '-variation-in-grouped' ] ) );
	}

	/**
	 * Ajax Add to Cart Variable Product.
	 *
	 * @return void
	 */
	public function ajax_add_to_cart() {
		if ( empty( $_POST[ self::$plugin_info['general_prefix'] . '-ajax-add-to-cart' ] ) ) {
			wc_add_notice( esc_html__( 'missing product ID', 'quick-view-and-buy-now-for-woocommerce' ), 'error' );
			wp_send_json(
				array(
					'result'  => false,
					'notices' => wc_print_notices( true ),
				)
			);
		}

		$product_id     = absint( wp_unslash( $_POST[ self::$plugin_info['general_prefix'] . '-ajax-add-to-cart' ] ) );
		$adding_to_cart = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			wc_add_notice( esc_html__( 'Invalid Product ID', 'quick-view-and-buy-now-for-woocommerce' ), 'error' );
			wp_send_json(
				array(
					'result'  => false,
					'notices' => wc_print_notices( true ),
				)
			);
		}

		$product_type = $adding_to_cart->get_type();
		$action_type  = 'add-to-cart';
		$is_popup     = self::is_add_to_cart_from_popup();

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-action-type' ] ) ) {
			$posted_action_type = sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '-action-type' ] ) );
			if ( in_array( $posted_action_type, self::$ajax_add_to_cart_actions ) ) {
				$action_type = $posted_action_type;
			}
		}

		if ( in_array( $product_type, self::$available_product_types ) ) {
			if ( 'simple' === $product_type ) {
				$result = $this->add_to_cart_handler_simple( $product_id );
			} elseif ( 'variable' === $product_type || 'variation' === $product_type ) {
				$result = $this->add_to_cart_handler_variable( $product_id );
			} elseif ( 'grouped' === $product_type ) {
				$result = $this->add_to_cart_handler_grouped( $product_id );
			}

			$response = self::prepare_ajax_add_to_cart_response( $result, $adding_to_cart, $action_type, $is_popup );

			wp_send_json( $response );
		} else {
			wc_add_notice( esc_html__( 'Invalid Product Type', 'quick-view-and-buy-now-for-woocommerce' ), 'error' );
			wp_send_json(
				array(
					'result'  => false,
					'notices' => wc_print_notices( true ),
				)
			);
		}
	}

	/**
	 * Prepare Ajax Add to Cart Respones
	 *
	 * @param boolean $result   Add To Cart Success-Failure.
	 * @param object  $product   Product Object.
	 * @param string  $action_type Action Type [ add-to-cart buy-now ].
	 * @param boolean $is_popup [ single - popup ].
	 * @return array
	 */
	private static function prepare_ajax_add_to_cart_response( $result, $product, $action_type, $is_popup ) {
		$response            = array();
		$response['result']  = $result;
		$response['notices'] = wc_print_notices( true );

		if ( $result ) {
			// Add to cart.
			if ( 'add-to-cart' === $action_type ) {
				$response['cart_hash'] = WC()->cart->get_cart_hash();

				// Add to cart custom redirect link.
				$custom_add_to_cart_redirect_link = Single::custom_add_to_cart_redirect_link( $product );
				if ( $custom_add_to_cart_redirect_link ) {
					$response['redirect_link'] = $custom_add_to_cart_redirect_link;
				}

				// Buy Now.
			} elseif ( 'buy-now' === $action_type ) {
				$buy_now_redirect_link = Single::buy_now_redirect_link( $product );
				if ( $buy_now_redirect_link ) {
					$response['redirect_link'] = $buy_now_redirect_link;
				}
			}
		}

		return $response;
	}

	/**
	 * Add To Cart Handler for Simple Product.
	 *
	 * @param int $product_id Simple Product ID.
	 *
	 * @return boolean
	 */
	private function add_to_cart_handler_simple( $product_id ) {
		$quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}
		return false;
	}

	/**
	 * Add To Cart Handler for Variable Product.
	 *
	 * @param int $product_id Variable Product ID.
	 *
	 * @return boolean
	 */
	private function add_to_cart_handler_variable( $product_id ) {
		$variation_id = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$quantity     = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$variations   = array();

		$product = wc_get_product( $product_id );

		foreach ( $_REQUEST as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'attribute_' !== substr( $key, 0, 10 ) ) {
				continue;
			}

			$variations[ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( ! $passed_validation ) {
			return false;
		}

		// Prevent parent variable product from being added to cart.
		if ( empty( $variation_id ) && $product && $product->is_type( 'variable' ) ) {
			/* translators: 1: product link, 2: product name */
			wc_add_notice( sprintf( __( 'Please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.', 'woocommerce' ), esc_url( get_permalink( $product_id ) ), esc_html( $product->get_name() ) ), 'error' );

			return false;
		}

		if ( false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}

		return false;
	}

	/**
	 * Add To Cart Handler for Grouped Product.
	 *
	 * @param int $product_id Grouped Product ID.
	 *
	 * @return boolean
	 */
	public function add_to_cart_handler_grouped( $product_id = null ) {
		$was_added_to_cart = false;
		$added_to_cart     = array();
		$items             = isset( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) ? wp_unslash( $_REQUEST['quantity'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! empty( $items ) ) {
			$quantity_set = false;

			foreach ( $items as $item => $quantity ) {
				$variation_id = 0;
				$variation    = array();
				$quantity     = wc_stock_amount( $quantity );
				if ( $quantity <= 0 ) {
					continue;
				}
				$quantity_set = true;

				// Add to cart validation.
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity );

				// prepare the selected attributes for variable products.
				$product_id = absint( sanitize_text_field( $item ) );
				if ( 'product_variation' === get_post_type( $product_id ) ) {
					if ( ! empty( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] ) && is_array( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] ) ) {
						$selected_options = wp_unslash( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] );
						foreach ( $selected_options as $selected_option ) {
							$selected_option = sanitize_text_field( $selected_option );
							$option_key_val  = explode( '|', $selected_option, 2 );
							if ( 2 !== count( $option_key_val ) ) {
								continue;
							}
							$variation[ $option_key_val[0] ] = $option_key_val[1];
						}
					}
				}

				// Suppress total recalculation until finished.
				remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

				if ( $passed_validation && false !== WC()->cart->add_to_cart( $item, $quantity, $variation_id, $variation ) ) {
					$was_added_to_cart      = true;
					$added_to_cart[ $item ] = $quantity;
				}

				add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
			}

			if ( ! $was_added_to_cart && ! $quantity_set ) {
				wc_add_notice( esc_html__( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'woocommerce' ), 'error' );
			} elseif ( $was_added_to_cart ) {
				wc_add_to_cart_message( $added_to_cart );
				WC()->cart->calculate_totals();
				return true;
			}
		} elseif ( $product_id ) {
			/* Link on product archives */
			wc_add_notice( esc_html__( 'Please choose a product to add to your cart&hellip;', 'woocommerce' ), 'error' );
		}
		return false;
	}

	/**
	 * Add To Cart Handler for Grouped Product for regular submit requests ( non-ajax ).
	 *
	 * @param string|false $url After Add to cart URL redirect.
	 *
	 * @return void
	 */
	public function add_to_cart_non_ajax_handler_grouped( $url ) {
		if ( ! isset( $_REQUEST['add-to-cart'] ) || ! is_numeric( wp_unslash( $_REQUEST['add-to-cart'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return;
		}
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST['add-to-cart'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$adding_to_cart    = wc_get_product( $product_id );
		$was_added_to_cart = false;
		$added_to_cart     = array();
		$items             = isset( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) ? wp_unslash( $_REQUEST['quantity'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! empty( $items ) ) {
			$quantity_set = false;

			foreach ( $items as $item => $quantity ) {
				$variation_id = 0;
				$variation    = array();
				$quantity     = wc_stock_amount( $quantity );
				if ( $quantity <= 0 ) {
					continue;
				}
				$quantity_set = true;

				// Add to cart validation.
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity );

				// prepare the selected attributes for variable products.
				$product_id = absint( sanitize_text_field( $item ) );
				if ( 'product_variation' === get_post_type( $product_id ) ) {
					if ( ! empty( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] ) && is_array( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$selected_options = wp_unslash( $_POST[ self::$plugin_info['name'] . '-variable-selected-options-in-grouped' ][ $product_id ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						foreach ( $selected_options as $selected_option ) {
							$selected_option = sanitize_text_field( $selected_option );
							$option_key_val  = explode( '|', $selected_option, 2 );
							if ( 2 !== count( $option_key_val ) ) {
								continue;
							}
							$variation[ $option_key_val[0] ] = $option_key_val[1];
						}
					}
				}

				// Suppress total recalculation until finished.
				remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

				if ( $passed_validation && false !== WC()->cart->add_to_cart( $item, $quantity, $variation_id, $variation ) ) {
					$was_added_to_cart      = true;
					$added_to_cart[ $item ] = $quantity;
				}

				add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
			}

			if ( ! $was_added_to_cart && ! $quantity_set ) {
				wc_add_notice( esc_html__( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'woocommerce' ), 'error' );
			} elseif ( $was_added_to_cart ) {
				wc_add_to_cart_message( $added_to_cart );
				WC()->cart->calculate_totals();

				$url = apply_filters( 'woocommerce_add_to_cart_redirect', $url, $adding_to_cart );
				if ( $url ) {
					wp_safe_redirect( $url );
					exit;
				} elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
					wp_safe_redirect( wc_get_cart_url() );
					exit;
				}
			}
		} elseif ( $product_id ) {
			/* Link on product archives */
			wc_add_notice( esc_html__( 'Please choose a product to add to your cart&hellip;', 'woocommerce' ), 'error' );
		}
	}

	/**
	 * Check if add to cart request from popup.
	 *
	 * @return boolean
	 */
	public static function is_add_to_cart_from_popup() {
		return ( wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '-context' ] ) && 'popup' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '-context' ] ) ) );
	}

	/**
	 * Redirect the add to cart grouped to our handler.
	 *
	 * @param string $product_type Product Type.
	 * @param object $product_obj  Product Object.
	 * @return string
	 */
	public function use_custom_handler_for_add_to_cart_grouped( $product_type, $product_obj ) {
		if ( 'grouped' === $product_type && ! wp_doing_ajax() ) {
			$product_type = self::$plugin_info['name'] . '-our-add-to-cart-grouped';
		}

		return $product_type;
	}

	/**
	 * Check if variable in grouped single product page request.
	 *
	 * @return boolean
	 */
	public static function is_variable_in_grouped_single_request() {
		return ( ! empty( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) && 'single-grouped' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) ) );
	}

	/**
	 * Check if add to cart redirect is needed.
	 *
	 * @param string $url Redirect URL.
	 * @param object $product Product Object.
	 * @return string|false
	 */
	public function add_to_cart_redirect_handle( $url, $product ) {
		if ( is_null( $product ) ) {
			return $url;
		}

		// Buy Now Request?.
		if ( ! $url && ! empty( $_POST[ self::$plugin_info['classes_prefix'] . '-regular-buy-now-request' ] ) && ! empty( $_POST[ self::$plugin_info['classes_prefix'] . '-regular-buy-now-request-buy-now' ] ) ) {
			$url = Single::buy_now_redirect_link( $product );
		}

		// Replace Cart Redirect to Checkout.
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) && 'yes' === Settings::get_setting( 'add_to_cart', 'replace_redirect_to_cart_with_checkout' ) ) {
			$url = wc_get_checkout_url();
		}

		// Remove the add to cart parameters after "add to cart" to avoid duplications.
		if ( ( empty( $url ) || ( false === $url ) ) && ! wp_doing_ajax() && ( 'yes' !== get_option( 'woocommerce_cart_redirect_after_add' ) ) ) {
			$url = remove_query_arg( array( 'add-to-cart', 'quantity' ), false );
		}

		return $url;
	}

	/**
	 * Get the "Quick View" Button arguments.
	 *
	 * @param object $product Product Object.
	 * @return array
	 */
	public static function quick_view_button_args( $product ) {
		$args     = array();
		$defaults = array(
			'quantity'   => 1,
			'class'      => implode(
				' ',
				array_filter(
					array(
						'button',
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
					)
				)
			),
			'attributes' => array(
				'data-product_id'  => $product->get_id(),
				'data-product_sku' => $product->get_sku(),
				'aria-label'       => $product->add_to_cart_description(),
				'rel'              => 'nofollow',
			),
		);

		$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
		return $args;
	}

	/**
	 * Checkout Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_add_to_cart_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['add_to_cart'] = $this->add_to_cart_fields;
		return $fields;
	}

	public function add_to_cart_title_notice() {
		?>
		<span class="tex-muted"><?php esc_html_e( 'You can add custom text and custom redirect for Add to cart button per product in', 'quick-view-and-buy-now-for-woocommerce' ); ?><b><?php self::$core->pro_btn(); ?></b> version</span>
		<?php
	}

	/**
	 * setup Settings Fields.
	 *
	 * @return void
	 */
	private function setup_settings_fields() {
		$this->add_to_cart_fields = array(
			array(
				'title' => esc_html__( 'Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-add-to-cart-settings-title',
			),
			array(
				'type' => self::$plugin_info['name'] . '-add-to-cart-settings-title',
			),
			array(
				'title'       => esc_html__( 'Add To Cart Custom Text', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'        => esc_html__( 'Replace Add to Cart Button Text', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'    => esc_html__( 'Add custom text for Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'          => Settings::$settings_name . '[add_to_cart][custom_add_to_cart_text]',
				'type'        => 'text',
				'class'       => 'input-text',
				'value'       => self::$settings['add_to_cart']['custom_add_to_cart_text'],
				'name_keys'   => array( 'add_to_cart', 'custom_add_to_cart_text' ),
				'placeholder' => esc_html__( 'Ex: Purchase', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			array(
				'title'       => esc_html__( 'Add To Cart - Variable Product - Custom Text', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'        => esc_html__( 'Replace Add to Cart Button Text of Variable products in loops. Default is: "Select Options"', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'    => esc_html__( 'Add custom text for Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'          => Settings::$settings_name . '[add_to_cart][custom_add_to_cart_variable_text]',
				'type'        => 'text',
				'class'       => 'input-text',
				'value'       => self::$settings['add_to_cart']['custom_add_to_cart_variable_text'],
				'name_keys'   => array( 'add_to_cart', 'custom_add_to_cart_variable_text' ),
				'placeholder' => esc_html__( 'Ex: Explore Colors', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			array(
				'title'     => esc_html__( 'Text Color', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Button Text Color', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'         => Settings::$settings_name . '[add_to_cart][color]',
				'type'      => 'text',
				'class'     => 'wp-color-picker',
				'default'   => '',
				'css'       => 'width:6em;',
				'value'     => self::$settings['add_to_cart']['color'],
				'name_keys' => array( 'add_to_cart', 'color' ),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			array(
				'title'     => esc_html__( 'Background Color', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Button Background Color', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '[add_to_cart][bg]',
				'type'      => 'text',
				'class'     => 'wp-color-picker',
				'default'   => '',
				'css'       => 'width:6em;',
				'value'     => self::$settings['add_to_cart']['bg'],
				'name_keys' => array( 'add_to_cart', 'bg' ),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
			array(
				'title' => esc_html__( 'Added To Cart Notice', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
			),
			array(
				'title'     => esc_html__( 'Replace Continue Shopping link', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Replace continue shopping link with checkout link in added to cart notice.', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[add_to_cart][replace_continue_shopping_with_checkout]',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'value'     => self::$settings['add_to_cart']['replace_continue_shopping_with_checkout'],
				'name_keys' => array( 'add_to_cart', 'replace_continue_shopping_with_checkout' ),
			),
			array(
				'title'     => esc_html__( 'Replace View Cart link', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Replace View Cart link with checkout link in added to cart notice.', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[add_to_cart][checkout_in_add_to_cart_msg]',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'value'     => self::$settings['add_to_cart']['checkout_in_add_to_cart_msg'],
				'name_keys' => array( 'add_to_cart', 'checkout_in_add_to_cart_msg' ),
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
			array(
				'title' => esc_html__( 'Redirect After Add To Cart', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
			),
		);

		$product_types = wc_get_product_types();
		unset( $product_types['external'] );

		foreach ( $product_types as $product_type_key => $product_key_label ) {
			$field_arr = array(
				'desc'      => sprintf( esc_html__( '%s', 'quick-view-and-buy-now-for-woocommerce' ), ( 'yith_bundle' === $product_type_key ? 'YITH Product Bundles' : $product_key_label ), $product_type_key ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'desc_tip'  => sprintf( esc_html__( 'Set Custom redirect to after add to cart', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label ),
				'id'        => Settings::$settings_name . '[add_to_cart][general_redirect_by_product_type_' . $product_type_key . ']',
				'type'      => 'single_select_page_with_search',
				'class'     => 'wc-page-search',
				'css'       => 'min-width:300px;',
				'args'      => array(
					'exclude' => array(),
				),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'value'     => self::$settings['add_to_cart'][ 'general_redirect_by_product_type_' . $product_type_key ],
				'name_keys' => array( 'add_to_cart', 'general_redirect_by_product_type_' . $product_type_key ),
			);

			if ( array_key_first( $product_types ) === $product_type_key ) {
				$field_arr['title'] = esc_html__( 'After add to cart redirect', 'quick-view-and-buy-now-for-woocommerce' );
			}
			$this->add_to_cart_fields[] = $field_arr;
		}

		$this->add_to_cart_fields[] = array(
			'name' => '',
			'type' => 'sectionend',
		);
		$this->add_to_cart_fields[] = array(
			'title' => esc_html__( 'Redirect to Cart [ Direct Checkout ]', 'quick-view-and-buy-now-for-woocommerce' ),
			'type'  => 'title',
		);
		$this->add_to_cart_fields[] = array(
			'title'     => esc_html__( 'Replace Redirect to Cart with Checkout', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Replace WooCommerce redirect to cart with redirect to checkout and replace "view cart" link with "Checkout" link.', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'  => esc_html__( 'This option requires WooCommerce ', 'quick-view-and-buy-now-for-woocommerce' ) . '<a target="_blank" href="' . esc_url_raw( admin_url( 'admin.php?page=wc-settings&tab=products' ) ) . '">' . esc_html__( 'Redirect to the cart page after successful addition', 'woocommerce' ) . '</a> ' . esc_html__( 'option to be enabled', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => Settings::$settings_name . '[add_to_cart][replace_redirect_to_cart_with_checkout]',
			'type'      => 'checkbox',
			'class'     => 'input-checkbox',
			'value'     => self::$settings['add_to_cart']['replace_redirect_to_cart_with_checkout'],
			'name_keys' => array( 'add_to_cart', 'replace_redirect_to_cart_with_checkout' ),
		);
		$this->add_to_cart_fields[] = array(
			'name' => '',
			'type' => 'sectionend',
		);

	}

	/**
	 * Get Global Custom Add To Cart.
	 *
	 * @return string
	 */
	private function get_global_custom_add_to_cart_text( $is_variable = false ) {
		if ( $is_variable ) {
			return Settings::get_setting( 'add_to_cart', 'custom_add_to_cart_variable_text' );
		}
		return Settings::get_setting( 'add_to_cart', 'custom_add_to_cart_text' );
	}
}
