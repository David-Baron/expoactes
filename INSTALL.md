# Installation

Quelque soit l'emplacement du serveur, local ou distant, et la machine qui permet de le faire (sous Windows, MacOS ou GNU/Linux), une installation ou une mise à jour se fait toujours dans l'ordre :

- Récupérer l'archive.
- La décompresser.
- Pousser l'arborescence actes/ en place définitive.
- Déclencher la configuration sur le serveur.

Maintenant avec un peu plus de détails : 


### Mise à jour

1. Mise en mode maintenance : "Administrer le logiciel/Etat serveur" -> 'Basculer en mode MAINTENANCE' (nécessite code et mdp admin EA).

2. Sauvegarder la base de données (au minimum toutes les tables commençant par 'act' suivi d'un blanc souligné) : Sauvegarde de l'actuelle BD : "Administrer les données/Backup" pour chaque type d'acte (donc 4) (nécessite code et mdp admin EA).

3. Recopie récursive de tout l'espace EA, c'est-à-dire l'arborescence existante (exemple "VotreDossier/" en "actes_sauvegarde/" par exemple), si possible sur place sur le serveur pour des questions de rapidité.
        Sinon par FTP, avec FileZilla par exemple, (nécessite code et mdp FTP) pour la mettre sur votre PC.
        Cela intègre donc les fichiers de sauvegarde de l'étape 2 (dossier _backup/backup*.bea...)
        Ainsi un retour en arrière sera possible le cas échéant.

4. Récupérer la dernière version d'ExpoActes : "https://expoactes.monrezo.be/download.htm" puis le dézipper en local.

5. Transférer par FTP l'arborescence complète "actes/" vers le serveur en prenant bien soin de travailler au même niveau de dossier (VotreDossier). (nécessite code et mdp FTP).

6. Exécuter dans votre butineur le code résidant à l'adresse VotreDomaine/VotreDossier/install/update.php (nécessite code et mdp d'un administrateur EA).
        Le programme utilise les informations de connexion du fichier "&#95;config/connect.inc.php" sauf si la connexion à la base de données échoue dans ce cas il appelle le configurateur en reprenant les informations du fichier et crée un fichier de configuration "BD-[nomserveur]-connect.inc.php".
        L'étape suivante est la mise à jour de la BD. Cette opération peut prendre quelque temps, et il est actuellement (version 3.2.4) nécessaire de les enchaîner manuellement.
        Si problèmes détectés, faire une copie d'écran de l'erreur, restaurer vos tables ExpoActes, renommer votre arborescence actes en actes-ko et renommer "actes_sauvegarde" en "VotreDossier". Vous devriez de nouveau être opérationnel. Finalement, faites parvenir votre copie d'écran sur la liste de diffusion pour obtenir de l'aide.

7. Sortir du mode maintenance (nécessite code et mdp admin EA).

Les opérations 1,2,3,7 peuvent être faites dans un premier temps pour déjà assurer de les maîtriser.

A noter : cette version permet de gérer des modèles utilisables lors des chargements de fichiers CSV, des modèles pour fichiers Nimègue sont disponibles dans "admin/&#95;upload&#95;init" ce sont les fichiers "*.m&#95;csv".

### Première installation

1. Récupérer la dernière version ExpoActes :  "https://expoactes.monrezo.be/download.htm" puis le dézipper en local.

2. Transférer par FTP l'arborescence complète "actes/" dans dossier de votre choix (VotreDossier) sur le serveur (nécessite code et mdp FTP) (avec FileZilla par exemple ou un outil comparable).

3. Exécuter dans votre butineur le code résidant à l'adresse VotreDomaine/VotreDossier/install/install.php. Le programme appellera le configurateur pour la base de données puis lancera la création d'un premier compte qui sera l'administrateur d'ExpoActes.

