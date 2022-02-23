<?php

// Remplacer le fichier connect.inc.php par celui-ci
if ($_SERVER['SERVER_ADDR']=='127.0.0.1')
  {
	$dbaddr="localhost";
	$dbuser="expoactes_dev";
	$dbpass="expoactes_dev";
	$dbname="expoactes_dev";
	}
else
  {
	$dbaddr="";
	$dbuser="";
	$dbpass="";
	$dbname="";
  }
?>