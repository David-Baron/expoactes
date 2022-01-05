<?php

$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = 1;
$program = "tab_mari.php";

pathroot($root,$path,$xcomm,$xpatr,$page);

$comdep  = html_entity_decode($xcomm, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);
$xord  = getparam('xord');
if ($xord == "")
  {$xord = "D";}   // N = Nom, D = dates, F = Femme
$pg = getparam('pg');
if ($pg<>"") $page=$pg;
$xannee = "";
if (mb_substr($xpatr,0,1) == "!")
	{
	$xannee = mb_substr($xpatr,1);
	}
$userlogin="";
$gid=0;
$note = geoNote($Commune,$Depart,'M');

if ($xpatr=="" or mb_substr($xpatr,0,1) == "_")
		// Lister les patronymes avec groupements si trop nombreux
		{
		$userlevel=logonok(2);
		while ($userlevel<2)
			{
			login($root);
			}
		open_page($xcomm." : ".$admtxt."Mariages",$root);
		navigation($root,ADM+2,'M',$xcomm);
		zone_menu(ADM,$userlevel);
		echo '<div id="col_main">'."\n";
		liste_patro_2($program,$path,$xcomm,$xpatr,"Mariages",EA_DB."_mar3","",$gid,$note);
		}
   else
    {
		$userlevel=logonok(3);
		while ($userlevel<3)
			{
			login($root);
			}
		$userid = current_user("ID");
		open_page($xcomm." : ".$admtxt."Table des mariages",$root);
		navigation($root,ADM+3,'M',$xcomm,$xpatr);
		zone_menu(ADM,$userlevel);
		echo '<div id="col_main">'."\n";
    // **** Lister la table des actes
    echo '<h2>Actes de mariage</h2>';

    echo '<p>';
    echo 'Commune/Paroisse : <a href="'.mkurl($path.'/'.$program,$xcomm).'"><b>'.$xcomm.'</b></a>'.geoUrl($gid).'<br />';
		if ($note<>'')
			echo "</p><p>".$note."</p><p>";
		if (mb_substr($xpatr,0,1) == "!")
			{
			echo 'Année : <b>'.$xannee.'</b>';
			$preorder = "act.NOM";
			$nameorder = "Patronymes";
			}
			else
			{
			echo 'Patronyme : <b>'.$xpatr.'</b>';
			$preorder = "PRE";
			$nameorder = "Prénoms";
			}
    echo '</p>';

    $baselink = $path.'/'.$program.'/'.urlencode($xcomm).'/'.urlencode($xpatr);
    if ($xord =="N")
      {
      $order = "act.NOM, PRE, LADATE";
      $hdate = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=D').'">Dates</a>';
      $hnoms = '<b>Epoux</b>';
      $hfemm = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=F').'">Epouses</a>';
      $baselink = mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=N');
      }
     elseif ($xord =="F")
      {
      $order = "C_NOM, C_PRE, LADATE";
      $hnoms = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=N').'">Epoux</a>';
      $hdate = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=D').'">Dates</a>';
      $hfemm = '<b>Epouses</b>';
      $baselink = mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=F');
      }
     else
      {
      $order = "LADATE, act.NOM, C_NOM";
      $hnoms = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=N').'">Epoux</a>';
      $hdate = '<b>Dates</b>';
      $hfemm = '<a href="'.mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=F').'">Epouses</a>';
      $baselink = mkurl($path.'/'.$program,$xcomm,$xpatr,'xord=D');
      }
	if ($xannee<>"")
		$condit = " and year(act.LADATE)=".$xannee;
		else
		$condit = " and (act.NOM  = '".sql_quote($xpatr)."' or C_NOM  = '".sql_quote($xpatr)."')";

	if ($Depart<>"")
		$condDep=" and DEPART = '".sql_quote($Depart)."'";
		else
		$condDep="";

	$request = "select act.NOM, act.PRE, C_NOM, C_PRE, DATETXT, act.ID, act.DEPOSANT"
				." from ".EA_DB."_mar3 as act"
			." where COMMUNE = '".sql_quote($Commune)."'".$condDep
				.$condit." order by ".$order;

	optimize($request);
	$result = EA_sql_query($request);
	$nbtot = EA_sql_num_rows($result);

	$limit="";
	$listpages="";
	pagination($nbtot,$page,$baselink,$listpages,$limit);

  if ($limit<>"")
    {
		$request = $request.$limit;
		$result = EA_sql_query($request);
		$nb = EA_sql_num_rows($result);
		}
	else
	  {
	  $nb = $nbtot;
	  }

	if ($nb > 0)
	  {
	  if ($listpages<>"")
	    echo '<p>'.$listpages.'</p>';
	  $i = 1+($page-1)*iif((ADM>0),MAX_PAGE_ADM,MAX_PAGE);
	  echo '<table summary="Liste des patronymes">';
	  echo '<tr class="rowheader">';
	  echo '<th> Tri : </th>';
	  echo '<th>'.$hdate.'</th>';
	  echo '<th>'.$hnoms.'</th>';
	  echo '<th>'.$hfemm.'</th>';
	  echo '<th>&nbsp;</th>';
		if (ADM==10) echo '<th>Déposant</th>';
	  echo '</tr>';

    $xpatr = remove_accent($xpatr);
	  while ($ligne = EA_sql_fetch_row($result))
		{
		echo '<tr class="row'.(fmod($i,2)).'">';
		echo '<td>'.$i.'. </td>';
		echo '<td>&nbsp;'.annee_seulement($ligne[4]).'&nbsp;</td>';
		if (remove_accent($ligne[0])==$xpatr)
		  {echo '<td>&nbsp;<b>'.$ligne[0].' '.$ligne[1].'</b></td>';}
		 else
		  {echo '<td>&nbsp;'.$ligne[0].' '.$ligne[1].'</td>';}
		if (remove_accent($ligne[2])==$xpatr)
		  {echo '<td>&nbsp;<b>'.$ligne[2].' '.$ligne[3].'</b></td>';}
		 else
		  {echo '<td>&nbsp;'.$ligne[2].' '.$ligne[3].'</td>';}

		echo '<td>&nbsp;<a href="'.$path.'/acte_mari.php?xid='.$ligne[5].'&xct='.ctrlxid($ligne[0],$ligne[1]).'">'."Détails".'</a>&nbsp;</td>';
    if (ADM==10)
      {
      actions_deposant($userid,$ligne[6],$ligne[5],'M');
      }
		echo '</tr>';
		$i++;
		}
	  echo '</table>';
	  if ($listpages<>"")
	    echo '<p>'.$listpages.'</p>';
		show_solde();
	  }
	 else
	  {
	  msg('Aucun acte trouvé');
	  }
  }

echo '</div>';
close_page();
?>

