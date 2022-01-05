<?php

//------------------------------------

//error_reporting(E_ALL);
//$T0 = time();
$Max_time = ini_get("max_execution_time")/2;
$bypassTIP=1;
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');
// récupération des variables éventuelles
if (isset($loc_host)) define("LOC_HOST",$loc_host);
if (isset($loc_mail)) define("LOC_MAIL",$loc_mail);
if (isset($smtp_host)) define("SMTP_HOST",$smtp_host);
if (isset($smtp_pass)) define("SMTP_PASS",$smtp_pass);
if (isset($smtp_acc)) define("SMTP_ACC",$smtp_acc);

include("instutils.php");

$root=$path="";
$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

$userlogin="";
$userlevel=logonok(9);
if ($userlevel<9)
	{
	login($root);
	}

$serveur = $_SERVER['SERVER_NAME'].' ['.$_SERVER['SERVER_ADDR'].']';
open_page("Mise à jour ".EA_VERSION_PRG." ExpoActes sur ".$serveur,$root);
$missingargs=true;
echo "<h1>Mise à jour ".EA_VERSION_PRG." de ExpoActes";

$dbok = false;

$db = con_db(1);  // avec affichage de l état de la connexion

if (!$dbok)
  {
  msg ("Mise à jour impossible : Vérifiez d'abord l'identification de votre base de données MySQL");
  exit;
  }
$sql = "select * from ".EA_DB."_user3;";
$result = EA_sql_query($sql);
$ok = true;
$dopasfait=0;
  
if ($result)
	{
  // PHASE 1 : Ajout modif des structures ///////////////////////////////////////
	echo "<h3>Mise à jour de la base de données sur ".$serveur."</h3>";
	
	echo "<p>NB : si la mise à jour vient à s'interrompre pour cause de temps dépassé, il suffit de "; 
	echo '<a href="update.php"><b>relancer avec ce lien</b></a></p>';

	$updates = file('update.sql');
	$cpt=0;
	reset($updates);
	echo '<p>';
	$doline=0;  // par défaut, ne pas exécuter la line
	foreach ($updates as $line)
		{
		if (time()-$T0>$Max_time)
			{
			// plus assez de temps
			echo "<p>Plus assez de temps pour continuer les mises à jour, veuillez  "; 
			echo '<a href="update.php"><b>relancer la mise à jour avec ce lien</b></a>.</p>';
			exit;	
			$doline=99;
			}
			else
			{
			// temps suffisant
			$line = rtrim($line);  # Get rid of newline characters
			$line = ltrim($line);  # Get rid of any leading spaces
			if ($line == "" || $line == "\n" || strstr($line,"#") == 1)
				{
				next($updates);
				}
			 else
				{
				//echo '<p>'.$line;
				if ($line[0]=="#")
					{
					$doline=0;  // exécuter la line ?
					echo "<p>";
					$cond = explode(";",mb_substr($line,1));
					$cond[1]=str_replace("EA_DB_",EA_DB."_",$cond[1]);
					//print_r( $cond);

					switch ($cond[0])
						{
						case "TYPE" :
							$sql = "SHOW COLUMNS FROM ".$cond[1]." LIKE '".$cond[2]."';";
							if ($res = EA_sql_query($sql))
								{
								if (EA_sql_num_rows($res)>0)
									{
									$row = EA_sql_fetch_array($res);
									 //print_r( $row);
									 if (strtoupper($row[1]) <> strtoupper($cond[3])) $doline=1;
									}
								}
							 else
								{
								msg('Erreur pour exécuter : '.$sql);
								$ok = false;
								}
							break;
						case "RENAME" :
							$res = EA_sql_query("SHOW COLUMNS FROM ".$cond[1]." LIKE '".$cond[2]."';");
							if (EA_sql_num_rows($res)>0)
								{
								$row = EA_sql_fetch_array($res);
								if (strtoupper($row[0]) == strtoupper($cond[2])) $doline=1;
								}
							break;
						case "ADDCOL" :
							$res = EA_sql_query("SHOW COLUMNS FROM ".$cond[1]." LIKE '".$cond[2]."';");
							if (EA_sql_num_rows($res)==0)
								{
								$doline=1;
								}
							break;
						case "INDEX" :
							$res = EA_sql_query("SHOW INDEX FROM ".$cond[1]."; ");
							$nbr = EA_sql_num_rows($res);
							$i=0;
							$doline = 1;  // faire si pas trouvé
							while ($i<$nbr and $doline)
								{
								$row = EA_sql_fetch_array($res);
								if (strtoupper($row[2]) == strtoupper($cond[2])) $doline=false;
								$i++;
								}
							if ($doline==1)
								{
								$res = EA_sql_query("select count(*) as NBRE from ".$cond[1]."; ");
								$row = EA_sql_fetch_array($res);
								$totfiches=$row[0];
								if ((val_var_mysql('wait_timeout')<60 or val_var_mysql('net_write_timeout')<60) and $totfiches > 50000)
									{
									echo "<p>Les paramètres de l'hébergement ne donnent pas assez de temps pour réaliser l'indexation de la table ".$cond[1];
									echo " dans de bonnes conditions. Elle n'est donc pas réalisée automatiquement.</p>";
									echo "<p>La commande d'indexation non exécutée est la suivante :</p>";
									$doline = 2;
									$dopasfait=$dopasfait+1;
									}
								}
							break;
						case "ADDTABLE" :
							$res = EA_sql_query("SHOW TABLES LIKE '".$cond[1]."';");
							if (EA_sql_num_rows($res)==0)
								{
								$doline=1;							
								}
							break;
						case "ALWAYS" :
							$doline=1;							
							break;
						}
					}
				if ($line[0]=="@")
					{
					if ($doline==1)
						{
						$action = mb_substr($line,1);
						echo "<p>".$action;
						$cpt++;
						}
					}
				if ($line[0]==">")
					{
					$line=str_replace("EA_DB_",EA_DB."_",$line);
					if ($doline==1)  // executer vraiment
						{
						$reqmaj = mb_substr($line,1);
						$res = EA_sql_query($reqmaj);
						if ($res === true)
							{
							echo " Ok";
							writelog($action);
							}
						 else
							{
							echo '<font color="#FF0000"> Erreur </font>';
							echo '<p>'.EA_sql_error().'<br>'.$reqmaj.'</p>';
							die();
							}
						}
					if ($doline==2) // Ne pas exécuter mais montrer l'instruction
						{
						$reqmaj = mb_substr($line,1);
						echo "<pre>".$reqmaj."</pre>";
						}
					}
				flush();
				}
			}
		}
	
	// PHASE 2 : Mise à jour des paramètres ///////////////////////////////////
	if ($doline<9)
		{
		echo "<h3>Mise à jour de la base des paramètres</h3>";
		$par_add = 0;
		$par_mod = 0;
		update_params("act_params.xml",0);  // Création des paramètres manquants et maj des définition des autres

		// Mise à jour de n° de version	
		$sql = "update ".EA_DB."_params set valeur = '".EA_VERSION_PRG."' where param = 'EA_VERSION'";
		EA_sql_query($sql);


		if ($par_add>0)
			echo "<p>".$par_add." paramètres ajoutés.</p>";
		if ($par_mod>0)
			echo "<p>".$par_mod." paramètres modifiés.</p>";	

		if ($par_add+$par_mod+$cpt==0 and $dopasfait==0)
			{
			echo "<h2>Votre système est à jour.</h2>";
			}
		 else
			{
			if ($ok and $dopasfait==0) 
				{
				writelog('Mise à jour OK en '.EA_VERSION_PRG);
				echo "<h2>Votre base est mise à jour.</h2>";
				}
			 else 
				{
				writelog('Mise à jour incomplète en '.EA_VERSION_PRG);
				echo "<h2>Base incomplètement mise à jour</h2>";
				}
			}
		if (($par_add+$par_mod+$cpt==0 and $dopasfait==0) or ($ok and $dopasfait==0))   // système déjà à jour ou mise à jour complète
		    {
                $liste_fic= array(
                '_config\connect.inc.php.add', // résidu d'installation très ancienne
                'unins000.exe', // fichier de désinstallation windows
                'unins000.dat', // fichier de désinstallation windows
                '.builpath', // résidu d'installation v3.2.2 ou 3.2.0 ?
                '.gitignore', // résidu d'installation v3.2.2 ou 3.2.0 ?
                '.project', // résidu d'installation v3.2.2 ou 3.2.0 ?
                '.rsyncExclude', // résidu d'installation v3.2.2 ou 3.2.0 ?
                'admin\.gitignore', // résidu d'installation v3.2.2 ou 3.2.0 ?
                'tools\defzones.inc.php', // aucune référence à ce fichier
                'install\creemeta3.sql', // appel était fait dans update3.php
                'install\creetables3.sql', // appel était faitdans update3.php
                'install\supprdataV2.php', // appel dans maj_sums.php
                'install\update3.php', // appel était fait dans index.php
                '__FREE\.htaccess.old',
                '__FREE\htaccess',
                '__FREE'  // cela doit supprimer le dossier
                );
                foreach($liste_fic as $fic)
                {
                    if ( file_exists('../'.$fic) )
                        {
                            echo "<p> Suppression du fichier obsol&egrave;te " . $fic . "</p>";
                            unlink('../'.$fic);
                        }
                }		    
		    }

		echo '<p>Vous pouvez à présent administrer la base : ';
		echo '<a href="../admin/index.php">Gestion des actes</a></p>';
		}
	}
	else
	{
	echo "Installation pas ou incomplètement réalisée - ";
	echo '<a href="install.php">Installer ExpoActes</a>';
	exit;
	}

load_params();  // pour rafraichir le pied de page 
close_page(0);
?>
