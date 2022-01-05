<?php
// VERSION 2015
// Utilitaires génériques
// Copyright (C) : André Delacharlerie, 2005-2015
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html

//-------------------------------------------------------------------

//error_reporting(1);  // redefini ensuite dans actutils...

if (file_exists(dirname(__FILE__) . '/EA_sql.inc.php')) include_once(dirname(__FILE__) . '/EA_sql.inc.php');
else {
	// Spécial pour EA 322 détourner les appels mysql  (BG: TODO cette branche else est-elle vraiment nécessaire ?)
	if (!function_exists('mysql_query') and ((!extension_loaded('mysql') or (substr(phpversion(), 0, 1) >= '7')))) {
		include_once(dirname(__FILE__) . '/mysql2i.class.php');
	}
}

//set_magic_quotes_runtime(0);  // annulation des magic quotes sur les téléchargements (obsolète à partir de 5.3)
dequote_magic_quotes();      // suppression des magic quotes sur les paramètres REQUEST et COOKIE

define('INTERNAL_CHARSET', 'UTF-8');
define('MAIL_CHARSET', INTERNAL_CHARSET); // Charset pour les mails 

define('ENTITY_CHARSET', INTERNAL_CHARSET); // POUR htmlspecialchars, htmlentities, html_entities_decode, htmlspecialchars_decode (il n'y en a pas pour version 3.2)
mb_internal_encoding(INTERNAL_CHARSET);
//define('ENTITY_REPLACE_FLAGS', ENT_COMPAT | ENT_XHTML); // idem ci dessus
define('ENTITY_REPLACE_FLAGS', ENT_COMPAT | 16); // idem ci dessus (ENT_XHTML n'est pas défini avant  php 5.4)
define('EL', '');  // definie mais vide 

//-------------------------------------------------------------------
/**
 * Converti une chaine Windows en UTF8 après avoir recodé le caractère 134 : †
 * @param string $text
 * @return string
 */
function ea_utf8_encode($text)
{
	//$text = str_replace(chr(134), "+=+", $text);
	//$text = utf8_encode($text);
	//$text = str_replace("+=+",chr(226).chr(128).chr(160), $text);
	$text = iconv("Windows-1252", "UTF-8", $text);
	return $text;
}

//-------------------------------------------------------------------
/**
 * Converti une chaine UTF8 en Windows avec recodage du caractère 134 : †
 * @param string $text
 * @return string
 */
function ea_utf8_decode($text)
{
	//$text = str_replace(chr(226).chr(128).chr(160), "+=+", $text);
	//$text = utf8_decode($text);
	//$text = str_replace("+=+",chr(134), $text);
	$text = iconv("UTF-8", "Windows-1252", $text);
	return $text;
}

//-------------------------------------------------------------------

function optimize($request)  // pour détection des optimisations à faire
{
	if (defined("OPTIMIZE") or getparam('OPTIMIZE') == "YES") {
		if (isin(strtoupper($request), 'SELECT') >= 0) {
			$optim = EA_sql_query("EXPLAIN " . $request);
			echo '<p>' . preg_replace('/union/', '<br /><b>UNION</b><br />', $request) . '</p>';
			if (strtoupper(mb_substr($request, 0, 1)) == 'S') {
				$nbres = EA_sql_num_rows($optim);
				if ($nbres > 0) {
					print '<pre> OPTIMISATION : <p> ';
					while ($line = EA_sql_fetch_assoc($optim)) {
						print_r($line);
					}
					echo '</pre>';
				}
			}
		} else {
			print '<p>REQUETE MAJ : ' . $request . '<p> ';
		}
	}
}

//-----------------------------------------
// conversion des valeurs de paramètres PHP de type 2M ou 256K
function return_bytes($val)
{  
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	$val = substr($val, 0, -1);     // BG: 28/10/2021: enlever le dernier caractère pour n'avoir qu'une valeur numérique
	switch ($last) {
			// Le modifieur 'G' est disponible depuis PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}

//---------------------------------------------------------

function isin($grand, $petit, $debut = 0) // retourne la position de $petit dans $grand ou -1 si non présent
{
	//echo "recherche de ".$petit." dans ".$grand.".";
	if ($petit == "")
		return -1;
	else {
		$pos = mb_strpos($grand, $petit, $debut);
		if ($pos === false) {
			return -1;
		} else {
			return $pos;
		}
	}
}

//---------------------------------------------------------
// André DELACHARLERIE
/*
function getparam($name) // initialise proprement une variable avec le contenu d'un paramètre facultatif
  {
  if (isset($_REQUEST[$name])) 
  	return $_REQUEST[$name];
   else 
    return "";
  }
*/
function getparam($name, $default = "", $allow_sql = 0) // initialise proprement une variable avec le contenu d'un paramètre facultatif et filtrage anti injection de code
{
	if (isset($_REQUEST[$name])) {
		$param = $_REQUEST[$name];
		if (!is_utf8($param)) $param = iconv('iso-8859-15', 'UTF-8', $param); // Conversion des paramètres en UTF8
		$paramMaj = strtoupper($param);
		$interdits = array("SELECT", "FROM", "INSERT", "DELETE", "UPDATE", "UNION", "SHOW", "PASSWORD", "SLEEP");
		if (!$allow_sql) {
			$ok = true;
			foreach ($interdits as $interdit) {
				if (preg_match("`([[:space:]]|\)|'|\`)" . $interdit .
					"([[:space:]]|\(|'|\`)`i", $paramMaj))
				// précédé et suivi de blanc,parenthèse,ou apostrophes
				{
					//if (isin($paramMaj,";")>0)  // rejet si mot interdit et ;
					$ok = false;
				}
			}
			if (!$ok) {
				msg(_gt_("Expression rejetee : ") . $param);
				writelog("SQL-INJ : " . EA_sql_real_escape_string($param));
				$param = "";
			}
		}
		return $param;
	} else
		return $default;
}
// André DELACHARLERIE  
//---------------------------------------------------------
// (et remplace la fonction glob qui n'est pas supportée sur tous les hébergements)
function mydir($dir, $ext) // retourne la liste des fichiers d'un répertoire 
{
	$files = array();
	$extup = strtoupper($ext); // mettre le .
	$lext = strlen($extup);
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (strtoupper(mb_substr($file, - ($lext))) == $extup)
					$files[] = $dir . $file;
			}
		}
	}
	sort($files);
	return $files;
}

//---------------------------------------------------------

function iif($bool, $vrai, $faux) // is bool alors vrai sinon faux
{
	if ($bool) return $vrai;
	else return $faux;
}

//---------------------------------------------------------

function echoln($texte) // echo + passage à la ligne
{
	echo $texte . "\n";
}

//---------------------------------------------------------

function div($nbre, $diviseur) // division entière
{
	return floor($nbre / $diviseur);
}

//---------------------------------------------------------

function entier($nbre) // pour mettre au format entier avec séparateur
{
	if ($nbre == '')
		return '';
	else
		return number_format(intval($nbre), 0, ',', '.');
}

//---------------------------------------------------------

function ads_explode($chaine, $separ, $nbre) // explose une chaines dans un tableau et complete si besoin les cases manquantes
{
	$tableau = explode($chaine, $separ);
	if (count($tableau) < $nbre) {
		for ($i = count($tableau); $i < $nbre; $i++) {
			$tableau[$i] = "";
		}
	}
	return $tableau;
}

//---------------------------------------------------------

function zeros($nbre, $chiffres) // pour précéder un entier de 0  (ex 006)
{
	return mb_substr('0000000000' . $nbre, -$chiffres, $chiffres);
}

//---------------------------------------------------------

function sans_quote($texte)  // retourne vrai si aucune quote dans le texte
{
	if (strpos($texte, "'") === false) return true;
	else return false;
}

//---------------------------------------------------------
function multisin($grand, $listpetits)
{ // retourne la position de la première petite chaine trouvée dans la grande
	$resu = -1;
	foreach ($listpetits as $petit)
		if (isin($grand, $petit) >= 0) {
			$resu = isin($grand, $petit);
			break;
		}
	return $resu;
}
//---------------------------------------------------------
// mode : 0 = ne rien faire, >0 activer les lien : 1 = séparés par des , ou des blancs ou  2 = séparés par des ;
function linkifie($texte, $mode)  // transforme en lien actif les noms de fichier image et les URL rencontrées
{
	global $userlevel, $userlogin;
	if ($mode == "2")
		$separs = ";";
	else
		$separs = " , "; // (séparés par des , ou des blancs)

	$listExtImages = array(".JPG", ".TIF", ".PNG", ".PDF", ".GIF");
	$cpt = 0;  // N° de l'image

	// $URL_JPG = "http://site1/;000>http://site2/;RFT_>http://site3/"; exemple de format
	$uptexte = mb_strtoupper($texte);
	if (multisin($uptexte, $listExtImages) < 0 and isin($uptexte, 'HTTP://') < 0 and isin($uptexte, 'HTTPS://') < 0) {
		// pas d'image ni d'url donc aucune manipulation
		return $texte;
	} else {
		// préparation du tableau des substitutions
		$prefix = array();
		$siturl = array();
		$modurl = array();

		$lbase = explode(";", URL_JPG);
		if (count($lbase) == 0)
			$base_url = "";
		else
			$base_url = $lbase[0];
		foreach ($lbase as $code) {
			$pl = isin($code, '>>');
			if ($pl > 0) {
				$prefix[] = trim(mb_strtoupper(mb_substr($code, 0, $pl)));
				$siturl[] = trim(mb_substr($code, $pl + 2));
				$modurl[] = "R";  // Remplacement du préfixe
			} else {
				$pl = isin($code, '>');
				if ($pl > 0) {
					$prefix[] = trim(mb_strtoupper(mb_substr($code, 0, $pl)));
					$siturl[] = trim(mb_substr($code, $pl + 1));
					$modurl[] = "A";  // Ajout d'un préfixe
				}
			}
		}
		// echo '<pre>'.$uptexte.'<br>'; print_r($prefix); print_r($siturl); print_r($modurl); echo '</pre>';

		// découpe du texte en fragments
		$list = array();
		$fragment = "";
		for ($k = 0; $k < mb_strlen($texte); $k++) {
			$char = mb_substr($texte, $k, 1);
			//echo '.'.$char;
			if (isin($separs, $char) >= 0) {
				$list[] = $fragment;
				$fragment = "";
			} else
				$fragment .= $char;
		}
		$list[] = $fragment;

		//echo '<pre>FRAG:';print_r($list);echo '</pre>';
		// analyse et reconstruction de la chaine
		$result = '';
		$ecarte_element = '';
		foreach ($list as $image) {
			if (isin(mb_strtoupper($image), 'IFRAME') == -1) {
				// Traitement des URL directes HTTP://
				$l = isin(mb_strtoupper($image), 'HTTP://');
				if ($l == -1)
					$l = isin(mb_strtoupper($image), 'HTTPS://');
				if ($l >= 0) {
					$debut = mb_substr($image, 0, $l);
					$url = mb_substr($image, $l);
					//echo "<p>L=".$l."LEN=".strlen($image)." URL=".$url;
					$cpt++;
					if (($userlogin == "") and (JPG_SI_LOGIN == 1))
						$lien = 'Document' . $cpt . ' privé';
					else
						$lien = '<a href="' . $url . '" target="_blank">Document' . $cpt . '</a> ';
					$result .= $ecarte_element . $debut . $lien;
				} else { //traitement des vraies images
					$uptexte = trim(mb_strtoupper($image));
					$l = multisin($uptexte, $listExtImages);
					if ($l >= 0) {
						$lesite = "";
						for ($ii = 0; $ii < count($prefix); $ii++) {
							if (mb_substr($uptexte, 0, mb_strlen($prefix[$ii])) == $prefix[$ii]) {
								$lesite = $siturl[$ii];
								if ($modurl[$ii] == "R")
									$image = mb_substr(trim($image), mb_strlen($prefix[$ii]));
								break;
							}
						}
						if ($lesite == "") $lesite = $base_url;  // valeur par défaut
						$urlimage = strtr($lesite . $image, "\\", "/");  // remplace les \ par des / (simples !)
						$ipublic = true;
						if (isin(mb_strtoupper($urlimage), 'PRIVE') > -1) {
							$ipublic = false;
							if ($userlevel < LEVEL_JPG_PRIVE)
								$result .= " ";	// On ne montre pas si prive et pas le niveau suffisant
							else
								$ipublic = true;
						} else
							if (($userlogin == "") and (JPG_SI_LOGIN == 1)) {
							$cpt++;
							$result .= $ecarte_element . 'Image' . $cpt . ' privée';	// On ne montre pas car login obligatoire
							$ipublic = false;
						}

						if ($ipublic) {
							$cpt++;
							$result .= $ecarte_element . '<a href="' . $urlimage . '" target="_blank">Image' . $cpt . '</a> ';
						}
					} else
						$result .= $ecarte_element . $image;
				}
			}  // iframe ?
			else
				$result .= $ecarte_element . $image;
			$ecarte_element = '<br>';
		} // foreach
		return $result;
	}
}

//------------------------------------------------------------

/*
function linkjpg($texte) // adapté pour la syntaxe de http://marmottesdesavoie.org/

	{
	$result = '';
	$cpt = 1;

	if ($texte !="") and ($userlevel >= LEVEL_JPG_PRIVE)
		{
		$longref = strlen($texte);
		$suffixe = mb_substr(strrchr($texte,"_"),1);
		$longsuffixe = strlen($suffixe);
		$longprefixe = $longref-$longsuffixe;
		$prefixe = mb_substr($texte,0,$longprefixe);


		//CAS 00A
		If ($longsuffixe==3)
			{
			$image=URL_JPG.$texte.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}


		//CAS 00A-00B
		If ($longsuffixe ==7)
			{
			$prem=mb_substr($suffixe,0,3);
			$dern=mb_substr($suffixe,4,3);
			$nb=$dern-$prem;

			for ($k = 0; $k <= $nb; $k++)
				{ 
				$index2=$prem+$k;
				$index1 = "000".$index2;
				$index = mb_substr($index1,-3);
				$image=URL_JPG.$prefixe.$index.".jpg";	
				$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
				}
			}


		//CAS 00Aet00B
		If ($longsuffixe ==8)
			{
			$prem=mb_substr($suffixe,0,3);
			$dern=mb_substr($suffixe,5,3);
			$image=URL_JPG.$prefixe.$prem.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			$image=URL_JPG.$prefixe.$dern.".jpg";	
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}

		//CAS  00Aet00B-00C et 00A-00Bet00C

IF ($longsuffixe ==12)
	{
	$test=mb_substr($suffixe,3,1);
	if ($test =="e")
		// Cas 00Aet00B-00C
		{
		$prem=mb_substr($suffixe,5,3);
		$dern=mb_substr($suffixe,-3);
		$extra=mb_substr($suffixe,0,3);
		$nb=$dern-$prem;
		$image=URL_JPG.$prefixe.$extra.".jpg";          
		$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
		for ($k = 0; $k <= $nb; $k++)
			{
			$index2=$prem+$k;
			$index1 = "000".$index2;
			$index = mb_substr($index1,-3);
			$image=URL_JPG.$prefixe.$index.".jpg";          
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}
		}
	else
	// Cas 00A-00Bet00C
		{
		$prem=mb_substr($suffixe,0,3);
		$dern=mb_substr($suffixe,4,3);
		$extra=mb_substr($suffixe,-3);
		$nb=$dern-$prem;
		for ($k = 0; $k <= $nb; $k++)
			{
			$index2=$prem+$k;
			$index1 = "000".$index2;
			$index = mb_substr($index1,-3);
			$image=URL_JPG.$prefixe.$index.".jpg";          
			$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
			}
		$image=URL_JPG.$prefixe.$extra.".jpg";          
		$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
		}
	}
// Cas 00A-00Bet00C-00D
IF ($longsuffixe >12)
	{
	$prem=mb_substr($suffixe,0,3);
	$dern=mb_substr($suffixe,4,3);
	$prem2=mb_substr($suffixe,9,3);
	$dern2=mb_substr($suffixe,13,3);
	$nb=$dern-$prem;
	$nb2=$dern2-$prem2;
	for ($k = 0; $k <= $nb; $k++)
		{
		$index2=$prem+$k;
		$index1 = "000".$index2;
		$index = mb_substr($index1,-3);
		$image=URL_JPG.$prefixe.$index.".jpg";          
		$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
		}
	for ($k = 0; $k <= $nb2; $k++)
		{
		$index2=$prem2+$k;
		$index1 = "000".$index2;
		$index = mb_substr($index1,-3);
		$image=URL_JPG.$prefixe.$index.".jpg";          
		$result .= ' <a href="'.$image.'" target="_blank">Image'.$cpt++.'</a> ';
		}                                                            
	}

	return $result;  	
	}
//*/

//---------------------------------------------------------

function dequote_magic_quotes() // pour retirer les magic quotes s il y en a !! sur REQUEST et $_COOKIE
{   // deprecated from PHP 7.4 and useless since 5.4)
	if (version_compare(phpversion(), '5.4.0', '<')) {
		if (get_magic_quotes_gpc()) {
			if (is_array($_REQUEST)) {
				foreach ($_REQUEST as $k => $v) {
					if (is_string($v)) $_REQUEST[$k]  = stripslashes($v);
				}
			}
			if (is_array($_COOKIE)) {
				foreach ($_COOKIE as $k => $v) {
					if (is_string($v)) $_COOKIE[$k]  = stripslashes($v);
				}
			}
		}
	}
}

//---------------------------------------------------------

function sql_and($cond) // ajoute and si condition non vide
{
	if ($cond != "") return $cond . " and ";
	else return "";
}

//---------------------------------------------------------

// André DELACHARLERIE
/*
function sql_quote($texte) // pour passer texte à MySQL en escapant les ' " \ ...
  {
	if (true) // (!get_magic_quotes_gpc()) // get_magic_quotes_gpc ne porte que sur les éléments recus via GET/POST
		{
		$result = addslashes(trim($texte));
//		$result = addslashes($texte);
		}
	 else
		{
		$result = trim($texte);
		}
  return $result;
  }
*/
function sql_quote($texte) // pour passer texte a MySQL en escapant les ' " \ ...
{
	$result = EA_sql_real_escape_string(trim($texte));
	return $result;
}
// André DELACHARLERIE

//---------------------------------------------------------

function sql_virgule($cond, $add) // ajoute , si liste non vide
{
	if ($cond != "") return $cond . ", " . $add;
	else return $add;
}

//---------------------------------------------------------

function heureminsec($totalsecondes)
{
	$secondes = $totalsecondes % 60;
	$minutes = ($totalsecondes / 60) % 60;
	$heures = ($totalsecondes / (60 * 60) % 24);
	$jours = ($totalsecondes / (60 * 60 * 24));
	return sprintf("%02dj %02dh %02d' %02d\"", $jours, $heures, $minutes, $secondes);
}

//---------------------------------------------------------

function val_var_mysql($label)
{
	if ($result = EA_sql_query("SHOW VARIABLES like '" . $label . "'")) {
		$row = EA_sql_fetch_assoc($result);
		return $row['Value'];
	} else
		return "??";
}

//---------------------------------------------------------

function val_status_mysql($label)
{
	if ($result = EA_sql_query("SHOW STATUS like '" . $label . "'")) {
		$row = EA_sql_fetch_assoc($result);
		return $row['Value'];
	} else
		return "??";
}

//---------------------------------------------------------
// décompose selon ; ou tab en respectant les guillements éventuels
function explode_csv($line)
{
	$l = strlen($line);
	$j = 0;
	$res = array("");
	$guill = false;
	for ($i = 0; $i < $l; $i++) {
		if ($line[$i] == '"') {
			if ($guill) $guill = false;
			else $guill = true;
		} elseif (($line[$i] == ';' or $line[$i] == chr(9)) and !$guill) {
			$j++;
			$res[$j] = "";
		} else {
			$res[$j] .= $line[$i];
		}
	}
	return $res;
}

//------------------------------------------------------------

function is__writable($path, $show = true)  // Teste si acces en ecriture est possible : attention aux deux _ _
{
	if ($path[strlen($path) - 1] == '/')
		return is__writable($path . uniqid(mt_rand()) . '.tmp', $show);

	if ($show)
		echo '<p>Test de création du fichier ' . $path . '';
	if (file_exists($path)) {
		if (!($f = @fopen($path, 'r+')))
			return false;
		fclose($f);
		return true;
	}
	if (!($f = @fopen($path, 'w')))
		return false;
	fclose($f);
	unlink($path);
	return true;
}

//---------------------------------------------------------

function permuter(&$un, &$deux)
{
	$trois = $un;
	$un = $deux;
	$deux = $trois;
}

//---------------------------------------------------------

function remove_accent($txt)
{ // adaptée UTF-8
	$tofind = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'Ç', 'ç', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü', 'ÿ', 'Ñ', 'ñ');
	$replac = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'C', 'c', 'I', 'I', 'I', 'I', 'i', 'i', 'i', 'i', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'y', 'N', 'n');
	return (str_replace($tofind, $replac, $txt));
}

//---------------------------------------------------------

function con_une_db($ladbaddr, $ladbuser, $ladbpass, $ladbname, $show = false, $new_link = false) // fonction de connexion à une DB
{
	global $dbok;

	if (file_exists('tools/function.php')) {
		$EA_Appel_dOu = '';
		$EA_Script_Courant = basename($_SERVER['PHP_SELF']);
	} else {
		$EA_Appel_dOu = '../';
		$EA_Script_Courant = basename(dirname($_SERVER['PHP_SELF'])) . '/' . basename($_SERVER['PHP_SELF']); // Ne pas utiliser DIRECTORY_SEPARATOR
	}

	if (function_exists('EA_sql_connect')) {
		$dblink = @EA_sql_connect("$ladbaddr", "$ladbuser", "$ladbpass", $new_link);
	} else {
		$dblink = @mysqli_connect("$ladbaddr", "$ladbuser", "$ladbpass", "$ladbname");
	}
	if ($dblink) {
		if ($show)  echo '<p>Connexion au serveur MySQL :<b> OK</b></p>';
		$dbok = EA_sql_select_db("$ladbname", $dblink);
		if ($dbok) {
			EA_sql_query('SET NAMES utf8', $dblink);  // oblige MySQL à répondre en UTF-8  (ISO-8859-1 par défaut)
			if ($show) {
				echo '<p>Connexion &agrave; la base de donn&eacute;es : <b> OK</b></p>';
			}
		} else {
			if (in_array($EA_Script_Courant, array('admin/index.php', 'install/install.php', 'install/update.php'))) {
				echo '<a href="' . $EA_Appel_dOu . 'install/configuration.php">Configurer la base de donn&eacute;es</a>';
			} else {
				msg("012 : La base sp&eacute;cifi&eacute;e n'est pas accessible sur le serveur MySQL : " . EA_sql_error());
			}
			exit(0);
		}
		return $dblink;
	} else {
		if (in_array($EA_Script_Courant, array('admin/index.php', 'install/install.php', 'install/update.php', 'index.php'))) {
			echo '<a href="' . $EA_Appel_dOu . 'install/configuration.php">Configurer la base de donn&eacute;es</a>';
		} else {
			msg("011: Impossible d'ouvrir la connexion au serveur MySQL avec l'utilisateur pr&eacute;sent&eacute; : " . EA_sql_error());
		}
		exit(0);
	}
}

///---------------------------------------------------------

function con_db($show = false) // fonction de connexion des DB
{
	global $dbaddr, $dbuser, $dbpass, $dbname, $a_db, $dbok;
	global $udbaddr, $udbuser, $udbpass, $udbname, $u_db;

	if (isset($udbaddr, $udbuser, $udbpass, $udbname)) {
		if ($show) {
			echo '<p><b>Base des utilisateurs :</b></p>';
		}
		$u_db = con_une_db($udbaddr, $udbuser, $udbpass, $udbname, $show);
		if ($show) {
			echo '<p><b>Base des actes :</b></p>';
		}
		$a_db = con_une_db($dbaddr, $dbuser, $dbpass, $dbname, $show, true);
	} else {
		if ($show) {
			echo '<p><b>Base des actes et des utilisateurs :</b></p>';
		}
		$u_db = $a_db = con_une_db($dbaddr, $dbuser, $dbpass, $dbname, $show);
	}
	return $a_db;
}

//---------------------------------------------------------

function close_db($dblink) // ferme la connexion à la DB
{
	EA_sql_close($dblink);
}

//---------------------------------------------------------

function msg($desc, $type = "erreur")
{
	if ($desc <> null) {
		echo "<p class=\"$type\">";
		if ($type == "erreur") echo "Erreur : ";
		echo "$desc</p>\n";
		global $root;
		if (empty($root)) $root = ".";
		if (intval($desc) > 0)
			echo '<p>Consultez la liste des <a href="' . $root . '/admin/aide/codeserreurs.html">codes d\'erreurs</a>.</p>';
	}
}

//---------------------------------------------------------

function writelog($texte, $commune = "-", $nbactes = 0)
{
	$user = sql_quote(current_user("ID"));
	$time = now();
	if (empty($user))
		$texte = $_SERVER['REMOTE_ADDR'] . ":" . $texte;
	$sql = "insert into " . EA_DB . "_log values ($user,'$time','" . sql_quote($texte) . "','" . sql_quote($commune) . "',$nbactes)";
	EA_sql_query($sql);
}

//---------------------------------------------------------
/**
 * Fonction simple identique à celle en PHP 5
 */
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

//---------------------------------------------------------

function now()
{
	return date("Y-m-d H:i:s", time());
}

//---------------------------------------------------------

function today()
{
	return date("Y-m-d", time());
}

//---------------------------------------------------------

function showdate($sqldate, $mode = "T")
{
	//mode T : Texte 23 jan 2009  S : Slash 23/01/2009
	$moistxt = array("Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Déc");
	$jour = mb_substr($sqldate, 8, 2);
	$mois = mb_substr($sqldate, 5, 2);
	$annee = mb_substr($sqldate, 0, 4);
	if ($mode == "T")
		if ($annee == "0000")
			$ladate = "Date inconnue";
		else
			$ladate = $jour . " " . $moistxt[intval($mois) - 1] . " " . $annee;
	else
		$ladate = $jour . "/" . $mois . "/" . $annee;
	//echo $sqldate ."-->".$ladate;
	return $ladate;
}

//---------------------------------------------------------

function date_rss($ladate)
{
	// Passe de date MySQL --> RSS
	$dt = explode('-', $ladate);
	$dtunix = mktime(12, 0, 0, $dt[1], $dt[2], $dt[0]);
	$texte = gmdate("D, d M Y H:i:s", $dtunix) . " GMT";
	return $texte;
}

//---------------------------------------------------------

function date_sql($ladate)
{
	// Passe de date jj/mm/aaaa --> aaaa-mm-jj
	if (isin($ladate, "/") > 0) {
		$dt = explode('/', $ladate);
		$dtunix = mktime(12, 0, 0, $dt[1], $dt[0], $dt[2]);
		$texte = gmdate("Y-m-d", $dtunix);
		return $texte;
	} else
		return $ladate;
}

//---------------------------------------------------------

function microdelay($delay) //Just for the fun ! ;-)
{
	@fsockopen("tcp://localhost", 31238, $errno, $errstr, $delay);
}

//---------------------------------------------------------------

function MakeRandomPassword($length = 6)
{
	$_vowels = array('a', 'e', 'i', 'o', 'u', '2', '3', '4', '5', '6', '7', '8', '9');
	$_consonants = array('b', 'c', 'd', 'f', 'g', 'h', 'k', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z');
	$_syllables = array();
	$newpass = "";
	foreach ($_vowels as $v) {
		foreach ($_consonants as $c) {
			array_push($_syllables, "$c$v");
			array_push($_syllables, "$v$c");
		}
	}
	for ($i = 0; $i < ($length / 2); $i++)
		$newpass = $newpass . $_syllables[array_rand($_syllables)];
	return $newpass;
}

//------------------------------------------------------------------------

function valid_mail_adrs($email)
{
	if (preg_match('`^\w([-_.]?\w)*@\w([-_.]?\w)*\.([a-z]{2,4})$`', $email))
		return true;
	else
		return false;
}

//------------------------------------------------------------------------

function mail_encode($texte)
{
	// code les textes pour l'adresse mail ou le sujet de façon à passer même en 7bits 
	return "=?" . MAIL_CHARSET . "?B?" . base64_encode($texte) . "?=";
}

//------------------------------------------------------------------------

function sendmail($from, $to, $sujet, $message)
{
	/*
		echo '<p>Expéditeur : ['.htmlspecialchars($from, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).']'; 
		echo '<br />Destinataire : ['.htmlspecialchars($to, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).']'; 
		echo '<br />Sujet : ['.htmlspecialchars($sujet, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).']'.base64_encode($sujet); 
		echo '<br />Message : ['.htmlspecialchars($message, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).']'.'</p>'; 
		*/
	if (EXTERN_MAIL == 0) {
		// appel de la fonction interne ... pour autant qu'elle soit bien configurée
		$headers  = 'MIME-Version: 1.0' . "\n";
		$headers .= "Content-Type: text/plain; charset=" . MAIL_CHARSET . "; format=flowed\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n";
		$headers .= "X-Mailer: PHP" . phpversion() . "\n";
		$headers .= 'From: ' . $from . "\n";

		$ok =  @mail($to, mail_encode($sujet), $message, $headers);
		if (!$ok) {
			msg("051 : L'envoi du mail via la procédure interne à PHP n'a pas réussi.");
			global $userlogin;
			if ($userlogin <> "") {
				echo '<p>Expéditeur : ' . htmlspecialchars($from, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
				echo '<br />Destinataire : ' . htmlspecialchars($to, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '</p>';
				//mail($to, mail_encode($sujet), $message, $headers); // nouvel essai.
			}
		}
		return $ok;
	} else {
		// envoi du mail vers un autre serveur smtp ... si c'est nécessaire
		$lb = "\r\n";                //linebreak

		if (SMTP_HOST == "" or LOC_HOST == "" or LOC_MAIL == "") {
			msg("052 : Paramètres de gestion du mail incomlètement configurés.");
			return false;
		}
		// if ($smtp_port=="")
		$smtp_port = 25;  // valeur par défaut

		$contenu  = "from:" . $from . $lb;
		$contenu .= "to:" . $to . $lb;
		$contenu .= "subject:" . $sujet . $lb;
		$contenu .= $message;

		$content   = explode($lb, $contenu);

		// if($body) {$bdy = preg_replace("/^\./","..",explode($body_lb,$body));}

		// build the array for the SMTP dialog. Line content is array(command, success code, additonal error message)

		if (SMTP_PASS <> "") {
			// SMTP authentication methode AUTH LOGIN, use extended HELO "EHLO"
			$smtp = array(
				// call the server and tell the name of your local host
				array("EHLO " . LOC_HOST . $lb, "220,250", "HELO error: "),
				// request to auth
				array("AUTH LOGIN" . $lb, "334", "AUTH error:"),
				// username
				array(base64_encode(SMTP_ACC) . $lb, "334", "AUTHENTIFICATION error : "),
				// password
				array(base64_encode(SMTP_PASS) . $lb, "235", "AUTHENTIFICATION error : ")
			);
		} else
			$smtp = array(array("HELO " . LOC_HOST . $lb, "220,250", "HELO error: "));

		// call the server and tell the name of your local host

		// envelop
		$smtp[] = array("MAIL FROM: <" . $from . ">" . $lb, "250", "MAIL FROM error: ");

		$tos    = explode(",", $to); //header
		for ($i = 0; $i < count($tos); $i++)
			$smtp[] = array("RCPT TO: <" . $tos[$i] . ">" . $lb, "250", "RCPT TO error: ");
		// begin data
		$smtp[] = array("DATA" . $lb, "354", "DATA error: ");
		foreach ($content as $cont) {
			$smtp[] = array($cont . $lb, "", "");
		}
		$smtp[] = array("." . $lb, "250", "DATA(end)error: ");
		$smtp[] = array("QUIT" . $lb, "221", "QUIT error: ");

		// open socket
		$fp = @fsockopen(SMTP_HOST, $smtp_port, $errno, $errstr, 15);
		if (!$fp) {
			writelog("Cannot connect to host", SMTP_HOST, 0);
			msg('053 : Impossible de se connecter au serveur mail "' . SMTP_HOST . '".');
			return false;
		}

		$banner = fgets($fp, 1024);
		// perform the SMTP dialog with all lines of the list
		foreach ($smtp as $req) {
			$r = $req[0];
			// send request
			@fputs($fp, $req[0]);
			// get available server messages and stop on errors
			if ($req[1]) {
				while ($result = fgets($fp, 1024)) {
					if (mb_substr($result, 3, 1) == " ") break;
				}
				if (!strstr($req[1], mb_substr($result, 0, 3))) {
					writelog($req[2] . $result, SMTP_HOST, 0);
					msg('054 : Problème lors du dialogue avec le serveur mail "' . SMTP_HOST . '" : ' . $req[2] . $result);
					return false;
				}
			}
		}
		$result = fgets($fp, 1024);

		// close socket
		fclose($fp);
		return true;
	}
}

//------------------------------------------------------------------------
// source : www.phpcs.com :  Tadpole
function crypter($mes, $password)
{
	$res = ' ';
	$j = 0;
	$tmp = 0;
	$lgmot = strlen($mes);
	for ($i = 0; $i < $lgmot; $i++) {
		$tmp = ord($mes[$i]) + ord($password[$j]);
		if ($tmp > 255) {
			$tmp = $tmp - 256;
		}
		$res[$i] = chr($tmp);
		if ($j == (strlen($password) - 1)) {
			$j = 0;
		} else {
			$j = (($j % (strlen($password))) + 1);
		}
	}
	$res = base64_encode($res);
	return $res;
}

//------------------------------------------------------------------------
// source : www.phpcs.com :  Tadpole
function decrypter($mes, $password)
{
	$res = ' ';
	$j = 0;
	$tmp = 0;
	$mes = base64_decode($mes);
	$lgmot = strlen($mes);
	for ($i = 0; $i < $lgmot; $i++) {
		$tmp = ord($mes[$i]) - ord($password[$j]);
		if ($tmp < 0) {
			$tmp = 256 + $tmp;
		}
		$res[$i] = chr($tmp);
		if ($j == (strlen($password) - 1)) {
			$j = 0;
		} else {
			$j = (($j % (strlen($password))) + 1);
		}
	}
	return $res;
}

//------------------------------------------------------------------------

function my_flush()
{
	// n'envoie que si le tampon n'est pas vide (
	$obtail = ob_get_length();
	if ($obtail and $obtail > 0)
		ob_flush();
}

//------------------------------------------------------------------------

// Détection du codage UTF-8 d'une chaîne.
// source : http://www.cylman.com/php/detecter-si-une-chaine-est-encodee-en-utf-8_qr13.html
function is_utf8($str)
{
	$c = 0;
	$b = 0;
	$bits = 0;
	$len = strlen($str);
	for ($i = 0; $i < $len; $i++) {
		$c = ord($str[$i]);
		if ($c > 128) {
			if (($c >= 254)) return false;
			elseif ($c >= 252) $bits = 6;
			elseif ($c >= 248) $bits = 5;
			elseif ($c >= 240) $bits = 4;
			elseif ($c >= 224) $bits = 3;
			elseif ($c >= 192) $bits = 2;
			else return false;
			if (($i + $bits) > $len) return false;
			while ($bits > 1) {
				$i++;
				$b = ord($str[$i]);
				if ($b < 128 || $b > 191) return false;
				$bits--;
			}
		}
	}
	return true;
}
