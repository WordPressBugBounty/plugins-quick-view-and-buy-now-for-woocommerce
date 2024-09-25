<?php
namespace GPLSCore\GPLS_PLUGIN_ARCW;

use GPLSCore\GPLS_PLUGIN_ARCW\Settings;

/**
 * Handle Screen Loader.
 */
class ScreenLoader {

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
	private $screen_loader_fields = array();

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
	 * Setup Loading Screen Settings Fields.
	 *
	 * @return void
	 */
	public function setup_settings_fields() {
		$this->screen_loader_fields = array(
			array(
				'title' => esc_html__( 'Loading Screen Settings', 'quick-view-and-buy-now-for-woocommerce' ),
				'type'  => 'title',
				'id'    => self::$plugin_info['name'] . '-quick-view-settings-loading-screen-title',
			),
			'selected' => array(
				'title'     => esc_html__( 'Loading Screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Select Loading Screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[loading_screen][selected]',
				'class'     => self::$plugin_info['classes_prefix'] . '-loading-screen-selected-field',
				'type'      => self::$plugin_info['name'] . '-screen-loader-selected-field',
				'default'   => '1',
				'options'   => self::get_screen_loaders(),
				'value'     => '1',
			),
			'random'   => array(
				'title'     => esc_html__( 'Random Loading Screens', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Select multiple loading screens to be used randomly.', 'quick-view-and-buy-now-for-woocommerce' ) . self::$core->pro_btn( '', 'Premium', '', '', true ),
				'id'        => Settings::$settings_name . '[loading_screen][random]',
				'class'     => self::$plugin_info['classes_prefix'] . '-loading-screen-random-field',
				'type'      => 'multiselect',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'options'   => self::get_screen_loaders(),
				'value'     => array(),
			),
			array(
				'title'     => esc_html__( 'Loop Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Enable loading screen', 'quick-view-and-buy-now-for-woocommerce' ). self::$core->pro_btn( '', 'Premium', '', '', true ),
				'desc_tip'  => esc_html__( 'Display loading screen animation when the loop buy now is clicked [ in shop and archive pages ].', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '-loop_buy_now-display_loader',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
				'value'     => 'no',
			),
			array(
				'title'     => esc_html__( 'Buy Now Button', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Enable loading screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Display loading screen animation until the buy now is finished [ in single product pages and inside quick view popups ]', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[buy_now][display_loader]',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'value'     => self::$settings['buy_now']['display_loader'],
				'name_keys' => array( 'buy_now', 'display_loader' ),
			),

			array(
				'title'     => esc_html__( 'Quick View Popup', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc'      => esc_html__( 'Enable loading screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'Display loading screen animation until the quick view popup is displayed', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '[quick_view][display_loader]',
				'type'      => 'checkbox',
				'class'     => 'input-checkbox',
				'value'     => self::$settings['quick_view']['display_loader'],
				'name_keys' => array( 'quick_view', 'display_loader' ),
			),
			array(
				'name' => '',
				'type' => 'sectionend',
			),
			array(
				'title'     => esc_html__( 'Custom Loading Screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'desc_tip'  => esc_html__( 'create full styled loading screen', 'quick-view-and-buy-now-for-woocommerce' ),
				'id'        => Settings::$settings_name . '-quick_view-custom-loading-screen',
				'type'      => 'title',
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
		add_filter( self::$plugin_info['name'] . '-localize-vars-arr', array( $this, 'screen_loader_localize_vars' ), 1000, 1 );
		add_action( 'wp_footer', array( $this, 'screen_loader' ) );
		add_filter( self::$plugin_info['name'] . '-screen-loader-icon', array( $this, 'selected_screen_loader_icon' ), 10, 1 );
		add_action( 'woocommerce_admin_field_' . self::$plugin_info['name'] . '-screen-loader-selected-field', array( $this, 'loading_screen_selected_settings_field' ), 100, 1 );
		add_action( self::$plugin_info['name'] . '-after-review-notice', array( $this, 'print_screen_loaders' ) );
		add_filter( self::$plugin_info['name'] . '-settings-fields', array( $this, 'add_screen_loader_fields' ), 100, 1 );
		add_action( 'woocommerce_settings_' . Settings::$settings_name . '-quick_view-custom-loading-screen', array( $this, 'custom_loading_screen_coming_soon' ) );
	}

	public function custom_loading_screen_coming_soon() {
		esc_html_e( 'Coming Soon ...', 'quick-view-and-buy-now-for-woocommerce' ); self::$core->pro_btn( '', 'Premium' );
	}

	/**
	 * Screen Loader Localize Vars for Frontend.
	 *
	 * @param array $localized_vars
	 * @return array
	 */
	public function screen_loader_localize_vars( $localized_vars ) {
		$localized_vars['screen_loader'] = array(
			'buy_now'      => array(
				'status' => self::buy_now_display_screen_loader(),
			),
			'quick_view'   => array(
				'status' => self::quick_view_display_screen_loader(),
			),
		);
		return $localized_vars;
	}


	/**
	 * Check if display Screen Loader while Quick View popup is opening.
	 *
	 * @return boolean
	 */
	public static function quick_view_display_screen_loader() {
		return ( 'yes' === self::$settings['quick_view']['display_loader'] );
	}

	/**
	 * Check if display Screen Loader while buy now button is clicked.
	 *
	 * @return boolean
	 */
	public static function buy_now_display_screen_loader() {
		return ( 'yes' === self::$settings['buy_now']['display_loader'] );
	}

	/**
	 * Get Saved Random Screen Loaders.
	 *
	 * @return array
	 */
	public static function get_saved_random_screen_loaders() {
		return self::$settings['loading_screen']['random'];
	}

	/**
	 * Screen Loader HTMl.
	 *
	 * @return void
	 */
	public function screen_loader() {
		$random_loaders = self::get_saved_random_screen_loaders();
		if ( empty( $random_loaders ) ) :
			$loader_num = $this->get_saved_loading_screen_index();
			?>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader' . ( ! empty( $loader_num ) ? ' ' . self::$plugin_info['classes_prefix'] . '-screen-loader-' . $loader_num : '' ) ); ?>">
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-wrapper' ); ?>">
					<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon' ); ?>">
						<?php echo wp_kses_post( apply_filters( self::$plugin_info['name'] . '-screen-loader-icon', $this->screen_loader_icon_1() ) ); ?>
					</div>
				</div>
			</div>
			<?php
		else :
			$this->print_random_screen_loaders( $random_loaders );
		endif;
	}

	/**
	 * Get Loading Screen Index.
	 *
	 * @return int
	 */
	public function get_saved_loading_screen_index() {
		return 1;
	}

	/**
	 * Selected screen Loader Icon.
	 *
	 * @param string $screen_loader_icon_html
	 * @return string
	 */
	public function selected_screen_loader_icon( $screen_loader_icon_html ) {
		$loader_num       = $this->get_saved_loading_screen_index();
		$loader_func_name = 'screen_loader_icon_' . $loader_num;
		if ( method_exists( $this, $loader_func_name ) ) {
			return call_user_func( array( $this, $loader_func_name ) );
		}
		return $screen_loader_icon_html;
	}

	/**
	 * Screen Loader Icon 1 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_1() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-1' ); ?>">
			<span></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 2 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_2() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-2' ); ?>">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 3 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_3() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-3' ); ?>">
			<p>l</p>
			<p>o</p>
			<p>a</p>
			<p>d</p>
			<p>i</p>
			<p>n</p>
			<p>g</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 4 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_4() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-4' ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-circle ' . self::$plugin_info['classes_prefix'] . '-circle-1' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-circle ' . self::$plugin_info['classes_prefix'] . '-circle-2' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-circle ' . self::$plugin_info['classes_prefix'] . '-circle-3' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-circle ' . self::$plugin_info['classes_prefix'] . '-circle-4' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-circle ' . self::$plugin_info['classes_prefix'] . '-circle-5' ); ?>"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 5 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_5() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-5' ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-loader--dot' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-loader--dot' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-loader--dot' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-loader--dot' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-loader--dot' ); ?>"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 6 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_6() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-6' ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot-loader' ); ?>">
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-dot' ); ?>"></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Screen Loader Icon 7 HTML.
	 *
	 * @return string
	 */
	private function screen_loader_icon_7() {
		ob_start();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon-7' ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square ' . self::$plugin_info['classes_prefix'] . '-square-last' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square ' . self::$plugin_info['classes_prefix'] . '-square-clear' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square ' . self::$plugin_info['classes_prefix'] . '-square-last' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square ' . self::$plugin_info['classes_prefix'] . '-square-clear' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square' ); ?> "></div>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-square ' . self::$plugin_info['classes_prefix'] . '-square-last' ); ?>"></div>
		</div>
		<?php
		return ob_get_clean();
	}


	/**
	 * Get Screen Loaders.
	 *
	 * @return array
	 */
	public static function get_screen_loaders() {
		$i       = 1;
		$loaders = array();
		while ( method_exists( static::class, 'screen_loader_icon_' . $i ) ) {
			$loaders[ $i ] = esc_html__( 'loading screen', 'quick-view-and-buy-now-for-woocommerce' ) . ' ' . $i . ( $i > 1 ? ' ( Premium )' : '' );
			++$i;
		}

		return apply_filters( self::$plugin_info['name'] . '-screen-loaders', $loaders );
	}

	/**
	 * Print Loading Screen HTML.
	 *
	 * @return void
	 */
	public function print_screen_loaders() {
		$loaders = self::get_screen_loaders();
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader' ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-wrapper' ); ?>">
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon' ); ?>">
				<?php
				foreach ( $loaders as $loader_index => $loader ) {
					echo wp_kses_post( call_user_func( array( $this, 'screen_loader_icon_' . $loader_index ) ) );
				}
				?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Print Random Loading Screens.
	 *
	 * @param array $loaders_index
	 * @return void
	 */
	public function print_random_screen_loaders( $loaders_index ) {
		foreach ( $loaders_index as $loader_index ) {
			if ( ! method_exists( $this, 'screen_loader_icon_' . $loader_index ) ) {
				continue;
			}
			?>
			<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader ' . self::$plugin_info['classes_prefix'] . '-screen-loader-' . $loader_index ); ?>">
				<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-wrapper' ); ?>">
					<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-screen-loader-icon' ); ?>">
					<?php echo wp_kses_post( call_user_func( array( $this, 'screen_loader_icon_' . $loader_index ) ) ); ?>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Loading Screen Selected Settings Field.
	 *
	 * @param array $field
	 * @return void
	 */
	public function loading_screen_selected_settings_field( $value ) {
		$value             = array_merge( $value, $this->screen_loader_fields['selected'] );
		$field_description = \WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$option_value      = 1;

		// Custom attribute handling.
		$custom_attributes = array();
		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<select
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
					>
					<?php
					foreach ( $value['options'] as $key => $val ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $option_value, (string) $key ); ?> ><?php echo esc_html( $val ); ?></option>
						<?php
					}
					?>
				</select> <?php echo $description; // WPCS: XSS ok. ?>
				<button class="button button-primary <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-preview-selected-loading-screen' ); ?>"><?php esc_html_e( 'Preview' ); ?></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add Screen Loader Fields.
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function add_screen_loader_fields( $fields ) {
		self::$settings = Settings::get_main_settings();
		$this->setup_settings_fields();
		$fields[ self::$plugin_info['name'] ]['loading_screen'] = $this->screen_loader_fields;
		return $fields;
	}
}
