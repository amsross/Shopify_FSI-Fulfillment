<?php

	include('lib/get_preferences.php');

	$posted_order_object = $raw_post_data;

	$batched_orders = array();
	$new_orders = array();
	$line = '';
	$ftp_conn = null;
	$smarty->assign('response', 'The page contains no data');

	try {

		if (!isset($posted_order_object ) || empty($posted_order_object )) :

			throw new Exception("Error: No orders selected.");
		else :

			// Get the selected order from Shopify
			$order_object = shopify_get_order($shopifyClient, $posted_order_object->id);
			// error_log(print_r($order_object, true)); //check error_log to see the result
		endif;

		// Open the FTP connection and login
		$ftp_conn = ftp_open($preferences, $ftp_conn);

		// Create a CSV file and write the first line depending on the FTP version
		csv_open($preferences, $ftp_conn, $line);

		// Create a DB entry for the order
		order_insert($mysqli, $order_object);

		// Write the order's line to the CSV
		csv_write($preferences, $order_object, $line);

		// Upload the CSV to FSI's server
		csv_upload($preferences, $ftp_conn);

		// Mark all the products as successfully batched
		order_update($mysqli, $order_object);

		// Mark the order as Fulfilled in Shopify
		shopify_fulfill($shopifyClient, $order_object);

		$mysqli->close();

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}