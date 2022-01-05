<?php

if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="CONTENT-TYPE" content="text/html; charset=utf-8" />
</head>

<body>
	<?php
	echo "<p>Date et heure du syst&egrave;me : " . now() . '</p>';
	echo "<p>Time : " . time() . '</p>';
	echo "<p>Microtime : " . microtime() . '</p>';

	phpinfo();
	if (isset($_REQUEST["debug"])) {
		echo '<h1>Test spécifiques</h1>';

		test_geocodage(true);

		echo "<h2>Création d'un fichier</h2>";
		$path = "../admin/_upload/";

		if (!is_dir($path))
			echo '<p>Répertoire "' . $path . '" inaccessible ou inexistant.';

		if (is__writable($path)) {
			echo "<p>Création de fichier OK dans " . $path;
			//echo "<br>Droits d'accès à ".$path." : ".mb_substr(sprintf('%o', fileperms($path)), -4);
		} else {
			echo "<p>IMPOSSIBLE de créer un fichier dans " . $path;
			echo "<br>Droits d'accès à " . $path . " : " . mb_substr(sprintf('%o', fileperms($path)), -4);
		}

		echo "<h2>Serveur MySQL</h2>";

		$db = con_db(1);  // avec affichage de l état de la connexion

		echo "<p>Version du serveur MySQL : <b>" . EA_sql_get_server_info() . "</b></p>";

		// paramètres du serveur MySQL
		$status = explode('  ', EA_sql_stat($db));
		echo '<pre>';
		print_r($status);
		echo '</pre>';
		echo '<h3>Variables du serveur</h3>';
		$result = EA_sql_query('SHOW VARIABLES', $db);
		echo '<pre>';
		while ($row = EA_sql_fetch_assoc($result)) {
			echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
		}
		echo '</pre>';
		echo '<h3>Statut du serveur</h3>';
		$result = EA_sql_query('SHOW status');
		echo '<pre>';
		while ($row = EA_sql_fetch_assoc($result)) {
			echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
		}
		echo '</pre>';
	} ?>
	
</body>

</html>