<?

include("../_config/connect.inc.php");
include("../tools/function.php");

/**
 * Teste si acces en ecriture est possible : attention aux deux _ _
 */
function writable($path)
{
	if ($path{
	strlen($path) - 1} == '/')
		return writable($path . uniqid(mt_rand()) . '.tmp');

	echo '<p>Test de création du fichier ' . $path . '';
	if (file_exists($path)) {
		echo '<p>Fichier existe déja';
		if (!($f = fopen($path, 'r+'))) {
			echo '<p>Impossible d\'ouvrir le ficher en mode R+';
			return false;
		}
		fclose($f);
		return true;
	}
	if (!($f = fopen($path, 'w'))) {
		echo '<p>Impossible d\'ouvrir le ficher en mode R+';
		return false;
	}
	fclose($f);
	unlink($path);
	return true;
}

echo '<h1>Test spécifiques</h1>';
echo "<h2>Création d'un fichier</h2>";
$path = "../admin/_upload/";

if (!is_dir($path))
	echo '<p>Répertoire "' . $path . '" inaccessible ou inexistant.';

if (writable($path)) {
	echo "<p>Création de fichier OK dans " . $path;
	//echo "<br>Droits d'accès à ".$path." : ".mb_substr(sprintf('%o', fileperms($path)), -4);
} else {
	echo "<p>IMPOSSIBLE de créer un fichier dans " . $path;
	echo "<br>Droits d'accès à " . $path . " : " . mb_substr(sprintf('%o', fileperms($path)), -4);
}
