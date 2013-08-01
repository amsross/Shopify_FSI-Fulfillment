<h3>FSI Fulfillment</h3>

<ul>
	{foreach $mainnav as $link}
	{strip}
	<li><a href="{$link.href}" id="{$link.class}">{$link.name}</a></li>
	{/strip}
	{/foreach}
</ul>
