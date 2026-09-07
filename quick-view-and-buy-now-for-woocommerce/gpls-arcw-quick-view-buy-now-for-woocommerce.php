<?php

namespace GPLSCore\GPLS_PLUGIN_ARCW;

/**
 * Plugin Name:  Direct Checkout | Quick View | Buy Now For WooCommerce
 * Description:  The plugin provides many features to help you ease and boost the purchase process in your woocommerce store.
 * Author:       GrandPlugins
 * Author URI:   https://profiles.wordpress.org/grandplugins/
 * Plugin URI:   https://grandplugins.com/product/quick-view-and-buy-now-for-woocommerce/
 * Domain Path:  /languages
 * Requires PHP: 7.0
 * Text Domain:  quick-view-and-buy-now-for-woocommerce
 * Std Name:     gpls-arcw-quick-view-buy-now-for-woocommerce
 * Version:      1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_ARCW\Core;
use GPLSCore\GPLS_PLUGIN_ARCW\Single;
use GPLSCore\GPLS_PLUGIN_ARCW\AddToCart;
use GPLSCore\GPLS_PLUGIN_ARCW\Popup;
use GPLSCore\GPLS_PLUGIN_ARCW\Buynow;
use GPLSCore\GPLS_PLUGIN_ARCW\Checkout;
use GPLSCore\GPLS_PLUGIN_ARCW\DirectPurchase;
use GPLSCore\GPLS_PLUGIN_ARCW\ScreenLoader;
use GPLSCore\GPLS_PLUGIN_ARCW\QuantityInput;
use GPLSCore\GPLS_PLUGIN_ARCW\CustomCSS;


if ( ! class_exists( __NAMESPACE__ . '\GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce' ) ) :
	/**
	 * Main Class.
	 */
	class GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce {

		/**
		 * Single Instance
		 *
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Plugin Info
		 *
		 * @var array
		 */
		private static $plugin_info;

		/**
		 * Debug Mode Status
		 *
		 * @var bool
		 */
		protected $debug = false;

		/**
		 * Core Object
		 *
		 * @var object
		 */
		private static $core;

		/**
		 * Settings Class Object.
		 *
		 * @var object
		 */
		public $settings;

		/**
		 * Single Product Class Object.
		 *
		 * @var object
		 */
		public $single;

		/**
		 * AddToCart Class Object.
		 *
		 * @var object
		 */
		public $add_to_cart;

		/**
		 * Checkout Class Object.
		 *
		 * @var object
		 */
		public $checkout;

		/**
		 * Popup Class Object.
		 *
		 * @var object
		 */
		public $popup;

		/**
		 * Buynow Class Object.
		 *
		 * @var object
		 */
		public $buynow;

		/**
		 * Singular init Function.
		 *
		 * @return Object
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Core Actions Hook.
		 *
		 * @return void
		 */
		public static function core_actions( $action_type ) {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
			self::$core = new Core( self::$plugin_info );
			if ( 'activated' === $action_type ) {
				self::$core->plugin_activated();
			} elseif ( 'deactivated' === $action_type ) {
				self::$core->plugin_deactivated();
			} elseif ( 'uninstall' === $action_type ) {
				self::$core->plugin_uninstalled();
			}
		}

		/**
		 * Plugin Activated Hook.
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::setup_plugin_info();
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once \ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				deactivate_plugins( self::$plugin_info['basename'] );
				wp_die( esc_html__( 'WooCommerce plugin is required in order to activate the plugin', 'quick-view-and-buy-now-for-woocommerce' ) );
			}
			if ( is_plugin_active( self::$plugin_info['text_domain'] . '/' . self::$plugin_info['name'] . '-pro.php' ) ) {
				deactivate_plugins( plugin_basename( self::$plugin_info['text_domain'] . '/' . self::$plugin_info['name'] . '-pro.php' ) );
			}
			self::core_actions( 'activated' );
			Settings::activated( self::$plugin_info );
			register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce', 'plugin_uninstalled' ) );
		}

		/**
		 * Plugin Deactivated Hook.
		 *
		 * @return void
		 */
		public static function plugin_deactivated() {
			self::setup_plugin_info();
			self::core_actions( 'deactivated' );
		}

		/**
		 * Plugin Installed hook.
		 *
		 * @return void
		 */
		public static function plugin_uninstalled() {
			self::setup_plugin_info();
			self::core_actions( 'uninstall' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			self::setup_plugin_info();
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once \ABSPATH . 'wp-admin/includes/plugin.php';
			}
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				deactivate_plugins( self::$plugin_info['basename'] );
				return;
			}

			$this->load_languages();
			$this->setup();
			$this->includes();

			if ( ! class_exists( 'woocommerce' ) ) {
				require_once \ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( self::$plugin_info['basename'] );
				return;
			}

			self::$core        = new Core( self::$plugin_info );
			$this->settings    = new Settings( self::$core, self::$plugin_info );
			$this->single      = new Single( self::$core, self::$plugin_info );
			$this->add_to_cart = new AddToCart( self::$core, self::$plugin_info );
			$this->popup       = new Popup( self::$core, self::$plugin_info );
			$this->buynow      = new Buynow( self::$core, self::$plugin_info );
			$this->checkout    = new Checkout( self::$core, self::$plugin_info );

			new DirectPurchase( self::$core, self::$plugin_info );
			new ScreenLoader( self::$core, self::$plugin_info );
			new QuantityInput( self::$core, self::$plugin_info );
			new CustomCSS( self::$core, self::$plugin_info );

			self::funnel();
		}

		/**
		 * Contextual upgrade prompts.
		 *
		 * Both offers are measured from the shop itself. They are passed as a
		 * closure so their strings are translated when the notice renders -
		 * this plugin boots on plugins_loaded, and translating before init
		 * trips WordPress 6.7's "translation loading triggered too early".
		 *
		 * @return void
		 */
		private static function funnel() {
			if ( ! class_exists( '\GPLS_Funnel' ) || ! function_exists( 'wc_get_products' ) ) {
				return;
			}

			\GPLS_Funnel::boot(
				array(
					'slug'       => 'quick-view-and-buy-now-for-woocommerce',
					'name'       => 'Direct Checkout, Quick View and Buy Now',
					'textdomain' => 'quick-view-and-buy-now-for-woocommerce',
					'cap'        => 'manage_woocommerce',
					'screens'    => array(
						'edit-product',
						'tools_page_' . self::$plugin_info['name'],
					),
					'offers'     => function () {
						return array(
							array(
								'id'         => 'loop_buy_now',
								'product'    => 'quick-view-and-buy-now-for-woocommerce',
								'when'       => function () {
									$count = (int) wp_count_posts( 'product' )->publish;

									// Under a handful of products nobody is
									// browsing a shop page in the first place.
									return $count >= 10 ? array( 'products' => $count ) : false;
								},
								'stat'       => '{products}',
								'stat_label' => esc_html__( 'products', 'quick-view-and-buy-now-for-woocommerce' ),
								'title'      => esc_html__( 'Your shop lists {products} products with no Buy Now button', 'quick-view-and-buy-now-for-woocommerce' ),
								'body'       => esc_html__( 'Quick View saves a page load on those listings, but a shopper who has already decided still has to add to cart and then go and find the checkout. Premium puts Buy Now on shop and category pages, and can hide Add to Cart so there is one button instead of two.', 'quick-view-and-buy-now-for-woocommerce' ),
								'cta'        => esc_html__( 'See what Premium adds', 'quick-view-and-buy-now-for-woocommerce' ),
							),
							array(
								'id'         => 'sales_without_deadline',
								'product'    => 'simple-countdown-timer',
								'when'       => function () {
									if ( ! function_exists( 'wc_get_product_ids_on_sale' ) ) {
										return false;
									}

									$dateless = 0;

									foreach ( wc_get_product_ids_on_sale() as $id ) {
										$product = wc_get_product( $id );

										if ( ! $product ) {
											continue;
										}

										$to = $product->get_date_on_sale_to();

										if ( ! $to || $to->getTimestamp() <= time() ) {
											$dateless++;
										}
									}

									return $dateless >= 3 ? array( 'dateless' => $dateless ) : false;
								},
								'stat'       => '{dateless}',
								'stat_label' => esc_html__( 'no deadline', 'quick-view-and-buy-now-for-woocommerce' ),
								'title'      => esc_html__( '{dateless} of your products are on sale with no end date', 'quick-view-and-buy-now-for-woocommerce' ),
								'body'       => esc_html__( 'A lower price is a fact. A lower price that ends on Friday is a reason to buy today. Simple Countdown Timer reads the sale end date WooCommerce already stores and shows a countdown on the product page.', 'quick-view-and-buy-now-for-woocommerce' ),
								'cta'        => esc_html__( 'See Simple Countdown Timer', 'quick-view-and-buy-now-for-woocommerce' ),
							),
						);
					},
				)
			);
		}

		/**
		 * Includes Files
		 *
		 * @return void
		 */
		public function includes() {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/Funnel.php';
		}

		/**
		 * Load languages Folder.
		 *
		 * @return void
		 */
		public function load_languages() {
			load_plugin_textdomain( self::$plugin_info['text_domain'], false, self::$plugin_info['path'] . 'languages/' );
		}

		/**
		 * Setup Function - Initialize Vars
		 *
		 * @return void
		 */
		public function setup() {
			$this->options_page_slug = self::$plugin_info['name'];
			$this->options_page_url  = admin_url( 'tools.php' ) . '?page=' . self::$plugin_info['name'];
		}

		/**
		 * Set Plugin Info
		 *
		 * @return array
		 */
		public static function setup_plugin_info() {
			$plugin_data = get_file_data(
				__FILE__,
				array(
					'Version'     => 'Version',
					'Name'        => 'Plugin Name',
					'URI'         => 'Plugin URI',
					'SName'       => 'Std Name',
					'text_domain' => 'Text Domain',
				),
				false
			);

			self::$plugin_info = array(
				'id'              => 690,
				'basename'        => plugin_basename( __FILE__ ),
				'version'         => $plugin_data['Version'],
				'name'            => $plugin_data['SName'],
				'text_domain'     => $plugin_data['text_domain'],
				'file'            => __FILE__,
				'plugin_url'      => $plugin_data['URI'],
				'public_name'     => $plugin_data['Name'],
				'path'            => trailingslashit( plugin_dir_path( __FILE__ ) ),
				'url'             => trailingslashit( plugin_dir_url( __FILE__ ) ),
				'options_page'    => $plugin_data['SName'],
				'localize_var'    => str_replace( '-', '_', $plugin_data['SName'] ) . '_localize_data',
				'type'            => 'free',
				'general_prefix'  => 'gpls-plugins-general-prefix',
				'classes_prefix'  => 'gpls-arcw',
				'prefix'          => 'gpls-arcw',
				'classes_general' => 'gpls-general',
				'pro_link'        => 'https://grandplugins.com/product/quick-view-and-buy-now-for-woocommerce/?utm_source=free&utm_medium=pro_btn',
			);
		}

		/**
		 * Define Constants
		 *
		 * @param string $key
		 * @param string $value
		 * @return void
		 */
		public function define( $key, $value ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
			}
		}

	}

	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce', 'init' ), 1000 );
	register_activation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce', 'plugin_activated' ) );
	register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ARCW_Quick_View_And_Buy_Now_For_WooCommerce', 'plugin_deactivated' ) );
endif;
