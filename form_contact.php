<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

//global $loc_mail;
if (AUTO_CAPTCHA)
	session_start();  // pour captcha

pathroot($root, $path, $xcomm, $xpatr, $page);

open_page("Formulaire de contact", $root);
navigation($root, 2, "", "Formulaire de contact");

echo '<div id="col_menu">' . "\n";
menu_public();
show_pub_menu();
echo '</div>' . "\n";
echo '<div id="col_main_adm">' . "\n";

$missingargs = true;
$nompre = getparam('nompre');
$txtmsg = getparam('txtmsg');
$email = getparam('email');
$sweb  = htmlentities(getparam('sweb'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$objet = getparam('objet');

//print '<pre>';  print_r($_REQUEST); echo '</pre>';
$ok = false;

// Données postées -> ajouter ou modifier
if (getparam('action') == 'submitted') {
	$ok = true;
	if (empty($nompre)) {
		msg('Merci de préciser vos nom et prenom');
		$ok = false;
	}
	if (empty($email) or isin($email, '@') == -1 or isin($email, '.') == -1) {
		msg("Vous devez préciser une adresse email valide");
		$ok = false;
	}
	if (strlen($txtmsg) < 10) {
		msg('Vous devez donner un message');
		$ok = false;
	}
	if (strlen($objet) < 2) {
		msg('Vous devez donner un objet');
		$ok = false;
	}
	if (AUTO_CAPTCHA and function_exists('imagettftext')) {
		if (md5(getparam('captcha')) != $_SESSION['valeur_image']) {
			msg('Attention à bien recopier le code dissimulé dans l\'image !');
			$ok = false;
		}
	}
	if ($ok) {
		$missingargs = false;
		$mes = "";
		$log = "Contact";
		$crlf = chr(10) . chr(13);

		$lemessage = "Message envoyé par " . $nompre . " (" . $email . ") via " . SITENAME . $crlf . $crlf;
		if ($sweb <> "")
			$lemessage .= "Site web : " . $sweb . " " . $crlf . $crlf;
		$lemessage .= $txtmsg . $crlf . $crlf;

		//echo "<p>MES = ".$lemessage."<p>";

		$sujet = $objet;
		$sender = mail_encode($nompre) . ' <' . $email . ">";
		$okmail = sendmail($sender, EMAIL_CONTACT, $sujet, $lemessage);
		if ($okmail) {
			$mes = "Un mail a été envoyé à l'administrateur.";
		} else {
			$mes = "ERREUR : Le mail n'a pas pu être envoyé ! <br />Merci de contactez directement l'administrateur du site à l'adresse " . EMAIL_CONTACT;
		}
		//writelog($log,$nompre,1);
		echo '<p><b>' . $mes . '</b></p>';
		$id = 0;
	}
}

//Si pas tout les arguments nécessaire, on affiche le formulaire
if (!$ok) {

	echo "<h2>Formulaire de contact</h2>" . "\n";
	echo '<form method="post"  action="">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">' . "\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Vos nom et prénom : </td>\n";
	echo '  <td><input type="text" size="50" name="nompre" value="' . $nompre . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Votre e-mail : </td>\n";
	echo '  <td><input type="text" name="email" size="50" value="' . $email . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">' . "Votre site web : </td>\n";
	echo '  <td><input type="text" name="sweb" size="50" value="' . $sweb . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td colspan="2">' . "Sujet : \n";
	echo '  <input type="text" name="objet" size="80" value="' . $objet . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td colspan="2">' . "Votre message : <br />\n";
	echo '  <textarea name="txtmsg" cols="80" rows="12">' . $txtmsg . '</textarea>' . "</td>\n";
	echo " </tr>\n";

	if (AUTO_CAPTCHA) {
		echo " <tr>\n";
		if (function_exists('imagettftext')) {
			echo '  <td align="right"><img src="tools/captchas/image.php" alt="captcha" id="captcha" /></td>' . "\n";
		} else {
			msg('061 : Librairie GD indisponible');
			echo '  <td align="right">Code captcha manquant</td>' . "\n";
		}
		echo '  <td>Recopiez le code ci-contre : <br />';
		echo '<input type="text" name="captcha" size="6" maxlength="5" value="" />' . "</td>\n";
		echo " </tr>\n";
	}

	echo " <tr>\n";
	echo '  <td colspan="2">&nbsp;</td>' . "\n";
	echo " </tr>\n";

	echo " <tr><td align=\"right\">\n";
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo ' &nbsp; <input type="reset" value=" Effacer " />' . "\n";
	echo " </td><td align=\"left\">\n";
	echo ' &nbsp; <input type="submit" value=" >> Envoyer >> " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<p align="center"><b>Nous vous répondrons dès que possible.</b></p>';
}
echo '</div>';
close_page(1);
