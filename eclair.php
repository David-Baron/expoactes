<?php
// Copyright (C) : André Delacharlerie, 2005-20010
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");

if (!defined("ECLAIR_LOG")) define ("ECLAIR_LOG",0);

$root = "";
$path = "";
$MT0 = microtime_float();
$cptrec = 0;
$cptper = 0;

$xcom = getparam('xcom');
$xdep = getparam('xdep');
$xtyp = getparam('xtyp');
$xini = getparam('xini');

$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml">';
echo '<head>';
echo '<meta name="robots" content="nofollow" />';
echo '<meta name="generator" content="ExpoActes" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
echo '</head>';
echo '<body>';


if (ECLAIR_AUTORISE==1)
	{
	if (($xcom=="") or ($xtyp==""))
		{
		// Lise des communes 

		$request ="SELECT TYPACT as TYP, sum(NB_TOT) as CPT, COMMUNE, DEPART"
						. " FROM ".EA_DB."_sums "
						. ' GROUP BY COMMUNE, DEPART, TYP ';

		optimize($request);
		$result = mysql_query($request);
		$nblign = mysql_num_rows($result);

		echo"<p>LISTE-URL</p>\n";
		echo "<ul>\n";
		while ($ligne = mysql_fetch_row($result))
			{
				echo '<li><a href="'.'eclair.php?xtyp='.$ligne[0].'&xcom='.urlencode($ligne[2]).'&xdep='.urlencode($ligne[3]).'">'.typact_txt($ligne[0]).' de '.$ligne[2].' ['.$ligne[3].']</a> ('.$ligne[1].' actes)</li>'."\n";
			}
		echo '</ul>'."\n";

		} // Liste des communes
	 else
		{
		// Traitement d'une commune praticulière
		switch ($xtyp)
			{
			case "N":
				$ntype = "de naissance";
				$table = EA_DB."_nai3";
				$zones = "NOM,P_NOM,M_NOM,T1_NOM,T2_NOM";
				break;
			case "D":
				$ntype = "de décès";
				$table = EA_DB."_dec3";
				$zones = "NOM,C_NOM,P_NOM,M_NOM,T1_NOM,T2_NOM";
				break;
			case "V":
				$ntype = "divers";
				$table = EA_DB."_div3";
				$zones = "NOM,C_NOM,P_NOM,M_NOM,CP_NOM,CM_NOM,T1_NOM,T2_NOM,T3_NOM,T4_NOM";
				break;
			case "M":
				$ntype = "de mariage";
				$table = EA_DB."_mar3";
				$zones = "NOM,C_NOM,P_NOM,M_NOM,CP_NOM,CM_NOM,T1_NOM,T2_NOM,T3_NOM,T4_NOM";
				break;
			default:
				$oktype=false;
			}

		// Extraction de la commune voulue
		$cond = "";
		if ($xini<>"") $cond .= " and NOM like '".$xini."%'";

		$request  ="SELECT year(LADATE), ".$zones
							. " FROM ".$table  //. " IGNORE INDEX (COM_DEP)"
							. " WHERE COMMUNE = '".sql_quote($xcom)."'".$cond;

		$result = mysql_query($request);

		optimize($request);

		$cptrow= mysql_num_rows($result);

		if ($cptrow > ECLAIR_MAX_ROW)
			{
			// Trop de lignes dans la commune => traiter par initiale 
				$lgi  = strlen($xini)+1;
				$initiale = "";
				if ($lgi>0)
					$initiale = " and left(NOM,$lgi-1)= '".sql_quote($xini)."'" ;

			$request = "select left(NOM,$lgi), count(*)"
								 ." from $table "
								 ." where COMMUNE = '".sql_quote($xcom)."' and DEPART = '".sql_quote($xdep)."'".$initiale
								 ." group by left(NOM,$lgi)";

			optimize($request);
			$result = mysql_query($request);
			$nblign = mysql_num_rows($result);

			if ($nblign==1 and $lgi>3)  // Permet d'éviter un bouclage si le nom devient trop petit
				{
				$request = "select NOM, count(NOM), min(NOM), max(NOM)"
									 ." from $table "
									 ." where COMMUNE = '".sql_quote($xcom)."' and DEPART = '".sql_quote($xdep)."'".$initiale
									 ." group by NOM";
				optimize($request);
				$result = mysql_query($request);
				}
			echo"<p>LISTE-URL</p>\n";
			echo "<ul>\n";
			while ($ligne = mysql_fetch_row($result))
				{
				echo '<li><a href="'.'eclair.php?xtyp='.$xtyp.'&xcom='.urlencode($xcom).'&xdep='.urlencode($xdep).'&xini='.urlencode($ligne[0]).'">'.typact_txt($xtyp).' ['.$xdep.'] de '.$xcom.' initiale '.$ligne[0].'</a> ('.$ligne[1].' actes)</li>'."\n";
				}
			echo '</ul>'."\n";
			}
		 else
			{
			// On y va pour la liste éclair	
			// Creation table temporaire

			$request = "CREATE TEMPORARY TABLE  tmp_eclair (ANNEE varchar(4), PATRO varchar(25)) DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci";
			$res = mysql_query($request);
			//echo '<p>'.$request;
			if (!($res === true))
				{
				echo '<font color="#FF0000"> Erreur </font>';
				echo '<p>'.mysql_error().'<br>'.$request.'</p>';
				die();
				}
			// Insertion des patronymes dans la table temporaire
			$k = 0;
			while ($row = mysql_fetch_row($result))
				{
				if ($row[0]==0) 
					$annee="NULL";
				else
					$annee = "'".$row[0]."'";
				$insert = "";
				for ($i=1;$i<count($row);$i++)
					{
					if ($row[$i]<>"")
						{
						if ($insert<>"") $insert .= ",";
						$insert .= "(".$annee.",'".sql_quote($row[$i])."')";
						$k++;
						}
					}
				$reqmaj = "INSERT into tmp_eclair VALUES ".$insert;		
				$res = mysql_query($reqmaj);
				//echo '<p>'.$reqmaj;
				if (!($res === true))
					{
					echo '<font color="#FF0000"> Erreur </font>';
					echo '<p>'.mysql_error().'<br>'.$reqmaj.'</p>';
					die();
					}
				}
			// Extraction du décompte des patronymes

			$request  ="SELECT count(*), min(ANNEE), max(ANNEE), PATRO FROM tmp_eclair group by PATRO";

			$result = mysql_query($request);

			optimize($request);

			echo"<p>LISTE-ECLAIR</p>\n";
			echo"<p>Attention : cette liste comprend les patronymes des interessés et des témoins (père, mère, ancien conjoint, parrain,..).</p>";
			echo"<p>Commune : $xcom</p>\n";
			echo"<p>Région : $xdep</p>\n";
			echo"<p>Type : ".typact_txt($xtyp)."</p>\n";

			echo"<p>\n";
      $cptrec = 0;
      $cptper = 0;
			while ($row = mysql_fetch_row($result))
				{

				echo "<br />".$row[3].";".$row[1].";".$row[2].";".$row[0].";\n";	
				$cptrec++;
				$cptper=$cptper+$row[0];
				}
			echo"</p>\n";

			}
		}
	echo '<p>Durée totale  : '.round(microtime_float()-$MT0,3).' sec.</p>'."\n";
	echo '<p>Individus cités : '.$cptper.'.</p>'."\n";
	echo '<p>Patronymes  : '.$cptrec.'.</p>'."\n";
	}
 else
	{
	echo '<p>NE PAS INDEXER CE SITE</p>';
	}
echo '</body></html>';
if (ECLAIR_LOG>0)
	{
	$array_server_values = $_SERVER;
	$Vua   = $array_server_values['HTTP_USER_AGENT'];
	$Vip   = $array_server_values['REMOTE_ADDR'];
	$hf = @fopen("_logs/eclair-".date('Y-m').".log","a");
	$dur = round(microtime_float()-$MT0,3)*1000;
	@fwrite($hf,now().";".$Vip.";".$xtyp.";".$xcom.";".$xdep.";".$xini.";".$cptrec.";".$dur.";".$Vua.chr(10));
	@fclose($hf);
	}

?>

