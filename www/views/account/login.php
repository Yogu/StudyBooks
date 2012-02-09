<?php
	defined('IN_APP') or die; 
	$data->title = 'Anmeldung';
?>

<?php if ($request->session) : ?>
	<p>Sie sind angemeldet als <b><?php echo htmlspecialchars($request->user->name); ?></b>.</p>
	<p><a href="./">» Zur Startseite</a></p>
	<form action="./anmeldung" method="post">
		<input type="submit" name="logout" value="Abmelden" />
	</form>
<?php else : ?>
	<?php if ($data->isFailed) : ?>
		<div class="errors">
			<p>Die Anmeldung ist fehlgeschlagen. Bitte überprüfen Sie Benutzernamen und Passwort. Sollten Sie Ihr Passwort vergessen haben, schicken Sie bitte eine E-Mail an <a href="mailto:info@yogularm.de">info@yogularm.de</a>.</p>
		</div>
	<?php elseif ($data->isBanned) : ?>
		<div class="errors">
			<p>Ihr Benutzerkonto wurde gesperrt. Wenn Sie den Grund dafür nicht kennen, schicken Sie bitte eine E-Mail an <a href="mailto:info@yogularm.de">info@yogularm.de</a>.</p>
		</div>
	<?php else : ?>
		<p>Diese Internetseite ist nur für registrierte Benutzer verfügbar. Wenn Sie an der Studienfahrt Saint Malo 2011 teilgenommen haben, aber noch keine Zugangsdaten erhalten hast, schreiben Sie bitte eine E-Mail an <a href="mailto:info@yogularm.de">info@yogularm.de</a>.</p>
	<?php endif; ?>
	
	<form action="./anmeldung" method="post">
		<input type="hidden" name="referer" value="<?php echo $request->internalURL; ?>" />
		<fieldset class="input">
			<dl>
				<dt><label for="user">Benutzername:</label></dt>
				<dd><input id="user" name="user" type="text" /></dd>
			</dl>
			<dl>
				<dt><label for="password">Passwort:</label></dt>
				<dd><input id="password" name="password" type="password" /></dd>
			</dl>
		</fieldset>
		<fieldset class="buttons">
			<input type="submit" name="login" value="Anmelden" />
		</fieldset>
	</form>
<?php endif; ?>
