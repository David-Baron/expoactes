<?php
// Page d'accueil ADMIN du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2007
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html
//-------------------------------------------------------------------
//ob_start(); //Pour éviter de tout recevoir en un seul bloc
//ob_implicit_flush(1);

define("ADM", 1); // Mode admin;

if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

$root = "";
$path = "";

//**************************** ADMIN **************************
//{ print '<pre>';  print_r($_POST); echo '</pre>'; }

//$xtyp="A";
pathroot($root, $path, $xtyp, $xpatr, $page);

$init  = getparam('init');

$userlogin = "";
$needlevel = 6;  // niveau d'accès (anciennement 5)
$userlevel = logonok($needlevel);
while ($userlevel < $needlevel) {
	login($root);
}

if (!defined('EA_TYPE_SITE')) define('EA_TYPE_SITE', 'ACTES');
if (((SITE_INVENTAIRE !== '') and ((substr(SITE_INVENTAIRE, 0, 7) == 'http://') or  (substr(SITE_INVENTAIRE, 0, 8) == 'https://')))) {
	$t = check_new_version("EXPOACTES", SITE_INVENTAIRE, EA_TYPE_SITE);
	$t = explode('|', $t . '|l');
	$newvers = $t[0];
	$status_inv = $t[1];
} else {
	$newvers = EA_VERSION;
	$status_inv = 'l';
}

open_page("Administration des actes", $root);
$missingargs = true;
$emailfound = false;
$oktype = false;
$cptact = 0;
$cptfil = 0;
navadmin($root, '');

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);

if ($xtyp == "")
	$xtyp  = getparam('xtyp');
if ($xtyp == "")
	$xtyp = 'A';


echo '</div>';

echo '<div id="col_main">';

echo '<h1 align="center">Administration des actes &amp; tables</h1>';

//{ print '<pre>';  print_r($lines); echo '</pre>'; }

if ((!check_version(phpversion(), "5.1.0")) or (!check_version("8.0.999", phpversion()))) {
	echo '<p class="erreur">Vous utilisez ExpoActes sur une version de PHP ' . phpversion() . ' non validée.<br /></p>';
}
if (!check_version(EA_VERSION, $newvers)) {
	echo '<p class="erreur">La version ' . $newvers . ' du logiciel Expoactes est maintenant disponible <br />';
	echo 'et peut être téléchargée sur le site <a href="' . SITE_INVENTAIRE . '">' . SITE_INVENTAIRE . '</a><p>';
}

switch (substr($status_inv, 0, 1)) {
	case 'l': // site "localhost" RAS
		break;
	case '-': // site  Pas Actif&Publié
	case 'N': // site Pas dans l'inventaire
		echo '<p class="erreur">Votre site n\'est pas enregistr&eacute; dans l\'inventaire, vous pouvez demander &agrave; l\'inscrire (si minimum 2000 actes) : <br /> ';
		echo '<a href="' . SITE_INVENTAIRE . '">' . SITE_INVENTAIRE . '</a><p>';
		break;
	case 'i': // version inconnue : Information de la suppression de l'inventaire (ne devrait pas arriver)
		echo '<p class="erreur">La version ExpoActes de votre site n\'est pas reconnue, votre site sera supprimé de l\'inventaire<br /> ';
		echo '<a href="' . SITE_INVENTAIRE . '">' . SITE_INVENTAIRE . '</a><p>';
		break;
	default: // Oversion => Si la version locale est supérieure à celle du programme : Information de la suppression de l'inventaire car version non reconnue/incohérente (on a le cas de genealogie23 qui remonte v3.3.0 et en plus qui a géré 2 bases avec un paramètre ?base= sauf que ce n'est pas compatible rss.php car le chargement ajoute ?all=Y  donc 2 paramètres ? ce qui ne fonctionne pas !
		$t = mb_substr($status_inv, 1);
		if (($t != '') and (!check_version($newvers, EA_VERSION))) // local > newvers
		{
			echo '<p class="erreur">La version ExpoActes de votre site ' . EA_VERSION . ' est inconnue/incoh&eacute;rente n\'assurant pas la compatibilit&eacute;, votre site sera supprimé de l\'inventaire<br /> ';
			echo '<a href="' . SITE_INVENTAIRE . '">' . SITE_INVENTAIRE . '</a><p>';
		}
}

$chemin = "/admin/";
$menu_actes = "";
if ($xtyp == "N") $menu_actes .= "Naissances" . " | ";
else $menu_actes .= '<a href="' . mkurl($root . $chemin . "index.php", "N") . '">' . "Naissances" . "</a> | ";
if ($xtyp == "M") $menu_actes .= "Mariages" . " | ";
else $menu_actes .= '<a href="' . mkurl($root . $chemin . "index.php", "M") . '">' . "Mariages" . "</a> | ";
if ($xtyp == "D") $menu_actes .= "Décès" . " | ";
else $menu_actes .= '<a href="' . mkurl($root . $chemin . "index.php", "D") . '">' . "Décès" . "</a> | ";
if ($xtyp == "V") $menu_actes .= "Divers";
else $menu_actes .= '<a href="' . mkurl($root . $chemin . "index.php", "V") . '">' . "Divers" . "</a>";
if ($xtyp == "A") $menu_actes .= " | Tous";
else $menu_actes .= ' | <a href="' . mkurl($root . $chemin . "index.php", "A") . '">' . "Tous" . '</a>';

echo '<p><b>' . $menu_actes . '</b></p>';

// --- module principal
include("../tools/tableau_index.php");

// verification des statistiques
$request = "select sum(NB_TOT) as nb_sum from " . EA_DB . "_sums where TYPACT='N'";
$result = EA_sql_query($request);
$row = EA_sql_fetch_row($result);
$nb_sum = $row[0];
$request = "select count(*) as nb_cnt from " . EA_DB . "_nai3";
$result = EA_sql_query($request);
$row = EA_sql_fetch_row($result);
$nb_cnt = $row[0];
if ($nb_sum <> $nb_cnt and $nb_cnt > 0) {
	msg("Attention : les statistiques doivent être recalculées");
	echo '<p><a href="maj_sums.php"><b>Calcul des statistiques</b></a></p>';
}



echo '</div>';

close_page(1, $root);
