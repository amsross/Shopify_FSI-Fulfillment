<?php

	$smarty->assign('response', 'The page contains no data');

	try {

		$delete = "DELETE
					FROM preferences
					WHERE Shop = '{$_SESSION['shop']}'
					";

		$resultSelect = $mysqli->query($delete);

	} catch (ShopifyApiException $e) {
		
		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		// error_log(var_export($e->getTrace(),true));
		error_log(var_export($e,true));
		$smarty->assign('response', $e->getMessage());
	}