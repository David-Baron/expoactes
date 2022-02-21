<?php
// Page de recherche du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2008
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html
//-------------------------------------------------------------------
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');


$root = "";
$path = "";
$xcomm= "";
$xpatr= "";
$page = "";
pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";
$userlevel=logonok(3);
while ($userlevel<3)
  {
  login($root);
  }

open_page(SITENAME." : Dépouillement d'actes de l'état-civil et des registres paroissiaux",$root,null,null,null,'../index.htm','rss.php');
navigation($root,2,'A',"Recherche avancée");

echoln('<div id="col_menu">');
form_recherche($root);
statistiques();
menu_public();
show_pub_menu();
show_certifications();

echo '</div>';

echo '<div id="col_main">';
echo '<h2>Recherche avancée</h2>';

if ((RECH_LEVENSHTEIN==1) and (max($userlevel,PUBLIC_LEVEL)>= LEVEL_LEVENSHTEIN))
	{
	echo '<div align="right">';
	echo '<a href="'.$root.'/rechlevenshtein.php">Recherche Levenshtein</a>&nbsp; &nbsp;';
	echo '</div>';
	}

echo '<div class="rech_zone">';

echo '<form class="form_rech" name="rechercheav" method="post" action="'.$root.'/chercher.php">';

	echo '<div class="rech_zone">';
	echo '<div class="rech_titre">Première personne concernée par l\'acte :</div>';
	echo '<p>&nbsp;&nbsp;Patronyme : <input type="text" name="achercher" />&nbsp; ';
	echo 'Prénom :    <input type="text" name="prenom" /></p>';
	echo '<p>&nbsp;&nbsp;De : <input type="radio" name="zone" value="1" checked="checked" /> Intéressé(e) &nbsp;';
	echo '   <input type="radio" name="zone" value="4" /> (future/ex) Conjoint &nbsp;';
	echo '   <input type="radio" name="zone" value="5" /> Père &nbsp;';
	echo '   <input type="radio" name="zone" value="6" /> Mère &nbsp;';
	echo '   <input type="radio" name="zone" value="7" /> Parrain/témoin &nbsp;';
	//echo '   <input type="radio" name="zone" value="8" /> Commentaires </p>';
	echo '<p>&nbsp;&nbsp;Comparaison :';
	echo '  <input type="radio" name="comp"'.prechecked("E").'/>Exacte&nbsp;';
	echo '  <input type="radio" name="comp"'.prechecked("D").'/>Au début&nbsp;';
	echo '  <input type="radio" name="comp"'.prechecked("F").'/>A la fin&nbsp;';
	echo '  <input type="radio" name="comp"'.prechecked("C").'/>Est dans&nbsp;';
	echo '  <input type="radio" name="comp"'.prechecked("S").'/>Sonore&nbsp;</p>';
	echo '</div>';


	echo '<div class="rech_zone">';
	echo '<div class="rech_titre">Seconde personne (éventuelle) :</div>';
	echo '<p>&nbsp;&nbsp;Patronyme : <input type="text" name="achercher2" />&nbsp; ';
	echo 'Prénom :    <input type="text" name="prenom2" /></p>';
	echo '<p>&nbsp;&nbsp;De : <input type="radio" name="zone2" value="4" checked="checked" /> (future/ex) Conjoint &nbsp;';
	echo '   <input type="radio" name="zone2" value="5" /> Père &nbsp;';
	echo '   <input type="radio" name="zone2" value="6" /> Mère &nbsp;';
	echo '   <input type="radio" name="zone2" value="7" /> Parrain/témoin </p>';
	echo '<p>&nbsp;&nbsp;Comparaison :';
	echo '  <input type="radio" name="comp2"'.prechecked("E").'/>Exacte&nbsp;';
	echo '  <input type="radio" name="comp2"'.prechecked("D").'/>Au début&nbsp;';
	echo '  <input type="radio" name="comp2"'.prechecked("F").'/>A la fin&nbsp;';
	echo '  <input type="radio" name="comp2"'.prechecked("C").'/>Est dans&nbsp;';
	echo '  <input type="radio" name="comp2"'.prechecked("S").'/>Sonore&nbsp;</p>';
	echo '</div>';

	echo '<div class="rech_zone">';
	echo '<div class="rech_titre">Autres éléments de l\'acte :</div>';
	echo '<p>&nbsp;&nbsp;Texte : <input type="text" name="achercher3" /></p>';
	echo '<p>&nbsp;&nbsp;Dans :';
	echo '   <input type="radio" name="zone3" value="9" checked="checked" /> Origines &nbsp;';
	echo '   <input type="radio" name="zone3" value="A" /> Professions &nbsp;';
	echo '   <input type="radio" name="zone3" value="8" /> Commentaires </p>';
	echo '<p>&nbsp;&nbsp;Comparaison :';
	echo '  <input type="radio" name="comp3"'.prechecked("E").'/>Exacte&nbsp;';
	echo '  <input type="radio" name="comp3"'.prechecked("D").'/>Au début&nbsp;';
	echo '  <input type="radio" name="comp3"'.prechecked("F").'/>A la fin&nbsp;';
	echo '  <input type="radio" name="comp3"'.prechecked("C").'/>Est dans&nbsp;';
	echo '  <input type="radio" name="comp3"'.prechecked("S").'/>Sonore&nbsp;</p>';
	echo '</div>';

	echo '<div class="rech_zone">';
	echo '<div class="rech_titre">Actes recherchés :</div>';
  if (CHERCH_TS_TYP==1)
  	{
		echo '<p>&nbsp;<input type="checkbox" name="TypN" value="N" checked="checked" />Naissances&nbsp;';
		echo '  <input type="checkbox" name="TypD" value="D" checked="checked" />Décès&nbsp;';
		echo '  <input type="checkbox" name="TypM" value="M" checked="checked" />Mariages&nbsp;';
		echo '  <input type="checkbox" name="TypV" value="V" checked="checked" />Actes divers :&nbsp;';
		}
	 else
	 	{
		echo '<p>&nbsp;<input type="radio" name="TypNDMV" value="N" checked="checked" />Naissances&nbsp;';
		echo '  <input type="radio" name="TypNDMV" value="D"  />Décès&nbsp;';
		echo '  <input type="radio" name="TypNDMV" value="M"  />Mariages&nbsp;';
		echo '  <input type="radio" name="TypNDMV" value="V"  />Actes divers :&nbsp;';
		}
	listbox_divers("typdivers","***Tous***",1);
	echo '</p>';
	echo '<p>&nbsp;&nbsp;Années à partir de : <input type="text" name="amin" size="4" />&nbsp; ';
	echo 'jusqu\'à :    <input type="text" name="amax" size="4" /></p>';
	echo '<p>&nbsp;&nbsp;Commune ou paroisse : ';
	listbox_communes("ComDep","***Toutes***",1);
	echo '</p>';
	echo '</div>';

	echo '<input type="hidden" name="debug" value="'.getparam('debug').'" />';
  echo '<p align="center"><input type="submit" name="Submit" value="Chercher" /></p>';

echo '</form>';
echo '</div>';


echo '<p>&nbsp;</p>';

echo '</div>';
close_page(1,$root);
?>

