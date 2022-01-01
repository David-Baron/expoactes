<?php
$TIPlevel = 1;
include("function.php");
include("adlcutils.php");
include("actutils.php");
include("loginutils.php");

$root = "";
$path = "";
$sp = "&nbsp; &nbsp; ";
$error = 0;

$xcomm = $xpatr = $page = "";
pathroot($root, $path, $xcomm, $xpatr, $page);

$xid = $_REQUEST['xid'];
$ctrlcod = $_REQUEST['xct'];

$userlogin = "";
if (ADM == 10)
	$lvl = 5;
else
	$lvl = 4;
$userlevel = logonok($lvl);
while ($userlevel < $lvl) {
	login($root);
}
$request = "SELECT * FROM " . EA_DB . "_mar3 WHERE ID = " . $xid;
optimize($request);

if ($result = EA_sql_query($request) and EA_sql_num_rows($result) != 0) {
	$row = EA_sql_fetch_array($result);
} else {
	$error = 1;
}

if (($error == 0) and !($ctrlcod == ctrlxid($row["NOM"], $row["PRE"]))) {
	$error = 1;
	$title = "Erreur";
} else
	$title = "Mariage : " . $row["NOM"] . " " . $row["PRE"] . " x " . $row["C_NOM"] . " " . $row["C_PRE"];
$avertissement = "";
if ($error == 0) {
	$xcomm = $row['COMMUNE'] . ' [' . $row['DEPART'] . ']';
	if (solde_ok(1, $row["DEPOSANT"], 'M', $xid) > 0) {
		open_page($title, $root);
		navigation($root, ADM + 4, 'M', $xcomm, $row["NOM"], $row["PRE"]);
		zone_menu(ADM, $userlevel);
		echo '<div id="col_main">' . "\n";
		echo '<h2>Acte de mariage</h2>';
		echo '<table summary="Fiche détaillée">';
		show_item3($row, 0, 5, 2003, mkurl('tab_mari.php', $xcomm));  // Commune
		show_item3($row, 1, 0, 2002);  // Code INSEE
		show_item3($row, 0, 4, 2005);  // Departement
		show_item3($row, 1, 0, 2004);  // Code Departement
		show_item3($row, 1, 4, 2007);  // date de l'acte
		show_grouptitle3($row, 0, 5, 'M', 'D1'); // Epoux
		show_item3($row, 1, 4, 2011, mkurl('tab_mari.php', $xcomm, $row["NOM"]), 2012); // Nom et prénom de l'époux
		show_item3($row, 1, 0, 2013);  // Origine
		show_item3($row, 1, 0, 2014);  // Date naiss
		show_item3($row, 1, 0, 2015);  // Age
		show_item3($row, 1, 0, 2016);  // Commentaire
		show_item3($row, 1, 0, 2017);  // profession
		show_item3($row, 1, 0, 2018, '', 2019);  // veuf de 
		show_item3($row, 2, 0, 2020); // commentaire
		show_grouptitle3($row, 1, 5, 'M', 'D2');  // Parents
		show_item3($row, 2, 0, 2021, '', 2022);  // Père
		show_item3($row, 3, 0, 2024);  // Profession
		show_item3($row, 3, 0, 2023);  // Commentaire
		show_item3($row, 2, 0, 2025, '', 2026);  // Mère
		show_item3($row, 3, 0, 2028);  // Profession
		show_item3($row, 3, 0, 2027);  // Commentaire
		show_grouptitle3($row, 0, 5, 'M', 'F1');  // Epouse
		show_item3($row, 1, 4, 2029, mkurl('tab_mari.php', $xcomm, $row["C_NOM"]), 2030); // Nom et prénom de l'épouse
		show_item3($row, 1, 0, 2031);  // Origine
		show_item3($row, 1, 0, 2032);  // Date naiss
		show_item3($row, 1, 0, 2033);  // Age
		show_item3($row, 1, 0, 2034);  // Commentaire
		show_item3($row, 1, 0, 2035);  // profession
		show_item3($row, 1, 0, 2036, '', 2037);  // veuve de 
		show_item3($row, 2, 0, 2038); // commentaire
		show_grouptitle3($row, 1, 5, 'M', 'F2');  // Parents
		show_item3($row, 2, 0, 2039, '', 2040);  // Père
		show_item3($row, 3, 0, 2042);  // Profession
		show_item3($row, 3, 0, 2041);  // Commentaire
		show_item3($row, 2, 0, 2043, '', 2044);  // Mère
		show_item3($row, 3, 0, 2046);  // Profession
		show_item3($row, 3, 0, 2045);  // Commentaire
		show_grouptitle3($row, 0, 5, 'M', 'T1');  // Témoins
		show_item3($row, 0, 0, 2047, '', 2048);  // témoin 1
		show_item3($row, 1, 0, 2049);
		show_item3($row, 0, 0, 2050, '', 2051);  // témoin 2
		show_item3($row, 1, 0, 2052);
		show_item3($row, 0, 0, 2053, '', 2054);  // témoin 3
		show_item3($row, 1, 0, 2055);
		show_item3($row, 0, 0, 2056, '', 2057);  // témoin 4
		show_item3($row, 1, 0, 2058);
		show_grouptitle3($row, 0, 5, 'M', 'V1');  // Références
		show_item3($row, 0, 0, 2059, "", "", "1");  // Autres infos + Links , 
		show_item3($row, 0, 0, 2009, "", "", "1");  // Cote 
		show_item3($row, 0, 0, 2010, "", "", "1");  // Libre (images)
		show_item3($row, 0, 0, 2069, "", "", "2");  // Photos (links ;)
		show_grouptitle3($row, 0, 5, 'M', 'W1');  // Crédits
		show_item3($row, 0, 2, 2064);  // Photographe
		show_item3($row, 0, 2, 2065);  // Releveur
		show_item3($row, 0, 2, 2066);  // Vérificateur
		show_deposant3($row, 0, 2, 2063, $xid, "M"); // Deposant (+corrections)
		show_grouptitle3($row, 0, 5, 'M', 'X0');  // Gestion
		show_item3($row, 0, 2, 2061);  // Date interne
		show_item3($row, 0, 2, 2067);  // DtDepot
		if ($row["DTDEPOT"] <> $row["DTMODIF"]) {
			show_item3($row, 0, 2, 2068);  // Date modif
		}
		if (ADM <> 10)
			show_signal_erreur('M', $xid, $ctrlcod);

		echo '</table>';
		if ($avertissement <> "")
			echo '<p><b>' . $avertissement . '</b></p>';
	} else {
		open_page($title, $root);
		msg($avertissement);
	}
} else {
	open_page($title, $root);
	msg('Identifiant incorrect');
}
echo '</div>';
close_page();
