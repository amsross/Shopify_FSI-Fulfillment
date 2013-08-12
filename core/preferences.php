<?php

	$preferences = array();
	$response = '';
	
	$smarty->assign('preferences', $preferences);
	$smarty->assign('response', '');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		try {
		
			@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

			if ($mysqli->connect_errno) {
			
				throw new Exception("Error: " . $mysqli->connect_error);
			}

			$ClientCode = filter_var($_POST['ClientCode'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$CarrierCode = filter_var($_POST['CarrierCode'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

			$insert = "INSERT INTO preferences (Token, ClientCode, CarrierCode)
						VALUES ('{$_SESSION['token']}', '$ClientCode', '$CarrierCode')
						ON DUPLICATE KEY
							UPDATE
								Token = '{$_SESSION['token']}',
								ClientCode = '$ClientCode',
								CarrierCode = '$CarrierCode'
								";

			if ($resultSelect = $mysqli->query($insert)) {

				$smarty->assign('response', 'Preferences Updated');
			}

		} catch (ShopifyApiException $e) {
			
			$smarty->assign('response', $e->getMessage());
		} catch (Exception $e) {

			$smarty->assign('response', $e->getMessage());
		}
	}

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

		$smarty->assign('preferences', $preferences);

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}
