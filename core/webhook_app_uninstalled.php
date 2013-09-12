<?php

	$smarty->assign('response', 'The page contains no data');

	try {

		$deletePrefs = "DELETE
					FROM preferences
					WHERE Shop = '{$_SESSION['shop']}'
					";

		$resultSelect = $mysqli->query($deletePrefs);

		$deleteOrders = "DELETE
					FROM orders
					WHERE Shop = '{$_SESSION['shop']}'
					";
		$resultSelect = $mysqli->query($deleteOrders);

	} catch (ShopifyApiException $e) {
		
		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	}