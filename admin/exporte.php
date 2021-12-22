<?php

function init_page($head="")
  {
  global $root,$userlevel,$htmlpage,$titre;

  open_page($titre,$root,null,null,$head);
    
   // Ajaxify Your PHP Functions   
	include("../tools/PHPLiveX/PHPLiveX.php");
	$ajax = new PHPLiveX(array("getCommunes"));  
	$ajax->Run(false,"../tools/PHPLiveX/phplivex.js");

	navadmin($root,$titre);

	echo '<div id="col_menu">';
	form_recherche($root);
	menu_admin($root,$userlevel);
	echo '</div>';

	echo '<div id="col_main_adm">';
	$htmlpage = true;
	flush();
  }

//-----------------------------------------

ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);
$bypassTIP=1; // pas de tracing ici

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$tpsreserve = 3;
$root = "";
$path = "";
$separator = ';';
$htmlpage = false;
$Max_exe_time = ini_get("max_execution_time");
$Max_time = min($Max_exe_time-$tpsreserve,MAX_EXEC_TIME);
$Max_size = return_bytes(ini_get("upload_max_filesize"));

//**************************** ADMIN **************************

pathroot($root,$path,$xcomm,$xpatr,$page);

$Destin   = getparam('Destin');
$Format   = getparam('Format');
$TypeActes = getparam('TypeActes');
if ($TypeActes=="") $TypeActes='N';

$userlogin="";
if ($Destin=="B")  // Backup
	{
	$needlevel=8;  // niveau d'accès
	$listcom = 2;  // liste de commune avec *** Backup complet
	$titre = "Backup des actes";
	$supp_fields = 0; // exporter tout
	$enclosed = '"';
	$enteteligne="EA32;";   // EA3 ansi / EA32 utf-8
	}
	else
	{
	$needlevel=6; // anciennement 5
	$listcom = 0;
	$titre = "Export d'une série d'actes";
	$supp_fields = 5; // Champs à ne pas exporter vers nimegue
	$enclosed = '';  // comme NIMEGUE
	if ($Format=='NIM2')
		$enteteligne="NIMEGUE-V2;";
	 else
		$enteteligne="NIMEGUEV3;";
	}
	
$userlevel=logonok($needlevel);
while ($userlevel<$needlevel)
  {
  login($root);
  }

$userid=current_user("ID");

$missingargs=false;
$oktype=false;
$tokenfile  = "../".DIR_BACKUP.$userlogin.'.txt';

$comdep  = html_entity_decode(getparam('ComDep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);
$AnneeDeb = getparam('AnneeDeb');
$AnneeFin = getparam('AnneeFin');
$TypeActes= mb_substr(getparam('TypeActes'),0,1);
$xtdiv    = getparam('typdivers');
$maxmega  = getparam('maxmega');
$skip = getparam('skip');
$skip = iif($skip>0,$skip,0);
$file = getparam('file');
$file = iif($file>1,$file,1);
$Maxbytes = (float)$maxmega*1024*1024;
//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; echo "<p>Commune= ".$Commune."<p>";}
$autokey= getparam('autokey');
$continue=1;
$xaction = getparam('action');
if ($xaction == 'go')
	{
	// Données postées
	if(empty($TypeActes) or empty($Destin))
		{
		init_page();
		if(empty($TypeActes)) msg('Vous devez préciser le type des actes');
		if(empty($Destin))    msg("Vous devez indiquer la destination de l'export");
		$missingargs=true;
		}
	if(empty($Commune))
		{
		init_page();
		msg('Vous devez préciser une commune.');
		$missingargs=true;
		}
	if (empty($autokey))
		$autokey = md5(uniqid(rand(), true)); // généré si lancement du chargement
		else
		{
		// récupération des valeurs dans le fichier
		if(($tof = fopen($tokenfile,"r")) === FALSE)
			{
			die('Impossible d\'ouvrir le fichier TOKEN en lecture!');
			}
			else
			{
			$vals=explode(";",fgets($tof));
      //{ print '<pre>';  print_r($vals); echo '</pre>'; }			
			fclose($tof);
			if ($vals[0]<>"EA_TOKEN") die('Fichier TOKEN invalide');
			if ($vals[1]<>$autokey) die('Mauvaise clé');
			$file=$vals[2];
			$skip=$vals[3];
			$continue=$vals[4];
			} 
		}
	if ($continue==1) // fichier a suivre
		{
		$reloadurl = 'exporte.php?action=go&TypeActes='.$TypeActes.'&Destin='.$Destin.'&maxmega='.$maxmega.'&ComDep='.$comdep.'&autokey='.$autokey;
		$metahead = '<META HTTP-EQUIV="Refresh" CONTENT="10; URL='.$reloadurl.'">';
		}
	}
 else
  {
  $missingargs=true;  // par défaut
	init_page();
	if ($Destin=="B")  // Backup
		menu_datas('B');	  
  }
if (! $missingargs)
	{
	if ($Destin=="B")  // Backup
		$mdb = load_zlabels($TypeActes,$lg,"EA3");
	 else
		$mdb = load_zlabels($TypeActes,$lg,$Format);		
		
	if ($continue==0) // fin d'une chaine automatique
		{
		init_page();
		menu_datas('B');	
		echo '<p>Le backup est terminé, il a sauvegardé '.entier($skip).' '.typact_txt($TypeActes).'.</p>';
		echo '<p><b>'.$file.' fichier(s) peut/peuvent à présent être récupéré(s) via FTP dans le répertoire "_backup".</b></p>';
		}
	else
		{
		$oktype=true;
		$condtdiv="";
		$soustype="";
		$extstype="";
		switch ($TypeActes)
			{
			case "N":
				$ntype = "naissance";
				$table = EA_DB."_nai3";
				break;
			case "V":
				$ntype = "types divers";
				$table = EA_DB."_div3";
				if (($xtdiv<>"") and (mb_substr($xtdiv,0,2)<>"**"))
					{
					$condtdiv = " and (LIBELLE='".urldecode($xtdiv)."')";
					$soustype = " (".$xtdiv.")";
					$extstype = "_".mb_substr($xtdiv,0,4);
					}
				break;
			case "M":
				$ntype = "mariage";
				$table = EA_DB."_mar3";
				break;
			case "D":
				$ntype = "décès";
				$table = EA_DB."_dec3";
				break;
			}
		$condad="";
		if ($AnneeDeb<>"")
			{
			$condad = " and year(LADATE)>=".$AnneeDeb;
			}
		$condaf="";
		if ($AnneeFin<>"")
			{
			$condaf = " and year(LADATE)<=".$AnneeFin;
			}
		$conddep = "";
		if ($userlevel<8)
			{
			$conddep = " and DEPOSANT=".$userid;
			}
		if (mb_substr($comdep,0,6)=="BACKUP")
			{
			$condcom = " NOT (ID IS NULL) ";
			}
			else
			{
			$condcom = " COMMUNE='".sql_quote($Commune)."' and DEPART='".sql_quote($Depart)."'";
			}
		if ($xaction == 'go')
			{
			$request = "select count(*) from ".$table.
								 " where ".$condcom.$condad.$condaf.$conddep.$condtdiv.";";
			//echo $request;
			$result = EA_sql_query($request);
			$row = EA_sql_fetch_row($result);
			$nbdocs = $row[0];
			
			$request = "select * from ".$table.
								 " where ".$condcom.$condad.$condaf.$conddep.$condtdiv." limit 0,1 ;";
			optimize($request);
			$result = EA_sql_query($request);
			$fields_cnt = EA_sql_num_fields($result);
			if ($nbdocs == 0)
				{
				init_page();
				msg("Il n'y a aucun acte de ".$ntype.$soustype. " à ".$comdep." (dont vous êtes le déposant) !","erreur");
				echo '<p><a href="exporte.php">Retour</a></p>';
				}
			 else
				{
				switch ($Destin) 
					{
					case 'T':
						// Download -> NIMEGUE -> ISO-8859-1 !!
						$filename  = strtr(remove_accent($Commune."_".$TypeActes.$extstype),'-/ "','____');
						$filename  .= '.TXT';
						$mime_type = 'text/x-csv;';
						header('Content-Type: ' . $mime_type. ' charset=iso-8859-1');
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
						$bytes = 0;
						break;
					case 'E':
						// HTML
						init_page();
						echo '<pre>' . "\n";
						break;
					case 'B':
						// Backup
						//$prc = intval($skip/$nbdocs*100);
						//$titre = $prc." % ".$titre;
						init_page($metahead);
						menu_datas('B');
						if (mb_substr($comdep,0,6)=="BACKUP")
							$com_name = "FULL";
						  else
							$com_name  = mb_substr(strtr(remove_accent($Commune),'-/ "','____'),0,8);
						$filename  = 'backup_'.date('Y-m-d').'_'.$com_name.'_'.$TypeActes.'.'.zeros($file,3).EXT_BACKUP;
						$bytes = 0;
						if (!is_dir("../".DIR_BACKUP))
							{
						  msg('034 : Répertoire de backup "'.DIR_BACKUP.'" inaccessible ou inexistant.');
						  die();
						  }
						if (!is__writable("../".DIR_BACKUP,false))
							{
						  msg('035 : Impossible de créer un fichier dans "'.DIR_BACKUP.'".');
						  die();
						  }

						echo '<p>Backup en cours vers le fichier <b>'.$filename.'</b> ...';
						$filename  = "../".DIR_BACKUP.$filename;
						if(($hof = fopen($filename,"w")) === FALSE)
							{
							die('Impossible d\'ouvrir le fichier en écriture!');
							// NB "a" permet "append"
							}
					} // switch					

				$stop=0;
				$request = "select * from ".$table.
									 " where ".$condcom.$condad.$condaf.$conddep.$condtdiv;
				if (MAX_SELECT_REC>0) 
					$request .= " limit ".$skip.",".MAX_SELECT_REC." ;";
					else
					$request .= " limit ".$skip.",999999999 ;";
				optimize($request);				
				$result = EA_sql_query($request);
				$nb=$skip;
				$nbzone = count($mdb);
				while ($row = EA_sql_fetch_assoc($result) and $stop==0)
					{
					if ($nb<$skip)
						$nb++;  // passer les lignes déjà traitées
					elseif (time()-$T0<$Max_time)  // on a encore le temps
						{
						$data=$enteteligne;
						if ($Destin=="B")
							$nbsepar=$nbzone;
						else 
							$nbsepar=$nbzone-1;  // pas de ; final en NIMEGUE
						for ($j = 1; $j < $nbzone; $j++)
							{
							$value = $row[$mdb[$j]['ZONE']];
							if (!isset($value))
								{ $data .= ''; }
							 elseif ($value == '0' || $value != '')
								{
							  	if ($mdb[$j]['ZONE']<>"PHOTOS")  // ne pas enlever les slash des chemins windows ! 
									{
									$value = stripslashes($value); // retire les slash protégeant les '  (\')
									}
								$value = preg_replace("/\r/", " ", $value);
								$value = preg_replace("/\n/", " ", $value);
								$donnee = $value;
								if ($enclosed == '')
									{
									$data .= $donnee;
									}
								 else
									{
									// protection des guillemets intérieurs en les redoublant
									$data .= $enclosed. str_replace($enclosed, $enclosed.$enclosed, $donnee). $enclosed;
									}
								}
							 else
								{
								$data .= '';
								}
							if ($j < $nbsepar)
								{
								$data .= $separator;
								}
							} // end for
						switch ($Destin) 
							{
							case 'T':
								// Download... donc NIMEGUE en iso et NON UTF-8
								echo ea_utf8_decode($data)."\r\n";  // pour mac : seulement \r  et pour linux \n !
								$nb++;
								break;
							case 'E':
								// HTML
								echo htmlspecialchars($data, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'<br />';
								$nb++;
								break;
							case 'B':
								// Backup en UTF8 
								if ($bytes+strlen($data)+2000>$Maxbytes)
									{
									$stop=2;
									$explic ="Taille maximale atteinte";
									}
									else
									{
									$bytes += fwrite($hof,$data."\r\n");
									$nb++;
									}
							}
						}  // if 
					else
						{
						$stop=1;
						$explic ="Temps maximum atteint";
						}
					} // while
				if (($stop==0) and ($nb < $nbdocs))
					{
					$stop=3;
					$explic ="Taille de requête maximale atteinte";				
					}				
				
				if ($Destin=='B')
					{
					if ($nb > 0)
						{
						//echo '</pre>';
						echo '<p>'.entier($nb-$skip).' actes de '.$ntype.$soustype.' exportés. ';
						if ($Destin=="B") echo '('.entier($bytes).' octets)';
						echo '</p>';
						writelog('Export '.$ntype.$soustype,$comdep,$nb);
						if ($stop>0)
							{
							// enregistrement fichier de passage de témoin
							$bytes += fwrite($hof,"EA_NEXT;".($file+1)."\r\n");
							$tof = fopen($tokenfile,"w");
							$token="EA_TOKEN;".$autokey.";".($file+1).";".($nb).";1;";
							fwrite($tof,$token."\r\n");
							fclose($tof);
							// 
							echo '<p><b>'.$explic.'.</b></p>';
							
							$fait = intval($nb/$nbdocs*100);
							echo '<p><div class="graphe"><strong class="barre" style="width:'.$fait.'%;">'.$fait.' %</strong></div></p>';
							echo '<p>Déjà '.entier($nb).' actes copiés.</p>';
							echo '<p>Il reste '.entier($nbdocs-$nb).' actes à traiter.</p>';
							$skip = $nb;
							$file = $file+1;
							echo '<p><a href="exporte.php?action=go&TypeActes='.$TypeActes.'&Destin='.$Destin.'&maxmega='.$maxmega.'&ComDep='.$comdep.'&autokey='.$autokey.'">
								<b>Continuez immédiatement avec le fichier suivant</b></a>';
							echo '<br />ou laissez le programme continuer seul dans quelques secondes.</p>';
							}	
						 else
							{
							if ($Destin=="B") 
								{
								// Fin de la chaine
								$tof = fopen($tokenfile,"w");
								$token="EA_TOKEN;".$autokey.";".($file).";".($nb).";0;";
								fwrite($tof,$token."\r\n");
								fclose($tof);
								$list_backups = get_last_backups();
								$list_backups[$TypeActes] = today();
								set_last_backups($list_backups);
								} 
							echo '<p>Transfert terminé.</p>';  
				//			if ($Destin=="B")
				//				echo '<p>'.$file.' fichier(s) peut/peuvent à présent être récupéré(s) via FTP dans le répertoire "_backup".</p>';
							}
						if ($Destin=="B") fclose($hof);  
						}
					 else
						{
						echo '<p>Aucun acte exporté.</p>';
						}
					}
					else
					{
					if ($Destin<>"B") 
						{
						if ($Destin=="E")
							echo '</pre>';
						if ($stop>0)
							{
						  echo '<p>Export interrompu pour des raisons techniques';
						  if ($stop==1)
						  	echo ' : Temps alloué au traitement dépassé.';
						  echo '</p>';
							}
						}
					writelog('Export '.$ntype.$soustype,$comdep,$nb);
					}
				} // nbdocs
			} // submitted ??
		}
  }
 else // missingargs
  //Si pas tout les arguments nécessaire, on affiche le formulaire
	{
	echo '<form method="post" enctype="multipart/form-data" action="">'."\n";
	echo '<h2 align="center">'.$titre.'</h2>';
	if ($userlevel < 8)
	  {
	  msg('Attention : Vous ne pourrez réexporter que les données dont vous êtes le déposant !','info');
	  }
	echo '<table cellspacing="0" cellpadding="0" border="0" align="center" summary="Formulaire">'."\n"; 
	if ($Destin=="B")
		{
		echo '<tr><td align="right">Derniers backups : &nbsp;</td><td>';
		echo show_last_backup("NMDV");
		echo "</td></tr>";
		echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		$mode = '2';
		$bouton = "SAUVEGARDER";
		}
		else
		{
		$mode = '0';
		$bouton = "EXPORTER";
		}
	form_typeactes_communes($mode);	
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	if ($Destin=="B")
		{
		$Max_mega = $Max_size/1024/1024;
		echo " <tr>\n";
		echo '  <td align="right">Taille maximale : &nbsp;</td>'."\n";
		echo '  <td><input type="text" name="maxmega" value="'.$Max_mega.'"size="2" /> ';
		echo ' Mb';
		echo '  </td>';
		}
	else
		{
		echo " <tr>\n";
		echo '  <td align="right">Années : &nbsp;</td>'."\n";
		echo '  <td>&nbsp;';
		echo '        de <input type="text" name="AnneeDeb" size="4" maxlength="4"/> ';
		echo '        à  <input type="text" name="AnneeFin" size="4" maxlength="4"/> (ces années comprises)';
		echo '  </td>';
		echo " </tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Destination : &nbsp;</td>'."\n";
		echo '  <td>';
		echo '        <br />';
		echo '        <input type="radio" name="Destin" value="E" checked="checked" />Ecran <br />';
		echo '        <input type="radio" name="Destin" value="T" />Fichier TXT téléchargé directement<br />';
		//echo '        <input type="radio" name="Destin" value="B" />Fichier BACKUP sur le serveur<br />';
		echo '        <br />';
		echo '  </td>';
		echo " </tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Format : &nbsp;</td>'."\n";
		echo '  <td>';
		echo '        <br />';
		echo '        <input type="radio" name="Format" value="NIM2" /> Nimègue V2<br />';
		echo '        <input type="radio" name="Format" value="NIM3" checked="checked" /> Nimègue V3<br />';
		echo '        <br />';
		echo '  </td>';
		}
	echo " </tr>\n";
	// echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="go" />';
	echo '  <input type="reset" value="Annuler" />'."\n";
	echo '  <input type="submit" value=" >> '.$bouton.' >> " />'."\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	}
if ($htmlpage)
  {
	echo '</div>';
	close_page(1,$root);
	}
?>
