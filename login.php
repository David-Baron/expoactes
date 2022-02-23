<?php
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
$bypassTIP=1;
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

$root = "";
$path = "";
$xcomm= "";
$xpatr= "";
$page = "";
pathroot($root,$path,$xcomm,$xpatr,$page);

$uri = getparam('uri');
if ($uri=="") $uri = "index.php";

$script = file_get_contents("tools/js/sha1.js");
open_page("ExpoActes : Login",$root,$script,null,null,'../index.htm');
navigation($root,2,'A',"Connexion");
?>
<script type="text/javascript">

function protect() 
	{
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

	zone_menu(0,0);
	echo '<div id="col_main">'."\n";

if (!check_version(EA_VERSION,EA_VERSION_PRG) or EA_MAINTENANCE==1)
	msg("Le système est en cours de mise à jour. <br/>Merci de revenir plus tard.");
	else
	{
	$motif=getparam('cas');
	$att = "Attention";
	if ($motif==1) msg('Login ou mot de passe incorrect (vérifiez Majuscules/minuscules) !',$att);
	if ($motif==2) msg("L'accès à la page que vous voulez consulter est réservé",$att);
	if ($motif==3) msg('Vos droits sont insuffisants pour accéder à cette page',$att);
	if ($motif==4) msg("Vous devez vous reconnecter avec le nouveau mot de passe",$att);
	if ($motif==5) msg("Votre compte doit encore être activé et/ou approuvé");
	if ($motif==6) msg("Votre compte a expiré. Contactez l'adminstrateur pour le réactiver");
	}

echo '<h2>Vous devez vous identifier : </h2>'."\n";

echo '<form id="log" name="logform" method="post" action="'.$uri.'" onsubmit="return protect()">'."\n";
echo '<table align="center" summary="Formulaire">'."\n";
echo '<tr><td align="right">Login</td><td><input name="login" size="15" maxlength="15" /></td></tr>'."\n";
echo '<tr><td align="right">Mot de passe</td><td><input type="password" name="passwd" size="15" maxlength="15" /></td></tr>'."\n";
echo '<tr><td colspan="2" align="left"><input type="checkbox" name="saved" value="yes" />Mémoriser le mot de passe quelques jours.</td></tr>'."\n";
echo '<tr><td colspan="2" align="center"><input type="submit" value=" Me connecter " /></td></tr>'."\n";
echo '</table>'."\n";
echo '<input type="hidden" name="codedpass" value="" />';
echo '<input type="hidden" name="iscoded" value="N" />';
echo '</form>'."\n";

echoln( '<p><a href="'.$root.'/acces.php">Voir les conditions d\'accès à la partie privée du site</a></p>'."\n");
echoln( '<p><a href="'.$root.'/renvoilogin.php">Login ou mot de passe perdu ?</a></p>'."\n");
if (USER_AUTO_DEF>0)
	{
	if (USER_AUTO_DEF==1)
		$mescpte="Demander ici la création d'un compte d'utilisateur";
	else
		$mescpte="Créer ici votre compte d'utilisateur";
	echoln( '<p><a href="'.$root.'/cree_compte.php"><b>Pas encore inscrit ? '.$mescpte.'</b></a></p>'."\n");
	}
echoln( '<p>&nbsp;</p>'."\n");

echo '</div>'."\n";
close_page(1,$root);
?>