<?php

// Définition des traitement d'importation

$trait = array (
  "SEX" => array ("M" => "Toujours Masculin",
  								"F" => "Toujours Féminin",
  								"?" => "Inconnu",
  								"S" => "Selon 1er prénom"
  								),
  "TXT" => array ("N" => '"N" si vide',
									"M" => "en Majuscules",
									"D" => "1er mot seulement",
  								"S" => "Sauf 1er mot",
  								"F" => "Dernier mot seulement",
  								"K" => "Sauf dernier mot",
  								"A" => "Début en majuscules",
  								"I" => "Fin en minuscules",
  								"P" => "Contenu parenthèses",
  								"Q" => "Sauf parenthèses",
  								"V" => "Avant la virgule",
  								"W" => "Après la virgule",
  								"C" => "Patronyme si pas vide",
  								"R" => "Date répub. 1 col.",
  								"T" => "Date répub. 3 col.",
  								"+" => '"+" si pas vide',
  								"1" => "+ col. suivante",
  								"2" => "+ 2 col. suivantes",
  								"3" => "+ 3 col. suivantes",
  								"4" => "+ 4 col. suivantes",
  								"5" => "+ 5 col. suivantes"
  								),
  "AGE" => array ("C" => "Chiffres seulement",
  								"A" => "Abréger a m j",
  								),
	"NUM" => array ("C" => "Chiffres seulement",
  								),
	"DAT" => array ("I" => "AAAA-MM-JJ",         // 1756-04-23
  								"S" => "JJ/MM/AAAA",         // 23/04/1756
  								"C" => "JJMMAAAA",           // 23041756
  								"J" => "AAAAMMJJ",           // 23APR1756
  								"T" => "JJMMMAAAA",          // 23APR1756
  								"U" => "AAAAMMMJJ",          // 1756APR23
  								"M" => "3 col. JJ MM AAAA", // 23  04   1756
  								"R" => "3 col. AAAA MM JJ",
  								"A" => "Date répub. 1 col.",
  								"B" => "Date répub. 3 col.",
  								),
  "TST" => array ("C" => "contient",
  								"P" => "ne contient pas",
  								"=" => " == ",
  								">" => " >= ",
  								"<" => " <= ",
  								"D" => " <> ",
  								"V" => "est vide",
  								"N" => "n'est pas vide",
  								"R" => "Est date républ.",
  								"G" => "Pas date républ.",
  								),
  "NTS" => array ("=" => " == ",
  								">" => " >= ",
  								"<" => " <= ",
  								"D" => " <> ",
  								)
 );
//------------------------------------------------------------------------------

function moisrepub($moisrepub,$typ=0)
  {
	switch(strtoupper(mb_substr($moisrepub,0,3)))
		{
		case "VEN" : 
		switch(strtoupper(mb_substr($moisrepub,0,4)))
			{		
			case "VEND" : 
				$MM = "01"; $MC = "Vend"; break;
			case "VENT" : 
				$MM = "06"; $MC = "Vent"; break;
			}
			break;
		case "BRU" : 
			$MM = "02"; $MC = "Brum"; break;
		case "FRI" : 
			$MM = "03"; $MC = "Frim"; break;
		case "NIV" : 
			$MM = "04"; $MC = "Nivo"; break;
		case "PLU" : 
			$MM = "05"; $MC = "Pluv"; break;
		case "GER" : 
			$MM = "07"; $MC = "Germ"; break;
		case "FLO" : 
			$MM = "08"; $MC = "Flor"; break;
		case "PRA" : 
			$MM = "09"; $MC = "Prai"; break;
		case "MES" : 
			$MM = "10"; $MC = "Mess"; break;
		case "TER" : 
		case "THE" : 
			$MM = "11"; $MC = "Ther"; break;
		case "FRU" : 
			$MM = "12"; $MC = "Fruc"; break;
		case "COM" : 
			$MM = "13"; $MC = "Comp"; break;
		default:
			$MM = "00"; $MC = "XXXX"; // si pas reconnu
		}
	if ($typ==0)
	  return $MM;
	 else
	  return $MC;
	}

//------------------------------------------------------------------------------

function chiffres($texte)
	{
	$l = strlen($texte);
	$result = "";
	for ($i=0;$i<$l;$i++)
		if (isin("0123456789",strtoupper($texte[$i]))>=0)
			$result .= $texte[$i];
	return $result;
  }

//------------------------------------------------------------------------------

function anneerepub($anneerepub)
  {
	$l = strlen($anneerepub);
	$annee = "";
	for ($i=0;$i<$l;$i++)
		if (isin("0123456789IVX",strtoupper($anneerepub[$i]))>=0)
			$annee .= $anneerepub[$i];  
	switch(strtoupper($annee))
		{
		case "II" : 
			$MM = "02"; break;
		case "III" : 
			$MM = "03"; break;
		case "IIII" : 
		case "IV" : 
			$MM = "04"; break;
		case "V" : 
			$MM = "05"; break;
		case "VI" : 
			$MM = "06"; break;
		case "VII" : 
			$MM = "07"; break;
		case "VIII" : 
			$MM = "08"; break;
		case "IX" : 
		case "VIIII" : 
			$MM = "09"; break;
		case "X" : 
			$MM = "10"; break;
		case "XI" : 
			$MM = "11"; break;
		case "XII" : 
			$MM = "12"; break;
		default:
			$MM = $annee; // si déjà numérique
		}
	return $MM;
	}

//------------------------------------------------------------------------------

function convert_dt_repub($rj,$rm,$ra) // transforme une date républicaine en date grégorienne
  {
	$nbannee = anneerepub($ra);
	$nojour = ($rj-1) + (moisrepub($rm,0)-1)*30 + ($nbannee-1)*365 + div($nbannee,4);
	$debutrepub = adodb_mktime(0,0,0,9,22,1792);  // 22 sep 1792
	$ladate = $debutrepub + $nojour*86400;
	$reponse = adodb_date("d-m-Y",$ladate);
	return $reponse;
	}

//------------------------------------------------------------------------------

function formate_dt_repub($rj,$rm,$ra) // formate une date républicaine longue en courte
  {
	$nbannee = mb_substr("0".anneerepub($ra),-2,2);
	$moisrep = moisrepub($rm,1);
	$nojour  = mb_substr("0".chiffres($rj),-2,2);
	$reponse = $nojour."/".$moisrep."/".$nbannee;
	return $reponse;
	}
//------------------------------------------------------------------------------

function quelmois($MMM)
  {
	switch(strtoupper($MMM))
		{
		case "JAN" : 
			$MM = "01"; break;
		case "FEV" : 
		case "FEB" : 
			$MM = "02"; break;
		case "MAR" : 
			$MM = "03"; break;
		case "AVR" : 
		case "APR" : 
			$MM = "04"; break;
		case "MAI" : 
		case "MAY" : 
			$MM = "05"; break;
		case "JUN" : 
			$MM = "06"; break;
		case "JUL" : 
			$MM = "07"; break;
		case "AOU" : 
		case "AUG" : 
			$MM = "08"; break;
		case "SEP" : 
			$MM = "09"; break;
		case "OCT" : 
			$MM = "10"; break;
		case "NOV" : 
			$MM = "11"; break;
		case "DEC" : 
			$MM = "12"; break;
		default:
			$MM = "00"; // si pas reconnu
		}
	return $MM;
	}

//------------------------------------------------------------------------------

function listbox_trait($fieldname,$typetrait,$default)
  {
  // Liste des définitions de traitements
  global $trait;
  $mes_trait = $trait[$typetrait];
	echo '<select name="'.$fieldname.'" size="1">'."\n";
	if ($typetrait=="TST" or $typetrait=="NTS") 
	  $mes = "&nbsp;";
	 else
	  $mes = " = ";
	echo '<option '.selected_option(0,$default).'>'.$mes.'</option>'."\n";
	foreach ($mes_trait as $key => $trt)
		{
		echo '<option '.selected_option($key,$default).'>'.$trt.'</option>'."\n";
		}
	echo " </select>\n";
  }

//------------------------------------------------------------------------------

function traitement($indice,$objet,$code)

	{
	global $acte, $data;
	$reponse = "";
	switch ($objet)
		{
		case "SEX": //----------- Traitements du genre -------------------
			switch ($code)
				{
				case "M": // Toujours M
					$reponse = "M";
					break;  
				case "F": // Toujours F
					$reponse = "F";
					break;
				case "?": // Inconnu
					$reponse = "U";
					break;
				case "S": // Détection selon le premier prénom
					$prem_pre = explode(' ', $acte[$indice], 2);
					$sql = "select * from ".EA_DB."_prenom where prenom = '".sql_quote($prem_pre[0])."'";
					$res = EA_sql_query($sql);
					$nb = EA_sql_num_rows($res);
					if ($nb>0)
						$reponse = "F";
					else					
						$reponse = "M";
					break;
				}
			break;
			
		case "TXT": //----------- Traitements des textes -------------------
			switch ($code)
				{
				case "N":  // N si vide
					if (empty($acte[$indice]))
						$reponse = "N";
					 else
					  $reponse = trim($acte[$indice]);
					break;
				case "M":  // en majuscules
					$reponse = strtoupper(trim($acte[$indice]));
					break;
				case "D":  // Premier mot
					$morceaux = explode(' ', $acte[$indice], 2);
					$reponse = $morceaux[0];
					break;
				case "S":  // Sauf le 1er mot
					$morceaux = explode(' ', $acte[$indice], 2);
					$reponse = $morceaux[1];
					break;
				case "F":  // Dernier mot
					$x = strrpos($acte[$indice]," ");
					if ($x===false)							
						$reponse = $acte[$indice];
					 else
					  $reponse = trim(mb_substr($acte[$indice],$x));
					break;
				case "K":  // Sauf dernier mot
					$x = strrpos($acte[$indice]," ");
					if ($x===false)							
						$reponse = "";
					 else
					  $reponse = trim(mb_substr($acte[$indice],0,$x));
					break;
				case "P":  // Contenu des parenthèses
					$x = strpos($acte[$indice],"(");
					if ($x===false)							
						$reponse = "";
					 else
					 	{
						$y = strpos($acte[$indice],")",$x);
						if ($y===false) 
					    $reponse = trim(mb_substr($acte[$indice],$x+1)); // fin
					   else
					    $reponse = trim(mb_substr($acte[$indice],$x+1,$y-$x-1)); 
					  }
					break;
				case "Q":  // Hors des parenthèses
					$x = strrpos($acte[$indice],"(");
					if ($x===false)							
						$reponse = $acte[$indice];
					 else
					 	{
					  $reponse = trim(mb_substr($acte[$indice],0,$x));
						$y = strpos($acte[$indice],")",$x);
						if (!($y===false)) 
					    $reponse = $reponse.' '.trim(mb_substr($acte[$indice],$y+1));
					  }
					break;
				case "V":  // Avant le (dernière) virgule
					$x = strrpos($acte[$indice],",");
					if ($x===false)							
						$reponse = $acte[$indice];
					 else
					  $reponse = trim(mb_substr($acte[$indice],0,$x));
					break;
				case "W":  // Après la (dernière) virgule
					$x = strrpos($acte[$indice],",");
					if ($x===false)							
						$reponse = "";
					 else
					  $reponse = trim(mb_substr($acte[$indice],$x+1));
					break;
				case "A":  // Début jusque majuscules
					$morceaux = explode(' ', $acte[$indice]);
					$fini = false;
					$curMaj = false;
					$reponse = "";
					$i = 0;
					while (!$fini and $i < count($morceaux))
						{
						$preMaj = $curMaj;
						$curMaj = ($morceaux[$i]==strtoupper($morceaux[$i]));
						if ($preMaj and !$curMaj)
						  $fini = true;
						 else
						  $reponse .= " ".$morceaux[$i];
						$i++;
						}
					$reponse = trim($reponse);	
					break;
				case "I":  // Fin après majuscules
					$morceaux = explode(' ', $acte[$indice]);
					$allez = false;
					$curMaj = false;
					$reponse = "";
					$i = 0;
					while ($i < count($morceaux))
						{
						$preMaj = $curMaj;
						$curMaj = ($morceaux[$i]==strtoupper($morceaux[$i]));
						if ($preMaj and !$curMaj)
						  $allez = true;
						if ($allez)  
						  $reponse .= " ".$morceaux[$i];
						$i++;
						}
					$reponse = trim($reponse);	
					break;
				case "C":  // = NOM si (prénom) pas vide
					if (!empty($acte[$indice]))
						$reponse = $data["NOM"];
					break;
				case "R":  // date révolutionnaire en une colonne
					$tdate = explode_date($acte[$indice]);
					if (count($tdate)==4)
						$tdate[2] = $tdate[2].$tdate[3];  // an XI
					if (count($tdate)>=3)
						$reponse = formate_dt_repub($tdate[0],$tdate[1],$tdate[2]);
					 else
					  $reponse = $acte[$indice];  // on ne change rien !!
					break;
				case "T":  // date révolutionnaire en 3 colonnes
					$reponse = formate_dt_repub($acte[$indice],$acte[$indice+1],$acte[$indice+2]);
					break;
				case "+":  // = NOM si (prénom) pas vide
					if (!empty($acte[$indice]))
						$reponse = "+";
					break;
				case "1":  // Plus zone suivante
				case "2":  // Plus 2 zones suivantes
				case "3":  // Plus 3 zones suivantes
				case "4":  // Plus 4 zones suivantes
				case "5":  // Plus 5 zones suivantes
					$reponse = trim($acte[$indice]);
					for ($i=1;$i<=$code;$i++)
						{
						$add = trim($acte[$indice+$i]);
					  if (strlen(trim($reponse))>0 and strlen(trim($add))>0)
					  	$reponse .= ', ';
					  $reponse .= $add;
					  }
					break;
				}
			break;
			
		case "AGE": //----------- Traitements des ages -------------------
		case "NUM": //----------- Traitements des nombres -------------------
			switch ($code)
				{
				case "C": // ne garder que les chiffres et le symboles "/" + séparation par des blancs
				  $l = strlen($acte[$indice]);
				  $reponse = "";
				  $sp = "";
				  for ($i=0;$i<$l;$i++)
				    if (isin("0123456789/-,.",$acte[$indice][$i])>0)
				    	{
				      $reponse .= $sp.$acte[$indice][$i];
				      $sp = "";
				      }
				     else
				      $sp = " ";
				  $reponse = trim($reponse);    
					break;
				case "A": // Réduire les mots à l'initiale
					$avant = array("années","annees","ans","an","mois","semaines","semaine","jours","jrs","jr",","," ");
					$apres = array("a",     "a",     "a",  "a", "m",   "s",       "s",      "j",    "j",  "j" ,"" ,"");
				  $reponse = str_replace($avant, $apres, strtolower($acte[$indice]));
					break;
				}
			break;
			
		case "DAT": //----------- Traitements des dates -------------------
		 						// attention 
			switch ($code)
				{
				case "I":  // Inverse la date de AAAA MM JJ vers JJ MM AAAA avec séparateurs espace / . -
					$tdate = explode_date($acte[$indice]);
					if (count($tdate)==3)
						$reponse = 	trim($tdate[2]).'-'.trim($tdate[1]).'-'.trim($tdate[0]);
					 else
					  $reponse = $acte[$indice];  // on ne change rien !!
					break;
				case "S": 
					$tdate = explode_date($acte[$indice]);
					if (count($tdate)==3)
						$reponse = 	trim($tdate[0]).'-'.trim($tdate[1]).'-'.trim($tdate[2]);
					 else
					  $reponse = $acte[$indice];  // on ne change rien !!
					break;
				case "C":  // JJMMAAAA
				  $ladate = mb_substr('00000000'.$acte[$indice],-8,8);
					$JJ=mb_substr($ladate,0,2);
					$MM=mb_substr($ladate,2,2);
					$AAAA=mb_substr($ladate,4,4);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "J":  // AAAAMMJJ
					$JJ=mb_substr($acte[$indice],6,2);
					$MM=mb_substr($acte[$indice],4,2);
					$AAAA=mb_substr($acte[$indice],0,4);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "T":  // JJMMMAAAA
					$JJ=mb_substr($acte[$indice],0,2);
					$MMM=mb_substr($acte[$indice],2,3);
					$AAAA=mb_substr($acte[$indice],5,4);
					$MM = quelmois($MMM);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "U":  // AAAAMMMJJ
					$JJ=mb_substr($acte[$indice],7,2);
					$MMM=mb_substr($acte[$indice],4,3);
					$AAAA=mb_substr($acte[$indice],0,4);
					$MM = quelmois($MMM);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "M":  // 3 colonnes contigues JJ puis MM puis AAAA
					$JJ = trim($acte[$indice]);
					$MM = trim($acte[$indice+1]);
					$AAAA=trim($acte[$indice+2]);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "R":  // 3 colonnes contigues AAAA puis MM puis JJ
					$JJ = trim($acte[$indice+2]);
					$MM = trim($acte[$indice+1]);
					$AAAA=trim($acte[$indice]);
					$reponse = $JJ.'-'.$MM.'-'.$AAAA;
					break;
				case "A":  // date révolutionnaire en une colonne
					$tdate = explode_date($acte[$indice]);
					if (count($tdate)==4)
						$tdate[2] = $tdate[2].$tdate[3];  // an XI
					if (count($tdate)>=3)
						$reponse = convert_dt_repub($tdate[0],$tdate[1],$tdate[2]);
					 else
					  $reponse = $acte[$indice];  // on ne change rien !!
					break;
				case "B":  // date révolutionnaire en 3 colonnes
					$reponse = convert_dt_repub($acte[$indice],$acte[$indice+1],$acte[$indice+2]);
					break;
				}
			//$reponse = ajuste_date($reponse,$ladate="",$MauvaiseAnnee=0);
			break;	
		}
  return $reponse;		
	}

//------------------------------------------------------------------------------

function comparer($comp1,$comp2,$code)

	{
	global $trait; 
	//----------- Comparaison pour filtre PHP dans chargeCSV-------------------
	$lstrepub = array("VEN","BRU","FRI","NIV","GER","FLO","PRA","MES","TER","THE","FRU","COM"); 
	$reponse = "";
	switch ($code)
		{
		case "=": 
		case ">": 
		case "<": 
		case "D": // différent de
			$code = '$reponse = '."('".$comp1."'".$trait['TST'][$code]."'".$comp2."');";
			//echo $code;
			eval($code);
			break;
		case "C": // Contient
			$reponse = (isin($comp1,$comp2)>=0);
			break;
		case "P": // Contient pas
			$reponse = (isin($comp1,$comp2)<0);
			break;
		case "V": // est vide
			$reponse = (strlen(trim($comp1))==0);
			break;
		case "N": // est pas vide
			$reponse = (strlen(trim($comp1))>0);
			break;
		case "R": // date républicaine
			$i=0;
			$comp = strtoupper($comp1);
			while ($i<12 and strpos($comp,$lstrepub[$i])===false)
				{ $i++; }
			$reponse = ($i<12);
			break;
		case "G": // pas date républicaine  donc problement grégorienne
			$i=0;
			$comp = strtoupper($comp1);
			while ($i<12 and strpos($comp,$lstrepub[$i])===false)
				{ $i++; }
			$reponse = ($i==12);
			break;
		}
  return $reponse;		
	}

//------------------------------------------------------------------------------

function comparerSQL($var,$comp,$code)

	{
	global $trait; 
	//----------- Comparaison pour filtre SQL -------------------
	$reponse = "";

	switch ($code)
		{
		case "=": 
		  $reponse = $var." = '".$comp."' ";
			break;
		case ">": 
		case "<": 
		case "D": // différent de
		  if (!is_numeric($comp)) $comp = "'".$comp."'"; 
		  $reponse = $var.$trait['TST'][$code].$comp;  
			break;
		case "C": // Contient
		  $reponse = $var." like '%".$comp."%' ";
			break;
		case "P": // Contient pas
		  $reponse = "not (".$var." like '%".$comp."%') ";
			break;
		case "V": // est vide
			$reponse = "length(trim(".$var."))=0";
			break;
		case "N": // est pas vide
			$reponse = "length(trim(".$var."))>0";
			break;
		}
  return $reponse;		
	}

//------------------------------------------------------------------------------

?>