<?php
// Utilitaires spécifiques aux programmes ExpoActes
// Copyright (C) : André Delacharlerie, 2005-2008
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html

//---------------------------------------------------------
// Lecture des paramètres de configuration

define("EA_VERSION_PRG","3.2.2");
//{ $GLOBALS['EAg_BETA']="-beta"; }
{ $GLOBALS['EAg_BETA']="-prod"; }

if (!defined("EA_DB")) define ("EA_DB","act");
if (!defined("EA_UDB")) define("EA_UDB",EA_DB); //préfixe de la table utilisateurs
if(function_exists("date_default_timezone_set"))
	date_default_timezone_set('Europe/Paris');
$T0 = time();
load_params();
$TIPmsg = "";
define ("EXT_BACKUP",".bea");
define ("DIR_BACKUP","_backup/");
if (!defined("EA_ERROR")) /// en principe est lu dans les paramètres 
	define ("EA_ERROR",0);  // Pas d'affichage d'erreur en production 
$ea_error = 0;
switch (EA_ERROR) 
		{
    case 0:
				$ea_error = 0;
        break;
    case 1:
				$ea_error = E_ERROR; // Erreurs uniquements
        break;
    case 2:
				$ea_error = E_ERROR | E_WARNING;
        break;
    case 3:
				$ea_error = E_ALL | E_STRICT;
        break;
    case 4:
				$ea_error = E_ALL;
				define ("OPTIMIZE","YES");
        break;
		}
error_reporting($ea_error);  // definition du niveau d'erreur
define('TOUJOURS','2033-12-31');  // limite des comptes illimités
$lg = 'fr';

//---------------------------------------------------------

function load_params()
	{
	$db  = con_db();
	$res = mysql_query("SHOW TABLES LIKE '".EA_DB."_params';");
	if (mysql_num_rows($res)>0)
		{
		$request = "select * from ".EA_DB."_params";
		$result = mysql_query($request);
		while ($row = mysql_fetch_array($result))
			{
			if (!defined($row["param"])) define ($row["param"],html_entity_decode($row["valeur"], ENTITY_REPLACE_FLAGS, ENTITY_CHARSET));
			//echo "<br>".$row["param"]." = ".constant($row["param"]);
			}
		}
	}	

//---------------------------------------------------------

function open_page($titre,$root="",$js=null,$addbody=null,$addhead=null,$index=null,$rss=null)
	{
	$carcode = 'UTF-8';
	//$carcode = 'ISO-8859-1';
	header('Content-Type: text/html; charset='.$carcode);
	if (file_exists( dirname(__FILE__).'/trt_charset.inc.php'  )) include(dirname(__FILE__).'/trt_charset.inc.php');
  global $path, $userlogin, $scriptname, $commune;
  if ($scriptname=="") $scriptname="index";
  
  if (!defined("META_DESCRIPTION"))  
  	$meta_description = "";
  	else
  	$meta_description = META_DESCRIPTION;
  if (!defined("META_KEYWORDS")) 
  	$meta_keywords = "";
  	else
  	$meta_keywords = META_KEYWORDS;

  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
  echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
  echo "<head>\n";
  echo '<link rel="shortcut icon" href="'.$root.'/img/favicon.ico" type="image/x-icon" />'."\n";
  echo '<link rel="stylesheet" href="'.$root.'/_config/actes.css" type="text/css" />'."\n";
  echo '<link rel="stylesheet" href="'.$root.'/_config/actes_print.css" type="text/css"  media="print" />'."\n";
  
  // Adapté de Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent
  // ADLC - 9/8/2015
  if (!defined("COOKIE_MESSAGE"))
  	$cookie_message = "Acceptez-vous d'utiliser les Cookies ?";
  else
  	$cookie_message = COOKIE_MESSAGE;
  if (!defined("COOKIE_URL_INFO"))
  	$cookie_url = "";
  else
  	$cookie_url = COOKIE_URL_INFO;  
  $cookie_styles = array(1 => "dark-top",2 => "light-top",3 => "dark-bottom",4 => "light-bottom",5 => "dark-floating",6 => "light-floating");
  if (!defined("COOKIE_STYLE"))
  	$cookie_style = $cookie_styles[1];
  else
  	$cookie_style = $cookie_styles[COOKIE_STYLE];  
  echo '<script type="text/javascript">
  		window.cookieconsent_options = {
  			"message":"'.$cookie_message.'",
  			"dismiss":"Accepter les cookies",
  			"learnMore":"En savoir plus",
  			"link":"'.$cookie_url.'",
  			"theme":"'.$cookie_style.'"};</script>';  
  echo '<script type="text/javascript" src="//s3.amazonaws.com/cc.silktide.com/cookieconsent.latest.min.js"></script>';
  // Cookie Consent plugin //
  
  if ($rss <> "")
    {
  	echo '<link rel="alternate" type="application/rss+xml" title="'.$titre.'" href="'.$root.'/'.$rss.'" />';
		}
  if (!($js == null))
    {
  	echo '<script language="Javascript 1.2" type="text/javascript">'."\n";
  	echo $js;
  	echo '</script>'."\n";
		}
  echo "<title>$titre</title>\n";
  echo '<meta http-equiv="Content-Type" content="text/html; charset="'.$carcode.'" />'."\n";
  echo '<meta name="expires" content="never" />'."\n";
  echo '<meta name="revisit-after" content="15 days" />'."\n";
  echo '<meta name="robots" content="all, index, follow" />'."\n";
  echo '<meta name="description" content="'.$meta_description.' '.$titre.'" />'."\n";
  echo '<meta name="keywords" content="'.$meta_keywords.', '.$titre.'" />'."\n";
  echo '<meta name="generator" content="ExpoActes" />'."\n";
  echo INCLUDE_HEADER."\n";
  if (!($addhead == null))
    {
  	echo $addhead."\n";
		}
  echo "</head>\n";
  echo '<body id="'.$scriptname.'" '." $addbody>\n";
  
  if (getparam(EL)=='O') echo $ExpoActes_Charset;
  
  global $TIPmsg;  // message d'alerte pré-blocage IP
  if ($TIPmsg<>"" and (TIP_MODE_ALERT%2)==1) 
		{
		echo '<h2><font color="#FF0000">'.$TIPmsg."</font></h2>\n";
		}
  echo '<div id="top" class="entete">';
	if (EA_MAINTENANCE==1)
		echo '<font color="#FF0000"><b>!! MAINTENANCE !!</b></font>';

  $bandeau = "_config/bandeau.htm";
  if ($root != $path) $bandeau = "../".$bandeau;
  include($bandeau);  
  echo "</div>\n";
	}

//---------------------------------------------------------

function close_page($complet=0,$root=null)
	{
	echo '<div id="pied_page2" class="pied_page2">';
	echo '<div id="totop2" class="totop2"><p class="totop2"><strong><a href="#top">Top</a></strong> &nbsp; </p></div>';
	echo '<div id="texte_pied2" class="texte_pied2"><p class="texte_pied2">'.PIED_PAGE.'</p></div>';
	echo '<div id="copyright2" class="copyright2"><p class="copyright2"><em><a href="http://expocartes.monrezo.be/">ExpoActes</a></em> version '.EA_VERSION.$GLOBALS['EAg_BETA'].' (&copy;<em> 2005-2015, ADSoft)</em></p></div>';
	// echo '<div id="copyright2" class="copyright2"><p class="copyright2"><em><a href="http://expocartes.monrezo.be/">ExpoActes</a></em> version '.EA_VERSION.' (&copy;<em> 2005-2015, ADSoft)</em></p></div>';
	echo '</div>';
	// gestion automatique de Google Analytics
	if (defined("GOOGLE_ANA_CODE") and GOOGLE_ANA_CODE <> "")
	  {
		echo "\n".'<script type="text/javascript">';
		echo "\n".'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");';
		echo "\n".'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E")); ';
		echo "\n".'</script>';
		echo "\n".'<script type="text/javascript">';
		echo "\n".'try {';
		echo "\n".'var pageTracker = _gat._getTracker("'.GOOGLE_ANA_CODE.'");';
		echo "\n".'pageTracker._trackPageview();';
  	echo "\n"."} catch(err) {}</script>\n";
  	}
  global $TIPmsg;  // message d'alerte pré-blocage IP
  if ($TIPmsg<>"" and TIP_MODE_ALERT>=2) 
		{
		echo "<SCRIPT language=javascript>";
		echo 'alert("'.$TIPmsg.'")';
		echo '</SCRIPT>';
		}
 echo "</body>\n";
  echo "</html>\n";  
	}

//---------------------------------------------------------

function explode_date($datetxt)  // transforme en date en un tableau en coupant sur / . - ou blanc
	{
  //echo "<p>".$datetxt;
	if (strpos($datetxt,'/')>0)  // couper sur / ou sur - ou sur un blanc
	  $elements = explode('/', $datetxt);
	elseif (strpos($datetxt,'-')>0)
	  $elements = explode('-', $datetxt);
	elseif (strpos($datetxt,'.')>0)
	  $elements = explode('.', $datetxt);
	elseif (strpos($datetxt,' ')>0)
	  $elements = explode(' ', $datetxt);
	else
		$elements[0] = $datetxt;
	return $elements;
  }
  
//---------------------------------------------------------

function ajuste_date($datetxt,&$datesql,&$badannee)  // remise en forme des dates incomplètes
	{
	//echo '<br>'.$datetxt;
	global $dateincomplete; 

	$elements = explode_date($datetxt);
	$i = count($elements);
	//if (!isset($elements[0])) $elements[0]="";
  $j =1;
	$tdate[1]=0; // annee
	$tdate[2]=0; // mois
	$tdate[3]=0; // jour

	$dateincomplete = false; 
//	if ($elements[0] <= 31 and $elements[2] >= 100)  // --> retourner la date !
//		permuter($elements[0],$elements[2]); 
	if (($i==2) and ($elements[0] > 100) and ($elements[1] > 100))
	  {
	  // cas particulier d'une fourchette d'année assimilée à la 1ere année citée  !!
	  $tdate[1]=intval($elements[0]);
	  $dateincomplete = true;
	  }
	 else
	  {
		while ($i >= 0)
			{
			if (!empty($elements[$i]))
				{
				$tdate[$j]=intval($elements[$i]);
				if ($tdate[$j]==0) $dateincomplete = true;
				$j++;
				}
			$i--;
			}
		}	
	if ($tdate[1] < 100 or $tdate[1] > 2050) $badannee = 1;
					 else $badannee = 0;
	$tdate[1] = trim(str_pad($tdate[1], 4, "0", STR_PAD_LEFT));
	$tdate[2] = trim(str_pad($tdate[2], 2, "0", STR_PAD_LEFT));
	$tdate[3] = trim(str_pad($tdate[3], 2, "0", STR_PAD_LEFT));
	$datetxt=  $tdate[3].'/'.$tdate[2].'/'.$tdate[1];
	$datesql = $tdate[1].'-'.$tdate[2].'-'.$tdate[3];
	//echo " Bad?".$badannee." --> ".$datetxt." --> ".$datesql." --> ".showdate($datesql);
	return $datetxt;
	}


//------------------------------------------------------------------------------

function form_recherche()
	{
	global $root, $userid;
	$userlevel = current_user("level");

	if (max($userlevel,PUBLIC_LEVEL)>= 3 and (current_user_solde()>0 or RECH_ZERO_PTS==1))
	  {
		echo '<div class="menu_zone">'."\n";
		echo '<div class="menu_titre">Recherche directe</div>'."\n";

		echo '<form class="form_rech" name="recherche" method="post" action="'.$root.'/chercher.php">'."\n";
		echo '&nbsp;<input type="text" name="achercher" />'."\n";
		echo '&nbsp;<input type="submit" name="Submit" value="Chercher" />'."\n";
		echo '<br /><input type="radio" name="zone" value="1" checked="checked" />Intéressé(e) '."\n";
		echo '<br /><input type="radio" name="zone" value="2" />Mère, conjoint, témoins, parrain...'."\n";
  	if (CHERCH_TS_TYP!=1)
			{
			echo '<br />&nbsp;Dans les actes de&nbsp;'."\n";
			listbox_types("typact","Naissances");
			//echo '</p>';
			}
		echo '<input type="hidden" name="direct" value="1" />'."\n";
		echo '<input type="hidden" name="debug" value="'.getparam('debug').'" />'."\n";
		echo '<div class="menuTexte" align="right"><dl><dd>';
		echo '<a href="'.$root.'/rechavancee.php">Recherche avancée</a>&nbsp; &nbsp;';

	  if ((RECH_LEVENSHTEIN==2) and (max($userlevel,PUBLIC_LEVEL)>= LEVEL_LEVENSHTEIN))
	    echo '<br /><a href="'.$root.'/rechlevenshtein.php">Recherche Levenshtein</a>&nbsp; &nbsp;';
	    
		echo '</dd></dl></div>';
		echo '</form>'."\n";
		echo '</div>'."\n";
	  }
	}


//------------------------------------------------------------------------------

/*** default_rech_code
* retourne mode de recherche par défaut selon le parametre RECH_DEF_TYP sous forme de lettre
*/
function default_rech_code()
	{
	$typs=array(1=>"E","D","F","C","S");
	return $typs[RECH_DEF_TYP];
	}

//------------------------------------------------------------------------------

/*** prechecked
* Préselectionne le mode de recherche par défaut selon le parametre RECH_DEF_TYP
*/
function prechecked($typrech)
	{
	$deftyp = default_rech_code();
	if ($typrech==$deftyp)
	  return ' value="'.$typrech.'" checked="checked" ';
	  else
	  return ' value="'.$typrech.'" ';
	}

//------------------------------------------------------------------------------

function statistiques($vue="T")
	{
	global $root, $xtyp, $show_alltypes;
	echo '<div class="menu_zone">'."\n";
	echo '<div class="menu_titre">Statistiques</div>'."\n";

	if (SHOW_DATES)
		{
		$crit_dates = " where year(LADATE) > 0 ";
		}
	 else
		{
		$crit_dates = "";
		}

  $request = "select TYPACT, sum(NB_TOT)"
						. " FROM ".EA_DB."_sums "
						. ' group by TYPACT'
						. " order by INSTR('NMDV',TYPACT)"     // cette ligne permet de trier dans l'ordre voulu
						;
	optimize($request);
	$result = mysql_query($request);
	if (!$result) 
		 {
		 $message  = '<p>Requête invalide : ' . mysql_error() . "\n";
		 $message  .= '<br>Requête : ' . $request . "\n";
		 echo ($message);
		 }

	$tot=0;
	$texte="";
	$menu_actes = "";
	if ($result)
	  {
		while ($ligne = mysql_fetch_row($result))
				{
				switch ($ligne[0])
					{
					case "N" :
						$typ="Naissances/Baptêmes";
						break;
					case "M" :
						$typ="Mariages";
						break;
					case "D" :
						$typ="Décès/Sépultures";
						break;
					case "V" :
						$typ="Actes divers";
						break;
					}
				if ($ligne[1]>0)
					{
					$menu_actes .= iif($menu_actes=="",""," | ");
					if ($xtyp != $ligne[0])
						{ $menu_actes .= '<a href="'.$root.'/'."index.php?vue=".$vue.'&xtyp='.$ligne[0].'">'.$typ.'</a>';}
						else
						{ $menu_actes .= $typ ;}
					$texte .= '<dd>';
					if (SHOW_ALLTYPES==0)
					  $texte .= '<a href="'.$root.'/'."index.php?vue=".$vue.'&xtyp='.$ligne[0].'">';
					$texte .= entier($ligne[1]).' '.$typ;
					if (SHOW_ALLTYPES==0) 
						$texte .= '</a>';
					$texte .= '</dd>'."\n";
					}
				$tot   += $ligne[1];
				}
		if (SHOW_ALLTYPES==1)
			{
			$menu_actes .= iif($menu_actes=="",""," | ");
			if ($xtyp != "A")
				{ $menu_actes .= '<a href="'.$root.'/'."index.php?vue=".$vue.'&xtyp=A">'.'Tous'.'</a>';}
				else
				{ $menu_actes .= 'Tous' ;}
			}
		}
	
	echo '<div class="menuTexte"><dl>'."\n";
	echo '<dt><strong>'.entier($tot).' actes</strong> dont :</dt>'."\n".$texte;
	if (SHOW_RSS<>0)
		{
		$urlrss = $root.'/rss.php';
		$mesrss = 'Résumé de la base en RSS';
		if ($show_alltypes==0)
			{
			$urlrss .= "?type=".$xtyp;
			$mesrss .= " (".typact_txt($xtyp).")";
			}
		echo '<dt><a href="'.$urlrss.'" title="'.$mesrss.'"><img src="'.$root.'/tools/MakeRss/feed-icon-16x16.gif" border="0" alt="'.$mesrss.'" /></a></dt>'."\n";
		}
  echo '</dl></div>'."\n";
	
	echo '</div>'."\n";
	return $menu_actes;
	}

//------------------------------------------------------------------------------

function menu_admin($root,$userlevel)
	{
	global $userlogin;
	$login= '&nbsp; &nbsp;&lt;'.$userlogin.'&gt;';

	echo '<div class="menu_zone">'."\n";
	echo '<div class="menu_titre">Administration'.$login.'</div>'."\n";
	echo '<div class="menuCorps"><dl>'."\n";
	if ($userlevel>=5)
		{
		echo '<dt><a href="'.$root.'/admin/index.php">Inventaire des actes</a></dt>'."\n";
		}
	if ($userlevel>=CHANGE_PW)
		{
  	echo '<dt><a href="'.$root.'/changepw.php">Changer le mot de passe</a></dt>'."\n";
		}
	if ($userlevel>=5)
		{
		echo '<dt><a href="'.$root.'/admin/charge.php">Charger des actes NIMEGUE</a></dt>'."\n";
		}
	if ($userlevel>=6)
		{
		echo '<dt><a href="'.$root.'/admin/chargecsv.php">Charger des actes CSV</a></dt>'."\n";
		}
	if ($userlevel>=5)
		{
		echo '<dt><a href="'.$root.'/admin/supprime.php">Supprimer des actes</a></dt>'."\n";
		echo '<dt><a href="'.$root.'/admin/exporte.php">Réexporter des actes</a></dt>'."\n";
		}
	if ($userlevel>=7)
		{
		echo '<dt><a href="'.$root.'/admin/maj_sums.php">Administrer les données</a></dt>'."\n";
		}
	if ($userlevel>=9)
		{
		echo '<dt><a href="'.$root.'/admin/listusers.php">Administrer les utilisateurs</a></dt>'."\n";
		echo '<dt><a href="'.$root.'/admin/gest_params.php">Administrer le logiciel</a></dt>'."\n";
		}
	echo '<dt><a href="'.$root.'/admin/aide/aide.html">Aide</a></dt>'."\n";
	echo '<dt><a href="'.$root.'/index.php?act=logout">Déconnexion</a></dt>'."\n";
	echo '</dl></div>'."\n";
	echo '</div>'."\n";
	}

//----------------------------------------------------------------------------

function menu_users($current)
	{
	global $udbname;
	echo '<p align="center"><strong>Administration utilisateurs : </strong>';
	showmenu('Lister','listusers.php','L',$current,false);
	showmenu('Ajouter','gestuser.php?id=-1','A',$current);
	if (!isset($udbname))
		{ // réservé à la base principale
		showmenu('Importer','loaduser.php','I',$current);
		showmenu('Exporter/Supprimer','expsupuser.php','E',$current);
		showmenu('Informer','envoimail.php','M',$current);
		showmenu('Modifications groupées','gestpoints.php','S',$current);
		}
	echo '</p>';
	}

//----------------------------------------------------------------------------

function menu_datas($current)
	{
	global $userlevel;
	echo '<p align="center"><strong>Administration des données : </strong>';
	showmenu('Statistiques','maj_sums.php','S',$current,false);
	if ($userlevel>7)
		showmenu('Localités','listgeolocs.php','L',$current);
	showmenu('Ajout d\'un acte','ajout_1acte.php','A',$current);
		if ($userlevel>7)
		{
		showmenu('Corrections groupées','corr_grp_acte.php','G',$current);
		showmenu('Backup','exporte.php?Destin=B','B',$current);
		showmenu('Restauration','charge.php?Origine=B','R',$current);
		}
	echo '</p>';
	}

//----------------------------------------------------------------------------

function menu_software($current)
	{
	global $userlevel;
	echo '<p align="center"><strong>Administration du logiciel : </strong>';
	showmenu('Paramétrage','gest_params.php','P',$current,false);
  showmenu('Etiquettes','gest_labels.php','Q',$current);
	showmenu('Etat serveur','serv_params.php','E',$current);
	showmenu('Fitrage IP','gesttraceip.php','F',$current);
	showmenu('Index','gestindex.php','I',$current);
	showmenu('Journal','listlog.php','J',$current);
	echo '</p>';
	}

//----------------------------------------------------------------------------

function showmenu($texte,$proc,$id,$current,$barre=true)
	{
	if ($barre)
		echo ' | ';	  
	if ($id==$current)
		echo '<strong><a href="'.$proc.'">'.$texte.'</a></strong>';
	 else
		echo '<a href="'.$proc.'">'.$texte.'</a>';
	}			

//------------------------------------------------------------------------------

function menu_public()
	{
	global $userlogin, $root, $userlevel;
	$changepw="";
	$login="";
	if ($userlogin!="")
		{
		$login= '&nbsp;&lt;'.$userlogin;
		$solde = current_user_solde();
		if ($solde < 9999)
		  $login .= ' : '.$solde.' pts';
		$login .= '&gt;';

    if ($userlevel >= CHANGE_PW)
      {
			$changepw = '<dt><a href="'.$root.'/changepw.php">Changer le mot de passe</a></dt>'."\n";
			}
		}
	echo '<div class="menu_zone">'."\n";
	if (PUBLIC_LEVEL==4 || PUBLIC_LEVEL==5)
		echo '<div class="menu_titre">Administration'.$login.'</div>'."\n";  // pas de membres visiteurs dans ce cas
	 else
		echo '<div class="menu_titre">Accès membres'.$login.'</div>'."\n";
	echo '<div class="menuCorps"><dl>'."\n";
	if ($userlogin=="")
		{
		echo '<dt><a href="'.$root.'/login.php">Connexion</a></dt>'."\n";
		if (SHOW_ACCES==1)
			{
			echo '<dt><a href="'.$root.'/acces.php">Conditions d\'accès</a></dt>'."\n";
			}
		}
	 else
	  {
	  if ($userlevel > 5)
			echo '<dt><a href="'.$root.'/admin/index.php">Gérer les actes</a></dt>'."\n";
		echo $changepw;
		echo '<dt><a href="'.$root.'/index.php?act=logout">Déconnexion</a></dt>'."\n";
		}
	if (EMAIL_CONTACT <> "")
		echo '<dt><a href="'.$root.'/form_contact.php">Contact</a></dt>'."\n";
	if ($userlevel > 5)	
	  echo '<dt><a href="'.$root.'/admin/aide/aide.html">Aide</a></dt>'."\n";
	echo '</dl></div>'."\n";
	echo '</div>'."\n";  
	}

//------------------------------------------------------------------------------

function show_certifications()
	{
	global $root;
	// Validation XHTML	
	$host = $_SERVER['HTTP_HOST'];
	$uri  = rtrim($_SERVER['PHP_SELF'], "/\\");
	echo '<div class="certificats">'."\n";
  echo '<a href="http://validator.w3.org/check?uri=http://'.$host.$uri.'">';
	echo '<img src="'.$root.'/img/valid-xhtml-10.gif" alt="Site Valide XHTML 1.0" border="0" />';
  echo '</a></div>'."\n";
	}
	
//------------------------------------------------------------------------------

function show_pub_menu()
	{
	if (!defined('PUB_ZONE_MENU')) define ('PUB_ZONE_MENU',"Zone info libre");
	// pub éventuelle
	echo '<div class="pub_menu">'."\n";
  echo PUB_ZONE_MENU;
  echo '</div>'."\n";
	}
	
//------------------------------------------------------------------------------

function zone_menu($admin,$userlevel)
		{
		//affice les menus standardises
		global $root;
		echo '<div id="col_menu">'."\n";
		form_recherche($root);
		if ($admin<>10)
			{
			menu_public();
			show_pub_menu();
			}
			else
			menu_admin($root,$userlevel);
		echo '</div>'."\n";
		}
			
//------------------------------------------------------------------------------

function navigation($root="",$level, $type="", $commune=null,$patronyme=null,$prenom=null)
	{
	$signe="";
	$s2="";
	$s4="";
	switch ($type)
		{
		case "N" :
			$s2="tab_naiss.php";
			$s4="acte_naiss.php";
			$signe="o";
			break;
		case "D" :
			$s2="tab_deces.php";
			$s4="acte_deces.php";
			$signe="+";
			break;
		case "M" :
			$s2="tab_mari.php";
			$s4="acte_mari.php";
			$signe="X";
			break;
		case "V" :
			$s2="tab_bans.php";
			$s4="acte_bans.php";
			$signe="Divers";
			break;
		case "A" :
			$signe=" Distribution selon les années ";
			break;
		case "R" :  // recherche
			$signe="";
			break;
		}
	if ($signe<>"") $signe="(".$signe.")";
	echo '<div class="navigation">';
	echo 'Navigation';
	if ($level>1)
		{
	  if ($level>10)
	    {
	    echo ' :: <a href="'.$root.'/index.php">Accueil</a>'."\n";
	    echo ' &gt; <a href="'.$root.'/admin/index.php">Administration</a>'."\n";
	    $path = $root.'/admin';
	    $level = $level-10;
	    }
	    else
	    {
	    if (SHOW_ALLTYPES==0)
				{
				echo ' :: <a href="'.mkurl($root.'/'."index.php",$type).'">Communes et paroisses</a>'."\n";
				}
			 else
				{
				echo ' :: <a href="'.$root.'/index.php">Communes et paroisses</a>'."\n";
				}
	    $path = $root;
			}
		}
	 else
		{
		if ($level==1) echo ' :: Communes et paroisses'."\n";
		}
	if ($level>2)
		{
		echo ' &gt; <a href="'.mkurl($path.'/'.$s2,$commune).'">'.$commune.$signe.'</a>';
		}
	 else
		{
		if ($level==2) echo ' &gt; '.$commune.$signe."\n";
		}
	if ($level>3)
		{
		echo ' &gt; <a href="'.mkurl($path.'/'.$s2,$commune,$patronyme).'">'.$patronyme.'</a>';
		}
	 else
		{
		if ($level==3) echo ' &gt; '.$patronyme."\n";
		}
  if ($level==4) echo ' &gt; '.$prenom."\n";
	echo '</div>'."\n";
	}

//------------------------------------------------------------------------------

function navadmin($root="",$current='')
	{
	echo '<div class="navigation">';
	echo 'Navigation';
	echo ' :: <a href="'.$root.'/index.php">Accueil</a>'."\n";
	if ($current=='')
	  {
		echo ' &gt; Administration'."\n";
		}
	 else
	  {
	  echo ' &gt; <a href="'.$root.'/admin/index.php">Administration</a>'."\n";
	  echo ' &gt; '.$current."\n";
	  }
	echo '</div>'."\n";
	}

//------------------------------------------------------------------------------

function getCommunes($params)   // Utilisée pour remplir dynamiquement une listbox selon le type d'actes
	{
	// nécessité de passer les parmètres dans une seule variable
	$typact = $params[0];
	$mode =   $params[1];
	$rs = mysql_query("select distinct COMMUNE,DEPART from ".EA_DB."_sums where TYPACT = '$typact' order by COMMUNE, DEPART");  
	$k=0;
	if (mysql_num_rows($rs)==0)
		$options[$k] = array("value" => "", "text" => ("Aucune commune pour ce type"));  
		else
		{
		if ($mode=='2') // backup
			$options[$k] = array("value" => "BACKUP COMPLET", "text" => ("*** Backup complet (par type) ***"));  
		elseif ($mode=='1') // tous
			$options[$k] = array("value" => "TOUTES", "text" => ("*** Toutes ***"));  
		 else
			$options[$k] = array("value" => "", "text" => ("Sélectionner une commune"));  
		while ($row = mysql_fetch_array($rs)) 
			{ 
			$k++;
			$comdep = ($row["COMMUNE"]." [".$row["DEPART"]."]");
			$options[$k] = array("value" => $comdep, "text" => $comdep);  
			}  
		}
	return $options;  
	}

//------------------------------------------------------------------------------

function form_typeactes_communes($mode='',$alldiv=1)
	{
	// Tableau avec choix du type + choix d'une commune existante 
	echo " <tr>\n";
	echo '  <td align="right">Type des actes : &nbsp;</td>'."\n";
	echo '  <td>';
	$ajaxcommune = ' onClick="'."getCommunes(this.value, {'content_type': 'json', 'target': 'ComDep', 'preloader': 'prl'})".'" ';
  echo '  			<input type="hidden" name="TypeActes" value="X" />';
	echo '        <input type="radio" name="TypeActes" value="N'.$mode.'" '.$ajaxcommune.'/>Naissances<br />';
	echo '        <input type="radio" name="TypeActes" value="M'.$mode.'" '.$ajaxcommune.'/>Mariages<br />';
	echo '        <input type="radio" name="TypeActes" value="D'.$mode.'" '.$ajaxcommune.'/>Décès<br />';
	echo '        <input type="radio" name="TypeActes" value="V'.$mode.'" '.$ajaxcommune.'/>Actes divers : &nbsp;';
	listbox_divers("typdivers","***Tous***",$alldiv);
	echo '        <br />&nbsp;<br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Commune / Paroisse : &nbsp;</td>'."\n";
	echo '  <td>';
	echo '  <select id="ComDep" name="ComDep">';  
  echo '    <option value="">Choisir d\'abord le type d\'acte</option> '; 
  echo '  </select><img id="prl" src="../img/minispinner.gif" style="visibility:hidden;">';
	echo '  </td>';
	echo " </tr>\n";
	}

//------------------------------------------------------------------------------

function listbox_communes($fieldname,$default,$vide=0)  // liste de toutes les communes ts actes confondus
  {
	$request = "select distinct COMMUNE,DEPART from ".EA_DB."_sums "
						." order by COMMUNE, DEPART ";
	optimize($request);
	if ($result = mysql_query($request))
		{
		$i = 1;
		echo '<select name="'.$fieldname.'" size="1">'."\n";
		if ($vide==1)
		  echo '<option>*** Toutes ***</option>'."\n";
		if ($vide==2)
		  echo '<option>*** Backup complet (par type) ***</option>'."\n";
		while ($row = mysql_fetch_array($result))
			{
			$comdep=$row["COMMUNE"]." [".$row["DEPART"]."]";
			echo '<option '.selected_option(htmlentities($comdep, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET),$default).'>'.htmlentities($comdep, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</option>'."\n";
			$i++;
			}
		}
	echo " </select>\n";
  }

//------------------------------------------------------------------------------

function communede($comdep)
	{
	$croch = mb_strpos($comdep,"[");
	if ($croch>0)
		$comm = mb_substr($comdep,0,$croch-1);
		else
		$comm = $comdep;
	return $comm;
	}

//------------------------------------------------------------------------------

function departementde($comdep)
	{
	$croch = mb_strpos($comdep,"[");
	if ($croch>0)
		$dep = mb_substr($comdep,$croch+1,mb_strlen($comdep)-$croch-2);
		else
		$dep = "";
	return $dep;
	}

//------------------------------------------------------------------------------

function load_zlabels($table,$lg,$ordre="CSV")
	{
	switch ($ordre)
		{
		case "CSV":
			$condit = "and not (groupe like '_0') order by groupe, OV3";
			break;
		case "NIM3" :
			$condit = "and (OV3>0 and OV3<100) order by OV3";
			break;		
		case "NIM2" :
			$condit = "and (OV2>0 and OV2<100) order by OV2";
			break;
		case "EA3" :
			$condit = "and (OV3>0) order by OV3";
			break;		
		case "EA2" :
			$condit = "and (OV2>0) order by OV2";
			break;		
		}
	// Charges les labels dans un table
	$req1= "select d.ZID, ZONE, GROUPE, BLOC, TAILLE, OBLIG, ETIQ, TYP, AFFICH, GETIQ from (".EA_DB."_metadb d join ".EA_DB."_metalg l join ".EA_DB."_mgrplg g)"
	        ." where ((d.ZID=l.ZID) and (d.GROUPE=g.GRP) and (g.LG='".$lg."') and (g.dtable='".$table."') and (g.sigle=' ') and (l.LG='".$lg."') and (d.dtable='".$table."')) ".$condit;
	//echo $req1;
	$res=mysql_query($req1);
	$nbtot = mysql_num_rows($res);
	$mdb=array();
    for ($j=0;$j<$nbtot;$j++)
  	    {
		array_push($mdb,mysql_fetch_assoc($res));
		}
    //{ print '<pre>MDB:';  print_r($mdb); echo '</pre>'; }		
	return $mdb;
	}

//------------------------------------------------------------------------------

function metadata($zone,$voulu)  // valeur $zone du record $voulu
	{
	global $mdb; /// postule que $mdb a été convenablement initialisé
	$i=0;
	$maxi=count($mdb);
	while (($i<$maxi) and $mdb[$i]['ZONE']<>$voulu)
		{
		$i++;
		}
	if ($i<$maxi)	
		return $mdb[$i][$zone];
		else
		return "Zone $voulu inconnue";
	}

//------------------------------------------------------------------------------

function listbox_types($fieldname,$default,$vide=0)
  {
  $request = "select distinct TYPACT as TYP"
						. " FROM ".EA_DB."_sums "
						. " order by INSTR('NMDV',TYPACT)"     // cette ligne permet de trier dans l'ordre voulu
						;

	optimize($request);
	if ($result = mysql_query($request))
		{
		$i = 1;
		echo '<select name="'.$fieldname.'" size="1">'."\n";
		if ($vide)
		  echo '<option> </option>'."\n";
		while ($row = mysql_fetch_array($result))
			{
			echo '<option '.selected_option($row["TYP"],$default).'>'.typact_txt($row["TYP"]).'</option>'."\n";
			$i++;
			}
		}
	echo " </select>\n";
  }

//------------------------------------------------------------------------------

function listbox_divers($fieldname,$default,$tous=0)
  {
	$request = "select distinct LIBELLE from ".EA_DB."_sums where length(LIBELLE)>0";
	optimize($request);
	if ($result = mysql_query($request))
		{
		$i = 1;
		echo '<select name="'.$fieldname.'">'."\n";
		if ($tous)
		  echo '<option>*** Tous ***</option>'."\n";
		while ($row = mysql_fetch_array($result))
			{
			echo '<option '.selected_option($row["LIBELLE"],$default).'>'.$row["LIBELLE"].'</option>'."\n";
			$i++;
			}
		}
	echo " </select>\n";
  }

//------------------------------------------------------------------------------

function listbox_users($fieldname,$default,$levelmin,$zero=0,$txtzero='')
  {
	global $u_db;
	$request = "select ID, NOM, PRENOM from ".EA_UDB."_user3 where LEVEL >= ".$levelmin." order by NOM,PRENOM";
	//optimize($request,$u_db);
	if ($result = mysql_query($request,$u_db))
		{
		$i = 1;
		echo '<select name="'.$fieldname.'">'."\n";
		if ($zero==1)
			echo '<option '.selected_option(0,$default).'>'.$txtzero.'</option>'."\n";		
		while ($row = mysql_fetch_array($result))
			{
			echo '<option '.selected_option($row["ID"],$default).'>'.$row["NOM"]." ".$row["PRENOM"].'</option>'."\n";
			$i++;
			}
		}
	echo " </select>\n";
  }

//------------------------------------------------------------------------------

function show_simple_item($retrait,$format,$info,$label,$info2="",$url="")  

  // format : somme de 1= label gras, 2 label italique, 4 info gras, 8 info italique
	{
	$sp ="";
	$url1="";
	$url2="";
	$claslab="fich0";
	$clasinf="fich1";

	for ($i=0;$i<$retrait;$i++)
		{ $sp .= "&nbsp;&nbsp;&nbsp;"; }
	if (fmod($format,2)==1) 
		{
		$label = '<strong>'.$label.'</strong>';
		$claslab="fich2";
		}

	if ( div($format,2)==1) $label = '<em>'.$label.'</em>';
	if ( div($format,4)==1) $info  = '<strong>'.$info.'</strong>';
	if ( div($format,8)==1) $info  = '<em>'.$info.'</em>';
	if ($info2<>"")
		{
		if ( div($format,4)==1) $info2  = '<strong>'.$info2.'</strong>';
		if ( div($format,8)==1) $info2  = '<em>'.$info2.'</em>';
		$info2 = " ".$info2;
		}
	if ($url<>"")
		{
		$url1='<a href="'.$url.'">';
		$url2='</a>';
		}
	echo '<tr>';
	echo '<td class="'.$claslab.'">'.$sp.$label.'&nbsp;:&nbsp;</td>';
	echo '<td class="'.$clasinf.'">'.$url1.$info.$url2.$info2.'</td>';
	echo '</tr>'."\n";
	}

//------------------------------------------------------------------------------
	
function grp_label($gp,$tb,$lg,$sigle='')
	{
	$request = "select GETIQ from ".EA_DB."_mgrplg where lg='".$lg."' and dtable='".$tb."' and grp='".$gp."' and sigle=' '";
	$result = mysql_query($request);
	$row = mysql_fetch_array($result);
	$label = $row["GETIQ"];
	if ($sigle<>'')
	  {  // on cherche le label spécifique s'il existe
		$request = "select GETIQ from ".EA_DB."_mgrplg where lg='".$lg."' and dtable='".$tb."' and grp='".$gp."' and sigle='".$sigle."'";
		$result = mysql_query($request);
		if (mysql_num_rows($result)>0)
			{
		  $row = mysql_fetch_array($result);
			$label = $row["GETIQ"];
			}
		}
	return $label;
	}
	
//------------------------------------------------------------------------------

function show_grouptitle3($row,$retrait,$format,$type,$group,$sigle='')  
  // 
  // format : somme de 1= label gras, 2 label italique, 4 info gras, 8 info italique
	{
	$listvals = "";
	$cas = "'O'";
	if (ADM==10) $cas .= ",'A'";
	$req1= "select count(ZONE) as CPT from ".EA_DB."_metadb"
	        ." where DTABLE='".$type."' and GROUPE='".$group."' and AFFICH in (".$cas.")";
	$rs=mysql_fetch_assoc(mysql_query($req1));
	$affich = $rs["CPT"];
	//echo "<p>".$req1." -> !".$affich."!";
	if ($affich==0)
		{ // si pas d'obligatoires alors voir les facultatives
		$req1= "select ZONE from ".EA_DB."_metadb"
						." where DTABLE='".$type."' and GROUPE='".$group."' and AFFICH='F'";
		$res1=mysql_query($req1);
		
		while ($rz=mysql_fetch_assoc($res1))
			$listvals .= trim($row[$rz["ZONE"]]);
		$affich = strlen($listvals);
		}
	//echo "<p>".$req1." -> !".$listvals."!".$affich;
	if ($affich>0)
		{
		$lg = 'fr';
		$label = grp_label($group,$type,$lg,$sigle);
		show_simple_item($retrait,$format,'',$label);
		}
  }

	//------------------------------------------------------------------------------

function show_item3($row,$retrait,$format,$zidinfo,$url="",$zidinfo2="",$activelink=0)  
  // 
  // format : somme de 1= label gras, 2 label italique, 4 info gras, 8 info italique
	{
	$lg = 'fr';
	$req1= "select ZONE, GROUPE, TYP, TAILLE, OBLIG, AFFICH, ETIQ, AIDE from (".EA_DB."_metadb d join ".EA_DB."_metalg l)"
	        ." where ((d.ZID=l.ZID) and (l.LG='".$lg."') and d.ZID=".$zidinfo.")";
	$res1=mysql_fetch_assoc(mysql_query($req1));
	//echo $req1;
  $info  = $row[$res1["ZONE"]];
  $oblig = $res1["AFFICH"];  // F = Facultatif, O = Obligatoire, A=Adminstration seulmt
  $label = $res1["ETIQ"];
  
	if ($zidinfo2!="")
		{
		$req2= "select ZONE, GROUPE, TYP, TAILLE, OBLIG, AFFICH, ETIQ, AIDE from (".EA_DB."_metadb d join ".EA_DB."_metalg l)"
						." where ((d.ZID=l.ZID) and (l.LG='".$lg."') and d.ZID=".$zidinfo2.")";
		$res2=mysql_fetch_assoc(mysql_query($req2));
 		$info2 = $row[$res2["ZONE"]];
		}
 		else
 		$info2 = "";
  
	if ((trim($info).trim($info2)!="" and $oblig=="F") or $oblig=='O' or (ADM==10 and $oblig=="A") or SHOW_NULL==1)
		{

		switch ($res1["TYP"])
			{
			case "TXT":
			case "AGE":
				//$info = strtr($info,"??","+"); signe "décédé"
				$info = str_replace("§"," <br />",$info);
				break;
			case "DAT":  // date en format texte
			  if ($res1["ZONE"]=="DATETXT")
			  	{
					if (trim($row["DREPUB"])!="")
						$info.=' ('.$row["DREPUB"].')';
					}			  
				break;
			case "DTE":  // date en format SQL
				$info = showdate($info);
				break;
			case "SEX":
				$info = sexe($info);
				break;
			}
		if ($activelink<>0)  // urlifie les url et les images JPG et autres
			$info = linkifie($info,$activelink);
		  
		show_simple_item($retrait,$format,$info,$label,$info2,$url);
		}
	}

//-----------------------------------------------------------------------------

function show_deposant3($row,$retrait,$format,$zidinfo,$xid,$tact)  
  // accessoirement affiche la possibilité de proposer une correction
  // format : somme de 1= label gras, 2 label italique, 4 info gras, 8 info italique
	{
	global $u_db;
	$lg = 'fr';
	$req1= "select ZONE, GROUPE, TYP, TAILLE, OBLIG, AFFICH, ETIQ, AIDE from (".EA_DB."_metadb d join ".EA_DB."_metalg l)"
	        ." where ((d.ZID=l.ZID) and (l.LG='".$lg."') and d.ZID=".$zidinfo.")";
	$res1=mysql_fetch_assoc(mysql_query($req1));
	//echo $req1;
  $info  = $row[$res1["ZONE"]];
  $oblig = $res1["AFFICH"];  // F = Facultatif, O = Obligatoire, A=Adminstration seulmt
  $label = $res1["ETIQ"];
	$depid  = $row["DEPOSANT"];
	$req= "select NOM,PRENOM from ".EA_UDB."_user3 where (ID=".$depid.")";
	$curs=mysql_query($req,$u_db);
	if (mysql_num_rows($curs)==1)
		{
	  $res=mysql_fetch_assoc($curs);
		$info = $res["NOM"]." ".$res["PRENOM"];
		}
		else
		$info = "#".$depid;

	if (ADM==10)
		{
		global $path,$userlevel;
		$userid = current_user('ID');
		show_simple_item($retrait,$format,$info,$label);
		if ($userid==$depid or $userlevel>=8)
			{
			$actions = "";
			if ($tact == 'M' or $tact == 'V')
				{ $actions .= '<a href="'.$path.'/permute.php?xid='.$xid.'&amp;xtyp='.$tact.'">Permuter</a> - ';}
			$actions .=  '<a href="'.$path.'/edit_acte.php?xid='.$xid.'&amp;xtyp='.$tact.'">Editer</a>';
			$actions .=  ' - <a href="'.$path.'/suppr_acte.php?xid='.$xid.'&amp;xtyp='.$tact.'">Supprimer</a>';
			show_simple_item($retrait,$format,$actions,'Actions');
			}	
		}
	 else
		{
		if ($oblig=="O" or (trim($info)!="" and $oblig=="F"))
			{
			show_simple_item($retrait,$format,$info,$label);
			}
		}
	}

//-----------------------------------------------------------------------------

function sexe($code)
  {
	switch ($code)
		{
		case "M" :
			return "Masculin";
			break;
		case "F" :
			return "Féminin";
			break;
		case "?" :
			return "Non précisé";
			break;
	  return $code;		
		}
  }
  
//-----------------------------------------------------------------------------

function liste_patro_1($script,$root,$xcomm,$xpatr,$titre,$table,$gid,$note)
// liste_patro_1("tabnaiss.php",$root,$xcomm,$xpatr,"Naissances / baptêmes",EA_DB."_nai");
// Liste des patronymes pour les actes à UN intervenant (naissance et décès)
	{
	$lgi = 1;
	$initiale = "";
	$comdep  = html_entity_decode($xcomm, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
	$Commune = communede($comdep);
	$Depart  = departementde($comdep);
	if (mb_substr($xpatr,0,1) == "_")
		{
		$lgi  = mb_strlen($xpatr); // utf-8 ==> multibytes si accents !
		$initiale = " and left(NOM,($lgi-1))= '".sql_quote(mb_substr($xpatr,1))."'" ;
		}

	echo '<h2>'.$titre.'</h2>'."\n";
	echo '<p>Commune/Paroisse : <a href="'.mkurl($root.'/'.$script,$xcomm).'"><strong>'.$xcomm.'</strong></a>'.geoUrl($gid).'</p>'."\n";
	if ($note<>'')
		echo "<p>".$note."</p>";

	if ($Depart<>"")
		$condDep=" and DEPART = '".sql_quote($Depart)."'";
		else
		$condDep="";

  // Faut-il découper le fichier par initiales ?
	$request = "select count(*)"
				." from $table "
				." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale;
	optimize($request);
	$result = mysql_query($request);
  $ligne = mysql_fetch_row($result);
  $nbresu = $ligne[0];

	if ($nbresu > 0 and $nbresu <= iif((ADM>0),MAX_PATR_ADM,MAX_PATR))
		{
		$request = "select NOM, count(*), min(year(LADATE)),max(year(LADATE)) "
					." from $table "
					." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale
					." group by NOM ";
		optimize($request);
		$result = mysql_query($request);
		$nblign = mysql_num_rows($result);

		$i = 1;
		echo '<table summary="Liste alphabétique">'."\n";
		echo '<tr class="rowheader">'."\n";
		echo '<th>&nbsp;</th>'."\n";
		echo '<th>&nbsp;Patronymes&nbsp;</th>'."\n";
		echo '<th>&nbsp;Périodes&nbsp;</th>'."\n";
		echo '<th>&nbsp;Actes&nbsp;</th>'."\n";
		echo '</tr>';
		while ($ligne = mysql_fetch_row($result))
			{
			echo '<tr class="row'.(fmod($i,2)).'">'."\n";
			echo '<td>&nbsp;'.$i.'.</td>'."\n";
			echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm,$ligne[0]).'">'.$ligne[0].'</a></td>'."\n";
			echo '<td align="center">'.$ligne[2];
			if ($ligne[2]<>$ligne[3]) echo '-'.$ligne[3];
			echo '</td>'."\n";
			echo '<td align="center">'.$ligne[1].'</td>'."\n";
			echo '</tr>'."\n";
			$i++;
			}
		echo '</table>'."\n";
		}
	if ($nbresu > iif((ADM>0),MAX_PATR_ADM,MAX_PATR) )
		{ // Alphabet car trop de patronymes
		$request = "select left(NOM,$lgi), count(distinct NOM), min(NOM), max(NOM)"
							 ." from $table "
							 ." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale
							 ." group by left(NOM,$lgi)";

		optimize($request);
		$result = mysql_query($request);
		$nblign = mysql_num_rows($result);
		
		if ($nblign==1 and $lgi>3)  // Permet d'éviter un bouclage si le nom devient trop petit
			{
			$request = "select NOM, count(distinct NOM), min(NOM), max(NOM)"
								 ." from $table "
								 ." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale
								 ." group by NOM";
			optimize($request);
			$result = mysql_query($request);
		  }

		$i = 1;
		echo '<table summary="Liste alphabétique">'."\n";
		echo '<tr class="rowheader">'."\n";
		echo '<th>Initiales</th>'."\n";
		echo '<th>Patronymes</th>'."\n";
		echo '<th>&nbsp;Noms&nbsp;</th>'."\n";
		echo '</tr>';
		while ($ligne = mysql_fetch_row($result))
			{
			echo '<tr class="row'.(fmod($i,2)).'">'."\n";
			echo '<td align="center"><strong>'.$ligne[0].'</strong></td>'."\n";
			if ($ligne[1]==1) 
				{
				echo '<td align="center">'.$ligne[1].'</td>'."\n";
				echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm,$ligne[2]).'">'.$ligne[2].'</a></td>'."\n";
				}
			 else
				{
				echo '<td align="center">'.$ligne[1].'</td>'."\n";
				while (mb_strlen($ligne[0])<$lgi)
					$ligne[0] = $ligne[0].' ';
				echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm,'_'.$ligne[0]).'">'.$ligne[2].' à '.$ligne[3].'</a></td>'."\n";
				}
			echo '</tr>'."\n";
			$i++;
			}
		echo '</table>'."\n";
		}
	if ($nbresu = 0)
		{
		echo 'Aucun patronyme trouvé'."\n";
		}
	}

//------------------------------------------------------------------------

function liste_patro_2($script,$root,$xcomm,$xpatr,$titre,$table,$stype="",$gid,$note)
// Liste des patronymes pour les actes à DEUX intervenants (mariages et divers)

  {
	$lgi = 1;
	$initiale  = "";
	$initialeF = "";
	$initdeux  = "";
	$comdep  = html_entity_decode($xcomm, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
	$Commune = communede($comdep);
	$Depart  = departementde($comdep);

	if (mb_substr($xpatr,0,1) == "_")
		{
		$lgi  = mb_strlen($xpatr);
		$initiale  = " left(NOM,($lgi-1))= '".sql_quote(mb_substr($xpatr,1))."'" ;
		$initialeF = " left(C_NOM,($lgi-1))= '".sql_quote(mb_substr($xpatr,1))."'" ;
		$initdeux  = " and (".$initiale. " or ". $initialeF.")";
		$initiale  = " and ".$initiale ;
		$initialeF = " and ".$initialeF ;
		}
		
  if ($stype<>"")
  	{
    $soustype = " and LIBELLE = '".sql_quote($stype)."'";
    $sousurl  = ";".$stype;
    $stitre   = " (".$stype.")";
}
    else
    {
    $soustype = "";
    $sousurl  = "";
    $stitre   = "";
}

	echo '<h2>'.$titre.'</h2>'."\n";
	echo '<p>Commune/Paroisse : <a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl).'"><strong>'.$xcomm.'</strong></a>'.geoUrl($gid).'</p>'."\n";
	if ($note<>'')
		echo "<p>".$note."</p>";

	if ($Depart<>"")
		$condDep=" and DEPART = '".sql_quote($Depart)."'";
		else
		$condDep="";

  // Faut-il découper le fichier par initiales ?
	$request = "select count(*)"
				." from $table "
				." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initdeux.$soustype;
	optimize($request);
	$result = mysql_query($request);
  $ligne = mysql_fetch_row($result);
  $nbresu = $ligne[0];

	if ($nbresu > iif((ADM>0),MAX_PATR_ADM,MAX_PATR))
		{ // Alphabet car trop de patronymes
		$req1 = "select left(NOM,$lgi), count(distinct NOM), min(NOM), max(NOM)"
							 ." from $table "
							 ." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale.$soustype
							 ." group by left(NOM,$lgi)";
		$result1 = mysql_query($req1);
		$nb1 = mysql_num_rows($result1);
		optimize($req1);

    $req2 = "select left(C_NOM,$lgi), count(distinct C_NOM), min(C_NOM), max(C_NOM)"
							 ." from $table "
							 ." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initialeF.$soustype
							 ." group by left(C_NOM,$lgi)";
		$result2 = mysql_query($req2);
		$nb2 = mysql_num_rows($result2);
		optimize($req2);


	  $i = 1;
	  $fini = 0;
	  $lire1 = 0;
	  $lire2 = 0;
	  $neof1 = ($ligne1 = mysql_fetch_row($result1));
	  while (remove_accent($ligne1[0])=="" and $neof1)
	    {$neof1 = ($ligne1 = mysql_fetch_row($result1));}
	  $neof2 = ($ligne2 = mysql_fetch_row($result2));
	  while (remove_accent($ligne2[0])=="" and $neof2)
	  	{$neof2 = ($ligne2 = mysql_fetch_row($result2));}
	    
	  
		echo '<table summary="Liste alphabétique">'."\n";
	  echo '<tr class="rowheader">'."\n";
	  echo '<th>Initiales</th>'."\n";
	  echo '<th>Patronymes</th>'."\n";
	  echo '</tr>';
	  while ($fini==0 and $i<100)
			{
			$mari = remove_accent($ligne1[0]);
			$femm = remove_accent($ligne2[0]);
			$code=250;
			if ($mari =="") {$mari = chr($code);}
			if ($femm =="") {$femm = chr($code);}
			//echo "<p>Mari = ".$mari." - Femme = ".$femm."</p>";
			if ($mari==chr($code) && $femm==chr($code))
				{
				$fini = 1;
				}
			 else
				{
  			echo '<tr class="row'.(fmod($i,2)).'">';
				if ($mari < $femm)
				  {
  	      $lenom=$ligne1[0];
  	      $lemin=$ligne1[2];
  	      $lemax=$ligne1[3];
					$lire1 = 1;
					}
				elseif ($mari > $femm)
					{
   	      $lenom=$ligne2[0];
  	      $lemin=$ligne2[2];
  	      $lemax=$ligne2[3];
  	      $lire2 = 1;
					}
				else
					{
					// alors =
   	      $lenom=$ligne1[0];
  	      $lemin=strmin($ligne1[2],$ligne2[2]);
  	      $lemax=strmax($ligne1[3],$ligne2[3]);
				  $lire1 = 1;
					$lire2 = 1;
					}
				echo '<td align="center"><strong>'.$lenom.'</strong></td>'."\n";
				while (mb_strlen($lenom)<$lgi)
					$lenom = $lenom.' ';
				if ($lemin==$lemax)
					{
					echo '<td><a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl,$lemin).'">'.$lemin.'</a></td>'."\n";
					}
				 else
					{
					echo '<td><a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl,'_'.$lenom).'">'.$lemin.' à '.$lemax.'</a></td>'."\n";
					}
				echo '</tr>';
				if ($lire1==1) //and $neof1)
					{
					$neof1 = ($ligne1 = mysql_fetch_row($result1));
					$lire1 = 0;
					}
				if ($lire2==1) //and $neof2)
					{
					$neof1 = ($ligne2 = mysql_fetch_row($result2));
					$lire2 = 0;
					}
				$i++;
				}
			}
		echo '</table>'."\n";
		}
  elseif ($nbresu > 0)
    {
		$req1 = "select distinct NOM, count(*), min(year(LADATE)),max(year(LADATE))"
					." from $table "
					." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initiale.$soustype
					." group by NOM "
					." order by NOM";
		$result1 = mysql_query($req1);
  	optimize($req1);
		$nb1 = mysql_num_rows($result1);

		$req2 = "select distinct C_NOM, count(*), min(year(LADATE)),max(year(LADATE)) "
					." from $table "
					." where COMMUNE = '".sql_quote($Commune)."'".$condDep.$initialeF.$soustype. " and C_NOM<>''"
					." group by C_NOM "
					." order by C_NOM";

		$result2 = mysql_query($req2);
  	optimize($req1);
		$nb2 = mysql_num_rows($result2);

	  $i = 1;
	  $fini = 0;
	  $lire1 = 0;
	  $lire2 = 0;
	  echo '<table summary="Liste alphabétique">'."\n";
	  echo '<tr class="rowheader">'."\n";
	  echo '<th> </th>';
	  echo '<th align="left">Patronymes</th>'."\n";
		echo '<th>Périodes</th>'."\n";
	  if ($table==EA_DB."_mar")
	  	{
			echo '<th>Epoux</th>'."\n";
			echo '<th>Epouses</th>'."\n";
			}
		else
	  	{
			echo '<th>Intervenant 1</th>'."\n";
			echo '<th>Intervenant 2</th>'."\n";
			}

	  echo '</tr>';
	  $neof1 = ($ligne1 = mysql_fetch_row($result1));
	  $mari = strtoupper(remove_accent($ligne1[0]));
	  $neof2 = ($ligne2 = mysql_fetch_row($result2));
	  $femm = strtoupper(remove_accent($ligne2[0]));

	  while ($fini==0)
			{
			$code=254;
			if ($mari =="" and !$neof1) {$mari = chr($code);}
			if ($femm =="" and !$neof2) {$femm = chr($code);}
			if ((!$neof1 and !$neof2) ) //or ($mari==chr($code) && $femm==chr($code)))
				{
				$fini = 1;
				}
			 else
				{
				echo '<tr class="row'.(fmod($i,2)).'">';
				echo '<td>'.$i.'. </td>';
				if ($mari < $femm)
					{
					echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl,$ligne1[0]).'">'.$ligne1[0].'</a></td>'."\n";
					echo '<td align="center"> '.fourchette_dates($ligne1[2],$ligne1[3]).'</td>'."\n";
					echo '<td align="center"> '.$ligne1[1].'</td>'."\n";
					echo '<td align="center"> '.'-'.'</td>'."\n";
					$lire1 = 1;
					}
				elseif ($mari > $femm)
					{
					echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl,$ligne2[0]).'">'.$ligne2[0].'</a></td>'."\n";
					echo '<td align="center"> '.fourchette_dates($ligne2[2],$ligne2[3]).'</td>'."\n";
					echo '<td align="center"> '.'-'.'</td>'."\n";
					echo '<td align="center"> '.$ligne2[1].'</td>'."\n";
					$lire2 = 1;
					}
				else
					{
					// alors =
					echo '<td>&nbsp;<a href="'.mkurl($root.'/'.$script,$xcomm.$sousurl,$ligne1[0]).'">'.$ligne1[0].'</a></td>'."\n";
					echo '<td align="center"> '.fourchette_dates($ligne1[2],$ligne1[3],$ligne2[2],$ligne2[3]).'</td>'."\n";
					echo '<td align="center"> '.$ligne1[1].'</td>'."\n";
					echo '<td align="center"> '.$ligne2[1].'</td>'."\n";
					$lire1 = 1;
					$lire2 = 1;
					}
				echo '</tr>'."\n";
				}
			if ($lire1==1) //and $neof1)
				{
				$neof1 = ($ligne1 = mysql_fetch_row($result1));
				$mari = strtoupper(remove_accent($ligne1[0]));
				$lire1 = 0;
				}
			if ($lire2==1) //and $neof2)
				{
				$neof2 = ($ligne2 = mysql_fetch_row($result2));
				$femm = strtoupper(remove_accent($ligne2[0]));
				$lire2 = 0;
				}
			$i++;
			}
		echo '</table>'."\n";
		}
	 else
		{
		echo 'Aucun patronyme trouvé'."\n";
		}
  }

//----------------------------------------------------------

function fourchette_dates($d1min=0,$d1max=0,$d2min=0,$d2max=0)
	{
	$min = 0;
	$max = 0;
	if ($d1min>0) $min = $d1min;
	if ($d2min>0 and $d2min < $min) $min = $d2min;
	if ($d1max>0) $max = $d1max;
	if ($d2max>0 and $d2max > $max) $max = $d2max;
	if ($max > $min) 
		{
		if ($min > 0) $res = $min."-".$max;
			else $res = $max;
		}
		else
		if ($min > 0) $res = $min;
			else $res = "-";
	return $res;	
	}

//------------------------------------------------------------------------

function pagination($nbtot, &$page, $href, &$listpages,&$limit)
  {
  // $nbtot : Nombre de records
  // $page : page courante
  // $href : URL de base
  // $listpages : liste des n° de page avec lien (en résultat)
  // $limit : clause LIMIT pour MySQL (résultat)

  $debut = 3;
  $autour = 4;
  $maxpage = iif((ADM>0),MAX_PAGE_ADM,MAX_PAGE);
	if ($nbtot > $maxpage)
		{
		// Plus d'une page
		$totpages = intval(($nbtot-1) / $maxpage)+1;
		$listpages = "";
		if ($page == "")
			{$page = 1;}
		if ($totpages > 1)
			{
			$pp = false;
			$listpages = "";
			for ($p=1;$p<=$totpages;$p++)
				{
				if (($p <= $debut) or ($p > ($totpages-$debut)) or ($p >= ($page-$autour) and $p <= ($page+$autour)))
				  {
					if ($p==$page)
						{
						$pp = false;
						$listpages = $listpages."<strong> ".$p."</strong>"."\n";
						}
					 else
						{
						$listpages = $listpages.' <a href="'.$href.'&amp;pg='.$p.'">'.$p."</a>"."\n";
						}
					}
				 else
				  {
				  if (!$pp) $listpages .= " ..... ";
				  $pp = true;
				  }
				}
		  $listpages = "<strong>Pages :</strong>".$listpages."\n";
			}
	  $limit = " limit ".($page-1)*$maxpage.",".$maxpage;
		}
	 else
		{
		$listpages = "";
		$limit = "";
		$page=1;
		}
  }

//---------------------------------------------------------------------------

function actions_deposant($userid,$depid,$actid,$typact)  // version graphique
  {
	global $path,$userlevel,$u_db;
	$req= "select NOM,PRENOM from ".EA_UDB."_user3 where (ID=".$depid.")";
	$curs=mysql_query($req,$u_db);
	if (mysql_num_rows($curs)==1)
		{
	  $res=mysql_fetch_assoc($curs);
		$depinfo = $res["NOM"]." ".$res["PRENOM"];
		}
		else
		$depinfo = "#".$depid;
	if ($userid==$depid or $userlevel>=8)
		{
		echo '<td align="center">&nbsp;'."\n";
		echo $depinfo.' ';
    if ($typact == 'M' or $typact == 'V')
      { echo '<a href="'.$path.'/permute.php?xid='.$actid.'&amp;xtyp='.$typact.'">'.icone("P").'</a> - ';}
		echo '<a href="'.$path.'/edit_acte.php?xid='.$actid.'&amp;xtyp='.$typact.'">'.icone("M").'</a>';
		echo ' - <a href="'.$path.'/suppr_acte.php?xid='.$actid.'&amp;xtyp='.$typact.'">'.icone("S").'</a>';
		echo '&nbsp;</td>'."\n";
		}
		else
		{
		echo '<td align="center">&nbsp;';
		echo $depinfo; //iif(($depnom==""),"#".$depid,$depnom.' '.$deppre);
		echo '&nbsp;</td>'."\n";
		}
	}

//----------------------------------------------------------------------------

function show_depart($depart)
	{
	if ($depart<>"") return " [".$depart.']';
	  else return "";
	}

//----------------------------------------------------------------------------

function typact_txt($typact)
	{
	$typ="";
	switch (strtoupper($typact))
		{
		case "N" :
		case "NAI" :
			$typ="Naissances";
			break;
		case "D" :
		case "DEC" :
			$typ="Décès";
			break;
		case "M" :
		case "MAR" :
			$typ="Mariages";
			break;
		case "V" :
		case "DIV" :
			$typ="Actes divers";
			break;
		case "U" :  // par extension ..
			$typ="Utilisateurs";
			break;
		case "P" :
			$typ="Paramètres";
			break;
		}
  return $typ;
	}

//--------------------------------------------------------------------------

// Vérification du solde des points et décompte de la consommation ($cout)
//  modifié le 5-3-2007 pour gérer la consultation répétée du meme acte

function solde_ok($cout=0,$dep_id,$typact,$xid)
	{
	global $userlogin, $avertissement,$u_db;

  if (GEST_POINTS==0 or PUBLIC_LEVEL>=4 or $cout==0)  // pas de gestion des points si .... !!
		{
	  return 1;  // pas de gestion des points
		}
   else
		{
		if (isset($_COOKIE['viewlst']))
			{$lstactvus = explode(',',decrypter($_COOKIE['viewlst'],'solde'));}
		 else
		  {$lstactvus = array();} // vide
		
		
		$sql = "SELECT * FROM ".EA_UDB."_user3 WHERE login = '".$userlogin."'";
		$res=mysql_query($sql,$u_db);
		if ($res and mysql_num_rows($res)!=0)
			{
			$row = mysql_fetch_array($res);
			$userid = $row['ID'];
			if (($row['level']>=8) or ($row['regime']==0) or ($userid == $dep_id))
				{
				// On note seulement la consultation
				$newconso = $row['pt_conso']+$cout;
				$reqmaj = "update ".EA_UDB."_user3 set pt_conso = ".$newconso." where ID=".$userid."";
				$result = mysql_query($reqmaj,$u_db);
				//$avertissement .= 'Solde inchangé ('.$lesolde. ' points) car vous avez déposé cet acte'; 
				return 1; // pas de restriction pour cet utilisateur car immunisé ou déposant
				}
			else
				{
				$lesolde = $row['solde'];
				$cle = $typact.number_format($xid,0,'','');
				if (!(array_search($cle,$lstactvus)===false))
					{
					$avertissement .= 'Déjà examiné ce jour : solde inchangé ('.$lesolde. ' points)'; 
					return 1;
					} // déja vu => cout nul
				else
					{
					if ($lesolde>=1)
						{
						// imputer le cout
						array_push($lstactvus,$cle);
						$newsolde = max($lesolde-$cout,0);
						$newconso = $row['pt_conso']+$cout;
						$reqmaj = "update ".EA_UDB."_user3 set solde = ".$newsolde.", pt_conso = ".$newconso." where ID=".$userid."";
						if ($result = mysql_query($reqmaj,$u_db))
							{
							$avertissement .= 'Il vous reste à présent '.$newsolde. ' points';  // passé par variable globale
							}
							else
							{
							echo 'Erreur dans la gestion des points ';
							//echo '<p>'.mysql_error().'<br />'.$reqmaj.'</p>';
							}
						//print_r($lstactvus);
						setcookie('viewlst',crypter(implode(',',$lstactvus),'solde'));	
						return $lesolde;  // solde avant retrait
						}
					else
						{
						$avertissement .= 'Votre solde de points est épuisé !';  // passé par variable globale
						if ($row['regime']==2)
							{
							$datecredit = date("d-m-Y",strtotime($row['maj_solde'])+(DUREE_PER_P*86400));
							$avertissement .= '<br /> <br />Il sera automatiquement crédité de '.PTS_PAR_PER.' points le '.$datecredit.'.';
							}
						return 0;
						}
					}
				}
			}
		}
	}

//------------------------------------------------------------------------

function dt_expiration_defaut()

	{
	if (LIMITE_EXPIRATION=="")
		$dtexpir = TOUJOURS;
		else
		{
		$dtexpir ="";
		if (isin(LIMITE_EXPIRATION,"/")>0)
			ajuste_date(LIMITE_EXPIRATION,$dtexpir,$MauvaiseAnnee=1);  // creée ladate en sql
			else
			{
		  if (LIMITE_EXPIRATION>0) 
				$dtexpir = date("Y-m-d",time()+60*1440*LIMITE_EXPIRATION);
				else
				$dtexpir = TOUJOURS;
			}
		}
	return $dtexpir;
	}

//------------------------------------------------------------------------

function recharger_solde()
	{
	global $userlogin, $avertissement, $u_db;
	$sql = "SELECT * FROM ".EA_UDB."_user3 WHERE login = '".$userlogin."'";
	$res=mysql_query($sql,$u_db);
	$row = mysql_fetch_array($res);
	// recharge SI conditions remplies par le compte de l'utilisateur
	$lesolde = $row['solde'];
	$userid = $row['ID'];
	if (($row['regime']==2) and ($row['level']<8))
		{
		// recharger si nécessaire
			if ((strtotime("now")-(DUREE_PER_P*86400)) >= strtotime($row['maj_solde']))
		  {
		  if ($lesolde < PTS_PAR_PER)	// pour ne pas supprimer des points "bonus" on attend.
				{
				$lesolde = PTS_PAR_PER;
				$reqmaj = "update ".EA_UDB."_user3 set solde = ".$lesolde.", maj_solde = '".today()."' where ID=".$userid."";
				if ($result = mysql_query($reqmaj,$u_db))
					{
					$avertissement .= 'Votre compte a été automatiquement crédité de '.PTS_PAR_PER. ' points<br />';  // passé par variable globale
					}
					else
					{
					echo 'Erreur dans gestion des points ';
					echo '<p>'.mysql_error().'<br />'.$reqmaj.'</p>'."\n";
					}
				}
		  }
		}
	}

//------------------------------------------------------------------------

function current_user_solde()
	{
	if (GEST_POINTS==0)
	  return 9999 ;
	 else
	 	{
	  if (current_user('level') >= 8 or current_user('regime')==0)
	    return 9999 ;
	   else
	    return current_user('solde');
	  }
	}

//------------------------------------------------------------------------

function show_signal_erreur($typ,$xid,$ctrlcod)
	{
	global $root;
	if (strlen(EMAIL_SIGN_ERR)>0)
		{
		show_simple_item(0,1,'<a href="'.$root.'/signal_erreur.php?xty='.$typ.'&xid='.$xid.'&xct='.$ctrlcod.'" target="_blank">Cliquez ici pour la signaler</a>','Trouvé une erreur ?');
		}
	}

//------------------------------------------------------------------------

function show_solde()
	{
	$solde = current_user_solde();
	if ($solde < 9999)
	  {
	  if ($solde > 0)
	  	$mess = 'Vous avez encore <b>'.$solde.' points</b> pour consulter le détail des actes.';
	 	 else
	  	$mess = '<font color="#FF0000"><b>Votre solde de points est épuisé pour consulter le détail des actes.</b></font>';
	  echo '<p>'.$mess.'</p>';	
	  }  
	}

//----------------------------------------------------------------------------

function annee_seulement($date_txt)  // affichage date simplifié à l'annee si droits limités
	{
	global $userid, $userlevel;

	if ((ANNEE_TABLE>=3) or (ANNEE_TABLE>=1 and $userid==0) or (ANNEE_TABLE>=2 and $userlevel<5) or (current_user_solde()==0))
		{
		$tdsql = "";
		$bad=0;
		$date_txt = ajuste_date($date_txt,$dtsql,$bad);
		return mb_substr($date_txt,strrpos($date_txt,"/")+1);
		}
	 else
		return $date_txt; // date complète
	}

//----------------------------------------------------------------------------

function lb_droits_user($lelevel,$all=0)  //
	{
	echo '<select name="lelevel" size="1">';
	echo '<option '.selected_option(0,$lelevel).'>0 : ** Aucun accès **</option>'."\n";
	echo '<option '.selected_option(1,$lelevel).'>1 : Liste des communes</option>'."\n";
	echo '<option '.selected_option(2,$lelevel).'>2 : Liste des patronymes</option>'."\n";
	echo '<option '.selected_option(3,$lelevel).'>3 : Table des actes</option>'."\n";
	echo '<option '.selected_option(4,$lelevel).'>4 : Détails des actes (avec limites)</option>'."\n";
	echo '<option '.selected_option(5,$lelevel).'>5 : Détails sans limitation</option>'."\n";
	echo '<option '.selected_option(6,$lelevel).'>6 : Chargement NIMEGUE et CSV</option>'."\n";
	echo '<option '.selected_option(7,$lelevel).'>7 : Ajout d\'actes</option>'."\n";
	echo '<option '.selected_option(8,$lelevel).'>8 : Administration tous actes</option>'."\n";
	echo '<option '.selected_option(9,$lelevel).'>9 : !! Gestion des utilisateurs !!</option>'."\n";
	if ($all==1)
		echo '<option '.selected_option(10,$lelevel).'>A : *** Tous >>> Backup ***</option>'."\n";
	if ($all==2)
		echo '<option '.selected_option(10,$lelevel).'>A : *** Envoi à tous ***</option>'."\n";
	echo "</select>\n";
	}

//----------------------------------------------------------------------------

function lb_statut_user($statut,$vide=0)  //
	 {
	 echo '<select name="statut" size="1">';
	 if (($vide%2)==1)
	 	echo '<option '.selected_option("0",$statut).'>- Pas de condition -</option>'."\n";	   
	 echo '<option '.selected_option("W",$statut).'>W : Attente d\'activation</option>'."\n";
	 echo '<option '.selected_option("A",$statut).'>A : Attente d\'approbation</option>'."\n";
	 echo '<option '.selected_option("N",$statut).'>N : Accès autorisé</option>'."\n";
	 echo '<option '.selected_option("B",$statut).'>B : Accès bloqué</option>'."\n";
	 if (($vide%4)==3)
	 	echo '<option '.selected_option("X",$statut).'>X : Compte expiré de '.DUREE_EXPIR.' jrs</option>'."\n";	   
	 echo "</select>\n";
	 }
//----------------------------------------------------------------------------

function lb_regime_user($regime,$vide=0)  //
	 {
	 echo '<select name="regime" size="1">';
	 if ($vide==1)
	 	echo '<option '.selected_option(-1,$regime).'>- Pas de condition -'."\n";	   
	 echo '<option '.selected_option(0,$regime).'>0 : Accès libre</option>'."\n";
	 echo '<option '.selected_option(1,$regime).'>1 : Recharge manuelle</option>'."\n";
	 echo '<option '.selected_option(2,$regime).'>2 : Recharge automatique</option>'."\n";
	 echo "</select>\n";
	 }
	
//----------------------------------------------------------------------------

function def_mes_sendmail()
	{
	$lb        = "\r\n";
	$message  = "Bonjour,".$lb;
	$message .= "".$lb;
	$message .= "Un compte vient d'être créé pour vous permettre de vous connecter au site :".$lb;
	$message .= "".$lb;
	$message .= "#URLSITE#".$lb;
	$message .= "".$lb;
	$message .= "Votre login : #LOGIN#".$lb;
	$message .= "Votre mot de passe : #PASSW#".$lb;
	$message .= "".$lb;
	$message .= "Cordialement,".$lb;
	$message .= "".$lb;
	$message .= "Votre webmestre.".$lb;
	return $message;
	}

//----------------------------------------------------------------------------

function stats_1_comm($xtyp,$lacom)
	{
	echo '<p>Traitement de <b>'.$lacom."</b></p>";
	switch ($xtyp)
		{
		case "N":
		$table = EA_DB."_nai3";
		$libel = "'' as LIBELLE,";
		break;
		case "V":
		$table = EA_DB."_div3";
		$libel = "LIBELLE,";
		break;
		case "M":
		$libel = "'' as LIBELLE,";
		$table = EA_DB."_mar3";
		break;
		case "D":
		$table = EA_DB."_dec3";
		$libel = "'' as LIBELLE,";
		break;
		}
		$request = "select COMMUNE, DEPART, ".$libel
									." count(*) as ctot,"
									."  DEPOSANT, max(DTDEPOT) as ddepot,"
									."  min(if(year(LADATE)>0,year(LADATE), null)) as dmin,"  // null indispensabel pour que le tri élimine les 0
									."  max(year(LADATE)) as dmax, "
									."  sum(if(length(concat_ws('',P_PRE,M_NOM,M_PRE))>0,1,0)) as cfil," 
									."  sum(if(year(LADATE)>0,1,0)) as cnnul"
				." from ".$table
				." where COMMUNE='".sql_quote($lacom)."'" 
				." group by COMMUNE,DEPART,LIBELLE; ";

	optimize($request);

	//$listcomm .= ",'".sql_quote($comm['COMMUNE'])."'";

	$result = mysql_query($request);
	$reqdel = "delete from ".EA_DB."_sums where TYPACT = '".$xtyp."' and COMMUNE='".sql_quote($lacom)."'";
	$resdel = mysql_query($reqdel);

	while ($ligne = mysql_fetch_array($result))
		{
		$reqins = "insert into ".EA_DB."_sums (COMMUNE,DEPART,TYPACT,LIBELLE,DEPOSANT,DTDEPOT,AN_MIN,AN_MAX,NB_TOT,NB_N_NUL,NB_FIL,DER_MAJ) values ("
								 ."'".sql_quote($ligne['COMMUNE'])."', "
								 ."'".sql_quote($ligne['DEPART'])."', "
								 ."'".$xtyp."', "
								 ."'".sql_quote($ligne['LIBELLE'])."', "
								 .$ligne['DEPOSANT'].", "
								 ."'".sql_quote($ligne['ddepot'])."', "
								 .iif($ligne['dmin']=='',0,$ligne['dmin']).", "
								 .$ligne['dmax'].", "
								 .$ligne['ctot'].", "
								 .$ligne['cnnul'].", "
								 .$ligne['cfil'].", "
								 ."'".now()."'); ";
		//echo "<p>".$reqins;
		if ($ligne['dmax']==0) 
		  msg('Les dates de '.$ligne['COMMUNE'].' sont mal encodées');
		if  ($resins = mysql_query($reqins))
			{
			// ajout ok
			}
		else
			{
			echo "Insertion non réalisée";
			echo '<p>'.mysql_error().'<br />'.$reqins.'</p>'."\n";
			}
		}
	}

//----------------------------------------------------------------------------

function maj_stats($xtyp, $T0, $path, $mode, $com="", $dep="")

// mode : A = all, C=Commune unique, N=Next commune (qd All pas terminé)

	{
	if ($mode=="C")
	  $tpsreserve= min(3,ini_get("max_execution_time")/2);
	 else
	  $tpsreserve= min(10,ini_get("max_execution_time")/2);
	  
	$Max_time = ini_get("max_execution_time")-$tpsreserve;
	if (time()-$T0>$Max_time)
	  {
	  echo "<p>Les statistiques n'ont pas pu être recalculées immédiatement.<br>";
	  echo '<a href="'.$path."/maj_sums.php".'?xtyp='.$xtyp.'&mode='.$mode.'&com='.urlencode($com).'">'."Cliquez ici pour recalculer ces statistiques"."</a></p>";
	  }
	 else
		{
		switch ($xtyp)
			{
			case "N":
			$typ="Naissances/Baptêmes";
			$table = EA_DB."_nai3";
			break;
			case "V":
			$typ="Actes divers";
			$table = EA_DB."_div3";
			break;
			case "M":
			$typ="Mariages";
			$table = EA_DB."_mar3";
			break;
			case "D":
			$typ="Décès/Sépultures";
			$table = EA_DB."_dec3";
			break;
			}

		$Max_time = ini_get("max_execution_time")-2;
			
		if ($mode=="C" or $mode=="D")
		  {
			stats_1_comm($xtyp,$com);
			if ($mode=="C")
				geoloc_1_com($com,$dep);		  
		  }
		else
			{
			if ($mode=="A")
				{
				$reqdel = "delete from ".EA_DB."_sums where TYPACT = '".$xtyp."'";
				echo "<p>Suppression des statistiques existantes</p>";
				$resdel = mysql_query($reqdel);
				}
	  	$reqbase = "set sql_big_selects=1";
      $resbase = mysql_query($reqbase);
			$reqbase = "select distinct COMMUNE,DEPART from ".$table. " order by COMMUNE";
			$resbase = mysql_query($reqbase);
			optimize($reqbase);
			$listcomm = "";
			$timeisup= false;
			while ($comm = mysql_fetch_array($resbase) and !$timeisup)
				{	
				$lacom = $comm['COMMUNE'];
				$ledep = $comm['DEPART'];
				$listcomm .= ",'".sql_quote($lacom)."'";
				if ($mode=="A" or $lacom >= $com)
					{
					stats_1_comm($xtyp,$lacom);
					geoloc_1_com($lacom,$ledep);
					}
				$timeisup = (time()-$T0>=$Max_time);
				}
			}
		if ($mode=="A" or $mode=="N" )
			{
		  if ($timeisup)
		    {
				echo "<p><b>Mise à jour INCOMPLETE des statistiques des ".$typ." </b></p>";
				//echo "<p>Pour continuer le calcul des statistiques cliquez le lien suivant :<br>";
				echo '<a href="'.$path."/maj_sums.php".'?xtyp='.$xtyp.'&mode=N&com='.urlencode($lacom).'">'."Cliquez ici pour CONTINUER le recalcul de ces statistiques"."</a></p>";
		    }
		    else
				{
				echo "<p><b>Mise à jour globale des statistiques des ".$typ." terminée.</b></p>";
				}
			}
			else
			echo "<p><b>Les statistiques ont été recalculées.</b></p>";
			
		}
	}

//----------------------------------------------------------------------------

function geocode_google($com,$dep)
// Interroge google pour pour connaitre les coordonnées d'une commune
	{
	include_once("GoogleMap/GoogleMapV3.php");
	include_once("GoogleMap/Jsmin.php");

	global $carto;
	if (!isset($carto))
		{
		$carto = new GoogleMapAPI(); 
		$carto->_minify_js = isset($_REQUEST["min"])?FALSE:TRUE;
		}		
	$coord = $carto->geoGetCoords(remove_accent($com).", ".remove_accent($dep));
	if (!$coord)
		{
		$coord['lon']=0;
		$coord['lat']=0;
		}	
	return $coord;
	}
	 
//------------------------------------------------------------------------------

function geoloc_1_com($com,$dep)

	{
	$reqbase = "select STATUT from ".EA_DB."_geoloc where COMMUNE = '".sql_quote($com)."' and DEPART = '".sql_quote($dep)."'";
	$res = mysql_query($reqbase);
	if ($res and mysql_num_rows($res)!=0)
		{
		$ligne = mysql_fetch_array($res);
		if ($ligne['STATUT']=='N')
			$rech = 2; // pas trouvé la dernière fois 
			else
			$rech = 0; // présent
		}
		else
		$rech = 1; // absent
	if ($rech>=1) // il faut geocoder
		{
		//echo "<p>Recherche de ".$com."/".$dep;
		$coord = geocode_google($com,$dep);
		if ($coord['lon']==0 and $coord['lat']==0)
			$statut = 'N'; // Non trouvé
			else
			$statut = 'A'; // Automatique
		if ($rech==1) 		
			$reqmaj = "insert into ".EA_DB."_geoloc (COMMUNE,DEPART,LON,LAT,STATUT)" 
								." values ('".sql_quote($com)."','".sql_quote($dep)."',".$coord['lon'].",".$coord['lat'].",'".$statut."')";
			else
			$reqmaj = "update ".EA_DB."_geoloc set LON=".$coord['lon'].", LAT=".$coord['lat'].",STATUT='".$statut."'"
								." where COMMUNE = '".sql_quote($com)."' and DEPART = '".sql_quote($dep)."'";
		$result = mysql_query($reqmaj);
		//echo $reqmaj;
		if ($coord['lat']<>0)
			echo "<p>".$com." [".$dep."] a été géocodé en ".$coord['lon'].",".$coord['lat']."</p>";
			else
			echo "<p>".$com." [".$dep."] n'a pu être géocodé</p>";
		}
	}

//------------------------------------------------------------------------------

function test_geocodage($show=false)
	
	{
  $coord = geocode_google("Paris","France");
	$xx = $coord['lon'] + $coord['lat'];
	$gok = true;
  if (($xx > 51) and ($xx< 52))	
		$msg = "<p>Le géocodage fonctionne normalement</p>";
		else
		{
		$gok = false;
		$msg = "<p><b>Le géocodage NE fonctionne PAS normalement</b> : cela PEUT notamment provenir de l'hébergement qui empêche le recours aux web services !</b></p>";
		}
	if ($show)
	  echo $msg;
	return $gok;
	}

//------------------------------------------------------------------------------

function geoUrl($gid)
	{
	global $root;
	$imgtxt = "Carte";
	if ($gid>0 and GEO_LOCALITE>0)
	  $geourl = ' &nbsp; <a href="'.$root.'/localite.php?id='.$gid.'"><img src="'.$root.'/img/boussole.png" border="0" alt="('.$imgtxt.')" title="'.$imgtxt.'" align="middle"></a>';
	 else
    $geourl = '';	 
	return $geourl;
	}
//----------------------------------------------------------------------------

function geoNote($Commune,$Depart,$atyp)
	{
	global $gid;
	$georeq = "select ID,LON,LAT,NOTE_".$atyp." from ".EA_DB."_geoloc where COMMUNE = '".sql_quote($Commune)."' and  DEPART = '".sql_quote($Depart)."'";
	$geores =  mysql_query($georeq);
	if ($geo = mysql_fetch_array($geores))
		{
		$gid = $geo['ID'];
		$lon = $geo['LON'];
		$lat = $geo['LAT'];
		if ($lon == 0 and $lat==0)
		  $gid = 0; // indique de ne pas afficher la carte 
		$note = $geo['NOTE_'.$atyp];
		}
	return $note;
	}

/*** ctrlxid
* retourne le code de contrôle relatif au couple nom et prenom
*/
function ctrlxid($nom,$pre)
	{
	if (!empty($nom)) 
	  {
		$c1=(ord($nom[0])+3);
		}
		else 
		$c1=13;
	if (!empty($pre)) 
		$c2=(ord($pre[0])+7); 
		else 
		$c2=19;
	return $c1*$c2;
	}

//------------------------------------------------------------------------------

function get_last_backups()
	{
	// recupère la liste des dates des derniers backups
	$temp = explode(';',EA_LSTBACKUP);
	$resu = '';
	foreach($temp as $tp)
		$list_backups[mb_substr($tp,0,1)] = mb_substr($tp,2);
	return $list_backups;	
	}
//------------------------------------------------------------------------------

function set_last_backups($list_backups)
	{
	// enregistre la liste des dates des derniers backups
	$laliste="";
	foreach($list_backups as $btyp => $bdate)
		$laliste .= $btyp.":".$bdate.";";
	$request = "update ".EA_DB."_params set valeur = '".$laliste."' where param = 'EA_LSTBACKUP'";
	$result = mysql_query($request);
	return $result;	
	}

//------------------------------------------------------------------------------

function show_last_backup($filtre="NMDVUP")
	{
	$list_backups = get_last_backups();
	$resu = "";
	foreach($list_backups as $btyp => $bdate)
		{
		if (isin($filtre,$btyp)>=0)
		  $resu .= typact_txt($btyp)." : ".showdate($bdate).'<br />';
		}
	return $resu;	
	}

?>