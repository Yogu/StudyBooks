<?php
defined('IN_APP') or die;
$data->title = "Viele Benutzer erstellen";
?>

<p>Geben Sie die Namen und E-Mail-Adressen der Benutzer ein, die Sie erstellen möchten. Wenn Sie auf &quot;Absenden&quot; klicken, werden Einladungs-Emails an die Benutzer verschickt, in denen ein zufällig generiertes Passwort enthalten ist.</p>

<form action="<?php echo $this->request->url; ?>" method="post">
	<input type="submit" name="submit" value="Absenden" />

	<table>
		<thead>
			<tr>
				<th>Benutzername</th>
				<th>E-Mail-Adresse</th>
				<th>Rolle</th>
			</tr>
		</thead>
		<tbody>
			<?php for ($i = 0; $i < $data->userCount; $i++) : ?>
				<?php $user = $data->users[$i]; ?>
				<?php if ($data->errors[$i]) : ?>
					<tr class="prepended">
						<td colspan="3">
							<div class="errors">
								<?php echo $data->errors[$i]; ?>
							</div>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<td><input type="text" name="name<?php echo $i; ?>" value="<?php echo $user->name; ?>" /></td>
					<td><input type="text" name="email<?php echo $i; ?>" value="<?php echo $user->email; ?>" /></td>
					<td>
						<select name="role<?php echo $i; ?>">
							<option value="guest" <?php if ($user->role == 'guest') echo 'selected="selected" ';?>>Gast (ohne Upload-Recht)</option>
							<option value="poster" <?php if ($user->role == 'poster') echo 'selected="selected" ';?>>Benutzer mit Upload-Recht</option>
							<option value="admin" <?php if ($user->role == 'admin') echo 'selected="selected" ';?>>Administrator</option>
						</select>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
</form>

<h3>Von Textdatei in Formular übernehmen</h3>

<p>Schreiben Sie in jede Zeile ein Benutzer, wobei Benutzername und E-Mail-Adresse durch ein Semikolon getrennt werden müssen.</p>

<form action="<?php echo $this->request->url; ?>" method="post">
	<fieldset class="inputs">
		<div><textarea name="listAsText" class="fullsize"></textarea></div>
		<div><label for="invertNames"><input type="checkbox" name="invertNames" id="invertNames" />Benutzernamen sind im Format <i>Nachname, Vorname</i> und sollen zu <i>Vorname Nachname</i> umgewandelt werden</label></div>
	</fieldset>
	<fieldset class="buttons">
		<input type="submit" name="check" value="In Formular übernehmen" />
	</fieldset>
</form>