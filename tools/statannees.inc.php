<?php
include("function.php");
include("adlcutils.php");
include("actutils.php");
include("loginutils.php");

function barre($valeur, $max)
	{
	$lgmax = 100;
	$chaine = "";
	$long = $valeur/$max*$lgmax;
	$chaine  = '<div class="histo"><strong class="barre" style="width:'.$long.'%;">'.$valeur.'</strong></div>';
	return $chaine;
	}
	
$root = "";
$path = "";

//**************************** ADMIN **************************

$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";
if (ADM==10)
	$lvl=5;
	else
	$lvl=2;
$userlevel=logonok($lvl);
while ($userlevel<$lvl)
  {
  login($root);
  }

$userid=current_user("ID");

$missingargs=false;
$oktype=false;

$TypeActes  = getparam('xtyp');
$xtdiv      = getparam('tdiv');
$comdep  = html_entity_decode(getparam('comdep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

// Données postées
if(empty($TypeActes))
	{
	msg('Vous devez préciser le type des actes.');
	$missingargs=true;
	}
if(empty($Commune))
	{
	msg('Vous devez préciser une commune.');
	$missingargs=true;
	}
if (! $missingargs)
	{
	$oktype=true;
	$condtdiv="";
	$soustype="";
	$linkdiv="";
	switch ($TypeActes)
		{
		case "N":
			$ntype = "naissance";
			$table = EA_DB."_nai3";
			$program = "tab_naiss.php";
			break;
		case "V":
			$ntype = "types divers";
			$table = EA_DB."_div3";
			$program = "tab_bans.php";
			$pos = isin($comdep,"];");
			if (($pos>0))
				{
				$Depart  = departementde(mb_substr($comdep,1,$pos));
				$stype = mb_substr($comdep,$pos+2);
				$condtdiv = " and (LIBELLE='".sql_quote($stype)."')";
				$soustype = " (".$stype.")";
				$linkdiv = ";".$stype;
				}
			break;
		case "M":
			$ntype = "mariage";
			$table = EA_DB."_mar3";
			$program = "tab_mari.php";
			break;
		case "D":
			$ntype = "décès";
			$table = EA_DB."_dec3";
			$program = "tab_deces.php";
			break;
		}
	$xcomm = $Commune.' ['.$Depart.']'.$linkdiv;;
 
	$title = $Commune." : Répartition des actes de ".$ntype.$soustype;

	open_page($title,$root);
	if (ADM<10)
		navigation($root,ADM+2,'A',$Commune);
		else
		navadmin($root,$title);
		
	zone_menu(ADM,$userlevel);

	echo '<div id="col_main_adm">';
	echo '<h2>'.$title.'</h2>';

	$request = "select year(ladate) as ANNEE,count(*) as CPT from ".$table.
						 " where COMMUNE='".sql_quote($Commune)."' and DEPART='".sql_quote($Depart)."'".$condtdiv." group by year(ladate) ;";
	//echo $request;
	$result = mysql_query($request);
	$k = 0;
	$annee = array(0);
	$cptan = array(0);
	$max = 0;
	while ($ligne = mysql_fetch_array($result))
		{
		$k++;
		$annee[$k]=$ligne['ANNEE'];
		$cptan[$k]=$ligne['CPT'];
		if ($cptan[$k]>$max) $max = $cptan[$k];
		}
	$nban = $k;
		
	echo '<table border="0">'."\n";
	echo "<tr><th>Années</th><th>Nombres d'actes</th></tr>";
	for ($k=1; $k<=$nban; $k++)
		{
		//echo $k."-".$annee[$k]."-".$cptan[$k];
		if ($annee[$k]==0)
			{
			echo '<tr>'."\n";
			echo '<td>'.'<b><a href="'.mkurl($path.'/'.$program,$xcomm,'!'.$annee[$k]).'">Sans date</a></b>'.'</td>'."\n";
			echo '<td>'.barre($cptan[$k],$max).'</td>'."\n";
			echo '</tr">'."\n";
			$k++;
			}
		elseif ($annee[$k]>$annee[$k-1]+3 and $annee[$k-1]<>0)
			{
			echo '<tr><td>...</td><td></td></tr>';
			echo '<tr><td>'.($annee[$k]-$annee[$k-1]-1).' années</td><td></td></tr>';
			echo '<tr><td>...</td><td></td></tr>';
			}
		elseif ($annee[$k]>$annee[$k-1]+1 and $annee[$k-1]<>0)
			{
		  for ($kk=1;$kk<=($annee[$k]-$annee[$k-1]-1);$kk++)
				{
				echo '<tr>'."\n";
				$anneezero = ($annee[$k-1]+$kk);
				if ($anneezero%10==0)
					echo '<td><b>'.$anneezero.'</b></td>'."\n";
					else
					echo '<td>'.$anneezero.'</td>'."\n";			
				//echo '<tr><td>'.($annee[$k-1]+$kk).'</td>';
				echo '<td>'.barre(0,$max).'</td><td></td></tr>';
				}
			}
		echo '<tr>'."\n";
		$link = '<a href="'.mkurl($path.'/'.$program,$xcomm,'!'.$annee[$k]).'">'.$annee[$k].'</a>';
		if ($annee[$k]%10==0)
			echo '<td><b>'.$link.'</b></td>'."\n";
			else
			echo '<td>'.$link.'</td>'."\n";			
		echo '<td>'.barre($cptan[$k],$max).'</td>'."\n";
		echo '</tr>'."\n";
		}
	echo '</table>'."\n";

  }
echo '</div>';
close_page(1,$root);
?>