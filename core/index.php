<?php

	try {
		
		$shop_response = $shopifyClient->call('GET', '/admin/shop.json');
		$smarty->assign('shop_name', $shop_response['name']);
		
		$product_count_response = $shopifyClient->call('GET', '/admin/products/count.json');
		$smarty->assign('product_count', $product_count_response);
		
		$order_count_response = $shopifyClient->call('GET', '/admin/orders/count.json');
		$smarty->assign('order_count', $order_count_response);
		
		$order_object_response = $shopifyClient->call('GET', '/admin/orders.json?updated_at_min=2012-09-08');
		$smarty->assign('order_object', $order_object_response);
	} catch (ShopifyApiException $ex) {
		// handle the exception
		debug($ex);
	}
?>