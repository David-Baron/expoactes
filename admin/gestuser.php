<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

pathroot($root, $path, $xcomm, $xpatr, $page);

$id  = getparam('id');
$act = getparam('act');

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

$sendmail   = ischecked('SendMail');
$autopw     = ischecked('autopw');
$xdroits    = getparam('lelevel');
$xregime    = getparam('regime');
$message    = getparam('Message');
$nom        = getparam('nom');
$email      = getparam('email');
$dtexpir    = getparam('dtexpir');

if (getparam('action') == 'submitted') {
	setcookie("chargeUSERparam", $sendmail . $xdroits . $xregime, time() + 60 * 60 * 24 * 60);  // 60 jours
	// setcookie("chargeUSERmessage", $message, time()+60*60*24*180);  // 180 jours
}

$script = file_get_contents("../tools/js/sha1.js");
open_page("Gestion des utilisateurs", $root, $script);

navadmin($root, "Gestion des utilisateurs");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);

echo '</div>';

echo '<div id="col_main_adm">';

if ($id == -1)
	menu_users('A');
else
	menu_users('1'); // ce qui affiche le lien vers ajouter !

if (isset($udbname)) {
	msg('ATTENTION : Données ajoutées/modifiées dans ' . $udbaddr . "/" . $udbuser . "/" . $udbname . "/" . EA_UDB . "</p>", 'info');
}


$missingargs = true;

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

$lelogin = getparam('lelogin');
$lepassw = getparam('lepassw');
$leid = getparam('id');

// Données postées -> ajouter ou modifier
if (getparam('action') == 'submitted') {
	$ok = true;
	if (strlen($nom) < 3) {
		msg('Vous devez préciser le nom de la personne');
		$ok = false;
	}
	if (!valid_mail_adrs($email)) {
		msg("Vous devez préciser une adresse email valide pour la personne");
		$ok = false;
	}
	if (strlen($lelogin) < 3 or strlen($lelogin) > 15) {
		msg('Vous devez donner un LOGIN d\'au moins 3 et au plus 15 caractères');
		$ok = false;
	}
	if (!(sans_quote($lelogin) and sans_quote($lepassw))) {
		msg('Vous ne pouvez pas mettre d\'apostrophe dans le LOGIN ou le MOT DE PASSE');
		$ok = false;
	}
	if ($autopw)
		$pw = MakeRandomPassword(8);
	else
	  if ($id == -1 or !empty($lepassw)) {
		if (strlen($lepassw) < 6 or strlen($lepassw) > 15) {
			msg('Vous devez donner un MOT DE PASSE d\'au moins 6 et au plus 15 caractères');
			$ok = false;
		}
		if ($lepassw <> getparam('passwverif')) {
			msg('Les deux copies du MOT DE PASSE ne sont pas identiques');
			$ok = false;
		}
		$pw = $lepassw;
	} else
		$pw = "";
	$res = EA_sql_query("SELECT * FROM " . EA_UDB . "_user3 WHERE login='" . sql_quote($lelogin) . "' and id <> " . $leid, $u_db);
	if (EA_sql_num_rows($res) != 0) {
		$row = EA_sql_fetch_array($res);
		msg('Ce code de login est déjà utilisé par "' . $row['prenom'] . ' ' . $row['nom'] . '" !');
		$ok = false;
	}
	if (TEST_EMAIL_UNIC == 1) {
		$res = EA_sql_query("SELECT * FROM " . EA_UDB . "_user3 WHERE email='" . sql_quote(getparam('email')) . "' and id <> " . $leid, $u_db);
		if (EA_sql_num_rows($res) != 0) {
			$row = EA_sql_fetch_array($res);
			msg('Cette adresse email est déjà utilisé par "' . $row['prenom'] . ' ' . $row['nom'] . '" !');
			$ok = false;
		}
	}
	if ($ok) {
		$mes = "";
		if ($dtexpir == "")
			$dtexpir = TOUJOURS;
		if ($id <= 0) {
			$maj_solde = date("Y-m-d");
			$reqmaj = "insert into " . EA_UDB . "_user3 "
				. "(nom, prenom, email, level, login, hashpass, regime, solde, maj_solde, statut, dtcreation, dtexpiration, rem, libre)"
				. " values('"
				. sql_quote(getparam('nom')) . "','"
				. sql_quote(getparam('prenom')) . "','"
				. sql_quote(getparam('email')) . "','"
				. sql_quote($xdroits) . "','"
				. sql_quote($lelogin) . "','"
				. sql_quote(sha1($pw)) . "','"
				. sql_quote(getparam('regime')) . "','"
				. sql_quote(getparam('solde')) . "','"
				. sql_quote($maj_solde) . "','"
				. sql_quote(getparam('statut')) . "','"
				. sql_quote($maj_solde) . "','"
				. sql_quote(date_sql($dtexpir)) . "','"
				. sql_quote(getparam('rem')) . "','"
				. sql_quote(getparam('libre')) . "')";
		} else {
			$missingargs = false;
			if (getparam('solde') != getparam('soldepre'))
				$maj_solde = date("Y-m-d");
			else
				$maj_solde = $_REQUEST['maj_solde'];

			$reqmaj = "update " . EA_UDB . "_user3 set ";
			$reqmaj = $reqmaj .
				"NOM        = '" . sql_quote(getparam('nom')) . "', " .
				"PRENOM     = '" . sql_quote(getparam('prenom')) . "', " .
				"EMAIL      = '" . sql_quote(getparam('email')) . "', " .
				"LEVEL      = '" . sql_quote($xdroits) . "', " .
				"LOGIN      = '" . sql_quote($lelogin) . "', ";
			if ($pw <> "")
				$reqmaj = $reqmaj . "HASHPASS   = '" . sql_quote(sha1($pw)) . "', ";
			$reqmaj = $reqmaj .
				"REGIME     = '" . sql_quote(getparam('regime')) . "', " .
				"SOLDE      = '" . sql_quote(getparam('solde')) . "', " .
				"MAJ_SOLDE  = '" . sql_quote($maj_solde) . "', " .
				"DTEXPIRATION= '" . sql_quote(date_sql($dtexpir)) . "', " .
				"STATUT     = '" . sql_quote(getparam('statut')) . "', " .
				"LIBRE      = '" . sql_quote(getparam('libre')) . "', " .
				"REM        = '" . sql_quote(getparam('rem')) . "' " .
				" where ID=" . $id . ";";
		}
		//echo "<p>".$reqmaj."</p>";

		if ($result = EA_sql_query($reqmaj, $u_db)) {
			// echo '<p>'.EA_sql_error().'<br />'.$reqmaj.'</p>';
			if ($id <= 0) {
				$log = "Ajout utilisateur";
				if ($sendmail == 1) {
					$urlsite = EA_URL_SITE . $root . "/index.php";
					$codes = array("#NOMSITE#", "#URLSITE#", "#LOGIN#", "#PASSW#", "#NOM#", "#PRENOM#");
					$decodes = array(SITENAME, $urlsite, $lelogin, $pw, getparam('nom'), getparam('prenom'));
					$bon_message = str_replace($codes, $decodes, $message);
					$sujet = "Votre compte " . SITENAME;
					$sender = mail_encode(SITENAME) . ' <' . LOC_MAIL . ">";
					$okmail = sendmail($sender, getparam('email'), $sujet, $bon_message);
				} else
					$okmail = false;
				if ($okmail) {
					$log .= " + mail";
					$mes = " et mail envoyé";
				} else
					$mes = " et mail PAS envoyé";

				writelog($log, $lelogin, 0);
			} else {
				writelog('Modification utilisateur ', $lelogin, 0);
			}
			echo '<p><b>Fiche enregistrée' . $mes . '.</b></p>';
			$id = 0;
		} else {
			echo ' -> Erreur : ';
			echo '<p>' . EA_sql_error() . '<br />' . $reqmaj . '</p>';
		}
	}
}

if ($id > 0 and $act == "del") {
	$reqmaj = "delete from " . EA_UDB . "_user3 where ID=" . $id . ";";
	if ($result = EA_sql_query($reqmaj, $u_db)) {
		writelog('Suppression utilisateur #' . $id, $lelogin, 1);
		echo '<p><b>FICHE SUPPRIMEE.</b></p>';
		$id = 0;
	} else {
		echo ' -> Erreur : ';
		echo '<p>' . EA_sql_error() . '<br />' . $reqmaj . '</p>';
	}
}

if ($id == -1) {  // Initialisation
	if (isset($_COOKIE['chargeUSERparam']))
		$chargeUSERparam = $_COOKIE['chargeUSERparam'];
	else
		$chargeUSERparam = "042";
	$sendmail   = $chargeUSERparam[0];
	$xdroits    = $chargeUSERparam[1];
	$xregime    = $chargeUSERparam[2];
	$message    = MAIL_NEWUSER;
	/* $_COOKIE['chargeUSERmessage'];
	if ($message=="")
	  {
	  $message  = def_mes_sendmail();
	  }
	*/
	$action = 'Ajout';
	$nom   = "";
	$prenom = "";
	$email = "";
	$lelogin = "";
	$lepassw = "";
	$level = $xdroits;
	$regime = $xregime;
	$solde  = PTS_PAR_PER;
	$maj_solde = today();
	$statut = "N";
	$dtcreation = $maj_solde;
	$dtexpir = dt_expiration_defaut();
	$pt_conso = 0;
	$rem   = "";
	$libre = "";
}

if ($id > 0) {  //
	$action = 'Modification';
	$request = "select NOM, PRENOM, EMAIL, LEVEL, LOGIN, REGIME, SOLDE, MAJ_SOLDE, STATUT, DTCREATION, DTEXPIRATION, PT_CONSO, REM, LIBRE"
		. " from " . EA_UDB . "_user3 "
		. " where ID =" . $id;
	//echo '<P>'.$request;
	if ($result = EA_sql_query($request, $u_db)) {
		$row = EA_sql_fetch_array($result);
		$nom       = $row["NOM"];
		$prenom    = $row["PRENOM"];
		$email     = $row["EMAIL"];
		$level     = $row["LEVEL"];
		$lelogin   = $row["LOGIN"];
		$regime    = $row["REGIME"];
		$solde     = $row["SOLDE"];
		$maj_solde = $row["MAJ_SOLDE"];
		$statut    = $row["STATUT"];
		$dtcreation = $row["DTCREATION"];
		$dtexpir   = $row["DTEXPIRATION"];
		$pt_conso  = $row["PT_CONSO"];
		$rem       = $row["REM"];
		$libre     = $row["LIBRE"];
	} else {
		echo "<p>*** FICHE NON TROUVEE***</p>";
	}
}


//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($id <> 0 and $missingargs) {
	echo '<h2>' . $action . " d'une fiche d'utilisateur</h2> \n";
	//  echo '<form method="post" id="fiche" name="eaform" action="gestuser.php" onsubmit="return  pwProtect();">'."\n";
	echo '<form method="post" id="fiche" name="eaform" action="gestuser.php">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">' . "\n";

	echo " <tr>\n";
	echo "  <td align='right'>Nom : </td>\n";
	echo '  <td><input type="text" size="30" name="nom" value="' . $nom . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Prénom : </td>\n";
	echo '  <td><input type="text" name="prenom" size="30" value="' . $prenom . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>E-mail : </td>\n";
	echo '  <td><input type="text" name="email" size="50" value="' . $email . '" />' . "</td>\n";
	echo " </tr>\n";

	$zonelibre = USER_ZONE_LIBRE;
	if (empty($zonelibre)) $zonelibre = "Zone libre (à définir)";
	echo " <tr>\n";
	echo "  <td align='right'>" . $zonelibre . " : </td>\n";
	echo '  <td><input type="text" name="libre" size="50" value="' . $libre . '" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr><td colspan=2>&nbsp;</td></tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Login : </td>\n";
	echo '  <td><input type="text" name="lelogin" size="15" maxlength="15" value="' . $lelogin . '" />' . "</td>\n";
	echo " </tr>\n";

	if ($id == -1)
		$lecture = "text";
	else
		$lecture = "password";
	echo " <tr>\n";
	echo "  <td align='right'>Mot de passe : </td>\n";
	echo '  <td><input type="' . $lecture . '" name="lepassw" size="15" maxlength="15" />';
	if ($id == -1) {
		echo ' &nbsp; <input type="checkbox" name="autopw" value="1" /> Mot de passe automatique&nbsp; ';
	}
	echo "</td>\n";
	echo " </tr>\n";
	echo " <tr>\n";
	echo "  <td align='right'>Mot de passe (vérif.) : </td>\n";
	echo '  <td><input type="' . $lecture . '" name="passwverif" size="15" maxlength="15" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr><td colspan=2>&nbsp;</td></tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Statut : </td>\n";
	echo '  <td>';
	lb_statut_user($statut);
	if (USER_AUTO_DEF == 1 and ($statut == "A" or $statut == "W")) {
		$urlapp = "approuver_compte.php?id=" . $id . "&action=";
		echo ' --> <a href="' . $urlapp . 'OK">Approuver</a> ou ';
		echo ' <a href="' . $urlapp . 'KO">Refuser</a> ';
	}
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Date entrée : </td>\n";
	echo '  <td>';
	if ($dtcreation != null)
		echo showdate($dtcreation, 'S');
	else
		echo '- Inconnue -';
	echo "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Date expiration : </td>\n";
	if ($dtexpir < today())
		$expiralert = '&nbsp; <b><span style="color:red">EXPIREE</span></b>';
	else
		$expiralert = "";
	$dtexpir = showdate($dtexpir, 'S');
	echo '  <td><input type="text" name="dtexpir" size="10" value="' . $dtexpir . '" />' . $expiralert . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align='right'>Droits d'accès : </td>\n";
	echo '  <td>';
	lb_droits_user($level);
	echo '  </td>';
	echo " </tr>\n";

	if (GEST_POINTS > 0) {
		echo " <tr><td colspan=2>&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo "  <td align='right'>Régime (points) : </td>\n";
		echo '  <td>';
		lb_regime_user($regime);
		echo '  </td>';
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td align='right'>Solde de points : </td>\n";
		echo '  <td><input type="text" name="solde" size="5" value="' . $solde . '" />';
		echo '<input type="hidden" name="soldepre" value="' . $solde . '" />';  // pour test si maj
		echo "</td>\n";
		echo " </tr>\n";

		echo " <tr>\n";
		echo "  <td align='right'>Dernière recharge : </td>\n";
		echo '  <td>' . date("d-m-Y", strtotime($maj_solde)) . "</td>\n";
		echo " </tr>\n";
		echo " <tr><td colspan=2>&nbsp;</td></tr>\n";

		echo " <tr>\n";
		echo "  <td align='right'>Points consommés : </td>\n";
		echo '  <td>' . $pt_conso . "</td>\n";
		echo " </tr>\n";
		echo " <tr><td colspan=2>&nbsp;</td></tr>\n";
	} else {
		echo ' <tr><td colspan="2">';
		echo '<input type="hidden" name="regime" value="' . $regime . '" />';
		echo '<input type="hidden" name="solde" value="' . $solde . '" />';
		echo '<input type="hidden" name="soldepre" value="' . $solde . '" />';
		echo "</td></tr>\n";
	}

	echo " <tr>\n";
	echo "  <td align='right'>Commentaire : </td>\n";
	echo '  <td><input type="text" name="rem" size="50" value="' . $rem . '" />';
	echo '<input type="hidden" name="maj_solde" value="' . $maj_solde . '" />';
	echo "</td>\n";
	echo " </tr>\n";

	if ($id == -1) {
		echo " <tr>\n";
		echo '  <td align="right">Envoi des codes d\'accès : </td>' . "\n";
		echo '  <td>';
		echo '    <input type="checkbox" name="SendMail" value="1"' . checked($sendmail) . ' />Envoi automatique du mail ci-dessous&nbsp; ';
		echo '  </td>';
		echo " </tr>\n";

		echo ' <tr>' . "\n";
		echo "  <td align='right'>Texte du mail : </td>\n";
		echo '  <td>';
		echo '<textarea name="Message" cols=50 rows=6>' . $message . '</textarea>';
		echo '  </td>';
		echo " </tr>\n";
	}

	echo " <tr>\n";
	echo '  <td colspan="2">&nbsp;</td>' . "\n";
	echo " </tr>\n";

	echo " <tr><td align=\"right\">\n";
	echo '  <input type="hidden" name="id" value="' . $id . '" />';
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <a href="aide/gestuser.html" target="_blank">Aide</a>&nbsp;';
	echo '  <input type="reset" value=" Effacer " />' . "\n";
	echo " </td><td align=\"left\">\n";
	echo ' &nbsp; <input type="submit" value=" *** ENREGISTRER *** " />' . "\n";
	if ($id > 0 and $level < 9) {
		echo ' &nbsp; &nbsp; &nbsp; <a href="gestuser.php?id=' . $id . '&amp;act=del">Supprimer cet utilisateur</a>' . "\n";
	}
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<p align="center"><a href="listusers.php">Retour à la liste des utilisateurs</a>';
	if ($leid > 0 and $act != "del")
		echo '&nbsp;|&nbsp; <a href="gestuser.php?id=' . $leid . '">Retour à la fiche de ' . getparam('prenom') . " " . getparam('nom') . '</a>';
	if ($leid == -1 and $act != "del")
		echo '&nbsp;|&nbsp; <a href="gestuser.php?id=-1">Ajout d\'une autre fiche' . '</a>';
	echo '</p>';
}
echo '</div>';
close_page(1);
