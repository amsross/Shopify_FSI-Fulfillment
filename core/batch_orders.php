<?php

	include('lib/get_preferences.php');

	$batched_orders = array();
	$batchedOrders = array();
	$new_orders = array();
	$line = '';
	$ftp_conn = null;

	$smarty->assign('batched_orders', $batchedOrders);
	$smarty->assign('response', count($batchedOrders) . ' Orders Batched');

	try {

		if (!isset($_POST['orders']) || empty($_POST['orders'])) :

			throw new Exception("Error: No orders selected.");
		endif;

		// Open the FTP connection and login
		$ftp_conn = ftp_open($preferences, $ftp_conn);

		// Get all the previously stored orders for cross-referencing
		$batched_orders = orders_get_batched($mysqli);

		// Create a CSV file and write the first line depending on the FTP version
		csv_open($preferences, $ftp_conn, $line);

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

			// Create a DB entry for the order
			order_insert($mysqli, $order_object);

			// Add thisorder to the list to mark as batched if we don't run into trouble
			$batchedOrders[] = $order_object;

			// Write the order's line to the CSV
			csv_write($preferences, $order_object, $line);
		endforeach;

		// Upload the CSV to FSI's server
		csv_upload($preferences, $ftp_conn);

		$smarty->assign('response', count($batchedOrders) . ' Orders Batched');
		
		// Mark all the batched products as such
		foreach ( $batchedOrders as $batchedOrder ) :

			// Mark all the products as successfully batched
			order_update($mysqli, $batchedOrder);
		endforeach;

		// Mark the order as Fulfilled in Shopify
		shopify_fulfill($shopifyClient, $order_object);

		$smarty->assign('batched_orders', $batchedOrders);

		$mysqli->close();

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}