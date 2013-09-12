<?php

	include('lib/get_preferences.php');

	$batchedOrders = array();
	$batched_orders = array();
	$line = '';
	$ftp_conn = null;

	$smarty->assign('response', '0 Orders Batched');
	$smarty->assign('batched_orders', array());

	try {

		if (!isset($_POST['orders']) || empty($_POST['orders'])) :

			throw new Exception("Error: No orders selected.");
		endif;

		// Get all the previously stored orders for cross-referencing
		$batched_orders = orders_get_batched($mysqli);

		foreach ($_POST['orders'] as $order) :

			// Get the selected order from Shopify
			$order_object = shopify_get_order($shopifyClient, $order);

			// Make sure this order was selected for batching
			if ( !is_array($_POST['orders']) || !in_array($order_object->id, $_POST['orders']) ) :

				continue;
			else :
			
				foreach ( $batched_orders as $batched_order ) :

					if ($order_object->id == $batched_order['OrderNumber']) {

						continue 2;
					}
				endforeach;
			endif;

			// Add thisorder to the list to mark as batched if we don't run into trouble
			$batchedOrders[] = $order_object;

			$raw_post_data = $order_object;

			include('webhook_orders_paid.php');
		endforeach;

		$smarty->assign('response', count($batchedOrders) . ' Orders Batched');

		$smarty->assign('batched_orders', $batchedOrders);
	} catch (ShopifyApiException $e) {
		
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	}