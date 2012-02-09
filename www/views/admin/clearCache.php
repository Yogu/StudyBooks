<?php
	defined('IN_APP') or die; 
	$data->title = 'Clear Cache';
?>

<p>Welche gecachten Inhalte möchten Sie löschen?</p>

<form action="<?php echo htmlspecialchars($this->request->url); ?>" method="post">
	<input type="submit" name="deleteThumbnails" value="Miniaturbilder löschen" />
	<input type="submit" name="deleteDisplayImages" value="Anzeigebilder löschen" />
</form>
