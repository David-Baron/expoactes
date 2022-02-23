<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
my_ob_start_affichage_continu();
include("../tools/traitements.inc.php");

$root = "";
$path = "";
$userlogin = "";
$T0 = time();

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

$sujet    = getparam('sujet');
$message  = getparam('message');
$xdroits  = getparam('lelevel');
$regime   = getparam('regime');
$rem      = getparam('rem');
$condit   = getparam('condit');
$xaction  = getparam('action');


open_page("Envoi d'un mail circulaire", $root);
navadmin($root, "Envoi d'un mail circulaire");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
$missingargs = true;
$emailfound = false;
$cptok = 0;
$cptko = 0;

menu_users('M');

$today = today();
$condrem = "";
$condlevel = "";
if ($xdroits <> "10") {
	$condlevel = " and level =" . $xdroits;
}
if ($condit <> "0") {
	$condrem = " and " . comparerSQL('REM', $rem, $condit);
}
$condreg = "";
if ($regime >= 0) {
	$condreg = " and regime =" . $regime;
}
if ($xaction == 'submitted') {
	$request = "select nom, prenom, email, level, statut"
		. " from " . EA_UDB . "_user3 "
		. " where (1=1) " . $condlevel . $condreg . $condrem . " ;";
	//echo $request1;
	$sites = EA_sql_query($request, $u_db);
	$nbsites = EA_sql_num_rows($sites);
	$nbsend = 0;
	$missingargs = false;

	while ($site = EA_sql_fetch_array($sites)) {
		if ($site['statut'] == 'N') {
			$mail = $site['email'];
			$nom = $site['nom'];
			$prenom = $site['prenom'];

			$urlsite = EA_URL_SITE . $root . "/index.php";
			$codes = array("#URLSITE#", "#NOM#", "#PRENOM#");
			$decodes = array($urlsite, $nom, $prenom);
			$bon_message = str_replace($codes, $decodes, $message);
			$sender = mail_encode(SITENAME) . ' <' . LOC_MAIL . ">";
			$okmail = sendmail($sender, $mail, $sujet, $bon_message);
			//$okmail=false;
			echo "<p>Envoi à " . $prenom . " " . $nom . " (" . $mail . ") ";
			if (!$okmail) {
				echo ' -> Mail PAS envoyé.';
				$cptko++;
			} else {
				echo ' -> Mail ENVOYE.';
				$cptok++;
			}
		}
	}
} // fichier d'actes

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	if ($xaction == '')  // parametres par défaut
	{
		if (isset($_COOKIE['chargeUSERlogs']))
			$logOk = $_COOKIE['chargeUSERlogs'][0];
		else
			$logOk = "1";
	}

	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">Envoi d\'un mail circulaire</h2>';
	echo '<table cellspacing="2" cellpadding="0" border="0" align="center">' . "\n";

	echo " <tr><td colspan=\"2\"><b>Destinataires</b></td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Droits d'accès : </td>\n";
	echo '  <td>';
	lb_droits_user($xdroits, 2);
	echo '  </td>';
	echo " </tr>\n";
	if (GEST_POINTS > 0) {
		echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo "  <td align=right>Régime (points) : </td>\n";
		echo '  <td>';
		lb_regime_user($regime, 1);
		echo '  </td>';
		echo " </tr>\n";
	} else {
		echo ' <tr><td colspan="2">';
		echo '<input type="hidden" name="regime" value="-1" />';
		echo "</td></tr>\n";
	}

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Commentaire : </td>\n";
	echo '  <td>';
	listbox_trait('condit', "TST", $condit);

	echo ' <input type="text" name="rem" size="50" value="' . $rem . '" />';
	echo "</td>\n";
	echo " </tr>\n";

	echo " <tr><td colspan=\"2\"><b>Message</b></td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Sujet : </td>\n";
	echo '  <td><input type="text" name="sujet" size="60" value="' . $sujet . '">' . "</td>\n";
	echo " </tr>\n";

	echo ' <tr>' . "\n";
	echo "  <td align=right>Texte du mail : </td>\n";
	echo '  <td>';
	echo '<textarea name="message" cols=60 rows=10>' . $message . '</textarea>';
	echo '  </td>';
	echo " </tr>\n";

	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br>";
	echo '  <input type="hidden" name="action" value="submitted">';
	echo '  <input type="reset" value="Effacer">' . "\n";
	echo '  <input type="submit" value=" >> ENVOYER >> ">' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<hr>';
	if ($cptok > 0) {
		echo '<p>Mails envoyés  : ' . $cptok;
		writelog('Mails envoyés ', "USERS", $cptok);
	}
	if ($cptko > 0)  echo '<br>Envois impossibles : ' . $cptko;
	echo '<br>Durée du traitement  : ' . (time() - $T0) . ' sec.';
	echo '</p>';
	echo '<p>Retour à la ';
	echo '<a href="' . mkurl("listusers.php", "") . '"><b>' . "liste des utilisateurs" . '</b></a>';
	echo '</p>';
}
echo '</div>';
close_page(1, $root);
