<?php

// Procédure légèrement transformée de http://www.notoon.com/forums/showthread.php?t=117

/**
On crée la fonction aleatoire() qui va retourner une valeur prise au hasard dans un tableau
Elle sera utilisée pour piocher une couleur et une police au hasard pour chaque caractère
 */
function aleatoire($tab)
{
  $max = count($tab) - 1;
  $hasard = mt_rand(0, $max);
  return ($tab[$hasard]);
}

/**
Pour créer une image, on envoie un en-tête pour dire au navigateur qu'on envoie une image
Lorsqu'on va appeler l'image avec <img src="..." /> on utlisera bien image.php et non bruit.png
Cela se fait avec la fonction header()
 */
header("Content-Type: image/png");
//echo '<p>TEST CAPTCHAS</p>';

/** On démarre une session */
session_start();

/**
mt_rand() génère un nombre aléatoire : cette fonction est plus rapide que rand() de la bibliothèque standard
sha1() renvoie une chaine cryptée de son paramètre. Elle est similaire à md5() mais renvoie une chaine plus longue, la probabilité de collision est donc réduite
mb_substr() retourne le segement d'une chaine. Dans notre cas on prend un segment de 5 caractères en partant du caractère 0
On stocke alors la chaine obtenue dans $rand_str
 */
$listcars = 'ABDEFHKLMNPQRSTUWXYZ23456789';
$ncars = strlen($listcars);
$rand_str = "";
for ($i = 0; $i < 5; $i++) {
  $rand_str .= mb_substr($listcars, mt_rand(0, $ncars - 1), 1);
}

/** On hash ensuite cette valeur avec md5() puis on stocke ce résultat dans variable de session $_SESSION['valeur_image'] de la session en cours */
$_SESSION['valeur_image'] = md5($rand_str);

/** Afin de personnaliser chacun de nos caractères, on les stocke un par un dans des variables */
$char1 = mb_substr($rand_str, 0, 1);
$char2 = mb_substr($rand_str, 1, 1);
$char3 = mb_substr($rand_str, 2, 1);
$char4 = mb_substr($rand_str, 3, 1);
$char5 = mb_substr($rand_str, 4, 1);

/**
imagecreatefrompng() crée une nouvelle image PNG à partir d'un fichier
On la stocke dans $image pour pouvoir y mettre ensuite nos caractères
 */
$image = imagecreatefrompng("bruit.png");

/**
putenv() fixe la valeur de la variable d'environnement pour GD. Cette valeur n'existera que durant la vie du script courant, et l'environnement initial sera restauré lorsque le script sera terminé
Cette ligne est utile si vous avez des problèmes lorsque la police de caractère réside dans le même dossier que le script qui l'utilise
Remarquez que lorsqu'on utilisera les polices, il faudra enlever l'extension .tff
 */
//putenv('GDFONTPATH=' . realpath('.'));
// A REVOIR ... envoyer dans fonts

/**
glob() retourne un tableau contenant les fichiers trouvés dans le dossier avec l'extension .ttf
Vous pouvez donc ajouter autant de polices .TTF que vous voulez
 */
//$files = glob("*.ttf");

//if (count($files)==0)
{
  $files[0] = "./scrawl.ttf";
  $files[1] = "./wavy.ttf";
}

/**
Pour chaque nom de fichier trouvé, on retire l'extension .tff
Et on l'ajoute au tableau $font[]
 */

foreach ($files as $filename) {
  // $filename = mb_substr($filename,0,-4); // retire l'extension .tff
  $filename = "./" . $filename; // on ajoute ./ pour préciser que c'est bien le même répertoire
  $fonts[] = $filename; // ajoute les noms des polices sans leur extension dans un tableau
  //echo '<p>'.$filename.'</p>';
}

/**
imagecolorallocate() retourne un identifiant de couleur
On définit les couleurs RVB qu'on va utiliser pour nos polices et on les stocke dans le tableau $colors[]
Vous pouvez ajouter autant de couleurs que vous voulez
 */
$colors = array(
  imagecolorallocate($image, 255, 0, 0), // rouge
  imagecolorallocate($image, 109, 30, 100), // violet
  imagecolorallocate($image, 30, 80, 180), //  bleu
  imagecolorallocate($image, 40, 100, 20), // vert
  imagecolorallocate($image, 255, 90, 0), // orange
  imagecolorallocate($image, 130, 130, 130)
); // gris

/**
On met en forme nos caractères un par un pour les disposer sur notre image d'origine bruit.png
imagettftext(image, taille_de_la_police, angle, coordonnée_X_à_partir_du_bord, coordonnée_Y_à_partir_du_bord, couleur_RVB, police_de_caractères, texte)  dessine un texte avec une police TrueType
 */
imagettftext($image, 25, -10, 10, 35, aleatoire($colors), aleatoire($fonts), $char1);
imagettftext($image, 25, 20, 40, 35, aleatoire($colors), aleatoire($fonts), $char2);
imagettftext($image, 25, -35, 60, 35, aleatoire($colors), aleatoire($fonts), $char3);
imagettftext($image, 25, 25, 100, 35, aleatoire($colors), aleatoire($fonts), $char4);
imagettftext($image, 25, -15, 120, 35, aleatoire($colors), aleatoire($fonts), $char5);

/** imagepng() crée une image PNG  en utilisant l'image $image */
imagepng($image);

/**
L'image a été créée
On appelle donc imagedestroy() qui libère toute la mémoire associée à l'image $image
 */
imagedestroy($image);
