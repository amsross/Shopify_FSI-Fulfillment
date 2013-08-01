<h3>FSI Fulfillment: New Orders</h3>

<h2>{$response}</h2>

<table style="width:100%;">
	<tbody>
		<tr>
			<th style="width:33%;">Order Number</th>
			<th style="width:33%;">Ship To Name</th>
			<th style="width:33%;">Order Date</th>
		</tr>
		{foreach $new_orders as $new_order}
		<tr>
			<td><a href="https://{$shopifyClient->shop_domain}/admin/orders/{$new_order->id}" target="_blank">{$new_order->id}</a></td>
			<td>{$new_order->shipping_address->first_name} {$new_order->shipping_address->last_name}</td>
			<td>{$new_order->created_at}</td>
		</tr>
		{/foreach}
	</tbody>
</table>