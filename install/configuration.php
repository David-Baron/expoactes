<?php
// Copyright (C) : Emmanuel Lethrosne 2021
// 21/11/2021 : V1.1 pour ExpoActes
// Toute utilisation ou inspiration est soumise à l'autorisation de son auteur : demulau@gmail.com
//-------------------------------------------------------------------
// Fonctionnement inspiré de "MyOOS-dumper", une partie du code a été reprise à l'identique.

if (defined('EA_VERSION_PRG')) {
    define('EACONF_VERSION', EA_VERSION_PRG);
} else {
    define('EACONF_VERSION', '');
}

$IP_Serveur = str_replace(array(':', '.'), '-', $_SERVER['SERVER_ADDR']);

define('CONFIG_DIRNAME', realpath('../_config') . '/');
define('CONFIG_FILENAME', 'connect.inc.php');
// Attention : Modifier les 2 lignes en cohérence
define('CONFIG_FILENAME_NEW', 'BD-' . $IP_Serveur . '-connect.inc.php');
define('Old_CONFIG_FILENAME_TEXTE', "'BD-'. \$IP_Serveur . '-connect.inc.php'");
// extraire la valeur dans une ligne définssant une variable de la forme  $VAR = 'VALEUR';  OU  $VAR = "VALEUR"; // QU'IMPORTE
function extractValue($s)
{
    $r = trim(substr($s, strpos($s, "=") + 1)); // La partie après le 1er signe =
    $first_car = substr($r, 0, 1); // récupère le 1er caractère
    $r = substr($r, 1); // retire le 1er caractère
    // supprimer les espaces entre  'FIRST_CAR' et  ;
    $r = preg_replace('|' . $first_car . '( )+' . '|', $first_car, $r);
    $i = strpos($r, $first_car . ";"); //  recherche la chaine  FIRST_CAR suivi de ;
    return substr($r, 0, $i); // rend la partie correspondante
}

function EA_check_connect_BD($ladbaddr, $ladbuser, $ladbpass, $ladbname)
{
    if (function_exists('mysqli_connect')) {
        return @mysqli_connect($ladbaddr, $ladbuser, $ladbpass, $ladbname);
    } else {
        return @mysql_connect($ladbaddr, $ladbuser, $ladbpass);
    }
}


$lang = array();

$lang['L_DBPARAMETER'] = "Paramètres de la base de données";
$lang['L_CONFIG_STABLE'] = 'La base de données est correctement configurée, accès non autorisé.';

$lang['L_CONFIG_SAVE_ERROR'] = "Erreur d'enregistrement";
$lang['L_CONFIGNOTWRITABLE'] = "Impossible d'écrire le fichier de configuration.
Veuillez utiliser votre programme FTP et chmoder ce fichier en 0777<br>
(Sous Windows vérifier les autorisations ou créer le fichier).";
$lang['L_TRYAGAIN'] = "Réessayer";
$lang['L_INSTALL_TOMENU'] = "Retour au menu principal";

$lang['L_DB_HOST'] = "Serveur de la base de données";
$lang['L_DB_USER'] = "Utilisateur";
$lang['L_DB_PASS'] = "Mot de passe";
$lang['L_DB'] = "Base de données";
$lang['L_ENTER_DB_INFO'] = "Vous devez indiquer ici une base de donnée puis cliquer sur le bouton \"connecter à la base de donnée\" pour vérifier la connexion.";
$lang['L_TESTCONNECTION'] = "Essayer la connexion";
$lang['L_CONNECTTOMYSQL'] = " connecter à la base de donnée ";

$lang['L_DBCONNECTION'] = "Connexion base de données";
$lang['L_CONNECTIONERROR'] = "Erreur: aucune connexion n'a pu être créée.";
$lang['L_CONNECTION_OK'] = "Connexion avec la base de données a été établie.";
// $lang['L_NO_DB_FOUND_INFO']="La connexion avec la base de données a été établie avec succès.<br>
// Vos données utilisateur sont valides et ont été acceptées par le serveur de base de donnée.";
$lang['L_SAVEANDCONTINUE'] = "sauvegarder et continuer";
$lang['L_CONFBASIC'] = "Configuration de base";
$lang['L_INSTALL_STEP2FINISHED'] = "Configuration de la base de données a été sauvegardée.";
$lang['L_LASTSTEP'] = "Terminer la configuration";
$lang['L_INSTALLFINISHED'] = "<br>Configuration terminée  --> <a href=\"../index.php\">lancer ExpoActes</a><br>";

// $phase, $connstr = '||||||';

if (isset($_REQUEST['phase'])) $phase = $_REQUEST['phase'];
$phase = (isset($phase)) ? $phase : 1;
$params_config_EA = array('$dbaddr', '$dbname', '$dbuser', '$dbpass');
date_default_timezone_set('UTC');
$lignes_fichier_config = array();

if ($phase != 5) {
    $EA_config = array('hebergement' => array(), 'local' => array());
    if (file_exists(CONFIG_DIRNAME . CONFIG_FILENAME_NEW)) {
        $lignes_fichier_config = file(CONFIG_DIRNAME . CONFIG_FILENAME_NEW);
        include(CONFIG_DIRNAME . CONFIG_FILENAME_NEW);
    } else { // nouveau fichier de config
        $lignes_fichier_config_ancien = array();
        // charger les données de l'ancien fichier de config. S'il n'existe pas : anomalie d'installation donc erreur !
        require(CONFIG_DIRNAME . CONFIG_FILENAME); // renseigne  $dbaddr ....
        $lignes_fichier_config_ancien = file(CONFIG_DIRNAME . CONFIG_FILENAME);
        // Ajout eventuel des lignes dans l'ancien fichier de config
        $texte_temoin = '// *** Ajout depuis v3.2.3 ***';
        $mettre_balise_ouverture = true;
        foreach ($lignes_fichier_config_ancien as $v) {
            $contenu_ligne = str_replace(array("\n", "\r"), '', $v);
            if ($texte_temoin == $contenu_ligne) {
                $texte_temoin = ''; // texte existant donc on vide pour ignorer la suite
            }
            // Patch pour '? >' dernière balise dans le fichier PHP
            $contenu_ligne = trim($contenu_ligne);
            $i = mb_strrpos($contenu_ligne, '?>');
            if ($i !== false) {
                $contenu_ligne = mb_substr($contenu_ligne, $i + 1);
                $mettre_balise_ouverture = true;
            }
            if (strpos($contenu_ligne, '<?php') !== false) {
                $mettre_balise_ouverture = false;
            }
        }
        if ($texte_temoin !== '') {
            // Ajout balise ouverture PHP si la derniere balise trouvée était une fermeture  
            if ($mettre_balise_ouverture) $lignes_fichier_config_ancien[] = '<?php' . "\n";
            $lignes_fichier_config_ancien[] = $texte_temoin . "\n";
            $lignes_fichier_config_ancien[] = '// *** Le ' . date('d-m-Y') . ' *** ' . "\n";
            $lignes_fichier_config_ancien[] = "\$IP_Serveur = str_replace(array(':','.'), '-' , \$_SERVER['SERVER_ADDR']);" . "\n";
            $x = " dirname( __FILE__ ) . '/' . " . Old_CONFIG_FILENAME_TEXTE . ' ';
            $lignes_fichier_config_ancien[] = "if (file_exists(" . $x . ") ) { include_once(" . $x . "); return; }" . "\n";
            $ret = true;
            if ($fp = fopen(CONFIG_DIRNAME . CONFIG_FILENAME, "wb")) {
                if (!fwrite($fp, implode("", $lignes_fichier_config_ancien))) $ret = false;
                if (!fclose($fp)) $ret = false;
                if (!$ret) {
                    echo '<p class="warnung">' . $lang['L_CONFIG_SAVE_ERROR'] . '</p>';
                    exit;
                }
            }
        }
        unset($lignes_fichier_config_ancien);
        // Fin Ajout eventuel des lignes dans l'ancien fichier de config

        // Création du nouveau fichier de config
        $lignes_fichier_config_new = array(
            '<?php' . "\n",
            '//  *** Ajout du ' . date('d-m-Y') . " *** \n",
        );
        $lignes_fichier_config = $lignes_fichier_config_new;
        // Positionne les variables avec les données héritées de l'ancien fichier de config
        foreach ($params_config_EA as $k => $v) {
            if (!isset(${substr($v, 1)})) {
                ${substr($v, 1)} = "";
            }
            $x = ${substr($v, 1)};
            $lignes_fichier_config[] = $lignes_fichier_config_new[] = $v . " = '" . $x  . "';" . "\n";
        }

        foreach ($lignes_fichier_config_new as $v) {
            @file_put_contents(CONFIG_DIRNAME . CONFIG_FILENAME_NEW, $v, FILE_APPEND | LOCK_EX);
        }
        // $dbpass = ''; // Nouveau fichier de config : pour forcer à revalider le mot de passe pour traiter comme une 1ere configuration
    }

    // A partir d'ici le nouveau fichier de config doit exister et son contenu correct !
    // on initialise les variables qui manqueraient (lignes manquantes ou ajout depuis ancienne config !)
    foreach ($params_config_EA as $k => $v) {
        if (!isset(${substr($v, 1)})) {
            ${substr($v, 1)} = ''; // variable dynamique : $dbuser = 'xxx'; $a = 'dbuser'; echo ${$a}; affiche xxx;
            // on ajoute dans le fichier et dans le tableau
            $x = $v . " = '';" . "\n";
            @file_put_contents(CONFIG_DIRNAME . CONFIG_FILENAME_NEW, $x, FILE_APPEND | LOCK_EX);
            $lignes_fichier_config[] = $x;
        }
        // on mémorise les valeurs actuelles de la config prise en compte à l'include pour controle à l'enregistrement
        $config_old[$v] = ${substr($v, 1)};
        $EA_config['hebergement'][$v] = $config_old[$v];
    }

    $connstr = "$dbaddr|$dbuser|$dbpass|$dbname";

    // CONTROLER QUE L'ON NE PEUT Y ACCEDER SANS VERIFICATION : Tenter la connexion
    $connection = EA_check_connect_BD($dbaddr, $dbuser, $dbpass, $dbname);
    if ($connection !== false) {
        $Initialiser = false;
        echo $lang['L_CONFIG_STABLE'];
        exit;
    } else {
        $Initialiser = true;
    }
}

foreach ($_GET as $getvar => $getval) {
    ${$getvar} = trim($getval);
}
foreach ($_POST as $postvar => $postval) {
    ${$postvar} = trim($postval);
}

if (isset($_POST['dbaddr'])) {
    $dbaddr = trim($_POST['dbaddr']);
    $dbuser = trim($_POST['dbuser']);
    $dbpass = trim($_POST['dbpass']);
    $dbname = trim($_POST['dbname']);
    $config = array('dbaddr' => $dbaddr, 'dbuser' => $dbuser, 'dbpass' => $dbpass, 'dbname' => $dbname);
} else { // Recupère la connexion existante
    if (isset($connstr) && !empty($connstr)) {
        $p = explode("|", $connstr);
        $dbaddr = $config['dbaddr'] = $p[0];
        $dbuser = $config['dbuser'] = $p[1];
        $dbpass = $p[2]; // Quand la configuration n'est pas bonne on ne remets pas toutes les infos de la configuration : sécurité
        if (!isset($config['dbpass']))  $config['dbpass'] = '';
        $dbname = $config['dbname'] = $p[3];
    } else {
        $connstr = '';
    }
}

$connstr = "$dbaddr|$dbuser|$dbpass|$dbname";

header('content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="cache-control" content="must-revalidate">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ExpoActes - Configuration</title>

    </script>
    <style type="text/css" media="screen">
        td {
            border: 1px solid #ddd;
        }

        td table td {
            border: 0;
        }
    </style>
</head>

<body class="content">

    <?php
    /*
    <link rel="stylesheet" type="text/css" href="css/msd/style.css">
    <script src="js/script.js" type="text/javascript">
*/
    $action = '';
    $href = $action . "?phase=$phase&amp;connstr=$connstr";
    $href_suite = $action . "?phase=";

    switch ($phase) {
        case 0:
            break;
        case 1:
    ?>
            <div id="pagetitle">
                <p>
                    Configuration - Étape 1
                </p>
            </div>
            <div id="content" align="center">
                <p class="small">
                    <strong>Version <?php echo EACONF_VERSION; ?></strong>
                    <br>
                </p>
        <?php
            // Phase 1
            echo '<h6>' . $lang['L_DBPARAMETER'] . '</h6>';
            $ret = true;
            @chmod(CONFIG_DIRNAME . CONFIG_FILENAME_NEW, 0666);
            $ret = @fopen(CONFIG_DIRNAME . CONFIG_FILENAME_NEW, "a+") or die('Erreur impossible de creer le fichier de configuration ');

            if (!$ret) {
                touch(CONFIG_DIRNAME . CONFIG_FILENAME_NEW);
                echo '<p class="warning">' . $lang['L_CONFIGNOTWRITABLE'] . '</p>';
                echo '<a href="' . $href . '">' . $lang['L_TRYAGAIN'] . '</a>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $action . '">' . $lang['L_INSTALL_TOMENU'] . '</a>';
            } else {
                fclose($ret);
                echo '<form action="' . $action . '?phase=' . $phase . '" method="post">';
                echo '<table class="bdr" style="width:700px;">';
                echo '<tr><td>' . $lang['L_DB_HOST'] . ':</td><td><input type="text" name="dbaddr" value="' . $config['dbaddr'] . '" size="60" maxlength="100"></td></tr>';
                echo '<tr><td>' . $lang['L_DB_USER'] . ':</td><td><input type="text" name="dbuser" value="' . $config['dbuser'] . '" size="60" maxlength="100"></td></tr>';
                echo '<tr><td>' . $lang['L_DB_PASS'] . ':</td><td><input type="password" name="dbpass" value="' . $config['dbpass'] . '" size="60" maxlength="100"></td></tr>';
                echo '<tr><td>* ' . $lang['L_DB'] . ':<p class="small">(' . $lang['L_ENTER_DB_INFO'] . ')</p></td><td><input type="text" name="dbname" value="' . $config['dbname'] . '" size="60" maxlength="100"></td></tr>';

                $connection = '';
                if (isset($_POST['dbconnect'])) {
                    echo '<tr class="thead"><th colspan="2">' . $lang['L_DBCONNECTION'] . '</th></tr>';
                    echo '<tr><td colspan="2">';
                    $connexion = EA_check_connect_BD($config['dbaddr'], $config['dbuser'], $config['dbpass'], $config['dbname']);

                    if (!$connexion) {
                        echo '<p class="error">' . $lang['L_CONNECTIONERROR'] . '</p>';
                    } else {
                        $databases = array();
                        echo '<p class="success">' . $lang['L_CONNECTION_OK'] . '</p>';
                        $connection = "ok";
                        $connstr = "$dbaddr|$dbuser|$dbpass|$dbname";
                        echo '<input type="hidden" name="connstr" value="' . $connstr . '">';
                    }
                    echo '</td></tr>';
                }
                if ($connection !== "ok") {
                    echo '<tr><td>' . $lang['L_TESTCONNECTION'] . ':</td><td><input type="submit" name="dbconnect" value="' . $lang['L_CONNECTTOMYSQL'] . '" class="Formbutton"></td></tr>';
                }
                echo '</table></form><br>';
                if ($connection == "ok") {
                    echo '<form action="' . $action . '?phase=' . ($phase + 1) . '" method="post">';
                    echo '<input type="hidden" name="dbaddr" value="' . $config['dbaddr'] . '">
                    <input type="hidden" name="dbuser" value="' . $config['dbuser'] . '">
                    <input type="hidden" name="dbpass" value="' . $config['dbpass'] . '">
                    <input type="hidden" name="dbname" value="' . $dbname . '">
                    <input type="hidden" name="connstr" value="' . $connstr . '">';
                    echo '<input type="submit" name="submit" value=" ' . $lang['L_SAVEANDCONTINUE'] . ' " class="Formbutton"></form>';
                }
            }
            break;
        case 2:  // Phase 2 Enregistrement
            echo '<h6>' . $lang['L_DBPARAMETER'] . ' - ' . $lang['L_CONFBASIC'] . '</h6>';
            foreach ($lignes_fichier_config as $i => $ix) {
                $xx = trim($lignes_fichier_config[$i]);
                foreach ($GLOBALS['params_config_EA'] as $k => $v) {
                    $element_longueur = mb_strlen($v);
                    if (($element_longueur > 0) and
                        (mb_strlen($xx) > $element_longueur) and
                        (mb_substr($xx, 0, $element_longueur) == $v)
                    ) {
                        $val = extractValue($xx);
                        $lignes_fichier_config[$i] = $v . " = '" . ${substr($v, 1)} . "';" . "\n";
                    }
                }
            }

            $ret = true;
            if ($fp = fopen(CONFIG_DIRNAME . CONFIG_FILENAME_NEW, "wb")) {
                if (!fwrite($fp, implode("", $lignes_fichier_config))) $ret = false;
                if (!fclose($fp)) $ret = false;
            }
            if (!$ret) {
                echo '<p class="warnung">' . $lang['L_CONFIG_SAVE_ERROR'] . '</p>';
                exit;
            } else {
                if (ini_get('safe_mode') == 1) {
                    $nextphase = (extension_loaded("ftp")) ? 10 : 9;
                } else {
                    $nextphase = $phase + 2;
                }
                $nextphase = '5';
                $lang['L_INSTALL_STEP2_1'] = '.';
                echo '<p>&nbsp;</p>';
                echo '<form action="' . $action . '?phase=' . $nextphase . '" method="post" name="continue">
            <input type="hidden" name="connstr" value="' . $connstr . '">
            <input class="Formbutton" style="width:1px;" type="submit" name="continue2" value=" ' . $lang['L_INSTALL_STEP2_1'] . ' "></form>';
                echo '<script language="javascript">';
                echo 'document.forms["continue"].submit();';
                echo '</script>';
            }

            break;
        case 5: // Phase 5
            echo '<h6>' . $lang['L_LASTSTEP'] . '</h6>';
            echo '<br><h4>' . $lang['L_INSTALLFINISHED'] . '</h4>';
            break;
        default:
            break;
    }
        ?>
        <br>
</body>

</html>