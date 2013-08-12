<?php

	$batched_orders = array();
	$new_orders = array();
	
	$smarty->assign('response', count($new_orders) . ' Unbatched Orders');
	$smarty->assign('new_orders', $new_orders);
	$smarty->assign('order_object', '');


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
			foreach ( $batched_orders as $batched_order ) :

				if ($order_object->id == $batched_order['OrderNumber']) {
					
					continue 2;
				}
			endforeach;

			$new_orders[] = $order_object;
		endforeach;

		$smarty->assign('response', count($new_orders) . ' Unbatched Orders');
		$smarty->assign('new_orders', $new_orders);

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}