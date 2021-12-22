<?php
// Définition des champs de la base de données 

// nom_zone, groupe, taille, usage(minus=obligatoire), label
$mdb = array(
  array("NOM",  "I1", "25", "nmdv", "Nom", "TXT"),
  array("PRE",  "I1", "30", "NMDV", "Prénom", "TXT"),
  array("ORI",  "I1", "35", "DMV", "Origine", "TXT"),
  array("DNAIS", "I1", "10", "MDV", "Date de naissance", "DAT"),
  array("SEXE", "I1", "1", "NDV", "Sexe", "SEX"),
  array("AGE",  "I1", "8", "MDV", "Age", "AGE"),
  array("PRO",  "I1", "30", "MDV", "Profession", "TXT"),
  array("EXCON", "I1", "50", "MV", "Ex-conjoint", "TXT"),
  array("COM",  "I1", "70", "NMDV", "Commentaire", "TXT"),
  array("P_NOM", "P1", "25", "NMDV", "Nom", "TXT"),
  array("P_PRE", "P1", "30", "NMDV", "Prénom", "TXT"),
  array("P_COM", "P1", "70", "NMDV", "Commentaire", "TXT"),
  array("P_PRO", "P1", "30", "NMV", "Profession", "TXT"),
  array("M_NOM", "M1", "25", "NMDV", "Nom", "TXT"),
  array("M_PRE", "M1", "30", "NMDV", "Prénom", "TXT"),
  array("M_COM", "M1", "70", "NMDV", "Commentaire", "TXT"),
  array("C_NOM", "C2", "25", "mDV", "Nom", "TXT"),
  array("C_PRE", "C2", "30", "MDV", "Prénom", "TXT"),
  array("C_SEXE", "C2", "1", "V",  "Sexe", "SEX"),
  array("C_ORI", "C2", "30", "MV", "Origine", "TXT"),
  array("C_DNAIS", "C2", "10", "MV", "Date de naissance", "DAT"),
  array("C_AGE",  "C2", "8", "MV", "Age", "AGE"),
  array("C_PRO",  "C2", "30", "MV", "Profession", "TXT"),
  array("C_EXCON", "C2", "50", "MV", "Ex-conjoint", "TXT"),
  array("C_COM", "C2", "70", "MDV", "Commentaire", "TXT"),
  array("CP_NOM", "P2", "25", "MV", "Nom", "TXT"),
  array("CP_PRE", "P2", "30", "MV", "Prénom", "TXT"),
  array("CP_COM", "P2", "70", "MV", "Commentaire", "TXT"),
  array("CP_PRO", "P2", "30", "MV", "Profession", "TXT"),
  array("CM_NOM", "M2", "25", "MV", "Nom", "TXT"),
  array("CM_PRE", "M2", "30", "MV", "Prénom", "TXT"),
  array("CM_COM", "M2", "70", "MV", "Commentaire", "TXT"),
  array("T1_NOM", "T0", "25", "NMDV", "Nom", "TXT"),
  array("T1_PRE", "T0", "30", "NMDV", "Prénom", "TXT"),
  array("T1_COM", "T0", "70", "NMDV", "Commentaire", "TXT"),
  array("T2_NOM", "T0", "25", "NMDV", "Nom", "TXT"),
  array("T2_PRE", "T0", "30", "NMDV", "Prénom", "TXT"),
  array("T2_COM", "T0", "70", "NMDV", "Commentaire", "TXT"),
  array("T3_NOM", "T0", "25", "MV", "Nom", "TXT"),
  array("T3_PRE", "T0", "30", "MV", "Prénom", "TXT"),
  array("T3_COM", "T0", "70", "MV", "Commentaire", "TXT"),
  array("T4_NOM", "T0", "25", "MV", "Nom", "TXT"),
  array("T4_PRE", "T0", "30", "MV", "Prénom", "TXT"),
  array("T4_COM", "T0", "70", "MV", "Commentaire", "TXT"),
  array("LIBELLE", "A0", "50", "V", "Type de document", "TXT"),
  array("COTE",   "A0", "20", "NMDV", "Cote", "TXT"),
  array("LIBRE",  "A0", "50", "NMDV", "Libre", "TXT"),
  array("DATETXT", "A0", "10", "nmdv", "Date de l'acte", "DAT"),
  array("DREPUB", "A0", "10", "NMDV", "Date républicaine", "TXT"),
  array("COMGEN", "A0", "500", "NMDV", "Commentaire général", "TXT")
);

// Définition des groupes de champs

$lib = array(
  "A" => array("N" => "Acte", "D" => "Acte", "M" => "Acte", "V" => "Document"),
  "I" => array("N" => "Nouveau-né", "D" => "Défunt", "M" => "Epoux", "V" => "Intervenant 1"),
  "C" => array("N" => "x", "D" => "Conjoint", "M" => "Epouse", "V" => "Intervenant 2"),
  "P" => array("N" => "Père", "D" => "Père", "M" => "Père", "V" => "Père"),
  "M" => array("N" => "Mère", "D" => "Mère", "M" => "Mère", "V" => "Mère"),
  "T" => array("N" => "Témoins/Parrain/Marraine", "D" => "Témoins", "M" => "Témoins", "V" => "Témoins")
);

//------------------------------------------------------------------------------
