<?php
include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

//define ("OPTIMIZE",1);
$MT0 = microtime_float();
$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = 1;
define("ADM", 10); // *** Mode administration ***

pathroot($root, $path, $xcomm, $xpatr, $page);

$xord  = getparam('xord');
if ($xord == "") {
	$xord = "D";
}   // N = Nom
$page  = getparam('pg');
$xdel  = getparam('xdel');
$xfilter = getparam('xfilter');

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

open_page(SITENAME . " : Activité du site", $root);
//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

navadmin($root, "Activité du site");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
menu_software('J');

// Suppression des informations anciennes
if ($xdel > 31) {
	$request = "delete from " . EA_DB . "_log where datediff(curdate(),DATE)>" . $xdel;
	$result = EA_sql_query($request);
	$nb = EA_sql_affected_rows();
	echo $nb . " ligne(s) suprimée(s)."; // .$datedel;
}
echo '<p><a href="?xdel=365">' . "Supprimer les événements âgés de plus d'un an</a></p>";
// Lister les actions
echo '<h2>Activité sur les données du site ' . SITENAME . '</h2>';

echo '<center><form method="post" action="">' . "\n";
echo '<input type="text" name="xfilter" value="" />' . "\n";
echo '&nbsp; &nbsp;<input type="submit" value="FILTRER" /></td>' . "\n";
echo '</form></center>';

$baselink = $root . '/admin/listlog.php' . "?xfilter=" . $xfilter;
if ($xord == "N") {
	$order = "NOM, PRENOM, DATE desc";
	$hdate = '<a href="' . $baselink . '&xord=D">Date et heure</a>';
	$hcomm = '<a href="' . $baselink . '&xord=C">Commune/Paroisse</a>';
	$baselink = $baselink . '&xord=N';
	$hnoms = '<b>Utilisateur</b>';
} elseif ($xord == "D") {
	$order = "DATE desc";
	$hcomm = '<a href="' . $baselink . '&xord=C">Commune/Paroisse</a>';
	$hnoms = '<a href="' . $baselink . '&xord=N">Utilisateur</a>';
	$baselink = $baselink . '&xord=D';
	$hdate = '<b>Date et heure</b>';
} else {
	$order = "COMMUNE, DATE desc";
	$hdate = '<a href="' . $baselink . '&xord=D">Date et heure</a>';
	$hnoms = '<a href="' . $baselink . '&xord=N">Utilisateur</a>';
	$baselink = $baselink . '&xord=L';
	$hcomm = '<b>Commune/Paroisse</b>';
}
$baselink .= "&xfilter=" . $xfilter;

$request = "create temporary table temp_user3 (ID int(11), nom varchar(30), prenom varchar(30), PRIMARY KEY (ID))";
$result = EA_sql_query($request);

$request = "select ID,NOM,PRENOM from " . EA_UDB . "_user3";
$result = EA_sql_query($request, $u_db);
while ($ligne = EA_sql_fetch_row($result)) {
	$treq = "insert into temp_user3 values (" . $ligne[0] . ",'" . $ligne[1] . "','" . $ligne[2] . "')";
	$tres = EA_sql_query($treq);
	//echo "<br>".$treq;
}

$request = "select NOM, PRENOM, ID, DATE, ACTION, COMMUNE, NB_ACTES"
	. " from " . EA_DB . "_log left join temp_user3 on (temp_user3.id=" . EA_DB . "_log.user)";
if ($xfilter <> "")
	$request .= " where COMMUNE like '%" . $xfilter . "%' or ACTION like '%" . $xfilter . "%' or NOM like '%" . $xfilter . "%'";
$request .= " order by " . $order;

optimize($request);

$result = EA_sql_query($request);
$nbtot = EA_sql_num_rows($result);

$limit = "";
$listpages = "";
pagination($nbtot, $page, $baselink, $listpages, $limit);

if ($limit <> "") {
	$request = $request . $limit;
	$result = EA_sql_query($request);
	$nb = EA_sql_num_rows($result);
} else {
	$nb = $nbtot;
}

if ($nb > 0) {
	echo '<p>' . $listpages . '</p>';
	$i = 1 + ($page - 1) * MAX_PAGE;
	echo '<table summary="Liste des actions">';
	echo '<tr class="rowheader">';
	// echo '<th> Tri : </th>';
	echo '<th>' . $hdate . '</th>';
	echo '<th>' . $hnoms . '</th>';
	echo '<th>' . $hcomm . '</th>';
	echo '<th>Action</th>';
	echo '<th>Actes</th>';
	echo '</tr>';

	while ($ligne = EA_sql_fetch_row($result)) {
		echo '<tr class="row' . (fmod($i, 2)) . '">';
		//		echo '<td>'.$i.'. </td>';
		echo '<td>' . $ligne[3] . ' </td>';
		echo '<td align="center">' . $ligne[0] . ' ' . $ligne[1] . '</td>';
		echo '<td align="center">' . $ligne[5] . '</td>';
		echo '<td>' . $ligne[4] . ' </td>';
		echo '<td>' . $ligne[6] . ' </td>';
		echo '</tr>';
		$i++;
	}
	echo '</table>';
	echo '<p>' . $listpages . '</p>';
} else {
	msg('Aucune action enregistrée');
}

echo '</div>';
echo '<p>Durée du traitement  : ' . round(microtime_float() - $MT0, 3) . ' sec.</p>' . "\n";
close_page(1);
