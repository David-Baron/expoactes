<?php
// Utilitaires généraux aux programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html

//-------------------------------------------------------------------

function pathroot(&$root, &$path, &$arg1, &$arg2, &$arg3)
{
	// Recupère les arguments passés en mode chemin ou args suivant config
	$defarg1 = $arg1;
	$defarg2 = $arg2;
	global $scriptname; // pour pouvoir le récupérer
	//$chemin = preg_split("/\/|\?/i", $_SERVER["REQUEST_URI"], -1, PREG_SPLIT_NO_EMPTY);
	//print '<p>'.$_SERVER["REQUEST_URI"].'</p>';
	$chemin = preg_split("/\//i", $_SERVER["REQUEST_URI"], -1, PREG_SPLIT_NO_EMPTY);
	// print '<pre>';  print_r($chemin); echo '</pre>';
	$i = 0;
	while (isset($chemin[$i]) and strpos($chemin[$i], ".php") === false and $i < count($chemin)) {
		if ($chemin[$i] != "admin" and $chemin[$i] != "install" and $chemin[$i] != "perso") {
			$root = $root . "/" . $chemin[$i];
		}
		$path = $path . "/" . $chemin[$i];
		$i++;
	}
	$arg1 = "";
	$arg2 = "";
	$arg3 = "";
	$pos = 0;
	if (isset($chemin[$i])) {
		$pos = strpos($chemin[$i], "?args=");
		$scriptname = mb_substr($chemin[$i], 0, strpos($chemin[$i], ".php"));  // nom du script sans le .php
	}
	if ($pos == 0) {
		$i++;
		if (count($chemin) > $i)
			$arg1 = decodemyslash(urldecode(nogetargs($chemin[$i])));
		$i++;
		if (count($chemin) > $i)
			$arg2 = decodemyslash(urldecode(nogetargs($chemin[$i])));
		$i++;
		if (count($chemin) > $i)
			$arg3 = urldecode(nogetargs($chemin[$i]));
	} else {
		$args = mb_substr($chemin[$i], $pos + 6);
		$pos = strpos($args, "&");
		if ($pos > 0) $args = mb_substr($args, 0, $pos);
		$argn = preg_split("/,/i", $args, -1, PREG_SPLIT_NO_EMPTY);
		$j = 0;
		if (count($argn) > $j)
			$arg1 = urldecode(nogetargs($argn[$j]));
		$j++;
		if (count($argn) > $j)
			$arg2 = urldecode(nogetargs($argn[$j]));
		$j++;
		if (count($argn) > $j)
			$arg3 = urldecode(nogetargs($argn[$j]));
	}
	// recup des valeurs par défaut
	if ($arg1 == "") $arg1 = $defarg1;
	if ($arg2 == "") $arg2 = $defarg2;

	/*
 	  echo '<p>ROOT ='.$root;
	  echo "<p>PATH =".$path;
	  echo "<p>ARG1 =".$arg1;
	  echo "<p>ARG2 =".$arg2;
	  echo "<p>ARG3 =".$arg3;
  */
}

//-------------------------------------------------------------------

function encodemyslash($text)
{
	// permet de passer des nom avec slash dans l'url (Alle s/Semois)
	$newslash = chr(190);  // 3/4
	return str_replace('/', $newslash, $text);
}

//-------------------------------------------------------------------

function decodemyslash($text)
{
	$newslash = chr(190);  // 3/4
	return str_replace($newslash, '/', $text);
}

//-------------------------------------------------------------------
// Compose une URL avec les arguments passés en mode chemin ou non suivant config.
function mkurl($script, $arg1, $arg2 = "", $args = "")
{
	$url = $script; // par défaut
	if (FULL_URL == 1) {
		if ($arg1 <> "") $url = $script . '/' . urlencode(encodemyslash($arg1));
		if ($arg2 <> "") $url .= '/' . urlencode(encodemyslash($arg2));
		if ($args <> "") $url .= "?" . $args;
	} else {
		if ($arg1 <> "") $url = $script . '?args=' . urlencode($arg1);
		if ($arg2 <> "") $url .= ',' . urlencode($arg2);
		if ($args <> "") $url .= "&amp;" . $args;
	}
	return $url;
}

//-------------------------------------------------------------------

function mkSiteUrl() // Compose le nom du serveur http:// ou https:// etc....  On récupère l'URL (sans le / de fin)
{
	// Utilisé dans :
	// activer_compte.php, cree_compte.php, localite.php, renvoilogin.php, signal_erreur.php, rss.php
	// admin/approuver_compte.php, admin/envoimail.php, admin/gestgeoloc.php, admin/gestuser.php, admin/loaduser.php
	// tools/carto_index.php, ?? tools/loginutils.php, ?? tools/traceIP/trace_ip.php
	// equivalent à   "http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	//          ET   "http://".$_SERVER['HTTP_HOST']
	// règle le pb http ou https et SERVER_PORT particulier ou par défaut
	$is_SSL = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	$HttpOuHttps = strtolower($_SERVER['SERVER_PROTOCOL']);
	$HttpOuHttps = substr($HttpOuHttps, 0, strpos($HttpOuHttps, '/')) . (($is_SSL) ? 's' : '');
	$ServerPort = ((!$is_SSL && $_SERVER['SERVER_PORT'] == '80') || ($is_SSL && $_SERVER['SERVER_PORT'] == '443'))
		? '' : ':' . $_SERVER['SERVER_PORT']; // Ne met le port que si c'est autre chose que 80(http) ou 443(https)
	$Hote = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . $ServerPort;
	$url_du_site = $HttpOuHttps . '://' . $Hote;
	// On peut forcer ici $url_du_site = "https://monsite.xxx" en cas de problème
	return $url_du_site;
}

//-------------------------------------------------------------------

function nogetargs($chaine)
{
	$x = strpos($chaine, "?");
	if ($x > 0) {
		$result = mb_substr($chaine, 0, $x);
	} else {
		$result = $chaine;
	}
	return $result;
}

//-------------------------------------------------------------------

function selected_option($valeur, $defaut)  // pour listbox
{
	$valeur = strval($valeur);
	$defaut = strval($defaut);
	if ($valeur == $defaut) {
		return 'value="' . $valeur . '" selected="selected"';
	} else {
		return 'value="' . $valeur . '"';
	}
}

//-------------------------------------------------------------------

function checked($valeur, $defaut = 1)  // retourne le mot checked si $valeur=1 pour CkeckBox ou radiobutton
{
	if ($valeur == $defaut)
		return ' checked="checked"';
	else
		return '';
}

//-------------------------------------------------------------------

function ischecked($name)  // retourne 1 ou 0 suivant que le parmetres est checké ou pas
{
	if (!isset($_REQUEST[$name]))
		return 0;
	else
		return $_REQUEST[$name];
}
//-------------------------------------------------------------------

function strmin($str1, $str2)
{
	// Retourne la chaine la plus en avant par ordre alphabétique
	if ($str1 > $str2) {
		return $str2;
	} else {
		return $str1;
	}
}

//-------------------------------------------------------------------

function strmax($str1, $str2)
{
	// Retourne la chaine la plus en arriere par ordre alphabétique
	if ($str1 < $str2) {
		return $str2;
	} else {
		return $str1;
	}
}

//-------------------------------------------------------------------

function icone($action)
{
	global $root;
	switch ($action) {
		case "P":
			$alt = "Permuter";
			$ima = "permuter.gif";
			break;
		case "S":
			$alt = "Supprimer";
			$ima = "supprimer.gif";
			break;
		case "M":
			$alt = "Modifier";
			$ima = "modifier.gif";
			break;
	}
	return '<img width="11" hspace="7" height="13" border="0" title="' . $alt . '" alt="' . $alt . '" src="' . $root . '/img/' . $ima . '" />';
}

//-------------------------------------------------------------------

function execute_script_sql($filename, $prefixe = "", $selecttxt = "")
{
	if ($prefixe == "")
		$prefixe = EA_DB;

	if (!file_exists($filename)) {
		msg('041 : Impossible de trouver le script SQL "' . $filename . '".');
		$ok = false;
		die();
	}
	$listreq = explode(';', file_get_contents($filename));

	//print '<pre>';  print_r($listreq); echo '</pre>';

	$ok = true;
	$i  = 0;
	//echo count($listreq);

	while ($ok and $i < count($listreq)) {
		$reqmaj = $listreq[$i];
		if ($selecttxt == "" or isin($reqmaj, $selecttxt) >= 0) // si instruction selectionnée ou toutes
		{
			$reqmaj = str_replace("EA_DB_", $prefixe . "_", $reqmaj);

			if (strlen(trim($reqmaj)) > 0) {
				if ($result = EA_sql_query($reqmaj . ';')) {
					echo '<p>Action ' . ($i + 1) . ' ok</p>';
				} else {
					echo ' -> Erreur : ';
					echo '<p>' . EA_sql_error() . '<br>' . $reqmaj . '</p>';
					$ok = false;
				}
			}
		}
		$i++;
	}
	return $ok;
}

//-----------------------------------------------------------------

function check_new_version($key, $urlsite, $type_site = '')
{
	$MODE_check = '';
	$MODE_check = 'JSON';

	// Par défaut :
	$lavaleur = EA_VERSION . '|l';
	if (!isset($_COOKIE[$key])) {
		$h = $_SERVER['HTTP_HOST'];
		$r = $_SERVER['REQUEST_URI'];

		if ($MODE_check === 'JSON') {
			if (!isset($_REQUEST['EA_VERSION_LAST'])) {
				$X = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
<script language="javascript">
    $.extend( {
        redirectPost: function (location, args) {
            var form = $("<form>", { action: location, method: "post" });
            $.each(args,
                function (key, val) {
                    $(form).append(
                        $("<input>", { type: "hidden", name: key, value: val })
                    );
                });
            $(form).appendTo("body").submit();
        }
    });
    window.onload = function(e){
        var toLoad = "' . SITE_INVENTAIRE . 'versions.php?type=JSON&req=' . $h . $r . '&inv=' . $type_site . '";
        var retourne = "";
        $.ajax({ url: toLoad,
            dataType: "json",
            timeout: 3000, // sets timeout to 3 seconds (ok 30 génère  erreur)
            success: function(REPONSE) {
				retourner(JSON.stringify(REPONSE));
			},
            error: function (jqXHR, textStatus, errorThrown) {
				retourner("erreur");
			},
        });
        function retourner(retourne) {
    		var myArray = {"EA_VERSION_LAST": retourne };
    	    $.redirectPost("", myArray);
        }
    }
</script>
';
				echo $X;
				return $lavaleur;
			} else {
				$obj = json_decode($_REQUEST['EA_VERSION_LAST'], true);
				if (!isset($obj['EXPOACTES'])) {
					$newvers = EA_VERSION;
					$status_inv = 'l';
					$lavaleur = EA_VERSION . '|l';
				} else {
					$lavaleur = $obj['EXPOACTES'];
					$t = explode('|', $lavaleur . '|l');
					$newvers = $t[0];
					$status_inv = $t[1];
				}
			}
		} else {
			$lines = @file($urlsite . 'versions.php?req=' . $h . $r . '&inv=' . $type_site);
			if ($lines) {
				$lavaleur = "";
				foreach ($lines as $line) {
					$laligne = explode(":", $line);
					if ($laligne[0] == $key) {
						$lavaleur = $laligne[1];
					}
				}
			}
		}
		setcookie($key, $lavaleur);  // session uniquement
	}
	return $lavaleur;
}

//-----------------------------------------------------------------
// retourne VRAI si currentversion est superieur ou égal à requiredversion
function check_version($currentversion, $requiredversion)
{
	list($majorC, $minorC, $editC) = explode(".", $currentversion);
	list($majorR, $minorR, $editR) = explode(".", $requiredversion);

	$majorC = intval($majorC);
	$majorR = intval($majorR);
	$minorC = intval($minorC);
	$minorR = intval($minorR);
	$editC  = intval($editC);
	$editR  = intval($editR);

	if ($majorC > $majorR) return TRUE;
	if ($majorC < $majorR) return FALSE;

	if ($minorC > $minorR) return TRUE;
	if ($minorC < $minorR) return FALSE;

	if ($editC  >= $editR) return TRUE;
	if ($editC  >= $editR) return TRUE;

	return FALSE;
}

//-------------------------------------------------------------------

function edit_text($name, $size, $value, $caption)
{
	echo ' <tr class="row1">' . "\n";
	echo "  <td align=right>" . $caption . " : </td>\n";
	echo '  <td>';
	if ($size <= 70) {
		$value = str_replace('"', '&quot;', $value); //+ $value = htmlentities($value, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);

		echo '<input type="text" name="' . $name . '" size=' . $size . '" maxlength=' . $size . ' value="' . $value . '">';
	} else {
		echo '<textarea name="' . $name . '" cols=70 rows=' . (min(4, $size / 70)) . '>' . $value . '</textarea>';
	}
	echo '  </td>';
	echo " </tr>\n";
}

