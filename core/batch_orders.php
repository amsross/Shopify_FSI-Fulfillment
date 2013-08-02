<?php

	$batched_orders = array();
	$new_orders = array();

	try {

		// open up MySQL connection
		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		// set up basic connection
		if (!@$ftp_conn = ftp_connect(FTP_SERVER)) {

			throw new Exception("Error: FTP connection failed.");
		}

		// login with username and password
		if (!@$login_result = ftp_login($ftp_conn, FTP_USER_NAME, FTP_USER_PASS)) {

			throw new Exception("Error: FTP login failed.");
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

		// Get all paid orders to see which have been batched.
		// Store the others.
		$order_object_response = $shopifyClient->call('GET', '/admin/orders.json?financial_status=paid');
		$smarty->assign('order_object', $order_object_response);
		
		$order_objects = json_encode( $order_object_response );
		$order_objects = json_decode( $order_objects );

		foreach ( $order_objects as $order_object ) :

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
			if (!$resultInsert = $mysqli->query($insert)) :

				throw new Exception("Error: " . $mysqli->error);
			else :

				foreach ( $order_object->line_items as $line_item ) :

					// Save the items in the DB too
					// We'll need these for the CSV
					$query = "INSERT INTO
								items (Token, OrderNumber, ItemNumber, QtyOrdered )
							VALUES (
								'{$_SESSION['token']}',
								'{$order_object->id}',
								'{$line_item->sku}',
								'{$line_item->quantity}'
							)
						";

					if (!$mysqli->query($query)) {

						throw new Exception("Error: " . $mysqli->error);
					}
				endforeach;
			endif;
		endforeach;
		
		// Get all the stored items to build the CSV batch file with
		$query = "SELECT * FROM items
			JOIN orders on items.OrderNumber = orders.OrderNumber
			AND orders.Batched = false
			WHERE orders.Token = '{$_SESSION['token']}'
		";

		if (!$resultSelect = $mysqli->query($query)) :

			throw new Exception("Error: " . $mysqli->error);
		else :

			if ( $resultSelect->num_rows > 0 ) :
		
				// Determine the CSV's filename
				$fileCSVName = 'BRYord' . date('mdY') . '.csv';

				// Try to get an existing version of the file on the server
				if ( @ftp_get($ftp_conn, $fileCSVName, FTP_SERVER_DIR . $fileCSVName, FTP_BINARY) ) {
					$line = "";
				} else {
					// If no previous version exists, create the column headers
					$line = "FileType,ClientCode,OrderNumber,CarrierCode,ShipToName,ShipToAddr1,ShipToAddr2,ShipToAddr3,ShipToAddr4,ShipToCity,ShipToState,ShipToZip,ShipToCountry,ItemNumber,QtyOrdered,ShipToPhone,EmailName,EmailAddress,OrderDate\n";
				}

				// Open the CSV file for writing without destroying existing content
				$fileCSV = fopen($fileCSVName, 'a');

				// Let there be light!
				fwrite($fileCSV, $line);
				
				while ( $order_item = $resultSelect->fetch_object() ) :

					// Add thisorder to the list to mark as batched if we don't run into trouble
					$batchedOrders[] = $order_item;

					// Write the order's info as a line in the CSV file
					$line = $order_item->FileType . ",";
					$line .= $order_item->ClientCode . ",";
					$line .= $order_item->OrderNumber . ",";
					$line .= $order_item->CarrierCode . ",";
					$line .= $order_item->ShipToName . ",";
					$line .= $order_item->ShipToAddr1 . ",";
					$line .= $order_item->ShipToAddr2 . ",";
					$line .= $order_item->ShipToAddr3 . ",";
					$line .= $order_item->ShipToAddr4 . ",";
					$line .= $order_item->ShipToCity . ",";
					$line .= $order_item->ShipToState . ",";
					$line .= $order_item->ShipToZip . ",";
					$line .= $order_item->ShipToCountry . ",";
					$line .= $order_item->ItemNumber . ",";
					$line .= $order_item->QtyOrdered . ",";
					$line .= $order_item->ShipToPhone . ",";
					$line .= $order_item->EmailName . ",";
					$line .= $order_item->EmailAddress . ",";
					$line .= $order_item->OrderDate . "\n";

					fwrite($fileCSV, $line);
				endwhile;

				fclose($fileCSV);

				// Try to upload CSV file
				if (!@ftp_put($ftp_conn, FTP_SERVER_DIR . $fileCSVName, $fileCSVName, FTP_BINARY)) :

					throw new Exception("Error: There was a problem while uploading $fileCSVName");
				endif;

				$smarty->assign('response', $resultSelect->num_rows . ' Orders Batched');
				
				// Mark all the batched products as such
				foreach ( $batchedOrders as $batchedOrder ) :

					$update = "UPDATE orders
						SET Batched = true
						WHERE OrderNumber = '{$batchedOrder->OrderNumber}'
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
			endif;
		endif;

		$mysqli->close();

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}