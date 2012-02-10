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
			
			if (isset($data->stylesheets))
				$stylesheets = array_merge($stylesheets, $data->stylesheets);
			if (isset($data->scripts))
				$scripts = array_merge($scripts, $data->scripts);
		
			foreach ($stylesheets as $stylesheet)
				echo '		<link rel="stylesheet" type="text/css" href="./stylesheets/'.htmlspecialchars($stylesheet).'" />'."\n";
		
			foreach ($scripts as $script)
				echo '		<script type="text/javascript" src="./scripts/'.htmlspecialchars($script).'"></script>'."\n"; 
		?>
		
		<title>{html ($config->site->title)}  – {block "title"}{/block}</title>
	</head>
	<body>
		<header>
			<h1><a href="./">{html $config->site->title}</a></h1>
			
			{if $request->session}
				<div id="login-box">
					<div>Logged in as <b>{html $request->user->name}</b></div>
					<div>
						<form action="./logout" method="post">
							<input type="submit" name="logout" value="Log out" />
						</form>
					</div>
				</div>
			{/if}
		
			<nav id="menu">
				<ul>
					{if $request->session}
						<li><a href="./">Home</a></li>
						<li><a href="./change-password">Passwort ändern</a></li>
						{if $request->user->role == 'admin'}
							<li><a href="./admin">Administration</a></li>
							<li><a href="./users">Benutzerverwaltung</a></li>
						{/if}
					{else}
						<li><a href="./logout">Log in</a></li>
					{/if}
				</ul>
			</nav>
		</header>
		
		<div id="content">
			<h2>{block "title"}{/block}</h2>
			
			<?php
				{block "body"}{/block}
			?>
		</div>
		
		<footer>
			<p><a href="./imprint">Imprint</a></p>
		</footer>
	</body>
</html>
