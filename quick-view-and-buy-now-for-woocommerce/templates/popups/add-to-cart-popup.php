<?php defined( 'ABSPATH' ) || exit(); ?>

<?php
$custom_popup = apply_filters( $plugin_info['name'] . '-before-product-quick-view-popup', null, $product );

if ( ! empty( $custom_popup ) ) {
	echo $custom_popup;
} else {
	echo \WC_Shortcodes::product_page(
		array(
			'id' => $product->get_id(),
		)
	);
}

do_action( $plugin_info['name'] . '-after-product-quick-view-popup', $product );
