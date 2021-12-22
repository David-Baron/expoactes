<?php
ob_implicit_flush(1);
$bypassTIP = 1; // pas de tracing ici

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";
$userlogin = "";
$T0 = time();

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
  login($root);
}

open_page("Gestion du filtrage IP", $root);
navadmin($root, "Gestion du filtrage IP");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
$missingargs = true;
$emailfound = false;
$cptok = 0;
$cptko = 0;

menu_software('F');

admin_traceip();

echo '</div>';
close_page(1, $root);
