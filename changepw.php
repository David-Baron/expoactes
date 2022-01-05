<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

pathroot($root, $path, $xcomm, $xpatr, $page);

$act = getparam('act');

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

$userlogin = "";

$userlevel = logonok(CHANGE_PW);
while ($userlevel < CHANGE_PW) {
	login($root);
}

$script = file_get_contents("tools/js/sha1.js");
open_page("Changement de mot de passe", $root, $script);
navigation($root, 2, 'A', "Changement de mot de passe");

?>
<script type="text/javascript">
	<!--
	function pwProtect() {
		form = document.forms["eaform"];
		if (form.oldpassw.value == "") {
			alert("Erreur : L'ancien mot de passe est vide !");
			return false;
		}
		if (form.passw.value == "") {
			alert("Erreur : Le nouveau mot de passe est vide !");
			return false;
		}
		if (form.passw.value.length < 6) {
			alert("Erreur : Le nouveau mot de passe est trop court (min 6 caractères) !");
			return false;
		}
		if (!(form.passw.value == form.passwverif.value)) {
			alert("Erreur : Les nouveaux mots de passes ne sont pas identiques !");
			return false;
		}
		if (sha1_vm_test()) // si le codage marche alors on l'utilise 
		{
			form.codedpass.value = hex_sha1(form.passw.value);
			form.codedoldpass.value = hex_sha1(form.oldpassw.value);
			form.passw.value = "";
			form.oldpassw.value = "";
			form.passwverif.value = "";
			form.iscoded.value = "Y";
		}
		return true;
	}
	//
	-->
</script>
<?php

echo '<div id="col_menu">';
form_recherche($root);
//menu_admin($root,$userlevel);
statistiques();
menu_public();
show_pub_menu();
show_certifications();

echo '</div>';

echo '<div id="col_main_adm">';

if ($act == "relogin") {
	echo '<p align="center"><a href="index.php">Retour à la page d\'accueil</a></p>';
	echo '</div>';
	close_page(1);
	exit;
}

$missingargs = true;
$userid = current_user("ID");

if (getparam('action') == 'submitted') {
	$ok = true;
	if (getparam('iscoded') == "N") {
		// Mot de passe transmis en clair  
		if (strlen(getparam('passw')) < 6) {
			msg('Vous devez donner un nouveau MOT DE PASSE d\'au moins 6 caractères');
			$ok = false;
		}
		if (getparam('passw') <> getparam('passwverif')) {
			msg('Les deux copies du nouveau MOT DE PASSE ne sont pas identiques');
			$ok = false;
		}
		if (!(sans_quote(getparam('passw')))) {
			msg('Vous ne pouvez pas mettre d\'apostrophe dans le MOT DE PASSE');
			$ok = false;
		}
		$codedpass = sha1(getparam('passw'));
	} else {
		$codedpass = getparam('codedpass');
	}
	$userpw = current_user("hashpass");
	if (getparam('codedoldpass') <> $userpw) {
		msg('Votre ancien mot de passe n\'est pas correct');
		$ok = false;
	}

	if ($ok) {
		$missingargs = false;
		$reqmaj = "UPDATE " . EA_UDB . "_user3 SET HASHPASS = '" . $codedpass . "' WHERE ID=" . $userid . ";";

		//echo "<p>".$reqmaj."</p>";

		if ($result = EA_sql_query($reqmaj, $u_db)) {
			// echo '<p>'.EA_sql_error().'<br>'.$reqmaj.'</p>';
			writelog('Modification mot de passe ', $_REQUEST['lelogin'], 0);
			echo '<p><b>MOT DE PASSE MODIFIE.</b></p>';
		} else {
			echo ' -> Erreur : ';
			echo '<p>' . EA_sql_error() . '<br>' . $reqmaj . '</p>';
		}
	}
}

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	echo "<h2>Modification de votre mot de passe</h2> \n";
	echo '<form method="post" name="eaform" action="" onsubmit="return  pwProtect();">' . "\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">' . "\n";

	echo " <tr>\n";
	$login = $userlogin;
	echo '  <td align="right">Code utilisateur : </td>' . "\n";
	echo '  <td>' . $login . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">Ancien mot de passe : </td>' . "\n";
	echo '  <td><input type="password" name="oldpassw" size="15" value="" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">Nouveau mot de passe : </td>' . "\n";
	echo '  <td><input type="password" name="passw" size="15" value="" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">Nouveau mot de passe (vérif.) : </td>' . "\n";
	echo '  <td><input type="password" name="passwverif" size="15" value="" />' . "</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td colspan="2">&nbsp;</td>' . "\n";
	echo " </tr>\n";

	echo " <tr><td align=\"right\">\n";
	echo '  <input type="hidden" name="codedpass" value="" />';
	echo '  <input type="hidden" name="codedoldpass" value="" />';
	echo '  <input type="hidden" name="iscoded" value="N" />';
	echo '  <input type="hidden" name="lelogin" value="' . $login . '" />';
	echo '  <input type="hidden" name="action" value="submitted" />';
	echo '  <input type="reset" value=" Effacer " />' . "\n";
	echo " </td><td align=\"left\">\n";
	echo ' &nbsp; <input type="submit" value=" *** MODIFIER *** " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	$mes = 'Vous DEVEZ vous reconnecter avec le nouveau mot de passe.';
	echo '<p align="center"><a href="login.php?cas=4">' . $mes . '</a></p>';
}
echo '</div>';
close_page(1);
