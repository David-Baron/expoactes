<?php
ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);

if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

function paspoint($string)
{
	$x = strpos($string, ":");
	if ($x > 0)
		return mb_substr($string, $x + 1);
	else
		return "";
}

$root = "";
$path = "";

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

open_page("Paramètres serveur", $root);
navadmin($root, "Paramètres serveur");

$action = getparam('maint');
if ($action <> "") {
	if ($action == "SET") {
		$request = "update " . EA_DB . "_params set valeur = '1' where param = 'EA_MAINTENANCE'";
		$result = EA_sql_query($request);
	}
	if ($action == "UNSET") {
		$request = "update " . EA_DB . "_params set valeur = '0' where param = 'EA_MAINTENANCE'";
		$result = EA_sql_query($request);
	}
}
echo '<div id="col_menu">';
form_recherche();
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

menu_software('E');

$request = "select valeur from " . EA_DB . "_params where param = 'EA_MAINTENANCE'";
$result = EA_sql_query($request);
$row = EA_sql_fetch_array($result);
if ($row[0] == 1) {
	echo '<p><font color="#FF0000"><b>Mode MAINTENANCE : l\'accès limité aux administrateurs.</b></font></p>';
	echo '<p><a href="?maint=UNSET"><b>Basculer en mode NORMAL</b></a></p>';
} else {
	echo '<p><font color="#009900"><b>Mode NORMAL : Le site est ouvert à la consultation.</b></font></p>';
	echo '<p><a href="?maint=SET"><b>Basculer en mode MAINTENANCE</b></a></p>';
}

echo '<h2>Informations sur le serveur web (site)</h2>';

echo "<p>Version du serveur PHP : <b>" . phpversion() . "</b></p>";
echo "<p>Type du serveur : <b>" . php_uname() . "</b></p>";

echo '<h2>Informations sur le serveur MySQL (base de données)</h2>';

$db = con_db();  // avec affichage de l état de la connexion

echo "<p>Version du serveur MySQL : <b>" . EA_sql_get_server_info() . "</b></p>";

// paramètres du serveur MySQL
$status = explode('  ', EA_sql_stat($db));

echo '<h3>Etat du serveur</h3>';
echo "<p>Serveur MySQL en fonctionnement depuis : " . heureminsec(paspoint($status[0])) . "</p>";
echo "<p>Nombre moyen de requêtes par sec (tous clients confondus) :" . paspoint($status[7]) . "</p>";

/*
	$result = EA_sql_query('SHOW status');
  echo '<pre>'; 
	while ($row = EA_sql_fetch_assoc($result)) 
		{
	  echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
	  }
  echo '</pre>'; 
  */
echo '<h3>Paramètres du serveur</h3>';
echo "<p>Temps limite pour l'exécution des requêtes (sec) : " . val_var_mysql('wait_timeout') . "</p>";
echo "<p>Temps limite pour les lectures (sec) : " . val_var_mysql('net_read_timeout') . "</p>";
echo "<p>Temps limite pour les écritures (sec) : " . val_var_mysql('net_write_timeout') . "</p>";
$maxcon = val_var_mysql('max_user_connections');
if ($maxcon == 0)
	$maxcon = val_var_mysql('max_connections');
echo "<p>Nombre maximal de connexions simultannées globalement : " . val_var_mysql('max_connections') . "</p>";
echo "<p>Nombre maximal de connexions simultannées pour vous : " . $maxcon . "</p>";

/*
  $result = EA_sql_query('SHOW VARIABLES');
  echo '<pre>'; 
	while ($row = EA_sql_fetch_assoc($result)) 
		{
	  echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
	  }
  echo '</pre>'; 
	*/

if (file_exists('serv_params_accents.inc.php')) include('serv_params_accents.inc.php');

echo '<h2>Informations sur le géocodage (via Google Maps)</h2>';
test_geocodage(true);

echo '</div>';
close_page(1, $root);
