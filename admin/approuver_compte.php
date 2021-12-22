<?php
include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}
$xaction = getparam('action');

open_page("Approbation d'un compte utilisateur", $root);
navadmin($root, "Approbation d'un compte utilisateur");

echo '<div id="col_menu">' . "\n";
menu_admin($root, $userlevel);
echo '</div>' . "\n";
echo '<div id="col_main_adm">' . "\n";

if (USER_AUTO_DEF <> 1) {
	echo "<p><b>Désolé : Cette action n'a pas de sens dans la configuration actuelle du logiciel</b></p>";
	echo '</div>';
	close_page(1);
	die();
}

if (!isset($_REQUEST['id'])) {
	echo "<p><b>Cette procédure ne peut être appelée directement.</b></p>";
	echo '</div>';
	close_page(1);
	die();
}

$missingargs = true;

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

// Données postées -> ajouter ou modifier
$ok = true;
if (!isset($_REQUEST['complet'])) {
	$ok = false;
}
if (!isset($_REQUEST['action'])) {
	msg('Vous devez sélectionner l\'action à poser');
	$ok = false;
}
$res = EA_sql_query("SELECT * FROM " . EA_UDB . "_user3 WHERE id='" . sql_quote(getparam('id'))
	. "' and  (statut='W' or statut='A')", $u_db);
if (EA_sql_num_rows($res) != 1) {
	echo "<p><b>Pas de compte à approuver avec cette identification.</b></p>";
	echo '</div>';
	close_page(1);
	die();
} else {
	$row = EA_sql_fetch_array($res);
	$nomprenom = $row['prenom'] . ' ' . $row['nom'];
	$login = $row['login'];
}
if ($ok) {
	$id = getparam('id');
	$missingargs = false;
	$mes = "";
	if ($xaction == "OK") {
		$statut = 'N';  // normal
		$sujet = "Approbation de votre compte";
		$mes = "approuvé";
	} else {
		$statut = 'B';  // bloqué
		$sujet = "Refus de votre compte";
		$mes = "refusé";
	}
	$reqmaj = "update " . EA_UDB . "_user3 set "
		. " statut = '" . $statut . "',"
		. " rem = ' '"
		. " where id=" . $id . ";";

	//echo "<p>".$reqmaj."</p>";
	if ($result = EA_sql_query($reqmaj, $u_db)) {
		$crlf = chr(10) . chr(13);
		$log = "Cpte " . $mes;
		$message = getparam('messageplus');

		$sql = "select NOM, PRENOM, LOGIN"
			. " from " . EA_UDB . "_user3 where id=" . $id . ";";
		$res = EA_sql_query($sql, $u_db);
		$ligne = EA_sql_fetch_array($res);

		$urlsite = EA_URL_SITE . $root . "/index.php";
		$codes = array("#NOMSITE#", "#URLSITE#", "#LOGIN#", "#NOM#", "#PRENOM#");
		$decodes = array(SITENAME, $urlsite, $ligne['LOGIN'], $ligne['NOM'], $ligne['PRENOM']);
		$bon_message = str_replace($codes, $decodes, $message);

		$sender = mail_encode(SITENAME) . ' <' . LOC_MAIL . ">";
		$okmail = sendmail($sender, $row['email'], $sujet, $bon_message);
		if ($okmail) {
			$log .= " + mail";
		} else {
			$log .= " NO mail";
		}
		writelog($log, $login, 0);
		echo '<p><b>La demande de compte a été ' . $mes . 'e.</b></p>';
	} else {
		echo ' -> Erreur : ';
		echo '<p>' . EA_sql_error() . '<br />' . $reqmaj . '</p>';
	}
}


//Si pas tout les arguments nécessaire, on affiche le formulaire
if (!$ok) {
	if ($xaction == 'KO')
		$messageplus = MAIL_REFUS;
	else
		$messageplus = MAIL_APPROBATION;

	echo '<h2>Approbation d\'un compte d\'utilisateur</h2>' . "\n";
	echo '<form method="post"  action="">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">' . "\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Candidat : </td>\n";
	echo '  <td><b>' . $nomprenom . "</b></td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Login : </td>\n";
	echo '  <td>' . $login . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Action : </td>\n";
	echo '  <td>';
	echo '        <input type="radio" name="action" value="OK" ' . checked("OK", $_REQUEST['action']) . ' /> = Approuver';
	echo '        <input type="radio" name="action" value="KO" ' . checked("KO", $_REQUEST['action']) . ' /> = REFUSER';
	echo '        cet utilisateur';
	echo '  </td>';
	echo " </tr>\n";

	echo ' <tr>' . "\n";
	echo "  <td align=right>Message : </td>\n";
	echo '  <td>';
	echo '<textarea name="messageplus" cols=60 rows=10>' . $messageplus . '</textarea>';
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td colspan="2">&nbsp;</td>' . "\n";
	echo " </tr>\n";

	echo " <tr><td align=\"right\">\n";
	echo '  <input type="reset" value=" Effacer " />' . "\n";
	echo '  <input type="hidden" name="complet" value="OK" />';
	echo " </td><td align=\"left\">\n";
	echo ' &nbsp; <input type="submit" value=" *** ENVOYER *** " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<p align="center">Aller à : <a href="index.php">la page d\'accueil</a>';
	echo '&nbsp;|&nbsp; <a href="listusers.php">la liste des utilisateurs</a>';
	echo '&nbsp;|&nbsp; <a href="gestuser.php?id=' . $id . '">la fiche de ' . $nomprenom . '</a></p>';
}
echo '</div>';
close_page(1);
