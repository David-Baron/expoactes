<?php
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');

pathroot($root,$path,$xcomm,$xpatr,$page);

$id  = getparam('id');
$act = getparam('act');

$userlogin="";
$userlevel=logonok(9);
while ($userlevel<9)
  {
  login($root);
  }

$lon    = getparam('lon');
$lat    = getparam('lat');
$noteN  = getparam('noteN');
$noteM  = getparam('noteM');
$noteD  = getparam('noteD');
$noteV  = getparam('noteV');

$missingargs=true;
$JSheader = "";
//print '<pre>';  print_r($_REQUEST); echo '</pre>';

$leid=getparam('id');
	 
if ($id > 0)  // édition
	{  //
	$action = 'Modification';
	$request = "select *"
				." from ".EA_DB."_geoloc "
				." where ID =".$id;
				//echo '<P>'.$request;
	if ($result = EA_sql_query($request))
		{
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
		}
	   else
		{
		echo "<p>*** FICHE NON TROUVEE***</p>";
		}
	$zoom = 11;	
	if ($lon==0 and $lat==0 and GEO_CENTRE_CARTE <> "")
		{
		$georeq = "select LON,LAT from ".EA_DB."_geoloc where COMMUNE = '".sql_quote(GEO_CENTRE_CARTE)."' and STATUT in ('A','M')";
		$geores =  EA_sql_query($georeq);
		if ($geo = EA_sql_fetch_array($geores))
			{
			$lon = $geo['LON'];
			$lat = $geo['LAT'];
			$zoom = 5;
			}
		}
	if ($lon==0 and $lat==0)
		{
		$lon = 5;
		$lat = 50; // Froidfontaine !!
		$zoom = 5;
		}
		
	include_once("../tools/GoogleMap/OrienteMap.inc.php");
	include_once("../tools/GoogleMap/Jsmin.php");

	$carto = new GoogleMapAPI(); 
	$carto->_minify_js = isset($_REQUEST["min"])?FALSE:TRUE;
	$carto->setMapType("terrain");
	$carto->setTypeControlsStyle("dropdown");
	$carto->setHeight(300);
	$carto->setWidth(500);
	global $root;
	$image = EA_URL_SITE.$root.'/img/pin_eye.png';
	$Xanchor = 10;
	$Yanchor = 35;
	$carto->setMarkerIcon($image,'',$Xanchor,$Yanchor); // défini le décalage du pied de la punaise
	$carto->addMarkerByCoords($lon,$lat);
	$carto->enableMarkerDraggable();
	$carto->setZoomLevel($zoom);

	$JSheader = $carto->getHeaderJS();
	$JSheader .= $carto->getMapJS();
	}

open_page("Gestion des localités",$root,null,null,$JSheader);
$carto->printOnLoad();

navadmin($root,"Gestion d'une localité");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root,$userlevel);
echo '</div>';
echo '<div id="col_main_adm">';
menu_datas('L');


if ($id > 0 and $act=="del")
	{
	$reqmaj = "delete from ".EA_DB."_geoloc where ID=".$id.";";
	if  ($result = EA_sql_query($reqmaj,$a_db))
		{
		//writelog('Suppression localité #'.$id,$lelogin,1);
		echo '<p><b>FICHE SUPPRIMEE.</b></p>';
		$id=0;
		}
	 else
		{
		echo ' -> Erreur : ';
		echo '<p>'.EA_sql_error().'<br />'.$reqmaj.'</p>';
		}
	}

	// Données postées -> ajouter ou modifier
if (getparam('action') == 'submitted')
	{
	$ok = true;
	// validations si besoin
	if ($ok)
		{
		$mes = "";
		if (getparam('lon')<>$lon or getparam('lat')<>$lat)
			$newstatut = 'M';
			else
			$newstatut = $statut;
		$missingargs=false;
		$reqmaj = "update ".EA_DB."_geoloc set ";
		$reqmaj = $reqmaj.
				 "NOTE_N = '".sql_quote(getparam('noteN'))."', ".
				 "NOTE_M = '".sql_quote(getparam('noteM'))."', ".
				 "NOTE_D = '".sql_quote(getparam('noteD'))."', ".
				 "NOTE_V = '".sql_quote(getparam('noteV'))."', ".
				 "STATUT = '".sql_quote($newstatut)."', ".
				 "LON    = '".sql_quote(getparam('lon'))."', ".
				 "LAT    = '".sql_quote(getparam('lat'))."' ".
			 " where ID=".$id.";";
			
	  //echo "<p>".$reqmaj."</p>";
		if  ($result = EA_sql_query($reqmaj))
			{
			// echo '<p>'.EA_sql_error().'<br />'.$reqmaj.'</p>';
			echo '<p><b>Fiche enregistrée'.$mes.'.</b></p>';
			$id = 0;
			}
		  else
			{
			echo ' -> Erreur : ';
			echo '<p>'.EA_sql_error().'<br />'.$reqmaj.'</p>';
			}
		}
  }

//Si pas tout les arguments nécessaire, on affiche le formulaire
if($id<>0 and $missingargs)
	{
	echo '<h2>'.$action." d'une fiche de localité</h2> \n";
  echo '<form method="post" id="fiche" name="eaform" action="gestgeoloc.php">'."\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">'."\n";

	echo " <tr>\n";
	echo "  <td align='right'>Localité : </td>\n";
	echo '  <td colspan="2"><b>'.$commune." [".$depart."]</b></td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Longitude : </td>\n";
	echo '  <td><input type="text" name="lon" id="lon" size="10" value="'.$row['LON'].'" />'."</td>\n";
	echo '  <td rowspan="4">';
	$carto->printMap();
	echo "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Latitude : </td>\n";
	echo '  <td><input type="text" name="lat" id="lat" size="10" value="'.$row['LAT'].'" />'."</td>\n";
	echo " </tr>\n";

	$ast= array("M" => "Manuelle", "N" => "Non définie","A" => "Automatique");
	echo " <tr>\n";
	echo "  <td align='right'>Géolocalisation : </td>\n";
	echo '  <td>'.$ast[$statut]."</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right' colspan='2'><b>Déplacer la punaise pour &nbsp; <br/ >corriger la localisation --> </b></td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Commentaire<br />Naissances : </td>\n";
	echo '  <td colspan="2"><textarea name="noteN" cols="60" rows="2">'.html_entity_decode($noteN, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</textarea></td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Commentaire<br />Mariages : </td>\n";
	echo '  <td colspan="2"><textarea name="noteM" cols="60" rows="2">'.html_entity_decode($noteM, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</textarea></td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Commentaire<br />Décès : </td>\n";
	echo '  <td colspan="2"><textarea name="noteD" cols="60" rows="2">'.html_entity_decode($noteD, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</textarea></td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Commentaire<br />Actes divers : </td>\n";
	echo '  <td colspan="2"><textarea name="noteV" cols="60" rows="2">'.html_entity_decode($noteV, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</textarea></td>';
	echo " </tr>\n";

	 echo " <tr><td align=\"right\">\n";
	 echo '  <input type="hidden" name="id" value="'.$id.'" />';
	 echo '  <input type="hidden" name="commune" value="'.$commune.'" />';
	 echo '  <input type="hidden" name="action" value="submitted" />';
	 echo '  <a href="aide/geoloc.html" target="_blank">Aide</a>&nbsp;';
	 echo '  <input type="reset" value=" Effacer " />'."\n";
	 echo " </td><td  colspan='2' align=\"left\">\n";
	 echo ' &nbsp; <input type="submit" value=" *** ENREGISTRER *** " />'."\n";
	 if ($id>0)
	   {
	   echo ' &nbsp; &nbsp; &nbsp; <a href="gestgeoloc.php?id='.$id.'&amp;act=del">Supprimer cette localité</a>'."\n";
	   }
	 echo " </td></tr>\n";
	 echo "</table>\n";
	 echo "</form>\n";
  }
 else
  {
	 echo '<p align="center"><a href="listgeolocs.php">Retour à la liste des localités</a>';
	 if ($leid > 0 and $act!="del")
	   echo '&nbsp;|&nbsp; <a href="gestgeoloc.php?id='.$leid.'">Retour à la fiche de '.getparam('commune').'</a>';
	 echo '</p>';
  }
echo '</div>';
close_page(1);
?>
