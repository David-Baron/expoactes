<?php
//
// Version du 09/12/2021
// Utilitaire des requêtes Base de données pour ExpoActes
// Auteur : Emmanuel Lethrosne
//
// ATTENTION : Ce script est écrit exclusivement pour ExpoActes version 3.2.x, certaines fonctions n'étant pas utilisées dans ExpoActes, le traitement est limité à celles utilisées.
//
// Inspiré de // https://github.com/dotpointer/mysql-shim/blob/master/mysql-shim.php
// # PHP MySQL to MySQLi migration shim library
//
// MIT License
//
// Copyright (c) 2018 Robert Klebe, dotpointer
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
//
// tableau des connexions aux bases de données. Sauf quand la base utilisateur est sur une autre base, il n'y en a toujours qu'une
$BD_EA_link = array();

// == fonctions communes appelées dans les 2 cas mysql et mysqli
// Recherche la liaison avec le serveur. Mysqli en a toujours besoin alors que Mysql utilise toujours le dernier
function EA_sql_which_link($link_identifier = null)
{
    // une liaison est indiqué, on la retourne
	if ($link_identifier !== null) {
		return $link_identifier;
	} else {
		// Aucune liason de BD connue dans la table, on retourne NULL pour indiquer qu'il n'y a pas de liaison active
		if (!count($GLOBALS['BD_EA_link'])) {
			return NULL;
		}
		// récupère la dernière liaison connue
		$last = end($GLOBALS['BD_EA_link']);
		// rentourner les infos correspondants à cette liaison
		return $last['link'];
	}
}

function is_mysqli_or_resource($r) {
  # get the type of the variable
  switch(gettype($r)) {
    # if it is a resource - could be mysql, file handle etc...
    case 'resource':
      return true;
    # if it is an object - must be a mysqli object then
    case 'object':
      # is this an instance of mysqli?
      if ($r instanceof mysqli) {
        # make sure there is no connection error
        return !($r->connect_error);
      }
      # or is this an instance of a mysqli result?
      if ($r instanceof mysqli_result) {
        return true;
      }
      return false;
    # negative on all other variable types
    default:
      return false;
  }
}

// Ajout d'une liaison dans la table
function BD_EA_link_add($ladbaddr, $ladbuser, $ladbpass, $new_link = false, $client_flags = 0, $is_mysql = false)
{
    // pas de nouveau lien, on vérifie si la connexion au serveur BD n'est pas déjà référencée
    if (! $new_link) {
      // il y a déjà des connexion dans le tableau
      if (count($GLOBALS['BD_EA_link'])) {
        $last = end($GLOBALS['BD_EA_link']); // dernière connexion faite
        // si elle correspond à ladbaddr/ladbuser/ladbpass indiqué
        if ( ($ladbaddr.'|'.$ladbuser.'|'.$ladbpass === $last['BD_sup']) &&
          ( is_mysqli_or_resource($last['link'])   ) ) {
			// on la prend donc
			return EA_sql_which_link(NULL);
        }
      }
    }
	// Ici nouvelle connexion au serveur, la tenter avec les données
	if ($is_mysql) {
		$link = @mysql_connect($ladbaddr, $ladbuser, $ladbpass, $new_link);
		if (@mysql_errno()) {
			return false;
		}
	} else {
		$link = @mysqli_connect($ladbaddr, $ladbuser, $ladbpass, '');
		if (@mysqli_connect_errno()) {
			return false;
		}
	}
    // insère les infos de la connexion serveur dans la table et retourne la liaison
    $GLOBALS['BD_EA_link'][] = array(
      'thread_id' => $link->thread_id,
      'BD_sup' => $ladbaddr.'|'.$ladbuser.'|'.$ladbpass,
      'link' => $link
    );
    return $link;
}
// Retrait d'une liaison de la table
function BD_EA_link_remove($LINK, $is_mysql = false)
{
	$LINK = EA_sql_which_link($LINK);
	if (isset($LINK->thread_id) && is_numeric($LINK->thread_id)) {
		$thread_id =  $LINK->thread_id;
	} else {
		$thread_id =  false;
	}
	if ($is_mysql) $result = mysql_close($LINK);
	else $result = mysqli_close($LINK);
	// la fermeture est OK et il y avait un ID de liaison BD
	if ($result && $thread_id) {
		// parcourir le tableau des liens pour supprimer celui traité
		foreach ($GLOBALS['BD_EA_link'] as $k => $v) {
			if ($v['thread_id'] === $thread_id) {
				array_splice($GLOBALS['BD_EA_link'], $k, 1);
				break;
			}
		}
	} else {
		// Ce cas ne devrait pas arriver
		// la fermeture d'une liaison existante dans le tableau est en échec
		if ($result === null) {
			return false;
		}
	} echo 'ON FERME';exit;foreach( $GLOBALS['BD_EA_link'] as $k => $v) { print_r($v); }

	return $result;
}
// == Fin des fonctions communes appelées dans les 2 cas mysql et mysqli

$APPEL = 'mysqli_connect';
if (! is_callable($APPEL))
{
    // definition des EA_sql_* pour mysql si mysqli n'existe pas
	function EA_sql_query($QUERY, $LINKS = null)
	{
		if ($LINKS !== null) {
			return  mysql_query($QUERY, $LINKS);
		} else {
			return  mysql_query($QUERY);
		}
	}
	function EA_sql_fetch_array($RESULT)
	{
		return mysql_fetch_array($RESULT, MYSQL_BOTH);
	}
	function EA_sql_num_rows($RESULT)
	{
		return mysql_num_rows($RESULT);
	}
	function EA_sql_fetch_assoc($RESULT)
	{
		return  mysql_fetch_assoc($RESULT);
	}
	function EA_sql_fetch_row($RESULT)
	{
		return  mysql_fetch_row($RESULT);
	}
	function EA_sql_get_server_info($dblink = null)
	{
		$dblink = EA_sql_which_link($dblink);
		return    mysql_get_server_info($dblink);
	}
	function EA_sql_affected_rows($dblink = null)
	{
		$dblink = EA_sql_which_link($dblink);
		return     mysql_affected_rows($dblink);
	}
	function EA_sql_stat($dblink = null)
	{
		$dblink = EA_sql_which_link($dblink);
		return        mysql_stat($dblink);
	}
	function EA_sql_error($dblink = null)
	{
		$dblink = EA_sql_which_link($dblink);
		return      mysql_error($dblink);
	}
	function EA_sql_num_fields($RESULT)
	{
		return      mysql_num_fields($RESULT);
	}
	function EA_sql_free_result($RESULT)
	{
		return mysql_free_result($RESULT);
	}
	function EA_sql_real_escape_string($param)
	{
		return mysql_real_escape_string($param);
	}
	function EA_sql_connect($ladbaddr, $ladbuser, $ladbpass, $new_link = false, $client_flags = 0)
	{
		return BD_EA_link_add($ladbaddr, $ladbuser, $ladbpass, $new_link, $client_flags, true);
	}
	function EA_sql_select_db($ladbname, $dblink)
	{
		return mysql_select_db($ladbname, $dblink);
	}
	function EA_sql_close($LINK)
	{
		return BD_EA_link_remove($LINK, true);
	}
} else {
	// definition des EA_sql_* pour mysqli procédural
	{
		function EA_sql_query($QUERY, $LINKS = null)
		{
			$LINKS = EA_sql_which_link($LINKS);
			return  mysqli_query($LINKS, $QUERY);
		}
		function EA_sql_fetch_array($RESULT)
		{
			return mysqli_fetch_array($RESULT, MYSQLI_BOTH);
		}
		function EA_sql_num_rows($RESULT)
		{
			return mysqli_num_rows($RESULT);
		}
		function EA_sql_fetch_assoc($RESULT)
		{
			return mysqli_fetch_assoc($RESULT);
		}
		function EA_sql_fetch_row($RESULT)
		{
			return mysqli_fetch_row($RESULT);
		}
		function EA_sql_get_server_info($dblink = null)
		{
			$dblink = EA_sql_which_link($dblink);
			return mysqli_get_server_info($dblink);
		}
		function EA_sql_affected_rows($dblink = null)
		{
			$dblink = EA_sql_which_link($dblink);
			return mysqli_affected_rows($dblink);
		}
		function EA_sql_stat($dblink = null)
		{
			$dblink = EA_sql_which_link($dblink);
			return mysqli_stat($dblink);
		}
		function EA_sql_error($dblink = null)
		{
			$dblink = EA_sql_which_link($dblink);
			return mysqli_error($dblink);
		}
		function EA_sql_num_fields($RESULT)
		{
			return  mysqli_num_fields($RESULT);
		}
		function EA_sql_free_result($RESULT)
		{
			return mysqli_free_result($RESULT);
		}
		function EA_sql_real_escape_string($param)
		{
			$dblink = EA_sql_which_link(NULL);
			return mysqli_real_escape_string($dblink, $param);
		}
		function EA_sql_connect($ladbaddr, $ladbuser, $ladbpass, $new_link = false, $client_flags = 0)
		{
			return BD_EA_link_add($ladbaddr, $ladbuser, $ladbpass, $new_link, $client_flags, false);
		}
		function EA_sql_select_db($ladbname, $dblink)
		{
			$dblink = EA_sql_which_link($dblink);
			return mysqli_select_db($dblink, $ladbname);
		}
		function EA_sql_close($LINK)
		{
			return BD_EA_link_remove($LINK, false);
		}
	}
}
