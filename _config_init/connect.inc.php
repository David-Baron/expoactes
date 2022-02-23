<?php
if ($_SERVER['SERVER_ADDR'] <> '127.0.0.1') {
    // Paramètres d'accès à la base de données CHEZ VOTRE HEBERGEUR
    $dbaddr = "@@serveur_BD@@"; // Adresse du serveur DISTANT
    $dbname = "@@nom_BD@@"; // Nom de la base
    $dbuser = "@@login_BD@@"; // Login MySQL
    $dbpass = "@@mot_de_passe_BD@@"; // Mot de passe
} else {
    // Paramètres d'accès à la base de données locale EasyPHP (facultatif)
    $dbaddr = "localhost"; // Adresse du serveur LOCAL
    $dbname = "expoactes"; // Nom de la base
    $dbuser = "expoactes"; // Login MySQL-EasyPHP
    $dbpass = "expoactes"; // Mot de passe
}
