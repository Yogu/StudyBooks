<?php
defined('IN_APP') or die;
$layout = null;
?>

<table>
	<thead>
		<tr>
			<th>Benutzername</th>
			<th>E-Mail-Adresse</th>
			<th>Rolle</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data->userList as $user) : ?>
			<tr>
				<td><?php echo htmlspecialchars($user->name); ?></td>
				<td><a href="mailto:<?php echo htmlspecialchars($user->email); ?>"><?php echo htmlspecialchars($user->email); ?></a></td>
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
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
