<?php
	defined('IN_APP') or die; 
	$data->title = 'Passwort ändern';
?>

<p>Mit diesem Formular können Sie Ihr Passwort ändern. Geben Sie dazu zunächst Ihr aktuelles Passwort ein, und tragen Sie dann Ihr gewünschtes neues Passwort in die beiden unteren Felder ein.</p>

<?php if ($data->errors) : ?>
	<div class="errors">
		<?php echo $data->errors; ?>
	</div>
<?php endif; ?>

<form action="./change-password" method="post">
	<fieldset class="input">
		<dl>
			<dt><label for="oldPassword">Aktuelles Passwort:</label></dt>
			<dd><input id="oldPassword" name="oldPassword" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="newPassword">Neues Passwort:</label></dt>
			<dd><input id="newPassword" name="newPassword" type="password" /></dd>
		</dl>
		<dl>
			<dt><label for="passwordConfirmation">Neues Passwort bestätigen:</label></dt>
			<dd><input id="passwordConfirmation" name="passwordConfirmation" type="password" /></dd>
		</dl>
	</fieldset>
	<fieldset class="buttons">
		<input type="submit" name="submit" value="Passwort ändern" />
	</fieldset>
</form>
