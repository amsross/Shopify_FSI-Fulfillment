<h3>FSI Fulfillment: Preferences</h3>

<h2>{$response}</h2>

<form method="POST">
<table style="width:100%;">
	<tbody>
		<tr>
			<th style="width:50%;">Client Code</th>
			<th style="width:50%;">Carrier Code</th>
		</tr>
		<tr>
			<td><input type="text" value="{$preferences.ClientCode}" name="ClientCode"/></td>
			<td><input type="text" value="{$preferences.CarrierCode}" name="CarrierCode"/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Save" /></td>
		</tr>
	</tbody>
</table>
</form>