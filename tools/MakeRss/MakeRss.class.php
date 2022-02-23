<?php
$version = '0.2';
/*
 +----------------------------------------------------------------------+
 | ILIX MakeRSS 0.2                                                     |
 +----------------------------------------------------------------------+
 | 12 avril 2005 Modifié 2015 UTF8                                                       |
 +----------------------------------------------------------------------+
 | Cette classe permet de gérer un flux RSS :                           |
 |  - GenBtn : gère le bouton orange RSS                                |
 |             et la reconnaissance par le navigateur                   |
 |             de la disponibilité d'un flux                            |
 |  - GenRss : génération du flux RSS                                   |
 +----------------------------------------------------------------------+
 | Auteur: ILIX - F. M.                                                 |
 +----------------------------------------------------------------------+
*/
class GenBtn
{
    var $titre;

    function SetTitre($value)
    {
        $this->titre = $value;
    }
    function Show($value, $url)
    {
        echo '<link rel="alternate" type="application/rss+xml" title="' . $this->titre . '" href="' . $url . '?show=flux" />';
        echo '<a href="' . $url . '?show=flux" title="Un fil RSS est disponible pour cette rubrique (' . $this->titre . ')"><img src="' . $value . '" border="0"></a>';
    }
}

class GenRss
{
    var $entete;
    var $generator;
    var $channel;
    var $fil;
    var $bot;
    var $html = 0;

    function Load()
    {
        Header("Content-type: text/xml; charset=utf-8;");
        //Header("Content-type: text/xml;");
        $this->entete .= '<' . '?xml version="1.0" encoding="utf-8" ?' . '>' . "\n";
        if ($this->html == 1) {
            $this->entete .= '<' . '?xml-stylesheet type="text/xsl" href="http://belrss.monrezo.be/rss2htm.xsl"?>' . "\n\n";
        }
        $this->entete .= '<rss version="2.0">' . "\n\n";
        $this->entete .= '<channel>' . "\n\n";
        $this->generator = '<generator>ExpoActes ' . EA_VERSION . ' + ILIX MakeRss 0.2</generator>' . "\n";   // MERCI DE NE PAS ENLEVER CETTE LIGNE
    }

    // ENTETE
    function SetTitre($value)
    {
        $this->channel .= '<title>' . $value . '</title>' . "\n";
    }
    function SetLink($value)
    {
        $this->channel .= '<link>' . $value . '</link>' . "\n";
    }
    function SetDetails($value)
    {
        $this->channel .= '<description>' . $value . '</description>' . "\n";
    }
    function SetLanguage($value)
    {
        $this->channel .= '<language>' . $value . '</language>' . "\n";
    }
    function SetRights($value)
    {
        $this->channel .= '<copyright>' . $value . '</copyright>' . "\n";
    }
    function SetEditor($value)
    {
        $this->channel .= '<managingEditor>' . $value . '</managingEditor>' . "\n";
    }
    function SetMaster($value)
    {
        $this->channel .= '<webMaster>' . $value . '</webMaster>' . "\n";
    }
    function SetHtml($value)
    {
        $this->html = $value;
    }
    function SetImage($value, $titre, $link)
    {
        $this->channel .= '<image>' . "\n";
        $this->channel .= '<url>' . $value . '</url>' . "\n";
        $this->channel .= '<title>' . $titre . '</title>' . "\n";
        $this->channel .= '<link>' . $link . '</link>' . "\n";
        $this->channel .= '</image>' . "\n";
    }

    // ITEMS
    function AddItem($titre, $desc, $from, $cat, $date, $link)
    {
        $this->fil .= '<item>' . "\n";
        $this->fil .= '<title>' . $titre . '</title>' . "\n";
        $this->fil .= '<author>' . $from . '</author>' . "\n";
        $this->fil .= '<category>' . $cat . '</category>' . "\n";
        $this->fil .= '<pubDate>' . $date . '</pubDate>' . "\n";
        $this->fil .= '<description>' . $desc . '</description>' . "\n";
        $this->fil .= '<guid>' . $link . '</guid>' . "\n";
        $this->fil .= '<link>' . $link . '</link>' . "\n";
        $this->fil .= '</item>' . "\n\n";
    }

    function Close()
    {
        $this->bot = '</channel>' . "\n";
        $this->bot .= '</rss>';
    }

    function Generer()
    {
        echo $this->entete;
        echo "\n";
        echo $this->channel;
        echo "\n";
        echo $this->generator;
        echo "\n";
        echo $this->fil;
        echo "\n";
        echo $this->bot;
    }
}
