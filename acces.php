<?php
// Page d'accueil publique du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html
//-------------------------------------------------------------------
if (file_exists('tools/_COMMUN_env.inc.php')) {
  $EA_Appel_dOu = '';
} else {
  $EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

$root = "";
$path = "";

$xcomm = $xpatr = $page = "";
pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$userlevel = logonok(1);

open_page(SITENAME . " : Dépouillement d'actes de l'état-civil et des registres paroissiaux", $root, null, null, null, '../index.htm', 'rss.php');
navigation($root, 2, 'A', "Conditions d'accès");

zone_menu(0, 0);

echo '<div id="col_main">';
echo "<h2>Conditions d'accès aux détails des données</h2>";

if (is_file("_config/acces.htm")) {
  include("_config/acces.htm");
} else {
  include("_config/commentaire.htm");
}

echo '<p>&nbsp;</p>';

echo '</div>';
close_page(1, $root);
