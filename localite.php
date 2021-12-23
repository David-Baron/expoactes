<?php
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

pathroot($root, $path, $xcomm, $xpatr, $page);

$id  = getparam('id');

$userlogin = "";
$userlevel = logonok(1);
while ($userlevel < 1) {
	login($root);
}

$ok = true;
$JSheader = "";
//print '<pre>';  print_r($_REQUEST); echo '</pre>';

if ($id > 0)  // édition
{
	$request = "select *"
		. " from " . EA_DB . "_geoloc "
		. " where ID =" . $id;
	if ($result = EA_sql_query($request)) {
		$row = EA_sql_fetch_array($result);
		$commune   = $row["COMMUNE"];
		$depart    = $row["DEPART"];
		$lon       = $row["LON"];
		$lat       = $row["LAT"];
		$statut    = $row["STATUT"];
		$noteN     = $row["NOTE_N"];
		$noteM     = $row["NOTE_M"];
		$noteD     = $row["NOTE_D"];
		$noteV     = $row["NOTE_V"];

		$request = "select *"
			. " from " . EA_DB . "_sums where COMMUNE = '" . sql_quote($commune) . "' and DEPART = '" . sql_quote($depart) . "'"
			. " order by INSTR('NMDV',TYPACT),LIBELLE; ";

		$cptN = $cptM = $cptD = $cptV = 0;
		$i = 0;
		$lasttyp = 'eye'; // default
		if ($result = EA_sql_query($request)) {
			while ($ligne = EA_sql_fetch_array($result)) {
				if ($ligne['TYPACT'] <> $lasttyp)
					$i++;
				$lasttyp = $ligne['TYPACT'];
				switch ($lasttyp) {
					case "N":
						$cptN = $ligne['NB_TOT'];
						break;
					case "M":
						$cptM = $ligne['NB_TOT'];
						break;
					case "D":
						$cptD = $ligne['NB_TOT'];
						break;
					case "V":
						$cptV += $ligne['NB_TOT'];
						break;
				}
			}
		}
	} else {
		echo "<p>*** FICHE NON TROUVEE***</p>";
		$ok = false;
	}

	include_once("tools/GoogleMap/GoogleMapV3.php");
	include_once("tools/GoogleMap/Jsmin.php");

	$carto = new GoogleMapAPI();
	$carto->_minify_js = isset($_REQUEST["min"]) ? FALSE : TRUE;
	$carto->setMapType("terrain");
	$carto->setTypeControlsStyle("dropdown");
	$carto->setHeight(400);
	$carto->setWidth(600);
	global $root;
	$fullpath = EA_URL_SITE . $root;
	$image = $fullpath . '/img/pin_eye.png';
	$Xanchor = 10;
	$Yanchor = 35;
	global $imagePin;
	$imagePin = $fullpath . '/img/pin_';
	$carto->setMarkerIcon($image, '', $Xanchor, $Yanchor); // défini le décalage du pied de la punaise
	$carto->addIcon($imagePin . "M.png", '', $Xanchor, $Yanchor);
	$carto->addIcon($imagePin . "D.png", '', $Xanchor, $Yanchor);
	$carto->addIcon($imagePin . "V.png", '', $Xanchor, $Yanchor);
	$carto->addIcon($imagePin . "2.png", '', $Xanchor, $Yanchor);
	$carto->addIcon($imagePin . "3.png", '', $Xanchor, $Yanchor);
	$carto->addIcon($imagePin . "4.png", '', $Xanchor, $Yanchor);
	if ($i == 1)
		$pin = $imagePin . $lasttyp . ".png";
	else {
		$pin = $imagePin . $i . ".png";
	}
	$carto->addMarkerByCoords($lon, $lat, "", "", "", $pin);
	$carto->setZoomLevel(11);

	$JSheader = $carto->getHeaderJS();
	$JSheader .= $carto->getMapJS();
}

$localite = $commune . " [" . $depart . "]";
$userid = current_user("ID");
open_page($localite, $root, null, null, $JSheader);
navigation($root, 2, "A", "Localisation d'une commune ou paroisse");
$carto->printOnLoad();
zone_menu(0, $userlevel);

echo '<div id="col_main">' . "\n";
echo '<h2>Commune/Paroisse : ' . $localite . '</h2>';
echo '<div id="mapzone" align="center">';
$carto->printMap();
echo '</div>';

if ($noteN <> '' or $cptN > 0) {
	if ($cptN > 0) {
		$href = '<a href="' . mkurl($root . '/tab_naiss.php', $commune . ' [' . $depart . ']') . '">';
		$txthtml = "<p>" . $href . entier($cptN) . " Naissances/Baptêmes</a><br />";
	} else
		$txthtml = "<p>";
	echo $txthtml . $noteN . "</p>";
}
if ($noteM <> '' or $cptM > 0) {
	if ($cptM > 0) {
		$href = '<a href="' . mkurl($root . '/tab_mari.php', $commune . ' [' . $depart . ']') . '">';
		$txthtml = "<p>" . $href . entier($cptM) . " Mariages</a><br />";
	} else
		$txthtml = "<p>";
	echo $txthtml . $noteM . "</p>";
}
if ($noteD <> '' or $cptD > 0) {
	if ($cptD > 0) {
		$href = '<a href="' . mkurl($root . '/tab_deces.php', $commune . ' [' . $depart . ']') . '">';
		$txthtml = "<p>" . $href . entier($cptD) . " Décès/Sépultures</a><br />";
	} else
		$txthtml = "<p>";
	echo $txthtml . $noteD . "</p>";
}
if ($noteV <> '' or $cptV > 0) {
	if ($cptV > 0) {
		$href = '<a href="' . mkurl($root . '/tab_bans.php', $commune . ' [' . $depart . ']') . '">';
		$txthtml = "<p>" . $href . entier($cptV) . " Actes divers</a><br />";
	} else
		$txthtml = "<p>";
	echo $txthtml . $noteV . "</p>";
}

echo '</div>';
close_page(1);
