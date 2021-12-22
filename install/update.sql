// Intervention sur les tables de la base à partir d'une version 3.2.2

// Mise à jour 3.2.3
#ALWAYS;-;-;-
@Positionne le défaut des champs IDNIM à 0 au lieu de 'NULL' et met ce champ à 0 s'il est à 'NULL'
>ALTER TABLE EA_DB_nai3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_nai3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_dec3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_dec3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_mar3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_mar3 SET IDNIM = 0 WHERE IDNIM  IS NULL;
>ALTER TABLE EA_DB_div3 change column IDNIM IDNIM int(11) DEFAULT 0;
>UPDATE EA_DB_div3 SET IDNIM = 0 WHERE IDNIM  IS NULL;

#ALWAYS;-;-;-
@Positionne le défaut des champs LADATE, DTDEPOT, DTMODIF à '1001-01-01' au lieu de '0000-00-00' et met ce champ à '1001-01-01' s'il est à '0000-00-00'
>ALTER TABLE EA_DB_nai3 change change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_nai3 SET LADATE = '1001-01-01' WHERE LADATE = '0000-00-00';
>UPDATE EA_DB_nai3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT = '0000-00-00';
>UPDATE EA_DB_nai3 SET DTMODIF = '1001-01-01' WHERE DTMODIF = '0000-00-00';
>ALTER TABLE EA_DB_dec3 change change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_dec3 SET LADATE = '1001-01-01' WHERE LADATE = '0000-00-00';
>UPDATE EA_DB_dec3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT = '0000-00-00';
>UPDATE EA_DB_dec3 SET DTMODIF = '1001-01-01' WHERE DTMODIF = '0000-00-00';
>ALTER TABLE EA_DB_mar3 change change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_mar3 SET LADATE = '1001-01-01' WHERE LADATE = '0000-00-00';
>UPDATE EA_DB_mar3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT = '0000-00-00';
>UPDATE EA_DB_mar3 SET DTMODIF = '1001-01-01' WHERE DTMODIF = '0000-00-00';
>ALTER TABLE EA_DB_div3 change change column LADATE LADATE date DEFAULT '1001-01-01', change column DTDEPOT DTDEPOT date DEFAULT '1001-01-01', change column DTMODIF DTMODIF date DEFAULT '1001-01-01';
>UPDATE EA_DB_div3 SET LADATE = '1001-01-01' WHERE LADATE = '0000-00-00';
>UPDATE EA_DB_div3 SET DTDEPOT = '1001-01-01' WHERE DTDEPOT = '0000-00-00';
>UPDATE EA_DB_div3 SET DTMODIF = '1001-01-01' WHERE DTMODIF = '0000-00-00';

#ALWAYS;-;-;-
@Positionne le défaut du champ 'maj_solde' à '1001-01-01' au lieu de '0000-00-00' et met ce champ à '1001-01-01' s'il est à '0000-00-00'
>ALTER TABLE EA_DB_log change change column `maj_solde` `maj_solde` date DEFAULT '1001-01-01';
>UPDATE EA_DB_log SET maj_solde = '1001-01-01' WHERE maj_solde = '0000-00-00';

#ALWAYS;-;-;-
@Positionne le défaut du champ 'date' à '1001-01-01 00:00:00' au lieu de '0000-00-00 00:00:00' et met ce champ à '1001-01-01 00:00:00' s'il est à '0000-00-00 00:00:00'
>ALTER TABLE EA_DB_log change change column `date` `date` datetime DEFAULT '1001-01-01 00:00:00';
>UPDATE EA_DB_log SET `date` = '1001-01-01 00:00:00' WHERE `date` = '0000-00-00 00:00:00';

