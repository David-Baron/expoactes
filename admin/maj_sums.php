<?php
// Mise a jour des sommes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";

//**************************** ADMIN **************************
//{ print '<pre>';  print_r($_POST); echo '</pre>'; }

$xtyp = "";
pathroot($root, $path, $xtyp, $xpatr, $page);

$userlogin = "";
$needlevel = 6;  // niveau d'accès (anciennement 5)
$userlevel = logonok($needlevel);
while ($userlevel < $needlevel) {
	login($root);
}

$xtyp = strtoupper(getparam('xtyp'));
$mode = strtoupper(getparam('mode'));
$com  = urldecode(getparam('com'));

open_page("Mise à jour des statistiques", $root);
$missingargs = true;
$emailfound = false;
$oktype = false;
$cptact = 0;
$cptfil = 0;
navadmin($root, "Mise à jour des statistiques");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);


$menu_actes = "";
$menu_actes .= '<a href="' . $root . '/admin/' . "maj_sums.php" . '?xtyp=N&amp;mode=A&amp;com=">' . "Naissances" . "</a> | ";
$menu_actes .= '<a href="' . $root . '/admin/' . "maj_sums.php" . '?xtyp=M&amp;mode=A&amp;com=">' . "Mariages" . "</a> | ";
$menu_actes .= '<a href="' . $root . '/admin/' . "maj_sums.php" . '?xtyp=D&amp;mode=A&amp;com=">' . "Décès" . "</a> | ";
$menu_actes .= '<a href="' . $root . '/admin/' . "maj_sums.php" . '?xtyp=V&amp;mode=A&amp;com=">' . "Divers" . '</a>';
echo '</div>';

echo '<div id="col_main">';

menu_datas('S');

echo '<h2 align="center">Mise à jour des statistiques</h2>';

echo '<p><b>' . $menu_actes . '</b></p>';


if ($xtyp == "") {
	$request = "select TYPACT, max(DER_MAJ) as DERMAJ, count(COMMUNE) as CPTCOM"
		. " from " . EA_DB . "_sums"
		. " group by TYPACT"
		. " order by INSTR('NMDV',TYPACT)"     // cette ligne permet de trier dans l'ordre voulu
	;

	// echo $request;
	if ($result = EA_sql_query($request)) {
		while ($ligne = EA_sql_fetch_array($result)) {
			echo '<p><b>' . typact_txt($ligne['TYPACT']) . '</b> : ' . $ligne['CPTCOM'] . ' localités mises-à-jour le ' . $ligne['DERMAJ'] . '</p>';
		}
	}
	echo "<p><b>Utilisez les liens ci-dessus pour recalculer les statistiques d'un type d'actes</b></p>";
	$res = EA_sql_query("SHOW TABLES LIKE '" . EA_DB . "_user';");
	if (EA_sql_num_rows($res) > 0)
		echo '<h3><br /><a href="../install/supprdataV2.php">Cliquez ici</a> pour supprimer les tables de données devenues inutiles en version 3</h3>';
} else {
	maj_stats($xtyp, $T0, $path, $mode, $com);
}

echo '</div>';

close_page(1, $root);
