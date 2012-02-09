<?php
	defined('IN_APP') or die; 
	$data->title = 'Benutzer löschen';
?>

<p>Möchten Sie den Benutzer <b><?php echo htmlspecialchars($data->user->name); ?></b> wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>

<form action="<?php echo htmlspecialchars($this->request->url); ?>" method="post">
	<input type="submit" name="confirm" value="Benutzer löschen" />
	<input type="submit" name="cancel" value="Abbrechen" />
</form>
