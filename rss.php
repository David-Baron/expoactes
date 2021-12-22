<?php
// Page d'accueil publique du programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2006
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
// Corrigé 07/09/2014 ADLC : Codage caractères pour XML (HTML)
// Adapté 06/10/2014  EL : Corrigé l'appel à la table des utilisateurs EA_UDB ligne 45, Remis le département dans le lien ligne 111, Mis le port du serveur lignes 66 et 119
//-------------------------------------------------------------------
include("_config/connect.inc.php");
include("tools/function.php");
include("tools/adlcutils.php");
include("tools/actutils.php");
include('tools/MakeRss/MakeRss.class.php');

function antispam($email)
	{
	return str_replace(array("@"), array("@anti.spam.com@"),$email);
	}

$root = "";
$path = "";

$max = 10;

$xtyp = getparam('type');
$xall = getparam('all');
$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

$request = "";
if ($xall=="") 
		$limit = ' LIMIT '.$max;
	else
		{
		$limit = '';
		$max = 1E4;
		}

if ($xtyp=="" or $xtyp=="A")
  $condit = "";
 else
  $condit = " WHERE TYPACT = '".$xtyp."'";

$request  .="SELECT TYPACT as TYP, sum(NB_TOT) as CPT, COMMUNE, DEPART, concat(PRENOM,' ',u.NOM) as DEPO, EMAIL, DTDEPOT as DTE, AN_MIN as DEB, AN_MAX as FIN"
					. " FROM ".EA_DB."_sums as a left join ".EA_UDB."_user3 as u on (a.deposant=u.id)"
					. $condit
					. ' GROUP BY COMMUNE, DEPART, TYP '
					. ' ORDER BY DTE desc '
					. $limit;
//echo $request;					

$result = EA_sql_query($request);

optimize($request);


/* CHARGEMENT DU GENERATEUR */
$rss = new GenRSS();

/* OUVERTURE DU FIL */
$rss->Load();
$titre = 'Actes de '.SITENAME;

/* LES PARAMETRES OBLIGATOIRES */
$ea_url_site = mkSiteUrl();
$rss->SetTitre(htmlspecialchars($titre, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET));
$rss->SetLink($ea_url_site.$root.'/index.php');
$rss->SetDetails("Dépouillement de tables et actes d'état-civil ou de registres paroissiaux");
/* LES PARAMETRES FACULTATIFS (Mettez // devant les paramètres que vous ne voulez pas renseigner) */
$rss->SetLanguage('fr');
//$rss->SetRights('copyright');
//$rss->SetEditor(EMAIL);
//$rss->SetMaster('email tech');
//$rss->SetImage('http://'.$_SERVER['SERVER_NAME'].DIR_VIGNET.$row["FICHIER"],'','lien');

/* AJOUT DES ARTICLES AU FIL */

$cpt = 0;
while ($row = EA_sql_fetch_array($result) and $cpt<$max)
  {
  $cpt++;
  $titre = $row["COMMUNE"];
  if ($row["DEPART"] != "") $titre .= ' ['.$row["DEPART"].']' ;
	$date_rss = date_rss($row["DTE"]);
	switch ($row["TYP"])
		{
		case "N" :
			$typ="Naissances/Baptêmes";
			$prog = "tab_naiss";
			break;
		case "D" :
			$typ="Décès/Sépultures";
			$prog = "tab_deces";
			break;
		case "M" :
			$typ="Mariages";
			$prog = "tab_mari";
			break;
		case "V" :
			$typ="Actes divers"; // : ".$row["LIBELLE"];
			$prog = "tab_bans";
			break;
		}
  $titre = htmlspecialchars($row["COMMUNE"], ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
  if ($row["DEPART"] != "") $titre .= ' ['.htmlspecialchars($row["DEPART"], ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).']' ;
  $titre .= ' : '.$typ;
  $description = $row["CPT"].' '.$typ.' de '.$row["DEB"].' à '.$row["FIN"];
  $auteur = "";
  $url = $root.'/'.$prog.".php?args=".urlencode($row["COMMUNE"].' ['.$row["DEPART"].']');

  /* $rss->AddItem('Titre','Descripton','Auteur','Catégorie','date','http://'); */
  $rss->AddItem(	htmlspecialchars($titre),
					htmlspecialchars($description),
					htmlspecialchars($auteur),
					$typ,
				  	$date_rss,
					$ea_url_site.$url);
	}

/* FERMETURE DU FIL */
$rss->Close();

/* GENERATION DU RSS */
$rss->Generer();

?>
