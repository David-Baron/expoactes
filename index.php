<?php
// Page d'accueil publique du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, GPL v2, publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
//-------------------------------------------------------------------
if (! file_exists("_config/connect.inc.php")) {
    echo '<p class="erreur">Lancer le script d''installation pour avoir acc&egrave;s &agrave; l''application<br />';
    echo 'Pour des raisons de s&eacute;curit&eacute; il n''a pas &eacute;t&eacute; fait de lien direct.<br /></p>';
    exit;
    }

include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

define ("ADM",0); // Mode public;

$xtyp = getparam('xtyp');
$act = getparam('act');
$init = getparam('init');
$vue = getparam('vue'); // T = Tableau / C = Carte
if ($vue == "" and GEO_MODE_PUBLIC%2==1)
	$vue = "C";
if (($vue == "" and GEO_MODE_PUBLIC%2==0) or $init<>"")
	$vue = "T";

$root = "";
$xpatr= "";
$page = "";
pathroot($root,$path,$xtyp,$xpatr,$page);


if ($act=="logout")
	{
	setcookie('userid',"",null,$root);
	setcookie('md5',"",null,$root);
	header("Location: ".$root."/index.php");
	die();
	}

$dbok = false;  
$userlogin="";

$request = "show tables like '".EA_DB."_%'";
$result = EA_sql_query($request);
$dbok = (EA_sql_num_rows($result) >= 7);
if (!$dbok)
	{
	header("Location: ".$root."/install/install.php");
	die();
	}	
if (!check_version(EA_VERSION,'3.2.2'))
    { // si version mémorisée < 3.2.2
    echo '<p class="erreur">Vous utilisez ExpoActes .<br /></p>';
    echo '<p class="erreur">La mont&eacute;e de version d\'ExpoActes n\'est possible que depuis la version 3.2.2.<br /></p>';
    echo 'Installer la version 3.2.2 pour pouvoir faire cette mise &agrave; jour.<br /></p>';
    exit;
    }
if (!check_version(EA_VERSION,EA_VERSION_PRG))
	{ // si version mémorisée < version du programme
	header("Location: ".$root."/install/update.php");
	die();
	}
//{ print '<pre>INDEX:';  print_r($_SERVER); echo '</pre>'; }
//{ print '<pre>INDEX:';  print_r($_REQUEST); echo '</pre>'; }
global $u_db;

$userlevel=logonok(1);
if ($userlevel==0)
	{
	login($root);
	}
	
if ($xtyp=="")
	$xtyp  = getparam('xtyp');

if ($xtyp=="" or $xtyp=='A')
	{
  if (SHOW_ALLTYPES==1) 
  	$xtyp='A'; 
    else 
    $xtyp='N';
  }

$chemin="/";
if (GEO_MODE_PUBLIC==5 or $vue=="C")  // si pas localité isolée et avec carte
	{
	include_once("tools/GoogleMap/GoogleMapV3.php");
	include_once("tools/GoogleMap/Jsmin.php");

	$carto = new GoogleMapAPI(); 
	$carto->_minify_js = isset($_REQUEST["min"])?FALSE:TRUE;
	include("tools/carto_index.php");
	//$carto->addMarkerByAddress("Bievre, Namur","Bièvre", "Texte de la bulle");
	$carto->setMapType("terrain");
	$carto->setTypeControlsStyle("dropdown");
	if (GEO_HAUT_CARTE=="") $geo_haut_carte=400;
		else $geo_haut_carte=GEO_HAUT_CARTE;
	$carto->setHeight($geo_haut_carte);
	$carto->setWidth("100%");
	$carto->enableClustering();
	if (GEO_ZOOM_DEGROUPAGE=="") $geo_degroupage=10; 
	  else $geo_degroupage=GEO_ZOOM_DEGROUPAGE;
	$carto->setClusterOptions($geo_degroupage); // plus de cluster au dela de ce niveau de zoom
	$carto->setClusterLocation("tools/GoogleMap/markerclusterer_compiled.js");
  if (GEO_CENTRE_CARTE <> "")
		{
		$georeq = "select LON,LAT from ".EA_DB."_geoloc where COMMUNE = '".sql_quote(GEO_CENTRE_CARTE)."' and STATUT in ('A','M')";
		$geores =  EA_sql_query($georeq);
		if ($geo = EA_sql_fetch_array($geores))
			{
			$carto->setCenterCoords($geo['LON'],$geo['LAT']);
			}
		}
	if (GEO_ZOOM_INITIAL=="") $geo_zoom=0; 
		else $geo_zoom=GEO_ZOOM_INITIAL;
	if ($geo_zoom > 0) 
		{
		$carto->disableZoomEncompass ();
		$carto->setZoomLevel($geo_zoom);
		}

	$JSheader = $carto->getHeaderJS();
	$JSheader .= $carto->getMapJS();
	}
	else
	$JSheader = "";

open_page(SITENAME." : Dépouillement d'actes de l'état-civil et des registres paroissiaux",$root,null,null,$JSheader,'../index.htm','rss.php');
navigation($root,1);

echoln('<div id="col_menu">');
form_recherche($root);
$menu_actes = statistiques($vue);
menu_public();
show_pub_menu();
show_certifications();

echo '</div>';

echo '<div id="col_main">';

if (strlen(trim(AVERTISMT))>0)
  {
  if (isin(AVERTISMT,"</p>")>0)
  	echo AVERTISMT;
  else
  	echo '<p>'.AVERTISMT.'</p>';
  }	
echo '<h2>Communes et paroisses';
if (GEO_MODE_PUBLIC>=3 and GEO_MODE_PUBLIC<5)
	{
	echo " : ";
	if ($xtyp=="")
		$argtyp = "";
		else
		$argtyp = "&xtyp=".$xtyp;
	$href = '<a href="'.$root.$chemin.'index.php';
	if ($vue=="C")
		echo "Carte";
		else
		echo '<a href="'.$root.$chemin.'index.php?vue=C'.$argtyp.'">Carte</a>';
	echo " | ";
	if ($vue=="T")
		echo "Tableau";
		else
		echo '<a href="'.$root.$chemin.'index.php?vue=T'.$argtyp.'">Tableau</a>';
	}
echo '</h2>';

if (GEO_MODE_PUBLIC==5 or $vue=="C")  // si pas localité isolée et avec carte
	{
	echo '<p><b>'.$menu_actes.'</b></p>';
	//--- Carte
	$carto->printOnLoad();
	$carto->printMap();
	//$carto->printSidebar();
	}

// --- module principal
if (GEO_MODE_PUBLIC==5 or $vue=="T")  // si pas localité isolée et avec carte
	{
	// $menu_actes calculé dans le module statistiques
	echo '<p><b>'.$menu_actes.'</b></p>';
	include("tools/tableau_index.php");
	}
echo '<p>&nbsp;</p>';
include("_config/commentaire.htm");

echo '</div>';
close_page(1,$root);

?>

