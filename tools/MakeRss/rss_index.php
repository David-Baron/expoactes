<?php

include ('MakeRss.class.php');

        if(!$show || $show=="btn"){
        /*
        Si on a pas demander d'afficher  le fil,
        c'est qu'on veut juste le bouton
        */
        $rss = new GenBtn();
        $rss->SetTitre('TITRE DE VOTRE FLUX');
        /* MODIFIEZ LES URL DU BOUTON EN ABSOLU !
        1) url du bouton
        2) url du dossier contenant les fichiers MakeRSS (celui-ci et MakeRss.class.php */
        $rss->Show('/rss.gif','/rss/');

        }   else {

/* Si on a décider d'afficher le flux... */

/* CHARGEMENT DU GENERATEUR */
$rss = new GenRSS();

/* OUVERTURE DU FIL */
$rss->Load();

/* LES PARAMETRES OBLIGATOIRES */
$rss->SetTitre('TITRE');
$rss->SetLink('URL');
$rss->SetDetails('Description');
/* LES PARAMETRES FACULTATIFS (Mettez // devant les paramètres que vous ne voulez pas renseigner) */
$rss->SetLanguage('fr');
$rss->SetRights('copyright');
$rss->SetEditor('email editeur');
$rss->SetMaster('email tech');
$rss->SetImage('url image','titre','lien');

/* AJOUT DES ARTICLES AU FIL */

$rss->AddItem('Titre','Descripton','Auteur','Catégorie','00/00/0000','http://');

// Vous pouvez sortir les infos d'une base SQL !
//Faite la routine habituelle ! et rentrez la table dans les paramètres de AddItem


/* FERMETURE DU FIL */
$rss->Close();

/* GENERATION DU RSS */
$rss->Generer();

}
?>
