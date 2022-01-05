<?php
// Module de recherche "levenshtein" du programmes ExpoActes
// Copyright (C) : André Delacharlerie + Jean Louis Cazor, 2006

if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
include("tools/cree_table_levenshtein.php");
include("tools/traite_tables_levenshtein.php");

//---------------------------------------------------------
function makecritjlc($xmin, $xmax, $xcomm, $xdepa, $pre, $c_pre, $xpre, $xc_pre) //
{
	$crit = "";
	if ($xmin != "") {
		// $crit = " (year(LADATE)>= ".$xmin.")";
		$crit = " (LADATE>= '" . $xmin . "-00-00')";
	}
	if ($xmax != "") {
		// $critx = " (year(LADATE)<= ".$xmax.")";
		$critx = " (LADATE<= '" . $xmax . "-99-99')";   // -99-99 au lieu de -12-31 pour avoir aussi les dates bizarres
		$crit = sql_and($crit) . $critx;
	}
	if (mb_substr($xcomm, 0, 2) != "**") {
		$critx = " (COMMUNE = '" . sql_quote($xcomm) . "' and DEPART= '" . sql_quote($xdepa) . "')";
		$crit = sql_and($crit) . $critx;
	}
	$critx = "(" . $pre . "  like '" . $xpre . "%')  ";
	$crit = sql_and($crit) . $critx;
	if ($c_pre != "") {
		$critx = "(" . $c_pre . "  like '" . $xc_pre . "%')  ";
		$crit = sql_and($crit) . $critx;
	}

	return $crit;
}
/*
function makecritjlcOLD($xmin,$xmax,$xcomm,$xdepa,$pre,$c_pre,$xpre,$xc_pre) //
{
$crit ="";
	if ($xmin!="")
		{
		$crit = " (year(LADATE)>= ".$xmin.")";
		}
	if ($xmax!="")
		{
		$critx = " (year(LADATE)<= ".$xmax.")";
		$crit = sql_and($crit).$critx;
		}
	if (mb_substr($xcomm,0,2)!="**")
		{
		$critx = " (COMMUNE = '".sql_quote($xcomm)."' and DEPART= '".sql_quote($xdepa)."')";
		$crit = sql_and($crit).$critx;
		}
	   $critx = "(".$pre."  like '".$xpre."%')  ";
	   $crit = sql_and($crit).$critx;
   if ($c_pre!="")
     {
     $critx = "(".$c_pre."  like '".$xc_pre."%')  ";
     $crit = sql_and($crit).$critx;
     }

return $crit;
}
*/
//---------------------------------------------------------

function cree_table_temp_sup($nom, $original)
{

	$request = "CREATE TEMPORARY TABLE " . EA_DB . "_" . $nom . " LIKE " . EA_DB . "_" . $original . ";";
	$result = EA_sql_query($request) or die('Erreur SQL duplication !' . $sql . '<br>' . EA_sql_error());
	$request = "INSERT INTO " . EA_DB . "_" . $nom . " SELECT * FROM " . EA_DB . "_" . $original . ";";
	$result = EA_sql_query($request) or die('Erreur SQL recopie !' . $sql . '<br>' . EA_sql_error());

	return "ok";
}

//--------------------------------------------------------

// récupération d l'adresse IP et substition de "_" aux "." pour créer les tables temporaires
// Modifié pour tenir compte des adresses IPV6
$orig = array('.', ':');
$repl = array('_', '_');
$ip_adr_trait = 'tmp_lev_' . str_replace($orig, $repl, getenv("REMOTE_ADDR"));

if (!defined("SECURITE_TIME_OUT_PHP")) $SECURITE_TIME_OUT_PHP = 5;
else $SECURITE_TIME_OUT_PHP = SECURITE_TIME_OUT_PHP;

$Max_time = ini_get("max_execution_time") - $SECURITE_TIME_OUT_PHP;

$T0 = time();

$root = "";
$path = "";
$txtcomp = "";

if (!defined("CHERCH_TS_TYP")) $cherch_ts_typ = 1;
else $cherch_ts_typ = CHERCH_TS_TYP;

pathroot($root, $path, $xcomm, $xpatr, $page);

$xach  = getparam('achercher');
$xpre  = getparam('prenom');

$xach2 = getparam('achercher2');
$xpre2 = getparam('prenom2');

$xtyps = getparam('TypNDMV');
if ($xtyps == "") { // plusieurs types possibles
	$xtypN = (getparam('TypN') == 'N');
	$xtypD = (getparam('TypD') == 'D');
	$xtypM = (getparam('TypM') == 'M');
	$xtypV = (getparam('TypV') == 'V');
} else { // un type à la fois
	$xtypN = ($xtyps == 'N');
	$xtypD = ($xtyps == 'D');
	$xtypM = ($xtyps == 'M');
	$xtypV = ($xtyps == 'V');
}

$xmin  = getparam('amin');
$xmax  = getparam('amax');
$xcomp = getparam('comp');
$xcomp2 = getparam('comp2');

$comdep  = html_entity_decode(getparam('ComDep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$xcomm = communede($comdep);
$xdepa  = departementde($comdep);

$xord  = getparam('xord');
$page  = getparam('pg');

$userlogin = "";
$db  = con_db();
$userlevel = logonok(LEVEL_LEVENSHTEIN);
while ($userlevel < LEVEL_LEVENSHTEIN) {
	login($root);
}

if (!defined("RECH_MIN")) $rech_min = 3;
else $rech_min = RECH_MIN;
open_page("Recherches dans les tables", $root);
if (current_user_solde() > 0 or RECH_ZERO_PTS == 1) {

	$nav = "";
	if ($xcomp != "")  $nav = '<a href="rechlevenshtein.php">Recherche Levenshtein</a> &gt; ';
	navigation($root, 2, 'A', $nav . "Résultats de la recherche");

	echo '<div class="contenu">';

	echo '<h2>Résultats de la recherche</h2>';

	// Controles critères  de la recherche
	if (strlen($xach) < $rech_min) {
		msg('Le patronyme à chercher doit compter au moins ' . $rech_min . ' caractères.');
	} elseif (!($xtypN or $xtypD or $xtypM or $xtypV))       // ###################NOUVEAU#######################
	{
		msg('La recherche doit porter sur au moins un des types d\'actes.');
	} elseif (strpos("X" . $xach . $xpre . $xach2 . $xpre2, '%') > 0 or strpos("X" . $xach . $xpre . $xach2 . $xpre2, '__') > 0) {
		msg('La recherche ne peut contenir les caractères "%" ou "__".');
	} else {
		$critN = "";
		$critD = "";
		$critM = "";
		$mes = "";

		// création des requetes jointure
		if ($xcomp == "Z") {
			$dm = 'aucune différence';
		}
		if ($xcomp == "U") {
			$dm = 'une différence';
		}
		if ($xcomp == "D") {
			$dm = 'deux différences';
		}
		if ($xcomp == "T") {
			$dm = 'trois différences';
		}
		if ($xcomp == "Q") {
			$dm = 'quatre différences';
		}
		if ($xcomp == "C") {
			$dm = 'cinq différences';
		}
		if ($xmax != '') {
			if ($xmin != '') {
				$critdate = ' de ' . $xmin . ' à ' . $xmax;
			} else {
				$critdate = ' jusqu en ' . $xmax;
			}
		} else {
			if ($xmin != '') {
				$critdate = ' à partir de ' . $xmin;
			} else {
				$critdate = '';
			}
		}

		$fin_ok = 'ok';
		$request = "";
		if (($xach != "") and ($xach2 != ""))   // recherche sur couple
		{
			if ($xcomp2 == "MA") // recherche mariages
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_mar3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach2, $xcomp, EA_DB . "_mar3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$request = "select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Mariage' as LIBELLE," . EA_DB . "_" . $ip_adr_trait . "_h.disth ," . EA_DB . "_" . $ip_adr_trait . "_f.distf "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_mar3.nom = " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_mar3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "C_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . " order by ladate";
				$mes = 'des mariages pour les noms <b>' . strtoupper($xach) . '</b> et <b>' . strtoupper($xach2) . '</b> avec ' . $dm . '  ' . $critdate;
			}
			if ($xcomp2 == "EN")      //recherche enfants naissances seulement
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_nai3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach2, $xcomp, EA_DB . "_nai3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);

				//." from ".EA_DB."_nai3 join ".$ip_adr_trait."_h on ".EA_DB."_nai3.p_nom = ".$ip_adr_trait."_h.nomlev "

				$request = "select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE, LADATE,'Naissance' as LIBELLE,NOM,M_NOM "
					. " from " . EA_DB . "_nai3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_nai3.P_NOM = " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_nai3.M_NOM = " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "P_PRE", "M_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . " order by ladate";
				$mes = 'des naissances enfants pour les noms <b>' . strtoupper($xach) . '</b> et <b>' . strtoupper($xach2) . '</b> avec ' . $dm . '  ' . $critdate;
			}
			if ($xcomp2 == "END")      //recherche enfants naissances deces 
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_nai3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach2, $xcomp, EA_DB . "_nai3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);

				cree_table_temp_sup($ip_adr_trait . "_hb", $ip_adr_trait . "_h");
				cree_table_temp_sup($ip_adr_trait . "_fb", $ip_adr_trait . "_f");
				//	." from ".EA_DB."_nai3 join ".$ip_adr_trait."_h on ".EA_DB."_nai3.p_nom = ".$ip_adr_trait."_h.nomlev "
				$request = "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE, LADATE,'Naissance' as LIBELLE,P_NOM,M_NOM "
					. " from " . EA_DB . "_nai3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_nai3.p_nom = " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_nai3.m_nom = " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "P_PRE", "M_PRE", $xpre, $xpre2);
				//." from ".EA_DB."_dec3 join ".$ip_adr_trait."_hb on ".EA_DB."_dec3.p_nom = ".$ip_adr_trait."_hb.nomlev 
				$request .= " where " . $crit . "  )";
				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE, LADATE,'Décès' as LIBELLE,P_NOM,M_NOM "
					. " from " . EA_DB . "_dec3 join " . EA_DB . "_" . $ip_adr_trait . "_hb on " . EA_DB . "_dec3.p_nom = " . EA_DB . "_" . $ip_adr_trait . "_hb.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_fb on " . EA_DB . "_dec3.m_nom = " . EA_DB . "_" . $ip_adr_trait . "_fb.nomlev ";

				$request .= " where " . $crit . "  )";
				$request .= "order by ladate ";
				$mes = 'des naissances et décès enfants pour les noms <b>' . strtoupper($xach) . '</b> et <b>' . strtoupper($xach2) . '</b> avec ' . $dm . '  ' . $critdate;
			}
			if ($xcomp2 == "TOUT")      //recherche mariages enfants naissances deces 
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_mar3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach2, $xcomp, EA_DB . "_mar3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_nai3", "N", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_dec3", "D", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$request = "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Mariage' as LIBELLE," . EA_DB . "_" . $ip_adr_trait . "_h.disth ," . EA_DB . "_" . $ip_adr_trait . "_f.distf "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_mar3.nom = " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_mar3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "P_PRE", "M_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . "  )";

				cree_table_temp_sup($ip_adr_trait . "_fb", $ip_adr_trait . "_f");

				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE, LADATE,'Naissance' as LIBELLE,NOM,M_NOM "
					. " from " . EA_DB . "_nai3 join " . EA_DB . "_" . $ip_adr_trait . "_n on " . EA_DB . "_nai3.nom = " . EA_DB . "_" . $ip_adr_trait . "_n.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_fb on " . EA_DB . "_nai3.m_nom = " . EA_DB . "_" . $ip_adr_trait . "_fb.nomlev ";
				$request .= " where " . $crit . "  )";

				cree_table_temp_sup($ip_adr_trait . "_ft", $ip_adr_trait . "_f");


				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE, LADATE,'Décès' as LIBELLE,P_NOM,M_NOM "
					. " from " . EA_DB . "_dec3 join " . EA_DB . "_" . $ip_adr_trait . "_d on " . EA_DB . "_dec3.nom = " . EA_DB . "_" . $ip_adr_trait . "_d.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_ft on " . EA_DB . "_dec3.m_nom = " . EA_DB . "_" . $ip_adr_trait . "_ft.nomlev ";

				$request .= " where " . $crit . "  )";



				//############################# Ajout JLC V 2.1.8 - 20-02-2009 ######################################"

				cree_table_temp_sup($ip_adr_trait . "_hq", $ip_adr_trait . "_h");
				cree_table_temp_sup($ip_adr_trait . "_fq", $ip_adr_trait . "_f");

				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Mariage' as LIBELLE," . EA_DB . "_" . $ip_adr_trait . "_hq.disth ," . EA_DB . "_" . $ip_adr_trait . "_fq.distf "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_hq on " . EA_DB . "_mar3.nom = " . EA_DB . "_" . $ip_adr_trait . "_hq.nomlev "
					. " join " . EA_DB . "_" . $ip_adr_trait . "_fq on " . EA_DB . "_mar3.m_nom = " . EA_DB . "_" . $ip_adr_trait . "_fq.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "P_PRE", "M_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . " )";

				cree_table_temp_sup($ip_adr_trait . "_hc", $ip_adr_trait . "_h");
				cree_table_temp_sup($ip_adr_trait . "_fc", $ip_adr_trait . "_f");

				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Mariage' as LIBELLE," . EA_DB . "_" . $ip_adr_trait . "_hc.disth ," . EA_DB . "_" . $ip_adr_trait . "_fc.distf "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_hc on " . EA_DB . "_mar3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_hc.nomlev "
					. " join " . EA_DB . "_" . $ip_adr_trait . "_fc on " . EA_DB . "_mar3.cm_nom = " . EA_DB . "_" . $ip_adr_trait . "_fc.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "CP_PRE", "CM_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . " )";
				//###########################################################""""

				$request .= "order by ladate ";

				$mes = 'des mariages et évènements enfants pour les noms <b>' . strtoupper($xach) . '</b> et <b>' . strtoupper($xach2) . '</b> avec ' . $dm . '  ' . $critdate;
			}
			if ($xcomp2 == "DIV")      //recherche actes divers            ###################NOUVEAU#######################
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_div3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach2, $xcomp, EA_DB . "_div3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				// recherche Int1 Int2 et Int2 Int1 (ce n'est pas H et F )
				$request = "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Acte divers' as LIBELLE," . EA_DB . "_" . $ip_adr_trait . "_h.disth ," . EA_DB . "_" . $ip_adr_trait . "_f.distf "
					. " from " . EA_DB . "_div3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_div3.nom = " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_div3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "C_PRE", $xpre, $xpre2);
				$request .= " where " . $crit . "   )";

				cree_table_temp_sup($ip_adr_trait . "_hb", $ip_adr_trait . "_h");
				cree_table_temp_sup($ip_adr_trait . "_fb", $ip_adr_trait . "_f");

				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Acte divers' as LIBELLE, 'Z' as P_NOM, 'T' as M_NOM "
					. " from " . EA_DB . "_div3 join " . EA_DB . "_" . $ip_adr_trait . "_fb on " . EA_DB . "_div3.nom = " . EA_DB . "_" . $ip_adr_trait . "_fb.nomlev "
					. "  join " . EA_DB . "_" . $ip_adr_trait . "_hb on " . EA_DB . "_div3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_hb.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "c_PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "  )";
				$request .= "order by ladate ";


				$mes = 'des actes divers pour les noms <b>' . strtoupper($xach) . '</b> et <b>' . strtoupper($xach2) . '</b> avec ' . $dm . '  ' . $critdate;
			}			      // ##########################FIN###############################"
		} elseif ($xach != "")  // recherche sur individu
		{
			$mes = 'du nom <b>' . strtoupper($xach) . '</b> avec ' . $dm . ' sur les ';

			if ($xtypM)       // mariages
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_mar3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_mar3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$request = "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE,  C_NOM, C_PRE, LADATE,'Mariage  ' as LIBELLE, 'Zzzzzzzzzzzzzzzzzzzzzzzzz' as P_NOM, 'Ttttttttttttttttttttttttt' as M_NOM  "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_h on " . EA_DB . "_mar3.nom =  " . EA_DB . "_" . $ip_adr_trait . "_h.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "   )";



				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Mariage' as LIBELLE, 'Z' as P_NOM, 'T' as M_NOM "
					. " from " . EA_DB . "_mar3 join " . EA_DB . "_" . $ip_adr_trait . "_f on " . EA_DB . "_mar3.c_nom =  " . EA_DB . "_" . $ip_adr_trait . "_f.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "c_PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "  )";
				$mes = $mes . ' Mariages ';
			}
			if ($xtypD)       //deces
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_dec3", "D", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				if (strlen($request) > 0) $request .= ' union ';
				$request .= "(select ID, TYPACT, DATETXT, COMMUNE, NOM,PRE,'X' as C_NOM, 'Y' as C_PRE, LADATE,'Décès' as LIBELLE, 'Z' as P_NOM, 'T' as M_NOM  "
					. " from " . EA_DB . "_dec3 join " . EA_DB . "_" . $ip_adr_trait . "_d on " . EA_DB . "_dec3.nom = " . EA_DB . "_" . $ip_adr_trait . "_d.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "  )";
				$mes = $mes . ' Décès ';
			}
			if ($xtypN)       //naissances	
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_nai3", "N", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				if (strlen($request) > 0) $request .= ' union ';
				$request .= "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, P_PRE, M_PRE,  LADATE,'Naissance' as LIBELLE,P_NOM,M_NOM  "
					. " from " . EA_DB . "_nai3 join " . EA_DB . "_" . $ip_adr_trait . "_n on " . EA_DB . "_nai3.nom = " . EA_DB . "_" . $ip_adr_trait . "_n.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . " )";
				$mes = $mes . ' Naissances ';
			}
			if ($xtypV)       //actes divers    ##########################NOUVEAU################################	
			{
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_div3", "H", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);
				$fin_ok = table_temp($xach, $xcomp, EA_DB . "_div3", "F", $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time);

				cree_table_temp_sup($ip_adr_trait . "_hb", $ip_adr_trait . "_h");
				cree_table_temp_sup($ip_adr_trait . "_fb", $ip_adr_trait . "_f");


				if (strlen($request) > 0) $request .= ' union ';
				$request .= "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE,  C_NOM, C_PRE, LADATE,'Acte Divers' as LIBELLE, 'Z' as P_NOM,  'T' as M_NOM  "
					. " from " . EA_DB . "_div3 join " . EA_DB . "_" . $ip_adr_trait . "_hb on " . EA_DB . "_div3.nom = " . EA_DB . "_" . $ip_adr_trait . "_hb.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "   )";
				$request .= "union (select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE,'Acte Divers' as LIBELLE, 'Z' as P_NOM, 'T' as M_NOM "
					. " from " . EA_DB . "_div3 join " . EA_DB . "_" . $ip_adr_trait . "_fb on " . EA_DB . "_div3.c_nom = " . EA_DB . "_" . $ip_adr_trait . "_fb.nomlev ";
				$crit = makecritjlc($xmin, $xmax, $xcomm, $xdepa, "c_PRE", "", $xpre, $xpre2);
				$request .= " where " . $crit . "  )";
				$mes = $mes . ' Actes divers ';   // ##########################FIN###############################"
			}
			$request .= "order by ladate ";
			$mes = $mes . '  ' . $critdate;
		}

		if ((time() - $T0 >= $Max_time) or ($fin_ok != 'ok')) {
			echo msg('La recherche ne peut aboutir car elle prend trop de temps !');
			echo '<th> &nbsp; </th>' . "\n";
			echo '<th> &nbsp; </th>' . "\n";
			echo '<th> &nbsp; </th>' . "\n";
			echo '<th> &nbsp; </th>' . "\n";
			echo '<p>Quelques suggestions :</p>';
			echo '<th> &nbsp; </th>' . "\n";
			echo '<th> &nbsp; </th>' . "\n";
			echo '<p>1- Mettez si ce n' . "'" . 'est déjà fait des dates min et max</p>';
			echo '<p>2- Réduisez l' . "'" . 'intervalle de recherche sur les dates</p>';
			echo '<p>3- Diminuez le nombre de différences</p>';
			echo '<p>4- Ne faite la recherche que sur un type d' . "'" . 'acte</p>';
			echo '<p>5-  .....</p>';
			echo '<p>En désespoir de cause, essayez plus tard, le serveur est peut être trop chargé</p>';
			echo '<th> &nbsp; </th>' . "\n";
			echo '<th> &nbsp; </th>' . "\n";
		} else {

			$result = EA_sql_query($request) or die('Erreur SQL requete générale!' . $sql . '<br>' . EA_sql_error());
			$nbtot = EA_sql_num_rows($result);
			//echo "nb".$nbtot."<br>";
			$baselink = $path . '/chercherlevenshtein.php?achercher=' . $xach . '&amp;prenom=' . $xpre . '&amp;comp=' . $xcomp;
			$baselink .= '&amp;achercher2=' . $xach2 . '&amp;prenom2=' . $xpre2 . '&amp;comp2=' . $xcomp2;
			$baselink .= iif($xtypN, '&amp;TypN=N', '') . iif($xtypD, '&amp;TypD=D', '') . iif($xtypM, '&amp;TypM=M', '') . iif($xtypV, '&amp;TypV=V', '');
			$baselink .= '&amp;ComDep=' . urlencode($comdep);
			$baselink .= '&amp;amin=' . $xmin . '&amp;amax=' . $xmax;
			$limit = "";
			$listpages = "";
			pagination($nbtot, $page, $baselink, $listpages, $limit);
			if ($limit <> "") {
				$request = $request . $limit;
				$result = EA_sql_query($request);
				$nb = EA_sql_num_rows($result);
			} else {
				$nb = $nbtot;
			}
			echo '<div class="critrech">Recherche Levenshtein <ul>' . "\n" . $mes . '</ul></div>' . "\n";
			if ($nb > 0) {
				$i = ($page - 1) * MAX_PAGE + 1;
				echo '<p><b>' . $nbtot . ' actes trouvés</b></p>' . "\n";
				echo '<p>' . $listpages . '</p>' . "\n";
				echo '<table summary="Liste des résultats">' . "\n";
				echo '<tr class="rowheader">' . "\n";
				echo '<th> &nbsp; </th>' . "\n";
				echo '<th>Type</th>' . "\n";
				echo '<th>Date</th>' . "\n";
				echo '<th>Intéressé(e)</th>' . "\n";
				echo '<th>Parents</th>' . "\n";
				echo '<th>Commune/Paroisse</th>' . "\n";
				echo '</tr>' . "\n";
				while ($ligne = EA_sql_fetch_row($result)) {
					switch ($ligne[1]) {
						case "N";
							$url = "acte_naiss.php";
							break;
						case "D";
							$url = "acte_deces.php";
							break;
						case "M";
							$url = "acte_mari.php";
							break;
						case "V";
							$url = "acte_bans.php";
							break;
					}
					echo '<tr class="row' . (fmod($i, 2)) . '">' . "\n";
					echo '<td>' . $i . '. </td>' . "\n";
					echo '<td>' . $ligne[9] . ' </td>' . "\n";
					echo '<td>&nbsp;' . annee_seulement($ligne[2]) . '&nbsp;</td>' . "\n";
					echo '<td><a href="' . $url . '?xid=' . $ligne[0] . '&xct=' . ctrlxid($ligne[4], $ligne[5]) . '">' . $ligne[4] . ' ' . $ligne[5] . '</a>';
					if ($ligne[1] == 'M' or ($ligne[1] == 'V' and $ligne[6] <> '')) {
						echo ' x <a href="' . $url . '?xid=' . $ligne[0] . '&xct=' . ctrlxid($ligne[4], $ligne[5]) . '">' . $ligne[6] . ' ' . $ligne[7] . '</a>';
					}
					echo '</td>' . "\n";
					if ($ligne[1] == 'N' or $ligne[1] == 'D') {
						if ($ligne[6][0] . $ligne[10] . $ligne[7][0] . $ligne[11] != 'XZYT') {
							echo '<td>' . $ligne[6][0] . ". " . $ligne[10] . " - " . $ligne[7][0] . ". " . $ligne[11] . ' </td>' . "\n";
						} else {
							echo '<td>' . "  " . ' </td>' . "\n";
						}
					} else {
						echo '<td>' . "  " . ' </td>' . "\n";
					}
					echo '<td>' . $ligne[3] . '</td>' . "\n";
					echo '</tr>' . "\n";
					$i++;
				}
				echo '</table>' . "\n";
				echo '<p>' . $listpages . '</p>' . "\n";
			} else {
				echo '<p> Aucun acte trouvé </p>' . "\n";
			}
			echo '<p>Durée du traitement  : ' . (time() - $T0) . ' sec.</p>' . "\n";
			echo '</div>' . "\n";
		}
	}
} else {
	msg('Recherche non autorisée car votre solde de points est épuisé !');
}
close_page();
