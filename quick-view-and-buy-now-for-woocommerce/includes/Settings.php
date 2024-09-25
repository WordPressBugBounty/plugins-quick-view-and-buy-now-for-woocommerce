<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Buynow;

/**
 * Redirects To Checkout Class.
 */
class Settings {

	/**
	 * Core Object
	 *
	 * @var object
	 */
	public $core;

	/**
	 * Plugin Info
	 *
	 * @var object
	 */
	public static $plugin_info;

	/**
	 * Settings Name.
	 *
	 * @var string
	 */
	public static $settings_name;

	/**
	 * Settings Tab Key
	 *
	 * @var string
	 */
	protected $settings_tab_key;

	/**
	 * Settings Tab name
	 *
	 * @var array
	 */
	protected $settings_tab;


	/**
	 * Current Settings Active Tab.
	 *
	 * @var string
	 */
	protected $current_active_tab;

	/**
	 * Default Settings.
	 *
	 * @var array
	 */
	public static $default_settings = array(
		'add_to_cart'    => array(
			'custom_add_to_cart_text'                 => '',
			'custom_add_to_cart_variable_text'        => '',
			'replace_continue_shopping_with_checkout' => 'no',
			'checkout_in_add_to_cart_msg'             => 'no',
			'replace_redirect_to_cart_with_checkout'  => 'no',
			'color'                                   => '',
			'bg'                                      => '',
		),
		'quick_view'     => array(
			'quick_view_text'                      => '',
			'quick_view_position'                  => 'before',
			'data_tabs_section'                    => 'no',
			'upsells_section'                      => 'no',
			'related_products_section'             => 'no',
			'force_default_variable_dropdowns'     => 'no',
			'force_default_product_gallery_styles' => 'no',
			'display_loader'                       => 'no',
			'color'                                => '',
			'bg'                                   => '',
		),
		'buy_now'        => array(
			'buy_now_text'     => '',
			'buy_now_position' => 'before',
			'redirect_after'   => 'checkout',
			'buy_now_ajax'     => 'yes',
			'display_loader'   => 'no',
			'color'            => '',
			'bg'               => '',
		),
		'direct_purchase' => array(
			'enable_by_category' => array(),
		),
		'checkout'       => array(
			'show_cart_on_checkout' => 'no',
			'redirect_after_checkout_with_payment'    => 0,
			'redirect_after_checkout_without_payment' => 0,
		),
		'advanced'       => array(
			'custom_css' => '',
		),
		'loading_screen' => array(
			'selected' => 1,
			'random'   => array(),
		),
	);

	/**
	 * Popup Show Animation Options classes.
	 *
	 * @var array
	 */
	public static $popup_show_animation_options = array(
		'Attention seekers'  => array(
			'animate__bounce'     => 'bounce',
			'animate__flash'      => 'flash',
			'animate__pulse'      => 'pulse',
			'animate__rubberBand' => 'rubberBand',
			'animate__shakeX'     => 'shakeX',
			'animate__shakeY'     => 'shakeY',
			'animate__headShake'  => 'headShake',
			'animate__swing'      => 'swing',
			'animate__tada'       => 'tada',
			'animate__wobble'     => 'wobble',
			'animate__jello'      => 'jello',
			'animate__heartBeat'  => 'heartBeat',
		),
		'Back entrances'     => array(
			'animate__backInDown'  => 'backInDown',
			'animate__backInLeft'  => 'backInLeft',
			'animate__backInRight' => 'backInRight',
			'animate__backInUp'    => 'backInUp',
		),
		'Bouncing entrances' => array(
			'animate__bounceIn'      => 'bounceIn',
			'animate__bounceInDown'  => 'bounceInDown',
			'animate__bounceInLeft'  => 'bounceInLeft',
			'animate__bounceInRight' => 'bounceInRight',
			'animate__bounceInUp'    => 'bounceInUp',
		),
		'Fading entrances'   => array(
			'animate__fadeIn'            => 'fadeIn',
			'animate__fadeInDown'        => 'fadeInDown',
			'animate__fadeInDownBig'     => 'fadeInDownBig',
			'animate__fadeInLeft'        => 'fadeInLeft',
			'animate__fadeInLeftBig'     => 'fadeInLeftBig',
			'animate__fadeInRight'       => 'fadeInRight',
			'animate__fadeInRightBig'    => 'fadeInRightBig',
			'animate__fadeInUp'          => 'fadeInUp',
			'animate__fadeInUpBig'       => 'fadeInUpBig',
			'animate__fadeInTopLeft'     => 'fadeInTopLeft',
			'animate__fadeInTopRight'    => 'fadeInTopRight',
			'animate__fadeInBottomLeft'  => 'fadeInBottomLeft',
			'animate__fadeInBottomRight' => 'fadeInBottomRight',
		),
		'Flippers'           => array(
			'animate__flipInX' => 'flipInX',
			'animate__flipInY' => 'flipInY',
		),
		'Lightspeed'         => array(
			'animate__lightSpeedInRight' => 'lightSpeedInRight',
			'animate__lightSpeedInLeft'  => 'lightSpeedInLeft',
		),
		'Rotating entrances' => array(
			'animate__rotateIn'          => 'rotateIn',
			'animate__rotateInDownLeft'  => 'rotateInDownLeft',
			'animate__rotateInDownRight' => 'rotateInDownRight',
			'animate__rotateInUpLeft'    => 'rotateInUpLeft',
			'animate__rotateInUpRight'   => 'rotateInUpRight',
		),
		'Specials'           => array(
			'animate__jackInTheBox' => 'jackInTheBox',
			'animate__rollIn'       => 'rollIn',
		),
		'Zooming entrances'  => array(
			'animate__zoomIn'      => 'zoomIn',
			'animate__zoomInDown'  => 'zoomInDown',
			'animate__zoomInLeft'  => 'zoomInLeft',
			'animate__zoomInRight' => 'zoomInRight',
			'animate__zoomInUp'    => 'zoomInUp',
		),
		'Sliding entrances'  => array(
			'animate__slideInDown'  => 'slideInDown',
			'animate__slideInLeft'  => 'slideInLeft',
			'animate__slideInRight' => 'slideInRight',
			'animate__slideInUp'    => 'slideInUp',
		),
	);


	/**
	 * Popup Animation Hide Options classes.
	 *
	 * @var array
	 */
	public static $popup_hide_animation_options = array(
		'Back exits'     => array(
			'animate__backOutDown'  => 'backOutDown',
			'animate__backOutLeft'  => 'backOutLeft',
			'animate__backOutRight' => 'backOutRight',
			'animate__backOutUp'    => 'backOutUp',
		),
		'Bouncing exits' => array(
			'animate__bounceOut'      => 'bounceOut',
			'animate__bounceOutDown'  => 'bounceOutDown',
			'animate__bounceOutLeft'  => 'bounceOutLeft',
			'animate__bounceOutRight' => 'bounceOutRight',
			'animate__bounceOutUp'    => 'bounceOutUp',
		),
		'Fading exits'   => array(
			'animate__fadeOut'            => 'fadeOut',
			'animate__fadeOutDown'        => 'fadeOutDown',
			'animate__fadeOutDownBig'     => 'fadeOutDownBig',
			'animate__fadeOutLeft'        => 'fadeOutLeft',
			'animate__fadeOutLeftBig'     => 'fadeOutLeftBig',
			'animate__fadeOutRight'       => 'fadeOutRight',
			'animate__fadeOutRightBig'    => 'fadeOutRightBig',
			'animate__fadeOutUp'          => 'fadeOutUp',
			'animate__fadeOutUpBig'       => 'fadeOutUpBig',
			'animate__fadeOutTopLeft'     => 'fadeOutTopLeft',
			'animate__fadeOutTopRight'    => 'fadeOutTopRight',
			'animate__fadeOutBottomRight' => 'fadeOutBottomRight',
			'animate__fadeOutBottomLeft'  => 'fadeOutBottomLeft',
		),
		'Flippers'       => array(
			'animate__flipOutX' => 'flipOutX',
			'animate__flipOutY' => 'flipOutY',
		),
		'Lightspeed'     => array(
			'animate__lightSpeedOutRight' => 'lightSpeedOutRight',
			'animate__lightSpeedOutLeft'  => 'lightSpeedOutLeft',
		),
		'Rotating exits' => array(
			'animate__rotateOut'          => 'rotateOut',
			'animate__rotateOutDownLeft'  => 'rotateOutDownLeft',
			'animate__rotateOutDownRight' => 'rotateOutDownRight',
			'animate__rotateOutUpLeft'    => 'rotateOutUpLeft',
			'animate__rotateOutUpRight'   => 'rotateOutUpRight',
		),
		'Specials'       => array(
			'animate__hinge'   => 'hinge',
			'animate__rollOut' => 'rollOut',
		),
		'Zooming exits'  => array(
			'animate__zoomOut'      => 'zoomOut',
			'animate__zoomOutDown'  => 'zoomOutDown',
			'animate__zoomOutLeft'  => 'zoomOutLeft',
			'animate__zoomOutRight' => 'zoomOutRight',
			'animate__zoomOutUp'    => 'zoomOutUp',
		),
		'Sliding exits'  => array(
			'animate__slideOutDown'  => 'slideOutDown',
			'animate__slideOutLeft'  => 'slideOutLeft',
			'animate__slideOutRight' => 'slideOutRight',
			'animate__slideOutUp'    => 'slideOutUp',
		),
	);

	/**
	 * Settings Array.
	 *
	 * @var array
	 */
	public static $settings;

	/**
	 * Settings Tab Fields
	 *
	 * @var Array
	 */
	protected $fields = array();

	/**
	 * Constructor.
	 *
	 * @param object $core Core Object.
	 * @param object $plugin_info Plugin Info Object.
	 */
	public function __construct( $core, $plugin_info ) {
		$this->core               = $core;
		self::$plugin_info        = $plugin_info;
		$this->settings_tab_key   = self::$plugin_info['name'] . '-settings-tab';
		self::$settings_name      = self::$plugin_info['name'] . '-main-settings-name';
		$this->settings_tab       = array( $this->settings_tab_key => esc_html__( 'Quick View and Buy Now', 'woocommrece' ) );
		$this->current_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'quick_view';
		$this->init();
		self::$settings = self::get_main_settings();
		$this->hooks();
	}

	/**
	 * Initialize function.
	 *
	 * @return void
	 */
	public function init() {
		$product_types                                    = wc_get_product_types();
		self::$default_settings['advanced']['custom_css'] = '';
		self::$default_settings['quick_view']['quick_view_text'] = esc_html__( 'Quick View', 'quick-view-and-buy-now-for-woocommerce' );
		self::$default_settings['buy_now']['buy_now_text']       = esc_html__( 'Buy Now', 'quick-view-and-buy-now-for-woocommerce' );
		foreach ( $product_types as $product_type_key => $product_type_label ) {
			self::$default_settings['quick_view'][ 'enable_by_product_type_' . $product_type_key ] = 'no';
			self::$default_settings['buy_now'][ 'enable_by_product_type_' . $product_type_key ]    = 'no';
		}
		foreach ( Buynow::get_loop_buy_now_product_types() as $product_type_key => $product_type_label ) {
			self::$default_settings['loop_buy_now'][ 'product_type_' . $product_type_key ] = 'no';
			self::$default_settings['loop_buy_now'][ 'product_type_' . $product_type_key ] = 'no';
		}
		foreach ( $product_types as $product_type_key => $product_type_label ) {
			self::$default_settings['add_to_cart'][ 'general_redirect_by_product_type_' . $product_type_key ] = 0;
		}
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 100, 1 );
		foreach ( array_keys( $this->settings_tab ) as $name ) {
			add_action( 'woocommerce_settings_' . $name, array( $this, 'settings_tab_action' ), 10 );
			add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
		}
		add_action( 'woocommerce_sections_' . $this->settings_tab_key, array( $this, 'settings_tabs' ), 100 );

		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_assets' ) );

		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-quick-view-popup-show-animation-class-name', array( $this, 'popup_show_animation_select_html' ), 100, 1 );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-quick-view-popup-hide-animation-class-name', array( $this, 'popup_hide_animation_select_html' ), 100, 1 );

		add_action( 'plugin_action_links_' . self::$plugin_info['basename'], array( $this, 'settings_link' ), 5, 1 );

		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-quick-view-button-shortcode-details', array( $this, 'quick_view_button_shortcode_details' ) );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-quick-view-popup-carousel', array( $this, 'quick_view_carousel_details' ) );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-buy-now-button-shortcode-details', array( $this, 'buy_now_button_shortcode_details' ) );

		// YITH product bundle comp.
		add_filter( 'yith_wcpb_get_frontend_assets', array( $this, 'yith_product_bundle_assets' ), 1000, 1 );

		// Review Notice.
		add_action( 'woocommerce_after_settings_' . $this->settings_tab_key, array( $this, 'review_notice' ) );
		add_action( 'admin_footer', array( $this, 'late_js' ), PHP_INT_MAX );
	}

	/**
	 * Plugin Activated.
	 *
	 * @param array $plugin_info
	 * @return void
	 */
	public static function activated( $plugin_info ) {
		set_transient( $plugin_info['name'] . '-activation-notice', true );
	}

	/**
	 * Add new updates Notice.
	 *
	 * @return void
	 */
	public function new_updates_notice() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && property_exists( $screen, 'id' ) && ( 'plugins' === $screen->id ) && ! empty( $_GET['activate'] ) && ! empty( get_transient( self::$plugin_info['name'] . '-activation-notice' ) ) ) :
			delete_transient( self::$plugin_info['name'] . '-activation-notice' );
			?>
			<div id="message" class="notice is-dismissible notice-warning">
				<p><?php echo esc_html__( 'New Features available ', 'quick-view-and-buy-now-for-woocommerce' ) . '<b> "Screen Loading" </b> and <b> "Custom Quantity Input" in </b> ' . ' <a href="' . esc_url_raw( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key ) ) . '" >  ' . esc_html__( 'settings', 'gpls-wmfw-watermark-image-for-wordpress' ) . '</a>  '; ?></p>
			</div>
			<?php
		endif;
	}

	/**
	 * YITH product Bundle Compatibility.
	 *
	 * @param array $assets
	 * @return array
	 */
	public function yith_product_bundle_assets( $assets ) {
		$assets['styles']['yith_wcpb_bundle_frontend_style']['where'] = false;
		return $assets;
	}

	/**
	 * Settings Link.
	 *
	 * @param array $links Plugin Row Links.
	 * @return array
	 */
	public function settings_link( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
		$links[] = '<a href="https://grandplugins.com/product/quick-view-and-buy-now-for-woocommerce/?utm_source=free&utm_medium=plugin_page">' . esc_html__( 'Premium' ) . '</a>';
		return $links;
	}

	/**
	 * Settings Page Assets.
	 *
	 * @return void
	 */
	public function settings_page_assets() {
		if ( ! empty( $_GET['tab'] ) && in_array( wp_unslash( $_GET['tab'] ), array_keys( $this->settings_tab ) ) ) {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( self::$plugin_info['name'] . '-bootstrap-css', $this->core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-admin-styels', self::$plugin_info['url'] . 'assets/dist/css/admin/admin-styles.min.css', array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_media();
			wp_enqueue_editor();
			if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_code_editor(
				array(
					'type' => 'text/css',
				)
			);
			wp_enqueue_script( self::$plugin_info['name'] . '-settings-js', self::$plugin_info['url'] . 'assets/dist/js/admin/settings-actions.min.js', array( 'jquery', 'wp-i18n' ), self::$plugin_info['version'], true );
		}
	}

	/**
	 * Late JS for settings.
	 */
	public function late_js() {
		$tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		if ( ! empty( $tab ) && in_array( wp_unslash( $tab ), array_keys( $this->settings_tab ) ) ) :
		?>
		<script>
			if ( window.jQuery ) {
				( function($) {
					$( function(e) {
						$('.wp-color-picker:not([disabled="disabled"])').wpColorPicker();
					});
				})(jQuery);
			}
		</script>
		<?php
		endif;
	}

	/**
	 * Create the Tab Fields
	 *
	 * @return mixed
	 */
	public function create_settings_fields( $return = false ) {
		$fields        = array();
		$product_types = wc_get_product_types();
		$main_settings = self::get_main_settings();

		// General Tab.
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title' => esc_html__( 'Quick View', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'  => esc_html__( '"Quick View" Button in shop and archive pages', 'quick-view-and-buy-now-for-woocommerce' ),
			'type'  => 'title',
			'id'    => self::$plugin_info['name'] . '-quick-view-settings-title',
		);
		foreach ( $product_types as $product_type_key => $product_key_label ) {
			$custom_attrs = array();
			if ( 'yith_bundle' === $product_type_key ) {
				$product_key_label        = 'YITH Product Bundle';
				$custom_attrs['disabled'] = 'disabled';
			}
			$field_arr = array(
				'desc'              => sprintf( esc_html__( '%1$s ( %2$s )', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label, $product_type_key ) . ( 'yith_bundle' === $product_type_key ? $this->core->pro_btn( '', 'Premium', '', '', true ) : '' ),
				'desc_tip'          => sprintf( esc_html__( 'Enable / Disable "Quick View" button for %s products', 'quick-view-and-buy-now-for-woocommerce' ), $product_key_label ),
				'id'                => self::$settings_name . '[quick_view][enable_by_product_type_' . $product_type_key . ']',
				'type'              => 'checkbox',
				'custom_attributes' => $custom_attrs,
				'checkboxgroup'     => ( array_key_first( $product_types ) === $product_type_key ) ? 'start' : ( array_key_last( $product_types ) === $product_type_key ? 'end' : '' ),
				'class'             => 'input-checkbox' . ( 'yith_bundle' === $product_type_key ? ' disabled' : '' ),
				'value'             => 'yith_bundle' === $product_type_key ? 'no' : $main_settings['quick_view'][ 'enable_by_product_type_' . $product_type_key ],
				'name_keys'         => array( 'quick_view', 'enable_by_product_type_' . $product_type_key ),
			);
			if ( array_key_first( $product_types ) === $product_type_key ) {
				$field_arr['title'] = esc_html__( 'Quick View Button', 'quick-view-and-buy-now-for-woocommerce' );
			}
			$fields[ self::$plugin_info['name'] ]['quick_view'][] = $field_arr;
		}
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Quick View Button Title', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => self::$settings_name . '[quick_view][quick_view_text]',
			'type'      => 'text',
			'class'     => 'input-text',
			'default'   => esc_html__( 'Quick View', 'quick-view-and-buy-now-for-woocommerce' ),
			'value'     => $main_settings['quick_view']['quick_view_text'],
			'name_keys' => array( 'quick_view', 'quick_view_text' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Quick View Button Position', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => self::$settings_name . '[quick_view][quick_view_position]',
			'type'      => 'select',
			'default'   => 'before',
			'options'   => array(
				'before' => esc_html__( 'Before Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'after'  => esc_html__( 'after Add To Cart Button', 'quick-view-and-buy-now-for-woocommerce' ),
			),
			'value'     => $main_settings['quick_view']['quick_view_position'],
			'name_keys' => array( 'quick_view', 'quick_view_position' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Text Color', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Button Text Color', 'quick-view-and-buy-now-for-woocommerce' ) . $this->core->pro_btn( '', 'Premium', '', '', true ),
			'id'         => self::$settings_name . '[quick_view][color]',
			'type'      => 'text',
			'class'     => 'wp-color-picker',
			'default'   => '',
			'css'       => 'width:6em;',
			'value'     => $main_settings['quick_view']['color'],
			'name_keys' => array( 'quick_view', 'color' ),
			'custom_attributes' => array(
				'disabled' => 'disabled',
			),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Background Color', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'      => esc_html__( 'Button Background Color', 'quick-view-and-buy-now-for-woocommerce' ) . $this->core->pro_btn( '', 'Premium', '', '', true ),
			'id'        => self::$settings_name . '[quick_view][bg]',
			'type'      => 'text',
			'class'     => 'wp-color-picker',
			'default'   => '',
			'css'       => 'width:6em;',
			'value'     => $main_settings['quick_view']['bg'],
			'name_keys' => array( 'quick_view', 'bg' ),
			'custom_attributes' => array(
				'disabled' => 'disabled',
			),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'name' => '',
			'type' => 'sectionend',
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title' => esc_html__( 'Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ),
			'type'  => 'title',
			'id'    => self::$plugin_info['name'] . '-quick-view-popup-settings-title',
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'         => esc_html__( 'Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'          => esc_html__( 'Data Tabs section', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'      => esc_html__( 'Show Data Tabs section ( Description - Additional information - Reviews ) in popup', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'            => self::$settings_name . '[quick_view][data_tabs_section]',
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'class'         => 'input-checkbox',
			'value'         => $main_settings['quick_view']['data_tabs_section'],
			'name_keys'     => array( 'quick_view', 'data_tabs_section' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'desc'          => esc_html__( 'Upsells products section', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'      => esc_html__( 'Show upsells products section in popup', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'            => self::$settings_name . '[quick_view][upsells_section]',
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'class'         => 'input-checkbox',
			'value'         => $main_settings['quick_view']['upsells_section'],
			'name_keys'     => array( 'quick_view', 'upsells_section' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'desc'          => esc_html__( 'Related products section', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'      => esc_html__( 'Show related products section in popup', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'            => self::$settings_name . '[quick_view][related_products_section]',
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'class'         => 'input-checkbox',
			'value'         => $main_settings['quick_view']['related_products_section'],
			'name_keys'     => array( 'quick_view', 'related_products_section' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'   => esc_html__( 'Quick View Popup show animation', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'    => esc_html__( 'Select custom animation when the "Quick View" popup is shown. animations can be viewed at', 'quick-view-and-buy-now-for-woocommerce' ) . ' <a target="_blank" href="https://animate.style/" >animate.style</a> ' . $this->core->pro_btn( '', 'Premium', '', '', true ),
			'id'      => self::$settings_name . '[advanced][quick_view_show_animation]',
			'type'    => self::$plugin_info['name'] . '-quick-view-popup-show-animation-class-name',
			'value'   => '',
			'default' => '',

		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'   => esc_html__( 'Quick View Popup hide animation', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'    => esc_html__( 'Select custom animation when the "Quick View" popup is closed', 'quick-view-and-buy-now-for-woocommerce' ) . $this->core->pro_btn( '', 'Premium', '', '', true ),
			'id'      => self::$settings_name . '[advanced][quick_view_hide_animation]',
			'type'    => self::$plugin_info['name'] . '-quick-view-popup-hide-animation-class-name',
			'value'   => '',
			'default' => '',
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'type' => self::$plugin_info['name'] . '-quick-view-button-shortcode-details',
		);

		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'type' => self::$plugin_info['name'] . '-quick-view-popup-carousel',
		);

		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'name' => '',
			'type' => 'sectionend',
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title' => esc_html__( 'Quick View Popup Compatibility', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc'  => esc_html__( 'Test these options if the quick view popup is broken or incompatible with your active theme', 'quick-view-and-buy-now-for-woocommerce' ),
			'type'  => 'title',
			'id'    => self::$plugin_info['name'] . '-quick-view-popup-compatibility-settings-title',
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Default Variations Dropdowns', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'  => esc_html__( 'This option will force the WooCommerce default dropdowns for variations in the quick view popup. Check this option if your active theme is overwritting the WooCommerce variations dropdowns and It\'s not working in the quick view popup.', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => self::$settings_name . '[quick_view][force_default_variable_dropdowns]',
			'type'      => 'checkbox',
			'class'     => 'input-checkbox',
			'value'     => $main_settings['quick_view']['force_default_variable_dropdowns'],
			'name_keys' => array( 'quick_view', 'force_default_variable_dropdowns' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'title'     => esc_html__( 'Default Product Gallery Styles', 'quick-view-and-buy-now-for-woocommerce' ),
			'desc_tip'  => esc_html__( 'This option will force the WooCommerce default style for the product gallery of variable and grouped products in the quick view popup. Check this option if your active theme is overwritting the WooCommerce gallery styles and the product gallery looks broken in the quick view popup.', 'quick-view-and-buy-now-for-woocommerce' ),
			'id'        => self::$settings_name . '[quick_view][force_default_product_gallery_styles]',
			'type'      => 'checkbox',
			'class'     => 'input-checkbox',
			'value'     => $main_settings['quick_view']['force_default_product_gallery_styles'],
			'name_keys' => array( 'quick_view', 'force_default_product_gallery_styles' ),
		);
		$fields[ self::$plugin_info['name'] ]['quick_view'][] = array(
			'name' => '',
			'type' => 'sectionend',
		);

		$fields = apply_filters( self::$plugin_info['name'] . '-settings-fields', $fields );

		if ( $return ) {
			return $fields;
		}
		$this->fields = $fields;
	}

	/**
	 * Quick View Button Shortcode Details.
	 *
	 * @return void
	 */
	public function quick_view_button_shortcode_details() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Quick View Button Shortcode', 'quick-view-and-buy-now-for-woocommerce' ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<label>
						<code>[<?php echo esc_html( self::$plugin_info['classes_prefix'] . '-quick-view-button' ); ?>]</code>
					</label>
					<p class="description">
						<?php esc_html_e( 'the shortcode accepts "product_id" attribute otherwise, it depends on global $product.', 'quick-view-and-buy-now-for-woocommerce' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Quick View Carousel annoucement.
	 *
	 * @return void
	 */
	public function quick_view_carousel_details() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Quick View Popup Carousel', 'quick-view-and-buy-now-for-woocommerce' ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<label>
						<?php $this->core->pro_btn( '', 'Premium' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'add carousel to quick view popup in shop and archive pages', 'quick-view-and-buy-now-for-woocommerce' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Buy Now Button Shortcode Details.
	 *
	 * @return void
	 */
	public function buy_now_button_shortcode_details() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Buy Now Button Shortcode', 'quick-view-and-buy-now-for-woocommerce' ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<label>
						<code>[<?php echo esc_html( self::$plugin_info['classes_prefix'] . '-buy-now-button' ); ?>]</code>
					</label>
					<p class="description">
						<?php esc_html_e( 'Make sure the shortcode is placed next to the product\'s "Add To Cart" button in order to function properly', 'quick-view-and-buy-now-for-woocommerce' ); ?>
					</p>
				</fieldset>
			</td>
		</tr>
		<?php
	}

	/**
	 * Settings Tabs.
	 *
	 * @return void
	 */
	public function settings_tabs() {
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wp-clearfix">
			<!-- Quick View -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key ) ); ?>" class="nav-tab<?php echo ( ! isset( $_GET['fields'] ) || isset( $_GET['fields'] ) && 'quick_view' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Quick View', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Add To Cart -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=add_to_cart' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'add_to_cart' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Add To Cart - Direct Checkout', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Buy Now -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=buy_now' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'buy_now' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Buy Now', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Direct Purchase -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=direct_purchase' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'direct_purchase' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Direct Purchase', 'quick-view-and-buy-now-for-woocommerce' ); ?><?php $this->core->new_keyword( 'Premium', false ); ?></a>
			<!-- Loading Screen -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=loading_screen' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'loading_screen' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Loading Screen', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Quantity -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=quantity' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'quantity' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Quantity Input', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Checkout -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=checkout' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'checkout' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Checkout', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
			<!-- Custom CSS -->
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->settings_tab_key . '&fields=custom_css' ) ); ?>" class="nav-tab<?php echo ( isset( $_GET['fields'] ) && 'custom_css' === sanitize_text_field( wp_unslash( $_GET['fields'] ) ) ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Custom CSS', 'quick-view-and-buy-now-for-woocommerce' ); ?></a>
		</nav>
		<?php
	}

	/**
	 * Plugin Settings Tab in WordPress Settings Page.
	 *
	 * @return array
	 */
	public function add_settings_tab( $settings_tabs ) {
		foreach ( array_keys( $this->settings_tab ) as $name ) {
			$settings_tabs[ $name ] = $this->settings_tab[ $name ];
		}
		return $settings_tabs;
	}

	/**
	 * Show the Settings Tab Fields.
	 *
	 * @return void
	 */
	public function settings_tab_action() {

		$this->create_settings_fields();

		$action = 'quick_view';

		if ( ! empty( $_GET['fields'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['fields'] ) ), array_keys( $this->fields[ self::$plugin_info['name'] ] ) ) ) {
			$action = sanitize_text_field( wp_unslash( $_GET['fields'] ) );
		}
		if ( 'quantity' === $action || 'direct_purchase' === $action ) {
			$GLOBALS['hide_save_button'] = true;
		}
		woocommerce_admin_fields( $this->fields[ self::$plugin_info['name'] ][ $action ] );
	}

	/**
	 * Get Settings.
	 *
	 * @return array
	 */
	public static function get_main_settings() {
		$settings = get_option( self::$settings_name, false );
		if ( $settings ) {
			return array_replace_recursive( self::$default_settings, $settings );
		}
		return self::$default_settings;
	}

	/**
	 * Get Setting.
	 *
	 * @param string $field
	 * @param string $sub_field
	 * @return mixed
	 */
	public static function get_setting( $field, $sub_field ) {
		$settings = self::get_main_settings();
		if ( isset( $settings[ $field ] ) && isset( $settings[ $field ][ $sub_field ] ) ) {
			return $settings[ $field ][ $sub_field ];
		}
		return null;
	}

	/**
	 * Save Tab Settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		$action = '';
		$fields = $this->create_settings_fields( true );
		if ( empty( $_GET['fields'] ) ) {
			$action = 'quick_view';
		}

		if ( ! empty( $_GET['fields'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['fields'] ) ), array_keys( $fields[ self::$plugin_info['name'] ] ) ) ) {
			$action = sanitize_text_field( wp_unslash( $_GET['fields'] ) );
		}

		// Save Settings.
		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-main-settings-name' ] ) && is_array( $_POST[ self::$plugin_info['name'] . '-main-settings-name' ] ) ) {
			$settings = self::get_main_settings();
			foreach ( $fields[ self::$plugin_info['name'] ][ $action ] as $setting ) {
				if ( ! empty( $setting['name_keys'] ) && isset( $_POST[ self::$settings_name ][ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] ) ) {
					$raw_value = wp_unslash( $_POST[ self::$settings_name ][ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] );
					switch ( $setting['type'] ) {
						case 'checkbox':
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
							break;
						case 'textarea':
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = wp_slash( trim( $raw_value ) );
							break;
						case 'select':
							$allowed_values = empty( $setting['options'] ) ? array() : array_map( 'strval', array_keys( $setting['options'] ) );
							if ( empty( $setting['default'] ) && empty( $allowed_values ) ) {
								$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = null;
								break;
							}
							$default = ( empty( $setting['default'] ) ? $allowed_values[0] : $setting['default'] );
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
							break;

						default:
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = wc_clean( $raw_value );
							break;
					}
				} elseif ( ! empty( $setting['name_keys'] ) ) {
					switch ( $setting['type'] ) {
						case 'checkbox':
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = 'no';
							break;
						case 'multiselect':
							$settings[ $setting['name_keys'][0] ][ $setting['name_keys'][1] ] = array();
							break;
					}
				}
			}
			self::update_main_settings( $settings );
		}
	}

	/**
	 * Update main settings.
	 *
	 * @param array $settings Settings Array.
	 * @return void
	 */
	public static function update_main_settings( $settings ) {
		update_option( self::$settings_name, $settings, true );
	}


	/**
	 * Check if Quick View Button is enabled globally.
	 *
	 * @param object $product Product Object.
	 * @return boolean
	 */
	public static function is_quick_view_global_enabled( $product ) {
		$product_type = $product->get_type();
		if ( ! empty( self::$settings['quick_view'][ 'enable_by_product_type_' . $product_type ] ) ) {
			if ( 'yes' === self::$settings['quick_view'][ 'enable_by_product_type_' . $product_type ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a section displayed in quick view popup.
	 *
	 * @param string $section   Single Product Section to display or not in product popup. [ data_tabs - upsells - related_products ]
	 * @return boolean
	 */
	public static function is_quick_view_popup_section_global_enabled( $section ) {
		if ( ! empty( self::$settings['quick_view'][ $section . '_section' ] ) ) {
			if ( 'yes' === self::$settings['quick_view'][ $section . '_section' ] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get "Quick View" popup animation classes.
	 *
	 * @param string $status popup animation case.
	 * @return string
	 */
	public static function get_popup_animation_classes( $status = 'show' ) {
		if ( 'show' === $status || 'hide' === $status ) {
			$settings = self::get_main_settings();
			return $settings['advanced'][ 'quick_view_' . $status . '_animation' ];
		}
		return '';
	}

	/**
	 * Check to display cart in checkout or not.
	 *
	 * @return boolean
	 */
	public static function show_cart_in_checkout() {
		return ( 'yes' === self::$settings['checkout']['show_cart_on_checkout'] ? true : false );
	}

	/**
	 * Quick View Popup Show Animation select HTML.
	 *
	 * @param array $details  Input Details Array.
	 * @return void
	 */
	public function popup_show_animation_select_html( $details ) {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="<?php echo esc_attr( $details['id'] ); ?>">
					<?php echo esc_html( $details['title'] ); ?>
				</label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $details['type'] ) ); ?>">
				<select name="<?php echo esc_attr( self::$settings_name . '[advanced][quick_view_show_animation]' ); ?>" id="<?php echo esc_attr( self::$plugin_info['name'] . '-popup-show-animation-class' ); ?>">
					<option value=""><?php esc_html_e( 'Disable', 'quick-view-and-buy-now-for-woocommerce' ); ?></option>
					<?php foreach ( array_keys( self::$popup_show_animation_options ) as $label ) : ?>
						<optgroup label="<?php echo esc_attr( $label ); ?>">
						<?php
						foreach ( self::$popup_show_animation_options[ $label ] as $val => $val_label ) :
							?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $details['value'] ); ?> ><?php echo esc_html( $val_label ); ?></option>
							<?php
						endforeach;
						?>
						</optgroup>
						<?php
					endforeach
					?>
				</select>
				<p class="description" ><?php echo wp_kses_post( $details['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Quick Hide Popup Show Animation select HTML.
	 *
	 * @param array $details  Input Details Array.
	 * @return void
	 */
	public function popup_hide_animation_select_html( $details ) {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc" >
				<label for="<?php echo esc_attr( $details['id'] ); ?>">
					<?php echo esc_html( $details['title'] ); ?>
				</label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $details['type'] ) ); ?>">
				<select name="<?php echo esc_attr( self::$settings_name . '[advanced][quick_view_hide_animation]' ); ?>" id="<?php echo esc_attr( self::$plugin_info['name'] . '-popup-hide-animation-class' ); ?>">
					<option value=""><?php esc_html_e( 'Disable', 'quick-view-and-buy-now-for-woocommerce' ); ?></option>
					<?php foreach ( array_keys( self::$popup_hide_animation_options ) as $label ) : ?>
						<optgroup label="<?php echo esc_attr( $label ); ?>">
						<?php
						foreach ( self::$popup_hide_animation_options[ $label ] as $val => $val_label ) :
							?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $details['value'] ); ?> ><?php echo esc_html( $val_label ); ?></option>
							<?php
						endforeach;
						?>
						</optgroup>
						<?php
					endforeach
					?>
				</select>
				<p class="description"><?php echo wp_kses_post( $details['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Review Notice in settings Tab.
	 *
	 * @return void
	 */
	public function review_notice() {
		?>
		<div style="clear:both;display:block;" class="d-flex w-100 justify-content-between align-items-start">
			<?php
			$this->core->review_notice( 'https://wordpress.org/support/plugin/quick-view-and-buy-now-for-woocommerce/reviews/#new-post' );
			$this->core->default_footer_section();
			?>
		</div>
		<?php
		do_action( self::$plugin_info['name'] . '-after-review-notice' );
		$this->core->plugins_sidebar( 'woo-quick-view' );
	}

}
