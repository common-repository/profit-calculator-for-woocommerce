<?php

	/**
	 * Plugin Name: Profit Calculator for WooCommerce
	 * Description: This plugin lets the admin calculate the profits they're making on orders.
	 * Author:      Lewis Self
	 * Author URI:  https://selfdesigns.co.uk/
	 * Version:			1.0.0
	 * License:			GPL2+
	 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
	 *
	 * WC requires at least:	3.0.0
	 * WC tested up to:				3.6.4
	 */

	if(!defined('ABSPATH'))
	{
		exit;
	}

	// Run admin actions
	if(is_admin())
	{
		require_once('admin/index.php');
	}

	/**
	 * Calculates order profits
	 *
	 * @param		WC_Order		$order	The order which to calculate the profits
	 *
	 * @return	bool | int					The calculated profit. If not possible to calculate, return false
	 */
	function pcfw_calculate_order_profits($order)
	{
		$products             = $order->get_items();
		$total                = 0;
		$cost_price           = 0;
		$cost_price_completed = true;

		foreach($products as $product)
		{
			$product_id        = ($product['variation_id'] ? $product['variation_id'] : $product['product_id']);
			$single_cost_price = get_post_meta($product_id, '_cost_price', true);

			if($single_cost_price != null)
			{
				$total      += $product['total'];
				$cost_price += $single_cost_price * $product['qty'];
			}
			else
			{
				$cost_price_completed = false;

				break;
			}
		}

		return ($cost_price_completed ? wc_price($total - $cost_price) : false);
	}

	/**
	 * Calculate profit margins and save information to order
	 *
	 * @param	int	$order_id	Calculate the profits from the order id
	 */
	function pcfw_save_order_profit_on_completed_order($order_id)
	{
		update_post_meta($order_id, '_order_profit', pcfw_calculate_order_profits(new WC_Order($order_id)));
	}
	add_action('woocommerce_order_status_completed', 'pcfw_save_order_profit_on_completed_order');

?>
