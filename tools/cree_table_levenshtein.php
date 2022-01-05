<?php

// remplissage table temporaire pour requete avec jointure
function table_temp($xacht, $xcomp, $table, $hf, $xcomm, $ip_adr_trait, $xmin, $xmax, $T0, $Max_time, $COLLATION = '') 
{
	if ($COLLATION == '') $COLLATION = 'latin1_general_ci'; // latin1_swedish_ci latin1_general_ci
	$COLLATION = 'latin1_swedish_ci';

	if (time() - $T0 >= $Max_time) {
		return 'timeout' . $hf;
	} else {

		if ($xcomp == "Z") {
			$dm = 1;
		}
		if ($xcomp == "U") {
			$dm = 2;
		}
		if ($xcomp == "D") {
			$dm = 3;
		}
		if ($xcomp == "T") {
			$dm = 4;
		}
		if ($xcomp == "Q") {
			$dm = 5;
		}
		if ($xcomp == "C") {
			$dm = 6;
		}
		$commune1 = "T";
		$crit = "";
		if ($xmin != "") {
			$crit = " (year(LADATE)>= " . $xmin . ")";
		}
		if ($xmax != "") {
			$critx = " (year(LADATE)<= " . $xmax . ")";
			$crit = sql_and($crit) . $critx;
		}


		if ($xcomm[0] != "*") {
			$commune1 = "U";
		}

		if ($hf == "H")  // recherche homme
		{
			//	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_h  (`nomlev` varchar( 25 ) COLLATE latin1_general_ci NOT NULL ,`disth` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
			//	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_h  (`nomlev` varchar( 25 ) COLLATE ".$COLLATION." NOT NULL ,`disth` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=".$COLLATION.";";
			$request = "CREATE TEMPORARY TABLE IF NOT EXISTS " . EA_DB . "_" . $ip_adr_trait . "_h  
		(`disth` int( 11 ) NOT NULL DEFAULT 0, PRIMARY KEY ( `nomlev` ) )
		AS (SELECT NOM AS nomlev FROM " . $table . " WHERE ID='0');";


			$result = EA_sql_query($request) or die('Erreur SQL creation !' . $sql . '<br>' . EA_sql_error());
			if ($table == EA_DB . "_div3") // ##########################NOUVEAU###############################"
			{
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
					} else {
						$request = "SELECT nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " GROUP BY nom ORDER BY nom";
					} else {
						$request = "SELECT nom FROM " . $table . " GROUP BY nom ORDER BY nom";
					}
				}
			} elseif ($table == EA_DB . "_mar3") {
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " AND commune ='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
					} else {
						$request = "SELECT nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " GROUP BY nom ORDER BY nom";
					} else {
						$request = "SELECT nom FROM " . $table . " GROUP BY nom ORDER BY nom";
					}
				}
			} else {
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT p_nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY p_nom ORDER BY p_nom";
					} else {
						$request = "SELECT p_nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY p_nom ORDER BY p_nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT p_nom FROM " . $table . " WHERE " . $crit . " GROUP BY p_nom ORDER BY p_nom";
					} else {
						$request = "SELECT p_nom FROM " . $table . " GROUP BY p_nom ORDER BY p_nom";
					}
				}
			}
		}

		if ($hf == "F")  // recherche femme
		{
			//	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_f  (`nomlev` varchar( 25 ) COLLATE latin1_general_ci NOT NULL ,`distf` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
			//	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_f  (`nomlev` varchar( 25 ) COLLATE ".$COLLATION." NOT NULL ,`distf` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=".$COLLATION.";";
			$request = "CREATE TEMPORARY TABLE IF NOT EXISTS " . EA_DB . "_" . $ip_adr_trait . "_f  
			(`distf` int( 11 ) NOT NULL DEFAULT 0, PRIMARY KEY ( `nomlev` ) )
			AS  (SELECT NOM AS nomlev FROM " . $table . " WHERE ID='0');";

			$result = EA_sql_query($request) or die('Erreur SQL creation !' . $sql . '<br>' . EA_sql_error());
			if ($table == EA_DB . "_div3")   // ##########################NOUVEAU###############################"
			{
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT c_nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY c_nom ORDER BY c_nom";
					} else {
						$request = "SELECT c_nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY c_nom ORDER BY c_nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT c_nom FROM " . $table . " WHERE " . $crit . " GROUP BY c_nom ORDER BY c_nom";
					} else {
						$request = "SELECT c_nom FROM " . $table . " GROUP BY c_nom ORDER BY c_nom";
					}
				}
			} elseif ($table == EA_DB . "_mar3") {
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT c_nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY c_nom ORDER BY c_nom";
					} else {
						$request = "SELECT c_nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY c_nom ORDER BY c_nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT c_nom FROM " . $table . " WHERE " . $crit . " GROUP BY c_nom ORDER BY c_nom";
					} else {
						$request = "SELECT c_nom FROM " . $table . " GROUP BY c_nom ORDER BY c_nom";
					}
				}
			} else {
				if ($commune1 == "U") {
					if ($crit != '') {
						$request = "SELECT m_nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY m_nom ORDER BY m_nom";
					} else {
						$request = "SELECT m_nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY m_nom ORDER BY m_nom";
					}
				} else {
					if ($crit != '') {
						$request = "SELECT m_nom FROM " . $table . " WHERE " . $crit . " GROUP BY m_nom ORDER BY m_nom";
					} else {
						$request = "SELECT m_nom FROM " . $table . " GROUP BY m_nom ORDER BY m_nom";
					}
				}
			}
		}


		if ($hf == "D")  // spécial décès
		{
			//	  	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_d  (`nomlev` varchar( 25 ) COLLATE latin1_general_ci NOT NULL ,`distd` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
			//	  	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_d  (`nomlev` varchar( 25 ) COLLATE ".$COLLATION." NOT NULL ,`distd` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=".$COLLATION.";";
			$request = "CREATE TEMPORARY TABLE IF NOT EXISTS " . EA_DB . "_" . $ip_adr_trait . "_d  
			(`distd` int( 11 ) NOT NULL DEFAULT 0, PRIMARY KEY ( `nomlev` ) )
			AS  (SELECT NOM AS nomlev FROM " . $table . " WHERE ID='0');";

			$result = EA_sql_query($request) or die('Erreur SQL creation !' . $sql . '<br>' . EA_sql_error());
			if ($commune1 == "U") {
				if ($crit != '') {
					$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
				} else {
					$request = "SELECT nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
				}
			} else {
				if ($crit != '') {
					$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " GROUP BY nom ORDER BY nom";
				} else {
					$request = "SELECT nom FROM " . $table . " GROUP BY nom ORDER BY nom";
				}
			}
		}

		if ($hf == "N")  // spécial naissance
		{
			//	  	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_n  (`nomlev` varchar( 25 ) COLLATE latin1_general_ci NOT NULL ,`distn` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
			//	  	  	  $request = "CREATE TEMPORARY TABLE IF NOT EXISTS ".EA_DB."_".$ip_adr_trait."_n  (`nomlev` varchar( 25 ) COLLATE ".$COLLATION." NOT NULL ,`distn` int( 11 ) NOT NULL ,PRIMARY KEY ( `nomlev` )
			//								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=".$COLLATION.";";
			$request = "CREATE TEMPORARY TABLE IF NOT EXISTS " . EA_DB . "_" . $ip_adr_trait . "_n  
			(`distn` int( 11 ) NOT NULL DEFAULT 0, PRIMARY KEY ( `nomlev` ) )
			AS  (SELECT NOM as nomlev FROM " . $table . " WHERE ID='0');";

			$result = EA_sql_query($request) or die('Erreur SQL creation !' . $sql . '<br>' . EA_sql_error());
			if ($commune1 == "U") {
				if ($crit != '') {
					$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " AND commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
				} else {
					$request = "SELECT nom FROM " . $table . " WHERE commune='" . sql_quote($xcomm) . "' GROUP BY nom ORDER BY nom";
				}
			} else {
				if ($crit != '') {
					$request = "SELECT nom FROM " . $table . " WHERE " . $crit . " GROUP BY nom ORDER BY nom";
				} else {
					$request = "SELECT nom FROM " . $table . " GROUP BY nom ORDER BY nom";
				}
			}
		}

		//$T5 = time(); 
		$result = EA_sql_query($request) or die('Erreur SQL !' . $sql . '<br>' . EA_sql_error());
		$nbtot = EA_sql_num_rows($result);
		$nb = $nbtot;

		//		echo '<p>Durée requete temp '.$hf.'  tot : '.$nb.'   : '.(time()-$T5).' sec.</p>'."\n";
		if ($nb > 0) {	//$T4 = time();  
			while ($ligne = EA_sql_fetch_row($result)) {

				$k = levenshtein(strtoupper($xacht), strtoupper($ligne[0]));
				if ($k < $dm) {
					if ($hf == "H") {
						$request1 = "INSERT IGNORE INTO " . EA_DB . "_" . $ip_adr_trait . "_h (nomlev,disth) VALUES ('" . sql_quote($ligne[0]) . "'," . $k . " )";
					}
					if ($hf == "F") {
						$request1 = "INSERT IGNORE INTO " . EA_DB . "_" . $ip_adr_trait . "_f (nomlev,distf) VALUES ('" . sql_quote($ligne[0]) . "'," . $k . " )";
					}
					if ($hf == "D") {
						$request1 = "INSERT IGNORE INTO " . EA_DB . "_" . $ip_adr_trait . "_d (nomlev,distd) VALUES ('" . sql_quote($ligne[0]) . "'," . $k . " )";
					}
					if ($hf == "N") {
						$request1 = "INSERT IGNORE INTO " . EA_DB . "_" . $ip_adr_trait . "_n (nomlev,distn) VALUES ('" . sql_quote($ligne[0]) . "'," . $k . " )";
					}

					$result1 = EA_sql_query($request1) or die('Erreur SQL insertion !' . $sql . '<br>' . EA_sql_error());
					//$i++;
				}
			}
			//echo '<p>Durée Levenshtein '.$hf.'  tot : '.$nb.'   : '.(time()-$T4).' sec.</p>'."\n";
		}
		return 'ok';
	}
}
