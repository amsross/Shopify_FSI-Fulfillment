<?php
include_once('lib/Smarty.class.php');
include_once('lib/config.lib.php');
include_once('lib/session.lib.php');
include_once('lib/ohShopify/shopify.php');

// if the code param has been sent to this page... we are in Step 2
if (isset($_GET['code'])) {

	// Step 2: do a form POST to get the access token
	$shopifyClient = new ShopifyClient($_GET['shop'], "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
	session_unset();
	$_SESSION['token'] = $shopifyClient->getAccessToken($_GET['code']);
	if ($_SESSION['token'] != '')
		$_SESSION['shop'] = $_GET['shop'];

	@$mysqli = new mysqli(MYSQL_SERVER, MYSQL_DB_UNAME, MYSQL_DP_PWORD, MYSQL_DB_NAME);

	if ($mysqli->connect_errno) {

		$table_format = "CREATE TABLE IF NOT EXISTS `items` (
			`Token` int(255) NOT NULL AUTO_INCREMENT,
			`OrderNumber` varchar(255) NOT NULL,
			`ItemNumber` varchar(255) NOT NULL,
			`QtyOrdered` varchar(255) NOT NULL,
			PRIMARY KEY (`Token`),
			KEY `FK_OrderNumber` (`OrderNumber`)
		) ENGINE=InnoDB	DEFAULT CHARSET=utf8 AUTO_INCREMENT=272 ;

		CREATE TABLE IF NOT EXISTS `orders` (
			`Token` varchar(255) DEFAULT NULL,
			`FileType` varchar(255) NOT NULL,
			`ClientCode` varchar(255) DEFAULT NULL,
			`OrderNumber` varchar(255) DEFAULT NULL,
			`CarrierCode` varchar(255) DEFAULT NULL,
			`ShipToName` varchar(255) DEFAULT NULL,
			`ShipToAddr1` varchar(255) DEFAULT NULL,
			`ShipToAddr2` varchar(255) DEFAULT NULL,
			`ShipToAddr3` varchar(255) DEFAULT NULL,
			`ShipToAddr4` varchar(255) DEFAULT NULL,
			`ShipToCity` varchar(255) DEFAULT NULL,
			`ShipToState` varchar(255) DEFAULT NULL,
			`ShipToZip` varchar(255) DEFAULT NULL,
			`ShipToCountry` varchar(255) DEFAULT NULL,
			`ShipToPhone` varchar(255) DEFAULT NULL,
			`EmailName` varchar(255) DEFAULT NULL,
			`EmailAddress` varchar(255) DEFAULT NULL,
			`OrderDate` timestamp NULL DEFAULT NULL,
			`ScrapedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`Batched` tinyint(1) NOT NULL,
			UNIQUE KEY `OrderNumber` (`OrderNumber`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `preferences` (
			`Token` varchar(255) NOT NULL,
			`ClientCode` text,
			`CarrierCode` text,
			UNIQUE KEY `Token` (`Token`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;

		ALTER TABLE `items`
			ADD CONSTRAINT `FK_OrderNumber` FOREIGN KEY (`OrderNumber`) REFERENCES `orders` (`OrderNumber`) ON DELETE CASCADE ON UPDATE CASCADE;
		";

		$mysqli->query($table_format);

		$resultSelect->close();
	}

	header("Location: index.php");

	exit;
} else if (isset($_POST['shop']) || isset($_GET['shop'])) {
	
	// Step 1: get the shopname from the user and redirect the user to the
	// shopify authorization page where they can choose to authorize this app
	$shop = isset($_POST['shop']) ? $_POST['shop'] : $_GET['shop'];
	$shopifyClient = new ShopifyClient($shop, "", SHOPIFY_API_KEY, SHOPIFY_SECRET);

	// get the URL to the current page
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}

	// redirect to authorize url
	header("Location: " . $shopifyClient->getAuthorizeUrl(SHOPIFY_SCOPE, $pageURL));
	exit;
}

// Show the form to ask the user for their shop name
$smarty->assign('title', 'FSI Fulfillment');
$smarty->display('templates/header.tpl');
$smarty->display('templates/authorize.tpl');
$smarty->display('templates/footer.tpl');

?>
