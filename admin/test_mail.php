<?php

error_reporting(E_ALL);

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";
$xcomm = $xpatr = $page = "";
pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$userlevel = logonok(8);
while ($userlevel < 8) {
	login($root);
}

open_page("Test e-mail", $root);
navadmin($root, "Test du mail");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

$missingargs = true;
echo "<h1>Test de l'envoi d'un mail</h1> \n";

if (getparam('action') == 'submitted') {
	$dest = getparam('email');
	if (empty($dest)) {
		msg("Vous devez préciser votre adresse email");
	} else {
		$missingargs = false;
		$sender = mail_encode(SITENAME) . ' <' . LOC_MAIL . ">";
		$okmail = sendmail($sender, $dest, 'Test messagerie de ' . SITENAME, 'Ce message de test a été envoyé via ExpoActes');
		if ($okmail)
			echo "<p>Un mail de test vous a été envoyé. Vérifiez qu'il vous est bien parvenu.</p>";
		else {
			echo "<p>La fonction mail n'a pas pu être vérifée.<br />";
			echo "<b>La consultation des actes peut très bien fonctionner sans mail</b> mais plusieurs fonctions de gestion des utilisateurs ne fonctionneront pas.";
		}
		echo '<p><a href="gest_params.php?grp=Mail">Retour au module de paramétrage</a></p>';
	}
}

if ($missingargs) {
	echo "<h3>Cette procédure ne peut envoyer qu'un mail de test !</h3> \n";
	echo "<p>Le texte du mail est donc fixe.</p> \n";
	echo '<form method="post"  action="">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0">' . "\n";

	echo "<tr>\n";
	echo "<td align=right>Votre adresse email : </td>\n";
	echo '<td><input type="text" name="email" size=40 value="' . LOC_MAIL . '"></td>';
	echo "</tr>\n";

	echo "<tr><td colspan=\"2\" align=\"center\">\n<br>";
	echo '<input type="hidden" name="action" value="submitted">';
	echo '<input type="reset" value="Effacer">' . "\n";
	echo '&nbsp; <input type="submit" value=" *** ENVOYER *** ">' . "\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}
echo '</div>';
close_page(0);
