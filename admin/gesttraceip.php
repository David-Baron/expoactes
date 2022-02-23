<?php
$bypassTIP = 1; // pas de tracing ici

if (file_exists('tools/_COMMUN_env.inc.php')) {
  $EA_Appel_dOu = '';
} else {
  $EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
my_ob_start_affichage_continu();

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
