<?php

//-------- utilitaire pour l'installation et la mise à jour ---------------

class Parametre
{
	var $param;  // nom du parametre et de la constante associéé
	var $groupe; // groupe de paramètres
	var $ordre;  // ordre pour classement dans l'écran
	var $type;   // B(ooleen), N(umérique), C(aractère) ou T(exte)
	var $valeur; // valeur effective (par défaut)
	var $listval; // liste des valeurs admissibles (lisbox)
	var $libelle; // texte dans l'ecran de saisie
	var $aide;   // aide associée

	function __construct($arg)
	{
		foreach ($arg as $k => $v)
			$this->$k = $arg[$k];
	}
}
//------------------------------------------------------------------------------

function xml_write_table($table, $zones, &$nb)
{
	// retorune sous forme XML les zones de la table designée
	$doc = "";
	$request = "select * from " . $table;
	//echo $request;
	$result = EA_sql_query($request);
	$nbdocs = EA_sql_num_rows($result);
	$fields_cnt = EA_sql_num_fields($result);
	//$nb=0; passé par référence !!
	while ($row = EA_sql_fetch_array($result)) {
		$doc .= "  <" . $table . ">\n";
		foreach ($zones as $zone => $zoneval) {
			$doc .= "    <" . $zoneval . ">";
			$tvalue = $row[$zoneval];
			$tvalue = str_replace(chr(13) . chr(10), '\n', $tvalue);      // codage des retours chariots    	
			$doc .= (htmlspecialchars(html_entity_decode($tvalue, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET), ENTITY_REPLACE_FLAGS, ENTITY_CHARSET));
			$doc .= "</" . $zoneval . ">\n";
		} // end foreach
		$doc .= "  </" . $table . ">\n";
		$nb++;
	}
	return $doc;
}

//------------------------------------------------------------------------------

function readDatabase($filename, $masterkey)
{
	// lit la base de données xml des paramètres
	$data = implode("", file($filename));
	$parser = xml_parser_create("UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $tags);
	xml_parser_free($parser);

	// boucle à travers les structures
	foreach ($tags as $key => $val) {
		if ($key == $masterkey) {
			$ranges = $val;
			// each contiguous pair of array entries are the
			// lower and upper range for each definition
			for ($i = 0; $i < count($ranges); $i += 2) {
				$offset = $ranges[$i] + 1;
				$len = $ranges[$i + 1] - $offset;
				$tdb[] = parseDef(array_slice($values, $offset, $len));
			}
		} else {
			continue;
		}
	}
	return $tdb;
}

//------------------------------------------------------------------------------

function parseDef($mvalues)
{
	for ($i = 0; $i < count($mvalues); $i++)
		if (isset($mvalues[$i]["value"]))
			$pardef[$mvalues[$i]["tag"]] = $mvalues[$i]["value"];
		else
			$pardef[$mvalues[$i]["tag"]] = "";
	return new Parametre($pardef);
}

//------------------------------------------------------------------------------

function update_params($paramfile, $modifvaleur = 0)
{ // utitilisé à la fois à l'installation, à la mise à jour	et aux backup/restore		
	$para = readDatabase($paramfile, "act_params");
	global $par_add, $par_mod;

	//{echo "<pre>";	print_r($para); echo "</pre>";}

	foreach ($para as $param) {
		// echo "<p>Traitement de ".$param->param;
		$request = "select * from " . EA_DB . "_params where param = '" . $param->param . "'";
		$result = EA_sql_query($request);
		if (EA_sql_num_rows($result) == 0) {
			// AJOUT DU PARAMETRE
			if (defined($param->param)) {
				// récupération de la valeur existante ... 
				$param->valeur = constant($param->param);
			}
			//echo "<p>".$param->valeur."</p>";
			$valeur = str_replace('\n', chr(13) . chr(10), $param->valeur);  // permet de récupérer les sauts de ligne
			$sql = "insert into " . EA_DB . "_params (param,groupe,ordre,type,valeur,listval,libelle,aide) "
				. "values ('" . $param->param . "','"
				. sql_quote($param->groupe) . "','"
				. $param->ordre . "','"
				. $param->type . "','"
				. sql_quote($valeur) . "','"
				. sql_quote($param->listval) . "','"
				. sql_quote($param->libelle) . "','"
				. sql_quote($param->aide) . "')";
			//echo "<p>".$sql."</p>";
			EA_sql_query($sql);
			if (EA_sql_affected_rows() == 1)
				$par_add++;
			else {
				msg('Impossible d\'ajouter le paramètre ' . $param->param);
				$par_add = -1000;
			}
		} else {
			$row = EA_sql_fetch_array($result);
			$dims = array('groupe', 'ordre', 'type', 'listval', 'libelle', 'aide');
			if ($modifvaleur) array_push($dims, "valeur"); // modifier aussi la valeur
			if ($modifvaleur and $row['groupe'] == 'Hidden') {
				// Ne rien faire si paramètre caché
			} else {
				foreach ($dims as $dim) {
					$dimvals = get_object_vars($param);
					if ($dim == 'valeur')
						$dimvals = str_replace('\n', chr(13) . chr(10), $dimvals);  // permet de récupérer les sauts de ligne
					if (trim($row[$dim]) <> trim($dimvals[$dim])) {
						// MISE A JOUR de la définition 
						$sql = "update " . EA_DB . "_params set " . $dim . " = '" . sql_quote($dimvals[$dim]) . "' where param = '" . $param->param . "'";
						// echo "<p>".$sql;
						$result = EA_sql_query($sql);
						$par_mod += EA_sql_affected_rows();
					}
				}
			} // si pas caché
		}
	} // foreach
}

//------------------------------------------------------------------------------

function xml_readDatabase($filename, $masterkey)
{
	// lit la base de données xml des paramètres et la retourne dans un tableau
	$data = implode("", file($filename));
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $data, $values, $tags);
	xml_parser_free($parser);

	// boucle à travers les structures
	foreach ($tags as $key => $val) {
		if ($key == $masterkey) {
			$ranges = $val;
			// each contiguous pair of array entries are the
			// lower and upper range for each definition
			for ($i = 0; $i < count($ranges); $i += 2) {
				$offset = $ranges[$i] + 1;
				$len = $ranges[$i + 1] - $offset;
				$tdb[] = tab_parseDef(array_slice($values, $offset, $len));
			}
		} else {
			continue;
		}
	}
	return $tdb;
}

//------------------------------------------------------------------------------

function tab_parseDef($mvalues)
{
	for ($i = 0; $i < count($mvalues); $i++)
		if (isset($mvalues[$i]["value"]))
			$pardef[$mvalues[$i]["tag"]] = ($mvalues[$i]["value"]);
		else
			$pardef[$mvalues[$i]["tag"]] = "";
	return $pardef;
}

//------------------------------------------------------------------------------

function update_metafile($tabdata, $tabkeys, $table, &$radd, &$rmod)
{
	foreach ($tabdata as $rowdata) {
		// création de la clé primaire	
		$condit = "";
		foreach ($tabkeys as $keycol) {
			$condit = sql_and($condit) . $keycol . " = '" . $rowdata[$keycol] . "'";
		}
		$request = "select * from " . $table . " where " . $condit;
		$result = EA_sql_query($request);
		if (EA_sql_num_rows($result) == 0) {
			// AJOUT de la ligne
			$lcol = "";
			$ldat = "";
			foreach ($rowdata as $col => $value) {
				$valeur = str_replace('\n', chr(13) . chr(10), $value);  // permet de récupérer les sauts de ligne
				$lcol = sql_virgule($lcol, $col);
				$ldat = sql_virgule($ldat, "'" . sql_quote($valeur) . "'");
			}
			$sql = "insert into " . $table . "(" . $lcol . ") values (" . $ldat . ")";
			//echo "<p>".$sql."</p>";
			EA_sql_query($sql);
			if (EA_sql_affected_rows() == 1)
				$radd++;
			else {
				msg('Impossible d\'ajouter le paramètre ' . $condit);
				$radd = -1000;
			}
		} else {
			// MISE A JOUR de la définition
			$lsets = "";
			foreach ($rowdata as $col => $value) {
				if (!in_array($col, $tabkeys)) {
					$valeur = str_replace('\n', chr(13) . chr(10), $value);  // permet de récupérer les sauts de ligne
					$lsets = sql_virgule($lsets, $col . "='" . sql_quote($valeur) . "'");
				}
			} // si pas caché
			$sql = "update " . $table . " set " . $lsets . " where " . $condit;
			//echo "<p>".$sql;
			$result = EA_sql_query($sql);
			$rmod += EA_sql_affected_rows();
		}
	} // foreach
}
