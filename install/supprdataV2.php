<?php

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";
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

$suppr    = getparam('suppr');
$xaction  = getparam('action');

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

open_page("Suppression des données en VERSION 2",$root);
navadmin($root,"Suppression V2");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root,$userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

if ($xaction == 'submitted')
	{
	if ($suppr=='YESV2')
		{
		$tables = array('nai','dec','mar','div','user');
		foreach($tables as $latable)
			{
			$request = "drop table ".EA_DB."_".$latable;
			$result = mysql_query($request);
			echo "<p> Suppression de la table ".EA_DB."_".$latable."</p>";
			}
		}
		else
		{
		echo "<p>Aucune suppression effectuée</p>";
		} // submitted ??
	echo '<p><a href="../admin/index.php">Retour à la gestion des actes</a></p>';
  }
 else // missingargs
  //Si pas tout les arguments nécessaire, on affiche le formulaire
	{
	global $dbaddr,$dbname;
	echo '<form method="post" enctype="multipart/form-data" action="">'."\n";
	echo '<h2 align="center">'."Supprimer des données de la Version 2".'</h2>';
	echo '<table cellspacing="0" cellpadding="0" border="0" align="center" summary="Formulaire">'."\n";
	echo " <tr><td colspan=\"2\" align=\"center\"><img src=\"../img/danger.png\" alt='ATTENTION !'><br />&nbsp;<br />";
	echo "Base <b>".$dbname."</b> sur <b>".$dbaddr."</b><td></tr>";
	echo " <tr>\n";
	echo '  <td align="right">Suppression : &nbsp;</td>'."\n";
	echo '  <td>';
	echo '        <br />';
	echo '        <input type="radio" name="suppr" value="N" checked="checked" />Ne pas encore supprimer<br />';
	echo '        <input type="radio" name="suppr" value="YESV2" />Supprimer les anciennes données de la version 2<br />';
	echo '        <br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <input type="reset" value="Annuler" />'."\n";
	echo '  <input type="submit" value=" >> SUITE >> " />'."\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo '</div>';
	close_page(1,$root);
	}
?>
