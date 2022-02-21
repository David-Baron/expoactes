<?php
// Intègration du tri sur ID     :   v. 1 	14 mars 2014 11:05 	Serge Milani - Original Yannick H -Yannick Quéré  (demande Yvon Leriche ) 6 mars 2014
// Intègration du tri sur ID     :   v 3.10x 	06 octobre 2014  Emmanuel Lethrosne  : correction et amélioration d'écriture,  Version en fonction de la compatibilité d'ExpoActe
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');

$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = 1;

pathroot($root,$path,$xcomm,$xpatr,$page);

$xord  = getparam('xord');
if ($xord == "")
  {$xord = "N";}   // N = Nom
$page  = getparam('pg');
$init  = getparam('init');


$userlogin="";
$userlevel=logonok(9);
while ($userlevel<9)
  {
  login($root);
  }

open_page(SITENAME." : Liste des utilisateurs enregistrés",$root);

navadmin($root,"Liste des utilisateurs");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root,$userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

// Lister les actes

menu_users('L');

echo '<h2>Utilisateurs enregistrés du site '.SITENAME.'</h2>';

if (isset($udbname))
	{
	msg('ATTENTION : Base des utilisateurs déportée sur '.$udbaddr."/".$udbuser."/".$udbname."/".EA_UDB."</p>",'info');
	}

$baselink = $root.'/admin/listusers.php';
//$request = "select distinct upper(left(NOM,1)) as init from ".EA_UDB."_user3 order by init";
// Sélectionner et grouper sur initiale utilisateur et ascii(initiale), ordonner code ascii ascendant pour avoir + grand code (accentué) en dernier
$request = "select  alphabet.init  from ( select upper(left(NOM,1)) as init,ascii(upper(left(NOM,1)))  as oo from ".EA_DB."_user3 group by init,oo  order by init , oo asc) as alphabet group by init";

$result = EA_sql_query($request,$u_db);
$alphabet = "";
while ($row = EA_sql_fetch_row($result))
  {
  if ($row[0]==$init)
  	$alphabet .= '<b>'.$row[0].'</b> ';
  else
  	$alphabet .= '<a href="'.$baselink.'?xord='.$xord.'&init='.$row[0].'">'.$row[0].'</a> ';
  }
echo '<p align="center">'.$alphabet.'</p>';

if ($init=="")
  $initiale = '';
 else
  $initiale = '&init='.$init;

$hlogin = '<a href="'.$baselink.'?xord=L'.$initiale.'">Login</a>';
$hnoms  = '<a href="'.$baselink.'?xord=N'.$initiale.'">Nom</a>';
$hid    = '<a href="'.$baselink.'?xord=I'.$initiale.'">ID</a>';
$hacces = '<a href="'.$baselink.'?xord=A'.$initiale.'">Niveau d\'accès</a>';
$hstatu = '<a href="'.$baselink.'?xord=S'.$initiale.'">Statut</a>';
$hsolde = '<a href="'.$baselink.'?xord=D'.$initiale.'">Solde</a>';
$hrecha = '<a href="'.$baselink.'?xord=R'.$initiale.'">Rechargé</a>';
$hconso = '<a href="'.$baselink.'?xord=C'.$initiale.'">Consommés</a>';
$baselink = $baselink.'?xord='.$xord.$initiale;

switch ($xord)
		{
		case "L":
			$order = "LOGIN, NOM";
			$hlogin = '<b>Login</b>';
			break;
		case "A":
			$order = "LEVEL desc";
			$hacces = '<b>Niveau d\'accès</b>';
			break;
		case "I":
			$order = "ID desc";
			$hid = '<b>ID</b>';
			break;
		case "S":
			$order = "find_in_set(STATUT,'W,A,B,N,X')";
			$hstatu = '<b>Statut</b>';
			break;
		case "D":
			$order = "SOLDE desc, REGIME asc";
			$hsolde = '<b>Solde</b>';
			break;
		case "R":
			$order = "MAJ_SOLDE desc";
			$hrecha = '<b>Rechargé</b>';
			break;
		case "C":
			$order = "PT_CONSO desc";
			$hconso = '<b>Consommés</b>';
			break;
		case "N":
		default:
			$order = "NOM, PRENOM, LOGIN";
			$hnoms = '<b>Nom</b>';
		}

	if ($init=="")
	  $condit = "";
	 else
	  $condit = " where NOM like '".$init."%' ";
	 

	$request = "select NOM, PRENOM, LOGIN, LEVEL, ID, EMAIL, REGIME, SOLDE, MAJ_SOLDE, if(STATUT='N',if(dtexpiration<'".date("Y-m-d",time())."','X',STATUT),STATUT) as STATUT, PT_CONSO"
				." from ".EA_UDB."_user3 "
				.$condit
				." order by ".$order;
  //echo $request;
	$result = EA_sql_query($request,$u_db);
	$nbtot = EA_sql_num_rows($result);

	$limit="";
	$listpages="";
	pagination($nbtot,$page,$baselink,$listpages,$limit);

  if ($limit<>"")
    {
		$request = $request.$limit;
		$result = EA_sql_query($request,$u_db);
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
	  $i = 1+($page-1)*MAX_PAGE_ADM;
	  echo '<table summary="Liste des utilisateurs">';
	  echo '<tr class="rowheader">';
	  echo '<th> Tri : </th>';
	  echo '<th>'.$hlogin.'</th>';
	  echo '<th>'.$hid.'</th>';
	  echo '<th>'.$hnoms.'</th>';
	  echo '<th>'.$hacces.'</th>';
	  echo '<th>'.$hstatu.'</th>';
	  if (GEST_POINTS>0)
	    {
	  	echo '<th>'.$hsolde.'</th>';
	  	echo '<th>'.$hrecha.'</th>';
	  	echo '<th>'.$hconso.'</th>';
	  	}
	  echo '<th> </th>';
	  echo '</tr>';

    
	  while ($ligne = EA_sql_fetch_row($result))
			{
			echo '<tr class="row'.(fmod($i,2)).'">';
			echo '<td>'.$i.'. </td>';
			echo '<td>'.$ligne[2].' </td>';
			echo '<td>'.$ligne[4].' </td>';
			$lenom = $ligne[0].' '.$ligne[1];
			if (trim($lenom)=="") $lenom = '&lt;non précisé&gt;';
			echo '<td><a href="'.$root.'/admin/gestuser.php?id='.$ligne[4].'">'.$lenom.'</a> </td>';
			echo '<td align="center">'.$ligne[3].'</td>';
			$ast= array("W" => "A activer", "A" => "A approuver","N" => "Normal","B" => "*Bloqué*","X" => "*Expiré*");

			echo '<td align="center">'.$ast[$ligne[9]].'</td>';
			if (GEST_POINTS>0)
				{
				if ($ligne[3]>=8 or $ligne[6]==0)
				 	{
					echo '<td colspan=2 align="center">* Libre accès *</td>';
					}
				 else
				 	{
					echo '<td align="center">'.$ligne[7].'</td>';
					echo '<td>'.date("d-m-Y",strtotime($ligne[8])).'</td>';
					}
			echo '<td align="center">'.$ligne[10].'</td>';
			}
			echo '<td>';
			if ($ligne[5]<>"")
			  { echo '&nbsp;<a href="mailto:'.$ligne[5].'">e-mail</a>&nbsp;'; }
			echo '</td>';
			echo '</tr>';
			$i++;
			}
	  echo '</table>';
	  if ($listpages<>"")
	  	echo '<p>'.$listpages.'</p>';
	  }
	 else
	  {
	  msg('Aucun utilisateur enregistré');
	  }

echo '</div>';

close_page(1);
?>

