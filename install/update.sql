// Intervention sur les tables de la base à partir d'une version 3.2.2

// Mise à jour 3.2.3
#ALWAYS;-;-;-
@Positionne le défaut des champs LADATE, DTDEPOT, DTMODIF à '1001-01-01' au lieu de '0000-00-00' et met ce champ à '1001-01-01' s'il est < à '1000-01-01' NMDV
>UPDATE EA_DB_nai3 SET LADATE = '1001-01-01' WHERE LADATE < '1000-01-01';
>UPDATE EA_DB_nai3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT < '1000-01-01';
>UPDATE EA_DB_nai3 SET DTMODIF = '1001-01-01' WHERE DTMODIF < '1000-01-01';
>ALTER TABLE EA_DB_nai3 change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_dec3 SET LADATE = '1001-01-01' WHERE LADATE < '1000-01-01';
>UPDATE EA_DB_dec3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT < '1000-01-01';
>UPDATE EA_DB_dec3 SET DTMODIF = '1001-01-01' WHERE DTMODIF < '1000-01-01';
>ALTER TABLE EA_DB_dec3 change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_mar3 SET LADATE = '1001-01-01' WHERE LADATE < '1000-01-01';
>UPDATE EA_DB_mar3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT < '1000-01-01';
>UPDATE EA_DB_mar3 SET DTMODIF = '1001-01-01' WHERE DTMODIF < '1000-01-01';
>ALTER TABLE EA_DB_mar3 change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_div3 SET LADATE = '1001-01-01' WHERE LADATE < '1000-01-01';
>UPDATE EA_DB_div3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT < '1000-01-01';
>UPDATE EA_DB_div3 SET DTMODIF = '1001-01-01' WHERE DTMODIF < '1000-01-01';
>ALTER TABLE EA_DB_div3 change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';

#ALWAYS;-;-;-
@Positionne le défaut des champs IDNIM à 0 au lieu de 'NULL' et met ce champ à 0 s'il est à 'NULL' NMDV
>ALTER TABLE EA_DB_nai3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_nai3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_dec3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_dec3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_mar3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_mar3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_div3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_div3 SET IDNIM = 0 WHERE IDNIM  IS NULL;

#ALWAYS;-;-;-
@Positionne le défaut du champ EA_DB_user3 'maj_solde' à '1001-01-01' au lieu de '0000-00-00' et met ce champ à '1001-01-01' s'il est < à '1000-01-01'
>UPDATE EA_DB_user3 SET maj_solde = '1001-01-01' WHERE maj_solde < '1000-01-01';
>ALTER TABLE EA_DB_user3 change column `maj_solde` `maj_solde` date DEFAULT '1001-01-01';

#ALWAYS;-;-;-
@Positionne le défaut du champ EA_DB_log 'date' à '1001-01-01 00:00:00' au lieu de '0000-00-00 00:00:00' et met ce champ à '1001-01-01 00:00:00' s'il est < à '1000-01-01 00:00:00'
>UPDATE EA_DB_log SET `date` = '1001-01-01 00:00:00' WHERE `date` < '1000-01-01 00:00:00';
>ALTER TABLE EA_DB_log change column `date` `date` datetime DEFAULT '1001-01-01 00:00:00';

// Alignement des choix AFFICH sur tous les types d'actes cf "creetables.sql"
#ALWAYS;-;-;-
@Positionne dans metadb, le champ AFFICH de CODCOM Naissance identique à CODCOM Deces soit F
>UPDATE EA_DB_metadb mytb INNER JOIN EA_DB_metadb ref ON ( ref.zid = '3002') SET mytb.affich = ref.affich WHERE mytb.ZID='1002';

#ALWAYS;-;-;-
@Positionne dans metadb, le champ AFFICH de CODCOM Divers identique à CODCOM Deces soit F
>UPDATE EA_DB_metadb mytb INNER JOIN EA_DB_metadb ref ON ( ref.zid = '3002') SET mytb.affich = ref.affich WHERE mytb.ZID='4002';

#ALWAYS;-;-;-
@Positionne dans metadb, le champ AFFICH de CODDEP Divers identique à CODDEP Deces soit M
>UPDATE EA_DB_metadb mytb INNER JOIN EA_DB_metadb ref ON ( ref.zid = '3004') SET mytb.affich = ref.affich WHERE mytb.ZID='4004';

#ALWAYS;-;-;-
@Positionne dans metadb, le champ AFFICH de IDNIM Divers identique à IDNIM Deces soit M pour v3 (T pour les install v3 depuis v2)
>UPDATE EA_DB_metadb mytb INNER JOIN EA_DB_metadb ref ON ( ref.zid = '3038') SET mytb.affich = ref.affich WHERE mytb.ZID='4064';

// FIN Mise à jour 3.2.3
