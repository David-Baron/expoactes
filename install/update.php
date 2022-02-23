<?php
// Documentation
// -------------
// les lignes autre que #, @ et > sont ignorées
// # : défini les conditions d'application (à détailler)   TYPE, RENAME, ADDCOL, INDEX, ADDTABLE, ALWAYS
//  #TYPE;Table;NomColonne;Type          :   Applique si la Colonne de la Table n'est pas de Type
//  #RENAME;EA_DB_traceip;OldName;-      =>   ##RENAME;EA_DB_traceip;OldName
//  #ADDCOL;EA_DB_traceip;login;-        =>   #ADDCOL;EA_DB_traceip;login
//  #ADDTABLE;EA_DB_traceip;-;-          =>   #ADDTABLE;EA_DB_traceip
//  #INDEX;Table;Colonne;-   Ceation index sur la "Colonne" de "Table" = controle que la table et la colonne existe  =>  #INDEX;Table;Colonne
//  #ALWAYS;-;-;-                      =>     #ALWAYS   en principe  est suffisant
// @ : Commentaire affiché
// > : x lignes à appliquer  (Note : si UPDATE avec ' LIMIT ' => boucle automatique apres SELECT généré
//

function init_page($head="")
	{
	global $root,$userlevel,$titre, $moderestore,$pageinited;

	if (!$pageinited)
		{
		if ($moderestore or $head=="")
			{
			// pas de remontée des infos car pas de listage des données
			$js="";
			$bodyaction="";
			}
			else
			{
			// Remontées des données finales
			$js = "function updatemessage(){ document.getElementById('topmsg').innerHTML = document.getElementById('finalmsg').innerHTML ; }";
			$bodyaction = " onload='updatemessage()'";
			}
		open_page($titre,$root,$js,$bodyaction,$head);

		$pageinited = true;
		}
	}

function lienautoreload($msg = '', $param_script= '')
	{// lienautoreload( 'Continuez immédiatement avec le fichier suivant', '?TypeActes='.$TypeActes.'&Origine='.$Origine);
	$param_script = str_replace('?', '&', $param_script); // '&TypeActes='.$TypeActes.'&Origine='.$Origine.'
	if (($GLOBALS['tempo_reload'] != 0) )
		{
		echo '<p><a href="'.htmlentities(basename($_SERVER['PHP_SELF'])) .'?autokey='.$GLOBALS['autokey'].'&action=go'. $param_script .'">
				<b>Continuez immédiatement avec le fichier suivant</b></a>';
		if ($GLOBALS['autoload'] == "Y")
			echo '<br />ou laissez le programme continuer seul dans quelques secondes.</p>';
		else
			echo '</p>';
		}
	}

function reload_lecture_token($tokenfile, $autokey, $new = false)
	{
	$trace = '';
	$vals =array('','','','','','','','','');
	if(($tof = fopen($tokenfile,"r")) === FALSE)
		{
		die('Impossible d\'ouvrir le fichier TOKEN en lecture!');
		}
		else
		{
		$vals=explode(";",fgets($tof));
		$trace = fgets($tof);
		fclose($tof);
		if ($vals[0]<>"EA_TOKEN") die('Fichier TOKEN invalide');
		if ( (! $new) and ($vals[1]<>$autokey) ) die('Mauvaise clé');
		if ($new)
			{
			$vals[1] = $autokey;
			}
		}
	return array($vals, $trace);
	}

function reload_write_token($tokenfile, $values, $trace = '')
	{// enregistrement fichier de passage de témoin
	$tof = fopen($tokenfile,"w");
	$token="EA_TOKEN;".$values[0].";".($values[1]).";".($values[2]).";".$values[3].";";
	fwrite($tof,$token."\r\n");
	if ($trace != '')
		{
		fwrite($tof, $trace);
		}
	fclose($tof);
	}

function reload_progress_bar($reload_deja, $nombre_total)
	{ // Affichage pourcentage avancement
	echo '<style> .graphe {  position: relative; /* IE hack */  width: 400px;
             border: 1px solid rgb(255, 204, 102);     padding: 2px;     margin: 0 auto; }
             .graphe .barre {     display: block;    position: relative;     background:rgb(255, 204, 102);     text-align: center;
             color: #333;     height: 2em;     line-height: 2em; } </style>';
	$fait = intval($reload_deja / $nombre_total * 100);
	echo '<p><div class="graphe"><strong class="barre" style="width:'.$fait.'%;">'.$fait.' %</strong></div></p>';
	}

function reload_check_autokey($xaction, $tokenfile, $autokey, $nombre_total, $param_script = '')
	{ //list($autokey, $continue, $nb_refresh, $reload_deja, $trace) = reload_check_autokey($xaction, $tokenfile, $autokey, $nombre_total, $param_script);
	parse_str($_SERVER['QUERY_STRING'], $queries); unset($queries['autokey']); unset($queries['action']);  $param_script = '&' . http_build_query($queries, null, '&amp;');

	switch ($xaction) {
		case '': // 1er lancement
			$autokey = md5(uniqid(rand(), true));
			$continue = 1;
			$reload_deja = -1;
			$nb_refresh = 0; // AJOUT  ++  plus bas donc 1 au premier passage
			$trace = '';
			$tof = fopen($tokenfile, "w");
			$values = array($autokey, $nb_refresh, $reload_deja, $continue);
			$token = "EA_TOKEN;" . $values[0] . ";" . ($values[1]) . ";" . ($values[2]) . ";" . $values[3] . ";";
			fwrite($tof, $token."\r\n");
			fwrite($tof, '');
			fclose($tof);
			break;

		case 'go':
			$metahead = $GLOBALS['metahead'];
			if (! empty($autokey))
			{ // récupération des valeurs dans le fichier
				$x = false;
				if ($autokey == 'reset') $x = true;
				list($vals, $trace) = reload_lecture_token($tokenfile, $autokey, $x);
				$nb_refresh = $vals[2];
				$reload_deja = $vals[3];
				$continue = $vals[4]; // if ($continue == 0) $metahead='';
			}
			else
			die('Erreur de clé');
		break;
	};

	if ( $reload_deja >= $nombre_total)
		{
		$continue=0;
		}

	$reloadurl = htmlentities($_SERVER['PHP_SELF']) . '?autokey=' . $autokey . '&action='; // htmlentities(basename($_SERVER['PHP_SELF']));

	if ($continue == 1) // fichier a suivre
		{
		$reloadurl .= 'go';
		$nb_refresh++;
		}
	$reloadurl .= $param_script;
	$metahead = '<META HTTP-EQUIV="Refresh" CONTENT="' . $GLOBALS['tempo_reload'] . '; URL=' . $reloadurl . '">';

	if ($continue == 0)
		{
		$trace .= '<br><br>Terminé en ' . $nb_refresh . ' fois.';
		$metahead = '';
		}

	return array($autokey, $continue, $nb_refresh, $reload_deja, $trace, $metahead);
	}
//------------------------------------

//$T0 = time();
$bypassTIP=1;
if (file_exists('tools/_COMMUN_env.inc.php')){
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php');
my_ob_start_affichage_continu();

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

$Max_time = min( ini_get("max_execution_time") - 3, MAX_EXEC_TIME) / 2; // On limite très bas pour assurer
if ($Max_time > 15) $Max_time = 15; // Limite à 15 s pour assurer encore plus

// Pour test sur un serveur rapide, appel possible avec par exemple  "?force=&Max=2"
//if (isset($_REQUEST['Max'] ) ) $Max_time  = $_REQUEST['Max']; // DEBUG

// Necessaires pour bouclage : ne pas toucher les valeurs
if (!isset($Max_time)) $Max_time = 15; // doit être positionné mais au cas
$T0 = time();
$autokey = getparam('autokey'); // Clé de vérification
$metahead = ''; // element de gestion du reload
$nb_refresh = 0; // Comptage du nombre de boucle
$continue = 1; // par défaut on continue (initialisation forcée lors du premier appel)
$tempo_reload = 5; // delai temporaisation avant reload auto
$xaction = getparam('action'); // action 'go' ou ''
$trace = ''; // element d'affichage cumulé (si non renseigné pas de gestion)
$reload_deja = -1; // ce paramètre doit être mis à jour en fonction de l'avancement des opérations

// seules valeurs à affecter
$tokenfile  = "../" . DIR_BACKUP . $userlogin . '-update.txt'; // Nom du fichier token (Attention doit dépendre de l'utilisateur
$boucle_max = 1; // Nombre total d'opérations prévues A positionner !
// Fin Necessaires pour bouclage

$next_indice = getparam('start', 0);
if ($next_indice < 0) $next_indice = 0;

// TRAITEMENT DU REFRESH
	$updates = file('update.sql');
	reset($updates);
	$boucle_max = count($updates);
	$cpt=0;

	list($autokey, $continue, $nb_refresh, $reload_deja, $trace, $metahead) = reload_check_autokey($xaction, $tokenfile, $autokey, $boucle_max);
	$next_indice = $reload_deja;

	$titre = "Mise à jour ".EA_VERSION_PRG." ExpoActes sur ".$serveur;
	$js = $bodyaction = '';
	init_page($metahead); // Affichage page avec envoi eventuel meta contenant etiquette METAHEAD de rechargement


$missingargs=true;
echo "<h1>Mise à jour ".EA_VERSION_PRG." de ExpoActes</h1>";

my_flush(); // On affiche un minimum

if (!check_version(EA_VERSION, '3.2.2'))
	{ // si version mémorisée < 3.2.2
	echo '<p class="erreur">Vous utilisez ExpoActes.<br /></p>';
	echo '<p class="erreur">La mont&eacute;e de version d\'ExpoActes n\'est possible que depuis la version 3.2.2.<br /></p>';
	echo 'Installer la version 3.2.2 pour pouvoir faire cette mise &agrave; jour.<br /></p>';
	exit;
	}

$installation_a_jour = false; 
if ( (EA_VERSION >= EA_VERSION_PRG) and (! isset($_REQUEST['force'])) ) // force= pour forcer la MAJ quand elle est déjà faire DEBUG
	{
	echo '<p>Votre installation est à jour.<br /></p>';
	$installation_a_jour = true; 
}
if ( ! $installation_a_jour)
{
$dbok = false;

$db = con_db(1);  // avec affichage de l'état de la connexion

if (!$dbok)
  {
  msg ("Mise à jour impossible : Vérifiez d'abord l'identification de votre base de données MySQL");
  exit;
  }
$sql = "select * from ".EA_DB."_user3;";
$result = EA_sql_query($sql);
$ok = true;
$dopasfait = 0;


if ($result)
	{
  // PHASE 1 : Ajout modif des structures ///////////////////////////////////////
	echo "<h3>Mise à jour de la base de données sur ".$serveur."</h3>";

	//echo "<p>NB : si la mise à jour vient à s'interrompre pour cause de temps dépassé, il suffit de ";
	//echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?start='. ($next_indice) .'"><b>relancer avec ce lien</b></a></p>';

	echo '<p>';
	$doline=0;  // par défaut, ne pas exécuter la line
	$num_line = 0;

	$a_traiter = false;
	$a_traiter = true; // A retirer pour les versions suivantes et adapter "update.sql"

	for ( $num_line = 0; $num_line < $boucle_max; $num_line++)
		{
		$line = $updates[$num_line];
		if (strpos($line,'//Depuis-'.EA_VERSION ) !== false )
			{
				$a_traiter = true;
			}

			{
			// temps suffisant
			$line = rtrim($line);  # Get rid of newline characters
			$line = ltrim($line);  # Get rid of any leading spaces
			if ($line == "" || $line == "\n" || ! $a_traiter || ! in_array( substr($line,0,1) , array('#','@','>')) )
				{ // passe les lignes vides, celles hors scope de la version (Depuis-VVV), et celles non traitées
				continue;
				}
			 else
				{
				//echo '<br>TRAITEMENT DE : ' . $line . ' &nbsp; &nbsp;  ==========>  &nbsp;  '; // DEBUG
				if ($line[0]=="#")
					{
					$doline=0;  // exécuter la line ?
					echo "<p>";
					$cond = explode(";",mb_substr($line . ';', 1));
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
						my_flush();
						$cpt++;
						}
					}
				if ($line[0]==">")
					{
					if ($num_line < $next_indice)
						{
							echo " Ok.";
							continue;
						}

					$line=str_replace("EA_DB_",EA_DB."_",$line);
					$pas_a_pas = false;
					if ($doline==1)  // executer vraiment
						{
						$reqmaj = mb_substr($line,1);
						if  ($res = EA_sql_query($reqmaj))
							{
							if ( ($cond[0] == 'ALWAYS') and ($cond[1] == 'P') )
								{
									$pas_a_pas = true; // Force le pas à pas
								}
							$i = strpos($line, ' LIMIT ');
							//if ( ( strpos($line, 'UPDATE ') !== false ) and ($i !== false) and (strpos($line, ' SELECT ') === false ) )
							//	{ // C'est un UPDATE sans SELECT et avec LIMIT
							//		$pas_a_pas = true; // Force le pas à pas
							//	}
							if ( ( strpos($line, 'UPDATE ') !== false ) and ($i !== false) and (strpos($line, ' SELECT ') !== false ) )
								{ // C'est un UPDATE avec SELECT et LIMIT
								$x = substr( $line , 0, $i); // AVANT LA PARTIE LIMIT
								$i = strrpos($x, 'SELECT ');
								$j = strrpos($x, ' FROM ');
								$x = str_replace ( substr( $x ,$i + 7, $j - $i - 7  ) , ' count(*) ', substr( $x , $i)  ); // Remplace l'extrait entre  SELECT et FROM par count(*)
								// execute la requete ainsi construite
								if  ($res = EA_sql_query($x))
									{
									$row = EA_sql_fetch_array($res);
									if ($row[0] > 0)
										{ // Il faut refaire l'UPDATE
										$num_line = $num_line - 1; // retraiter la même ligne
										}
									}
								}
							else

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
					if ( $pas_a_pas )
						{
						echo "<p>Opérations longues à réaliser, passage en pas à pas, veuillez  ";
						echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?start='. (num_line + 1) .'"><b>poursuivre la mise à jour avec ce lien</b></a>.</p>';
						exit;
						}
					}
				my_flush();
				}
			}

		// check timeout
		$stop = 0;
		$raison = '';
		if (time()-$T0 > $Max_time)
			{
			$stop=1;
			$raison = "Plus assez de temps pour continuer les mises à jour.";  // FACULTATIF
			break;
			}

		} // Fin du for


	$reload_deja = $num_line;
	echo $trace . '<br><br>'; // FACULTATIF MAIS C'EST CELA QUI AFFICHE LE NOMBRE TOTAL ITERATIONS
	echo '<p><b>' . $raison . '</b></p>';
	// CONDITION D'ARRET
	if ( ($num_line + 1) >= $boucle_max)
		{
		if ($metahead != '') echo 'Encore un peu de patience...';
		$continue = 0;
		}
		else
		{
		if (($tempo_reload != 0) )
			{
			echo '<p><a href="'.htmlentities(basename($_SERVER['PHP_SELF'])) .'?autokey='.$autokey.'&action=go">
			<b>Continuez immédiatement avec le fichier suivant</b></a>';
			echo '<br />ou laissez le programme continuer seul dans quelques secondes.</p>';
			}
		}
	// LA ON DOIT CONTINUER DONC ENREGISTRER TOKEN ETC...
	$values = array($autokey, $nb_refresh, ($num_line + 1) , $continue);
	reload_write_token($tokenfile, $values, $trace);

	if ( ($continue != 0 ) or ($metahead != '') ) exit; // Bouclage pour les cas où il y a d'autres choses à faire ou bouclage en cours

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
				'install\licence_gpl.txt', // les licences GPL ont évoluées
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

		// passe en séquence donc identique à goto BasDePage;
		}
	}
	else
	{
	echo "Installation pas ou incomplètement réalisée - ";
	echo '<a href="install.php">Installer ExpoActes</a>';
	exit;
	}
}

BasDePage:
	echo '<p>Vous pouvez à présent administrer la base : ';
	echo '<a href="../admin/index.php">Gestion des actes</a></p>';

load_params();  // pour rafraichir le pied de page
close_page(0);
?>
