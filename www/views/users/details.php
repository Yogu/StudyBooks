<?php
	defined('IN_APP') or die; 
	$data->title = 'Benutzer "'.$data->user->name.'"';
?>

<div class="infolist">
	<dl>
		<dt>Name:</dt>
		<dd><?php echo htmlspecialchars($data->user->name); ?></dd>
	</dl>
	
	<dl>
		<dt>E-Mail-Adresse:</dt>
		<dd><?php echo htmlspecialchars($data->user->email); ?></dd>
	</dl>
	
	<dl>
		<dt>Rolle:</dt>
		<dd>
			<?php
				switch ($data->user->role) {
					case 'guest':
						echo 'Gast (ohne Upload-Recht)';
						break;
					case 'poster':
						echo 'Benutzer mit Upload-Recht';
						break;
					case 'admin':
						echo 'Administrator';
						break;
				}
			?>
		</dd>
	</dl>
	
	<dl>
		<dt>Status:</dt>
		<dd><?php echo $data->user->isBanned ? 'Gesperrt (Anmeldung wird verweigert)' : 'Aktiviert (Anmeldung möglich)'; ?>
	</dl>
	
	<dl>
		<dt>Erstellt:</dt>
		<dd><?php echo strftime('%a, %#d. %B %Y %H:%M', $data->user->createTime); ?></dd>
	</dl>
	
	<dl>
		<dt>Letzte Anmeldung:</dt>
		<dd>
			<?php
				if ($data->user->lastLoginTime)
			 		echo strftime('%a, %#d. %B %Y %H:%M', $data->user->lastLoginTime);
			 	else
			 		echo 'noch nie';
			?>
	</dl>
	
	<dl>
		<dt>Aktionen</dt>
		<dd><a href="./users/<?php echo $data->user->id; ?>/edit">Benutzer bearbeiten</a></dd>
		<dd><a href="./users/<?php echo $data->user->id; ?>/delete">Benutzer löschen</a></dd>
	</dl>
</div>