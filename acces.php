<?php
// Page d'accueil publique du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

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
