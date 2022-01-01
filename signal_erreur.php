<?php
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

global $loc_mail;
if (AUTO_CAPTCHA)
	session_start();  // pour captcha

pathroot($root, $path, $xcomm, $xpatr, $page);

open_page("Signaler une erreur dans un acte", $root);
navigation($root, 2, "", "Signaler une erreur dans un acte");

echo '<div id="col_menu">' . "\n";
menu_public();
show_pub_menu();
echo '</div>' . "\n";
echo '<div id="col_main_adm">' . "\n";

$missingargs = true;
$nompre = getparam('nompre');
$msgerreur = getparam('msgerreur');
$email = getparam('email');
$xid   = getparam('xid');
$xct   = getparam('xct');
$xty   = getparam('xty');

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
	if (strlen($msgerreur) < 10) {
		msg('Vous devez décrire l\'erreur observée');
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
		$log = "Signalmt erreur";
		$crlf = chr(10) . chr(13);

		switch ($xty) {
			case "N":
				$s4 = "acte_naiss.php";
				break;
			case "D":
				$s4 = "acte_deces.php";
				break;
			case "M":
				$s4 = "acte_mari.php";
				break;
			case "V":
				$s4 = "acte_bans.php";
				break;
		}

		$urlvalid = EA_URL_SITE . $root . "/admin/" . $s4 . "?xid=" . $xid . "&xct=" . $xct . $crlf . $crlf;
		$lemessage = "Erreur signalée par " . $nompre . " (" . $email . ") : " . $crlf . $crlf;
		$lemessage .= $msgerreur . $crlf . $crlf;
		$lemessage .= "Acte concerné : " . $crlf . $crlf;
		$lemessage .= $urlvalid . $crlf . $crlf;

		//echo "<p>MES = ".$lemessage."<p>";

		$sujet = "Erreur signalée sur " . SITENAME;
		$sender = mail_encode($nompre) . ' <' . $email . ">";
		$okmail = sendmail($sender, EMAIL_SIGN_ERR, $sujet, $lemessage);
		if ($okmail) {
			$log .= " + mail";
			$mes = "Un mail a été envoyé à l'administrateur.";
		} else {
			$log .= " NO mail";
			$mes = "ERREUR : Le mail n'a pas pu être envoyé ! <br />Merci de contactez directement l'administrateur du site.";
		}

		writelog($log, $nompre, 1);
		echo '<p><b>' . $mes . '</b></p>';
		$id = 0;
	}
}

echo '<script language="javascript1.4" type="text/javascript">';
echo 'function _closeWindow() { window.opener = self; self.close();}';
echo '</script>';

//Si pas tout les arguments nécessaire, on affiche le formulaire
if (!$ok) {

	echo "<h2>Signalement d'une erreur dans un acte</h2>" . "\n";
	echo '<form method="post"  action="">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">' . "\n";

	echo "<tr>\n";
	echo '<td colspan="2">' . "Description de l'erreur observée : <br />\n";
	echo '<textarea name="msgerreur" cols="80" rows="12">' . $msgerreur . '</textarea>' . "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo '<td align="right">' . "Vos nom et prénom : </td>\n";
	echo '<td><input type="text" size="50" name="nompre" value="' . $nompre . '" />' . "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo '<td align="right">' . "Votre e-mail : </td>\n";
	echo '<td><input type="text" name="email" size="50" value="' . $email . '" />' . "</td>\n";
	echo "</tr>\n";

	if (AUTO_CAPTCHA) {
		echo " <tr>\n";
		if (function_exists('imagettftext')) {
			echo '<td align="right"><img src="tools/captchas/image.php" alt="captcha" id="captcha" /></td>' . "\n";
		} else {
			msg('061 : Librairie GD indisponible');
			echo '<td align="right">Code captcha manquant</td>' . "\n";
		}
		echo '<td>Recopiez le code ci-contre : <br />';
		echo '<input type="text" name="captcha" size="6" maxlength="5" value="" />' . "</td>\n";
		echo "</tr>\n";
	}

	echo "<tr>\n";
	echo '<td colspan="2">&nbsp;</td>' . "\n";
	echo "</tr>\n";

	echo "<tr><td align=\"right\">\n";
	echo '<input type="hidden" name="xid" value="' . $xid . '" />';
	echo '<input type="hidden" name="xty" value="' . $xty . '" />';
	echo '<input type="hidden" name="xct" value="' . $xct . '" />';
	echo '<input type="hidden" name="action" value="submitted" />';
	echo '<a href="#" Onclick="javascript:window.close()">Fermer cette page</a></p>';
	echo '&nbsp; <input type="reset" value=" Effacer " />' . "\n";
	echo "</td><td align=\"left\">\n";
	echo '&nbsp; <input type="submit" value=" >> Envoyer >> " />' . "\n";
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<p align="center"><b>Merci de  votre aide.</b><br /><a href="#" Onclick="javascript:window.close()">Fermer cette page</a></p>';
}
echo '</div>';
close_page(1);
