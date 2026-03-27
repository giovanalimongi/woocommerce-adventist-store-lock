<?php
/**
 * WooCommerce Adventist Store Lock
 *
 * Blocks purchases weekly from Friday 18:00 to Saturday 18:00
 * and synchronizes an Elementor popup status accordingly.
 *
 * Requirements:
 * - WordPress
 * - WooCommerce
 * - Elementor Pro
 *
 * Recommended usage:
 * - Add via Code Snippets plugin, or
 * - Paste into your child theme's functions.php
 */

/**
 * Check whether the store should be locked according to the weekly schedule.
 *
 * Schedule:
 * - Friday from 18:00 onward
 * - Saturday until 18:00
 *
 * Uses the timezone configured in WordPress.
 *
 * @return bool
 */
function wcasl_is_store_locked() {
	$now = current_datetime();

	// 0 = Sunday, 5 = Friday, 6 = Saturday
	$day    = (int) $now->format( 'w' );
	$hour   = (int) $now->format( 'H' );
	$minute = (int) $now->format( 'i' );

	// Friday from 18:00 onward
	if ( 5 === $day && ( $hour > 18 || ( 18 === $hour && $minute >= 0 ) ) ) {
		return true;
	}

	// Saturday before 18:00
	if ( 6 === $day && $hour < 18 ) {
		return true;
	}

	return false;
}

/**
 * Synchronize the Elementor popup status with the store lock state.
 *
 * During the locked period:
 * - popup status => publish
 *
 * Outside the locked period:
 * - popup status => draft
 *
 * This runs on page load and only updates the post status if a change is needed.
 *
 * IMPORTANT:
 * Replace the popup ID below with the real Elementor popup ID.
 *
 * @return void
 */
function wcasl_sync_popup_status() {
	static $has_run = false;

	if ( $has_run ) {
		return;
	}

	$has_run = true;

	// Avoid running in admin screens or AJAX requests.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$popup_id = 1234; // TODO: Replace with your actual Elementor popup ID.

	$popup = get_post( $popup_id );

	if ( ! $popup ) {
		return;
	}

	$desired_status = wcasl_is_store_locked() ? 'publish' : 'draft';

	if ( $popup->post_status !== $desired_status ) {
		wp_update_post(
			array(
				'ID'          => $popup_id,
				'post_status' => $desired_status,
			)
		);
	}
}
add_action( 'init', 'wcasl_sync_popup_status', 20 );

/**
 * Prevent products from being purchasable during the locked period.
 *
 * @param bool       $purchasable Whether the product is purchasable.
 * @param WC_Product $product     The product object.
 * @return bool
 */
function wcasl_block_product_purchase( $purchasable, $product ) {
	if ( wcasl_is_store_locked() ) {
		return false;
	}

	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'wcasl_block_product_purchase', 10, 2 );

/**
 * Block add-to-cart requests during the locked period.
 *
 * This is an important fallback in case a purchase attempt bypasses UI restrictions.
 *
 * @param bool $passed     Whether validation passed.
 * @param int  $product_id Product ID.
 * @param int  $quantity   Requested quantity.
 * @return bool
 */
function wcasl_block_add_to_cart( $passed, $product_id, $quantity ) {
	if ( wcasl_is_store_locked() ) {
		wc_add_notice(
			__( 'Our store is temporarily unavailable for purchases from Friday 6:00 PM to Saturday 6:00 PM.', 'wcasl' ),
			'error'
		);

		return false;
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wcasl_block_add_to_cart', 10, 3 );

/**
 * Remove add-to-cart buttons from shop loops and single product pages
 * during the locked period.
 *
 * @return void
 */
function wcasl_remove_purchase_buttons() {
	if ( ! wcasl_is_store_locked() ) {
		return;
	}

	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}
add_action( 'wp', 'wcasl_remove_purchase_buttons' );

/**
 * Show a notice on cart and checkout pages during the locked period.
 *
 * @return void
 */
function wcasl_show_store_locked_notice() {
	if ( ! wcasl_is_store_locked() ) {
		return;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wc_add_notice(
			__( 'Purchases are temporarily unavailable from Friday 6:00 PM to Saturday 6:00 PM.', 'wcasl' ),
			'error'
		);
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wc_add_notice(
			__( 'Purchases are temporarily unavailable from Friday 6:00 PM to Saturday 6:00 PM.', 'wcasl' ),
			'error'
		);
	}
}
add_action( 'template_redirect', 'wcasl_show_store_locked_notice' );
