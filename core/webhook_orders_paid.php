<?php

	$posted_order_object = $raw_post_data;
	$line = '';
	$ftp_conn = null;

	$smarty->assign('response', 'The page contains no data');

	try {

		if (!isset($posted_order_object ) || empty($posted_order_object )) :

			throw new Exception("Error: No orders selected.");
		endif;

		// Get the selected order from Shopify
		$order_object = shopify_get_order($shopifyClient, $posted_order_object->id);

		// Open the FTP connection and login
		$ftp_conn = ftp_open($preferences, $ftp_conn);

		// Determine the CSV's filename
		$fileCSVName = $preferences['ClientCode'] . 'ord' . date('mdY') . '.' . $order_object->id . '.' . $order_object->shipping_address->last_name . '.csv';

		// Create a CSV file and write the first line depending on the FTP version
		csv_open($fileCSVName, $preferences, $ftp_conn, $line);

		// Create a DB entry for the order
		order_insert($mysqli, $order_object);

		// Write the order's line to the CSV
		csv_write($fileCSVName, $preferences, $order_object, $line);

		// Upload the CSV to FSI's server
		csv_upload($fileCSVName, $preferences, $ftp_conn);

		// Mark the order as Fulfilled in Shopify
		shopify_fulfill($shopifyClient, $order_object);

		// Mark all the products as successfully batched
		order_update($fileCSVName, $mysqli, $order_object);
	} catch (ShopifyApiException $e) {
		
		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	}