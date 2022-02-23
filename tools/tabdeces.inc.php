<?php
include("function.php");
include("adlcutils.php");
include("actutils.php");
include("loginutils.php");

$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = 1;
$program = "tab_deces.php";

pathroot($root, $path, $xcomm, $xpatr, $page);

$comdep  = html_entity_decode($xcomm, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);
$xord  = getparam('xord');
if ($xord == "") {
	$xord = "D";
}   // N = Nom, D = dates
$pg = getparam('pg');
if ($pg <> "") $page = $pg;
$xannee = "";
if (mb_substr($xpatr, 0, 1) == "!") {
	$xannee = mb_substr($xpatr, 1);
}
$userlogin = "";
$gid = 0;
$note = geoNote($Commune, $Depart, 'D');

if ($xpatr == "" or mb_substr($xpatr, 0, 1) == "_")
// Lister les patronymes avec groupements si trop nombreux
{
	$userlevel = logonok(2);
	while ($userlevel < 2) {
		login($root);
	}
	open_page($xcomm . " : " . $admtxt . "Décès/sépultures", $root);
	navigation($root, ADM + 2, 'D', $xcomm);
	zone_menu(ADM, $userlevel);
	echo '<div id="col_main">' . "\n";
	liste_patro_1($program, $path, $xcomm, $xpatr, "Décès / sépultures", EA_DB . "_dec3", $gid, $note);
} else {
	$userlevel = logonok(3);
	while ($userlevel < 3) {
		login($root);
	}
	$userid = current_user("ID");
	open_page($xcomm . " : " . $admtxt . "Table des décès/sépultures", $root);
	navigation($root, ADM + 3, 'D', $xcomm, $xpatr);
	zone_menu(ADM, $userlevel);
	echo '<div id="col_main">' . "\n";
	// Lister les actes
	echo '<h2>Actes de décès/sépulture</h2>';

	echo '<p>';
	echo 'Commune/Paroisse : <a href="' . mkurl($path . '/' . $program, $xcomm) . '"><b>' . $xcomm . '</b></a>' . geoUrl($gid) . '<br />';
	if ($note <> '')
		echo "</p><p>" . $note . "</p><p>";
	if (mb_substr($xpatr, 0, 1) == "!") {
		echo 'Année : <b>' . $xannee . '</b>';
		$preorder = "act.NOM";
		$nameorder = "Patronymes";
	} else {
		echo 'Patronyme : <b>' . $xpatr . '</b>';
		$preorder = "PRE";
		$nameorder = "Prénoms";
	}
	echo '</p>';

	$baselink = $path . '/' . $program . '/' . urlencode($xcomm) . '/' . urlencode($xpatr);
	if ($xord == "N") {
		$order = $preorder . ", LADATE";
		$hdate = '<a href="' . mkurl($path . '/' . $program, $xcomm, $xpatr, 'xord=D') . '">Dates</a>';
		$baselink = mkurl($path . '/' . $program, $xcomm, $xpatr, 'xord=N');
		$hnoms = '<b>' . $nameorder . '</b>';
	} else {
		$order = "LADATE, " . $preorder;
		$hnoms = '<a href="' . mkurl($path . '/' . $program, $xcomm, $xpatr, 'xord=N') . '">' . $nameorder . '</a>';
		$baselink = mkurl($path . '/' . $program, $xcomm, $xpatr, 'xord=D');
		$hdate = '<b>Dates</b>';
	}
	if ($xannee <> "")
		$condit = " and year(act.LADATE)=" . $xannee;
	else
		$condit = " and act.NOM = '" . sql_quote($xpatr) . "'";

	if ($Depart <> "")
		$condDep = " and DEPART = '" . sql_quote($Depart) . "'";
	else
		$condDep = "";

	//	$request = "select act.NOM, act.PRE, DATETXT, act.ID, P_NOM, dep.NOM, dep.PRENOM, LOGIN, dep.ID, ORI, T1_NOM, COM, COTE"
	$request = "select act.NOM, act.PRE, DATETXT, act.ID, act.DEPOSANT"
		. " from " . EA_DB . "_dec3 as act"
		. " where COMMUNE = '" . sql_quote($Commune) . "'" . $condDep
		. $condit . " order by " . $order;

	optimize($request);
	$result = mysql_query($request);
	$nbtot = mysql_num_rows($result);

	$limit = "";
	$listpages = "";
	pagination($nbtot, $page, $baselink, $listpages, $limit);

	if ($limit <> "") {
		$request = $request . $limit;
		$result = mysql_query($request);
		$nb = mysql_num_rows($result);
	} else {
		$nb = $nbtot;
	}

	if ($nb > 0) {
		if ($listpages <> "")
			echo '<p>' . $listpages . '</p>';
		$i = 1 + ($page - 1) * iif((ADM > 0), MAX_PAGE_ADM, MAX_PAGE);
		echo '<table summary="Liste des patronymes">';
		echo '<tr class="rowheader">';
		echo '<th> Tri : </th>';
		echo '<th>' . $hdate . '</th>';
		echo '<th>' . $hnoms . '</th>';
		if (ADM == 10) echo '<th>Déposant</th>';
		echo '</tr>';

		while ($ligne = mysql_fetch_row($result)) {
			echo '<tr class="row' . (fmod($i, 2)) . '">';
			echo '<td>' . $i . '. </td>';
			echo '<td>&nbsp;' . annee_seulement($ligne[2]) . '&nbsp;</td>';
			echo '<td>&nbsp;<a href="' . $path . '/acte_deces.php?xid=' . $ligne[3] . '&xct=' . ctrlxid($ligne[0], $ligne[1]) . '">' . $ligne[0] . ' ' . $ligne[1] . '</a></td>';
			if (ADM == 10) {
				actions_deposant($userid, $ligne[4], $ligne[3], 'D');
			}
			echo '</tr>';
			$i++;
		}
		echo '</table>';
		if ($listpages <> "")
			echo '<p>' . $listpages . '</p>';
		show_solde();
	} else {
		msg('Aucun acte trouvé');
	}
}

echo '</div>';
close_page();
