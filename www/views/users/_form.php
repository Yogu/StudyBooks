<?php
	defined('IN_APP') or die; 
	$layout = null;
?>
	
<?php if ($data->errors) : ?>
	<div class="errors">
		<?php echo $data->errors; ?>
	</div>
<?php endif; ?>

<form action="./users/<?php if ($request->action == 'edit') echo $data->user->id.'/edit'; else echo 'create'; ?>" method="post">
	<fieldset class="input">
		<dl>
			<dt><label for="name">Benutzername:</label></dt>
			<dd><input id="name" name="name" type="text" value="<?php echo htmlspecialchars($data->user->name); ?>" /></dd>
		</dl>
		<dl>
			<dt><label for="password">Passwort:</label></dt>
			<dd><input id="password" name="password" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="passwordConfirmation">Passwort bestätigen:</label></dt>
			<dd><input id="passwordConfirmation" name="passwordConfirmation" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="passwordConfirmation">Generiertes Passwort:</label></dt>
			<dd><?php echo Security::generateRandomPassword(); ?></dd>
		</dl>
		<dl>
			<dt><label for="email">E-Mail-Adresse:</label></dt>
			<dd><input id="email" name="email" type="text" value="<?php echo htmlspecialchars($data->user->email); ?>" /></dd>
		</dl>
		<dl>
			<dt><label for="email">Rolle:</label></dt>
			<dd>
				<select size="1" name="role">
					<option value="guest" <?php if ($data->user->role == 'guest') echo ' selected="selected"' ?>>Gast</option>
					<option value="poster" <?php if ($data->user->role == 'poster') echo ' selected="selected"' ?>>Benutzer mit Upload-Recht</option>
					<option value="admin" <?php if ($data->user->role == 'admin') echo ' selected="selected"' ?>>Administrator</option>
				</select>
			</dd>
		</dl>
		<dl>
			<dt><label for="isBanned">Status:</label></dt>
			<dd><label for="isBanned"><input id="isBanned" name="isBanned" type="checkbox" <?php if ($data->user->isBanned) echo 'checked="checked"'; ?> /> Benutzer sperren (Anmeldung verweigern)</label></dd>
		</dl>
	</fieldset>
	<fieldset class="buttons">
		<input type="submit" name="submit" value="Absenden" />
	</fieldset>
</form>