<!DOCTYPE html>
<html lang="de-de">
	<head>
		<meta charset="utf-8" />		
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<base href="{html $rootURL}" />
	
		<?php
			$stylesheets = array('reset.css', 'general.css', 'layout.css', 'elements.css');
			$scripts = array('jquery-1.5.2.min.js');			
		
			foreach ($stylesheets as $stylesheet)
				echo '		<link rel="stylesheet" type="text/css" href="./stylesheets/'.htmlspecialchars($stylesheet).'" />'."\n";
		
			foreach ($scripts as $script)
				echo '		<script type="text/javascript" src="./scripts/'.htmlspecialchars($script).'"></script>'."\n"; 
		?>
		{block "head"}{/block}
		
		<title>{html ($config->site->title)}  – {block "title"}{/block}</title>
	</head>
	<body>
		<header>
			<h1><a href="{url index home}">{html $config->site->title}</a></h1>
			
			{if $request->session}
				<div id="login-box">
					<div>{l LOGGED_IN_AS} <b>{html $request->user->name}</b> {l LOGGED_IN_AS_}</div>
					<div>
						<form action="{url login account}" method="post">
							<input type="submit" name="logout" value="{l LOG_OUT}" />
						</form>
					</div>
				</div>
			{/if}
		
			<nav id="menu">
				<ul>
					{if $request->session}
						<li><a href="{url index home}">{l MENU_HOME}</a></li>
						<li><a href="{url changePassword account}">{l MENU_CHANGE_PASSWORD}</a></li>
						<li><a href="{url index tree}">{l MENU_TREE}</a></li>
						{if $request->user->role == 'admin'}
							<li><a href="{url index admin}">{l MENU_ADMINISTRATION}</a></li>
							<li><a href="{url index users}">{l MENU_USERS}</a></li>
						{/if}
					{else}
						<li><a href="{url login account}">{l LOG_IN}</a></li>
					{/if}
				</ul>
			</nav>
		</header>
		
		<div id="content">
			<h2>{block "title"}{/block}</h2>
			
			<div id="body">
				{block "body"}{/block}
			</div>
		</div>
		
		<footer>
			<p><a href="{url imprint home}">{l IMPRINT}</a></p>
		</footer>
	</body>
</html>
