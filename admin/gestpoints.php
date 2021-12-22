<?php
ob_implicit_flush(1);

include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");
include("../tools/traitements.inc.php");

$root = "";
$path = "";
$userlogin = "";
$T0 = time();

//**************************** ADMIN **************************

pathroot($root, $path, $xcomm, $xpatr, $page);

//print '<pre>';  print_r($_REQUEST); echo '</pre>';

$userlogin = "";
$userlevel = logonok(9);
while ($userlevel < 9) {
	login($root);
}

$oper     = getparam('oper');
$nbrepts  = getparam('nbrepts');
$xdroits  = getparam('lelevel');
$regime   = getparam('regime');
$rem      = getparam('rem');
$condit   = getparam('condit');
$statut   = getparam('statut');
$dtexpir   = getparam('dtexpir');
$conditexp = getparam('conditexp');

$ptitle = "Modifications groupées";
open_page($ptitle, $root);
navadmin($root, $ptitle);

echo '<div id="col_menu">';
form_recherche($root);
menu_admin($root, $userlevel);
echo '</div>';

echo '<div id="col_main_adm">';
$missingargs = true;
$emailfound = false;
$cptok = 0;
$cptko = 0;

menu_users('S');
$ok = true;
$today = today();
$condrem = "";
if (getparam('action') == 'submitted') {
	if ($oper == "") {
		msg("Vous devez préciser une action à réaliser");
		$ok = false;
	}
	if ($oper == "E") {
		$sqlnewdt = "";
		$baddt = 0;
		ajuste_date($nbrepts, $sqlnewdt, $baddt);
		if ($sqlnewdt == '0000-00-00' or $baddt) {
			msg("La nouvelle date d'expiration n'est pas valide");
			$ok = false;
		}
	}

	if ($condit <> "0") {
		$condrem = " and " . comparerSQL('REM', $rem, $condit);
	}
	$condreg = "";
	if ($regime >= 0) {
		$condreg = " and regime =" . $regime;
	}
	if ($statut > 0) {
		$condreg = " and statut =" . $statut;
	}
	$sqlexpir = "";
	$baddt = 0;
	ajuste_date($dtexpir, $sqlexpir, $baddt);
	if ($sqlexpir > '0000-00-00' and $conditexp <> "0") {
		$condreg = " and " . comparerSQL('dtexpiration', $sqlexpir, $conditexp);
	}

	if ($ok) {
		if ($oper == "E") // ==== ajustement de la date d'expiration
		{

			$request = "update " . EA_UDB . "_user3 set"
				. " dtexpiration='" . $sqlnewdt . "'"
				. " where level=" . $xdroits . $condreg . $condrem . " ;";
			$result = EA_sql_query($request, $u_db);
			//echo $request;
			$nb = EA_sql_affected_rows($u_db);
			echo "<p>Modification de la date d'expiration de " . $nb . " comptes utilisateurs.</p>";
			writelog('Modif. dates expiration', "USERS", $nb);
			$missingargs = false;
		} elseif ($oper == "R") //==== remise à 0 des points consommés
		{
			$request = "update " . EA_UDB . "_user3 set"
				. " pt_conso=0"
				. " where level=" . $xdroits . $condreg . $condrem . " ;";
			$result = EA_sql_query($request, $u_db);
			//echo $request;
			$nb = EA_sql_affected_rows($u_db);
			echo "<p>Remise à zéro des points consommés de " . $nb . " comptes utilisateurs.</p>";
			writelog('RAZ des points consommés', "USERS", $nb);
			$missingargs = false;
		} elseif ($oper == "A" or $oper == "F") {
			// modification des points disponibles
			$request = "select id, nom, prenom, solde, pt_conso"
				. " from " . EA_UDB . "_user3 "
				. " where level=" . $xdroits . $condreg . $condrem . " ;";
			//echo $request;
			$sites = EA_sql_query($request, $u_db);
			$nbsites = EA_sql_num_rows($sites);
			$nbsend = 0;
			$missingargs = false;

			while ($site = EA_sql_fetch_array($sites)) {
				$idsit = $site['id'];
				$oldsolde = $site['solde'];
				$nom = $site['nom'];
				$prenom = $site['prenom'];
				if ($oper == "A")
					$newsolde = $oldsolde + $nbrepts;
				else
					$newsolde = $nbrepts;
				$request = "update " . EA_UDB . "_user3 set"
					. " solde=" . $newsolde . ", maj_solde='" . $today . "'"
					. " where id=" . $idsit . " ;";
				$result = EA_sql_query($request, $u_db);
				// echo $request;
				$nb = EA_sql_affected_rows($u_db);

				if ($nb == 1) {
					echo "<p>Modifié le solde de " . $prenom . " " . $nom . " (" . $oldsolde . " -> " . $newsolde . ") </p>";
					$cptok++;
				}
			}
		} // fichier d'actes
		if ($cptok > 0) {
			echo '<p>Soldes modifiés  : ' . $cptok;
			writelog('Soldes modifiés ', "USERS", $cptok);
		}
		if ($cptko > 0)  echo '<br>Soldes impossible à modifier : ' . $cptko;
	}
}
//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
	if (getparam('action') == '')  // parametres par défaut
	{
		if (isset($_COOKIE['chargeUSERlogs']))
			$logOk = $_COOKIE['chargeUSERlogs'][0];
		else
			$logOk = "1";
	}

	echo '<form method="post" enctype="multipart/form-data" action="">' . "\n";
	echo '<h2 align="center">' . $ptitle . '</h2>';
	echo '<table cellspacing="2" cellpadding="0" border="0" align="center">' . "\n";

	echo " <tr><td colspan=\"2\"><b>Utilisateurs concernés</b></td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Droits d'accès : </td>\n";
	echo '  <td>';
	lb_droits_user($xdroits);
	echo '  </td>';
	echo " </tr>\n";
	if (GEST_POINTS > 0) {
		echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
		echo " <tr>\n";
		echo "  <td align=right>Régime (points) : </td>\n";
		echo '  <td>';
		lb_regime_user($regime, 1);
		echo '  </td>';
		echo " </tr>\n";
	} else {
		echo ' <tr><td colspan="2">';
		echo '<input type="hidden" name="regime" value="-1" />';
		echo "</td></tr>\n";
	}

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align='right'>Statut : </td>\n";
	echo '  <td>';
	lb_statut_user($statut, 1);
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align='right'>Date expiration : </td>\n";
	echo '  <td>';
	listbox_trait('conditexp', "NTS", $conditexp);
	echo '<input type="text" name="dtexpir" size="10" value="' . $dtexpir . '" />' . "</td>\n";
	echo " </tr>\n";


	echo " <tr><td align=right>ET</td><td>&nbsp;</td></tr>\n";
	echo " <tr>\n";
	echo "  <td align=right>Commentaire : </td>\n";
	echo '  <td>';
	listbox_trait('condit', "TST", $condit);

	echo ' <input type="text" name="rem" size="50" value="' . $rem . '" />';
	echo "</td>\n";
	echo " </tr>\n";

	echo " <tr><td colspan=\"2\"><b>Action à effectuer</b></td></tr>\n";
	echo " <tr>\n";
	echo '  <td align="right">Opération : </td>' . "\n";
	echo '  <td>';
	echo '        <br />';
	echo '        <input type="radio" name="oper" value="E" />Fixer la date d\'expiration des comptes à <br />';
	if (GEST_POINTS > 0) {
		echo '        <input type="radio" name="oper" value="R" />Remettre à 0 les points <i>consommés</i> <br />';
		echo '        <input type="radio" name="oper" value="A" />Ajouter les points suivants au solde <i>disponible</i><br />';
		echo '        <input type="radio" name="oper" value="F" />Fixer le solde de points <i>disponibles</i> à <br />';
	}
	echo '        <br />';
	echo '  </td>';
	echo " </tr>\n";

	echo " <tr>\n";
	echo "  <td align=right>Valeur : </td>\n";
	echo '  <td><input type="text" name="nbrepts" size="12" value="' . $nbrepts . '">' . "</td>\n";
	echo " </tr>\n";

	echo " </tr>\n";
	echo " <tr><td colspan=\"2\">&nbsp;</td></tr>\n";
	echo " <tr><td colspan=\"2\" align=\"center\">\n<br>";
	echo '  <input type="hidden" name="action" value="submitted">';
	// echo '  <a href="aide/chargecsv.html" target="_blank">Aide</a>&nbsp;';
	echo '  <input type="reset" value="Effacer">' . "\n";
	echo '  <input type="submit" value=" >> EFFECTUER >> ">' . "\n";
	echo " </td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
} else {
	echo '<hr>';
	echo '<br>Durée du traitement  : ' . (time() - $T0) . ' sec.';
	echo '</p>';
	echo '<p>Retour à la ';
	echo '<a href="' . mkurl("listusers.php", "") . '"><b>' . "liste des utilisateurs" . '</b></a>';
	echo '</p>';
}
echo '</div>';
close_page(1, $root);
