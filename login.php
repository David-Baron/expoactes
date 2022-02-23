<?php
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html
//-------------------------------------------------------------------
$bypassTIP = 1;
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

$root = "";
$path = "";
$xcomm = "";
$xpatr = "";
$page = "";
pathroot($root, $path, $xcomm, $xpatr, $page);

$uri = getparam('uri');
if ($uri == "") $uri = "index.php";

$script = file_get_contents("tools/js/sha1.js");
open_page("ExpoActes : Login", $root, $script, null, null, '../index.htm');
navigation($root, 2, 'A', "Connexion");
?>
<script type="text/javascript">
	function protect() {
		if (sha1_vm_test()) // si le codage marche alors on l'utilise
		{
			form = document.forms["logform"];
			form.codedpass.value = hex_sha1(form.passwd.value);
			form.passwd.value = "";
			form.iscoded.value = "Y";
		}
		return true;
	}
</script>
<?php
//{ print '<pre>LOGIN:';  print_r($_REQUEST); echo '</pre>'; }

zone_menu(0, 0);
echo '<div id="col_main">' . "\n";

if (!check_version(EA_VERSION, EA_VERSION_PRG) or EA_MAINTENANCE == 1)
	msg("Le système est en cours de mise à jour. <br/>Merci de revenir plus tard.");
else {
	$motif = getparam('cas');
	$att = "Attention";
	if ($motif == 1) msg('Login ou mot de passe incorrect (vérifiez Majuscules/minuscules) !', $att);
	if ($motif == 2) msg("L'accès à la page que vous voulez consulter est réservé", $att);
	if ($motif == 3) msg('Vos droits sont insuffisants pour accéder à cette page', $att);
	if ($motif == 4) msg("Vous devez vous reconnecter avec le nouveau mot de passe", $att);
	if ($motif == 5) msg("Votre compte doit encore être activé et/ou approuvé");
	if ($motif == 6) msg("Votre compte a expiré. Contactez l'administrateur pour le réactiver");
	if ($motif == 7) msg("Votre compte est bloqué. Contactez l'administrateur");
}

echo '<h2>Vous devez vous identifier : </h2>' . "\n";

echo "<script type=\"text/javascript\">function seetext(x){ x.type = 'text';}function seeasterisk(x){ x.type = 'password';}</script>";

echo '<form id="log" name="logform" method="post" action="' . $uri . '" onsubmit="return protect()">' . "\n";
echo '<table align="center" summary="Formulaire">' . "\n";
//echo '<tr><td align="right">Login</td><td><input name="login" size="15" maxlength="15" /></td></tr>'."\n";
echo '<tr><td align="right">Login</td><td><input name="login" size="18" maxlength="15" /></td></tr>' . "\n";
//echo '<tr><td align="right">Mot de passe</td><td><input type="password" name="passwd" size="15" maxlength="15" /></td></tr>'."\n";
echo '<tr><td align="right">Mot de passe</td><td><input id="EApwd" type="password" name="passwd" size="15" maxlength="15" />
<img onmouseover="seetext(EApwd)" onmouseout="seeasterisk(EApwd)" style="vertical-center" border="0" src="img/eye-16-16.png" alt="Voir mot de passe" width="16" height="16">
</td></tr>' . "\n";
echo '<tr><td colspan="2" align="left"><input type="checkbox" name="saved" value="yes" />Mémoriser le mot de passe quelques jours.</td></tr>' . "\n";
echo '<tr><td colspan="2" align="center"><input type="submit" value=" Me connecter " /></td></tr>' . "\n";
echo '</table>' . "\n";
echo '<input type="hidden" name="codedpass" value="" />';
echo '<input type="hidden" name="iscoded" value="N" />';
echo '</form>' . "\n";

echoln('<p><a href="' . $root . '/acces.php">Voir les conditions d\'accès à la partie privée du site</a></p>' . "\n");
echoln('<p><a href="' . $root . '/renvoilogin.php">Login ou mot de passe perdu ?</a></p>' . "\n");
if (USER_AUTO_DEF > 0) {
	if (USER_AUTO_DEF == 1)
		$mescpte = "Demander ici la création d'un compte d'utilisateur";
	else
		$mescpte = "Créer ici votre compte d'utilisateur";
	echoln('<p><a href="' . $root . '/cree_compte.php"><b>Pas encore inscrit ? ' . $mescpte . '</b></a></p>' . "\n");
}
echoln('<p>&nbsp;</p>' . "\n");

echo '</div>' . "\n";
close_page(1, $root);
