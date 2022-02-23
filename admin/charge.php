<?php
ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");

$autorise_autoload = true;  // Autorise le rechargement automatisé
//$autorise_autoload=false;  //Bloque le rerchargement automatisé

//---------------------------------------------------------

function msgplus($msg)
// ouvre la page si elle ne l'est pas encore
{
	init_page();
	msg($msg);
}

//---------------------------------------------------------

function lienautoreload($msg)
{
	global $autoload;
	global $TypeActes, $Origine, $autokey;
	echo '<p><a href="charge.php?action=go&TypeActes=' . $TypeActes . '&Origine=' . $Origine . '&autokey=' . $autokey . '"><b>' . $msg . '</b></a>';
	if ($autoload == "Y")
		echo '<br />ou laissez le programme continuer seul dans quelques secondes.</p>';
	else
		echo '</p>';
}

//---------------------------------------------------------

function init_page($head = "")
{
	global $root, $userlevel, $titre, $moderestore, $pageinited;

	if (!$pageinited) {
		if ($moderestore or $head == "") {
			// pas de remontée des infos car pas de listage des données
			$js = "";
			$bodyaction = "";
		} else {
			// Remontées des données finales
			$js = "function updatemessage(){ document.getElementById('topmsg').innerHTML = document.getElementById('finalmsg').innerHTML ; }";
			$bodyaction = " onload='updatemessage()'";
		}
		open_page($titre, $root, $js, $bodyaction, $head);
?>
		<script type="text/javascript">
			function ShowSpinner() {
				document.getElementById("spinner").style.visibility = "visible";
			}
		</script>
<?php

		// Ajaxify Your PHP Functions   
		include("../tools/PHPLiveX/PHPLiveX.php");
		$ajax = new PHPLiveX(array("getBkFiles"));
		$ajax->Run(false, "../tools/PHPLiveX/phplivex.js");

		navadmin($root, $titre);

		echo '<div id="col_menu">';
		form_recherche($root);
		menu_admin($root, $userlevel);
		echo '</div>';

		echo '<div id="col_main_adm">';
		if ($moderestore)  menu_datas('R');
		$pageinited = true;
	}
}

//---------------------------------------------------------

function quote_explode($sep, $qot, $line)  // découpe la ligne selon le separateur en tenant compte des quotes 
{
	$ai = 0;
	$part = "";
	$cci = strlen($line);
	$inquot = false;
	$ci = 0;
	while ($ci < $cci) {
		if ($line[$ci] == $sep and !$inquot) {
			$tabl[$ai] = $part;
			//echo "<br>".$ai."-".$tabl[$ai];
			$ai++;
			$part = "";
			$ci++;
		} elseif ($line[$ci] == $qot and $part == "" and !$inquot)  // quote en début de partie
		{
			$inquot = true;
			$ci++;
		} elseif ($line[$ci] == $qot and $line[$ci + 1] == $qot)  // quote redoublé -> en garder un seul
		{
			$part .= $line[$ci];
			$ci = $ci + 2;
		} elseif ($line[$ci] == $qot and ($line[$ci + 1] == $sep or $line[$ci + 1] == chr(10) or $line[$ci + 1] == chr(13)))  // quote de fin
		{
			$inquot = false;
			$ci++;
		} else {
			$part .= $line[$ci];
			$ci++;
		}
	}
	if ($part <> "")
		$tabl[$ai] = $part;
	return $tabl;
}

//---------------------------------------------------------

function acte2data($acte, $moderestore)
{
	$data = array();
	$i = 0;
	$lgacte = count($acte);
	global $mdb;
	foreach ($mdb as $zone) {
		if ($i < $lgacte) {
			$data[$zone['ZONE']] = $acte[$i];
			if ($zone['ZONE'] == 'PHOTOS' and !$moderestore)  // cas particulier de la liste des photos
			{
				$i++;  // déjà chargé le 1er !
				while ($i < $lgacte) {
					$data[$zone['ZONE']] .= ";" . $acte[$i];
					$i++;
				}
			}
		} else
			$data[$zone['ZONE']] = "";
		$i++;
	}
	return $data;
}

//------------------------------------------------------------------------------

function getBkFiles($typact)  // Utilisée pour remplir dynamiquement la listbox selon le type d'actes
{
	$restfiles = mydir("../" . DIR_BACKUP, EXT_BACKUP);
	$filterdfiles = array();
	foreach ($restfiles as $bkfile)
		if (isin($bkfile, "_" . $typact . ".") > 1)
			$filterdfiles[] = $bkfile;

	if (!empty($filterdfiles[0])) {
		$k = 0;
		$options[$k] = array("value" => "", "text" => ("Sélectionner un fichier"));
		foreach ($filterdfiles as $bkfile) {
			$k++;
			$options[$k] = array("value" => "$bkfile", "text" => (mb_substr($bkfile, 11)));
		}
	} else {
		$options[0] = array("value" => "", "text" => ("Pas de fichiers à restaurer de ce type"));
	}
	return $options;
}

//---------------------------------------------------------

$root = "";
$path = "";
$delaireload = 10;
$MT0 = microtime_float();
$Max_time = min(ini_get("max_execution_time") - 3, MAX_EXEC_TIME);
$Max_size = return_bytes(ini_get("upload_max_filesize"));

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

$Origine = getparam('Origine');

if ($Origine == "B") {
	$moderestore = true;
	$needlevel = 8;  // niveau d'accès
	$titre = "Restauration d'un backup";
	$lemess = "un backup Expoactes";
	$lefich = "Premier fichier à restaurer :";
	$txtaction = "la restauration";
	$autoload = "Y";
} else {
	$Origine = "N";
	$moderestore = false;
	$needlevel = 6;  // niveau d'accès (anciennement 5)
	$titre = "Chargement d'actes NIMEGUE";
	$txtaction = "le chargement";
	$lemess = "des actes NIMEGUE";
	$lefich = "Fichier de NIMEGUE (V2 ou V3) :";
	$autoload = "Y";
}

$userlevel = logonok($needlevel);
while ($userlevel < $needlevel) {
	login($root);
}

$missingargs = true;
$newload = false;
///////////////////////$emailfound=false;
$oktype = false;
$cptmaj = 0;
$cptadd = 0;
$cptign = 0;
$cptver = 0;
$cptdeja = 0;
$avecidnim = false;
$resume = false;
$bkfilename = "";
$file_no = 0;
$pageinited = false;
$numfile = 0;
$numpart = 0;
$totactes = 0;

$TypeActes = getparam('TypeActes');
if ($TypeActes == "") $TypeActes = 'N';
$mdb = load_zlabels($TypeActes, $lg);

$Dedoublon  = getparam('Dedoublon');

$Filiation  = ischecked('Filiation');
$AnneeVide  = ischecked('AnneeVide');
$AVerifier  = ischecked('AVerifier');
$logOk      = ischecked('LogOk');
$logKo      = ischecked('LogKo');
$logRed     = ischecked('LogRed');
$deposant   = getparam('deposant');
$passlign   = getparam('passlign');
$photo   		= getparam('photo');
$trans   		= getparam('trans');
$verif   		= getparam('verif');
$depart = '';
$NewId      = ischecked('NewId');

if (getparam('action') == 'submitted' and $Origine <> "B")
	setcookie("chargeNIM", $Filiation . $AnneeVide . $AVerifier . $logOk . $logKo . $logRed . $Dedoublon, time() + 60 * 60 * 24 * 60);  // 60 jours

$autokey = getparam('autokey');
$tokenfile  = "../" . DIR_BACKUP . $userlogin . '.txt';
$bkfile = getparam('bkfile');
if (empty($bkfile)) $bkfile = "../" . DIR_BACKUP . getparam('bkfile2');  // lecture directe
if ($autokey == "" or $autokey == "NEW") {
	if (($tof = @fopen($tokenfile, "r")) and $autokey != "NEW") {
		$vals = explode(";", fgets($tof));
		fclose($tof);
		if ($vals[0] == "EA_RESTORE" and $vals[14] > 0 and $vals[1] <> "NEW") {
			$autokey = $vals[1];
			$totactes = $vals[2];
			$uploadfile = $vals[4];
			$TypeActes = mb_substr($uploadfile, -9, 1);
			$reloadurl = 'charge.php?action=go&TypeActes=' . $TypeActes . '&Origine=' . $Origine . '&autokey=' . $autokey;
			$newurl = 'charge.php?action=go&Origine=' . $Origine . '&autokey=NEW';
			msgplus('La dernière restauration n\'était pas terminée !');
			echo '<p><a href="' . $reloadurl . '">Poursuivre la restauration abandonnée</a></p>';
			echo '<p>ou</p>';
			echo '<p><a href="' . $newurl . '">Commencer une nouvelle restauration</a></p>';
			die();
		} else {
			$newload = true;
			$autokey = md5(uniqid(rand(), true)); // généré si lancement direct
			//echo 'NEW interne';
		}
	} else {
		$newload = true;
		$autokey = md5(uniqid(rand(), true)); // généré lancement récupéré new
		//echo 'NEW externe';
	}
}

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }
//{ print '<pre>';  print_r($_FILES); echo '</pre>'; }

$today = today();
$userid = current_user("ID");
$gosuivant = 0; // N° du fichier suivant


$reloadurl = 'charge.php?action=go&TypeActes=' . $TypeActes . '&Origine=' . $Origine . '&autokey=' . $autokey;
if ($autoload == 'Y' and $autorise_autoload)
	$metahead = '<META HTTP-EQUIV="Refresh" CONTENT="' . $delaireload . '; URL=' . $reloadurl . '">';
else
	$metahead = '';

$ok = false;
if (getparam('action') == 'submitted') 	// Lancement d'une opération de chargement ou de restauration
{
	if (empty($TypeActes)) {
		msgplus('Vous devez préciser le type des actes.');
	} elseif ($bkfile == 'NULL') {
		// Cas d'une importation NIMEGUE -> fichier téléchargé en direct
		if (empty($_FILES['Actes']['tmp_name']) and empty($_FILES['Actes']['name'])) {
			msgplus('Pas trouvé le fichier spécifié.');
		} elseif (!is_uploaded_file($_FILES['Actes']['tmp_name'])) {
			msgplus('Méthode de téléchargement non valide.');
			writelog('Possible tentative de téléchargement NIM frauduleux');
		} elseif (empty($_FILES['Actes']['tmp_name']) and !empty($_FILES['Actes']['name'])) {
			msgplus('Fichier impossible à charger (probablement trop volumineux).');
		} elseif (empty($TypeActes)) {
			msgplus('Vous devez préciser le type des actes.');
		} elseif (strtolower(mb_substr($_FILES['Actes']['name'], -4)) <> ".txt") //Vérifie que l'extension est bien '.TXT'
		{ // type TXT
			msgplus("Type de fichier incorrect (.TXT attendu)");
		} elseif (empty($_FILES)) {
			msgplus('Le fichier n\'a pu être chargé, il est peut-être trop volumineux.');
			// NB : Si la taille dépasse post_max_size, alors rien n'est renvoyé et la détection est impossible
		} else
			$ok = true;
		if ($ok)
		// Stockage du fichier chargé
		{
			$uploadfile = UPLOAD_DIR . '/' . $userlogin . '.csv';
			$filename = $_FILES['Actes']['tmp_name'];
			if (!move_uploaded_file($_FILES['Actes']['tmp_name'], $uploadfile)) {
				msgplus('033 : Impossible de ranger le fichier dans "' . UPLOAD_DIR . '".');
				$missingargs = true;
				$ok = false;
			}
		}
		$numpart = 1;
		$totpart = 0;
	} else {
		// Cas de la restauration d'un backup 
		$ok = true;
		$uploadfile = $bkfile;
		$numfile = mb_substr($uploadfile, -7, 3);
		$filename = $bkfile;
		$logOk = $logRed = 0;
		$logKo = 1;
		$numpart = 1;
		$totpart = 0;
	}
} else {
	if (!$newload) // On continue un chargement déjà commencé
	{
		if ($Origine == "N")
			if (isset($_COOKIE['chargeNIM']))
				$chargeNIM  = $_COOKIE['chargeNIM'] . str_repeat(" ", 10);
			else
				$chargeNIM  = "000111M";  // vals par défaut
		else
			$chargeNIM  = "000000M";  // vals pour restauration			
		$Filiation  = $chargeNIM[0];
		$AnneeVide  = $chargeNIM[1];
		$AVerifier  = $chargeNIM[2];
		$logOk      = $chargeNIM[3];
		$logKo      = $chargeNIM[4];
		$logRed     = $chargeNIM[5];
		$Dedoublon  = $chargeNIM[6];
		// récupération des valeurs dans le fichier
		$ok = true;
		if (($tof = @fopen($tokenfile, "r")) === FALSE) {
			$ok = false;
			msgplus('Impossible d\'ouvrir le fichier TOKEN (' . $tokenfile . ') en lecture!');
		} else {
			$ok = true;
			$continue = 0;
			$vals = explode(";", fgets($tof));
			//	{ print '<pre>TOF : ';  print_r($vals); echo '</pre>'; }			
			fclose($tof);
			if ($vals[0] <> "EA_RESTORE") {
				$ok = false;
				msgplus('Fichier TOKEN invalide');
			} elseif ($autokey == "NEW") {
				$ok = false;
			} elseif ($vals[1] <> $autokey) {
				$ok = false;
				msgplus('Clé invalide ou non trouvée');
			} else {
				$totactes = $vals[2];
				$file_no = $vals[3];
				$uploadfile = $vals[4];
				$numfile = mb_substr($uploadfile, -7, 3);
				$deposant = $vals[5];
				$passlign = $vals[6];
				$totpart = $vals[7];
				$numpart = $vals[8];
				$commune = $vals[9];
				$depart = $vals[10];
				$photo  = $vals[11];
				$trans  = $vals[12];
				$verif  = $vals[13];
				$continue = $vals[14];

				$numpart = $numpart + 1;
			}
		}
	} else {  // appel simple de la page 
		$ok = false;
		init_page();
	}
}
switch ($TypeActes) {
	case "N":
		$ntype = "Naissance";
		$table = EA_DB . "_nai3";
		$script = 'tab_naiss.php';
		break;
	case "V":
		$ntype = "Divers";
		$table = EA_DB . "_div3";
		$script = 'tab_bans.php';
		break;
	case "M":
		$ntype = "Mariage";
		$table = EA_DB . "_mar3";
		$script = 'tab_mari.php';
		break;
	case "D":
		$ntype = "Décès";
		$table = EA_DB . "_dec3";
		$script = 'tab_deces.php';
		break;
}

if ($ok)
	if (isset($continue) and $continue == 0) {
		init_page("");
		echo '<h2 align="center">' . $titre . '</h2>';
		$missingargs = false;
		if ($Origine == "B") {
			echo '<p>Restauration terminée, ' . $totactes . ' actes traités en ' . ($totpart + 1) . ' étapes.</p>';
			echo '<p>Optimisation de la table ...';
			$reqmaj = "ANALYZE TABLE " . $table . ";";
			$res = mysql_query($reqmaj);
			echo " Ok.</p>";
			writelog($reqmaj);
			maj_stats($TypeActes, $T0, $path, "A", "");
		} else {
			echo '<p>Chargement terminé, ' . $totactes . ' actes traités en ' . ($totpart + 1) . ' étapes.</p>';
			maj_stats($TypeActes, $T0, $path, "C", $commune, $depart);
			echo '<p>Voir la liste des actes de ';
			echo '<a href="' . mkurl($script, stripslashes($commune . ' [' . $depart . ']')) . '"><b>' . stripslashes($commune) . '</b></a>';
			echo '</p>';
		}
	} else
	// Suite du traitement
	{ // fichier d'actes

		$missingargs = false;
		$csv = file($uploadfile);
		$no_ligne = 0;
		$fichiersuivant = false;

		$T1 = $T0;
		$isUTF8 = false;
		while (list($line_num, $line) = each($csv) and (time() - $T0 < $Max_time)) { // par ligne
			/*
			$TX = time();
			if ($T1<>$TX)
				{
			  echo "<p>Déjà ".($TX-$T0)." sec.</p>";
			  $T1=$TX;
			  }
			*/
			if ($line_num == 0)   // SI c'est la première ligne
			{
				if ($Origine == "N")  // on décode la première pour vérifier le contenu
					$acte = quote_explode(';', '', $line);  // pas de " dans les NIMEGUES
				else
					$acte = quote_explode(';', '"', $line);
				switch ($acte[0]) {
					case "EA2":
						$identif = "EA2";
						$mdb = load_zlabels($TypeActes, $lg, "EA2");
						$minzones = count($mdb);
						break;
					case "EA3":
						$identif = "EA3";
						$mdb = load_zlabels($TypeActes, $lg, "EA3");
						$minzones = count($mdb);
						break;
					case "EA32":
						$identif = "EA32";
						$mdb = load_zlabels($TypeActes, $lg, "EA3");
						$minzones = count($mdb);
						$isUTF8 = true;
						break;
					case "NIMEGUE-V2":
						$lemess = "des actes NIMEGUE version 2";
						$lefich = "Fichier de NIMEGUE-V2 :";
						$identif = "NIMEGUE-V2";
						$mdb = load_zlabels($TypeActes, $lg, "NIM2");
						$minzones = count($mdb);
						break;
					case "NIMEGUEV3":
						$lemess = "des actes NIMEGUE version 3";
						$lefich = "Fichier de NIMEGUEV3 :";
						$identif = "NIMEGUEV3";
						$mdb = load_zlabels($TypeActes, $lg, "NIM3");
						$minzones = count($mdb) - 1; // PHOTOS est facultatif
						break;
					default:
						$identif = "PASBON";
				}
				//	{ print '<pre>';  print_r($acte); echo '</pre>'; }				
				//	echo '<pre>'; print_r($mdb); echo '</pre>';

				$data = acte2data($acte, $moderestore);

				if ($data['BIDON'] <> $identif) {
					echo "<div id='topmsg'></div>";
					$errdesc = 'Le fichier <i>"' . $filename . '"</i> ne contient pas ' . $lemess . $data['BIDON'];
					msgplus($errdesc);
					$no_ligne = -1;
					break;
				} elseif ($data['TYPACT'] <> $TypeActes) {
					echo "<div id='topmsg'></div>";
					$errdesc = 'Le fichier "' . $filename . '" ne contient pas des actes du type annoncé <br />mais des actes ' . $acte[5];
					msgplus($errdesc);
					$no_ligne = -1;
					break;
				} else {
					$oktype = true;
					init_page($metahead);
					echo '<h2 align="center">' . $titre . '</h2>';
					if ($numfile > 0)
						echo '<p>Fichier : <b>' . mb_substr($uploadfile, 11) . '</b></p> ';
					if ($numpart > 1)
						echo '<p>Partie ' . $numpart . '</p>';

					echo '<div id="topmsg"><p>Traitement en cours ... <b>!! NE PAS INTERROMPRE !!</b></p><p align="center"><img src="../img/spinner.gif"></p></div>';
					my_flush();
				}
			} // ligne 0

			$no_ligne = $line_num;
			$reqmaj = "";
			if (!$isUTF8) {
				$line = ea_utf8_encode($line); // ADLC 24/09/2015
			}
			if ($Origine == "N")
				$acte = quote_explode(';', '', $line);  // pas de " dans les NIMEGUES
			else
				$acte = quote_explode(';', '"', $line);

			if ($oktype == true and $line_num >= $passlign) {	// --------- Traitement ----------
				$data = acte2data($acte, $moderestore);
				if ($data["BIDON"] == "EA_NEXT") {
					$fichiersuivant = true;
					$gosuivant = $acte[1];
					break;
				} elseif ($data["BIDON"] <> $identif) {
					// format invalide
					$cptign++;
					if ($logKo == 1) echo "<br />LIGNE " . ($line_num + 1) . " INVALIDE -> Ignorée";
				} elseif (count($acte) < $minzones) {
					// format invalide
					$cptign++;
					if ($logKo == 1) echo "<br />LIGNE " . ($line_num + 1) . " INCOMPLETE " . count($acte) . "/" . $minzones . " -> Ignorée";
				} else { // complet 
					$lignvalide = false;
					$missingargs = false;
					$commune = $data["COMMUNE"];
					$depart  = $data["DEPART"];
					$datetxt = $data["DATETXT"];
					if ($TypeActes == "V") {
						$decal = 2;
					} else {
						$decal = 0;
					}
					$nom     = trim($data["NOM"]);
					$pre     = trim($data["PRE"]);
					$idnim   = $data["IDNIM"];
					$log = '<br />' . $ntype . ' ' . $nom . ' ' . $pre . ' le ' . $datetxt . ' à ' . stripslashes($commune) . " : ";
					if ($moderestore) {
						$ladate = $data["LADATE"];
						if ($NewId == 1) {
							$id     = 'null';
							$condit = "ID=0";
						} else {
							$id     = $data["ID"];
							$condit = "ID=" . $id;
						}
						$deposant = $data["DEPOSANT"];
						$dtdepot = $data["DTDEPOT"];
						$dtmodif = $data["DTMODIF"];
						if ($acte[0] == "EA2") {
							$photo = "";
							$trans = "";
							$verif = "";
						} else {
							$photo = $data["PHOTOGRA"];
							$trans = $data["RELEVEUR"];
							$verif = $data["VERIFIEU"];
						}
						$lignvalide = true;
					} else { // mode nimegue
						$id      = 'null';
						$dtdepot = $today;
						$dtmodif = $today;
						$ladate = "";
						$avecidnim = ($data["IDNIM"] > 0);
						$MauvaiseDate = 0;
						ajuste_date($datetxt, $ladate, $MauvaiseDate);
						if (($data["P_NOM"] . $data["P_PRE"] . $data["M_NOM"] . $data["M_NOM"] == "") and ($Filiation == 1))
						// pas le nom d'au moins un parent
						{
							$cptign++;
							if ($logKo == 1) echo $log . "INCOMPLET (" . ($line_num + 1) . ") -> Ignoré";
						} elseif ($avecidnim and intval($idnim) == 0 and $acte[0] <> "EA2") {
							$cptign++;
							if ($logKo == 1) echo $log . "INVALIDE (; dans une zone) (" . ($line_num + 1) . ") -> Ignoré";
						} elseif ($commune == "")
						// acte sans commune
						{
							$cptign++;
							if ($logKo == 1) echo $log . "PAS DE COMMUNE (" . ($line_num + 1) . ") -> Ignoré";
						} elseif ($nom == "")
						// acte sans nom
						{
							$cptign++;
							if ($logKo == 1) echo $log . "PAS DE NOM (" . ($line_num + 1) . ") -> Ignoré";
						} elseif (($AnneeVide == 1) and ($MauvaiseDate == 1))
						// acte avec année incomplète (testée dans ajuste_date)
						{
							$cptign++;
							if ($logKo == 1) echo $log . "ANNEE INCOMPLETE (" . ($line_num + 1) . ") -> Ignoré";
						} elseif (($AVerifier == 1) and (strpos($acte[8], "RIF") > 0))
						// acte restant à vérifier
						{
							$cptver++;
							if ($logKo == 1) echo $log . "A VERIFIER (" . ($line_num + 1) . ") -> Ignoré";
						} else {  // complet
							$lignvalide = true;
							if ($avecidnim and ($Dedoublon == 'I' or $Dedoublon == 'M')) {
								$condit = "COMMUNE='" . sql_quote($commune) . "' and DEPART='" . sql_quote($depart) . "' and IDNIM=" . $idnim;
							} else {
								$condit = "COMMUNE='" . sql_quote($commune) . "' and DEPART='" . sql_quote($depart) . "' and DATETXT='" . $datetxt . "' and NOM='" . sql_quote($nom) . "' and PRE='" . sql_quote($pre) . "'";
							}
						}
					}
					if ($lignvalide) {
						// Dédoublonnage
						if ($Dedoublon <> 'A' and $NewId == 0) {
							$request = "select ID from " . $table . " where " . $condit . ";";
							//echo '<p>'.$no_ligne." -> ".$request.'</p>';
							$result = mysql_query($request);
							$nb = mysql_num_rows($result);
						} else
							$nb = 0;
						if ($nb > 0) { // record existe
							if ($moderestore or $Dedoublon == 'M') { // MAJ
								$ligne = mysql_fetch_assoc($result);
								$id = $ligne["ID"];
								//------
								$reqtest = "select * from " . $table . " where " . $condit . ";";
								$restest = mysql_query($reqtest);
								$ligtest = implode('|', mysql_fetch_row($restest));
								$ligtest = mb_substr($ligtest, 0, strlen($ligtest) - 10); // éliminer la date de dernière modif
								$crc1 = crc32($ligtest);
								//echo $crc1. " --> ".$ligtest;
								//------
								$action = "MISE A JOUR";

								$listmaj = "";
								foreach ($mdb as $zone) {
									if ($listmaj <> "") $listmaj .= ", ";
									$listmaj .= $zone['ZONE'] . "='" . sql_quote($data[$zone['ZONE']]) . "'";
								}
								$listmaj .= ",LADATE = '" . $ladate . "'";
								$listmaj .= ",DEPOSANT = '" . $deposant . "'";
								$listmaj .= ",PHOTOGRA = '" . sql_quote($photo) . "'";
								$listmaj .= ",RELEVEUR = '" . sql_quote($trans) . "'";
								$listmaj .= ",VERIFIEU = '" . sql_quote($verif) . "'";
								$listmaj .= ",DTMODIF= '" . $today . "' ";
								$reqmaj = "update " . $table . " set " . $listmaj . " where ID=" . $id . ";";
							} // MAJ
							else {
								$cptdeja++;
								if ($logKo == 1) echo $log . "Acte déjà présent (" . ($line_num + 1) . ") -> Ignoré";
							}
						} // record existe
						else { // ADD
							$action = "AJOUT";
							$crc1 = 0;
							$listzon = "";
							$listmaj = "";
							foreach ($mdb as $zone) {
								if ($zone['ZONE'] == "LADATE")
									break;  // les autres sont gérés autrement ci-dessous
								if ($listzon <> "") $listzon .= ", ";
								$listzon .= $zone['ZONE'];
								if ($listmaj <> "") $listmaj .= ", ";
								$listmaj .= "'" . sql_quote($data[$zone['ZONE']]) . "'";
							}
							if (true) // (!$moderestore)  // dans tous les cas à présent
							{
								$listzon .= ", LADATE, ID, DEPOSANT, PHOTOGRA, RELEVEUR, VERIFIEU, DTDEPOT, DTMODIF";
								$listmaj .= ",'" . $ladate . "'," . $id . "," . $deposant . ",'" . sql_quote($photo) . "','" . sql_quote($trans) . "','" . sql_quote($verif) . "','" . $dtdepot . "','" . $dtmodif . "'";
							}
							//echo "<p>".$listzon." <br>--> ".$listmaj." <br>cond-> ".$condit;
							$reqmaj = "insert into " . $table . "(" . $listzon . ") values (" . $listmaj . ");";
						} // ADD
						//	if ($cptadd+$cptmaj<5)	echo "<p>".$reqmaj;

						if (!empty($reqmaj)) {
							if ($result = mysql_query($reqmaj)) {
								if ($NewId == 0) // pas d'ajout forcé
								{
									//------
									$reqtest = "select *   from " . $table . " where " . $condit . ";";
									$restest = mysql_query($reqtest);
									$ligtest = implode('|', mysql_fetch_row($restest));
									$ligtest = mb_substr($ligtest, 0, strlen($ligtest) - 10); // éliminer la date de dernière modif
									$crc2 = crc32($ligtest);
									//------
								} else
									$crc2 = 1; // bidon
								if ($crc2 != $crc1) {
									if ($logOk == 1) echo $log . $action . ' -> Ok.';
									if ($nb > 0) {
										$cptmaj++;
									} else {
										$cptadd++;
									}
								} else {
									if ($logRed == 1) echo $log . 'Non modifié';
									$cptdeja++;
								}
							} else {
								echo ' -> Erreur : ';
								echo '<p>' . mysql_error() . '<br />' . $reqmaj . '</p>';
							}
						} // reqmaj pas vide	
					}  // complet
				} // lignvalide
			}	// --------- Traitement ----------
		} // par ligne	
		if ($no_ligne == -1) {
			$continue = 0;  // fin sur erreur	
		} elseif ($acte[0] == "") //($no_ligne==0)
		{
			msg("Fichier " . $uploadfile . " vide ou absent !");
		} elseif ($no_ligne + 1 < count($csv))  // Si interruption 
		{
			// Fin du temps -> relancer pour continuer
			echo "<div id='finalmsg'>";
			echo "<p><b>Attention : Temps maximum d'éxecution écoulé</b></p>";
			echo '<p>' . ($no_ligne + 1) . ' lignes déjà traitées;</p>';
			echo '<p>Il reste ' . (count($csv) - $no_ligne - 1) . ' lignes à traiter.</p>';
			$resume = true;
			$passlign = $no_ligne + 1;
			$totpart = $totpart + 1;
			$continue = 1;
			lienautoreload('Continuez immédiatement ' . $txtaction);
			echo "</div>";
		} elseif ($gosuivant > 0)  // Si fichier suivant
		{
			// Passer au fichier suivant
			$file_no = $file_no + 1;
			$numfile = $numfile + 1;
			$numpart = 0;
			$totpart = $totpart + 1;
			$uploadfile = mb_substr($uploadfile, 0, -7) . zeros($numfile, 3) . '.bea';
			$totactes = $totactes + $no_ligne;
			$passlign = 0;
			echo "<div id='finalmsg'>";
			lienautoreload('Continuez immédiatement avec le fichier ' . zeros($numfile, 3));
			echo "</div>";
			$continue = 2;
		} else {
			// Fin du dernier fichier
			echo "<div id='finalmsg'>";
			echo "<p>Traitement terminé.</p>";
			$continue = 0;
			$totactes = $totactes + $no_ligne + 1;
			if (!($moderestore)) {
				echo '<p>Chargement terminé, ' . $totactes . ' actes traités en ' . ($totpart + 1) . ' étapes.</p>';
				maj_stats($TypeActes, $T0, $path, "C", $commune, $depart);
				echo '<p>Voir la liste des actes de ';
				echo '<a href="' . mkurl($script, stripslashes($commune . ' [' . $depart . ']')) . '"><b>' . stripslashes($commune) . '</b></a>';
				echo '</p>';
			} else {
				lienautoreload('Recalculer les statistiques');
			}
			echo "</div>";
		}
	} // fichier d'actes

//if ($resume) 
if (!$missingargs) {
	$tof = @fopen($tokenfile, "w");
	$token  = "EA_RESTORE;" . $autokey . ";";      // 0 et 1
	$token .= ($totactes) . ";" . ($file_no) . ";";	 // 2 et 3 : total des actes et indice fichier backup
	$token .= $uploadfile . ";" . ($deposant) . ";"; // 4 et 5 : nom fichier import NIMEGUE + code déposant
	$token .= ($passlign) . ";";                 // 6  : lignes à passer 
	$token .= ($totpart) . ";" . ($numpart) . ";";   // 7 et 8 : totale de parties et de la partie traitée	
	$token .= ($commune) . ";" . ($depart) . ";";		 // 9 et 10: commune et depart
	$token .= ($photo) . ";" . ($trans) . ";" . ($verif) . ";";	// 11,12 et 13: credits

	if (!isset($continue)) $continue = 0;
	$token .= ($continue) . ";";								 // 14 : continue ? 
	fwrite($tof, $token . "\r\n");
	fclose($tof);
}

init_page("");

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	if (getparam('action') == '')  // parametres par défaut
	{
		if (isset($_COOKIE['chargeNIM']))
			$chargeNIM  = $_COOKIE['chargeNIM'] . str_repeat(" ", 10);
		else
			$chargeNIM  = "000111M";
		$Filiation  = $chargeNIM[0];
		$AnneeVide  = $chargeNIM[1];
		$AVerifier  = $chargeNIM[2];
		$logOk      = $chargeNIM[3];
		$logKo      = $chargeNIM[4];
		$logRed     = $chargeNIM[5];
		$Dedoublon  = $chargeNIM[6];
		$TypeActes = "X";
	}
	if ($moderestore)
		$ajaxbackup = ' onClick="' . "getBkFiles(this.value, {'content_type': 'json', 'target': 'bkfile', 'preloader': 'prl'})" . '" ';
	else
		$ajaxbackup = '';

	echo "<div id='topmsg'></div>";
	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">' . $titre . '</h2>';
	echo '<table cellspacing="0" cellpadding="0" border="0" align="center" summary="Formulaire">' . "\n";
	echo " <tr>\n";
	echo '  <td align="right">Type des actes : </td>' . "\n";
	echo '  <td>';
	echo '        <input type="radio" name="TypeActes" value="N"' . checked($TypeActes, 'N') . $ajaxbackup . ' />Naissances<br />';
	echo '        <input type="radio" name="TypeActes" value="M"' . checked($TypeActes, 'M') . $ajaxbackup . ' />Mariages<br />';
	echo '        <input type="radio" name="TypeActes" value="D"' . checked($TypeActes, 'D') . $ajaxbackup . ' />Décès<br />';
	echo '        <input type="radio" name="TypeActes" value="V"' . checked($TypeActes, 'V') . $ajaxbackup . ' />Actes divers<br />';
	echo '        <br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr>\n";
	echo "  <td>" . $lefich . " </td>\n";
	if ($moderestore) {
		echo '<td> <select id="bkfile" name="bkfile">';
		echo '    <option value="">Choisir d\'abord le type d\'acte</option> ';
		echo '  </select><img id="prl" src="../img/minispinner.gif" style="visibility:hidden;"></td>';
	} else {
		echo '  <td><input type="file" size="62" name="Actes" />';
		// MAX_FILE_SIZE doit précéder le champs input de type file
		echo '  <input type="hidden" name="MAX_FILE_SIZE" value="' . $Max_size . '" />';
		echo '  <input type="hidden" name="bkfile" value="NULL" />';
		echo "</td>\n";
	}
	echo " </tr>\n";
	if ($moderestore) {
		echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Migration de données : </td>' . "\n";
		echo '  <td>';
		echo '        <input type="checkbox" name="NewId" value="1" /> AJOUTER toutes ces données à la base SANS vérification <br />'; // jamais par défaut
		echo '  </td>';
		echo " </tr>\n";
	}

	if (!$moderestore) {
		if (isin('OFA', metadata('AFFICH', 'PHOTOGRA')) >= 0) {
			echo " <tr>\n";
			echo '  <td align="right">' . metadata('ETIQ', 'PHOTOGRA') . ' : </td>' . "\n";
			echo '  <td><input type="text" size="40" name="photo" value="' . $photo . '" />';
			//	 echo '   ou <input type="checkbox" name="photocsv" value="1" '.checked($photocsv).'/> Lu dans le CSV ';
			echo "  </td>\n";
			echo " </tr>\n";
			echo " <tr>\n";
		}
		if (isin('OFA', metadata('AFFICH', 'RELEVEUR')) >= 0) {
			echo " <tr>\n";
			echo '  <td align="right">' . metadata('ETIQ', 'RELEVEUR') . ' : </td>' . "\n";
			echo '  <td><input type="text" size="40" name="trans" value="' . $trans . '" />';
			// echo '   ou <input type="checkbox" name="transcsv" value="1" '.checked($transcsv).'/> Lu dans le CSV ';
			echo "  </td>\n";
			echo " </tr>\n";
			echo " <tr>\n";
		}
		if (isin('OFA', metadata('AFFICH', 'VERIFIEU')) >= 0) {
			echo " <tr>\n";
			echo '  <td align="right">' . metadata('ETIQ', 'VERIFIEU') . ' : </td>' . "\n";
			echo '  <td><input type="text" size="40" name="verif" value="' . $verif . '" />';
			// echo '   ou <input type="checkbox" name="verifcsv" value="1" '.checked($verifcsv).'/> Lu dans le CSV ';
			echo "  </td>\n";
			echo " </tr>\n";
			echo " <tr>\n";
		}
		echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Dédoublonnage : </td>' . "\n";
		echo '  <td>';
		echo '        <input type="radio" name="Dedoublon" value="N"' . checked($Dedoublon, 'N') . ' />Sur la combinaison date+nom+prenom<br />';
		echo '        <input type="radio" name="Dedoublon" value="I"' . checked($Dedoublon, 'I') . ' />Sur le n° ID de NIMEGUE (Ignorer si existe déjà)<br />';
		echo '        <input type="radio" name="Dedoublon" value="M"' . checked($Dedoublon, 'M') . ' />Sur le n° ID de NIMEGUE (Mettre à jour si existe déjà)<br />';
		echo '        <input type="radio" name="Dedoublon" value="A"' . checked($Dedoublon, 'A') . ' />Aucune vérification<br />';
		echo '        <br />';
		echo '  </td>';
		echo " </tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Filtrage des données : </td>' . "\n";
		echo '  <td>';
		echo '        <input type="checkbox" name="Filiation" value="1"' . checked($Filiation) . ' />Eliminer les actes sans filiation <br />';
		echo '        <input type="checkbox" name="AnneeVide" value="1"' . checked($AnneeVide) . ' />Eliminer les actes dont l\'année est incomplète (ex. 17??)<br />';
		echo '        <input type="checkbox" name="AVerifier" value="1"' . checked($AVerifier) . ' />Eliminer les actes "A VERIFIER" ("VERIF" dans zone Cote) <br />';
		echo '  </td>';
		echo " </tr>\n";
		echo " <tr>\n";
		echo '  <td align="right"> <br />Contrôle des résultats : </td>' . "\n";
		echo '  <td>';

		echo '    <br /><input type="checkbox" name="LogOk" value="1"' . checked($logOk) . ' />Actes chargés &nbsp; ';
		echo '        <input type="checkbox" name="LogKo" value="1"' . checked($logKo) . ' />Actes erronés &nbsp; ';
		echo '        <input type="checkbox" name="LogRed" value="1"' . checked($logRed) . ' />Actes redondants<br />';
		echo '  </td>';
		echo " </tr>\n";
		if ($userlevel >= 8) {
			echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
			echo " <tr>\n";
			echo '  <td align="right">Déposant : </td>' . "\n";
			echo '  <td>';
			listbox_users("deposant", $userid, DEPOSANT_LEVEL);
			echo '  </td>';
			echo " </tr>\n";
		} else
			echo '  <input type="hidden" name="deposant" value="' . $userid . '" />';
		echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	}
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	//	 echo '  <input type="hidden" name="uploadfile" value="NULL" />';
	echo '  <input type="hidden" name="action" value="submitted" />';
	// echo '  <input type="hidden" name="autokey" value="" />';
	echo '  <a href="aide/charge.html" target="_blank">Aide</a>&nbsp;';
	echo '  <input type="reset" value="Effacer" />' . "\n";
	echo '  <input type="submit" value=" >> CHARGER >> "  onclick="ShowSpinner();" />' . "\n";
	echo " </td></tr>\n";
	echo '<tr><td colspan="2" align="center"><span id="spinner" style="visibility:hidden"><img src="../img/spinner.gif"></span></td></tr>';
	echo "</table>\n";
	echo "</form>\n";
	echo "<div id='finalmsg'></div>";
} else {
	echo "<hr /><p id='finalmsg'>";
	if ($cptadd > 0) {
		if ($moderestore) {
			$action = "restaurés";
			$txtlog = "Restauration ";
		} else {
			$action = "ajoutés";
			$txtlog = "Ajout NIMEGUE";
		}

		echo 'Actes ' . $action . '  : ' . $cptadd;
		writelog($txtlog . ' ' . $ntype, $commune, $cptadd);
	}
	if ($cptmaj > 0) {
		echo '<br />Actes modifiés : ' . $cptmaj;
		writelog('Mise à jour NIMEGUE ' . $ntype, $commune, $cptmaj);
	}
	if ($cptign > 0)  echo '<br />Actes incomplets  : ' . $cptign;
	if ($cptver > 0)  echo '<br />Actes à vérifier  : ' . $cptver;
	if ($cptdeja > 0) echo '<br />Actes redondants  : ' . $cptdeja;
	echo '</p>';
	echo '<p>Durée du traitement  : ' . (time() - $T0) . ' sec.</p>';
	echo '<p>Durée du traitement  : ' . (microtime_float() - $MT0) . ' microsec.</p>';
}
echo '</div>';
close_page(1, $root);
