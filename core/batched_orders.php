<?php

	$batched_orders = array();
	$new_orders = array();

	try {

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

		$select = "SELECT *
					FROM orders
					WHERE Token = '{$_SESSION['token']}'
					AND Batched = true
					";

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
