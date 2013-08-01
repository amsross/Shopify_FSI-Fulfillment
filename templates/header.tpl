<html>
<head>
	<link href="css/css.css" media="screen" rel="stylesheet" type="text/css" />

</head>
<body class="adminz">
	
	<div id="header"> 
		<h1><a href="index.php">{$title}</a></h1>      
	</div> 

	<div id="container" class="clearfix"> 

		<ul id="tabs"> 
			{foreach $mainnav as $link}
			{strip}
			<li><a href="{$link.href}" id="{$link.class}">{$link.name}</a></li>
			{/strip}
			{/foreach}
		</ul>
		
		<div id="main" class="clearfix">
