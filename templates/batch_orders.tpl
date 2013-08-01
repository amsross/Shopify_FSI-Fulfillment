<h3>FSI Fulfillment: Batch Orders</h3>

<h2>{$response}</h2>

<table style="width:100%;">
	<tbody>
		<tr>
			<th style="width:25%;">Order Number</th>
			<th style="width:25%;">Ship To Name</th>
			<th style="width:25%;">Order Date</th>
			<th style="width:25%;">Batched Date</th>
		</tr>
		{foreach $batched_orders as $batched_order}
		<tr>
			<td><a href="https://{$shopifyClient->shop_domain}/admin/orders/{$batched_order.OrderNumber}" target="_blank">{$batched_order.OrderNumber}</a></td>
			<td>{$batched_order.ShipToName}</td>
			<td>{$batched_order.OrderDate}</td>
			<td>{$batched_order.ScrapedDate}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
