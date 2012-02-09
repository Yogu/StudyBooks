<?php
	defined('IN_APP') or die; 
	$data->title = 'Benutzerverwaltung';
?>

<p><a href="./users/create">Benutzer erstellen</a> | <a href="./users/create-many">Viele Benutzer erstellen</a></p>

<?php if (count($data->users)) : ?>
	<table>
		<thead>
			<tr>
				<th>Benutzername</th>
				<th>E-Mail-Adresse</th>
				<th>Rolle</th>
				<th>Status</th>
				<th>Erstelldatum</th>
				<th>Letzte Anmeldung</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data->users as $user) : ?>
				<tr>
					<td><a href="./users/<?php echo $user->id; ?>"><?php echo htmlspecialchars($user->name); ?></a></td>
					<td><?php echo htmlspecialchars($user->email); ?></td>
					<td>
						<?php
							switch ($user->role) {
								case 'guest':
									echo 'Gast';
									break;
								case 'poster':
									echo 'Benutzer';
									break;
								case 'admin':
									echo 'Administrator';
									break;
							}
						?>
					</td>
					<td><?php echo $user->isBanned ? 'Gesperrt' : 'Aktiviert'; ?></td>
					<td><?php echo strftime('%a, %#d. %B %Y %H:%M', $user->createTime); ?></td>
					<td><?php if ($user->lastLoginTime) echo strftime('%a, %#d. %B %Y %H:%M', $user->lastLoginTime); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else : ?>
	<p>Momentan existiert kein Benutzer.</p>
<?php endif; ?>