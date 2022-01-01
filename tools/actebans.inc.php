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

$request = "SELECT * FROM " . EA_DB . "_div3 WHERE ID = " . $xid;

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
	$title = $row["LIBELLE"] . " : " . $row["NOM"] . " " . $row["PRE"];
$avertissement = "";
if ($error == 0) {
	$xcomm = $row['COMMUNE'] . ' [' . $row['DEPART'] . ']';
	if (solde_ok(1, $row["DEPOSANT"], 'V', $xid) > 0) {
		open_page($title, $root);
		navigation($root, ADM + 4, 'V', $xcomm, $row["NOM"], $row["PRE"]);
		zone_menu(ADM, $userlevel);
		echo '<div id="col_main">' . "\n";
		$sigle = $row["SIGLE"];
		echo '<h2>' . $row["LIBELLE"] . '</h2>';
		echo '<table summary="Fiche détaillée">';
		show_item3($row, 0, 5, 4003, mkurl('tab_bans.php', $xcomm));  // Commune
		show_item3($row, 1, 0, 4002);  // Code INSEE
		show_item3($row, 0, 4, 4005);  // Departement
		show_item3($row, 1, 0, 4004);  // Code Departement
		show_item3($row, 1, 4, 4007);  // date de l'acte
		show_grouptitle3($row, 0, 5, 'V', 'D1', $sigle); // Intervenant 1		
		show_item3($row, 1, 4, 4013, mkurl('tab_bans.php', $xcomm, $row["NOM"]), 4014); // Intervenant 1
		show_item3($row, 1, 0, 4015);  // Sexe
		show_item3($row, 1, 0, 4016);  // Origine
		show_item3($row, 1, 0, 4017);  // Date naiss
		show_item3($row, 1, 0, 4018);  // Age
		show_item3($row, 1, 0, 4019);  // Commentaire
		show_item3($row, 1, 0, 4020);  // profession
		show_item3($row, 1, 0, 4021, '', 4022);  // veuf de 
		show_item3($row, 2, 0, 4023); // commentaire
		show_grouptitle3($row, 1, 5, 'V', 'D2', $sigle); // Parents		
		show_item3($row, 2, 0, 4024, '', 4025);  // Père
		show_item3($row, 3, 0, 4027);  // Profession
		show_item3($row, 3, 0, 4026);  // Commentaire
		show_item3($row, 2, 0, 4028, '', 4029);  // Mère
		show_item3($row, 3, 0, 4031);  // Profession
		show_item3($row, 3, 0, 4030);  // Commentaire
		if (trim($row["C_NOM"]) != "") {
			show_grouptitle3($row, 0, 5, 'V', 'F1', $sigle); // Intervenant 2		
			show_item3($row, 1, 4, 4032, mkurl('tab_bans.php', $xcomm, $row["C_NOM"]), 4033); // Intervenant 2
			show_item3($row, 1, 0, 4034);  // Sexe
			show_item3($row, 1, 0, 4035);  // Origine
			show_item3($row, 1, 0, 4036);  // Date naiss
			show_item3($row, 1, 0, 4037);  // Age
			show_item3($row, 1, 0, 4038);  // Commentaire
			show_item3($row, 1, 0, 4039);  // profession
			show_item3($row, 1, 0, 4040, '', 4041);  // veuve de 
			show_item3($row, 2, 0, 4042); // commentaire
			show_grouptitle3($row, 1, 5, 'V', 'F2', $sigle); // Parents		
			show_item3($row, 2, 0, 4043, '', 4044);  // Père
			show_item3($row, 3, 0, 4046);  // Profession
			show_item3($row, 3, 0, 4045);  // Commentaire
			show_item3($row, 2, 0, 4047, '', 4048);  // Mère
			show_item3($row, 3, 0, 4050);  // Profession
			show_item3($row, 3, 0, 4049);  // Commentaire
		}
		show_grouptitle3($row, 0, 5, 'V', 'T1', $sigle);  // Témoins
		show_item3($row, 0, 0, 4051, '', 4052);  // témoin 1
		show_item3($row, 1, 0, 4053);
		show_item3($row, 0, 0, 4054, '', 4055);  // témoin 2
		show_item3($row, 1, 0, 4056);
		show_item3($row, 0, 0, 4057, '', 4058);  // témoin 3
		show_item3($row, 1, 0, 4059);
		show_item3($row, 0, 0, 4060, '', 4061);  // témoin 4
		show_item3($row, 1, 0, 4062);
		show_grouptitle3($row, 0, 5, 'V', 'V1');  // Références
		show_item3($row, 0, 0, 4063, "", "", "1");  // Autres infos + Links
		show_item3($row, 0, 0, 4009, "", "", "1");  // Cote 
		show_item3($row, 0, 0, 4010, "", "", "1");  // Libre (images)
		show_item3($row, 0, 0, 4073, "", "", "2");  // Photos (links)
		show_grouptitle3($row, 0, 5, 'V', 'W1');  // Crédits
		show_item3($row, 0, 2, 4068);  // Photographe
		show_item3($row, 0, 2, 4069);  // Releveur
		show_item3($row, 0, 2, 4070);  // Vérificateur
		show_deposant3($row, 0, 2, 4067, $xid, "V"); // Deposant (+corrections)
		show_grouptitle3($row, 0, 5, 'V', 'X0');  // Gestion
		show_item3($row, 0, 2, 4065);  // Date interne
		show_item3($row, 0, 2, 4071);  // DtDepot
		if ($row["DTDEPOT"] <> $row["DTMODIF"]) {
			show_item3($row, 0, 2, 4072);  // Date modif
		}
		if (ADM <> 10)
			show_signal_erreur('V', $xid, $ctrlcod);

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
