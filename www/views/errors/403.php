<?php
	defined('IN_APP') or die; 
	$data->title = 'Zugriff verweigert';
?>

<p>Sie besitzen nicht die erforderlichen Berechtigungen, um diese Seite aufzurufen.</p>
<p><a href="./">» Zurück zur Startseite</a></p>
<?php if ($data->details) : ?>
	<p><?php echo htmlspecialchars($data->details); ?></p>
<?php endif; ?>
