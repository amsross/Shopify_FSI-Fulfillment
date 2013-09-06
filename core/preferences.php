<?php

	$response = '';
	
	$smarty->assign('preferences', $preferences);
	$smarty->assign('response', '');


	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		try {
		
			@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

			if ($mysqli->connect_errno) {
			
				throw new Exception("Error: " . $mysqli->connect_error);
			}

			$ClientCode = filter_var(trim($_POST['ClientCode']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$CarrierCode = filter_var(trim($_POST['CarrierCode']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$FTPServer = filter_var(trim($_POST['FTPServer']), FILTER_SANITIZE_URL, FILTER_FLAG_STRIP_HIGH);
			$FTPServerDir = filter_var(trim($_POST['FTPServerDir']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$FTPUserName = filter_var(trim($_POST['FTPUserName']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$FTPPassword = filter_var(trim($_POST['FTPPassword']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

			$insert = "INSERT INTO preferences (Shop, Token, ClientCode, CarrierCode, FTPServer, FTPServerDir, FTPUserName, FTPPassword)
						VALUES ('{$_SESSION['shop']}', '{$_SESSION['token']}', '$ClientCode', '$CarrierCode', '$FTPServer', '$FTPServerDir', '$FTPUserName', '$FTPPassword')
						ON DUPLICATE KEY
							UPDATE
								Shop = '{$_SESSION['shop']}',
								Token = '{$_SESSION['token']}',
								ClientCode = '$ClientCode',
								CarrierCode = '$CarrierCode',
								FTPServer = '$FTPServer',
								FTPServerDir = '$FTPServerDir',
								FTPUserName = '$FTPUserName',
								FTPPassword = '$FTPPassword'
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

		include('lib/get_preferences.php');

		$smarty->assign('preferences', $preferences);

	} catch (ShopifyApiException $e) {
		
		$smarty->assign('response', $e->getMessage());
	} catch (Exception $e) {

		$smarty->assign('response', $e->getMessage());
	}
