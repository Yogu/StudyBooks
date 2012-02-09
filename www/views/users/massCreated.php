<?php
defined('IN_APP') or die;
$data->title = "Viele Benutzer wurden erstellt";
?>

<p>
	<?php if (count($data->successful)) : ?>
		<b><?php echo count($data->successful) . '</b> Benutzer ' . (count($data->successful) == 1 ? 'wurde' : 'wurden'); ?> erfolgreich erstellt und die E-Mails wurden versendet.
	<?php elseif (count($data->failed)) : ?>
		 <?php echo (count($data->failed) == 1 ? 'Der Benutzer wurde' : 'Die ' . count($data->failed) . ' Benutzer wurden'); ?> erstellt, das Senden der E-Mail mit dem Passwort ist aber<?php if (count($data->failed) > 1) echo ' bei allen' ?> fehlgeschlagen.
	<?php elseif (count($data->successful) && count($data->failed)) : ?>
		<strong>Bei <?php echo count($data->failed); ?> Benutzern ist das Senden der E-Mail-Adresse fehlgeschlagen!</strong> Nachfolgend sind die erstellten Benutzer aufgelistet.
	<?php endif; ?>
</p>

<?php if (count($data->failed)) : ?>
	<h3>E-Mail-Versand fehlgeschlagen</h3>
	<?php
		$data->userList = $data->failed;
		$view->renderSubview('_massCreatedUserList');
	?>
<?php endif; ?>

<?php if (count($data->successful)) : ?>
	<h3>Erfolgreich erstellte Benutzer</h3>
	<?php
		$data->userList = $data->successful;
		$view->renderSubview('_massCreatedUserList');
	?>
<?php endif; ?>