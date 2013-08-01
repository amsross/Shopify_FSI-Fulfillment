<?php

	$batched_orders = array();
	$new_orders = array();

	try {
	
		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		$select = "SELECT * FROM orders";
		if ($resultSelect = $mysqli->query($select)) {

			while ($row = $resultSelect->fetch_assoc()) {
				$batched_orders[] = $row;
			}

			$resultSelect->close();
		}

		// Get all paid orders
		$order_object_response = $shopifyClient->call('GET', '/admin/orders.json?financial_status=paid');
		
		$order_objects = json_encode( $order_object_response );
		$order_objects = json_decode( $order_objects );
		
		foreach ( $order_objects as $order_object ) :

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