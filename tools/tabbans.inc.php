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
$program = "tab_bans.php";

pathroot($root, $path, $xcomm, $xpatr, $page);

$xord  = getparam('xord');
if ($xord == "") {
	$xord = "D";
}   // N = Nom, D = dates, F = Femme
$pg = getparam('pg');
if ($pg <> "") $page = $pg;
$xannee = "";
if (mb_substr($xpatr, 0, 1) == "!") {
	$xannee = mb_substr($xpatr, 1);
}
$p = isin($xcomm, ";");
if ($p > 0) {
	$stype = mb_substr($xcomm, $p + 1);
	$xcomm = mb_substr($xcomm, 0, $p);
	$stitre = " (" . $stype . ")";
	$soustype = " and LIBELLE = '" . sql_quote($stype) . "'";
	$sousurl  = ";" . $stype;
} else {
	$stype = "";
	$stitre = "";
	$soustype = "";
	$sousurl  = "";
}

$comdep  = html_entity_decode($xcomm, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);

$userlogin = "";
$gid = 0;
$note = geoNote($Commune, $Depart, 'V');

if ($xpatr == "" or mb_substr($xpatr, 0, 1) == "_")
// Lister les patronymes avec groupements si trop nombreux
{
	$userlevel = logonok(2);
	while ($userlevel < 2) {
		login($root);
	}
	open_page($xcomm . " : " . $admtxt . "Divers" . $stitre, $root);
	navigation($root, ADM + 2, 'V', $xcomm);
	zone_menu(ADM, $userlevel);
	echo '<div id="col_main">' . "\n";
	liste_patro_2($program, $path, $xcomm, $xpatr, "Divers $stitre", EA_DB . "_div3", $stype, $gid, $note);
} else {
	// **** Lister les actes
	$userlevel = logonok(3);
	while ($userlevel < 3) {
		login($root);
	}
	$userid = current_user("ID");
	open_page($xcomm . " : " . $admtxt . "Divers" . $stitre, $root);
	navigation($root, ADM + 3, 'V', $xcomm, $xpatr);
	zone_menu(ADM, $userlevel);
	echo '<div id="col_main">' . "\n";
	echo '<h2>Divers' . $stitre . '</h2>';

	echo '<p>';
	echo 'Commune/Paroisse : <a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl) . '"><b>' . $xcomm . '</b></a>' . geoUrl($gid) . '<br />';
	if ($note <> '')
		echo "</p><p>" . $note . "</p><p>";
	if (mb_substr($xpatr, 0, 1) == "!") {
		echo 'Année : <b>' . $xannee . '</b>';
	} else {
		echo 'Patronyme : <b>' . $xpatr . '</b>';
	}
	echo '</p>';

	$baselink = $path . '/' . $program . '/' . urlencode($xcomm) . '/' . urlencode($xpatr);
	if ($xord == "N") {
		$order = "act.NOM, PRE, LADATE, LIBELLE";
		$hdate = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=D') . '">Dates</a>';
		$hnoms = '<b>Intervenant 1</b>';
		$hfemm = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=F') . '">Intervenant 2</a>';
		$htype = '<b>Document</b>';
		$baselink = mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=N');
	} elseif ($xord == "F") {
		$order = "C_NOM, C_PRE, LADATE, LIBELLE";
		$hnoms = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=N') . '">Intervenant 1</a>';
		$hdate = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=D') . '">Dates</a>';
		$hfemm = '<b>Intervenant 2</b>';
		$htype = '<b>Document</b>';
		$baselink = mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=F');
	} else {
		$order = "LADATE, act.NOM, C_NOM, LIBELLE";
		$hnoms = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=N') . '">Intervenant 1</a>';
		$hdate = '<b>Dates</b>';
		$hfemm = '<a href="' . mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=F') . '">Intervenant 2</a>';
		$htype = '<b>Document</b>';
		$baselink = mkurl($path . '/' . $program, $xcomm . $sousurl, $xpatr, 'xord=D');
	}
	if ($xannee <> "")
		$condit = " and year(act.LADATE)=" . $xannee;
	else
		$condit = " and (act.NOM  = '" . sql_quote($xpatr) . "' or C_NOM  = '" . sql_quote($xpatr) . "')";

	if ($Depart <> "")
		$condDep = " and DEPART = '" . sql_quote($Depart) . "'";
	else
		$condDep = "";

	$request = "select act.NOM, act.PRE, C_NOM, C_PRE, DATETXT, act.ID, act.LIBELLE, act.DEPOSANT"
		. " from " . EA_DB . "_div3 as act"
		. " where COMMUNE = '" . sql_quote($Commune) . "'" . $condDep
		. " " . $soustype . $condit
		. " order by " . $order;

	//echo $request;
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
		echo '<th>' . $hfemm . '</th>';
		echo '<th>' . $htype . '</th>';
		echo '<th>&nbsp;</th>';
		if (ADM == 10) echo '<th>Déposant</th>';
		echo '</tr>' . "\n";

		$xpatr = remove_accent($xpatr);
		while ($ligne = mysql_fetch_row($result)) {
			echo '<tr class="row' . (fmod($i, 2)) . '">' . "\n";
			echo '<td>' . $i . '. </td>' . "\n";
			echo '<td>&nbsp;' . annee_seulement($ligne[4]) . '&nbsp;</td>' . "\n";
			if (remove_accent($ligne[0]) == $xpatr) {
				echo '<td>&nbsp;<b>' . $ligne[0] . ' ' . $ligne[1] . '</b></td>' . "\n";
			} else {
				echo '<td>&nbsp;' . $ligne[0] . ' ' . $ligne[1] . '</td>' . "\n";
			}
			if (remove_accent($ligne[2]) == $xpatr) {
				echo '<td>&nbsp;<b>' . $ligne[2] . ' ' . $ligne[3] . '</b></td>' . "\n";
			} else {
				echo '<td>&nbsp;' . $ligne[2] . ' ' . $ligne[3] . '</td>' . "\n";
			}
			echo '<td>&nbsp;' . $ligne[6] . '</td>';
			echo '<td>&nbsp;<a href="' . $path . '/acte_bans.php?xid=' . $ligne[5] . '&xct=' . ctrlxid($ligne[0], $ligne[1]) . '">' . "Détails" . '</a>&nbsp;</td>' . "\n";
			if (ADM == 10) {
				actions_deposant($userid, $ligne[7], $ligne[5], 'V');
			}
			echo '</tr>' . "\n";
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
