<?php
// Centralisation pour éviter de répéter le code. Gestion des inclusions indispensables du programme ExpoActes
// Copyright (C) : André Delacharlerie, Emmanuel Lethrosne 2005-2019
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GNU, version 2 (GPLv2), publiée par la Free Software Foundation
// Texte de la licence : https://www.gnu.org/licenses/old-licenses/gpl-2.0.fr.html
//-------------------------------------------------------------------

/* APPEL À FAIRE :
//     $EA_level_min = 1; // À positionner avant l'appel si besoin : niveau minimum d'accès (par défaut ce sera 1 : public)
//   Eventuellement define(ADM,nn); // voir dans "admin/index.php"
if (file_exists("tools/_COMMUN_env.inc.php")) {
    $EA_Appel_dOu = '';
} else {
    $EA_Appel_dOu = '../';
}
if (file_exists('LOCAL-'.basename(__FILE__))) {
    include('LOCAL-'.basename(__FILE__));
    return;
} else {
    include($EA_Appel_dOu.'tools/_COMMUN_env.inc.php'); // ON est ADMIN
}
*/

if ($EA_Appel_dOu == '../') { // par rapport à la position du script appelant : ON est dans ADMIN ou TOOLS ou Autre
    if (!defined('ADM')) {
        define('ADM', 10);
    }
    $admtxt = 'Gestion ';
} else { // ON EST A LA RACINE
    if (!defined('ADM')) {
        define('ADM', 0); // Mode public;
    }
    $admtxt = '';
}

$Ref_Serveur = str_replace(array(':','.'), '-' , $_SERVER['SERVER_NAME']);
if (strtoupper( substr( $Ref_Serveur, 0 , 4) ) == 'WWW-') $Ref_Serveur = substr( $Ref_Serveur, 4);
if (file_exists( $EA_Appel_dOu . '_config/' . 'BD-'. $Ref_Serveur . '-connect.inc.php' ) )
	{
	include_once( $EA_Appel_dOu . '_config/' . 'BD-'. $Ref_Serveur . '-connect.inc.php' );
	}
	else
	{
	include_once $EA_Appel_dOu.'_config/connect.inc.php';   // $dbname = $dbuser=  $dbpass= $dbaddr = '';
	}
include_once 'function.php';
include_once 'adlcutils.php';
include_once 'actutils.php';

// p_info.php n'utilise pas adlcutils.php

if (! in_array(basename($_SERVER['PHP_SELF']),array('eclair.php', 'rss.php', 'p_info.php'))) {
    include_once 'loginutils.php';
}
// Cette partie gère l'existence d'un script modifié localement, son nom doit alors être LOCAL-[Nom complet d'origine]
$EA_Ce_Script = str_replace('LOCAL-','',basename($_SERVER['PHP_SELF']));



