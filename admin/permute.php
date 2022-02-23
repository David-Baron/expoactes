<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
	$EA_Appel_dOu = '';
} else {
	$EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');
my_ob_start_affichage_continu();

$root = "";
$path = "";

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

$userlogin = "";
$needlevel = 6;  // niveau d'accès (anciennement 5)
$userlevel = logonok($needlevel);
while ($userlevel < $needlevel) {
	login($root);
}

open_page("Permutation d'un acte", $root);
navadmin($root, "Suppression d'un acte");

echo '<div id="col_menu">';
form_recherche();
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';

$missingargs = false;
$oktype = false;

$xid  = getparam('xid');
$xtyp = getparam('xtyp');
$xconfirm = getparam('xconfirm');
$today = today();

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if ($xid == '' or $xtyp == '') {
	// Données postées
	msg("Vous devez préciser le numéro et le type de l'acte.");
	$missingargs = true;  // par défaut
}
if (!$missingargs) {
	$oktype = true;
	switch ($xtyp) {
		case "V":
			$ntype = "divers";
			$table = EA_DB . "_div3";
			$script = "tab_bans.php";
			$sexe = "SEXE, C_SEXE, ";
			break;
		case "M":
			$ntype = "mariage";
			$table = EA_DB . "_mar3";
			$script = "tab_mari.php";
			$sexe = "";
			break;
		default:
			$oktype = false;
	}

	if ($xconfirm == 'confirmed' and $oktype) {
		$request = "select NOM, PRE, ORI, DNAIS, AGE, PRO, EXCON, EXC_PRE, EXC_COM, COM, " . $sexe
			. "P_NOM, P_PRE, P_COM, P_PRO, M_NOM, M_PRE, M_COM, M_PRO, "
			. "C_NOM, C_PRE, C_ORI, C_DNAIS, C_AGE, C_PRO, C_EXCON, C_X_PRE, C_X_COM, C_COM, "
			. "CP_NOM, CP_PRE, CP_COM, CP_PRO, CM_NOM, CM_PRE, CM_COM, CM_PRO, "
			. "DATETXT,COMMUNE,DEPART from " . $table . " where ID=" . $xid;
		$result = EA_sql_query($request);
		//echo $request;
		if ($acte = EA_sql_fetch_array($result)) {
			permuter($acte["NOM"], $acte["C_NOM"]);
			permuter($acte["PRE"], $acte["C_PRE"]);
			permuter($acte["ORI"], $acte["C_ORI"]);
			permuter($acte["DNAIS"], $acte["C_DNAIS"]);
			permuter($acte["AGE"], $acte["C_AGE"]);
			permuter($acte["PRO"], $acte["C_PRO"]);
			permuter($acte["EXCON"], $acte["C_EXCON"]);
			permuter($acte["EXC_PRE"], $acte["C_X_PRE"]);
			permuter($acte["EXC_COM"], $acte["C_X_COM"]);
			permuter($acte["COM"], $acte["C_COM"]);
			permuter($acte["P_NOM"], $acte["CP_NOM"]);
			permuter($acte["P_PRE"], $acte["CP_PRE"]);
			permuter($acte["P_COM"], $acte["CP_COM"]);
			permuter($acte["P_PRO"], $acte["CP_PRO"]);
			permuter($acte["M_NOM"], $acte["CM_NOM"]);
			permuter($acte["M_PRE"], $acte["CM_PRE"]);
			permuter($acte["M_COM"], $acte["CM_COM"]);
			permuter($acte["M_PRO"], $acte["CM_PRO"]);
		}
		$request = "update " . $table . " set " .
			"NOM    = '" . sql_quote($acte["NOM"]) . "', " .
			"PRE    = '" . sql_quote($acte["PRE"]) . "', " .
			"ORI    = '" . sql_quote($acte["ORI"]) . "', " .
			"DNAIS  = '" . sql_quote($acte["DNAIS"]) . "', " .
			"AGE    = '" . sql_quote($acte["AGE"]) . "', " .
			"PRO    = '" . sql_quote($acte["PRO"]) . "', " .
			"EXCON  = '" . sql_quote($acte["EXCON"]) . "', " .
			"EXC_PRE= '" . sql_quote($acte["EXC_PRE"]) . "', " .
			"EXC_COM= '" . sql_quote($acte["EXC_COM"]) . "', " .
			"COM    = '" . sql_quote($acte["COM"]) . "', " .
			"P_NOM  = '" . sql_quote($acte["P_NOM"]) . "', " .
			"P_PRE  = '" . sql_quote($acte["P_PRE"]) . "', " .
			"P_COM  = '" . sql_quote($acte["P_COM"]) . "', " .
			"P_PRO  = '" . sql_quote($acte["P_PRO"]) . "', " .
			"M_NOM  = '" . sql_quote($acte["M_NOM"]) . "', " .
			"M_PRE  = '" . sql_quote($acte["M_PRE"]) . "', " .
			"M_COM  = '" . sql_quote($acte["M_COM"]) . "', " .
			"M_PRO  = '" . sql_quote($acte["M_PRO"]) . "', " .
			"C_NOM  = '" . sql_quote($acte["C_NOM"]) . "', " .
			"C_PRE  = '" . sql_quote($acte["C_PRE"]) . "', " .
			"C_ORI  = '" . sql_quote($acte["C_ORI"]) . "', " .
			"C_DNAIS= '" . sql_quote($acte["C_DNAIS"]) . "', " .
			"C_AGE  = '" . sql_quote($acte["C_AGE"]) . "', " .
			"C_PRO  = '" . sql_quote($acte["C_PRO"]) . "', " .
			"C_EXCON= '" . sql_quote($acte["C_EXCON"]) . "', " .
			"C_X_PRE= '" . sql_quote($acte["C_X_PRE"]) . "', " .
			"C_X_COM= '" . sql_quote($acte["C_X_COM"]) . "', " .
			"C_COM  = '" . sql_quote($acte["C_COM"]) . "', " .
			"CP_NOM = '" . sql_quote($acte["CP_NOM"]) . "', " .
			"CP_PRE = '" . sql_quote($acte["CP_PRE"]) . "', " .
			"CP_COM = '" . sql_quote($acte["CP_COM"]) . "', " .
			"CP_PRO = '" . sql_quote($acte["CP_PRO"]) . "', " .
			"CM_NOM = '" . sql_quote($acte["CM_NOM"]) . "', " .
			"CM_PRE = '" . sql_quote($acte["CM_PRE"]) . "', " .
			"CM_COM = '" . sql_quote($acte["CM_COM"]) . "', " .
			"CM_PRO = '" . sql_quote($acte["CM_PRO"]) . "', ";
		if ($xtyp == "V") {
			$request .=
				"SEXE   = '" . sql_quote($acte["SEXE"]) . "', " .
				"C_SEXE = '" . sql_quote($acte["C_SEXE"]) . "', ";
		}
		$request .=
			"DTMODIF= '" . $today . "' " .
			" where ID=" . $xid . ";";
		$result = EA_sql_query($request);
		//echo $request;
		$nb = EA_sql_affected_rows();
		if ($nb > 0) {
			echo '<p>' . $nb . ' acte de ' . $ntype . ' modifié.</p>';
			$comdep = $acte["COMMUNE"] . ' [' . $acte["DEPART"] . ']';
			writelog('Permutation ' . $ntype . ' #' . $xid, $acte["COMMUNE"], $nb);
			echo '<p>Retourner à la liste des actes ';
			echo '<a href="' . mkurl($script, stripslashes($comdep), $acte["NOM"]) . '"><b>' . $acte["NOM"] . '</b></a>';
			echo ' ou <a href="' . mkurl($script, stripslashes($comdep), $acte["C_NOM"]) . '"><b>' . $acte["C_NOM"] . '</b></a></p>';
		} else {
			echo '<p>Aucun acte modifié.</p>';
		}
	} else {
		$request = "select NOM,PRE, C_NOM, C_PRE, DATETXT,COMMUNE,DEPART from " . $table . " where ID=" . $xid;
		$result = EA_sql_query($request);
		if ($acte = EA_sql_fetch_array($result)) {
			if ($acte["C_NOM"] <> '') {
				echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
				echo '<h2 align="center">Confirmation de la permutation</h2>';
				echo '<p class="message">Epoux  = ' . $acte["C_NOM"] . " " . $acte["C_PRE"] . '</p>';
				echo '<p class="message">Epouse = ' . $acte["NOM"] . " " . $acte["PRE"] . '</p>';
				echo '<p class="message">';
				echo '<input type="hidden" name="xtyp" value="' . $xtyp . '">';
				echo '<input type="hidden" name="xid"  value="' . $xid . '">';
				echo '<input type="hidden" name="xconfirm" value="confirmed">';
				echo '<input type="submit" value=" >> CONFIRMER >> ">' . "\n";
				$comdep = $acte["COMMUNE"] . ' [' . $acte["DEPART"] . ']';
				$url = mkurl($script, stripslashes($comdep), $acte["NOM"]);
				echo '&nbsp; &nbsp; &nbsp; <a href="' . $url . '">Annuler</a></p>';
				echo "</form>\n";
			} else {
				msg('Interdit de permtuer un acte sans conjoint');
			}
		} else {
			msg('Impossible de trouver cet acte !');
		}
	} // confirmed ??
}
echo '</div>';
close_page(1, $root);
