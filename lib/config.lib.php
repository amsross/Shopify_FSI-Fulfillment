<?php
	/* SHOPIFY CONFIG */
	define('SHOPIFY_API_KEY', $_ENV['SHOPIFY_API_KEY']);
	define('SHOPIFY_SECRET', $_ENV['SHOPIFY_SECRET']);
	define('SHOPIFY_SCOPE', 'write_orders,read_products,read_customers');
		
	$mysql=parse_url(getenv("CLEARDB_DATABASE_URL"));
	define('MYSQL_SERVER', $mysql["host"]);
	define('MYSQL_DB_NAME', substr($mysql["path"],1));
	define('MYSQL_DB_UNAME', $mysql["user"]);
	define('MYSQL_DP_PWORD', $mysql["pass"]);

	$preferences = array(
		'ClientCode' => null,
		'CarrierCode' => null,
		'FTPServer' => null,
		'FTPServerDir' => 'SO_Files',
		'FTPUserName' => null,
		'FTPPassword' => null,
		);
	
	if (class_exists("Smarty"))
	{
		$smarty = new Smarty;
		//$smarty->force_compile = true;
		$smarty->debugging = false;
		$smarty->caching = false;
		$smarty->cache_lifetime = 120;
	}

	/* simple debug function */
	function debug(&$var) {
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}
