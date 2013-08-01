<?php

	$batched_orders = array();
	$new_orders = array();

	try {

		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		$select = "SELECT * FROM orders WHERE Batched = true";
		if ($resultSelect = $mysqli->query($select)) {

			$smarty->assign('response', $resultSelect->num_rows . ' Batched Orders Found');

			while ($row = $resultSelect->fetch_assoc()) {
				$batched_orders[] = $row;
			}
			$smarty->assign('batched_orders', $batched_orders);

			$resultSelect->close();

		} else {

			$smarty->assign('response', 'No Batched Orders Found');
		}

	} catch (ShopifyApiException $e) {
		
		// handle the exception
		debug($e);
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}
