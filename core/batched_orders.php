<?php

	include('lib/get_preferences.php');

	$batched_orders = array();
	$new_orders = array();

	$smarty->assign('response', 'No Batched Orders Found');
	$smarty->assign('batched_orders', $batched_orders);
	try {

		$select = "SELECT *
					FROM orders
					WHERE Shop = '{$_SESSION['shop']}'
					AND Batched = true
					ORDER BY id DESC
					LIMIT 0, 20
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
