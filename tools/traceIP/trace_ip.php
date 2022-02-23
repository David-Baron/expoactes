<?php
/////////////////////////////////////////////////////////////////////////////////////
//  Trace IP v2 - http://www.php-astux.info - http://www.1001bd.com                //
/////////////////////////////////////////////////////////////////////////////////////
// 
// Version adaptée pour Expoactes (A. Delacharlerie 2008-2009)
//

function traceip()
{

	// visitor general data
	$TIPlocked = 0;
	$Vcpt  = 1;
	$Vdate = date("Y/m/d H:i");
	$Vdatetime = time(); // timestamp
	$array_server_values = $_SERVER;
	$Vua   = $array_server_values['HTTP_USER_AGENT'];
	$Vip   = $array_server_values['REMOTE_ADDR'];
	global $userlogin;

	if (!defined("TIP_FILTRER")) define("TIP_FILTRER", "0");
	if (!defined("TIP_AUTOFREE")) define("TIP_AUTOFREE", "0");
	if (!defined("TIP_DUREE")) define("TIP_DUREE", "1");

	global $bypassTIP; // bypassTIP : pour ne pas exécuter traceip sur une page il suffit de déclarer $bypassTIP=1;
	if (!isset($bypassTIP)) $bypassTIP = 0;
	global $TIPlevel;  // $TIPlevel : niveau de la page : 1=page a compter (actes) 0 = autre (par défaut)
	if (!isset($TIPlevel)) $TIPlevel = 0;

	if ((TIP_FILTRER == 1 or (TIP_FILTRER == 2 and $TIPlevel > 0)) and $bypassTIP != 1)
	// Filtrage activé
	{

		// Elimine l'IP non bloquée qui est plus âgée que TIP_DUREE minutes
		$req_tip_del_oldIP = "DELETE FROM " . EA_DB . "_traceip WHERE (datetime < " . ($Vdatetime - 60 * TIP_DUREE) . " AND locked = 0)";
		mysql_query($req_tip_del_oldIP) or die(mysql_error() . ' ' . __LINE__);
		if (TIP_AUTOFREE > 0) {
			// clean up IP banned older then TIP_AUTOFREE days (and with less then 2 X TIP_MAX_PAGE_COUNT)
			$req_tip_del_oldIP = "DELETE FROM " . EA_DB . "_traceip WHERE (datetime < " . ($Vdatetime - 60 * 60 * 24 * TIP_AUTOFREE) . " AND locked = 1 AND cpt <= " . (2 * TIP_MAX_PAGE_COUNT) . ")";
			mysql_query($req_tip_del_oldIP) or die(mysql_error() . ' ' . __LINE__);
		}

		$lock = 0;
		$whitelist = explode(",", TIP_WHITELIST);
		foreach ($whitelist as $whitesign) {
			if (isin($Vua, trim($whitesign)) >= 0) {
				$lock = -1;
				break;
			}
		}

		// is this visitor banned or already in DB ?
		$req_tip_ip = "SELECT ip, datetime, cpt, locked FROM " . EA_DB . "_traceip WHERE ip = '" . $Vip . "';";
		$tip_ip = mysql_query($req_tip_ip) or die(mysql_error() . ' ' . __LINE__);
		$tip_num_rows = mysql_num_rows($tip_ip);

		if ($tip_num_rows != 0) {
			$Vdata = mysql_fetch_array($tip_ip);
			$dateReOpen = date("d M Y H:i:s", $Vdata['datetime'] + 60 * 1440 * TIP_AUTOFREE);
			$dateTerme  = date("d M Y H:i:s", $Vdata['datetime'] + 60 * TIP_DUREE);

			if ($Vdata['locked'] == 1) {
				$TIPlocked = 1;
			};
		}; // end of if (mysql_num_rows($tip_ip) != 0)


		// here we assume he's not banned or not in DB list ...
		// if not in DB list : add him, otherwise add +1 to his counter.
		if ($tip_num_rows == 0) {
			if (($lock == 0) or ($lock == -1 and TIP_MEMOWHITE == 1)) {
				$req_tip_IP = "INSERT INTO " . EA_DB . "_traceip (ua, ip, datetime, cpt, locked, login) VALUES ('" . addslashes($Vua) . "','" . $Vip . "'," . $Vdatetime . "," . $Vcpt . "," . $lock . ",'" . $userlogin . "');";
				mysql_query($req_tip_IP) or die(mysql_error() . ' ' . __LINE__);
			}
		} else // he's in DB, neither locked nor banned
		{
			$Vcpt = $Vdata['cpt']++; // update his counter
			$req_tip_IP = "UPDATE " . EA_DB . "_traceip SET cpt=cpt+1, login='" . $userlogin . "' WHERE ip='" . $Vip . "';";
			mysql_query($req_tip_IP) or die(mysql_error() . ' ' . __LINE__);
		};
		// Avertissement du blocage imminent
		if ($Vcpt >= TIP_MAX_PAGE_COUNT - TIP_ALERT and $Vdata['locked'] == 0) {
			global $TIPmsg;
			$codes = array("#IPCLIENT#", "#COMPTE#", "#RESTE#", "#TERME#");
			$decodes = array($Vip, $Vcpt, TIP_MAX_PAGE_COUNT - $Vcpt, $dateTerme);
			$TIPmsg = str_replace($codes, $decodes, TIP_MSG_ALERT);
		}

		// if this visitor is at TIP_MAX_PAGE_COUNT, ban his IP.
		if ($Vcpt > TIP_MAX_PAGE_COUNT and $Vdata['locked'] == 0) {
			$TIPlocked = 1;
			$req_tip_banIP = "UPDATE " . EA_DB . "_traceip SET locked = 1 WHERE ip='" . $Vip . "';";
			mysql_query($req_tip_banIP) or die(mysql_error() . ' ' . __LINE__);

			// Generate the alert mail
			//$tip_headers = 'From: Expoactes/TraceIP v2 <'.LOC_MAIL.'>' . "\r\n";
			$tip_subject = '[IP Interdite] ' . $Vip . ' - ' . $Vdate . "\r\n";
			$tip_body  = 'Variables serveur envoyées :' . "\n";
			while (list($key, $val) = each($array_server_values)) {
				$tip_body .= '  ' . $key . ' => ' . $val . "\n";
			};
			$tip_body = addslashes($tip_body);
			eval("\$tip_body = \"$tip_body\";");
			$tip_body = stripslashes($tip_body);
			$root = "";
			$path = "";
			$xcomm = $xpatr = $page = "";
			pathroot($root, $path, $xcomm, $xpatr, $page);
			$url_admin_tip = "http://" . $array_server_values['SERVER_NAME'] . $root . "/admin/gesttraceip.php";
			$tip_body .= "\n\nAdministrer les IP bannies : " . $url_admin_tip;

			// Send the mail
			//mail(TIP_MAIL_TO, $tip_subject, $tip_body, $tip_headers);
			$from = remove_accent(SITENAME) . ' <' . LOC_MAIL . '>';
			sendmail($from, TIP_MAIL_TO, $tip_subject, $tip_body);
		};
		if ($TIPlocked == 1) {
			// this visitor is banned
			echo "<h2>" . SITENAME . " : IP " . $Vip . " bloqu&eacute;e";
			if (TIP_AUTOFREE > 0) echo " jusque " . $dateReOpen;
			echo " </h2>";
			echo "<h2>" . TIP_MSG_BAN . "</h2>";
			exit();
		};
		mysql_free_result($tip_ip);
	}
}


function admin_traceip()
// Administration des IP Bannies 
{
	echo '<h1>Gestion du filtrage d\'IP (adapté de TraceIP v2)</h1>' . "\n";

	if (!defined("TIP_FILTRER")) define("TIP_FILTRER", "0");

	if (TIP_FILTRER == 0) // Filtrage desactivé
	{
		echo "<p class=\"erreur\">" . "Le filtrage des adresses IP n'est pas activé.";
		echo '<br />Pour activer le filtrage passez par le paramétrage "FiltreIP" </p>';
	}

	$do = (isset($_GET['do'])) ? $_GET['do'] : '';
	$ipid = (isset($_GET['ipid'])) ? abs(sprintf("%d", $_GET['ipid'])) : 0;

	if (($do == 'cle') && ($ipid != 0)) {
		$req_delban = "DELETE FROM " . EA_DB . "_traceip WHERE locked = -1 and cpt<='" . $ipid . "';";
		mysql_query($req_delban);
		echo '<h3>Nettoyage d\'IP effectuée.</h3>' . "\n";
	};
	if (($do == 'del') && ($ipid != 0)) {
		$req_delban = "DELETE FROM " . EA_DB . "_traceip WHERE id='" . $ipid . "' LIMIT 1;";
		mysql_query($req_delban);
		echo '<h3>Suppression d\'IP effectuée.</h3>' . "\n";
	};

	if (($do == 'fre') && ($ipid != 0)) {

		$req_delban = "UPDATE " . EA_DB . "_traceip SET locked = -1 WHERE id='" . $ipid . "' LIMIT 1;";
		mysql_query($req_delban);
		echo '<h3>Affranchissement permanent d\'IP effectué.</h3>' . "\n";
	};

	$req_allIP = "SELECT id,ua, ip, login, datetime, cpt,locked
										FROM " . EA_DB . "_traceip
										ORDER BY id ASC";

	$allIP = mysql_query($req_allIP) or die(mysql_error() . ' ' . __LINE__);
	$array_IP = array();
	while ($ip = mysql_fetch_array($allIP)) {
		$ip_id = $ip['id'];
		$array_IP[$ip_id]['ua'] = $ip['ua'];
		$array_IP[$ip_id]['ip'] = $ip['ip'];
		$array_IP[$ip_id]['login'] = $ip['login'];
		$array_IP[$ip_id]['datetime'] = $ip['datetime'];
		$array_IP[$ip_id]['cpt'] = $ip['cpt'];
		$array_IP[$ip_id]['locked'] = $ip['locked'];
	};

	// display lines

	echo '<h2>Liste des adresses IP récentes, affranchies ou bannies</h2>' . "\n";
	if (count($array_IP) == 0) {
		echo '<p>Aucune IP dans la base de données.</p>' . "\n";
	} else {
		$tot = array(-1 => 0, 0 => 0, 1 => 0);
		$totcpt = 0;
		echo '<p>Ci dessous est affichée la liste des IP présentes dans votre base de données. <br />Les IP en rouge sont bannies. Les IP en vert sont affranchie de façon permanente.</p>' . "\n";
		echo '<p><a href="?act=admin&amp;do=cle&amp;ipid=25" title="Nettoyer"> Nettoyer les IP-autoaffranchies</a> (cpt < 25)' . "\n";
		echo '<table width="100%" border="1" cellpadding="0" cellspacing="0" summary="" style="margin:auto;">' . "\n";
		echo '<thead>' . "\n";
		echo '	<tr style="background-color:#EFEFEF; text-align:center;">' . "\n";
		echo '		<th>ID</th>' . "\n";
		echo '		<th>User Agent</th>' . "\n";
		echo '		<th>IP</th>' . "\n";
		echo '		<th>Login</th>' . "\n";
		echo '		<th>Date</th>' . "\n";
		echo '		<th>Compte pages</th>' . "\n";
		echo '		<th>Opérations</th>' . "\n";
		echo '	</tr>' . "\n";
		echo '</thead>' . "\n";
		echo '<tbody>' . "\n";

		foreach ($array_IP as $id => $ip) {
			$tot[$ip['locked']]++;
			if ($ip['locked'] == 0) $totcpt += $ip['cpt'];
			echo '  <tr';
			echo ($ip['locked'] == 1) ? ' style="color:#CC0000;"' : '';
			echo ($ip['locked'] == -1) ? ' style="color:#339900;"' : '';
			echo '>' . "\n";
			echo '    <td style="text-align:center;">' . $id . '</td>' . "\n";
			echo '    <td>' . $ip['ua'] . '</td>' . "\n";
			echo '    <td style="text-align:center;">' . $ip['ip'] . '</td>' . "\n";
			echo '    <td style="text-align:center;">' . $ip['login'] . '</td>' . "\n";
			echo '    <td style="text-align:center;">' . date("d/m/Y H:i:s", $ip['datetime']) . '</td>' . "\n";
			echo '    <td style="text-align:center;">' . $ip['cpt'] . '</td>' . "\n";
			echo '    <td style="text-align:center;"><a href="?act=admin&amp;do=del&amp;ipid=' . $id . '" title="Supprimer">[Supprimer]</a> <a href="?act=admin&amp;do=fre&amp;ipid=' . $id . '" title="Affranchir">[Affranchir]</a></td>' . "\n";
			echo '	</tr>' . "\n";
		};
		$dateInstant  = date("d M Y H:i:s", time());

		echo $dateInstant . ' : Accès affranchis : ' . $tot[-1];
		echo ' - Accès normaux : ' . $tot[0] . ' (Total de vus : ' . $totcpt . ')';
		echo ' - Accès bloqués : ' . $tot[1];

		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
	}; // end of if (count($array_IP) == 0)


	//footer
	$tip_footer = '<div style="text-align:right; margin-top:2em;"><a href="http://www.php-astux.info">http://www.php-astux.info</a></div>';
	echo $tip_footer;
};
