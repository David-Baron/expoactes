# Installation

## Sous Windows, mais destiné à une installation sur un serveur GNU/Linux, ou sous GNU/Linux

### Première installation

- Dézipper le fichier expoactes-vn.n.n-xxx.zip en local.
- Ajuster le contenu du fichier actes/_config_init/connect.ins.php pour coller à votre environnement de production.
- Pousser l'arborescence complète actes/* à la racine de votre hébergement avec FileZilla ou un outil comparable.
- Exécuter dans votre butineur le code résidant à l'adresse votre_domaine/actes/install/install.php.

### Mise à jour

- Dézipper le fichier expoactes-vn.n.n-xxx.zip en local.
- Sauvegarder la base de données (au minimum toutes les tables commençant par 'act_').
- Copier l'arborescence existante actes/*en actes_sauvegarde/* par exemple.
- Pousser l'arborescence complète actes/*.
- Exécuter dans votre butineur le code résidant à l'adresse votre_domaine/actes/install/update.php.
- Si problèmes détectés, faire une copie d'écran de l'erreur, restaurer vos tables ExpoActes, renommer votre arborescence actes en actes-ko et remommer actes_sauvegarde en actes. Vous devriez de nouveau être opérationnel. Finalement faites parvenir votre copie d'écran sur la liste de diffusion.

## Sous Windows pour une utilisation locale

(À venir)
