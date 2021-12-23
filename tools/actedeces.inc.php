<?php
$TIPlevel = 1;
include("function.php");
include("adlcutils.php");
include("actutils.php");
include("loginutils.php");

$root = "";
$path = "";
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

$request = "SELECT * FROM " . EA_DB . "_dec3 WHERE ID = " . $xid;
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
	$title = "Décès : " . $row["NOM"] . " " . $row["PRE"];
$avertissement = "";
if ($error == 0) {
	$xcomm = $row['COMMUNE'] . ' [' . $row['DEPART'] . ']';
	if (solde_ok(1, $row["DEPOSANT"], 'D', $xid) > 0) {
		open_page($title, $root);
		navigation($root, ADM + 4, 'D', $xcomm, $row["NOM"], $row["PRE"]);
		zone_menu(ADM, $userlevel);
		echo '<div id="col_main">' . "\n";

		// Afficher l acte
		echo '<h2>Acte de décès/sépulture</h2>';

		echo '<table summary="Fiche détaillée">';

		show_item3($row, 0, 5, 3003, mkurl('tab_deces.php', $xcomm));  // Commune
		show_item3($row, 1, 0, 3002);  // Code INSEE
		show_item3($row, 0, 4, 3005);  // Departement
		show_item3($row, 1, 0, 3004);  // Code Departement

		show_grouptitle3($row, 0, 5, 'D', 'D1'); // Décédé
		show_item3($row, 1, 4, 3011, mkurl('tab_deces.php', $xcomm, $row["NOM"]), 3012); // Nom et prénom é
		show_item3($row, 1, 4, 3007);  // date de l'acte

		show_item3($row, 1, 0, 3013); // origine
		show_item3($row, 1, 0, 3014); // date de naissance 
		show_item3($row, 1, 0, 3015); // sexe
		show_item3($row, 1, 0, 3016); // age
		show_item3($row, 1, 0, 3017); // commentaire 
		show_item3($row, 1, 0, 3018); // profession 

		show_grouptitle3($row, 1, 5, 'D', 'D2'); // Parents
		show_item3($row, 2, 0, 3019, '', 3020);  // Père
		show_item3($row, 3, 0, 3022);  // Profession
		show_item3($row, 3, 0, 3021); // Commentaire

		show_item3($row, 2, 0, 3023, '', 3024); // Mère
		show_item3($row, 3, 0, 3026);  // Profession
		show_item3($row, 3, 0, 3025);  // Commentaire

		show_grouptitle3($row, 0, 5, 'D', 'F1'); // Conjoint
		show_item3($row, 1, 0, 3027, '', 3028);  // conjoint
		show_item3($row, 1, 0, 3030);  // Profession
		show_item3($row, 1, 0, 3029); // Commentaire

		show_grouptitle3($row, 0, 5, 'D', 'T1'); // Témoins
		show_item3($row, 0, 0, 3031, '', 3032);  // Témoin 1
		show_item3($row, 1, 0, 3033);  // Commentaire
		show_item3($row, 0, 0, 3034, '', 3035);  // Témoin 2
		show_item3($row, 1, 0, 3036);  // Commentaire

		show_grouptitle3($row, 0, 5, 'D', 'V1');  // Références
		show_item3($row, 0, 0, 3037, "", "", "1");  // Autres infos + Links
		show_item3($row, 0, 0, 3009, "", "", "1");  // Cote 
		show_item3($row, 0, 0, 3010, "", "", "1");  // Libre (images)
		show_item3($row, 0, 0, 3047, "", "", "2");  // Photos (links)

		show_grouptitle3($row, 0, 5, 'D', 'W1');  // Crédits
		show_item3($row, 0, 2, 3042);  // Photographe
		show_item3($row, 0, 2, 3043);  // Releveur
		show_item3($row, 0, 2, 3044);  // Vérificateur
		show_deposant3($row, 0, 2, 3041, $xid, "D"); // Deposant (+corrections)

		show_grouptitle3($row, 0, 5, 'D', 'X0');  // Gestion
		show_item3($row, 0, 2, 3039);  // Date interne
		show_item3($row, 0, 2, 3045);  // DtDepot
		if ($row["DTDEPOT"] <> $row["DTMODIF"]) {
			show_item3($row, 0, 2, 3046);  // Date modif
		}

		if (ADM <> 10)
			show_signal_erreur('D', $xid, $ctrlcod);

		echo '</table>';
		if ($avertissement <> "")
			echo '<p><b>' . $avertissement . '</b></p>' . "\n";
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
