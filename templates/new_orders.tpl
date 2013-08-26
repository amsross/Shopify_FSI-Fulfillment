<h3>FSI Fulfillment: New Orders</h3>

<h2>{$response}</h2>

<form method="POST" action="?action=batch_orders">
	<table style="width:100%;">
		<tbody>
			<tr>
				<th style="width:9%;"></th>
				<th style="width:30%;">Order Number</th>
				<th style="width:30%;">Pay Status</th>
				<th style="width:30%;">Ship Status</th>
				<th style="width:30%;">Ship To Name</th>
				<th style="width:30%;">Order Date</th>
			</tr>
			{foreach $new_orders as $new_order}
			<tr>
				<td><input type="checkbox" name="orders[]" value="{$new_order->id}" /></td>
				<td><a href="https://{$shopifyClient->shop_domain}/admin/orders/{$new_order->id}" target="_blank">{$new_order->id}</a></td>
				<td>{$new_order->financial_status}</td>
				<td>{$new_order->fulfillment_status}</td>
				<td>{$new_order->shipping_address->first_name} {$new_order->shipping_address->last_name}</td>
				<td>{$new_order->created_at}</td>
			</tr>
			{/foreach}
			<tr>
				<td colspan="4"><input type="submit" value="Batch" /></td>
			</tr>
		</tbody>
	</table>
</form>