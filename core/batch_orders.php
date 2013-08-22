<?php

	$batched_orders = array();
	$batchedOrders = array();
	$new_orders = array();

	$smarty->assign('batched_orders', $batchedOrders);
	$smarty->assign('response', count($batchedOrders) . ' Orders Batched');

	try {

		// open up MySQL connection
		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		$select = "SELECT *
					FROM preferences
					WHERE Token = '{$_SESSION['token']}'
					LIMIT 1
					";

		if ($resultSelect = $mysqli->query($select)) {
			
			if (count($resultSelect->num_rows) > 0) {

				while ($row = $resultSelect->fetch_assoc()) {

					$preferences = $row;
				}
			}
			
			$resultSelect->close();
		}

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

		// Get all the previously stored orders for cross-referencing
		$select = "SELECT *
					FROM orders
					WHERE Token = '{$_SESSION['token']}'
					";

		if ($resultSelect = $mysqli->query($select)) {

			while ($row = $resultSelect->fetch_assoc()) {

				$batched_orders[] = $row;
			}

			$resultSelect->close();
		}

		// Determine the CSV's filename
		$fileCSVName = $preferences['ClientCode'] . 'ord' . date('mdY') . '.csv';

		// Try to get an existing version of the file on the server
		if (@ftp_get($ftp_conn, $fileCSVName, $preferences['FTPServerDir'] . $fileCSVName, FTP_BINARY) || file_exists($fileCSVName)) {
			$line = "";
		} else {
			// If no previous version exists, create the column headers
			$line = "FileType,ClientCode,OrderNumber,CarrierCode,ShipToName,ShipToAddr1,ShipToAddr2,ShipToAddr3,ShipToAddr4,ShipToCity,ShipToState,ShipToZip,ShipToCountry,ItemNumber,QtyOrdered,ShipToPhone,EmailName,EmailAddress,OrderDate\n";
		}

		// Open the CSV file for writing without destroying existing content
		$fileCSV = fopen($fileCSVName, 'a');

		if (!isset($_POST['orders']) || empty($_POST['orders'])) :

			throw new Exception("Error: No orders selected.");
		endif;

		$order_object_responses = array();

		foreach ($_POST['orders'] as $order) :

			// Get the selected order
			// Store the others.
			$order_object_response = $shopifyClient->call('GET', "/admin/orders/{$order}.json");

			$order_object_responses[] = $order_object_response;
			
			$order_objects = json_encode( $order_object_response );
			$order_object = json_decode( $order_objects );

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

			// Create the DB record for the order to poll for status later
			$insert = "INSERT INTO
				orders (
					Token, FileType, ClientCode, OrderNumber, CarrierCode, ShipToName, ShipToAddr1, ShipToAddr2, ShipToAddr3, ShipToAddr4,
					ShipToCity, ShipToState, ShipToZip, ShipToCountry, ShipToPhone, EmailName, EmailAddress, OrderDate, Batched
				)
				VALUES (
					'{$_SESSION['token']}',
					'STANDARD',
					'{$preferences['ClientCode']}',
					'{$order_object->id}',
					'{$preferences['CarrierCode']}',
					'{$order_object->shipping_address->first_name} {$order_object->shipping_address->last_name}',
					'{$order_object->shipping_address->address1}',
					'{$order_object->shipping_address->address2}',
					'',
					'',
					'{$order_object->shipping_address->city}',
					'{$order_object->shipping_address->province_code}',
					'{$order_object->shipping_address->zip}',
					'{$order_object->shipping_address->country_code}',
					'{$order_object->shipping_address->phone}',
					'{$order_object->customer->first_name} {$order_object->customer->last_name}',
					'{$order_object->customer->email}',
					'{$order_object->created_at}',
					false
				)
				";

			// Create the DB record for the order to poll for status later
			$insert = "INSERT INTO
				orders (
					Token, OrderNumber, Batched
				)
				VALUES (
					'{$_SESSION['token']}',
					'{$order_object->id}',
					false
				)
				";

			if (!$resultInsert = $mysqli->query($insert)) :

				throw new Exception("Error: " . $mysqli->error);
			endif;

			// Add thisorder to the list to mark as batched if we don't run into trouble
			$batchedOrders[] = $order_object;

			foreach ( $order_object->line_items as $line_item ) :

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
				$line .= $line_item->sku . ",";
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
		endforeach;

		// Let there be light!
		fwrite($fileCSV, $line);

		fclose($fileCSV);

		// Try to upload CSV file
		if (!@ftp_put($ftp_conn, $preferences['FTPServerDir'] . '/' . $fileCSVName, $fileCSVName, FTP_BINARY)) :

			throw new Exception("Error: There was a problem while uploading $fileCSVName");
		endif;

		$smarty->assign('response', count($batchedOrders) . ' Orders Batched');
		
		// Mark all the batched products as such
		foreach ( $batchedOrders as $batchedOrder ) :

			$update = "UPDATE orders
				SET Batched = true
				WHERE OrderNumber = '{$batchedOrder->id}'
				AND Token = '{$_SESSION['token']}'";

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
		endforeach;

		$smarty->assign('batched_orders', $batchedOrders);

		$mysqli->close();

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}