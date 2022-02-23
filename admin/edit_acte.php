<?php
include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";
$lg='fr';

//**************************** ADMIN **************************
 
pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";

$needlevel=6;  // niveau d'accès (anciennement 5)
$userlevel=logonok($needlevel);
while ($userlevel<$needlevel)
  {
  login($root);
  }

$xid      = getparam('xid');
$xtyp     = strtoupper(getparam('xtyp'));
if ($xtyp=="")
	$xtyp = getparam('TypeActes');
$xconfirm = getparam('xconfirm');

if ($xid<0)
	{
	$title = "Ajout d'un acte";
	$logtxt = "Ajout";
	$comdep  = html_entity_decode(getparam('ComDep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
	$Commune = communede($comdep);
	$Depart  = departementde($comdep);
	$xtdiv    = getparam('typdivers');
	}
	else
	{
	$title = "Edition d'un acte";
	$logtxt = "Edition";
	}
	
open_page($title,$root);
navadmin($root,$title);

echo '<div id="col_menu">';
form_recherche();
menu_admin($root,$userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

$ok = false;
$missingargs=false;
$oktype=false;
$today = today();

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if ($xid == '' or $xtyp =='' or $xtyp=='X')
	{
	// Données postées
	msg("Vous devez préciser le numéro et le type de l'acte.");
  $missingargs=true;  // par défaut
  }
if (! $missingargs)
	{
	$oktype=true;
	switch ($xtyp)
		{
		case "N":
			$ntype = "de naissance";
			$table = EA_DB."_nai3";
			$script = "tab_naiss.php";
			break;
		case "D":
			$ntype = "de décès";
			$table = EA_DB."_dec3";
			$script = "tab_deces.php";
			break;
		case "V":
			$ntype = "divers";
			$table = EA_DB."_div3";
			$script = "tab_bans.php";
			break;
		case "M":
			$ntype = "de mariage";
			$table = EA_DB."_mar3";
			$script = "tab_mari.php";
			break;
		default:
			$oktype=false;
		}
	// LIBELLE","A0","50","V","Type de document","TXT"), 
	$mdb = load_zlabels($xtyp,$lg);
	if ($xconfirm == 'confirmed' and $oktype)
		{
		// *** Vérification des données reçues
		$ladate="";
		$ok=true;
		$MauvaiseDate=0;
		$ladate="";
		ajuste_date(getparam("DATETXT"),$ladate,$MauvaiseDate);
		
		if ($xid<0)
			{
			$request = "insert into ".$table." ";
			$zlist = "(";
			$vlist = "(";
			$txt = "ajouté";
			}
			else
			{
			$request = "update ".$table." set ";
			$txt = "modifié";
			$logtxt = "Edition";
			}		
		for ($i=0;$i<count($mdb);$i++)
			{
			if ($mdb[$i]['OBLIG'] == 'Y')  //  obligatoire
				{
				// champ obligatoire
				if (empty($_REQUEST[$mdb[$i]['ZONE']]))
					{
					msg('La zone ['.$mdb[$i]['ETIQ'].'] de ['.$mdb[$i]['GETIQ'].'] est obligatoire.');
					$ok = false;
					}
				}
			$valeurlue = getparam($mdb[$i]['ZONE']);
			$valeurlue = str_replace("++",chr(226).chr(128).chr(160), $valeurlue);
			if ($xid<0)
				{ // ajout
				$zlist .= $mdb[$i]['ZONE'].",";
				$vlist .= "'".sql_quote($valeurlue)."', ";
				}
				else
				$request .= $mdb[$i]['ZONE']." = '".sql_quote($valeurlue)."', "; // modif
			}
		if ($xid<0)	
			$request .= $zlist."LADATE,DTDEPOT,DTMODIF,TYPACT,IDNIM) values ".$vlist."'".$ladate."','".$today."','".$today."','".$xtyp."',0)";
		else
			$request .= "LADATE= '".$ladate."', "."DTMODIF= '".$today."' where ID=".$xid.";";
		if ($ok)
		  // *** si tout est ok : sauvegarde de l acte modifié
		  {
			$result = mysql_query($request);
		  // echo $request;
			$nb = mysql_affected_rows();
			if ($nb > 0)
				{
				echo '<p>'.$nb.' acte de '.$ntype.' '.$txt.'</p>';
				writelog($logtxt.' '.$ntype.' #'.$xid,getparam("COMMUNE"),$nb);
				echo '<p>Retourner à la liste des actes ';
				echo '<a href="'.mkurl($script,getparam("COMMUNE")." [".getparam("DEPART")."]",getparam("NOM")).'"><b>'.getparam("NOM").'</b></a>';
				if (strpos("MV",$xtyp)!==false)
					{
					echo ' ou <a href="'.mkurl($script,getparam("COMMUNE")." [".getparam("DEPART")."]",getparam("C_NOM")).'"><b>'.getparam("C_NOM").'</b></a>';
					}
				echo '</p>';
				maj_stats($xtyp, $T0, $path,"C",getparam("COMMUNE"),getparam("DEPART")); 
				}
			 else
				{
				echo '<p>Aucun acte modifié.</p>';
				}
			}
		}
	if (!$ok)
		{
		// *** pas encre Ok : On charge l acte pour édition ***
		$champs = "";
		for ($i=0;$i<count($mdb);$i++)
		  {
		    {
		    $champs .= $mdb[$i]['ZONE'].", ";
		    }
		  }
		$request = "select ".$champs." ID from ".$table." where ID=".$xid;
		$result = mysql_query($request);
    //echo $request;
		if ($acte = mysql_fetch_array($result) or $xid==-1)
		  {
		  // lecture des tailles effective des zones
		  $qColumnNames = mysql_query("SHOW COLUMNS FROM ".$table);
			$numColumns = mysql_num_rows($qColumnNames);
			$xx = 0;
			while ($xx < $numColumns)
				{
				$colname = mysql_fetch_row($qColumnNames);
				$xy = isin($colname[1],'(');
				if ($xy>0) 
					{
					$xt = mb_substr($colname[1],$xy+1,isin($colname[1],')')-$xy-1);
					}
					else
						switch (strtoupper($colname[1]))
						{
						case "TEXT": 
							$xt = 1000;
							break;
						case "DATE": 
							$xt = 10;  
							break;
						}	
					
				$col[$colname[0]] = $xt;
				$xx++;
				}
        //{ print '<pre>';  print_r($col); echo '</pre>'; }
	  
			echo '<form method="post" action="">'."\n";
			echo '<h2 align="center">'.$logtxt.' '.$ntype.'</h2>';
 			//echo '<h3 align="center">Commune/paroisse : '.$acte["COMMUNE"].'</h3>';
			echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">'."\n";

			$grp = "";
			for ($i=0;$i<count($mdb);$i++)				
				{
				if ($mdb[$i]['GROUPE']<>$grp)
					{
					$grp=$mdb[$i]['GROUPE'];
					echo ' <tr class="row0">'."\n";
					echo '  <td align="left"><b>&nbsp; '.$mdb[$i]['GETIQ']."  </b></td>\n";
					echo '  <td> </td>'."\n";
					echo ' </tr>';
					}
				// parametres : $name,$size,$value,$caption	
				$value = getparam($mdb[$i]['ZONE']);
				if ($value=="")  // premier affichage
					{
					if ($xid<0)
						{
						switch ($mdb[$i]['ZONE'])
							{
							case "COMMUNE":
								$value = $Commune;
								break;
							case "DEPART":
								$value = $Depart;
								break;
							case "LIBELLE":
								$value = $xtdiv;
								break;
							case "DEPOSANT":
								$value = current_user("ID");
								break;
							default: 
								$value = getparam($mdb[$i]['ZONE']);
							}
						}
						else
						$value = $acte[$mdb[$i]['ZONE']];
					}
				edit_text($mdb[$i]['ZONE'],$col[$mdb[$i]['ZONE']],$value,$mdb[$i]['ETIQ']);
				}
			echo ' <tr class="row0"><td>'."\n";
			echo '<input type="hidden" name="xtyp" value="'.$xtyp.'" />'."\n";
			echo '<input type="hidden" name="xid"  value="'.$xid.'" />'."\n";
			echo '<input type="hidden" name="xconfirm" value="confirmed" />'."\n";
			echo '<td><input type="submit" value=" >> ENREGISTRER >> " />'."\n";
			$comdep=$acte["COMMUNE"].' ['.$acte["DEPART"].']';
			if ($xid<0)
				$url="ajout_1acte.php";
			 else
				$url=mkurl($script,stripslashes($comdep),$acte["NOM"]);
			echo '&nbsp; &nbsp; &nbsp; <a href="'.$url.'">Annuler</a>'."\n";
		  echo "</td></tr></table>\n";
			echo "</form>\n";
			}
			else
			{
    	msg('Impossible de trouver cet acte !');
			}
		} // confirmed ??
	}
echo '</div>';
close_page(1,$root);
?>
