<?php
// Tableau en version cartographique pour la page d'accueil

function plot_commune($carto, $depart, $commune, $etiquette, $texte_html, $listTypes = "", $listSigles = "")
{
	global $imagePin;
	$georeq = "select LON,LAT from " . EA_DB . "_geoloc where COMMUNE = '" . sql_quote($commune) . "' and DEPART = '" . sql_quote($depart) . "' and STATUT in ('A','M')";
	$geores =  EA_sql_query($georeq);
	if ($geo = EA_sql_fetch_array($geores)) {
		if (strlen($listTypes) == 1) {
			$pin = $imagePin . $listTypes . ".png";
		} else {
			$pin = $imagePin . strlen($listTypes) . ".png";
			//$etiquette .= "(".$listSigles.")";
			$etiquette = $listSigles . " : " . $etiquette;
		}
		//$carto->addMarkerByAddress($ligne['COMMUNE'].", ".$ligne['DEPART'],$ligne['COMMUNE'], "");
		//$carto->addMarkerByCoords($geo['LON'],$geo['LAT'],$etiquette,"XX"); //$etiquette,$texte_html);
		$carto->addMarkerByCoords($geo['LON'], $geo['LAT'], $etiquette, $texte_html, '', $pin);
	}
}

/*** URL pour générer des icones avec chld=o/x/%2B/%23|<color fond>:FFCC66/FF9900|<color text>:000000
http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=o|FFCC66|000000
 */

if ($xtyp == "" or $xtyp == "A") {
	$condit1 = "";
} else {
	$condit1 = " where TYPACT='" . $xtyp . "'";
}

$request = "select *"
	. " from " . EA_DB . "_sums " . $condit1
	. " order by DEPART,COMMUNE,INSTR('NMDV',TYPACT),LIBELLE; ";

optimize($request);
//echo '<p>'.$request;
$pre_libelle = "XXX";
$pre_commune = "XXX";
$txthtml = "";
global $root;
$fullpath = EA_URL_SITE . $root;
$image = $fullpath . '/img/pin_N.png';
global $imagePin;
$imagePin = $fullpath . '/img/pin_';
$Xanchor = 10;
$Yanchor = 42;
$carto->setMarkerIcon($image, '', $Xanchor, $Yanchor); // défini le décalage du pied de la punaise
$carto->addIcon($imagePin . "M.png", '', $Xanchor, $Yanchor);
$carto->addIcon($imagePin . "D.png", '', $Xanchor, $Yanchor);
$carto->addIcon($imagePin . "V.png", '', $Xanchor, $Yanchor);
$carto->addIcon($imagePin . "2.png", '', $Xanchor, $Yanchor);
$carto->addIcon($imagePin . "3.png", '', $Xanchor, $Yanchor);
$carto->addIcon($imagePin . "4.png", '', $Xanchor, $Yanchor);
$listTypes = '';
$listSigles = '';
$pre_type = "W";
if ($result = EA_sql_query($request)) {
	$i = 1;
	while ($ligne = EA_sql_fetch_array($result)) {
		$i++;
		if ($ligne['DEPART'] . $ligne['COMMUNE'] <> $pre_commune) { // nouvelle commune
			if ($pre_commune <> "XXX") {
				plot_commune($carto, $depart, $commune, $etiquette, $txthtml, $listTypes, $listSigles);
				$listTypes = '';
				$listSigles = '';
				$pre_type = "W";
			}
			$depart = $ligne['DEPART'];
			$commune = $ligne['COMMUNE'];
			$pre_commune = $depart . $commune;
			$etiquette = $commune . " [" . $depart . "]";
			$txthtml = "<b>" . $commune . " [" . $depart . "]</b><br />&nbsp;<br />";
		}
		$linkdiv = "";
		switch ($ligne['TYPACT']) {
			case "N":
				$typel = "Naissances/Baptêmes";
				$prog = "tab_naiss.php";
				$sigle = "°";
				break;
			case "M":
				$typel = "Mariages";
				$prog = "tab_mari.php";
				$sigle = "x";
				break;
			case "D":
				$typel = "Décès/Sépultures";
				$prog = "tab_deces.php";
				$sigle = "+";
				break;
			case "V":
				if ($ligne['LIBELLE'] == "")
					$typel = "Divers";
				else {
					$typel = $ligne['LIBELLE'];
					$linkdiv = ';' . $ligne['LIBELLE'];
				}
				$prog = "tab_bans.php";
				$sigle = "#";
				break;
		}
		if ($pre_type <> $ligne['TYPACT']) {
			$listTypes .= $ligne['TYPACT'];
			$pre_type = $ligne['TYPACT'];
			$listSigles .= $sigle;
		}
		$href = '<a href="' . mkurl($root . $chemin . $prog, $ligne['COMMUNE'] . ' [' . $ligne['DEPART'] . ']' . $linkdiv) . '">';
		$txthtml .= $href . entier($ligne['NB_TOT']) . " " . $typel . "</a><br />";
		//echo $pre_type." --> ".$href."</p>";
	}
	if ($pre_commune <> "XXX") {
		plot_commune($carto, $depart, $commune, $etiquette, $txthtml, $listTypes, $listSigles);
	}
}
