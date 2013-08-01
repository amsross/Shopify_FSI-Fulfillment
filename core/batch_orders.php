<?php

	$batched_orders = array();
	$new_orders = array();
	$preferences = array();

	try {

		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		$select = "SELECT * FROM preferences WHERE Token = '{$_SESSION['token']}' LIMIT 1";

		if ($resultSelect = $mysqli->query($select)) {
			
			if (count($resultSelect->num_rows) < 1) {

				$preferences = array(
					'ClientCode' => null,
					'CarrierCode' => null,
					);
			} else {

				while ($row = $resultSelect->fetch_assoc()) {
					$preferences = $row;
				}
			}
			
			$resultSelect->close();
		}

		$select = "SELECT * FROM orders";
		if ($resultSelect = $mysqli->query($select)) {

			while ($row = $resultSelect->fetch_assoc()) {
				$batched_orders[] = $row;
			}

			$resultSelect->close();
		}

		// set up basic connection
		if (!@$ftp_conn = ftp_connect(FTP_SERVER)) {

			throw new Exception("Error: FTP connection failed.");
		}

		// login with username and password
		if (!@$login_result = ftp_login($ftp_conn, FTP_USER_NAME, FTP_USER_PASS)) {

			throw new Exception("Error: FTP login failed.");
		}

		try {

			// Get all paid orders
			$order_object_response = $shopifyClient->call('GET', '/admin/orders.json?financial_status=paid');
			$smarty->assign('order_object', $order_object_response);
			
			$order_objects = json_encode( $order_object_response );
			$order_objects = json_decode( $order_objects );

			foreach ( $order_objects as $order_object ) :

				foreach ( $batched_orders as $batched_order ) :

					if ($order_object->id == $batched_order['OrderNumber']) {

						continue 2;
					}
				endforeach;

				$insert = "INSERT INTO
					orders (
						FileType,
						ClientCode,
						OrderNumber,
						CarrierCode,
						ShipToName,
						ShipToAddr1,
						ShipToAddr2,
						ShipToAddr3,
						ShipToAddr4,
						ShipToCity,
						ShipToState,
						ShipToZip,
						ShipToCountry,
						ShipToPhone,
						EmailName,
						EmailAddress,
						OrderDate,
						Batched
					)
					VALUES (
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
				if (!$resultInsert = $mysqli->query($insert)) {

					throw new Exception("Error: " . $mysqli->error);
				} else {
					foreach ( $order_object->line_items as $line_item ) :
						if (!$mysqli->query("
							INSERT INTO items (
								OrderNumber,
								ItemNumber,
								QtyOrdered
							)
							VALUES (
								'{$order_object->id}',
								'{$line_item->sku}',
								'{$line_item->quantity}'
							)
						")) {
							throw new Exception("Error: " . $mysqli->error);
						}
					endforeach;
				}
			endforeach;
			
			if (!$resultSelect = $mysqli->query("
				SELECT * FROM items
				JOIN orders on items.OrderNumber = orders.OrderNumber
				AND Batched = false
			")) :
				throw new Exception("Error: " . $mysqli->error);
			else :
				if ( $resultSelect->num_rows > 0 ) :
			
					$fileCSVName = 'BRYord' . date('mdY') . '.csv';
					$fileCSV = fopen($fileCSVName, 'w');
					$line = "FileType,ClientCode,OrderNumber,CarrierCode,ShipToName,ShipToAddr1,ShipToAddr2,ShipToAddr3,ShipToAddr4,ShipToCity,ShipToState,ShipToZip,ShipToCountry,ItemNumber,QtyOrdered,ShipToPhone,EmailName,EmailAddress,OrderDate\n";
					fwrite($fileCSV, $line);
					
					while ( $order_item = $resultSelect->fetch_object() ) :
						$batchedOrders[] = $order_item->OrderNumber;
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

					// try to upload $file
					if (!@ftp_put($ftp_conn, FTP_SERVER_DIR . $fileCSVName, $fileCSVName, FTP_BINARY)) :

						throw new Exception("Error: There was a problem while uploading $fileCSVName");
					endif;

					$smarty->assign('response', $resultSelect->num_rows . ' Orders Batched');
					
					// mark all the batched products as such
					foreach ( $batchedOrders as $batchedOrder ) :
						if (!$resultUpdate = $mysqli->query("
							UPDATE orders
							SET Batched = true
							WHERE OrderNumber = '" . $batchedOrder . "'
						")) :
							// printf("Error: %s\n", $mysqli->error);

							if ( file_exists( $fileCSVName ) ) :
								rename( $fileCSVName, 'error-'.$fileCSVName );
							endif;

							throw new Exception("Error: " . $mysqli->error);
						else :
							if ( file_exists( $fileCSVName ) ) :
								unlink( $fileCSVName );
							endif;
						endif;
					endforeach;
				endif;
			endif;

		} catch (ShopifyApiException $e) {

			// handle the exception
			$smarty->assign('response', $e->getMessage());

		} catch (Exception $e) {

			// handle the exception
			$smarty->assign('response', $e->getMessage());
		}

		$mysqli->close();

	} catch (ShopifyApiException $e) {
		
		// handle the exception
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}