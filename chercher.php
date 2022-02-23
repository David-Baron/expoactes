<?php
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include("tools/loginutils.php");

define("ADM", 0); // Mode public;

//---------------------------------------------------------

function sqlcomp($lazone, $valeur)
{
	global $typ_compare, $txtcomp, $codecomp;
	$valeur = sql_quote($valeur);
	switch ($typ_compare) {
		case "E": // Exacte
			$sql = $lazone . " = '" . $valeur . "'";
			if (isin("A", $codecomp) >= 0)
				$txtcomp = ' est la';
			else
				$txtcomp = ' est le';
			break;
		case "D": // Débute par
			$sql = $lazone . " like '" . $valeur . "%'";
			if (isin("A", $codecomp) >= 0)
				$txtcomp = ' au début de la';
			else
				$txtcomp = ' au debut du';
			break;
		case "F": // Fini par
			$sql = $lazone . " like '%" . $valeur . "'";
			if (isin("A", $codecomp) >= 0)
				$txtcomp = ' à la fin de la';
			else
				$txtcomp = ' à la fin du';
			break;
		case "C": // Contient
			$sql = $lazone . " like '%" . $valeur . "%'";
			if (isin("A", $codecomp) >= 0)
				$txtcomp = ' dans la';
			else
				$txtcomp = ' dans le';
			break;
		case "S": // Soundex
			$sql = "soundex(" . $lazone . ") = soundex('" . $valeur . "')";
			if (isin("A", $codecomp) >= 0)
				$txtcomp = ' sonne comme la';
			else
				$txtcomp = ' sonne comme le';
			break;
	}
	return $sql;
}

//---------------------------------------------------------


function makecrit($nom, $pre, $zone, $comp)
{
	global $critN, $critD, $critM, $critM1, $critM2, $mes;  // valeurs mises à jour par la procédure
	global $xtypN, $xtypD, $xtypM;
	global $typ_compare, $txtcomp, $compmode, $codecomp;
	$typ_compare = $comp;
	$txtcomp = "";
	$codecomp = $zone;

	switch ($codecomp) {
		case "1":
				if ($nom != "") {
					$critX = sqlcomp("NOM", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("C_NOM", $nom) . ')';
					$critM1 = sql_and($critM1) . "(" . $critX . ')';
					$critM2 = sql_and($critM2) . "(" . sqlcomp("C_NOM", $nom) . ')';
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " patronyme de la personne intéressée</li>\n";
				}
				if ($pre != "") {
					$critX = sqlcomp("PRE", $pre);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("C_PRE", $pre) . ')';
					//$critM1 = sql_and($critM1)."(".$critX.')';
					//$critM2 = sql_and($critM2)."(".sqlcomp("C_PRE",$pre).')';
					$mes .= '<li><b>' . $pre . "</b>" . $txtcomp . " prénom de la personne intéressée</li>\n";
				}
				break;
		
		case "2":
				if ($nom != "") {
					$critX = sqlcomp("M_NOM", $nom) . " or " . sqlcomp("T1_NOM", $nom) . " or " . sqlcomp("T2_NOM", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . $critX . " or " . sqlcomp("C_NOM", $nom);
					$critM = sql_and($critM) . "(" . $critD . " or " . sqlcomp("CM_NOM", $nom) . " or " . sqlcomp("T3_NOM", $nom) . " or " . sqlcomp("T4_NOM", $nom) . ")";
					// trop de zones pour faire une recherche indexée ici
					$critD = "(" . $critD . ")";
					$mes .= '<li><b>' . $nom . "</b> dans le patronyme de la mère, des témoins, du parrain ou de la marraine</li>\n";
				}
				break;
	
		case "4": // recherche sur le conjoint
	
				if ($nom != "") {
					$critX = sqlcomp("C_NOM", $nom);
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("EXCON", $nom) . " or " . sqlcomp("C_EXCON", $nom) . ")";
					// trop de zones pour faire une recherche indexée ici
					$xtypN = false;
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " patronyme du (futur/ex) conjoint</li>\n";
				}
				if ($pre != "") {
					$critX = sqlcomp("C_PRE", $pre);
					$xtypN = false;
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . ")";
					$mes .= '<li><b>' . $pre . "</b>" . $txtcomp . " prénom de la (future) épouse</li>\n";
				}
				break;
	
		case "5": // recherche sur patronyme du père

				if ($nom != "") {
					$critX = sqlcomp("P_NOM", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("CP_NOM", $nom) . ')';
					$critM1 = sql_and($critM1) . "(" . $critX . ')';
					$critM2 = sql_and($critM2) . "(" . sqlcomp("CP_NOM", $nom) . ')';
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " patronyme du père</li>\n";
				}
				if ($pre != "") {
					$critX = sqlcomp("P_PRE", $pre);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("CP_PRE", $pre) . ')';
					$mes .= '<li><b>' . $pre . "</b>" . $txtcomp . " prénom du père</li>\n";
				}
				break;

		case "6": // recherche sur patronyme de la mère

				if ($nom != "") {
					$critX = sqlcomp("M_NOM", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("CM_NOM", $nom) . ')';
					$critM1 = sql_and($critM1) . "(" . $critX . ')';
					$critM2 = sql_and($critM2) . "(" . sqlcomp("CM_NOM", $nom) . ')';
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " patronyme de la mère</li>\n";
				}
				if ($pre != "") {
					$critX = sqlcomp("M_PRE", $pre);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("CM_PRE", $pre) . ')';
					$mes .= '<li><b>' . $pre . "</b>" . $txtcomp . " prénom de la mère</li>\n";
				}
				break;
		
		case "7": // recherche sur les parrains / témoins et commentaires
						if ($nom != "") {
					$critX = sqlcomp("T1_NOM", $nom) . " or " . sqlcomp("T2_NOM", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("T3_NOM", $nom) . " or " . sqlcomp("T4_NOM", $nom) . ")";
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " patronyme des témoins ou des parrains et marraines</li>\n";
				}
				if ($pre != "") {
					$critX = sqlcomp("T1_PRE", $pre) . " or " . sqlcomp("T2_PRE", $pre);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("T3_PRE", $pre) . " or " . sqlcomp("T4_PRE", $pre) . ")";
					$mes .= '<li><b>' . $pre . "</b>" . $txtcomp . " prénom des témoins ou des parrains et marraines</li>\n";
				}
				break;
	
		case "8": // recherche sur les commentaires
						if ($nom != "") {
					$critX = sqlcomp("COM", $nom) . " or " . sqlcomp("P_COM", $nom) . " or " . sqlcomp("M_COM", $nom)
						. " or " . sqlcomp("T1_COM", $nom) . " or " . sqlcomp("T2_COM", $nom) . " or " . sqlcomp("COMGEN", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . " or " . sqlcomp("C_COM", $nom) . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("C_COM", $nom) . " or " . sqlcomp("CP_COM", $nom) . " or " . sqlcomp("CM_COM", $nom)
						. " or " . sqlcomp("T3_COM", $nom) . " or " . sqlcomp("T4_COM", $nom) . ")";
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " texte des commentaires personnels et généraux</li>\n";
				}
				break;
			
		case "9": // recherche sur les origines
				if ($nom != "") {
					$critN = ""; // pas d'origine dans les naissances
					$critX = sqlcomp("ORI", $nom);
					$critD = sql_and($critD) . "(" . $critX . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("C_ORI", $nom) . ")";
					$critM1 = sql_and($critM1) . "(" . $critX . ')';
					$critM2 = sql_and($critM2) . "(" . sqlcomp("C_ORI", $nom) . ')';
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " lieu d'origine d'un des intéressés</li>\n";
				}
				break;
			
		case "A": // recherche sur les professions
				if ($nom != "") {
					$critX = sqlcomp("P_PRO", $nom) . " or " . sqlcomp("M_PRO", $nom);
					$critN = sql_and($critN) . "(" . $critX . ")";
					$critD = sql_and($critD) . "(" . $critX . " or " . sqlcomp("PRO", $nom) . ")";
					$critM = sql_and($critM) . "(" . $critX . " or " . sqlcomp("PRO", $nom)
						. " or " . sqlcomp("C_PRO", $nom) . " or " . sqlcomp("CP_PRO", $nom) . " or " . sqlcomp("CM_PRO", $nom) . ")";
					$mes .= '<li><b>' . $nom . "</b>" . $txtcomp . " profession (intéressé et parents)</li>\n";
				}
				break;
			
	}
}

//---------------------------------------------------------
$T0 = time();
$MT0 = microtime_float();

$root = "";
$path = "";
$txtcomp = "";

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

pathroot($root, $path, $xcomm, $xpatr, $page);

$xach  = getparam('achercher');
$xzone = getparam('zone');
$xpre  = getparam('prenom');
$xach2 = getparam('achercher2');
$xzone2 = getparam('zone2');
$xpre2 = getparam('prenom2');
$xach3 = getparam('achercher3');
$xzone3 = getparam('zone3');
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
$xtdiv = getparam('typdivers');
$xmin  = getparam('amin');
$xmax  = getparam('amax');
$xcomp = getparam('comp');
$xcomp2 = getparam('comp2');
$xcomp3 = getparam('comp3');
$comdep  = html_entity_decode(getparam('ComDep'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$xcomm = communede($comdep);
$xdepa  = departementde($comdep);
$xord  = getparam('xord');
$page  = getparam('pg');
if ($xord == "") {
	$xord = "D";
}   // N = Nom, D = dates
if (getparam('direct') == 1)   // ***** recherche directe ****
{
	$xcomp = default_rech_code();  // dépend du parametre RECH_DEF_TYP
	$xcomm = "***";  // toutes 
	$comdep = $xcomm;
	if (CHERCH_TS_TYP == 1) {
		$xtypN = true;
		$xtypD = true;
		$xtypM = true;
		$xtypV = true;
	} else {
		$xtypN = (getparam('typact') == "N");
		$xtypD = (getparam('typact') == "D");
		$xtypM = (getparam('typact') == "M");
		$xtypV = (getparam('typact') == "V");
	}
};
$compmode = "I"; // Indexée par défaut mais ...
if (((isin("FCS", $xcomp) >= 0  or isin("2478", $xzone) >= 0)  and !empty($xach))
	or ((isin("FCS", $xcomp2) >= 0 or isin("2478", $xzone2) >= 0) and !empty($xach2))
	or ((isin("FCS", $xcomp3) >= 0 or isin("2478A", $xzone3) >= 0) and !empty($xach3))
	or ($xpre . $xpre2 <> "")
)
	$compmode = "F"; // Full scan 
$userlogin = "";
$userlevel = logonok(3);
while ($userlevel < 3) {
	login($root);
}

$userid = current_user('ID');
open_page("Recherches dans les tables", $root);
if (current_user_solde() > 0 or RECH_ZERO_PTS == 1) {
	$nav = "";
	if ($xcomp != "")  $nav = '<a href="rechavancee.php">Recherche (avancée)</a> &gt; ';
	navigation($root, 2, 'A', $nav . "Résultats de la recherche");

	echo '<div class="contenu">';

	echo '<h2>Résultats de la recherche</h2>';

	//echo '<p>Mode : '.$compmode."</p>";
	//define ("OPTIMIZE","YES");


	// Résultats de la recherche
	$critN = "";
	$critD = "";
	$critM = "";   // critère mariage/divers pour rech full scan 
	$critM1 = "";  // critère mariage/divers pour rech indexée sur 1er intéressé  ( car le "or" bloque l'indexé)
	$critM2 = "";  // critère mariage/divers pour rech indexée sur 2d intéressé
	$critV = "";
	$mes = "";

	if ($xach . $xpre == "")   $xzone = "";   // zone1 pas utilisé
	if ($xach2 . $xpre2 == "") $xzone2 = "";  // zone2 pas utilisé
	if ($xach3 == "")        $xzone3 = "";  // zone3 pas utilisé

	if ((strlen(trim($xach . $xpre . $xach2 . $xpre2 . $xach3)) < RECH_MIN) and ($userlevel < 8)) {
		msg('La recherche doit porter sur au moins ' . RECH_MIN . ' caractères non blancs.');
	} elseif (!($xtypN or $xtypD or $xtypM or $xtypV)) {
		msg('La recherche doit porter sur au moins un des types d\'actes.');
	} elseif (strpos("X" . $xach . $xpre . $xach2 . $xpre2 . $xach3, '%') > 0 or strpos("X" . $xach . $xpre . $xach2 . $xpre2 . $xach3, '__') > 0) {
		msg('La recherche ne peut contenir les caractères "%" ou "__".');
	} elseif (($xzone == 4 or $xzone2 == 4) and $xtypN and !($xtypD or $xtypM or $xtypV)) {
		msg('Pas de "Conjoint" dans les actes de naissance.');
	} elseif ($xzone3 == 9 and $xtypN and !($xtypD or $xtypM or $xtypV)) {
		msg('Pas de zone "Origine" dans les actes de naissance.');
	} else {
		makecrit($xach, $xpre, $xzone, $xcomp);      // génération critère 1ere personne
		makecrit($xach2, $xpre2, $xzone2, $xcomp2);  // génération critère 2ème personne
		makecrit($xach3, "", $xzone3, $xcomp3);      // génération critère autres élements
		if ($xmin != "") {
			$critX = " (year(LADATE)>= " . $xmin . ")";
			$critN = sql_and($critN) . $critX;
			$critD = sql_and($critD) . $critX;
			$critM = sql_and($critM) . $critX;
			$critM1 = sql_and($critM1) . $critX;
			$critM2 = sql_and($critM2) . $critX;
			if ($xmax == "") $mes .= '<li>Années égales ou postérieures à <b>' . $xmin . "</b></li>\n";
		}
		if ($xmax != "") {
			$critX = " (year(LADATE)<= " . $xmax . ")";
			$critN = sql_and($critN) . $critX;
			$critD = sql_and($critD) . $critX;
			$critM = sql_and($critM) . $critX;
			$critM1 = sql_and($critM1) . $critX;
			$critM2 = sql_and($critM2) . $critX;
			if ($xmin == "") $mes .= '<li>Années antérieures ou égales à <b>' . $xmax . "</b></li>\n";
			else $mes .= '<li>Années comprises entre <b>' . $xmin . '</b> et <b>' . $xmax . "</b></li>\n";
		}
		if (mb_substr($xcomm, 0, 2) != "**") {
			$critX = " (COMMUNE = '" . sql_quote($xcomm) . "' and DEPART= '" . sql_quote($xdepa) . "') ";
			$critN = sql_and($critN) . $critX;
			$critD = sql_and($critD) . $critX;
			$critM = sql_and($critM) . $critX;
			$critM1 = sql_and($critM1) . $critX;
			$critM2 = sql_and($critM2) . $critX;
			$mes .= '<li>Commune ou paroisse de <b>' . $xcomm . " [" . $xdepa . "]</b></li>\n";
		}
	}
	$critV = $critM;
	$critV1 = $critM1;
	$critV2 = $critM2;
	if (($xtypV) and ($xtdiv <> "") and (mb_substr($xtdiv, 0, 2) <> "**")) {
		if (!empty($critV))  $critV  = sql_and($critV) . " (LIBELLE='" . urldecode($xtdiv) . "')";
		if (!empty($critV1)) $critV1 = sql_and($critV1) . " (LIBELLE='" . urldecode($xtdiv) . "')";
		if (!empty($critV2)) $critV2 = sql_and($critV2) . " (LIBELLE='" . urldecode($xtdiv) . "')";
		$mes .= '<li>Actes divers de type <b>' . $xtdiv . "</b></li>" . "\n";
	}

	if (trim($critN . $critM . $critD) == "") {
		msg('Aucun critère de recherche n\'a été spécifié.' . "\n");
	} else {
		$request = "";
		$listactes = "";
		$listtyps = "";
		$listcrit = "";
		if ($xtypM)  // M en premier pour taille zones C_NOM et C_PRE
		{
			$listzones = "ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE, 'Mariage' as LIBELLE ";
			if ($compmode == "F")  // full scan 
			{
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_mar3 "
					. " where  " . $critM . ") ";
			} else  // indexed
			{
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_mar3 "
					. " where  " . $critM1 . ") ";
				$request .= ' union ';
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_mar3 "
					. " where  " . $critM2 . ") ";
			}
			$listactes = "mariages";
			$listtyps .= "N";
		}
		if ($xtypV) {
			if (strlen($request) > 0) $request .= ' union ';
			$listzones = "ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, C_NOM, C_PRE, LADATE, LIBELLE ";
			if ($compmode == "F") {
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_div3 "
					. " where  " . $critV . ") ";
			} else {
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_div3 "
					. " where  " . $critV1 . ") ";
				$request .= ' union ';
				$request .= "(select " . $listzones
					. " from " . EA_DB . "_div3 "
					. " where  " . $critV2 . ") ";
			}
			if (strlen($listactes) > 0) $listactes .= ", ";
			$listactes .= "types divers";
			$listtyps .= "N";
		}
		if ($xtypD) {
			if (strlen($request) > 0) $request .= ' union ';
			$request .= "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, 'X' as C_NOM, 'Y' as C_PRE, LADATE,'Décès' as LIBELLE "
				. " from " . EA_DB . "_dec3 "
				. " where  " . $critD . ") ";
			if (strlen($listactes) > 0) $listactes = ", " . $listactes;
			$listactes = "décès" . $listactes;
			$listtyps .= "N";
		}
		if ($xtypN and !empty($critN)) {
			if (strlen($request) > 0) $request .= ' union ';
			$request .= "(select ID, TYPACT, DATETXT, COMMUNE, NOM, PRE, 'X' as C_NOM, 'Y' as C_PRE, LADATE,'Naissance' as LIBELLE "
				. " from " . EA_DB . "_nai3 "
				. " where  " . $critN . ") ";
			if (strlen($listactes) > 0) $listactes = ", " . $listactes;
			$listactes = "naissances" . $listactes;
			$listtyps .= "N";
		}
		$request .= " order by LADATE";
		$listactes = "<li>Actes de " . $listactes . "</li>\n";

		//	echo $request;
		$reqbigs = "set sql_big_selects=1";
		$resbase = mysql_query($reqbigs);

		optimize($request);

		$result = mysql_query($request);
		$nbtot = mysql_num_rows($result);

		$baselink = $path . '/chercher.php?achercher=' . $xach . '&amp;zone=' . $xzone . '&amp;prenom=' . $xpre . '&amp;comp=' . $xcomp;
		$baselink .= '&amp;achercher2=' . $xach2 . '&amp;zone2=' . $xzone2 . '&amp;prenom2=' . $xpre2 . '&amp;comp2=' . $xcomp2;
		$baselink .= '&amp;achercher3=' . $xach3 . '&amp;zone3=' . $xzone3 . '&amp;comp3=' . $xcomp3;
		$baselink .= iif($xtypN, '&amp;TypN=N', '') . iif($xtypD, '&amp;TypD=D', '') . iif($xtypM, '&amp;TypM=M', '') . iif($xtypV, '&amp;TypV=V', '');
		$baselink .= '&amp;typdivers=' . urlencode($xtdiv) . '&amp;ComDep=' . urlencode($comdep);
		$baselink .= '&amp;amin=' . $xmin . '&amp;amax=' . $xmax;

		$limit = "";
		$listpages = "";
		pagination($nbtot, $page, $baselink, $listpages, $limit);

		if ($limit <> "") {
			$request = $request . $limit;
			$result = mysql_query($request);
			$nb = mysql_num_rows($result);
		} else {
			$nb = $nbtot;
		}

		echo '<div class="critrech">Recherche de : <ul>' . "\n" . $mes . $listactes . '</ul></div>' . "\n";

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
			echo '<th>Commune/Paroisse</th>' . "\n";
			echo '</tr>' . "\n";
			while ($ligne = mysql_fetch_row($result)) {
				switch ($ligne[1]) {
					case "N":
						$url = "acte_naiss.php";
						break;
					case "D":
						$url = "acte_deces.php";
						break;
					case "M":
						$url = "acte_mari.php";
						break;
					case "V":
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
				echo '<td>' . $ligne[3] . '</td>' . "\n";
				echo '</tr>' . "\n";
				$i++;
			}
			echo '</table>' . "\n";
			echo '<p>' . $listpages . '</p>' . "\n";
		} else {
			echo '<p> Aucun acte trouvé </p>' . "\n";
		}
	}
	echo '<p>Durée du traitement  : ' . round(microtime_float() - $MT0, 3) . ' sec.</p>' . "\n";
} else {
	msg('Recherche non autorisée car votre solde de points est épuisé !');
}
echo '</div>' . "\n";
close_page();
