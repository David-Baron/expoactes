<?php
// Module de recherche "levenshtein" du programmes ExpoActes
// Copyright (C) : André Delacharlerie + Jean Louis Cazor, 2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html

//-------------------------------------------------------------------
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");
include("tools/cree_table_levenshtein.php");

$root = "";
$path = "";
$xcomm = $xpatr = $page = "";
pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$db  = con_db();
$userlevel = logonok(LEVEL_LEVENSHTEIN);
while ($userlevel < LEVEL_LEVENSHTEIN) {
	login($root);
}

open_page(SITENAME . " : Dépouillement d'actes de l'état-civil et des registres paroissiaux", $root, null, null, null, '../index.htm', 'rss.php');
navigation($root, 2, 'A', "Recherche  Levenshtein");

echoln('<div id="col_menu">');
form_recherche($root);
statistiques();
menu_public();
show_pub_menu();
show_certifications();
if (!defined("CRIT_RECH_COUPLES")) $CRIT_RECH_COUPLES = 2;
else $CRIT_RECH_COUPLES = CRIT_RECH_COUPLES;

echo '</div>';

echo '<div id="col_main">';
echo '<h2>Recherche Levenshtein</h2>';
echo 'Permet de rechercher des noms dont l\'orthographe varie de 0 à 5 lettres (en plus, en moins, en remplacement)';
echo ' de celui indiqué (ex : Recherche Vernhes + 2 différences donne : Vernhes,Verhnes,Vergne,Vergnes,Verghnes, Bernhes ...)';
echo '<br><b>Aide</b> : <a href="admin/aide/levenshtein_principes.html">Principes</a> et <a href="admin/aide/levenshtein_utilisation.html">Utilisation</a>';
echo '<div class="rech_zone">';

echo '<form class="form_rech" name="rechercheav" method="post" action="' . $root . '/chercherlevenshtein.php">';

echo '<div class="rech_zone">';
echo '<div class="rech_titre">Individu concerné  :</div>';
echo '<p>&nbsp;&nbsp;Patronyme : <input type="text" name="achercher" />&nbsp; ';
echo 'Prénom :    <input type="text" name="prenom" /></p>';

echo '<p>&nbsp;&nbsp;Comparaison :';
echo '  <input type="radio" name="comp" value="Z" />Exacte&nbsp;';
echo '  <input type="radio" name="comp" value="U" />1 différence&nbsp;';
echo '  <input type="radio" name="comp" value="D" checked="checked" />2 diff.&nbsp;';
echo '  <input type="radio" name="comp" value="T" />3 diff.&nbsp;';
echo '  <input type="radio" name="comp" value="Q" />4 diff.&nbsp;';
echo '  <input type="radio" name="comp" value="C" />5 diff.&nbsp;</p>';
echo '</div>';

echo '<div class="rech_zone">';
echo '<div class="rech_titre">Conjoint (éventuel) :</div>';
echo '<p>&nbsp;&nbsp;Patronyme : <input type="text" name="achercher2" />&nbsp; ';
echo 'Prénom :    <input type="text" name="prenom2" /></p>';
echo '</div>';

echo '<div class="rech_zone">';
echo '<div class="rech_titre">Actes recherchés individus (suppose conjoint non renseigné) :</div>';
if (CHERCH_TS_TYP == 1) {
	echo '<p>&nbsp;<input type="checkbox" name="TypN" value="N" checked="checked" />Naissances&nbsp;';
	echo '  <input type="checkbox" name="TypD" value="D" checked="checked" />Décès&nbsp;';
	echo '  <input type="checkbox" name="TypM" value="M" checked="checked" />Mariages&nbsp;';
	echo '  <input type="checkbox" name="TypV" value="V" checked="checked" />Actes divers &nbsp;';
} else {
	echo '<p>&nbsp;<input type="radio" name="TypNDMV" value="N" checked="checked" />Naissances&nbsp;';
	echo '  <input type="radio" name="TypNDMV" value="D"  />Décès&nbsp;';
	echo '  <input type="radio" name="TypNDMV" value="M"  />Mariages&nbsp;';
	echo '  <input type="radio" name="TypNDMV" value="V"  />Actes divers &nbsp;';
}
echo '</div>';

echo '</p>';
echo '<div class="rech_zone">';
echo '<div class="rech_titre">Actes recherchés couples (suppose conjoint renseigné) :</div>';
echo '  <p>&nbsp;<input type="radio" name="comp2" value="MA" checked="checked" />Mariages&nbsp;';
echo '  <input type="radio" name="comp2" value="EN" />Enfants (naissances)&nbsp;';
if ($CRIT_RECH_COUPLES == 3 or $CRIT_RECH_COUPLES == 4) {
	echo '  <input type="radio" name="comp2" value="END" />Enfants (naissances et décès)&nbsp;';
}
if ($CRIT_RECH_COUPLES == 4) {
	echo '  <input type="radio" name="comp2" value="TOUT" />Couple (mariage, naissances et décès enfants)&nbsp;';
}
echo '  <input type="radio" name="comp2" value="DIV" />Actes Divers&nbsp;';	// ########## NOUVEAU ##############
echo '</div>';

echo '</p>';
echo '<div class="rech_zone">';
echo '<div class="rech_titre">Critères :</div>';
echo '<p>&nbsp;&nbsp;Années à partir de : <input type="text" name="amin" size="4" />&nbsp; ';
echo 'jusqu\'à :    <input type="text" name="amax" size="4" /></p>';
echo '<p>&nbsp;&nbsp;Commune ou paroisse : ';
listbox_communes("ComDep", "", 1);
echo '</p>';
echo '</div>';

echo '<input type="hidden" name="debug" value="' . getparam('debug') . '" />';
echo '<p align="center"><input type="submit" name="Submit" value="Chercher" /></p>';

echo '</form>';
echo '</div>';

echo '</div>';
close_page(1, $root);
