<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
my_ob_start_affichage_continu();

$root = "";
$path = "";
// SUPPRESSION D'UNE SERIE D'ACTES ***

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$needlevel = 6;  // niveau d'accès (anciennement 5)
$userlevel = logonok($needlevel);
while ($userlevel < $needlevel) {
	login($root);
}

$userid = current_user("ID");

open_page("Suppression d'une série d'actes", $root);

// Ajaxify Your PHP Functions
include("../tools/PHPLiveX/PHPLiveX.php");
$ajax = new PHPLiveX(array("getCommunes"));
$ajax->Run(false, "../tools/PHPLiveX/phplivex.js");

navadmin($root, "Suppression d'actes");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

$missingargs = false;
$oktype = false;

$AnneeDeb   = getparam('AnneeDeb');
$AnneeFin   = getparam('AnneeFin');
$TypeActes  = getparam('TypeActes');
$Filiation  = getparam('Filiation');
$AVerifier  = getparam('AVerifier');
$xtdiv      = getparam('typdivers');
$comdep  = html_entity_decode(getparam('ComDep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$Commune = communede($comdep);
$Depart  = departementde($comdep);
$xaction = getparam('action');

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if ($xaction == 'submitted' or $xaction == 'validated') {
	// Données postées
	if ((empty($TypeActes) or ($TypeActes == 'X'))) {
		msg('Vous devez préciser le type des actes.');
		$missingargs = true;
	}
	if (empty($Commune)) {
		msg('Vous devez préciser une commune.');
		$missingargs = true;
	}
} else {
	$missingargs = true;  // par défaut
}
if (!$missingargs) {
	$oktype = true;
	$olddepos = getparam('olddepos', 0);
	$params = array(
		'xtdiv' => $xtdiv,
		'userlevel' => $userlevel,
		'userid' => $userid,
		'olddepos' => $olddepos,
		'TypeActes' => $TypeActes,
		'AnneeDeb' => $AnneeDeb,
		'AnneeFin' => $AnneeFin,
		'comdep' => $comdep,
	);
	list($table, $ntype, $soustype, $condcom, $condad, $condaf, $condtdiv, $conddep) = set_cond_select_actes($params);

	if ($xaction <> 'validated') {
		$request = "select count(*) from " . $table .
			" where " . $condcom . $condad . $condaf . $conddep . $condtdiv . " ;";

		// echo $request;
		optimize($request);
		$result = EA_sql_query($request);
		$ligne = EA_sql_fetch_row($result);
		$nbrec = $ligne[0];
		if ($nbrec == 0) {
			msg("Il n'y a aucun acte de " . $ntype . $soustype . " à " . $comdep . " (dont vous êtes le déposant) !", "erreur");
			echo '<p><a href="supprime.php">Retour</a></p>';
		} else {
			echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
			echo '<h2 align="center">Confirmation de la suppression</h2>';
			echo '<p class="message">Vous allez supprimer ' . $nbrec . ' actes de ' . $ntype . $soustype . ' de ' . $comdep . ' !</p>';
			echo '<p class="message">';
			echo '<input type="hidden" name="action" value="validated" />';
			echo '<input type="hidden" name="TypeActes" value="' . $TypeActes . '" />';
			echo '<input type="hidden" name="AnneeDeb"  value="' . $AnneeDeb . '" />';
			echo '<input type="hidden" name="AnneeFin"  value="' . $AnneeFin . '" />';
			echo '<input type="hidden" name="ComDep"   value="' . htmlentities($comdep, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />';
			echo '<input type="hidden" name="typdivers" value="' . $xtdiv . '" />';
			echo '<input type="hidden" name="olddepos" value="' . $olddepos . '" />';
			echo '<input type="submit" value=" >> CONFIRMER LA SUPPRESSION >> " />' . "\n";
			echo '&nbsp; &nbsp; &nbsp; <a href="index.php">Annuler</a></p>';
			echo "</form>\n";
		}
	} else {
		$request = "delete from " . $table .
			" where " . $condcom . $condad . $condaf . $conddep . $condtdiv . " ;";
		// echo $request;
		$result = EA_sql_query($request);
		optimize($request);
		$nb = EA_sql_affected_rows();
		if ($nb > 0) {
			echo '<p>' . $nb . ' actes de ' . $ntype . $soustype . ' supprimés.</p>';
			writelog('Suppression ' . $ntype, $Commune, $nb);
			maj_stats($TypeActes, $T0, $path, "D", $Commune);
		} else {
			echo '<p>Aucun acte supprimé.</p>';
		}
	} // validated ??
} else // missingargs
//Si pas tout les arguments nécessaire, on affiche le formulaire
{
	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">Suppression de certains actes</h2>';
	if ($userlevel < 8) {
		msg('Attention : Vous ne pourrez supprimer que les données dont vous êtes le déposant !', 'info');
	}
	echo '<table cellspacing="0" cellpadding="0" border="0" align="center" summary="Formulaire">' . "\n";

	form_typeactes_communes();
	echo " <tr>\n";
	echo '  <td align="right">Déposant : </td>' . "\n";
	echo '  <td>';
	if ($userlevel < 8) echo '<input type="hidden" name="olddepos" value="0" />';
	else listbox_users("olddepos", 0, DEPOSANT_LEVEL, 1, ' *** Tous *** ');
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Années : </td>' . "\n";
	echo '  <td>&nbsp;';
	echo '        de <input type="text" name="AnneeDeb" size="4" maxlength="4" /> ';
	echo '        à  <input type="text" name="AnneeFin" size="4" maxlength="4" /> (ces années comprises)';
	echo '  </td>';
	echo " </tr>\n";
	// echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <input type="reset" value="Annuler" />' . "\n";
	echo '  <input type="submit" value=" >> SUPPRIMER >> " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}
echo '</div>';
close_page(1, $root);
