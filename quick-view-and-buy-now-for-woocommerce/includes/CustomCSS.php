<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Custom CSS Class.
 */
class CustomCSS {

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
	 * Loading Screen Fields.
	 *
	 * @var array
	 */
	private $custom_css_fields = array();

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

		$this->setup_settings_fields();
		$this->hooks();
	}


	/**
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$this->custom_css_fields = array(
			array(
				'title' => esc_html__( 'Custom CSS Settings', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-quick-view-settings-custom-css-title',
			),
			array(
				'title'             => esc_html__( 'Custom Css', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'              => esc_html__( 'Add custom css for the popups and "Buy Now" - "Quick View" Buttons', 'quick-view-and-buy-now-for-woocommerce' ) . $this->custom_css_classes(),
				'id'                => Settings::$settings_name . '[advanced][custom_css]',
				'type'              => 'textarea',
				'placeholder'       => esc_html__( 'Custom CSS...', 'quick-view-and-buy-now-for-woocommerce' ),
				'value'             => self::$settings['advanced']['custom_css'],
				'default'           => '',
				'custom_attributes' => array(
					'rows' => 10,
				),
				'css'               => 'width: 100%',
				'name_keys'         => array( 'advanced', 'custom_css' ),
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
		);
	}

	/**
	 * Filters and Actions Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_custom_css_fields' ), 100, 1 );
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public static function get_custom_css() {
		return self::$settings['advanced']['custom_css'];
	}

	/**
	 * Print custom css in
	 *
	 * @return void
	 */
	public static function print_custom_css() {
		$custom_css = sanitize_text_field( self::get_custom_css() );
		if ( 'yes' === self::$settings['quick_view']['force_default_product_gallery_styles'] ) {
			$custom_css . ' ' . self::default_woo_product_gallery_css();
		}

		$custom_css = apply_filters( self::$plugin_info['name'] . '-custom-css-before-inline', $custom_css );

		wp_add_inline_style(
			self::$plugin_info['name'] . '-animate-css',
			$custom_css
		);
	}

	/**
	 * Add The WooCommerce Default Gallery CSS Code.
	 *
	 * @return string
	 */
	private static function default_woo_product_gallery_css() {
		ob_start();
		?>
		.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-viewport{margin-bottom:1.618em}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs{margin:0;padding:0}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs::before,.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs::after{content:"";display:table}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs::after{clear:both}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs li{list-style:none;margin-bottom:1.618em;cursor:pointer}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs li img{opacity:.5;transition:all,ease,0.2s}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs li img.flex-active{opacity:1}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery .flex-control-thumbs li:hover img{opacity:1}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-2 .flex-control-thumbs li{width:42.8571428571%;float:left;margin-right:14.2857142857%}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-2 .flex-control-thumbs li:nth-child(2n){margin-right:0}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-2 .flex-control-thumbs li:nth-child(2n+1){clear:both}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-3 .flex-control-thumbs li{width:23.8095238%;float:left;margin-right:14.2857142857%}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-3 .flex-control-thumbs li:nth-child(3n){margin-right:0}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-3 .flex-control-thumbs li:nth-child(3n+1){clear:both}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-4 .flex-control-thumbs li{width:14.2857142857%;float:left;margin-right:14.2857142857%}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-4 .flex-control-thumbs li:nth-child(4n){margin-right:0}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-4 .flex-control-thumbs li:nth-child(4n+1){clear:both}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-5 .flex-control-thumbs li{width:8.5714285714%;float:left;margin-right:14.2857142857%}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-5 .flex-control-thumbs li:nth-child(5n){margin-right:0}.gpls-arcw-quick-view-popup .single-product div.product .woocommerce-product-gallery.woocommerce-product-gallery--columns-5 .flex-control-thumbs li:nth-child(5n+1){clear:both}
		<?php
		return ob_get_clean();
	}

	/**
	 * Custom Css Classes.
	 *
	 * @return void
	 */
	private function custom_css_classes() {
		ob_start();
		?>
		<div class="list-group">
			<div class="list-group-item">
				<div class="d-flex w-100 justify-content-between">
					<h6 class="mb-1 w-25"><?php esc_html_e( 'Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ); ?></h6>
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Description', 'quick-view-and-buy-now-for-woocommerce' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Class Name', 'quick-view-and-buy-now-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Quick View Button', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-btn' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-popup' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Quick View Popup of Grouped Product', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-popup.grouped-product-popup' ); ?></td>
							</tr>

							<tr>
								<td><?php esc_html_e( 'Variable product popup in a Grouped Product popup', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-quick-view-popup.variable-in-grouped-product-popup' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Back button', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-popup-back-btn' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Close button', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-popup-close-btn' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Selected options row of a variable product in grouped product', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-variable-selected-options-in-grouped' ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="list-group-item">
				<div class="d-flex w-100 justify-content-between">
					<h6 class="mb-1 w-25"><?php esc_html_e( 'Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' ); ?></h6>
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Description', 'quick-view-and-buy-now-for-woocommerce' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Class Name', 'quick-view-and-buy-now-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( '"Buy Now" Button general Class', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-buy-now' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( '"Buy Now" Button simple product', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-simple-buy-now' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( '"Buy Now" Button variable product', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-variable-buy-now' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( '"Buy Now" Button grouped product', 'quick-view-and-buy-now-for-woocommerce' ); ?></td>
								<td class="font-weight-bold"><?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-grouped-buy-now' ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add custom css Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_custom_css_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['custom_css'] = $this->custom_css_fields;
		return $fields;
	}

}
