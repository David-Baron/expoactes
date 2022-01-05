<?php
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');

function show_grp($grp,$current,$barre)
	{
	if ($barre)
		echo ' | ';	  
	if ($grp==$current)
		echo '<strong>'.$grp.'</strong>';
	 else
		echo '<a href="gest_params.php?grp='.$grp.'">'.$grp.'</a>';
	}			

function alaligne($texte)
  {
  // insert des BR pour provoquer des retour à la ligne
  $order   = array("\r\n", "\n", "\r");
	$replace = '<br />';
	// Traitement du premier \r\n, ils ne seront pas convertis deux fois.
	return str_replace($order, $replace, $texte);
  }

$root = "";
$path = "";

$js_show_help = "";
$js_show_help .= "function show(id) \n ";
$js_show_help .= "{ \n ";
$js_show_help .= "	el = document.getElementById(id); \n ";
$js_show_help .= "	if (el.style.display == 'none') \n ";
$js_show_help .= "	{ \n ";
$js_show_help .= "		el.style.display = ''; \n ";
$js_show_help .= "		el = document.getElementById('help' + id); \n ";
$js_show_help .= "	} else { \n ";
$js_show_help .= "		el.style.display = 'none'; \n ";
$js_show_help .= "		el = document.getElementById('help' + id); \n ";
$js_show_help .= "	} \n ";
$js_show_help .= "} \n ";


//**************************** ADMIN **************************

pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";
$userlevel=logonok(9);
while ($userlevel<9)
  {
  login($root);
  }

open_page("Paramétrage du logiciel",$root,$js_show_help);
navadmin($root,"Paramétrage du logiciel");

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

echo '<div id="col_menu">';
form_recherche();
menu_admin($root,$userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

menu_software('P');

echo '<h2>Paramétrage du site "'.SITENAME.'"</h2>';


$ok = false;
$missingargs=false;

$xgroupe  = getparam('grp');
$xconfirm = getparam('xconfirm');

if ($xgroupe == '')
	{
	// Données postées
	$xgroupe="Affichage";
  $missingargs=true;  // par défaut
  }

//$request = "select distinct groupe from ".EA_DB."_params order by groupe";
$request = "select distinct groupe from ".EA_DB."_params where not (groupe in ('Hidden','Deleted')) order by groupe";
$result = EA_sql_query($request);
//echo $request;
$barre = false;
echo '<p align="center"><strong>Paramètres : </strong>';
while ($row = EA_sql_fetch_array($result))
  {
	show_grp($row["groupe"],$xgroupe,$barre);
	$barre = true;
  }
echo ' || <a href="update_params.php">Backup</a>';
echo '</p>';

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if (!$missingargs)
	{
	$oktype=true;

	if ($xconfirm == 'confirmed')
		{
		// *** Vérification des données reçues
		$parnbr = getparam("parnbr");
		$i = 1;
		$cpt = 0;
		while ($i <= $parnbr)
			{
			$parname = getparam("parname$i");
			$parvalue = htmlentities(getparam("parvalue$i"), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
			if ($parvalue == "")
				{
				$request = "select * from ".EA_DB."_params where param = '".$parname."'";
				$result = EA_sql_query($request);
				$row = EA_sql_fetch_array($result);
				if ($row["type"]=="B")
					$parvalue=0;
				}
			$request = "update ".EA_DB."_params set valeur = '".sql_quote($parvalue)."' where param = '".$parname."'";
		//echo "<p>".$request;
			optimize($request);
			$result = EA_sql_query($request);
			$cpt += EA_sql_affected_rows();
			$i++;
			}	
		if ($cpt > 0)
		  msg("Sauvegarde : ".$cpt." paramètre(s) modifié(s).","info");
		}
	}	

$request = "select * from ".EA_DB."_params where groupe='".$xgroupe."' order by ordre";
optimize($request);
$result = EA_sql_query($request);
//echo $request;
echo '<h2 align="center">'.$xgroupe.'</h2>';
if ($xgroupe=="Mail")
	{
	echo '<p align="center"><a href="test_mail.php"><b>Tester l\'envoi d\'e-mail</b></a></p>';
	}
if ($xgroupe=="Utilisateurs" and isset($udbname))
	{
	msg('ATTENTION : Base des utilisateurs déportée sur '.$udbaddr."/".$udbuser."/".$udbname."/".EA_UDB."</p>",'info');
	}
	
echo '<form method="post" action="">'."\n";
echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">'."\n";
$i=0;
$prog = "gest_params.php?grp=".$xgroupe;
while ($row = EA_sql_fetch_array($result))
	{
	$i++;
	echo ' <tr>'."\n";
	echo '  <td align="right"><b>'.$row["libelle"]."</b>";
	echo ' <a href="'.$prog.'" id="help'.$i.'" onclick="javascript:show(\'aide'.$i.'\');return false;"><b>(?)</b></a>';
	echo '<span id="aide'.$i.'" style="display: none" class="aide"><br />'.$row["param"]." : ".alaligne($row["aide"]).'</span>';
	echo " : </td>\n";
	echo '  <td>';
	echo '<input type="hidden" name="parname'.$i.'"  value="'.$row["param"].'" />'."\n";
	switch ($row["type"])
		{
		case "B":
			$size = 1;
			break;
		case "N":
		case "L":
			$size = 5;
			$maxsize = 5;
			break;
		case "C":
			$size = 50;
			$maxsize = 250;
			break;
		case "T":
			$size = 1000;
			$maxsize = 0;
			break;
		}
	if ($row["type"]=="B")
		{
		echo '<input type="checkbox" name="parvalue'.$i.'" value="1"'.checked($row["valeur"]).' />';
		}
	 elseif ($row["type"]=="L")
		{
		$leschoix=explode(";",$row["listval"]);
		echo '<select name="parvalue'.$i.'">'."\n";
		foreach($leschoix as $lechoix)
			{
			echo '<option '.selected_option(intval(mb_substr($lechoix,0,isin($lechoix,"-",0)-1)),$row["valeur"]).'>'.$lechoix.'</option>'."\n";
			}
		echo " </select>\n";
		}
	 else
		{
		if ($size <= 100)
			{
			echo '<input type="text" name="parvalue'.$i.'" size="'.$size.'" maxlength="'.$maxsize.'" value="'.$row["valeur"].'" />';
			}
		 else
			{
			echo '<textarea name="parvalue'.$i.'" cols="40" rows="6">'.html_entity_decode($row["valeur"], ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</textarea>';
			}
		}
	echo '  </td>';
	echo " </tr>\n";
	}
echo ' <tr><td align="right">'."\n";
echo '<input type="hidden" name="parnbr"  value="'.$i.'" />'."\n";
echo '<input type="hidden" name="grp"  value="'.$xgroupe.'" />'."\n";
echo '<input type="hidden" name="xconfirm" value="confirmed" />'."\n";
echo '<a href="index.php">Annuler</a> &nbsp; &nbsp;</td>'."\n";
echo '<td><input type="submit" value="ENREGISTRER" /></td>'."\n";
echo "</tr></table>\n";
echo "</form>\n";
echo '</div>';
close_page(1,$root);
?>
