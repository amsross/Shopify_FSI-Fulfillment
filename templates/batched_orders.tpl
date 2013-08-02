<h3>FSI Fulfillment: Batched Orders</h3>

<h2>{$response}</h2>

<table style="width:100%;">
	<tbody>
		<tr>
			<th style="width:50%;">Order Number</th>
			<th style="width:50%;">Batched Date</th>
		</tr>
		{foreach $batched_orders as $batched_order}
		<tr>
			<td><a href="https://{$shopifyClient->shop_domain}/admin/orders/{$batched_order.OrderNumber}" target="_blank">{$batched_order.OrderNumber}</a></td>
			<td>{$batched_order.ScrapedDate}</td>
		</tr>
		{/foreach}
	</tbody>
</table>