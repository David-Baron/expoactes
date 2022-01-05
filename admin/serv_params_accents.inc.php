<?php
// VERSION 5 : 03/01/2022

$MODE_DEBUG = true; // N'applique pas les mises à jour & affiche les information DEBUG de la fin et des requêtes
$MODE_DEBUG = false; // Production

function init_page_EL_GENERALE($head = "", $ajax = "", $affiche = '') // SOURCE DE exporte.php
{
	global $root, $userlevel, $htmlpage, $titre;

	open_page($titre, $root, null, null, $head);
	/*		if ($ajax != "")
		// Ajaxify Your PHP Functions
		include("../tools/PHPLiveX/PHPLiveX.php");
		$ajax = new PHPLiveX($ajax);
		$ajax->Run(false,"../tools/PHPLiveX/phplivex.js");
*/
	navadmin($root, $titre);

	echo '<div id="col_menu">';
	form_recherche($root);
	menu_admin($root, $userlevel);
	echo '</div>';

	echo '<div id="col_main_adm">';
	$htmlpage = true;
	flush();
}

function get_collation_infos_table($table)
{
	global $dbname;
	$requete = "SELECT T.TABLE_COLLATION,CCSA.COLLATION_NAME, CCSA.character_set_name  FROM information_schema.`TABLES` T,
		information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA WHERE CCSA.collation_name = T.table_collation
		AND T.table_schema = '" . $dbname . "'  AND T.table_name = '" . $table . "'";
	if ($GLOBALS['MODE_DEBUG']) echo 'Multibase infos table ' . $requete . '<br>';
	$result = EA_sql_query($requete);
	if ($row = EA_sql_fetch_assoc($result)) { // MULTIBASE mais pas chez FREE !
		$TAB_character_set_name = $row['character_set_name'];
		$TAB_collation_name = $row['TABLE_COLLATION'];
	} else { // MONOBASE
		$requete = "SHOW TABLE STATUS FROM " . $dbname . " LIKE '" . $table . "';";
		if ($GLOBALS['MODE_DEBUG']) echo 'Monobase infos collation table  ' . $requete . '<br>';
		$result = EA_sql_query($requete);
		$row = EA_sql_fetch_assoc($result);
		$TAB_collation_name = $row['Collation'];
		// POUR PRECISER LA BASE :  "use ".$dbname.";" .   NE FONCTIONNE PAS CHEZ FREE
		$requete = "SELECT CHARACTER_SET_NAME FROM information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` WHERE collation_name = '" .  $TAB_collation_name . "';";
		if ($GLOBALS['MODE_DEBUG']) echo 'Monobase infos character table ' . $requete . '<br>';
		$result = EA_sql_query($requete);
		$row = EA_sql_fetch_assoc($result);
		$TAB_character_set_name = $row['CHARACTER_SET_NAME'];
	}
	return array($TAB_character_set_name, $TAB_collation_name);
}

function check_collation($mes_variables_divers, $Trt_accents = 'N')
{
	global $table_control, $dbname, $tables;

	// PARTIE MISE A JOUR
	$Total_etapes = getparam('nbope');
	$Trt_accents = 'N';
	$Trt_accents = 'O'; // VALIDE LES OPERATIONS SINON RIEN ??
	$action = '';
	if ($Trt_accents == 'O') $action = getparam('accents');
	if ($action <> '') {		///// CAS MODIF
		$operation = 'Demande de positionner la recherche ';
		switch ($action) {
			case 'SET':
				$operation .= 'SENSIBLE AUX ACCENTS';
				$new_collation = 'latin1_general_ci';
				break;
			case 'UNSET':
				$operation .= 'INSENSIBLE AUX ACCENTS';
				$new_collation = 'latin1_swedish_ci';
				break;
			default:
				echo 'LLLLLLLLLLLLLLLLL';
				exit;
		};
		$time_start = microtime_float();
		// $autoload = "Y"; $autorise_autoload=true;  // Autorise le rechargement automatisé
		$delaireload = 2;
		$num = getparam('num');
		if ($num == '') $num = 0; // donc $num=0;  au départ
		$max_num = count($tables);
		$affiche = '';
		$affiche_accents_OK = '';

		for ($i = $num; $i < $max_num; $i++) {
			$value = $tables[$i];
			//  POUR EVITER DU TRAVAIL INUTILE
			list($t['character_set_name'], $t['TABLE_COLLATION']) = get_collation_infos_table($value);
			$v = $t['TABLE_COLLATION'];

			if ($v == $new_collation) continue;
			$Total_etapes--;
			$reloadurl = $_SERVER['PHP_SELF'] . '?accents=' . $action . '&num=' . $i . '';

			$affiche .= 'Traitement de la table : ' . $value . ' : ' . $v . '<br>';
			$affiche .= '<div id="topmsg"><p>' . $operation . ' en cours de traitement ... <b>!! NE PAS INTERROMPRE !!</b></p><p align="center"><img src="../img/spinner.gif"></p></div>';
			$affiche .= '<p>' . "Patienter l'opération est très longue : " . (1 + $i) . ' sur ' . $max_num . ", <a href=" . $reloadurl . ">relancer</a> en cas de blocage qui semble trop long.</p>";

			$AUTOMATIQUE = true;
			$AUTOMATIQUE = false; // LE CAS AUTOMATIQUE (AUTOREFRESH) NE FONCTIONNE PAS

			$refresh_automate = getparam('autorefresh');
			if (($refresh_automate == '') and $AUTOMATIQUE) {
				$reloadurl .= '&autorefresh=go';
			} else {
				$r = true;
				if ($GLOBALS['MODE_DEBUG']) $v = $new_collation; // INHIBE EN MODE DEBUG
				if ($v != $new_collation) {
					$requete = "ALTER TABLE `" . $value . "` CONVERT TO CHARACTER SET latin1 COLLATE " . $new_collation . ";";
					if ($GLOBALS['MODE_DEBUG']) echo 'Changement collation ' . $requete . '<br>';
					$r = EA_sql_query($requete);   //      si OK $i++ et  refresh avec param $i;
				}
				if (!$r) {
					echo '<p>' . EA_sql_error() . '<br />' . $requete . '</p>';
					$Total_etapes++;
					exit;
				} else {
					$i++;
				}
				$reloadurl = $_SERVER['PHP_SELF'] . '?accents=' . $action . '&num=' . $i . ''; // $reloadurl.='&autorefresh=go';  A VOIR
				$reloadurl .= '&amp;nbope=' . $Total_etapes;
			};

			if (!$AUTOMATIQUE) {
				$time_end = microtime(true);
				$time = $time_end - $time_start;
				$affiche_accents_OK = '<font color="#FF0000"><b>Fin traitement de la table : ' . $value  . '&nbsp; &nbsp; <a href=' . $reloadurl . '> Continuer ... </a>' . ' (étapes restantes : ' . $Total_etapes . ') </b></font>';
				return array(0, $affiche_accents_OK, false);
			} else {
				// $pageinited = false;
				$metahead = '<META HTTP-EQUIV="Refresh" CONTENT="' . $delaireload . '; URL=' . $reloadurl . '">';
				init_page_EL_GENERALE($metahead, null, $affiche);
				exit;
				return; // DANS LE CAS DU REFRESH ON N'AFFICHE PAS LA SUITE
			}
		} // FIN de boucle sur les tables

		$Collation_nb_diff = 0;
		$affiche_accents_OK = ''; // $affiche_accents_OK .= $affiche;

		echo '<br>'; //echo 'TERMINÉ<br><br>';

	}
	// FIN PARTIE MISE A JOUR

	// PARTIE  CONTROLE
	$Collation_ref = $mes_variables_divers['actuel_collation_tables'];

	$Collation_nb_diff = 0;
	$affiche = '';

	foreach ($tables as $num => $value) {
		list($t['character_set_name'], $t['TABLE_COLLATION']) = get_collation_infos_table($value);
		$v = $t['TABLE_COLLATION'];

		if ($Collation_ref != $v) {
			$Collation_nb_diff++;

			$affiche .= 'La table ' . $value . ' est codée (caractères/Collation) : <b>' . $t['character_set_name'] . ' / ' . $t['TABLE_COLLATION'] . '</b><br>';
			$affiche .= 'Pour aligner : ' . "ALTER TABLE `" . $value . "` CONVERT TO CHARACTER SET latin1 COLLATE " . $Collation_ref . '<br>';
			$affiche .= '<br>';
		}
	}
	if ($Collation_nb_diff != 0) {
		$affiche_accents_OK = '<br><br>' . $affiche;
	}
	// FIN PARTIE  CONTROLE

	return array($Collation_nb_diff, $affiche_accents_OK, true);
} // FIN check_collation
// ==========================
// ==========================
// ==========================
// ==========================

$LOCAL_EA_MAINTENANCE = $row[0]; // Récupère l'information MAINTENANCE du script parents

$GLOBALS['Type_Table'] = array('N' => EA_DB . '_nai3', 'M' => EA_DB . '_mar3', 'D' => EA_DB . '_dec3', 'V' => EA_DB . '_div3');
$affiche_debug = 'TEST';

// $table_control = $GLOBALS['Type_Table']['V']; $champ_control = 'BIDON';
$table_control = EA_DB . '_traceip';
$champ_control = 'ip';
$mes_variables_divers = array();

$tables = array(
	$GLOBALS['Type_Table']['V'], $GLOBALS['Type_Table']['M'], $GLOBALS['Type_Table']['D'], $GLOBALS['Type_Table']['N'],
	EA_DB . '_geoloc', EA_DB . '_log', EA_DB . '_metadb', EA_DB . '_metalg', EA_DB . '_mgrplg', EA_DB . '_prenom', EA_DB . '_sums', EA_DB . '_params', EA_DB . '_traceip', EA_UDB . '_user3',
	$table_control
);

echo '<h3>Paramètres de codage des tables</h3>';

// CONTROLE INDEX DES TABLES
$requete = "SHOW INDEX FROM `" . $GLOBALS['Type_Table']['V'] . "` FROM `" . $dbname . "` WHERE `Key_name`= '" . "LADATE" . "';";
if ($GLOBALS['MODE_DEBUG']) echo 'Controle indexe ' . $requete . '<br>';
$result = EA_sql_query($requete);
$row = EA_sql_fetch_assoc($result);
// récupérer la colonne "Comment" si disabled  faire    ALTER TABLE act_mar3 enable keys
if ($row['Comment'] == 'disabled') echo '<p><font color="#FF0000"><b>Problème les tables n\'ont plus les index actifs.<b></font></p>';

//DONNEES
$requete = "SHOW FULL COLUMNS FROM `" . $table_control . "` FROM `" . $dbname . "` WHERE `Field`= '" . $champ_control . "';";
if ($GLOBALS['MODE_DEBUG']) echo 'Collation données ' . $requete . '<br>';
$result = EA_sql_query($requete);
$row = EA_sql_fetch_assoc($result);
$mes_variables_divers['actuel_collation_donnees'] = $row['Collation'];

// SQL : Que pour la session
$requete = "SHOW SESSION VARIABLES WHERE Variable_Name LIKE " . "'character_set%'" . " or Variable_Name LIKE " . "'collation%'";
if ($GLOBALS['MODE_DEBUG']) echo 'Variables SQL ' . $requete . '<br>';
$result = EA_sql_query($requete);
while ($row = EA_sql_fetch_assoc($result)) {
	$mes_variables_sql[$row['Variable_name']] = $row['Value'];
}

// BASE DE DONNEES
$requete = "SELECT * FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $dbname . "';";
if ($GLOBALS['MODE_DEBUG']) echo 'Multibase infos BD ' . $requete . '<br>';
$result = EA_sql_query($requete);
if ($row = EA_sql_fetch_assoc($result)) { // MULTIBASE mais pas chez FREE !
	$Multibase = true;
	$mes_variables_divers['actuel_character_set_database'] = $row['DEFAULT_CHARACTER_SET_NAME'];
	$mes_variables_divers['actuel_collation_database'] = $row['DEFAULT_COLLATION_NAME'];
} else { // MONOBASE 
	$Multibase = false;
	$requete = "SELECT @@character_set_database AS L_DEF_CHARACTER_SET_NAME, @@collation_database AS L_DEF_COLLATION_NAME;";
	if ($GLOBALS['MODE_DEBUG']) echo 'Monobase infos BD ' . $requete . '<br>';
	$result = EA_sql_query($requete);
	$row = EA_sql_fetch_assoc($result); // show variables like "character_set_database";
	$mes_variables_divers['actuel_character_set_database'] = $row['L_DEF_CHARACTER_SET_NAME']; // $mes_variables_sql['character_set_database'];
	$mes_variables_divers['actuel_collation_database'] = $row['L_DEF_COLLATION_NAME']; // $mes_variables_sql['collation_database'];
}

// TABLES
list($mes_variables_divers['actuel_character_set_tables'], $mes_variables_divers['actuel_collation_tables']) = get_collation_infos_table($table_control);


list($Collation_differentes, $affiche_accents_OK, $fin_maj) = check_collation($mes_variables_divers);

if ($Collation_differentes != 0) {
	if ($LOCAL_EA_MAINTENANCE) {
		$x = ' Site en maintenance';
	} // INHIBE L'AFFICHAGE DE LA BASCULE MAINTENANCE
	else  $x = '  Mettre le site en maintenance pour pouvoir régler le problème';
	echo '<font color="#FF0000"><b>ATTENTION : Anomalie dans le définition du codage des tables, , cela peut engendrer des comportements imprévus !<br>' . $x . '.</b></font>';
}
echo $affiche_accents_OK;

if ((getparam('accents') == '') or ($fin_maj)) {
	echo '<p>';
	switch ($mes_variables_divers['actuel_collation_donnees']) {
		case 'latin1_swedish_ci':
			$t = array('v_actuel' => 'UNSET', 't_actuel' => 'INSENSIBLES (ne distinguent pas)', 'v_change' => 'SET', 't_change' => 'SENSIBLES (distinguent)',);
			break;
		case 'latin1_general_ci':
		default:
			$t = array('v_actuel' => 'SET', 't_actuel' => 'SENSIBLES (distinguent)', 'v_change' => 'UNSET', 't_change' => 'INSENSIBLES (ne distinguent pas)',);
			break;
	}
	if (!in_array($mes_variables_divers['actuel_collation_donnees'], array('latin1_swedish_ci', 'latin1_general_ci')))
		echo '<font color="#FF0000"><b>ATTENTION : Codage de table inconnu, cela peut engendrer des comportements imprévus !.</b></font>';
	else {
		echo 'Les recherches sont ' . $t['t_actuel'] . ' aux accents.';
		if (!$LOCAL_EA_MAINTENANCE) {
			echo ' (changement possible en mode maintenance)';
		} else {
			if ($Collation_differentes != 0) {
				echo '&nbsp; &nbsp;<a href="?nbope=' . $Collation_differentes . '&amp;accents=' . $t['v_actuel'] .  '"><b>Corriger les erreurs de codage</b></a> (nombre d\'étapes : ' . ($Collation_differentes) . ')<br><br>';
			}
			if ($affiche_debug != "") {
				$nb_change = (count($tables) -  $Collation_differentes);
				//echo '&nbsp; &nbsp;<a href="?nbope=' . $nb_change . '&amp;accents=' . $t['v_change'] . '"><b>Modifier pour rendre les recherches ' . $t['t_change'] . ' aux ACCENTS</b></a>' . '&nbsp; (nombre d\'étapes : ' . $nb_change . ')<br>';
				echo '&nbsp; &nbsp;<a href="?nbope=' . $nb_change . '&amp;accents=' . $t['v_change'] . '"><b>Pour changer le comportement lors des recherches</b></a>' . '&nbsp; <font color="#FF0000">(Attention bien faire toutes les étapes : ' . $nb_change . ')</font><br>';
			}
		}
	}
	echo '</p>';
}


// DEBUG AJOUTER  SUR LA LIGNE   &DEBUG=O
if ((getparam('DEBUG') == 'O') or ($MODE_DEBUG)) {
	echo '<br><br>INFORMATIONS A FOURNIR POUR AIDE : (' . ($Multibase ? "Multibase" : "Monobase") . ')<br>';
	echo '========================';
	echo "<p>Encodage du système/fichiers système : " . $mes_variables_sql['character_set_system'] . ' / ' . $mes_variables_sql['character_set_filesystem'] . "</p>";
	echo "<p>Encodage/Interclassement du serveur : " . $mes_variables_sql['character_set_server'] . ' / ' . $mes_variables_sql['collation_server'] . "</p>";
	echo "<p>Encodage/Interclassement par défaut des BD : " . $mes_variables_sql['character_set_database'] . ' / ' . $mes_variables_sql['collation_database'] . "</p>";
	echo "<p>Encodage/Interclassement de cette BD et par défaut de ses tables : " . $mes_variables_divers['actuel_character_set_database'] . ' / ' . $mes_variables_divers['actuel_collation_database'] . "</p>";
	echo "<p>Encodage/Interclassement de la table de référence de cette BD (" . $table_control . ") et par défaut de ses données : " . $mes_variables_divers['actuel_character_set_tables'] . ' / ' . $mes_variables_divers['actuel_collation_tables'] . "</p>";
	echo "<p>Interclassement pour la référence donnée-table de cette BD : " . $mes_variables_divers['actuel_collation_donnees'] . "</p>";
	echo '<br>';
	echo "<p>Encodage/Interclassement de connexion : " . $mes_variables_sql['character_set_connection'] . ' / ' . $mes_variables_sql['collation_connection'] . "</p>";
	echo "<p>Encodage du client : " . $mes_variables_sql['character_set_client'] . "</p>";
	echo "<p>Encodage des résultats : " . $mes_variables_sql['character_set_results'] . "</p>";
	echo '========================<br>';
}
