<?php
	defined('IN_APP') or die; 
	$data->title = 'Benutzer gelöscht';
?>

<p>Der Benutzer <b><?php echo htmlspecialchars($data->user->name); ?></b> wurde gelöscht.</p>
<p><a href="./users">» Zur Benutzerliste</a></p>
