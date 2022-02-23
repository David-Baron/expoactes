<?php

function init_page()
  {
  global $root,$userlevel,$htmlpage;

  open_page("Export d'une série d'utilisateur",$root);
	navadmin($root,"Export d'utilisateurs");

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

include("../tools/traitements.inc.php");

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

$regime   = getparam('regime');
$lelevel  = getparam('lelevel');
$rem      = getparam('rem');
$suppr    = getparam('suppr');
$condit   = getparam('condit');
$statut   = getparam('statut');
$xaction  = getparam('action');
$dtexpir   = getparam('dtexpir');
$conditexp = getparam('conditexp');
$conditpts = getparam('conditpts');
$ptscons 	= getparam('ptscons');

$Destin = 'T'; // Toujours vers fichier (sauf pour debug)
//$Destin = 'P'; // pour debug
if ($regime=="") $regime=-1;

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if ($xaction == 'submitted')
	{
	// Données postées
	if($lelevel >= 9 and $suppr=='Y')
		{
		init_page();
		menu_users('E');
		msg('Interdit de supprimer les administrateurs !');
		$missingargs=true;
		}
	}
 else
  {
  $missingargs=true;  // par défaut
	init_page();
	menu_users('E');
  }
if (! $missingargs)
	{
	$condlevel="";
	if ($lelevel<10)
		$condlevel = "level=".$lelevel;
	 else
		$condlevel = "level>=0";
	$condrem="";
	if ($condit<>"0")
		{
		$condrem = " and ".comparerSQL('REM',$rem,$condit);
		}
	$condreg="";
	if ($regime>=0)
		{
		$condreg = " and regime =".$regime;
		}
	$condsta="";
	if ($statut<>"0")
		{
		if ($statut=="X")
		  $condsta = " and dtexpiration<'".date("Y-m-d",time()-(DUREE_EXPIR*24*60*60))."'";
			else
		  $condsta = " and statut ='".$statut."'";
		}
	$sqlexpir = "";
	$baddt = 0;
	ajuste_date($dtexpir,$sqlexpir,$baddt);
	$condexp = "";
	if ($sqlexpir>'0000-00-00' and $conditexp<>"0")
		{
		$condexp = " and ".comparerSQL('dtexpiration',$sqlexpir,$conditexp);
		}
	$condpts="";	
	if ($ptscons<>"" and $conditpts<>"0")
		{
		$condpts = " and ".comparerSQL('pt_conso',$ptscons,$conditpts);
		}

	if ($suppr=='Y')
		{
		$request = "select count(*) from ".EA_UDB."_user3"
							 ." where ".$condlevel.$condreg.$condrem.$condsta.$condexp.$condpts." ;";
 //echo $request;
		$result = mysql_query($request,$u_db);
		$ligne = mysql_fetch_row($result);
		$nbrec = $ligne[0];
		if ($nbrec==0)
		  {
		  $suppr='N'; // retour à la procdure de base
		  }
		  else
		  {
			init_page();
			menu_users('E');
			echo '<form method="post" action="">'."\n";
			echo '<h2 align="center">Confirmation de la suppression</h2>';
			echo '<p class="message">Vous allez supprimer '.$nbrec.' utilisateurs !</p>';
			echo '<p class="message">';
			echo '<input type="hidden" name="action" value="submitted" />';
			echo '<input type="hidden" name="regime" value="'.$regime.'" />';
			echo '<input type="hidden" name="lelevel"  value="'.$lelevel.'" />';
			echo '<input type="hidden" name="rem"    value="'.$rem.'" />';
			echo '<input type="hidden" name="condit" value="'.$condit.'" />';
			echo '<input type="hidden" name="statut" value="'.$statut.'" />';
			echo '<input type="hidden" name="conditexp" value="'.$conditexp.'" />';
			echo '<input type="hidden" name="dtexpir" value="'.$dtexpir.'" />';
			echo '<input type="hidden" name="conditpts" value="'.$conditpts.'" />';
			echo '<input type="hidden" name="ptscons" value="'.$ptscons.'" />';
			echo '<input type="hidden" name="suppr"  value="Oui" />';
			echo '<input type="submit" value=" >> CONFIRMER EXPORT + SUPPRESSION >> " />'."\n";
			echo '&nbsp; &nbsp; &nbsp; <a href="index.php">Annuler</a></p>';
			echo "</form>\n";
			}
		}
  if ($xaction == 'submitted' and $suppr<>"Y")
	  {
		$request = "select * from ".EA_UDB."_user3"
							 ." where ".$condlevel.$condreg.$condrem.$condsta.$condexp.$condpts." ;";
//echo $request;
		$result = mysql_query($request,$u_db);
		$nbdocs = mysql_num_rows($result);
		$fields_cnt = mysql_num_fields($result);
		if ($nbdocs == 0)
			{
			init_page();
		  msg("Il n'y a aucun utilisateur avec ce critère !");
			echo '<p><a href="expsupuser.php">Retour</a></p>';
			}
		 else
		  {
		  if ($lelevel<10)
		  	$texlevel = $lelevel;
		  	else
		  	$texlevel = "ALL";
		  $filename = "USERS_".$texlevel;
			if ($regime>=0) $filename .= "_".$regime;
			if ($rem<>"")   $filename .= "_".$rem;
			if ($statut>=0) $filename .= "_".$statut;
			$filename  = strtr(remove_accent($filename),'-/ "','____');
			$filename  .= '.CSV';
			$mime_type = 'text/x-csv';
			if ($Destin=='T')
				{
				// Download
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
				}
			 else
				{
				// HTML
				init_page();
				echo '<pre>' . "\n";
				} // end download

			$nb=0;
			$zones= array('nom','prenom','email','login','hashpass','level','regime','solde','REM','dtexpiration','libre','ID','statut','dtcreation','pt_conso','maj_solde');
			while ($row = mysql_fetch_array($result))
				{
				$data="";
				$j=0;
				foreach ($zones as $zone) // ($j = 1; $j < $fields_cnt-$supp_fields; $j++)
					{
					if ($j > 0)
						{
						$data .= $separator;
						}
					$j++;
					if (!isset($row[$zone]))
						{ $data .= ''; }
					 elseif ($row[$zone] == '0' || $row[$zone] != '')
						{
						$row[$zone] = stripslashes($row[$zone]);
						$row[$zone] = preg_replace("/\015(\012)?/", "\012", $row[$zone]);
						if ($enclosed == '')
							{
							$data .= $row[$zone];
							}
						 else
							{
							$data .= $enclosed. str_replace($enclosed, $enclosed.$enclosed, $row[$zone]). $enclosed;
							}
						}
					 else
						{
						$data .= '';
						}
					} // end foreach
				$nb++;
				if ($Destin=='T')
					$data = ea_utf8_decode($data);  // retour à ISO
				
				echo $data."\r\n";  // pour mac : seulement \r  et pour linux \n !
				}
			if ($lelevel<10)
				$actie="Export";
				else
				{
				$actie="Backup";
				$list_backups = get_last_backups();
				$list_backups["U"] = today();
				set_last_backups($list_backups);
				}
			writelog($actie.' de fiches utilisateur',"USERS",$nb);
			if ($suppr == "Oui")
				{
				$request = "delete from ".EA_UDB."_user3"
									." where level=".$lelevel.$condreg.$condrem.$condsta.$condexp.$condpts." ;";
				$result = mysql_query($request,$u_db);
     //echo $request;
				$nb = mysql_affected_rows();
				if ($nb > 0)
					{
					writelog('Suppression d\'utilisateurs',"USERS",$nb);
					}
				} // supprimer pour de bon
      } // nbdocs
		} // submitted ??
  }
 else // missingargs
  //Si pas tout les arguments nécessaire, on affiche le formulaire
	{
	echo '<form method="post" enctype="multipart/form-data" action="">'."\n";
	echo '<h2 align="center">'."Export/Suppression d'utilisateurs".'</h2>';
	echo '<table cellspacing="0" cellpadding="0" border="0" align="center" summary="Formulaire">'."\n";
	echo '<tr><td align="right">Dernier backup : &nbsp;</td><td>';
	echo show_last_backup("U");
	echo "</td></tr>";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Droits d'accès : &nbsp;</td>\n";
	echo '  <td>';
	lb_droits_user($lelevel,1); // avec All
	echo '  </td>';
	echo " </tr>\n";
	if (GEST_POINTS>0)
		{
		echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo "  <td align=right>Régime (points) : &nbsp;</td>\n";
		echo '  <td>';
		lb_regime_user($regime,1);
		echo '  </td>';
		echo " </tr>\n";
    }
	 else
		{
		echo ' <tr><td colspan="2">';
		echo '<input type="hidden" name="regime" value="-1" />';
		echo "</td></tr>\n";
		}

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Commentaire : &nbsp;</td>\n";
	echo '  <td>';
	listbox_trait('condit',"TST",$condit);

	echo ' <input type="text" name="rem" size="50" value="'.$rem.'" />';
	echo "</td>\n";
	echo " </tr>\n";

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Statut : &nbsp;</td>'."\n";
	echo '  <td>';	
	lb_statut_user($statut,3);
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align='right'>Date expiration : &nbsp;</td>\n";
	echo '  <td>';
	listbox_trait('conditexp',"NTS",$conditexp);
	echo '<input type="text" name="dtexpir" size="10" value="'.$dtexpir.'" />'."</td>\n";
	echo " </tr>\n";

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align='right'>Points consommés : &nbsp;</td>\n";
	echo '  <td>';
	listbox_trait('conditpts',"NTS",$conditpts);
	echo '<input type="text" name="ptscons" size="5" value="'.$ptscons.'" />'."</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">Suppression : &nbsp;</td>'."\n";
	echo '  <td>';
	echo '        <br />';
	echo '        <input type="radio" name="suppr" value="N" checked="checked" />Non<br />';
	echo '        <input type="radio" name="suppr" value="Y" />Supprimer les utilisateurs exportés<br />';
	echo '        <br />';
	echo '  </td>';
	echo " </tr>\n";
	// echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <input type="reset" value="Annuler" />'."\n";
	echo '  <input type="submit" value=" >> SUITE >> " />'."\n";
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
