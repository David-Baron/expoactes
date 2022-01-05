<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = 1;

pathroot($root, $path, $xcomm, $xpatr, $page);

$xord  = getparam('xord');
if ($xord == "") {
	$xord = "N";
}   // N = Nom
$page  = getparam('pg');
$init  = getparam('init');

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

open_page(SITENAME . " : Liste des localités (communes et paroisses)", $root);

navadmin($root, "Liste des localités");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

// Lister les actes

menu_datas('L');

echo '<h2>Localités connues du site ' . SITENAME . '</h2>';

$baselink = $root . '/admin/listgeolocs.php';
$request = "select distinct upper(left(COMMUNE,1)) as init from " . EA_DB . "_geoloc order by init";
$result = EA_sql_query($request);
$alphabet = "";
while ($row = EA_sql_fetch_row($result)) {
	if ($row[0] == $init)
		$alphabet .= '<b>' . $row[0] . '</b> ';
	else
		$alphabet .= '<a href="' . $baselink . '?xord=' . $xord . '&init=' . $row[0] . '">' . $row[0] . '</a> ';
}
echo '<p align="center">' . $alphabet . '</p>';

if ($init == "")
	$initiale = '';
else
	$initiale = '&init=' . $init;

$hcommune = '<a href="' . $baselink . '?xord=C' . $initiale . '">Commune</a>';
$hdepart  = '<a href="' . $baselink . '?xord=D' . $initiale . '">Département</a>';
$hgeoloc  = '<a href="' . $baselink . '?xord=S' . $initiale . '">Géolocalisation</a>';
$baselink = $baselink . '?xord=' . $xord . $initiale;

if ($xord == "C") {
	$order = "COMMUNE,DEPART";
	$hcommune = '<b>Commune</b>';
} elseif ($xord == "D") {
	$order = "DEPART, COMMUNE";
	$hdepart = '<b>Département</b>';
} elseif ($xord == "S") {
	$order = "find_in_set(STATUT,'N,M,A')";
	$hgeoloc = '<b>Géolocalisation</b>';
} else {
	$order = "COMMUNE,DEPART";
	$hcommune = '<b>Commune</b>';
}
if ($init == "")
	$condit = "";
else
	$condit = " where COMMUNE like '" . $init . "%' ";


$request = "select ID,COMMUNE,DEPART,LON,LAT,STATUT"
	. " from " . EA_DB . "_geoloc "
	. $condit
	. " order by " . $order;
//echo $request;
$result = EA_sql_query($request);
$nbtot = EA_sql_num_rows($result);

$limit = "";
$listpages = "";
pagination($nbtot, $page, $baselink, $listpages, $limit);

if ($limit <> "") {
	$request = $request . $limit;
	$result = EA_sql_query($request, $a_db);
	$nb = EA_sql_num_rows($result);
} else {
	$nb = $nbtot;
}

if ($nb > 0) {
	if ($listpages <> "")
		echo '<p>' . $listpages . '</p>';
	$i = 1 + ($page - 1) * MAX_PAGE_ADM;
	echo '<table summary="Liste des localités">';
	echo '<tr class="rowheader">';
	echo '<th> Tri : </th>';
	echo '<th>' . $hcommune . '</th>';
	echo '<th>' . $hdepart . '</th>';
	echo '<th>' . $hgeoloc . '</th>';
	echo '</tr>';

	while ($ligne = EA_sql_fetch_array($result)) {
		echo '<tr class="row' . (fmod($i, 2)) . '">';
		echo '<td>' . $i . '. </td>';
		$lenom = $ligne['COMMUNE'];
		if (trim($lenom) == "") $lenom = '&lt;non précisé&gt;';
		echo '<td><a href="' . $root . '/admin/gestgeoloc.php?id=' . $ligne['ID'] . '">' . $lenom . '</a> </td>';
		echo '<td>' . $ligne['DEPART'] . ' </td>';
		$ast = array("M" => "Manuelle", "N" => "Non définie", "A" => "Auto");
		echo '<td align="center">' . $ast[$ligne['STATUT']] . '</td>';
		echo '</tr>';
		$i++;
	}
	echo '</table>';
	if ($listpages <> "")
		echo '<p>' . $listpages . '</p>';
} else {
	msg('Aucune localité géocodée');
}
echo '</div>';
close_page(1);
