<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;
use GPLSCore\GPLS_PLUGIN_ARCW\Single;
use GPLSCore\GPLS_PLUGIN_ARCW\AddToCart;

/**
 * Quick View Popups Class.
 */
class Popup {

	/**
	 * Settings.
	 *
	 * @var array
	 */
	private static $settings;

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
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-quick_view_popup', array( $this, 'get_product_add_to_cart_popup' ) );
		add_action( 'wp_ajax_nopriv_' . self::$plugin_info['name'] . '-quick_view_popup', array( $this, 'get_product_add_to_cart_popup' ) );

		add_filter( 'woocommerce_locate_template', array( $this, 'filter_custom_templates_for_popup' ), PHP_INT_MAX, 3 );
		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'custom_class_for_single_product_gallery_in_popup' ), PHP_INT_MAX, 1 );

		add_action( self::$plugin_info['name'] . '-variable-in-grouped-selected-options-template', array( $this, 'ajax_variable_in_grouped_selected_options_template' ) );

		add_filter( self::$plugin_info['name'] . '-before-product-quick-view-popup', array( $this, 'adjust_hooks_before_quick_view_popup' ), 100, 2 );
		add_filter( self::$plugin_info['name'] . '-after-product-quick-view-popup', array( $this, 'adjust_hooks_after_quick_view_popup' ), 100, 1 );

		// Quick View Button Shortcode.
		add_action( 'init', array( $this, 'quick_view_button_shortcode' ) );

		add_filter( 'woocommerce_locate_template', array( $this, 'fallback_to_default_template' ), PHP_INT_MAX, 3 );

		add_filter( 'woocommerce_disable_compatibility_layer', array( $this, 'disable_removing_hooks_in_popup' ), PHP_INT_MAX, 1 );
	}

	/**
	 * Disable removing hooks of single product in popups.
	 *
	 * @param boolean $status
	 * @return boolean
	 */
	public function disable_removing_hooks_in_popup( $status ) {
		global $product;
		if ( ! is_a( $product, '\WC_Product' ) ) {
			return $status;
		}
		if ( self::is_quick_view_main_product( $product ) ) {
			$status = true;
		}
		return $status;
	}

	/**
	 * Fallback to Default WooCommerce Template.
	 *
	 * @param string $template_path
	 * @param string $template_name
	 * @param string $template_path
	 * @return string
	 */
	public function fallback_to_default_template( $template, $template_name, $template_path ) {
		if ( self::is_quick_view_popup_request() && 'yes' === self::$settings['quick_view']['force_default_variable_dropdowns'] ) {
			if ( 'single-product/add-to-cart/variable.php' === $template_name ) {
				$template = WC()->plugin_path() . '/templates/' . $template_name;
			}
		}
		return $template;
 	}

	/**
	 * Quick View Button Shortcode Register.
	 *
	 * @return void
	 */
	public function quick_view_button_shortcode() {
		add_shortcode( self::$plugin_info['classes_prefix'] . '-quick-view-button', array( $this, 'quick_view_button_shortcode_content' ) );
	}

	/**
	 * Quick View Button Shortcode HTML.
	 *
	 * @return string
	 */
	public function quick_view_button_shortcode_content( $attrs ) {
		$product_id = 0;
		$_product   = null;

		if ( ! empty( $attrs['product_id'] ) ) {
			$product_id = absint( sanitize_text_field( wp_unslash( $attrs['product_id'] ) ) );
			$_product   = wc_get_product( $product_id );
		} else {
			global $product;
			if ( is_a( $product, '\WC_Product' ) ) {
				$_product   = $product;
				$product_id = $_product->get_id();
			}
		}

		if ( is_null( $_product ) ) {
			return;
		}

		ob_start();
		$GLOBALS[ self::$plugin_info['name'] . '-quick-view-button-shortcode' ] = true;
		Single::quick_view_button( $_product, AddToCart::quick_view_button_args( $_product ), true );
		unset( $GLOBALS[ self::$plugin_info['name'] . '-quick-view-button-shortcode' ] );
		return ob_get_clean();
	}

	/**
	 * Populate the Product to GLOBALS.
	 *
	 * @param object $product
	 * @return void
	 */
	private static function populate_the_product( $product ) {
		global $wp_query, $wp_the_query, $post;
		$args = array(
			'p'                   => $product->get_id(),
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => ( ! empty( $atts['status'] ) ) ? $atts['status'] : 'publish',
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1,
		);
		if ( isset( $atts['sku'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_sku',
				'value'   => sanitize_text_field( $atts['sku'] ),
				'compare' => '=',
			);

			$args['post_type'] = array( 'product', 'product_variation' );
		}
		$GLOBALS['product'] = $product;
		$single_product     = new \WP_Query( $args );
		$wp_query           = $single_product;
		$wp_the_query       = $wp_query;
		$post               = isset( $wp_query->post ) ? $wp_query->post : null;
	}

	/**
	 * Is Quick View Popup Request.
	 *
	 * @return boolean
	 */
	public static function is_quick_view_popup_request() {
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( wp_doing_ajax() && ! empty( $_POST['action'] ) && ( self::$plugin_info['name'] . '-quick_view_popup' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) );
	}

	/**
	 * Check if the given product is the main product of the quick view popup. ( not a related - upsell product in the popup )
	 *
	 * @param object $product Product Object.
	 * @return boolean
	 */
	public static function is_quick_view_main_product( $product ) {
		if ( ! self::is_quick_view_popup_request() ) {
			return false;
		}
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $_POST['product_id'] ) ) {
			return false;
		}
		// phpcs:ignore WordPress.Security.NonceVerification
		$product_id  = absint( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) );
		$product_obj = wc_get_product( $product_id );
		if ( ! $product_obj ) {
			return false;
		}

		if ( $product->get_id() === $product_obj->get_id() ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter any custom templates for the popup.
	 *
	 * @param string $template  Template PATH in the active theme.
	 * @param string $template_name  Template name ( sub-directory/file-name.php ).
	 * @param string $template_path  The Default WooCommerce Path ( woocommerce/ ).
	 * @return string
	 */
	public function filter_custom_templates_for_popup( $template, $template_name, $template_path ) {
		if ( self::is_quick_view_popup_request() ) {
			$forced_default_templates = array( 'single-product/product-image.php', 'single-product/product-thumbnails.php' );
			$template_default_path    = WC()->plugin_path() . '/templates/' . $template_name;
			// Force the default gallery templates.
			if ( in_array( $template_name, $forced_default_templates ) ) {
				return $template_default_path;
			}
		}
		return $template;
	}

	/**
	 * Check if variable quick view popup in grouped product popup request.
	 *
	 * @return boolean
	 */
	public static function is_variable_popup_in_grouped_popup_request() {
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) && 'popup-variable-in-grouped' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) ) );
	}

	/**
	 * Check if a variable quick view popup in single grouped product request.
	 *
	 * @return boolean
	 */
	public static function is_variable_popup_in_grouped_single_request() {
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) && 'single-grouped' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) ) );
	}

	/**
	 * Check if a Grouped Quick View popup request.
	 *
	 * @return boolean
	 */
	public static function is_grouped_quick_view_popup() {
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) && 'grouped' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) ) );
	}

	/**
	 * Check if an External Quick View popup request.
	 *
	 * @return boolean
	 */
	public static function is_external_quick_view_popup() {
		// phpcs:ignore WordPress.Security.NonceVerification
		return ( wp_doing_ajax() && ! empty( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) && 'external' === sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '_additional_action' ] ) ) );
	}

	/**
	 * Add Custom Class for single product gallery wrapper in popups.
	 *
	 * @param array $classes_arr
	 * @return array
	 */
	public function custom_class_for_single_product_gallery_in_popup( $classes_arr ) {

		if ( self::is_quick_view_popup_request() ) {
			$classes_arr[] = self::$plugin_info['classes_prefix'] . '-single-product-gallery-in-popup';
		}
		return $classes_arr;
	}

	/**
	 * Ajax Get Product Quick View Popup.
	 *
	 * @return void
	 */
	public function get_product_add_to_cart_popup() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ! empty( $_POST['product_id'] ) ) {

			$product_id     = absint( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) );
			$woo_context    = ! empty( $_POST['woo_context'] ) ? sanitize_text_field( wp_unslash( $_POST['woo_context'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$product        = wc_get_product( $product_id );
			$single_product = new \WP_Query(
				array(
					'p'                   => $product_id,
					'posts_per_page'      => 1,
					'post_type'           => 'product',
					'post_status'         => 'publish',
					'ignore_sticky_posts' => 1,
					'no_found_rows'       => 1,
				)
			);
			$plugin_info    = self::$plugin_info;

			if ( ! $single_product ) {
				wp_send_json(
					array(
						'result' => false,
					)
				);
			}

			// Allow integration to custom quick view popups.
			$result = apply_filters( self::$plugin_info['name'] . '-custom-quick-view-popup-integration', false, $product_id );

			// Fallback to the default popup.
			if ( ! $result ) {
				ob_start();
				require_once self::$plugin_info['path'] . 'templates/popups/add-to-cart-popup.php';
				$result = ob_get_clean();
			}

			wp_send_json(
				array(
					'result' => true,
					'popup'  => $result,
					'misc'   => array(
						'add_to_cart_status' => Single::add_to_cart_status( $product, 'popup' ),
					),
				)
			);

		}
		wp_send_json(
			array(
				'result' => false,
			)
		);
	}

	/**
	 * Ajax Fill the Variable in Grouped Product Selected Options Template.
	 *
	 * @return void
	 */
	public function ajax_variable_in_grouped_selected_options_template() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), self::$plugin_info['name'] . '-arcw-main-nonce' ) ) {
			if ( empty( $_POST['product_id'] ) ) {
				wp_send_json(
					array(
						'result' => false,
						'type'   => 'missing-product-id',
					)
				);
			}

			$product_id  = absint( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) );
			$product_obj = wc_get_product( $product_id );

			if ( ! $product_obj || is_null( $product_obj ) || ( is_object( $product_obj ) && 'variable' !== $product_obj->get_type() ) ) {
				wp_send_json(
					array(
						'result' => false,
						'type'   => 'missing-product-id',
					)
				);
			}
		}
	}

	/**
	 * Control the Hooks ( filters - actions ) before the quick view popup.
	 *
	 * @param object $product Product Object.
	 * @return void
	 */
	public function adjust_hooks_before_quick_view_popup( $popup_content, $product ) {
		// Remove after the summary sections.
		remove_all_actions( 'woocommerce_after_single_product_summary' );

		// Check if additional sections enabled in the popup.
		if ( Settings::is_quick_view_popup_section_global_enabled( 'data_tabs' ) ) {
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		}
		if ( Settings::is_quick_view_popup_section_global_enabled( 'upsells' ) ) {
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		}
		if ( Settings::is_quick_view_popup_section_global_enabled( 'related_products' ) ) {
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}

		// Remove after the single product sections.
		remove_all_actions( 'woocommerce_after_single_product' );
		return $popup_content;
	}

	/**
	 * Restore any hooks ( filters - actions ) after the quick view popup.
	 *
	 * @param object $product Product Object.
	 * @return void
	 */
	public function adjust_hooks_after_quick_view_popup( $product ) {

	}

	/**
	 * Check if It's Our Quick View.
	 *
	 * @return boolean
	 */
	public static function is_quick_view() {
		return ( wp_doing_ajax() && ! empty( $_POST['action'] ) && ( self::$plugin_info['name'] . '-quick_view_popup' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) );
	}

	/**
	 * Check If It's a different QuicK View.
	 *
	 * @return boolean
	 */
	public static function is_different_quick_view() {
		return (
			wp_doing_ajax()
		&&
			(
				empty( $_POST['action'] )
			||
				( ! empty( $_POST['action'] ) && ( self::$plugin_info['name'] . '-quick_view_popup' !== sanitize_text_field( wp_unslash( $_POST['action'] ) ) ) )
			)
		);
	}
}
