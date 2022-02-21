<?php
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');

pathroot($root,$path,$xcomm,$xpatr,$page);

open_page("Activer mon compte utilisateur",$root);
navigation($root,2,"","Activation de mon compte");

echo '<div id="col_menu">'."\n";
menu_public();
show_pub_menu();
echo '</div>'."\n";
echo '<div id="col_main_adm">'."\n";

if (USER_AUTO_DEF==0)
	{
  echo "<p><b>Désolé : Cette action n'est pas autorisée sur ce site</b></p>";
  echo "<p>Vous devez contacter le gestionnaire du site pour demander un compte utilisateur</p>";
  echo '</div>';
	close_page(1);
  die();
  }
  
$missingargs=true;

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

// Données postées -> ajouter ou modifier
$ok = true;
if(empty($_REQUEST['login']))
	{
	msg('Vous devez préciser le login');
	$ok = false;
	}
if(empty($_REQUEST['key']))
	{
	msg('Vous devez inscrire la clé qui vous été envoyée par mail');
	$ok = false;
	}
$res=EA_sql_query("SELECT * FROM ".EA_UDB."_user3 WHERE login='".sql_quote($_REQUEST['login'])
																									."' and  rem='".sql_quote($_REQUEST['key'])
																									."' and  statut='W'",$u_db);
if (EA_sql_num_rows($res)!=1)
	{
	msg('Pas/Plus de compte à activer avec ces valeurs. Vérifiez vos codes.');
	$ok=false;
	}
if ($ok)
  {
  $row = EA_sql_fetch_array($res);
 // $login=$row['login'];
  $id=$row['ID'];
  $nomprenom = $row['prenom'].' '.$row['nom'];
  $login=$row['login']; 
	$missingargs=false;
	$mes = "";
	if (USER_AUTO_DEF==1)
		$statut = 'A';  // attente approbation par admin
	 else
		$statut = 'N';  // normal  
	$reqmaj = "update ".EA_UDB."_user3 set "
								." statut = '".$statut."',"
								." rem = ' '"
								." where id=".$id.";";

		 //echo "<p>".$reqmaj."</p>";
	if  ($result = EA_sql_query($reqmaj,$u_db))
		{
		$crlf = chr(10).chr(13);
		$log = "Activation compte";
		if (USER_AUTO_DEF==1)
			{
			$message  = $nomprenom." (".$login.")".$crlf;
			$message .= "vient de demander accès au site ".SITENAME.".".$crlf;
			$message .= "Vous pouvez APPROUVER cet acces avec le lien suivant : ".$crlf; 
			$message .= EA_URL_SITE.$root."/admin/approuver_compte.php?id=".$id."&action=OK".$crlf;
			$message .= "OU ".$crlf; 
			$message .= "Vous pouvez REFUSER cet acces avec le lien suivant : ".$crlf; 
			$message .= EA_URL_SITE.$root."/admin/approuver_compte.php?id=".$id."&action=KO".$crlf;
			$sujet = "Approbation acces de ".$nomprenom;
			$mes = " Votre demande de compte est soumise à l'approbation de l'administrateur.";
			}
			else
			{
			$message  = $nomprenom." (".$login.")".$crlf;
			$message .= "vient d'obtenir un accès au site ".SITENAME.".".$crlf;
			$sujet = "Validation acces de ".$nomprenom;
			$mes = " Votre compte est actif et vous pouvez à présent vous connecter.";		
			}
		$sender = mail_encode(SITENAME).' <'.LOC_MAIL.">";
		$okmail = sendmail($sender,LOC_MAIL,$sujet,$message);
		if ($okmail)
			{
			$log.= " + mail";
			}
			else
			{
			$log.= " NO mail";
			}
		writelog($log,$login,0);
		echo '<p><b>Votre adresse a été vérifiée.<br />'.$mes.'</b></p>';
		}
		else
		{
		echo ' -> Erreur : ';
		echo '<p>'.EA_sql_error().'<br />'.$reqmaj.'</p>';
		}
	}


//Si pas tout les arguments nécessaire, on affiche le formulaire
if(!$ok)
	{
	$id = -1;
	$action = 'Ajout';
	$login = $_REQUEST['login'];
	$key   = $_REQUEST['key'];
	
	echo '<h2>Activation de mon compte d\'utilisateur</h2>'."\n";
	echo '<form method="post"  action="">'."\n";
	echo '<table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">'."\n";

	echo " <tr>\n";
	echo '  <td align="right">'."Login : </td>\n";
	echo '  <td><input type="text" size="30" name="login" value="'.$login.'" />'."</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td align="right">'."Clé d'activation : </td>\n";
	echo '  <td><input type="text" name="key" size="30" value="'.$key.'" />'."</td>\n";
	echo " </tr>\n";

	echo " <tr>\n";
	echo '  <td colspan="2">&nbsp;</td>'."\n";
	echo " </tr>\n";

	echo " <tr><td align=\"right\">\n";
	echo '  <input type="reset" value=" Effacer " />'."\n";
	echo " </td><td align=\"left\">\n";
	echo ' &nbsp; <input type="submit" value=" *** ACTIVER LE COMPTE *** " />'."\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
  }
 else
  {
	 echo '<p align="center"><a href="index.php">Retour à la page d\'accueil</a></p>';
  }
echo '</div>';
close_page(1);
my_flush();
?>
