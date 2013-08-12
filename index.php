<?php
include_once('lib/Smarty.class.php');
include_once('lib/config.lib.php');
include_once('lib/session.lib.php');
include_once('lib/ohShopify/shopify.php');

$action = (isset($_GET['action'])) ? $_GET['action'] : 'index';

// Check for shopify authentication
if (isset($_SESSION['shop']) && isset($_SESSION['token'])){
	$shopifyClient = new ShopifyClient($_SESSION['shop'], $_SESSION['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
	$smarty->assign('shopifyClient', $shopifyClient);
	
	// setup links in view
	$returnURL = 'http://' . $shopifyClient->shop_domain . '/admin';
	$smarty->assign('mainnav', array(
		array('name' => 'Home', 	'href' => '?action=index', 'class' => ''),
		array('name' => 'Preferences', 	'href' => '?action=preferences', 'class' => ''),
		array('name' => 'New orders', 	'href' => '?action=new_orders', 'class' => ''),
		array('name' => 'Batched orders', 	'href' => '?action=batched_orders', 'class' => ''),
		array('name' => 'Return to My Store', 	'href' => $returnURL, 'class' => ''),
	));
	$smarty->assign('shopURL', $shopifyClient->shop_domain);
}else{
	// not authorized to get into the app so show them the authorization form
	$action = "authorize";
	$smarty->assign('mainnav', array(
		array('name' => 'Install', 'href' => getLink('authorize'), 'class' => '')));
}

/* based on the action, get a url */
function getLink($action='') {
	if (strlen($action) == 0)
		return 'index.php';
	else
		return 'index.php?action=' . $action;
}

/*
 *
 *  Include the code and templates for this action
 *
 */
if (file_exists('core/' . $action . '.php'))
	include('core/' . $action . '.php');

$smarty->assign('title', 'FSI Fulfillment');
$smarty->assign('action', $action);
$smarty->display('templates/header.tpl');
$smarty->display('templates/' . $action .'.tpl');
$smarty->display('templates/footer.tpl');

?>
