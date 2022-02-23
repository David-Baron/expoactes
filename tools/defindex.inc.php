<?php
// Définition des index utilisables sr la base de données

// table, nom_index, cle, type, multi, obligatoire
$idx = array (
  array ("nai3","LADATE",  "LADATE",              "BT","1","1","Date de l'acte"),
  array ("nai3","IDNIM",   "IDNIM",               "BT","1","1","Identifiant Nimegue"),
  array ("nai3","COM_DEP", "COMMUNE(10),DEPART(4)","BT","2","1","Commune et département"),
  array ("nai3","NOM",     "NOM(10)",             "BT","1","1","Patronyme nouveau-né"),
  array ("nai3","P_NOM",   "P_NOM(10)",           "BT","1","0","Patronyme du père"),
  array ("nai3","M_NOM",   "M_NOM(10)",           "BT","1","0","Patronyme de la mère"),

  array ("mar3","LADATE",  "LADATE",              "BT","1","1","Date de l'acte"),
  array ("mar3","IDNIM",   "IDNIM",               "BT","1","1","Identifiant Nimegue"),
  array ("mar3","COM_DEP", "COMMUNE(10),DEPART(4)","BT","2","1","Commune et département"),
  array ("mar3","NOM",     "NOM(10)",             "BT","1","1","Patronyme époux"),
  array ("mar3","C_NOM",   "C_NOM(10)",           "BT","1","0","Patronyme épouse"),
  array ("mar3","P_NOM",   "P_NOM(10)",           "BT","1","0","Patronyme du père de l'époux"),
  array ("mar3","M_NOM",   "M_NOM(10)",           "BT","1","0","Patronyme de la mère de l'époux"),
  array ("mar3","CP_NOM",  "CP_NOM(10)",          "BT","1","0","Patronyme du père de l'épouse"),
  array ("mar3","CM_NOM",  "CM_NOM(10)",          "BT","1","0","Patronyme de la mère de l'épouse"),
  array ("mar3","ORI",     "ORI(12)",             "BT","1","0","Origine de l'époux"),
  array ("mar3","C_ORI",   "C_ORI(12)",           "BT","1","0","Origine de l'épouse"),

  array ("dec3","LADATE",  "LADATE",              "BT","1","1","Date de l'acte"),
  array ("dec3","IDNIM",   "IDNIM",               "BT","1","1","Identifiant Nimegue"),
  array ("dec3","COM_DEP", "COMMUNE(10),DEPART(4)","BT","2","1","Commune et département"),
  array ("dec3","NOM",     "NOM(10)",             "BT","1","1","Patronyme du décédé"),
  array ("dec3","C_NOM",   "C_NOM(10)",           "BT","1","0","Patronyme du conjoint"),
  array ("dec3","P_NOM",   "P_NOM(10)",           "BT","1","0","Patronyme du père"),
  array ("dec3","M_NOM",   "M_NOM(10)",           "BT","1","0","Patronyme de la mère"),
  array ("dec3","ORI",     "ORI(12)",             "BT","1","0","Origine du décédé"),

  array ("div3","LADATE",  "LADATE",              "BT","1","1","Date de l'acte"),
  array ("div3","IDNIM",   "IDNIM",               "BT","1","1","Identifiant Nimegue"),
  array ("div3","COM_DEP_LIB","COMMUNE(10),DEPART(4),LIBELLE(12)","BT","2","1","Commune et départ. & libellé"),
  array ("div3","NOM",     "NOM(10)",             "BT","1","1","Patronyme du 1er intervenant"),
  array ("div3","C_NOM",   "C_NOM(10)",           "BT","1","0","Patronyme du 2d intervenant"),
  array ("div3","P_NOM",   "P_NOM(10)",           "BT","1","0","Patronyme du père du 1er it."),
  array ("div3","M_NOM",   "M_NOM(10)",           "BT","1","0","Patronyme de la mère du 1er it."),
  array ("div3","CP_NOM",  "CP_NOM(10)",          "BT","1","0","Patronyme du père du 2d it."),
  array ("div3","CM_NOM",  "CM_NOM(10)",          "BT","1","0","Patronyme de la mère du 2d it."),
  array ("div3","ORI",     "ORI(12)",             "BT","1","0","Origine du 1er intervanant"),
  array ("div3","C_ORI",   "C_ORI(12)",           "BT","1","0","Origine du 2d intervanant"),
  );

//------------------------------------------------------------------------------


?>