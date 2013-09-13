<?php

function csv_open($fileCSVName, $preferences, $ftp_conn, $line) {

	// // Determine the CSV's filename
	// $fileCSVName = $preferences['ClientCode'] . 'ord' . date('mdY') . '.csv';

	// Try to get an existing version of the file on the server
	if (@ftp_get($ftp_conn, $fileCSVName, $preferences['FTPServerDir'] . '/' . $fileCSVName, FTP_BINARY) || file_exists($fileCSVName)) {
		$line = "";
	} else {
		// If no previous version exists, create the column headers
		// $line = "FileType,ClientCode,OrderNumber,CarrierCode,ShipToName,ShipToAddr1,ShipToAddr2,ShipToAddr3,ShipToAddr4,ShipToCity,ShipToState,ShipToZip,ShipToCountry,ItemNumber,QtyOrdered,ShipToPhone,EmailName,EmailAddress,OrderDate\n";
		$line = "";
	}

	// Open the CSV file for writing without destroying existing content
	$fileCSV = fopen($fileCSVName, 'a');

	// Let there be light!
	fwrite($fileCSV, $line);

	fclose($fileCSV);
};

function csv_write($fileCSVName, $preferences, $order_object, $line) {

	foreach ( $order_object->line_items as $line_item ) :

		// // Determine the CSV's filename
		// $fileCSVName = $preferences['ClientCode'] . 'ord' . date('mdY') . '.csv';

		// Open the CSV file for writing without destroying existing content
		$fileCSV = fopen($fileCSVName, 'a');

		$lineItemSKU = $preferences['ClientCode'] . end(explode('-',$line_item->sku));

		// Write the order's info as a line in the CSV file
			// FileType
		$line .= "STANDARD,";
			// ClientCode
		$line .= $preferences['ClientCode'] . ",";
			// OrderNumber
		$line .= $order_object->id . ",";
			// CarrierCode
		$line .= $preferences['CarrierCode'] . ",";
			// ShipToName
		$line .= $order_object->shipping_address->first_name . " " . $order_object->shipping_address->last_name . ",";
			// ShipToAddr1
		$line .= $order_object->shipping_address->address1 . ",";
			// ShipToAddr2
		$line .= $order_object->shipping_address->address2 . ",";
			// ShipToAddr3
		$line .= ",";
			// ShipToAddr4
		$line .= ",";
			// ShipToCity
		$line .= $order_object->shipping_address->city . ",";
			// ShipToState
		$line .= $order_object->shipping_address->province_code . ",";
			// ShipToZip
		$line .= $order_object->shipping_address->zip . ",";
			// ShipToCountry
		$line .= $order_object->shipping_address->country_code . ",";
			// ItemNumber
		$line .= $lineItemSKU . ",";
			// QtyOrdered
		$line .= $line_item->quantity . ",";
			// ShipToPhone
		$line .= $order_object->shipping_address->phone . ",";
			// EmailName
		$line .= $order_object->customer->first_name . " " . $order_object->customer->last_name . ",";
			// EmailAddress
		$line .= $order_object->customer->email . ",";
			// OrderDate
		$line .= $order_object->created_at . ",";
		$line .= "\n";
	endforeach;

	// Let there be light!
	fwrite($fileCSV, $line);

	fclose($fileCSV);
};

function csv_upload($fileCSVName, $preferences, $ftp_conn) {

	// $fileCSVName = $preferences['ClientCode'] . 'ord' . date('mdY') . '.csv';

	// Try to upload CSV file
	if (!@ftp_put($ftp_conn, $preferences['FTPServerDir'] . '/' . $fileCSVName, $fileCSVName, FTP_BINARY)) :

		throw new Exception("Error: There was a problem while uploading $fileCSVName");
	endif;
};

function ftp_open($preferences, $ftp_conn) {

	// set up basic connection
	if (!@$ftp_conn = ftp_connect($preferences['FTPServer'])) {

		throw new Exception("Error: FTP connection failed.");
	}

	// login with username and password
	if (!@$login_result = ftp_login($ftp_conn, $preferences['FTPUserName'], $preferences['FTPPassword'])) {

		throw new Exception("Error: FTP login failed.");
	}

	// heroku only supports passive FTP
	ftp_pasv($ftp_conn, true);

	return $ftp_conn;
};

function order_insert($mysqli, $order_object) {

	// Create the DB record for the order to poll for status later
	$insert = "INSERT INTO
		orders (
			Shop, OrderNumber, Batched
		)
		VALUES (
			'{$_SESSION['shop']}',
			'{$order_object->id}',
			false
		)
		";

	if (!$resultInsert = $mysqli->query($insert)) :

		throw new Exception("Error: " . $mysqli->error);
	endif;
};

function order_update($fileCSVName, $mysqli, $order_object) {

	$update = "UPDATE orders
		SET Batched = true
		WHERE OrderNumber = '{$order_object->id}'
		AND Shop = '{$_SESSION['shop']}'";

	if (!$resultUpdate = $mysqli->query($update)) :

		// Save a version of the CSV for later investigation
		if ( file_exists( $fileCSVName ) ) :

			rename( $fileCSVName, 'error-'.$fileCSVName );
		endif;

		throw new Exception("Error: " . $mysqli->error);
	else :

		if ( file_exists( $fileCSVName ) ) :
		
			// Get rid of the CSV file
			unlink( $fileCSVName );
		endif;
	endif;
};

function orders_get_batched($mysqli) {

	$batched_orders = array();

	// Get all the previously stored orders for cross-referencing
	$select = "SELECT *
				FROM orders
				WHERE Shop = '{$posted_order_object->shop}'
				";

	if ($resultSelect = $mysqli->query($select)) {

		while ($row = $resultSelect->fetch_assoc()) {

			$batched_orders[] = $row;
		}

		$resultSelect->close();
	}

	return $batched_orders;
};

function shopify_fulfill($shopifyClient, $order_object) {

	$line_items = array();
	foreach ($order_object->line_items as $line_item) {
		$line_items[] = array("id" => $line_item->id);
	}
	$fulfillment = array(
		"fulfillment" => array(
			"tracking_number" => null,
			"notify_customer" => false,
			"line_items" => $line_items,
		)
	);
	$fulfillment_object_response = $shopifyClient->call('POST', "/admin/orders/{$order_object->id}/fulfillments.json", $fulfillment);
	$fulfillment_objects = json_encode( $fulfillment_object_response );
	$fulfillment_object = json_decode( $fulfillment_objects );

	if ($fulfillment_object->status !== "success") {
		
		throw new Exception("Error: Failed to update Shopify fulfillment status.");
	}
};

function shopify_get_order($shopifyClient, $id) {

	return json_decode(json_encode($shopifyClient->call('GET', "/admin/orders/{$id}.json")));
};