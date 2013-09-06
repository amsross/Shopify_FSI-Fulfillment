<?php
	
		@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

		if ($mysqli->connect_errno) {
		
			throw new Exception("Error: " . $mysqli->connect_error);
		}

		$select = "SELECT *
					FROM preferences
					WHERE Shop = '{$_SESSION['shop']}'
					LIMIT 1
					";

		if ($resultSelect = $mysqli->query($select)) {
			
			if (count($resultSelect->num_rows) < 1) {

				$preferences = array(
					'Shop' => null,
					'Token' => null,
					'ClientCode' => null,
					'CarrierCode' => null,
					'FTPServer' => 'ftp.unitedfsi.com',
					'FTPServerDir' => 'SO_Files',
					'FTPUserName' => null,
					'FTPPassword' => null,
					);
				
					$_SESSION['shop'] = $preferences['Shop'];
					$_SESSION['token'] = $preferences['Token'];
			} else {

				while ($row = $resultSelect->fetch_assoc()) {
					$preferences = $row;
				}
			}

			$resultSelect->close();

		}