
CREATE TABLE IF NOT EXISTS EA_DB_nai3 (
  BIDON varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  CODCOM varchar(12) COLLATE latin1_general_ci DEFAULT NULL,
  COMMUNE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  CODDEP varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DEPART varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  TYPACT varchar(1) COLLATE latin1_general_ci DEFAULT 'N',
  DATETXT varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DREPUB varchar(25) COLLATE latin1_general_ci DEFAULT NULL,
  COTE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  LIBRE varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  SEXE char(1) COLLATE latin1_general_ci DEFAULT NULL,
  COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  P_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T1_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T2_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T2_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T2_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  COMGEN text COLLATE latin1_general_ci,
  IDNIM int(11) DEFAULT NULL,
  PHOTOS text COLLATE latin1_general_ci,
  LADATE date DEFAULT '0000-00-00',
  ID int(11) NOT NULL AUTO_INCREMENT,
  DEPOSANT int(11) DEFAULT NULL,
  PHOTOGRA varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  RELEVEUR varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  VERIFIEU varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  DTDEPOT date DEFAULT '0000-00-00',
  DTMODIF date DEFAULT '0000-00-00',
  PRIMARY KEY (ID),
  KEY LADATE (LADATE),
  KEY IDNIM (IDNIM),
  KEY NOM (NOM(10)),
  KEY P_NOM (P_NOM(10)),
  KEY M_NOM (M_NOM(10)),
  KEY COM_DEP (COMMUNE(10),DEPART(4))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE IF NOT EXISTS EA_DB_dec3 (
  BIDON varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  CODCOM varchar(12) COLLATE latin1_general_ci DEFAULT NULL,
  COMMUNE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  CODDEP varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DEPART varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  TYPACT varchar(1) COLLATE latin1_general_ci DEFAULT 'D',
  DATETXT varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DREPUB varchar(25) COLLATE latin1_general_ci DEFAULT NULL,
  COTE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  LIBRE varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  ORI varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  DNAIS varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  SEXE char(1) COLLATE latin1_general_ci DEFAULT NULL,
  AGE varchar(15) COLLATE latin1_general_ci DEFAULT NULL,
  COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRO varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  P_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  P_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T1_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T2_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T2_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T2_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  COMGEN text COLLATE latin1_general_ci,
  IDNIM int(11) DEFAULT NULL,
  PHOTOS text COLLATE latin1_general_ci,
  LADATE date DEFAULT '0000-00-00',
  ID int(11) NOT NULL AUTO_INCREMENT,
  DEPOSANT int(11) DEFAULT NULL,
  PHOTOGRA varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  RELEVEUR varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  VERIFIEU varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  DTDEPOT date DEFAULT '0000-00-00',
  DTMODIF date DEFAULT '0000-00-00',
  PRIMARY KEY (ID),
  KEY LADATE (LADATE),
  KEY IDNIM (IDNIM),
  KEY COM_DEP (COMMUNE(10),DEPART(4)),
  KEY NOM (NOM(10)),
  KEY C_NOM (C_NOM(10)),
  KEY P_NOM (P_NOM(10)),
  KEY M_NOM (M_NOM(10)),
  KEY ORI (ORI(12))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE IF NOT EXISTS EA_DB_div3 (
  BIDON varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  CODCOM varchar(12) COLLATE latin1_general_ci DEFAULT NULL,
  COMMUNE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  CODDEP varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DEPART varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  TYPACT varchar(1) COLLATE latin1_general_ci DEFAULT 'V',
  DATETXT varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DREPUB varchar(25) COLLATE latin1_general_ci DEFAULT NULL,
  COTE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  LIBRE varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  SIGLE varchar(5) COLLATE latin1_general_ci DEFAULT NULL,
  LIBELLE varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  SEXE char(1) COLLATE latin1_general_ci DEFAULT NULL,
  ORI varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  DNAIS varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  AGE varchar(15) COLLATE latin1_general_ci DEFAULT NULL,
  COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  EXCON varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  EXC_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  EXC_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  P_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_SEXE char(1) COLLATE latin1_general_ci DEFAULT NULL,
  C_ORI varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_DNAIS varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  C_AGE varchar(8) COLLATE latin1_general_ci DEFAULT NULL,
  C_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRO varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  C_EXCON varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  C_X_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_X_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CP_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  CP_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CP_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CP_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CM_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  CM_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CM_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CM_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T1_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T2_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T2_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T2_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T3_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T3_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T3_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T4_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T4_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T4_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  COMGEN text COLLATE latin1_general_ci,
  IDNIM int(11) DEFAULT NULL,
  PHOTOS text COLLATE latin1_general_ci,
  LADATE date DEFAULT '0000-00-00',
  ID int(11) NOT NULL AUTO_INCREMENT,
  DEPOSANT int(11) DEFAULT NULL,
  PHOTOGRA varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  RELEVEUR varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  VERIFIEU varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  DTDEPOT date DEFAULT '0000-00-00',
  DTMODIF date DEFAULT '0000-00-00',
  PRIMARY KEY (ID),
  KEY LADATE (LADATE),
  KEY IDNIM (IDNIM),
  KEY COM_DEP_LIB (COMMUNE(10),DEPART(4),LIBELLE(12)),
  KEY NOM (NOM(10)),
  KEY C_NOM (C_NOM(10)),
  KEY P_NOM (P_NOM(10)),
  KEY M_NOM (M_NOM(10)),
  KEY CP_NOM (CP_NOM(10)),
  KEY CM_NOM (CM_NOM(10)),
  KEY ORI (ORI(12)),
  KEY C_ORI (C_ORI(12)),
  FULLTEXT KEY PHOTOS (PHOTOS)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE IF NOT EXISTS EA_DB_mar3 (
  BIDON varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  CODCOM varchar(12) COLLATE latin1_general_ci DEFAULT NULL,
  COMMUNE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  CODDEP varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DEPART varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  TYPACT varchar(1) COLLATE latin1_general_ci DEFAULT 'M',
  DATETXT varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  DREPUB varchar(25) COLLATE latin1_general_ci DEFAULT NULL,
  COTE varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  LIBRE varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  ORI varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  DNAIS varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  AGE varchar(15) COLLATE latin1_general_ci DEFAULT NULL,
  COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  EXCON varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  EXC_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  EXC_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  P_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  P_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  M_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  M_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_ORI varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_DNAIS varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  C_AGE varchar(8) COLLATE latin1_general_ci DEFAULT NULL,
  C_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  C_PRO varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  C_EXCON varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  C_X_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  C_X_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CP_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  CP_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CP_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CP_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CM_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  CM_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  CM_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  CM_PRO varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T1_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T1_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T2_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T2_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T2_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T3_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T3_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T3_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  T4_NOM varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  T4_PRE varchar(35) COLLATE latin1_general_ci DEFAULT NULL,
  T4_COM varchar(70) COLLATE latin1_general_ci DEFAULT NULL,
  COMGEN text COLLATE latin1_general_ci,
  IDNIM int(11) DEFAULT NULL,
  PHOTOS text COLLATE latin1_general_ci,
  LADATE date DEFAULT '0000-00-00',
  ID int(11) NOT NULL AUTO_INCREMENT,
  DEPOSANT int(11) DEFAULT NULL,
  PHOTOGRA varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  RELEVEUR varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  VERIFIEU varchar(40) COLLATE latin1_general_ci DEFAULT NULL,
  DTDEPOT date DEFAULT '0000-00-00',
  DTMODIF date DEFAULT '0000-00-00',
  PRIMARY KEY (ID),
  KEY LADATE (LADATE),
  KEY IDNIM (IDNIM),
  KEY COM_DEP (COMMUNE(10),DEPART(4)),
  KEY NOM (NOM(10)),
  KEY C_NOM (C_NOM(10)),
  KEY P_NOM (P_NOM(10)),
  KEY M_NOM (M_NOM(10)),
  KEY CP_NOM (CP_NOM(10)),
  KEY CM_NOM (CM_NOM(10)),
  KEY ORI (ORI(12)),
  KEY C_ORI (C_ORI(12))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


CREATE TABLE IF NOT EXISTS EA_DB_user3 (
  login varchar(15) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  hashpass varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  nom varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  prenom varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  email varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `level` int(11) DEFAULT '1',
  regime int(11) DEFAULT '0',
  solde int(11) DEFAULT '0',
  maj_solde date DEFAULT '0000-00-00',
  statut varchar(1) COLLATE latin1_general_ci NOT NULL DEFAULT 'N',
  dtcreation date DEFAULT NULL,
  dtexpiration date DEFAULT '2033-12-31',
  pt_conso int(11) NOT NULL DEFAULT '0',
  REM varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  libre varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  ID int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
