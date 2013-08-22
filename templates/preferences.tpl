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
				<td>
					<select name="CarrierCode">
						<option {if $preferences.CarrierCode eq 'UGR'}selected{/if} value="UGR">UPS Ground Residential</option>
						<option {if $preferences.CarrierCode eq 'UGC'}selected{/if} value="UGC">UPS Ground Commercial</option>
						<option {if $preferences.CarrierCode eq 'U3PP'}selected{/if} value="U3PP">UPS 3 Day Select</option>
						<option {if $preferences.CarrierCode eq 'U2PP'}selected{/if} value="U2PP">UPS 2nd Day Air</option>
						<option {if $preferences.CarrierCode eq 'U2PA'}selected{/if} value="U2PA">UPS 2ND Day Air AM</option>
						<option {if $preferences.CarrierCode eq 'UNPP'}selected{/if} value="UNPP">UPS Next Day Air Saver</option>
						<option {if $preferences.CarrierCode eq 'UNPA'}selected{/if} value="UNPA">UPS Next Day Air by 10:30am</option>
						<option {if $preferences.CarrierCode eq 'UNDE'}selected{/if} value="UNDE">UPS Next Day Air A.M.</option>
						<option {if $preferences.CarrierCode eq 'UWSC'}selected{/if} value="UWSC">UPS Standard to Canada</option>
						<option {if $preferences.CarrierCode eq 'UWEP'}selected{/if} value="UWEP">UPS Worldwide Express / Saver</option>
						<option {if $preferences.CarrierCode eq 'UWEX'}selected{/if} value="UWEX">UPS Worldwide Expedited</option>
						<option {if $preferences.CarrierCode eq 'FC'}selected{/if} value="FC">USPS First Class</option>
						<option {if $preferences.CarrierCode eq 'PM'}selected{/if} value="PM">USPS Priority Mail</option>
						<option {if $preferences.CarrierCode eq 'PME'}selected{/if} value="PME">USPS Flat-Rate Envelope</option>
						<option {if $preferences.CarrierCode eq 'PMS'}selected{/if} value="PMS">USPS Small Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'PMM'}selected{/if} value="PMM">USPS Medium Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'PML'}selected{/if} value="PML">USPS Large Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'EM'}selected{/if} value="EM">USPS Express</option>
						<option {if $preferences.CarrierCode eq 'AM'}selected{/if} value="AM">USPS Air Mail</option>
						<option {if $preferences.CarrierCode eq 'GPM'}selected{/if} value="GPM">USPS International Priority Mail</option>
						<option {if $preferences.CarrierCode eq 'IPME'}selected{/if} value="IPME">USPS International Flat-Rate Envelope</option>
						<option {if $preferences.CarrierCode eq 'IPMS'}selected{/if} value="IPMS">USPS International Small Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'IPMM'}selected{/if} value="IPMM">USPS International Medium Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'IPML'}selected{/if} value="IPML">USPS International Large Flat Rate Box</option>
						<option {if $preferences.CarrierCode eq 'MM'}selected{/if} value="MM">USPS Media Mail</option>
						<option {if $preferences.CarrierCode eq 'PP'}selected{/if} value="PP">USPS Parcel Post</option>
						<option {if $preferences.CarrierCode eq 'LTL'}selected{/if} value="LTL">Freight Shipment</option>
						<option {if $preferences.CarrierCode eq 'CPU'}selected{/if} value="CPU">Customer Pickup</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th>FTP Server</th>
				<th>FTP Server Directory</th>
			</tr>
			<tr>
				<td><input type="text" value="{$preferences.FTPServer}" name="FTPServer"/></td>
				<td><input type="text" value="{$preferences.FTPServerDir}" name="FTPServerDir"/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th>FTP Username</th>
				<th>FTP Password</th>
			</tr>
			<tr>
				<td><input type="text" value="{$preferences.FTPUserName}" name="FTPUserName"/></td>
				<td><input type="text" value="{$preferences.FTPPassword}" name="FTPPassword"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Save" /></td>
			</tr>
		</tbody>
	</table>
</form>