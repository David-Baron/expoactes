<?php
// VERSION DU 26/09/2014
// Tableau en version texte pour la page d'accueil

if ($xtyp == "" or $xtyp == "A") {
	$condit1 = "";
} else {
	// André DELACHARLERIE $condit1 = " where TYPACT='".$xtyp."'";
	$condit1 = " where TYPACT='" . sql_quote($xtyp) . "'";
}

if ($init == "") {
	$initiale = '';
	$condit2 = '';
} else {
	$initiale = '&amp;init=' . $init;
	$leninit = mb_strlen($init);
	// André DELACHARLERIE $condit2 = " and upper(left(COMMUNE,".$leninit."))='".$init."'";
	$condit2 = " and upper(left(COMMUNE," . $leninit . "))='" . sql_quote($init) . "'";
}


$baselink = $root . $chemin . 'index.php';
$request = "SELECT DISTINCT upper(left(COMMUNE,1)) AS init FROM " . EA_DB . "_sums " . $condit1 . " ORDER BY init";
// Sélectionner et grouper sur initiale de commune et ascii(initiale), ordonner code ascii ascendant pour avoir + grand code (accentué) en dernier
$request = "SELECT alphabet.init FROM ( SELECT upper(left(COMMUNE,1)) AS init,ascii(upper(left(COMMUNE,1))) AS oo FROM " . EA_DB . "_sums " . $condit1 . " GROUP BY init,oo  ORDER BY init , oo ASC) AS alphabet GROUP BY init";
optimize($request);
$result = EA_sql_query($request);
$alphabet = "";
while ($row = EA_sql_fetch_row($result)) {
	if ($row[0] == $init)
		$alphabet .= '<b>' . $row[0] . '</b> ';
	else
		$alphabet .= '<a href="' . $baselink . '?xtyp=' . $xtyp . '&amp;init=' . $row[0] . '">' . $row[0] . '</a> ';
}
echo '<p align="center">' . $alphabet . '</p>';

echo '<table summary="Liste des communes avec décompte des actes">';
echoln('<tr class="rowheader">');
echo '<th>Localité</th>';
$nbcol = 3;
$cols = 1;  // pour graphique de répartition
if (ADM == 1 or SHOW_DATES == 1) {
	if (ADM == 1 or SHOW_DISTRIBUTION == 1)
		$cols = 2;
	echo '<th colspan="' . $cols . '">Période</th>';
	$nbcol++;
}
echo '<th>Actes</th>';
if (ADM == 1) {
	echo '<th>Datés</th>';
	$nbcol++;
}
echo '<th>Filiatifs</th>';
echoln('</tr>');

if ($xtyp == 'A')
	$arr = array('N', 'M', 'D', 'V');
else
	$arr = array($xtyp);

$nbcol += $cols;
$cptact = 0;
$cptnnul = 0;
$cptfil = 0;

foreach ($arr as $ztyp) {
	//André DELACHARLERIE 					." where typact = '".$ztyp."'".$condit2
	$request = "SELECT *"
		. " FROM " . EA_DB . "_sums "
		. " WHERE typact='" . sql_quote($ztyp) . "'" . $condit2
		. " ORDER BY LIBELLE,COMMUNE,DEPART; ";

	optimize($request);
	//echo '<p>'.$request;
	$pre_libelle = "XXX";
	if ($result = EA_sql_query($request)) {
		$i = 1;
		while ($ligne = EA_sql_fetch_array($result)) {
			if ($ligne['TYPACT'] . $ligne['LIBELLE'] <> $pre_libelle) {
				$pre_libelle = $ligne['TYPACT'] . $ligne['LIBELLE'];
				$linkdiv = "";
				switch ($ztyp) {
					case "N":
						$typel = "Naissances &amp; Baptêmes";
						$prog = "tab_naiss.php";
						break;
					case "V":
						$typel = "Divers : " . $ligne['LIBELLE'];
						$prog = "tab_bans.php";
						$linkdiv = ';' . $ligne['LIBELLE'];
						break;
					case "M":
						$typel = "Mariages";
						$prog = "tab_mari.php";
						break;
					case "D":
						$typel = "Décès &amp; Sépultures";
						$prog = "tab_deces.php";
						break;
				}
				echoln('<tr class="rowheader">');
				echo '<th colspan="' . $nbcol . '">' . $typel . '</th>';
				echoln('</tr>');
			}
			echoln('<tr class="row' . (fmod($i, 2)) . '">');
			echo '<td><a href="' . mkurl($root . $chemin . $prog, $ligne['COMMUNE'] . ' [' . $ligne['DEPART'] . ']' . $linkdiv) . '">' . $ligne['COMMUNE'] . '</a>';
			if ($ligne['DEPART'] <> "") echo ' [' . $ligne['DEPART'] . ']';
			echo '</td>';
			$imgtxt = "Distribution par années";
			if (ADM == 1 or SHOW_DATES == 1) {
				if (ADM == 1 or SHOW_DISTRIBUTION == 1)
					echo '<td><a href="' . $root . $chemin . 'stat_annees.php?comdep=' . urlencode($ligne['COMMUNE'] . ' [' . $ligne['DEPART'] . ']' . $linkdiv) . '&xtyp=' . $ztyp . '"><img src="' . $root . '/img/histo.gif" border="0" alt="' . $imgtxt . '" title="' . $imgtxt . '"></a></td>';
				echo '<td> (' . $ligne['AN_MIN'] . '-' . $ligne['AN_MAX'] . ') </td>';
			}
			echo '<td align="right"> ' . entier($ligne['NB_TOT']) . '</td>';
			if (ADM == 1)
				echo '<td align="right"> ' . entier($ligne['NB_N_NUL']) . '</td>';
			echo '<td align="right"> ' . entier($ligne['NB_FIL']) . '</td>';
			echoln('</tr>');
			$cptact = $cptact + $ligne['NB_TOT'];
			$cptnnul = $cptnnul + $ligne['NB_N_NUL'];
			$cptfil = $cptfil + $ligne['NB_FIL'];
			$i++;
		}
	}
}
echoln('<tr class="rowheader">');
echo '<td align="right"><b>Totaux :</b></td>';
if (ADM == 1 or SHOW_DATES == 1)
	echo '<td colspan="' . $cols . '">  </td>';
echo '<td align="right"> ' . entier($cptact) . '</td>';
if (ADM == 1)
	echo '<td align="right"> ' . entier($cptnnul) . '</td>';
echo '<td align="right"> ' . entier($cptfil) . '</td>';
echoln('</tr>');
echoln('</table>');
