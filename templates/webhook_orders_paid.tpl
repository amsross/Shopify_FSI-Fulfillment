<h3>FSI Fulfillment: Order Payment</h3>

<h2>{$response}</h2>

<table style="width:100%;">
	<tbody>
		<tr>
			<th style="width:33%;">Order Number</th>
			<th style="width:33%;">Ship To Name</th>
			<th style="width:33%;">Order Date</th>
		</tr>
		{foreach $batched_orders as $batched_order}
		<tr>
			<td><a href="https://{$shopifyClient->shop_domain}/admin/orders/{$batched_order->id}" target="_blank">{$batched_order->id}</a></td>
			<td>{$batched_order->shipping_address->first_name} {$batched_order->shipping_address->last_name}</td>
			<td>{$batched_order->created_at}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
