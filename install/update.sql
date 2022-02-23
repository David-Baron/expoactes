
// Mise à jour 2.2
#ADDTABLE;EA_DB_traceip;-;-
@Création table de suivi des adresses IP
>CREATE TABLE EA_DB_traceip (id INT(10) NOT NULL auto_increment, ua VARCHAR(255) NOT NULL default '', ip VARCHAR(50)  NOT NULL default '', login VARCHAR(15) NULL, datetime INT(10) NOT NULL, cpt      INT(11) NOT NULL default 0,locked SMALLINT(1)  NOT NULL default 0, PRIMARY KEY  (id), KEY ip (ip));

// Mise à jour 3.1
#ADDTABLE;EA_DB_geoloc;-;-
@Création table cache des géolocalisations
>CREATE TABLE IF NOT EXISTS EA_DB_geoloc (ID int(11) NOT NULL AUTO_INCREMENT, COMMUNE varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '', DEPART varchar(40) COLLATE latin1_general_ci NOT NULL DEFAULT '', LON float DEFAULT NULL, LAT float DEFAULT NULL, STATUT varchar(1) COLLATE latin1_general_ci NOT NULL DEFAULT 'N',   NOTE_N text COLLATE latin1_general_ci, NOTE_M text COLLATE latin1_general_ci, NOTE_D text COLLATE latin1_general_ci, NOTE_V text COLLATE latin1_general_ci, PRIMARY KEY  (id), UNIQUE KEY `COMMUNE` (`COMMUNE`,`DEPART`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;


// Mise à jour 3.1.2
#ALWAYS;-;-;-
@Mise à F des cases vides dans metadb
>UPDATE EA_DB_metadb SET affich = 'F' WHERE affich = '';

#ADDCOL;EA_DB_traceip;login;-
@Ajout Login dans traceip
>ALTER TABLE EA_DB_traceip ADD login VARCHAR(15) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL AFTER ip; 
