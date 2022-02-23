<?php

function init_page()
  {
  global $root,$userlevel,$htmlpage;

  open_page("Export des paramètres ",$root);
	navadmin($root,"Export des paramètres");

	echo '<div id="col_menu">';
	form_recherche($root);
	menu_admin($root,$userlevel);
	echo '</div>';

	echo '<div id="col_main_adm">';
	$htmlpage = true;
  }

//-----------------------------------------

ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");
include("../install/instutils.php");


$root = "";
$path = "";
$enclosed = '"';  // ou '"'
$separator = ';';
$htmlpage = false;

//**************************** ADMIN **************************

pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";
$userlevel=logonok(9);
while ($userlevel<9)
  {
  login($root);
  }

$userid=current_user("ID");

$missingargs=false;
$oktype=false;

$Destin = 'T'; // Toujours vers fichier (T) (sauf pour debug .. D )

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

		$filename = "ea_params_".gmdate('Ymd').'.xml';
		$mime_type = 'text/xml';
		if ($Destin=='T')
			{
			// Download
			header('Content-Type: ' . $mime_type);
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			// lem9 & loic1: IE need specific headers
			if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') === true)
				{
				header('Content-Disposition: inline; filename="' . $filename . '"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				}
			 else
				{
				header('Content-Disposition: attachment; filename="' . $filename . '"');
				header('Pragma: no-cache');
				}
			}
		 else
			{
			// HTML
			init_page();
			echo '<pre>' . "\n";
			//$row = mysql_fetch_array($result);
			//{ print '<pre>';  print_r($row); echo '</pre>'; }
			} // end download
  
  $doc  = "<?xml version='1.0' encoding='UTF-8'?>\n"; 
  $doc .= "<!-- Export des parametres du ".gmdate('d M Y')." -->\n"; 
  $doc .= "<expoactes>\n";

	$nb=0;
	// Export des paramètres de configuration principaux
	$table = EA_DB."_params";
	$zones= array('param','groupe','ordre','type','valeur','listval','libelle','aide');
	$doc .= xml_write_table($table,$zones,$nb);

	// Export des étiquettes des zones
	$table = EA_DB."_metadb";
	$zones= array('ZID', 'dtable', 'zone', 'groupe', 'bloc', 'typ', 'taille', 'OV2', 'OV3', 'oblig', 'affich');
	$doc .= xml_write_table($table,$zones,$nb);

	// Export des libellés étiquettes des zones
	$table = EA_DB."_metalg";
	$zones= array('ZID','lg','etiq','aide');
	$doc .= xml_write_table($table,$zones,$nb);

	// Export des étiquettes des groupes
	$table = EA_DB."_mgrplg";
	$zones= array('grp','dtable','lg','sigle','getiq');
	$doc .= xml_write_table($table,$zones,$nb);

	$doc .= "</expoactes>\n";
  echo $doc;
	$list_backups = get_last_backups();
	$list_backups["P"] = today();
	set_last_backups($list_backups);	
	writelog('Backup des paramètres',"PARAMS",$nb);

if ($htmlpage)
  {
	echo '</div>';
	close_page(1,$root);
	}
?>
