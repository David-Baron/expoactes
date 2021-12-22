<?php
ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$root = "";
$path = "";
$userlogin = "";
$T0 = time();

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

$logOk      = ischecked('LogOk');
$logKo      = ischecked('LogKo');
$logRed     = ischecked('LogRed');
$sendmail   = ischecked('SendMail');
$xdroits    = getparam('lelevel');
$xregime    = getparam('regime');
if ($xregime == '') $xregime = 2; // pas activé -> automatique

$message    = getparam('Message');
$xaction    = getparam('action');
if ($xaction == 'submitted') {
	setcookie("chargeUSERparam", $sendmail . $xdroits . $xregime, time() + 60 * 60 * 24 * 60);  // 60 jours
	setcookie("chargeUSERlogs", $logOk . $logKo . $logRed, time() + 60 * 60 * 24 * 60);  // 60 jours
}


open_page("Chargement des utilisateurs (CSV)", $root);
navadmin($root, "Chargement des utilisateurs CSV");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
$missingargs = true;
$emailfound = false;
$oktype = false;
$cptmaj = 0;
$cptign = 0;
$cptadd = 0;
$cptdeja = 0;
$avecidnim = false;

menu_users('I');

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

$today = today();
$userid = current_user("ID");

if ($xaction == 'submitted') {
	// Données postées
	if (empty($_FILES['Users']['tmp_name'])) {
		msg('Pas trouvé le fichier spécifié.');
	}
}

if (!empty($_FILES['Users']['tmp_name'])) { // fichier d'utilisateurs
	if (strtolower(mb_substr($_FILES['Users']['name'], -4)) == ".csv") //Vérifie que l'extension est bien '.CSV'
	{ // type TXT
		$csv = file($_FILES['Users']['tmp_name']);
		foreach ($csv as $line_num => $line) { // par ligne
			$line = ea_utf8_encode($line); // ADLC 24/09/2015
			if ($line_num == 0) {
				$line = str_replace('"', '', $line);      // Suppression des guillemets éventuels
				$line = str_replace(chr(9), ';', $line);  // remplacement des TAB par des ;
				$acte = explode(";", trim($line));
			}
			$oktype = true;
			if ($oktype == true) {	// --------- Traitement ----------
				$missingargs = false;
				$line = str_replace('"', '', $line);      // Suppression des guillemets éventuels
				$line = str_replace(chr(9), ';', $line);  // remplacement des TAB par des ;
				$user = ads_explode(";", trim($line), 14);

				//{ print '<pre>';  print_r($user); echo '</pre>'; }

				$nom     = $user[0];
				$pre     = $user[1];
				$mail    = $user[2];
				$log = '<br />USER ' . $nom . ' ' . $pre . ' ' . $mail;
				$ok = true;

				if (($nom == "") or ($pre == "") or ($mail == ""))
				// pas de nom ou de prenom
				{
					$cptign++;
					if ($logKo == 1) echo $log . " INCOMPLET (nom, prenom ou e-mail) -> Ignoré";
					$ok = false;
				}
				if (isin($mail, "@") == -1 or isin($mail, ".") == -1)
				// emal surement pas valide
				{
					$cptign++;
					if ($logKo == 1) echo $log . " email invalide -> Ignoré";
					$ok = false;
				}
				if ($ok) {
					// Recherche si existant
					$sql = "select * from " . EA_UDB . "_user3 where nom = '" . sql_quote($nom) . "' and prenom = '" . sql_quote($pre) . "'";
					$res = EA_sql_query($sql, $u_db);
					$nb = EA_sql_num_rows($res);
					if ($nb > 0) {
						$cptdeja++;
						if ($logRed == 1) echo $log . " NOM+PRENOM DEJA PRESENT -> Ignoré";
						$ok = false;
					}
				}
				if ($ok and TEST_EMAIL_UNIC == 1) {
					$sql = "select * from " . EA_UDB . "_user3 where email = '" . sql_quote($mail) . "'";
					$res = EA_sql_query($sql, $u_db);
					$nb = EA_sql_num_rows($res);
					if ($nb > 0) {
						$cptdeja++;
						if ($logRed == 1) echo $log . " ADRESSE EMAIL DEJA PRESENTE -> Ignoré";
						$ok = false;
					}
				}
				if ($ok) {
					$login = $user[3];
					$pw    = $user[4];
					if (($login == "") or (strtoupper($login) == "AUTO")) {
						// création automatique du login
						$racine = strtolower(mb_substr($pre, 0, 3) . mb_substr($nom, 0, 3));
						$login = $racine;
						// recherche si existe
						$sql = "select * from " . EA_UDB . "_user3 where login = '" . sql_quote($login) . "'";
						$res = EA_sql_query($sql, $u_db);
						$nb = EA_sql_num_rows($res);
						if ($nb > 0) {
							// création d'un login numéroté
							$sql = "select login from " . EA_UDB . "_user3"
								. " where login like '" . $racine . "__' AND cast( substring( login, 7, 2 ) AS unsigned ) >0"
								. " order by login desc";
							$res = EA_sql_query($sql, $u_db);
							$nb = EA_sql_num_rows($res);
							$val = 1;
							if ($nb > 0) {
								$ligne = EA_sql_fetch_row($res);
								$val = mb_substr($ligne[0], 6, 2) + 1;
							}
							$login = $racine . mb_substr("0" . $val, -2, 2);
						}
					}
					// TEST FINAL du login (dans tous les cas)
					$sql = "select * from " . EA_UDB . "_user3 where login = '" . sql_quote($login) . "'";
					$res = EA_sql_query($sql, $u_db);
					$nb = EA_sql_num_rows($res);
					if ($nb > 0) {
						$ok = false;
						if ($logRed == 1) echo $log . " [login=" . $login . "]" . " LOGIN DEJA PRESENT -> Ignoré";
						$cptign++;
					}
					if (($pw == "") or (strtoupper($pw) == "AUTO")) {
						// création automatique du passw
						$pw = MakeRandomPassword(8);
					}
					if (strlen($pw) < 40) {
						// il faut hasher le pw
						$hashpw = sha1($pw);
					} else
						$hashpw = $pw;  // on présume que c'est le hash dans une restauration

					if ($user[5] == "")
						$droits = $xdroits;
					else {
						$droits = $user[5];
						if (!($droits > 0 and $droits <= 8) and $user[9] = "")   // Interdit de créer des administrateurs à la volée
						{
							$ok = false;
							if ($logKo == 1) echo $log . " ADMINISTRATEURS INTERDITS -> Ignoré";
							$cptign++;
						}
					}
					if ($user[6] == "")
						$regime = $xregime;
					else {
						$regime = $user[6];
						if (!($regime >= 0 and $regime <= 2)) {
							$ok = false;
							if ($logKo == 1) echo $log . " REGIME " . $regime . " INVALIDE -> Ignoré";
							$cptign++;
						}
					}
					if ($user[7] == "")
						$solde = PTS_PAR_PER;
					else
						$solde = $user[7];
					$comment = $user[8]; // REM
					if ($user[9] == "")  // 11 = date d'expiration
						$dtexpiration = dt_expiration_defaut();
					else
						$dtexpiration = $user[9];
					$libre = $user[10]; // libre

					// 11 = ID

					if ($user[12] == "")  // statut
						$statut = "N";
					else
						$statut = $user[12];
					if ($user[13] == "")  // 13 = date de création
					{
						if (!empty($user[11]))
							$dtcreation = "0000-00-00";  // restauration
						else
							$dtcreation = today();
					} else
						$dtcreation = $user[13];
					if (empty($user[14]))
						$pt_conso = 0;
					else
						$pt_conso = $user[14];
					if (empty($user[15]))  // 15 = date de dernière recharge
						$maj_solde = today();
					else
						$maj_solde = $user[15];
					// test sur le n° ID
					if (!empty($user[11])) {
						$iduser = $user[11];
						$sql = "select * from " . EA_UDB . "_user3 where ID = '" . sql_quote($iduser) . "'";
						$res = EA_sql_query($sql, $u_db);
						$nb = EA_sql_num_rows($res);
						if ($nb > 0) {
							$ok = false;
							if ($logRed == 1) echo $log . " [login=" . $login . "]" . " ID déjà présent -> Ignoré";
							$cptign++;
						}
					} else
						$iduser = "";
				}

				if ($ok) {
					// insertion
					$reqmaj = "insert into " . EA_UDB . "_user3 "
						. " ( `login` , `hashpass` , `nom` , `prenom` , `email` , `level` , `regime` , `solde` , `maj_solde` , statut, dtcreation, dtexpiration, libre, pt_conso, ID,`REM`)"
						. " values('" . sql_quote($login) . "','"
						. sql_quote($hashpw) . "','"
						. sql_quote($nom) . "','"
						. sql_quote($pre) . "','"
						. sql_quote($mail) . "',"
						. sql_quote($droits) . ","
						. sql_quote($regime) . ","
						. sql_quote($solde) . ",'"
						. sql_quote(date_sql($maj_solde)) . "','"
						. sql_quote($statut) . "','"
						. sql_quote(date_sql($dtcreation)) . "','"
						. sql_quote(date_sql($dtexpiration)) . "','"
						. sql_quote($libre) . "','"
						. sql_quote($pt_conso) . "','"
						. sql_quote($iduser) . "','"
						. sql_quote($comment) . "');";
					//echo $reqmaj;
					if ($result = EA_sql_query($reqmaj, $u_db)) {
						if ($sendmail == 1) {
							$urlsite = EA_URL_SITE . $root . "/index.php";
							$codes = array("#NOMSITE#", "#URLSITE#", "#LOGIN#", "#PASSW#", "#NOM#", "#PRENOM#");
							$decodes = array(SITENAME, $urlsite, $login, $pw, $nom, $pre);
							$bon_message = str_replace($codes, $decodes, $message);
							$sujet = "Votre compte " . SITENAME;
							$sender = mail_encode(SITENAME) . ' <' . LOC_MAIL . ">";
							$okmail = sendmail($sender, $mail, $sujet, $bon_message);
						} else
							$okmail = false;
						if (!$okmail) {
							$log .= " [login=" . $login . "]";
							$log .= " [password=" . $pw . "]";
							echo $log . ' -> Créé - Mail PAS envoyé.';
						} else {
							if ($logOk == 1) echo $log . ' -> Ok.';
						}
						$cptadd++;
					} else {
						echo ' -> Erreur : ';
						echo '<p>' . EA_sql_error() . '<br />' . $reqmaj . '</p>';
					}
				}  // complet
			}	// --------- Traitement ----------
		} // par ligne
	} // type TXT
	else {
		msg("Type de fichier incorrect !");
	}
} // fichier d'actes

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	if ($xaction == '')  // parametres par défaut
	{
		if (isset($_COOKIE['chargeUSERlogs']))
			$chargeUSERlogs = $_COOKIE['chargeUSERlogs'];
		else
			$chargeUSERlogs = "111";
		$logOk      = $chargeUSERlogs[0];
		$logKo      = $chargeUSERlogs[1];
		$logRed     = $chargeUSERlogs[2];
		if (isset($_COOKIE['chargeUSERparam']))
			$chargeUSERparam = $_COOKIE['chargeUSERparam'];
		else
			$chargeUSERparam = "042";
		$sendmail   = $chargeUSERparam[0];
		$xdroits    = $chargeUSERparam[1];
		$xregime    = $chargeUSERparam[2];
		$message    = MAIL_NEWUSER;
	}
	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">Chargement de comptes utilisateurs</h2>';
	msg("Veillez à vérifier que le fichier votre fichier CSV respecte le "
		. '<a href="aide/gestuser.html">format</a> ad hoc !', "info");
	echo '<table cellspacing="2" cellpadding="0" border="0" align="center" summary="Formulaire">' . "\n";
	echo " <tr>\n";
	echo '  <td align="right">Fichier utilisateurs CSV : </td>' . "\n";
	echo '  <td><input type="file" size="62" name="Users" />' . "</td>\n";
	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";

	echo " <tr>\n";
	echo "  <td align=right>Droits d'accès AUTO : </td>\n";
	echo '  <td>';
	lb_droits_user($xdroits);
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr><td colspan=2>&nbsp;</td></tr>\n";

	if (GEST_POINTS > 0) {
		echo " <tr>\n";
		echo "  <td align=right>Régime (points) AUTO : </td>\n";
		echo '  <td>';
		lb_regime_user($xregime);
		echo '  </td>';
		echo " </tr>\n";

		echo " <tr><td colspan=2>&nbsp;</td></tr>\n";
	} else {
		echo '<input type="hidden" name="regime" value="' . $xregime . '" />';
	}

	echo " <tr>\n";
	echo '  <td align="right">Envoi des codes d\'accès : </td>' . "\n";
	echo '  <td>';
	echo '    <input type="checkbox" name="SendMail" value="1"' . checked($sendmail) . ' />Envoi automatique du mail ci-dessous&nbsp; ';
	echo '  </td>';
	echo " </tr>\n";

	echo ' <tr>' . "\n";
	echo "  <td align=right>Texte du mail : </td>\n";
	echo '  <td>';
	echo '<textarea name="Message" cols=50 rows=6>' . $message . '</textarea>';
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">Contrôle des résultats : </td>' . "\n";
	echo '  <td>';
	echo '    <input type="checkbox" name="LogOk"  value="1"' . checked($logOk) . ' />Comptes créés &nbsp; ';
	echo '    <input type="checkbox" name="LogKo"  value="1"' . checked($logKo) . ' />Comptes erronés &nbsp; ';
	echo '    <input type="checkbox" name="LogRed" value="1"' . checked($logRed) . ' />Comptes redondants<br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <a href="aide/gestuser.html" target="_blank">Aide</a>&nbsp;';
	echo '  <input type="reset" value="Effacer" />' . "\n";
	echo '  <input type="submit" value=" >> CHARGER >> " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<p>';
	if ($cptadd > 0) {
		echo '<br />User ajoutés  : ' . $cptadd;
		writelog('Ajout USERS CSV ', "", "", $cptadd);
	}
	if ($cptign > 0)  echo '<br />User erronés  : ' . $cptign;
	if ($cptdeja > 0)  echo '<br />User redondants  : ' . $cptdeja;
	echo '<br />Durée du traitement  : ' . (time() - $T0) . ' sec.';
	echo '</p>';
	echo '<p>Retour à la ';
	echo '<a href="' . mkurl("listusers.php", "") . '"><b>' . "liste des utilisateurs" . '</b></a>';
	echo '</p>';
}
echo '</div>';
close_page(1, $root);
