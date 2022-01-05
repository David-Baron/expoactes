<?php
ob_start(); //Pour éviter de tout recevoir en un seul bloc
ob_implicit_flush(1);

if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

include("../tools/traitements.inc.php");
include("../tools/adodb-time.inc.php");


//------------------------------------------------------------------------------

function nomcolonne($i)  // noms des colonnes à la Excel
{
	if ($i <= 26)
		return chr(64 + $i);
	else {
		$un = floor(($i - 1) / 26) + 64;
		$de = fmod($i - 1, 26) + 65;
		return chr($un) . chr($de);
	}
}

//------------------------------------------------------------------------------

function listbox_cols($fieldname, $default)
{
	global $acte;
	$i = 1;
	$len = 15;
	echo '<select name="' . $fieldname . '" size="1">' . "\n";
	echo '<option ' . selected_option(0, $default) . '> -- </option>' . "\n";
	foreach ($acte as $zone) {
		$zone = trim($zone);
		if (strlen($zone) > $len - 2)
			$exemple = mb_substr($zone, 0, $len - 2) . "..";
		else
			$exemple = mb_substr($zone, 0, $len);

		echo '<option ' . selected_option($i, $default) . '>Col. ' . nomcolonne($i) . '-' . $i . ' (' . $exemple . ')</option>' . "\n";
		$i++;
	}
	echo " </select>\n";
}

//------------------------------------------------------------------------------

$root = "";
$path = "";
$userlogin = "";
//$T0 = time();
$Max_time = min(ini_get("max_execution_time") - 3, MAX_EXEC_TIME);

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

$userlogin = "";
$userlevel = logonok(6);
while ($userlevel < 6) {
	login($root);
}

$AnneeVide  = ischecked('AnneeVide');
$SuprRedon  = ischecked('SuprRedon');
$SuprPatVid = ischecked('SuprPatVid');
$logOk      = ischecked('LogOk');
$logKo      = ischecked('LogKo');
$logRed     = ischecked('LogRed');
$TypeActes  = getparam('TypeActes');
$commune    = html_entity_decode(getparam('Commune'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$depart     = html_entity_decode(getparam('Depart'), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
$communecsv = ischecked('CommuneCsv');
$departcsv  = ischecked('DepartCsv');
$typedoc    = getparam('typedoc');
$deposant   = getparam('deposant');
$Filtre    	= getparam('Filtre');
$Condition 	= getparam('Condition');
$Compare   	= getparam('Compare');
$photo   		= getparam('photo');
$trans   		= getparam('trans');
$verif   		= getparam('verif');
$photocsv   = ischecked('photocsv');
$transcsv   = ischecked('transcsv');
$verifcsv   = ischecked('verifcsv');
$isUTF8     = false;

if (isset($_REQUEST['submitD']))  // définir les mapping
	$submit = "D";
elseif (isset($_REQUEST['submitC']))  // charger
	$submit = "C";
elseif (isset($_REQUEST['submitR']))  // remise à zéro
	$submit = "R";
elseif (isset($_REQUEST['submitV']))  // Voir exemple
	$submit = "V";

if ($TypeActes == "") $TypeActes = 'N';

$mdb = load_zlabels($TypeActes, $lg);

if (isset($_REQUEST['action'])) {
	setcookie("chargeCSV", $AnneeVide . $SuprRedon . $SuprPatVid . $logOk . $logKo . $logRed, time() + 60 * 60 * 24 * 365);  // 1 an
	if (isset($_REQUEST['Zone1'])) {
		$i = 0;
		$nameI = "ZID" . $i;
		$nameZ = "Zone" . $i;
		$nameT = "Trait" . $i;
		$cookie = "";
		while (isset($_REQUEST[$nameI])) {
			$cookie .= $_REQUEST[$nameI] . '-' . $_REQUEST[$nameZ] . '-' . $_REQUEST[$nameT] . '+';
			$i++;
			$nameI = "ZID" . $i;
			$nameZ = "Zone" . $i;
			$nameT = "Trait" . $i;
		}
		if ($submit == 'R') // Remise à blanc
			$cookie = "               ";
		setcookie("charge" . getparam('TypeActes'), $cookie, time() + 60 * 60 * 24 * 365);
	}
	switch ($TypeActes) {
		case "N":
			$ntype = "Naissance";
			$table = EA_DB . "_nai3";
			$annee = 8;
			$script = 'tab_naiss.php';
			break;
		case "M":
			$ntype = "Mariage";
			$table = EA_DB . "_mar3";
			$annee = 6;
			$script = 'tab_mari.php';
			break;
		case "D":
			$ntype = "Décès";
			$table = EA_DB . "_dec3";
			$annee = 7;
			$script = 'tab_deces.php';
			break;
		case "V":
			$ntype = "Divers";
			$table = EA_DB . "_div3";
			$annee = 0;
			$script = 'tab_bans.php';
			break;
	}
}

open_page("Chargement des actes (CSV)", $root);
navadmin($root, "Chargement des actes CSV");

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
$emailfound = false;
$oktype = false;
$cptign = 0;
$cptadd = 0;
$cptdeja = 0;
$cptfiltre = 0;
$avecidnim = false;

$today = today();
$userid = current_user("ID");
$missingargs = true;


if (isset($_REQUEST['action'])) {
	// Données postées
	$missingargs = false;
	if ($submit == 'D')  // upload du fichier CSV
	{
		if (empty($_FILES['Actes']['tmp_name'])) {
			msg('Pas trouvé le fichier spécifié.');
			$missingargs = true;
		}
		if (!is_uploaded_file($_FILES['Actes']['tmp_name'])) {
			msgplus('Méthode de téléchargement non valide.');
			writelog('Possible tentative de téléchargement CSV frauduleux');
		}
		if (strtolower(mb_substr($_FILES['Actes']['name'], -4)) <> ".csv") //Vérifie que l'extension est bien '.CSV'
		{
			msg('Les fichier doit être de type .CSV.');
			$missingargs = true;
		}
	}
	if ($submit <> 'D') {
		if (empty($_REQUEST['fileuploaded'])) {
			msg('Pas de fichier spécifié.');
			$missingargs = true;
		}
	}
	if (empty($TypeActes)) {
		msg('Vous devez préciser le type des actes.');
		$missingargs = true;
	}
	if (empty($commune) and !$communecsv) {
		msg('Vous devez préciser le nom de la commune ou de la paroisse ou indiquer qu\'il sera lu dans le fichier.');
		$missingargs = true;
	}
	if (empty($depart) and !$departcsv) {
		msg('Vous devez préciser le nom du département ou de la province ou indiquer qu\'il sera lu dans le fichier.');
		$missingargs = true;
	}
}

$zonelibelle = "ZoneX";
if ($TypeActes == 'V') {
	for ($i = 1; $i < 9; $i++)  // tjrs entre 1 et 9
	{
		if (array_key_exists('ZID' . $i, $_REQUEST) and $_REQUEST['ZID' . $i] == '4012')  // LIBELLE de Divers
			$zonelibelle = "Zone" . $i;
	}
	//echo "<P>ZONE : ".	$zonelibelle;
}
$meserrdivers1 = "Vous devez préciser le type de document dont il s'agit soit globalement, soit par chargement d'une zone";
$meserrdivers2 = "Vous ne pouvez pas spécifier simultanément un type de document global et une zone fournissant le type de document";

if (!$missingargs) { // fichier d'actes
	//	if ($TypeFich == "L")
	//		{ // Format LIBRE
	$resume = false;
	if (isset($_REQUEST['passlign']))
		$passlign = getparam('passlign');
	else
		$passlign = 1;

	if ($submit == 'D')  // upload du fichier CSV
	// Stockage du fichier chargé
	{
		$uploadfile = UPLOAD_DIR . '/' . $userlogin . '.csv';
		if (!move_uploaded_file($_FILES['Actes']['tmp_name'], $uploadfile)) {
			msg('033 : Impossible de ranger le fichier dans "' . UPLOAD_DIR . '".');
			$missingargs = true;
		}
	}

	if ($submit == 'C') {
		// chargement effectif 
		$missingargs = false;
		$csv = file(getparam('fileuploaded'));
		if ($TypeActes == 'V' and $typedoc == "" and empty($_REQUEST[$zonelibelle])) {
			msg($meserrdivers1);
			$resume = true;
		} elseif ($TypeActes == 'V' and $typedoc <> "" and !empty($_REQUEST[$zonelibelle])) {
			msg($meserrdivers2);
			$resume = true;
		} else { // all ok
			foreach ($csv as $line_num => $line) { // par ligne
				if ($line_num >= getparam('passlign') and (time() - $T0 < $Max_time)) { // ligne à traiter	
					$curr_line = $line_num;
					$listzone[0] = "";
					$listzone[1] = "";
					$listzone[2] = "";
					$listdata[0] = "";
					$listdata[1] = "";
					$listdata[2] = "";
					// -- découpage et construction des données
					if (!$isUTF8)
						$line = ea_utf8_encode($line); // ADLC 24/09/2015
					$acte = explode_csv($line);
					if (!empty($Filtre) and !comparer($acte[$Filtre - 1], $Compare, $Condition)) {
						$cptfiltre++;
					} else { // Ok Filtre
						$i = 0;
						foreach ($mdb as $zone) {
							if (array_key_exists('ZID' . $i, $_REQUEST) and $zone['ZID'] == $_REQUEST['ZID' . $i]) { // traiter ce champ
								if ($_REQUEST['Zone' . $i] > 0)  // la zone à été notée à charger
								{
									$listzone[$zone['BLOC']] .= $zone['ZONE'] . ",";  // liste des champs
									if ($_REQUEST['Trait' . $i] == "0")
										$info = trim($acte[$_REQUEST['Zone' . $i] - 1]);
									else
										$info = traitement($_REQUEST['Zone' . $i] - 1, $zone['TYP'], $_REQUEST['Trait' . $i]);
									$data[$zone['ZONE']] = $info;
									if (($zone['ZONE'] == "NOM" or ($zone['ZONE'] == "C_NOM" and $TypeActes == 'M')) and $info == "") {
										$info = "N";
									}
									$listdata[$zone['BLOC']] .= "'" . sql_quote($info) . "',"; 	// Bloc 0 =communs, 1= 1er intervenant, 2 = 2d interv.

								}
								$i++; // suivant 
							}
						}

						/*
							echo '<p>'.$line;
							echo '<p>L1='.$listdata[1];
							echo '<p>L2='.$listdata[2];
							echo '<p>L0='.$listdata[0];
							*/
						//echo '<pre>'; print_r($data); echo '</pre>';

						// -- vérifications	
						$dateincomplete = false; // pour vérification dans ajuste_date et détection des doublons

						$ladate = "";
						$MauvaiseAnnee = 0;
						ajuste_date($data["DATETXT"], $ladate, $MauvaiseAnnee);  // creée ladate en sql
						$datetxt = trim($data["DATETXT"]);
						if ($communecsv)
							$commune = $data["COMMUNE"];
						if ($departcsv)
							$depart = $data["DEPART"];
						$nom     = trim(mb_substr($data["NOM"], 0, metadata("TAILLE", "NOM")));
						if (array_key_exists('PRE', $data))
							$pre     = trim(mb_substr($data["PRE"], 0, metadata("TAILLE", "PRE")));
						else
							$pre = "";
						$log = '<br />' . $ntype . ' ' . $nom . ' ' . $pre;
						$cnom = "";
						if ($TypeActes == "M") {
							$cnom = trim(mb_substr($data["C_NOM"], 0, metadata("TAILLE", "C_NOM")));
							$cpre = trim(mb_substr($data["C_PRE"], 0, metadata("TAILLE", "C_PRE")));
							$log .= ' X ' . $cnom . ' ' . $cpre;
						}
						if ($TypeActes == "V") {
							if (isset($data["C_NOM"]))
								$cnom = trim(mb_substr($data["C_NOM"], 0, metadata("TAILLE", "C_NOM")));
							else
								$cnom = "";
							if (isset($data["C_PRE"]))
								$cpre = trim(mb_substr($data["C_PRE"], 0, metadata("TAILLE", "C_PRE")));
							else
								$cpre = "";
							if (!empty($cnom) or !empty($cpre))
								$log .= ' & ' . $cnom . ' ' . $cpre;
						}
						$log .= ' le ' . $datetxt . ' à ' . $commune . " : ";
						if (($SuprPatVid == 1) and ($nom == ""))
						// pas de patronyme (ni de "N")
						{
							$cptign++;
							if ($logKo == 1) echo $log . " INCOMPLET (" . $curr_line . ") -> Ignoré";
						} elseif (($SuprPatVid == 1) and ($cnom == "") and ($TypeActes == "M"))
						// pas de patronyme d'épouse (ni de "N")
						{
							$cptign++;
							if ($logKo == 1) echo $log . " INCOMPLET (" . $curr_line . ") -> Ignoré";
						} elseif (trim($data["DATETXT"]) == "00/00/0000")
						// acte avec date vide 
						{
							$cptign++;
							if ($logKo == 1) echo $log . "DATE MANQUANTE (" . $curr_line . ") -> Ignoré";
						} elseif (($AnneeVide == 1) and ($MauvaiseAnnee == 1))
						// acte avec année incomplète (testée dans ajuste_date)
						{
							$cptign++;
							if ($logKo == 1) echo $log . "ANNEE INVALIDE (" . $curr_line . ") -> Ignoré";
						} else {  // complet
							if ($nom == "") $nom = "N";
							//if ($cnom=="") $cnom = "N";
							if ($TypeActes == "M" and $SuprRedon == 1) {  // inversion éventuelle des mariages
								$inversion = false;
								// Recherche si épouse en 1er
								$prem_pre = explode(' ', $pre, 2);
								$sql = "select * from " . EA_DB . "_prenom where prenom = '" . sql_quote($prem_pre[0]) . "'";
								$res = EA_sql_query($sql);
								$nb = EA_sql_num_rows($res);
								if ($nb > 0) {
									// vérifier que cpre n'est pas feminin
									$prem_pre = explode(' ', $cpre, 2);
									$sql = "select * from " . EA_DB . "_prenom where prenom = '" . sql_quote($prem_pre[0]) . "'";
									$res = EA_sql_query($sql);
									$nb = EA_sql_num_rows($res);
									if ($nb == 0) {
										$inversion = true;
										$log .= ' ** Permuté ** ';
										permuter($listdata[1], $listdata[2]);
									}
								}
								// recherche doublon inversé	
								$condit = "DATETXT='" . $datetxt . "' and NOM='" . sql_quote($cnom) . "' and PRE='" . sql_quote($cpre) . "'";
								$condit .= " and C_NOM='" . sql_quote($nom) . "' and C_PRE='" . sql_quote($pre) . "'";
								if ($TypeActes == 'V')
									$condit .= " and LIBELLE='" . sql_quote($typedoc) . "'";
								$request = "select ID from " . $table .
									" where COMMUNE='" . sql_quote($commune) . "' and DEPART='" . sql_quote($depart) . "' and " . $condit . ";";

								//echo '<p>'.$request;
								$result = EA_sql_query($request);
								$nbx = EA_sql_num_rows($result);
								if ($nbx > 0) {
									//echo ' ** INVERSE DETECTE **';
								}
							} else {
								$nbx = 0;
							}
							if ($SuprRedon == 1) {
								// Détection si déjà présent 								
								$condit = "DATETXT='" . $datetxt . "' and NOM='" . sql_quote($nom) . "' and PRE='" . sql_quote($pre) . "'";
								if (($TypeActes == "M") or ($TypeActes == 'V' and !empty($cnom)))
									$condit .= " and C_NOM='" . sql_quote($cnom) . "'";
								if (($TypeActes == "M") or ($TypeActes == 'V' and !empty($cpre)))
									$condit .= " and C_PRE='" . sql_quote($cpre) . "'";
								if ($TypeActes == 'V')
									$condit .= " and LIBELLE='" . sql_quote($typedoc) . "'";
								$request = "select ID from " . $table .
									" where COMMUNE='" . sql_quote($commune) . "' and DEPART='" . sql_quote($depart) . "' and " . $condit . ";";
								$result = EA_sql_query($request);
								//echo '<p>'.$request;
								$nb = EA_sql_num_rows($result);
							} else
								$nb = 0;
							if ($TypeActes == 'V' and !empty($typedoc))  // ajout du type d'acte divers global 
							{
								$listzone[0] .= "LIBELLE,";
								$listdata[0] .= "'" . sql_quote($typedoc) . "',";
							}

							if (!$communecsv) {
								$listzone[0] .= "COMMUNE,";
								$listdata[0] .= "'" . sql_quote($commune) . "',";
							}
							if (!$departcsv) {
								$listzone[0] .= "DEPART,";
								$listdata[0] .= "'" . sql_quote($depart) . "',";
							}
							if (!$photocsv) {
								$listzone[0] .= "PHOTOGRA,";
								$listdata[0] .= "'" . sql_quote($photo) . "',";
							}
							if (!$transcsv) {
								$listzone[0] .= "RELEVEUR,";
								$listdata[0] .= "'" . sql_quote($trans) . "',";
							}
							if (!$verifcsv) {
								$listzone[0] .= "VERIFIEU,";
								$listdata[0] .= "'" . sql_quote($verif) . "',";
							}

							if (($SuprRedon == 1) and ($nb + $nbx) > 0 and !($dateincomplete)) {
								$reqmaj = "";
							} else { // ADD
								$action = "AJOUT";
								$reqmaj = "insert into " . $table
									. " (BIDON,TYPACT," . $listzone[1] . $listzone[2] . $listzone[0]
									. "LADATE,DEPOSANT,DTDEPOT,DTMODIF)"
									. " values('CSV','" . $TypeActes . "'," . $listdata[1] . $listdata[2] . $listdata[0]
									. "'" . $ladate . "'," . $deposant . ",'" . $today . "','" . $today . "');";
							} // ADD
							//echo "<p>".$reqmaj;
							if ($reqmaj <> '') {
								if ($result = EA_sql_query($reqmaj)) {
									if ($logOk == 1) echo $log . $action . ' -> Ok.';
									$cptadd++;
								} else {
									echo ' -> Erreur : ';
									echo '<p>' . EA_sql_error() . '<br />' . $reqmaj . '</p>';
								}
							} else {
								if ($logRed == 1) echo $log . 'Déjà présent (' . $curr_line . ') ';
								$cptdeja++;
							}
						} // Ok Filtre	
					}  // complet
				} // ligne à traiter
			}	// par ligne		
			if ($curr_line < count($csv) - 1)  // Si interruption 
			{
				msg("Temps maximum d'exécution écoulé");
				echo '<p>' . ($curr_line + 1) . ' lignes déjà traitées;</p>';
				echo '<p>Il reste ' . (count($csv) - $curr_line) . ' lignes à traiter.</p>';
				$resume = true;
				$passlign = $curr_line + 1;
			}
		} // all ok	
	} // chargement effectif

	if (isin("DVAR", $submit) >= 0 or $resume) // liste des champs (+fiche exemple?)
	{
		if ($resume)
			echo '<h2>Poursuite du chargement</h2>';
		else
			echo '<h2>Lecture des affectations de zones</h2>';
		if (isset($_REQUEST['fileuploaded']))
			$uploadfile = getparam('fileuploaded');

		$csv = file($uploadfile);
		$line = $csv[0];
		if (!$isUTF8)
			$line = ea_utf8_encode($line); // ADLC 24/09/2015
		$acte = explode_csv($line);

		$cookname = "charge" . getparam('TypeActes');
		if (isset($_COOKIE[$cookname]) and strlen($_COOKIE[$cookname]) > 20)
			$cookparam = $_COOKIE[$cookname];
		else
			$cookparam = str_repeat("0-0-0+", 2);
		$presetslist = explode("+", $cookparam);
		$presets = array();
		$p = 0;
		foreach ($presetslist as $une) {
			if (isin($une, "-") >= 0) {
				$elem = explode("-", $une);
				$presets[$elem[0]][0] = $elem[1];  // source
				$presets[$elem[0]][1] = $elem[2];  // traitement
			}
		}

		//{ print '<pre>';  echo $cookname." : ".$_COOKIE[$cookname].'<br>'; print_r($presets); echo '</pre>'; }


		echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
		echo '<input type="hidden" name="fileuploaded" value="' . $uploadfile . '" />' . "\n";
		echo '<input type="hidden" name="TypeActes" value="' . $TypeActes . '" />' . "\n";
		echo '<input type="hidden" name="Commune" value="' . htmlentities($commune, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />' . "\n";
		echo '<input type="hidden" name="CommuneCsv" value="' . $communecsv . '" />';
		echo '<input type="hidden" name="Depart" value="' . htmlentities($depart, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />' . "\n";
		echo '<input type="hidden" name="DepartCsv" value="' . $departcsv . '" />';
		echo '<input type="hidden" name="deposant" value="' . $deposant . '" />' . "\n";
		echo '<input type="hidden" name="AnneeVide" value="' . ischecked('AnneeVide') . '" />' . "\n";
		echo '<input type="hidden" name="SuprRedon" value="' . ischecked('SuprRedon') . '" />' . "\n";
		echo '<input type="hidden" name="SuprPatVid" value="' . ischecked('SuprPatVid') . '" />' . "\n";
		echo '<input type="hidden" name="LogOk"  value="' . ischecked('LogOk') . '" />' . "\n";
		echo '<input type="hidden" name="LogKo"  value="' . ischecked('LogKo') . '" />' . "\n";
		echo '<input type="hidden" name="LogRed" value="' . ischecked('LogRed') . '" />' . "\n";

		echo '<table cellspacing="2" cellpadding="0" border="0" align="center" summary="Formulaire">' . "\n";
		echo '<tr>';
		echo '<td align="right"><strong>Type d\'actes :</strong></td>';
		echo '<td colspan="2">' . $ntype . '</td>';
		echo '</tr>';
		if (!$communecsv) {
			echo '<tr>';
			echo '<td align="right"><strong>Origine :</strong></td>';
			echo '<td colspan="2">' . $commune . ' [' . $depart . ']' . '</td>';
			echo '</tr>';
		}
		if (isset($_FILES['Actes']['name'])) {
			echo '<tr>';
			echo '<td align="right"><strong>Fichier téléchargé :</strong></td>';
			echo '<td colspan="2">' . $_FILES['Actes']['name'] . '</td>';
			echo '</tr>';
		}
		echo '<tr>';
		echo '<td align="right"><strong>Lignes à passer :</strong></td>';
		echo '<td colspan="2"><input type="text" name="passlign" value="' . $passlign . '" size="5" /> (au chargement effectif)</td>';
		echo '</tr>';
		if ($TypeActes == "V")
			if ($submit <> 'C') {
				echo '<tr>';
				echo '<td align="right"><strong>Type de document :</strong></td>';
				echo '<td colspan="2"><input type="text" name="typedoc" value="' . $typedoc . '" size="30" /></td>';
				echo '</tr>';
			} else
				echo '<input type="hidden" name="typedoc" value="' . $typedoc . '" />';

		if ($submit <> 'C') {
			echo '<tr><td colspan="3">&nbsp;</td></tr>';
			echo '<tr class="rowheader">';
			echo '<th>Votre fichier</th>';
			echo '<th>Destination</th>';
			echo '<th>Traitement</th>';
			echo '</tr>';
		}
		$i = 0;
		//{ print '<pre>';  print_r($mdb); echo '</pre>'; }
		foreach ($mdb as $zone) {
			/*
				[0] => Array
						(
								[ZID] => 1004	
								[ZONE] => CODCOM
								[GROUPE] => A1
								[TAILLE] => 12
								[OBLIG] => N
								[ETIQ] => Code INSEE
								[TYP] => TXT
								[AFFICH] => A
								[GETIQ] => Acte de naissance
        )
				*/
			if (($zone['ZONE'] == 'CODCOM'   and !($communecsv and isin('OFA', metadata('AFFICH', 'CODCOM')) >= 0))
				or ($zone['ZONE'] == 'COMMUNE'  and !($communecsv))
				or ($zone['ZONE'] == 'CODDEP'   and !($departcsv and isin('OFA', metadata('AFFICH', 'CODDEP')) >= 0))
				or ($zone['ZONE'] == 'DEPART'   and !($departcsv))
				or ($zone['ZONE'] == 'PHOTOGRA' and !($photocsv))
				or ($zone['ZONE'] == 'RELEVEUR' and !($transcsv))
				or ($zone['ZONE'] == 'VERIFIEU' and !($verifcsv))
				or ($zone['ZONE'] == 'DEPOSANT')
			) {
				// ne rien lire car déjà lu ou automatique
				// echo "<p>Code ".$zone['ZONE']."déja vu ";
			} else {

				if (isset($_REQUEST['Zone' . $i]))
					$lechamp = $_REQUEST['Zone' . $i];
				else
						if (isset($presets[$zone['ZID']][0]))
					$lechamp = $presets[$zone['ZID']][0];
				else
					$lechamp = 0;

				if (isset($_REQUEST['Trait' . $i]))
					$letrait = $_REQUEST['Trait' . $i];
				else
						if (isset($presets[$zone['ZID']][1]))
					$letrait = $presets[$zone['ZID']][1];
				else
					$letrait = 0;

				if ($submit == 'R') // Remise à blanc
				{
					$lechamp = 0;
					$letrait = 0;
				}

				echo '<input type="hidden" name="ZID' . $i . '" value="' . $zone['ZID'] . '" />';
				if ($submit == 'C') {
					// Chargement : on n'affiche plus les mapping de zones
					echo '<input type="hidden" name="Zone' . $i . '" value="' . $_REQUEST['Zone' . $i] . '" />';
					echo '<input type="hidden" name="Trait' . $i . '" value="' . $_REQUEST['Trait' . $i] . '" />' . "\n";
				} else {
					// Affichage ud mapping des zones et traitements
					echo '<tr class="row' . (fmod($i, 2)) . '">';
					echo '<td>';
					listbox_cols('Zone' . $i, $lechamp);
					echo '</td>';
					echo '<td>--> ' . $zone['GETIQ'] . ' : ' . $zone['ETIQ'] . '</td>';
					echo '<td>';
					listbox_trait('Trait' . $i, $zone['TYP'], $letrait);
					echo '</td>';
					echo '</tr>' . "\n";
				}
				$i++;
			}
		}
		// Masque du Filtre
		if ($submit == 'C') {
			echo '<input type="hidden" name="Filtre" value="' . $Filtre . '" />';
			echo '<input type="hidden" name="Condition" value="' . $Condition . '" />';
			echo '<input type="hidden" name="Compare" value="' . $Compare . '" />' . "\n";
		} else {
			echo '<tr><td colspan="3"><br /><strong>Filtre éventuel sur le fichier CSV</strong></td></tr>';
			echo '<tr class="row0">';
			echo '<td>';
			listbox_cols('Filtre', $Filtre);
			echo '</td>';
			echo '<td align="center">';
			listbox_trait('Condition', "TST", $Condition);
			echo '</td>';
			echo '<td><input type="text" name="Compare" value="' . $Compare . '" size="20" />';
			echo '</td>';
			echo '</tr>' . "\n";
		}
		if ($submit == 'V')  // Voir un exemple
		{
			if (isset($_REQUEST['nofiche'])) {
				$nofiche = getparam('nofiche') + 1;
			} else
				$nofiche = 1;
			$line = $csv[$nofiche];
			if (!$isUTF8)
				$line = ea_utf8_encode($line); // ADLC 24/09/2015
			$acte = explode_csv($line);
			if (!empty($Filtre))
				while ((!comparer($acte[$Filtre - 1], $Compare, $Condition)) and ($nofiche < count($csv))) {
					// echo "Passer ".$nofiche;
					$nofiche++;
					$acte = explode_csv($csv[$nofiche]);
				}
			// Affichage de la fiche exemple
			echo '<tr><td colspan="3">&nbsp;<input type="hidden" name="nofiche" value="' . $nofiche . '" /></td></tr>';
			echo '<tr><td colspan="3">';
			echo '<table cellspacing="2" cellpadding="0" border="0" width="80%" summary="Fiche exemple">';
			echo '<tr class="rowheader">';
			echo '<th>Fiche exemple</th>';
			echo '<th>Données de la ligne ' . $nofiche . '</th>';
			echo '</tr>';

			if ($TypeActes == 'V' and $typedoc == "" and empty($_REQUEST[$zonelibelle])) {
				msg($meserrdivers1);
			}
			if ($TypeActes == 'V' and $typedoc <> "" and !empty($_REQUEST[$zonelibelle])) {
				msg($meserrdivers2);
			}

			$i = 0;
			$j = 0;
			$data = array();
			foreach ($mdb as $zone) {
				// extraction des données à afficher pour l'exemple en suivant les consignes du modèle de chargement
				if (array_key_exists('ZID' . $i, $_REQUEST) and $zone['ZID'] == $_REQUEST['ZID' . $i]) { // traiter ce champ
					//echo "<p> vu ".$i."->".$zone['ZID']." ".$zone['ZONE'];
					if ($zone['OBLIG'] == 'Y' and $_REQUEST['Zone' . $i] == 0) { // zone obligatoire
						if (!($TypeActes == 'V' and $typedoc <> ""))  // cas particulier pour le libelle des actes divers : ne plus vérifier, déjà fait + haut
							msg('Vous devez affecter un contenu à (' . $zone['GETIQ'] . ' : ' . $zone['ETIQ'] . ')');
					}
					if ($_REQUEST['Zone' . $i] > 0)  // la zone à été notée à charger
					{
						$j++;
						//echo "==>".trim($acte[$_REQUEST['Zone'.$i]-1]);
						echo '<tr class="row' . (fmod($j, 2)) . '">';
						echo '<td> &nbsp;' . $zone['GETIQ'] . ' : ' . $zone['ETIQ'] . '</td>';
						if ($_REQUEST['Trait' . $i] == "0")
							$info = trim($acte[$_REQUEST['Zone' . $i] - 1]);
						else
							$info = traitement($_REQUEST['Zone' . $i] - 1, $zone['TYP'], $_REQUEST['Trait' . $i]);
						$data[$zone['ZONE']] = $info;
						echo '<td> &nbsp;' . $info . '</td>';
						echo '</tr>';
					}
					$i++; // suivant 
				}
				//else
				//echo "<p>pas vu ".$i."->".$zone['ZID']." ".$zone['ZONE'];
			}
			//{ print '<pre>';  print_r($data); echo '</pre>'; }
			$j++;
			echo '<tr class="row' . (fmod($j, 2)) . '">';
			echo '<td> &nbsp;Décodage de la date</td>';
			$info = "";
			$MauvaiseDate = 0;
			$info = ajuste_date($data["DATETXT"], $info, $MauvaiseDate);  // info est docn remise en forme
			if ($MauvaiseDate > 0) $info .= " NON VALIDE !";
			echo '<td> &nbsp;' . $info . '</td>';
			echo '</tr>';

			echo '</table>';
			echo '</td></tr>';
		}
		echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
		echo '  <input type="hidden" name="photo" value="' . htmlentities($photo, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />';
		echo '  <input type="hidden" name="trans" value="' . htmlentities($trans, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />';
		echo '  <input type="hidden" name="verif" value="' . htmlentities($verif, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET) . '" />';
		echo '  <input type="hidden" name="photocsv" value="' . $photocsv . '" />';
		echo '  <input type="hidden" name="transcsv" value="' . $transcsv . '" />';
		echo '  <input type="hidden" name="verifcsv" value="' . $verifcsv . '" />';
		echo '  <input type="hidden" name="action" value="phase_2" />';

		if ($submit == 'C') {
			echo '  <input type="submit" name="submitC" value=" Relancer le chargement " />' . "\n";
		} else {
			echo '  <input type="submit" name="submitR" value=" Remise à blanc " />' . "\n";
			echo '  <input type="submit" name="submitV" value=" VOIR un exemple " />' . "\n";
			echo '  <input type="submit" name="submitC" value=" CHARGER maintenant " />' . "\n";
		}
		echo " </td></tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	} // 1er chargement

} // fichier d'actes

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	if (getparam('action') == '')  // parametres par défaut
	{
		if (isset($_COOKIE['chargeCSV']))
			$chargeCSV  = $_COOKIE['chargeCSV'] . str_repeat(" ", 10);
		else
			$chargeCSV  = "000111";
		$AnneeVide  = $chargeCSV[0];
		$SuprRedon  = $chargeCSV[1];
		$SuprPatVid = $chargeCSV[2];
		$logOk      = $chargeCSV[3];
		$logKo      = $chargeCSV[4];
		$logRed     = $chargeCSV[5];
	}

	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">Chargement de données CSV</h2>';
	echo '<table cellspacing="2" cellpadding="0" border="0" align="center" summary="Formulaire">' . "\n";
	echo " <tr>\n";
	echo '  <td align="right">Type des actes : </td>' . "\n";
	echo '  <td>';
	echo '        <input type="radio" name="TypeActes" value="N" />Naissances<br />';
	echo '        <input type="radio" name="TypeActes" value="M" />Mariages<br />';
	echo '        <input type="radio" name="TypeActes" value="D" />Décès<br />';
	echo '        <input type="radio" name="TypeActes" value="V" />Actes divers <br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Fichier CSV : </td>' . "\n";
	echo '  <td><input type="file" size="62" name="Actes" />' . "</td>\n";
	echo " </tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">' . metadata('ETIQ', 'COMMUNE') . ' : </td>' . "\n";
	echo '  <td><input type="text" size="40" name="Commune" value="' . $commune . '" />';
	echo '   ou <input type="checkbox" name="CommuneCsv" value="1" ' . checked($communecsv) . '/> Lu dans le CSV ';
	echo "  </td>\n";
	echo " </tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">' . metadata('ETIQ', 'DEPART') . ' : </td>' . "\n";
	echo '  <td><input type="text" size="40" name="Depart" value="' . $depart . '" />';
	echo '   ou <input type="checkbox" name="DepartCsv" value="1" ' . checked($departcsv) . '/> Lu dans le CSV ';
	echo "  </td>\n";
	echo " </tr>\n";

	if (isin('OFA', metadata('AFFICH', 'PHOTOGRA')) >= 0) {
		echo " <tr>\n";
		echo '  <td align="right">' . metadata('ETIQ', 'PHOTOGRA') . ' : </td>' . "\n";
		echo '  <td><input type="text" size="40" name="photo" value="' . $photo . '" />';
		echo '   ou <input type="checkbox" name="photocsv" value="1" ' . checked($photocsv) . '/> Lu dans le CSV ';
		echo "  </td>\n";
		echo " </tr>\n";
		echo " <tr>\n";
	}
	if (isin('OFA', metadata('AFFICH', 'RELEVEUR')) >= 0) {
		echo " <tr>\n";
		echo '  <td align="right">' . metadata('ETIQ', 'RELEVEUR') . ' : </td>' . "\n";
		echo '  <td><input type="text" size="40" name="trans" value="' . $trans . '" />';
		echo '   ou <input type="checkbox" name="transcsv" value="1" ' . checked($transcsv) . '/> Lu dans le CSV ';
		echo "  </td>\n";
		echo " </tr>\n";
		echo " <tr>\n";
	}
	if (isin('OFA', metadata('AFFICH', 'VERIFIEU')) >= 0) {
		echo " <tr>\n";
		echo '  <td align="right">' . metadata('ETIQ', 'VERIFIEU') . ' : </td>' . "\n";
		echo '  <td><input type="text" size="40" name="verif" value="' . $verif . '" />';
		echo '   ou <input type="checkbox" name="verifcsv" value="1" ' . checked($verifcsv) . '/> Lu dans le CSV ';
		echo "  </td>\n";
		echo " </tr>\n";
		echo " <tr>\n";
	}
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Filtrage des données : </td>' . "\n";
	echo '  <td>';
	echo '        <input type="checkbox" name="AnneeVide" value="1"' . checked($AnneeVide) . ' />Eliminer les actes dont l\'année est incomplète (ex. 17??)<br />';
	echo '        <input type="checkbox" name="SuprRedon" value="1"' . checked($SuprRedon) . ' />Eliminer les actes ayant mêmes noms et prénoms<br />';
	echo '        <input type="checkbox" name="SuprPatVid" value="1"' . checked($SuprPatVid) . ' />Eliminer les actes dont le patronyme est vide<br />';
	echo '  </td>';
	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Contrôle des résultats : </td>' . "\n";
	echo '  <td>';
	echo '    <input type="checkbox" name="LogOk" value="1"' . checked($logOk) . ' />Actes chargés &nbsp; ';
	echo '    <input type="checkbox" name="LogKo" value="1"' . checked($logKo) . ' />Actes erronés &nbsp; ';
	echo '    <input type="checkbox" name="LogRed" value="1"' . checked($logRed) . ' />Actes redondants<br />';
	echo '  </td>';
	echo " </tr>\n";

	if ($userlevel >= 8) {
		echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo '  <td align="right">Déposant : </td>' . "\n";
		echo '  <td>';
		listbox_users("deposant", $userid, DEPOSANT_LEVEL);
		echo '  </td>';
		echo " </tr>\n";
	} else
		echo '  <input type="hidden" name="deposant" value="' . $userid . '" />';
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br />";
	echo '  <input type="hidden" name="action" value="phase_1" />';
	echo '  <a href="aide/chargecsv.html" target="_blank">Aide</a>&nbsp;';
	echo '  <input type="reset" value="Effacer" />' . "\n";
	echo '  <input type="submit" name="submitD" value=" >> CHARGER >> " />' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	if (isset($nom)) {
		echo '<hr /><p>';
		if ($cptadd > 0) {
			echo 'Actes ajoutés  : ' . $cptadd;
			writelog('Ajout CSV ' . $ntype, $commune, $cptadd);
		}
		if ($cptfiltre > 0)  echo '<br />Actes filtré  : ' . $cptfiltre;
		if ($cptign > 0)  echo '<br />Actes ignorés  : ' . $cptign;
		if ($cptdeja > 0)  echo '<br />Actes redondants  : ' . $cptdeja;
		echo '<br />Durée du traitement  : ' . (time() - $T0) . ' sec.';
		echo '</p>';
		if (!$resume)  // fini
		{
			echo '<p>Voir la liste des actes de ';
			echo '<a href="' . mkurl($script, stripslashes($commune . ' [' . $depart . ']')) . '"><b>' . stripslashes($commune) . '</b></a>';
			echo '</p>';
			if ($communecsv)
				maj_stats($TypeActes, $T0, $path, "A");
			else
				maj_stats($TypeActes, $T0, $path, "C", $commune, $depart);
		}
	}
}
echo '</div>';

close_page(1, $root);
