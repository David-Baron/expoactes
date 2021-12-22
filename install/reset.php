<?php

error_reporting(E_ALL);

//-------------------------------------------------------------------

$root = '..'; // Pour avoir un lien correct vers consultez code erreurs

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

include("instutils.php");

if (!defined("UPLOAD_DIR")) define("UPLOAD_DIR","_upload");
if (!defined("INCLUDE_HEADER")) define("INCLUDE_HEADER",""); 
if (!defined("SITENAME")) define("SITENAME","Expoactes"); 
if (!defined("SITE_URL")) define("SITE_URL","https://expoactes.monrezo.be/"); 
if (!defined("PIED_PAGE")) define("PIED_PAGE",""); 
if (!defined("EA_VERSION")) define("EA_VERSION",EA_VERSION_PRG); 
if (!defined("EA_MAINTENANCE")) define("EA_MAINTENANCE",0);
if (!defined("EXTERN_MAIL")) define("EXTERN_MAIL",0);

$root="";
$path="";
$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

$serveur = $_SERVER['SERVER_NAME'].' ['.$_SERVER['SERVER_ADDR'].']';

open_page("Désinstallation ExpoActes sur ".$serveur,$root);
$missingargs=true;
echo "<h1>Désinstallation de ExpoActes sur ".$serveur."</h1> \n";
echo "<h3>Vérification de l'environnement</h3> \n";

$minPHPversion = "5.0.0";
if (! check_version(phpversion(),$minPHPversion))
	{
  msg('021 : Ce programme nécessite au moins la version '.$minPHPversion.' de PHP');
  die('Version PHP détectée : '.phpversion());
  }

$db = con_db(1);  // avec affichage de l état de la connexion

$minSQLversion = "4.0.1";
if (! check_version(EA_sql_get_server_info(),$minSQLversion))
	{
  msg('022 : Ce programme nécessite au moins la version '.$minSQLversion.' de MySQL');
  die('Version du serveur MySQL : '.EA_sql_get_server_info());
  }

$tables = array('dec3', 'div3', 'geoloc', 'log', 'mar3', 'metadb', 'metalg', 'mgrplg', 'nai3', 'params', 'prenom', 'sums', 'traceip', 'user3');
foreach ($tables as $k => $v) {
    if ((file_exists('../'.$k)) and ! file_exists('../'.$v ) )  rename('../'.$k, '../'.$v);
    $sql = "drop table ".EA_DB."_$v;";
    echo $sql."\n";
    $result = EA_sql_query($sql);

    if (! $result) {
       echo "<p><b>Suppression table ".EA_DB."_$v pas effectuée</p>";
       exit;
       }
}

exit;
$sql = "select * from ".EA_DB."_user3;";
$result = EA_sql_query($sql);

if ($result) {
   echo "<p><b>Installation déjà réalisée</b> - ";
   echo '<a href="../admin/index.php">Administration de la base ExpoActes</a></p>';
   exit;
   }

$xaction  = getparam('action');
$xlogin   = getparam('login');
$xnom     = getparam('nom');
$xprenom  = getparam('prenom');
$xemail   = getparam('email');
$xpw1     = getparam('pw1');
$xpw2     = getparam('pw2');

if ($xaction == 'submitted')
	{
	  if(empty($xlogin) or empty($xpw1) or empty($xemail) or empty($xpw2) or empty($xnom))
		 {
		 msg("Vous devez préciser votre nom, votre adresse email ainsi que le code d'accès et le mot de passe (2x) de votre choix");
		 }
	  elseif ((strlen($xlogin)>15) or (strlen($xpw1)>15))
		 {
		 msg('Le login et le mot de passe sont limités à 15 catactères');
		 }
	  elseif (!(sans_quote($xlogin) and sans_quote($xpw1)))
  	 {
	   msg('Vous ne pouvez pas mettre d\'apostrophe dans le LOGIN ou le MOT DE PASSE');
	 	 }
	  elseif ($xpw1 <> $xpw2)
		 {
		 msg('Les 2 mots de passe doivent être identiques');
		 }
	  else
		{
		$missingargs=false;
		
	  echo "<h3>Création des tables de données</h3> \n";

    $ok = execute_script_sql('creetables.sql');  // création des tables
		if ($ok)
		  {
		  echo '<p>Toutes tables créées.</p>';
		  }
		 else
			{
			msg("042 : Problème prendant l'exécution du script de génération des tables.");
			die();
			}		  

    $prenoms = file('liste_prenoms.csv');
    $cpt=0;
    reset($prenoms);
    echo '<p>';
    foreach ($prenoms as $line)
      {
     	$line = rtrim($line);  # Get rid of newline characters
     	$line = ltrim($line);  # Get rid of any leading spaces
      if ($line == "" || $line == "\n" || strstr($line,"#") == 1)
      	{
       	next($prenoms);
     		}
     	 else
     		{
     		$reqmaj = "INSERT INTO ".EA_DB."_prenom VALUES ('".sql_quote($line)."')";
	      if ($result = EA_sql_query($reqmaj.';'))
	        {
	        $cpt++;
	        echo ".";
	        }
        }
      }

		echo '</p>';
		echo '<p>'.$cpt.' prénoms féminins enregistrés.</p>';
		
		define('LOC_MAIL',$xemail); // pour initialiser le paramètre automatiquement

		$reqmaj = "insert into ".EA_DB."_user3 (login,hashpass,nom,prenom,email,level)"
		          ." values ('".sql_quote($xlogin)."','".sql_quote(sha1($xpw1))."','".sql_quote($xnom)."','".sql_quote($xprenom)."','".$xemail."',9);";

	  if ($result = EA_sql_query($reqmaj.';'))
			{
			echo "<p>Codes d'accès enregistrés.</p>";
			}
		  else
			{
			echo ' -> Erreur : ';
			echo '<p>'.EA_sql_error().'<br>'.$reqmaj.'</p>';
			$ok = false;
			}

		echo "<h3>Initialisation de la base des paramètres</h3>";
		$par_add = 0;
		$par_mod = 0;
		update_params("act_params.xml",0);  // Création des paramètres manquants et maj des définition des autres

		// Mise à jour de n° de version	
		$sql = "update ".EA_DB."_params set valeur = '".EA_VERSION_PRG."' where param = 'EA_VERSION'";
		EA_sql_query($sql);


		if ($par_add>0)
		  echo "<p>".$par_add." paramètres ajoutés.</p>";


		$okmail = sendmail($xemail,$xemail,'Test messagerie','Ce message de test a été envoyé via ExpoActes');
		if ($okmail)
		  echo "<p>Un mail de test vous a été envoyé. Vérifiez qu'il vous est bien parvenu.</p>";
		 else
		  {
		  echo "<p>La fonction mail n'a PAS pu être vérifée.<br />";
		  echo "<b>Le logiciel peut très bien fonctionner sans mail</b> mais il est impossible d'envoyer automatiquement les mots de passe aux utilisateurs.";
		  }

		$okgd = function_exists('imagettftext');  // fonction de la librairie GD
		if ($okgd)
		  echo "<p>La librairie graphique GD a été vérifiée.</p>";
		 else
		  {
		  echo "<p>La librairie graphique GD n'a PAS pu être vérifée.<br />";
		  echo "<b>Sans cette librairie, il ne sera pas possible de protéger les formulaires d'auto-inscription par un code image.</b></p>";
			$sql = "update ".EA_DB."_params set valeur = '0' where param = 'AUTO_CAPTCHA'";
			EA_sql_query($sql);
		  echo '<p>La génération de "captcha" a donc été (provisoirement) désactivée.</p>';
		  }
		
		test_geocodage(true);
			
		if ($ok)
		  {
		  writelog('Création de la base de données '.EA_VERSION_PRG);
		  echo '<h2>Installation terminée</h2>';
		  echo '<p>Vous pouvez à présent administrer la base.</p>';
		  echo '<p><a href="../admin/index.php">Gestion des actes</a></p>';
		  }
	  }
    }

if($missingargs)
	{
	 echo "<h3>Lecture des coordonnées de l'administrateur de la base</h3> \n";
	 echo '<form method="post"  action="">'."\n";
	 echo '<table cellspacing="0" cellpadding="1" border="0">'."\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Votre nom : </td>\n";
	 echo '  <td><input type="text" name="nom" size=30 value="'.$xnom.'"></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Votre prénom : </td>\n";
	 echo '  <td><input type="text" name="prenom" size=30 value="'.$xprenom.'"></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Votre adresse email : </td>\n";
	 echo '  <td><input type="text" name="email" size=30 value="'.$xemail.'"></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>&nbsp;</td>\n";
	 echo '  <td></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Votre login ExpoActes : </td>\n";
	 echo '  <td><input type="text" name="login" size=20 value="'.$xlogin.'"></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Votre mot de passe : </td>\n";
	 echo '  <td><input type="password" name="pw1" size=20 value=""></td>';
	 echo " </tr>\n";

	 echo " <tr>\n";
	 echo "  <td align=right>Confirmation du mot de passe : </td>\n";
	 echo '  <td><input type="password" name="pw2" size=20 value=""></td>';
	 echo " </tr>\n";


	 echo " <tr><td colspan=\"2\" align=\"center\">\n<br>";
	 echo '  <input type="hidden" name="action" value="submitted">';
	 echo '  <input type="reset" value="Effacer">'."\n";
	 echo ' &nbsp; <input type="submit" value=" *** ENVOYER *** ">'."\n";
	 echo " </td></tr>\n";
	 echo "</table>\n";
	 echo "</form>\n";
	}

load_params();  // pour rafraichir le pied de page 
close_page(0);
?>
