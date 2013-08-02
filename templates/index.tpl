<h3>FSI Fulfillment</h3>

<ul>
	{foreach $mainnav as $link}
	{strip}
	<li><a href="{$link.href}" id="{$link.class}">{$link.name}</a></li>
	{/strip}
	{/foreach}
</ul>

<p>If this is your first time using the app, please visit the <a href="?action=preferences">preferences</a> page to set your Shipping Carrier and FSI-designated Client Code.</p>
