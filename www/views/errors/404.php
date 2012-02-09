<?php
	defined('IN_APP') or die; 
	$data->title = 'Seite nicht gefunden';
?>

<p>Entschuldigung, die angeforderte Seite wurde nicht gefunden.</p>
<p><a href="./">» Zurück zur Startseite</a></p>
<?php if ($data->details) : ?>
	<p><?php echo htmlspecialchars($data->details); ?></p>
<?php endif; ?>
