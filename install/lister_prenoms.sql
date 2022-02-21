drop table if exists EA_DB_prenom_local;
create table EA_DB_prenom_local as SELECT distinct left(PRE,instr(PRE,' ')) as prenom FROM `EA_DB_nai` where SEXE = 'F' order by prenom;