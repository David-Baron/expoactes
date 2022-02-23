<?php
if (file_exists('tools/_COMMUN_env.inc.php')) {
    $EA_Appel_dOu = '';
} else {
    $EA_Appel_dOu = '../';
}
include($EA_Appel_dOu . 'tools/_COMMUN_env.inc.php');

pathroot($root, $path, $xcomm, $xpatr, $page);

$act = getparam('act');

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

$userlogin = "";

$userlevel = logonok(CHANGE_PW);
while ($userlevel < CHANGE_PW) {
    login($root);
}

$script = file_get_contents("tools/js/sha1.js");
open_page("Changement de mot de passe", $root, $script);
navigation($root, 2, 'A', "Changement de mot de passe");

?>
<script type="text/javascript">
function pwProtect() {
    form = document.forms["eaform"];
    if (form.oldpassw.value == "") {
        alert("Erreur : L'ancien mot de passe est vide !");
        return false;
    }
    if (form.passw.value == "") {
        alert("Erreur : Le nouveau mot de passe est vide !");
        return false;
    }
    if (form.passw.value.length < 6) {
        alert("Erreur : Le nouveau mot de passe est trop court (min 6 caractères) !");
        return false;
    }
    if (!(form.passw.value == form.passwverif.value)) {
        alert("Erreur : Les nouveaux mots de passes ne sont pas identiques !");
        return false;
    }
    if (sha1_vm_test()) { // si le codage marche alors on l'utilise
        form.codedpass.value = hex_sha1(form.passw.value);
        form.codedoldpass.value = hex_sha1(form.oldpassw.value);
        form.passw.value = "";
        form.oldpassw.value = "";
        form.passwverif.value = "";
        form.iscoded.value = "Y";
    }
    return true;
}
function seetext(x) {
    x.type = 'text';
}
function seeasterisk(x){
    x.type = 'password';
}
</script>
<?php


echo '<div id="col_menu">';
form_recherche($root);
//menu_admin($root, $userlevel);
statistiques();
menu_public();
show_pub_menu();
show_certifications();

echo '</div>';

echo '<div id="col_main_adm">';

if ($act == "relogin") {
    echo '<p align="center"><a href="index.php">Retour à la page d\'accueil</a></p>';
    echo '</div>';
    close_page(1);
    exit;
}

$missingargs = true;

$userid = current_user("ID");

if (getparam('action') == 'submitted') {
    $ok = true;
    if (getparam('iscoded') == "N") {
        // Mot de passe transmis en clair
        if (strlen(getparam('passw')) < 6) {
            msg('Vous devez donner un nouveau MOT DE PASSE d\'au moins 6 caractères');
            $ok = false;
        }
        if (getparam('passw') <> getparam('passwverif')) {
            msg('Les deux copies du nouveau MOT DE PASSE ne sont pas identiques');
            $ok = false;
        }
        if (! (sans_quote(getparam('passw')))) {
            msg('Vous ne pouvez pas mettre d\'apostrophe dans le MOT DE PASSE');
            $ok = false;
        }
        $codedpass = sha1(getparam('passw'));
        $codedoldpass = sha1(getparam('oldpassw'));
    } else {
        $codedpass = getparam('codedpass');
        $codedoldpass = getparam('codedoldpass');
    }
    $userpw = current_user("hashpass");
    if ($codedoldpass <> $userpw) {
        msg('Votre ancien mot de passe n\'est pas correct');
        $ok = false;
    }

    if ($ok) {
        $missingargs = false;
        $reqmaj = "UPDATE " . EA_UDB . "_user3 SET hashpass = '" . $codedpass . "' " . 
            " WHERE id = " . $userid . ";";

        //echo "<p>" . $reqmaj . "</p>";

        if ($result = EA_sql_query($reqmaj, $u_db)) {
            // echo '<p>' . EA_sql_error() . '<br>' . $reqmaj . '</p>';
            writelog('Modification mot de passe ', $_REQUEST['lelogin'], 0);
            echo '<p><b>MOT DE PASSE MODIFIE.</b></p>';
        } else {
            echo ' -> Erreur : ';
            echo '<p>' . EA_sql_error() . '<br>' . $reqmaj . '</p>';
        }
    }
}

//Si pas tout les arguments nécessaire, on affiche le formulaire
if ($missingargs) {
    echo<<<AAA
     <h2>Modification de votre mot de passe</h2>
     <form method="post" name="eaform" action="" onsubmit="return  pwProtect();">
     <table cellspacing="0" cellpadding="1" border="0" summary="Formulaire">
     <tr>
       <td align="right">Code utilisateur : </td>
       <td>$userlogin</td>
     </tr>
     <tr>
       <td align="right">Ancien mot de passe : </td>
       <td><input id="EAoldpwd" type="password" name="oldpassw" size="15" value="" />
       <img onmouseover="seetext(EAoldpwd)" onmouseout="seeasterisk(EAoldpwd)"
        style="vertical-center" border="0" src="img/eye-16-16.png"
        alt="Voir mot de passe" width="16" height="16">
       </td>
     </tr>
     <tr>
       <td align="right">Nouveau mot de passe : </td>
       <td><input id="EApwd" type="password" name="passw" size="15" value="" />
       <img onmouseover="seetext(EApwd)" onmouseout="seeasterisk(EApwd)"
        style="vertical-center" border="0" src="img/eye-16-16.png"
        alt="Voir mot de passe" width="16" height="16">
       </td>
     </tr>
     <tr>
       <td align="right">Nouveau mot de passe (vérif.) : </td>
       <td><input id="EApwdverif" type="password" name="passwverif" size="15" value="" />
       <img onmouseover="seetext(EApwdverif)" onmouseout="seeasterisk(EApwdverif)"
        style="vertical-center" border="0" src="img/eye-16-16.png"
        alt="Voir mot de passe" width="16" height="16">
       </td>
     </tr>
     <tr>
       <td colspan="2">&nbsp;</td>
     </tr>
     <tr><td align="right">
       <input type="hidden" name="codedpass" value="" />
       <input type="hidden" name="codedoldpass" value="" />
       <input type="hidden" name="iscoded" value="N" />
       <input type="hidden" name="lelogin" value="$userlogin" />
       <input type="hidden" name="action" value="submitted" />
       <input type="reset" value=" Effacer " />
       </td>
       <td align="left">
         &nbsp; <input type="submit" value=" *** MODIFIER *** " />
       </td>
     </tr>
     </table>
     </form>

AAA;
} else {
    $mes = 'Vous DEVEZ vous reconnecter avec le nouveau mot de passe.';
    echo '<p align="center"><a href="login.php?cas=4">' . $mes . '</a></p>';
}
echo '</div>';
close_page(1);
