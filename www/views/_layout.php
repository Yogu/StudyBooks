<!DOCTYPE html>
<html lang="de-de">
	<head>
		<meta charset="utf-8" />		
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<base href="<?php echo ROOT_URL ?>" />
		
<?php
			$stylesheets = array('reset.css', 'general.css', 'layout.css', 'elements.css');
			$scripts = array('jquery-1.5.2.min.js');
			
			if (is_array($data->stylesheets))
				$stylesheets = array_merge($stylesheets, $data->stylesheets);
			if (is_array($data->scripts))
				$scripts = array_merge($scripts, $data->scripts);
		
			foreach ($stylesheets as $stylesheet)
				echo '		<link rel="stylesheet" type="text/css" href="./stylesheets/'.htmlspecialchars($stylesheet).'" />'."\n";
		
			foreach ($scripts as $script)
				echo '		<script type="text/javascript" src="./scripts/'.htmlspecialchars($script).'"></script>'."\n"; 
		?>
		
		<title><?php echo htmlspecialchars(Config::$config->site->title) .' – '. htmlspecialchars($data->title); ?></title>
	</head>
	<body>
		<header>
			<h1><a href="./"><?php echo htmlspecialchars(Config::$config->site->title); ?></a></h1>
			
			<?php if ($request->session) : ?>
			<div id="login-box">
				<div>Logged in as <b><?php echo $request->user->name; ?></b></div>
				<div><form action="./logout" method="post"><input type="submit" name="logout" value="Log out" /></form></div>
			</div>
			<?php endif; ?>
		
			<nav id="menu">
				<ul>
					<?php if ($request->session) : ?>
						<li><a href="./">Home</a></li>
						<li><a href="./change-password">Passwort ändern</a></li>
						<?php if ($request->user->role == 'admin') : ?>
							<li><a href="./admin">Administration</a></li>
							<li><a href="./users">Benutzerverwaltung</a></li>
						<?php endif; ?>
					<?php else : ?>
						<li><a href="./logout">Log in</a></li>
					<?php endif; ?>
				</ul>
			</nav>
		</header>
		
		<div id="content">
			<h2><?php echo htmlspecialchars($data->title); ?></h2>
			
			<?php
				echo $body;
			?>
		</div>
		
		<footer>
			<p><a href="./imprint">Imprint</a></p>
		</footer>
	</body>
</html>
