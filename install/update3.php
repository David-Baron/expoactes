<?php

$Max_time = ini_get("max_execution_time");
$bypassTIP=1;
include("../_config/connect.inc.php");
include("../tools/function.php");
include("../tools/adlcutils.php");
include("../tools/actutils.php");
include("../tools/loginutils.php");
include("instutils.php");
include("../tools/PHPLiveX/PHPLiveX.php");
error_reporting(6143);  // definition du niveau d'erreur

//------------------------------------------------------------------------

function logonokV2($level=0)
	{
	global $root,$userlogin,$statut;
  //{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

	  { // Autentification PHP
		if (isset($_REQUEST['login']))  // si on présente un login, on le teste de suite
			{
			$userid = 0;
			$statut = "";
			$userlevel=CheckUserV2(getparam('login'),getparam('passwd'),getparam('codedpass'),getparam('iscoded'),$userid);
			if($userlevel==0)
				{
				$niveau=0;
				}
			 else
				{
				if (getparam('iscoded')=='Y')
					$md5=getparam('codedpass');
					else
					$md5=sha1(getparam('passwd'));
				if (getparam('saved')=='yes') $duree = time()+(60*60*24)*5;
				                       else $duree = null;
				setcookie('md5',$md5,$duree,$root);
				setcookie('userid',$userid,$duree,$root);
				$niveau = $userlevel;
				$userlogin = getparam('login');
				}
			}
		elseif(isset($_COOKIE['userid']) and isset($_COOKIE['md5']))
			{
			$niveau = CheckMD5V2($_COOKIE['userid'],$_COOKIE['md5']);  // mets à jour $userlogin
			if ($niveau < $level) $niveau = 0;
			}
		elseif ($level <= PUBLIC_LEVEL)
			{
			$niveau = PUBLIC_LEVEL;  // ne pas (re)positionner userlogin
			}
		else
			{
			$niveau=0;
			}
	  }
	return $niveau;
	}

//------------------------------------------------------------------------

function CheckUserV2($login,$pw,$codedpw,$coded,&$userid)
  {
  global $statut, $level;
  if (strlen($login)>15 or strlen($pw)>15)
  	{
		writelog('Login ERROR : '.$login,$pw,$nbresult);
		// probablement attaque avec injection de code
    return 0;
    }
    else
    {
		$res=mysql_query("SELECT * FROM ".EA_DB."_user WHERE login='".$login."'"); // AND passw='".$pw."'");
		$nbresult = mysql_num_rows($res);
		if ($nbresult>1)
			{
			writelog('Login ERROR : ',$login,$nbresult);
			// probablement attaque avec injection de code ou 2 logins identiques dans la base 
			return 0;
			}
			else
			{		
			if ($nbresult==1)
				{
				$row = mysql_fetch_array($res);
				$statut = $row["statut"];
				if ($coded=='N') 
					$pwok = ($row["passw"]===$pw);
					else
					$pwok = (sha1($row["passw"])===$codedpw);
				// recontrôle des user et pw pour assurer respect de la casse et anti injection
				if ($row["login"]==$login and $pwok and $row["level"]>=$level and ($row["level"]==9 or $row["statut"]=='N'))
					{
					$userid = $row["ID"];
					return max($row["level"],PUBLIC_LEVEL);
					}
					else
					{return 0;}
				}
			else
				return 0;
			}
		}
  }

//------------------------------------------------------------------------

function CheckMD5V2($userid,$md5)
  {
  global $userlogin, $level;
	$res=mysql_query("SELECT * FROM ".EA_DB."_user WHERE ID='".$userid."'");
	if (mysql_num_rows($res)==1)
	  {
 	  $row = mysql_fetch_array($res);
	  if (sha1($row["passw"])!=$md5)
			 {return 0;}
			else
			 {
			 if ($row["level"]>=$level)
				 {
				 $userlogin = $row["login"];
				 return max($row["level"],PUBLIC_LEVEL);
				 }
				 else
				 {return 0;}
			 }
	 }
	else
	 return 0;
  }//-----------------------------------------------------

function getMaintenance()
	{
	$request = "select VALEUR from ".EA_DB."_params where PARAM='EA_MAINTENANCE';";
	$result = mysql_query($request);
	$row = mysql_fetch_array($result);
	return $row[0];
	}

//-----------------------------------------------------

function setMaintenance($value)
	{
	$request = "update ".EA_DB."_params set VALEUR='".$value."' where PARAM='EA_MAINTENANCE';";
	mysql_query($request);
	$nb = mysql_affected_rows();
	return $nb;
	}

//-----------------------------------------------------	

function sqltype($typ,$tail)
	{
		$letyp = "********";
	if ($typ == "TXT" or $typ == "DAT" or $typ == "AGE")
		{
		if ($tail < 9999)
		  $letyp = "VARCHAR(".$tail.") ";
		  else
		  $letyp = "TEXT ";
		}
	if ($typ == "SEX")
		  $letyp = "CHAR(1) ";
	if ($typ == "DTE")
		  $letyp = "DATE default '0000-00-00' ";
	if ($typ == "NUM")
		  $letyp = "INT(".$tail.") ";
	return $letyp;	
	}
	
//-----------------------------------------------------	

function tablename3($code, $vers=3)
	{
	if ($vers==3)
		$tables = array("N"=>"_nai3","M"=>"_mar3","D"=>"_dec3","V"=>"_div3","U"=>"_user3");
	else
		$tables = array("N"=>"_nai","M"=>"_mar","D"=>"_dec","V"=>"_div","U"=>"_user");
	return EA_DB.$tables[$code]; 
	}
	
//-----------------------------------------------------	

function nextID($code)
	{
	// détermine le prochain identifiant à transférer 
	$req0 = "select max(ID) as MAXID from ".tablename3($code,3);
	$res0 = mysql_query($req0);
	$row0 = mysql_fetch_array($res0);
	$nid3 = $row0["MAXID"];
	if (empty($nid3)) 
		$nid3 = 0;
	$req0 = "select max(ID) as MAXID from ".tablename3($code,2);
	$res0 = mysql_query($req0);
	$row0 = mysql_fetch_array($res0);
	$nid2 = $row0["MAXID"];
	if (empty($nid2)) 
		$nid2 = 0;
	if ($nid2 > $nid3)
		$nid = $nid3;
		else
		$nid = -1; // fini, tout a été transféré. 
	return $nid;
	}
	
	
//---------------------------------------------------------
// CLASSE DivMsg

class divMsg

	{
	var $texte;
	
	function divMsg ()
		{
		$this->texte = "";
		}
		
	function addTxt($msg)
		{
		$this->texte .= htmlentities($msg, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET);
		}
		
	function addLink($link,$msg)
		{
		$this->texte .= '<a href="'.$link.'">'.htmlentities($msg, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET).'</a>';
		}

	function addBold($msg)
		{
		$this->texte .= "<strong>".htmlentities($msg, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET)."</strong>";
		}
	function addErr($msg)
		{
		$this->texte .= '<strong><font color="#FF0000">'.htmlentities($msg, ENTITY_REPLACE_FLAGS, ENTITY_CHARSET)."</font></strong>";
		}
	function LF()  // LineFeed = saut à la ligne suivante
		{
		$this->texte .= "<br />";
		}
		
	function show()  // LineFeed = saut à la ligne suivante
		{
		return $this->texte;
		}
	}

//-----------------------------------------------------	

function allongeZoneTables($table,&$logtxt)
	{	
	$sql = "SHOW COLUMNS FROM ".$table.";";
	if ($res = mysql_query($sql))
		{
		if (mysql_num_rows($res)>0)
			{
			while ($row = mysql_fetch_array($res))
				{
				//print_r( $row);
				$field= $row[0];
				$ftype= $row[1];
				$par=isin($ftype,"(");
				$flen = 0;
				if ($par>0)
					{
					$flenX = mb_substr($ftype,$par+1);
					$flen = mb_substr($flenX,0,isin($flenX,")"));
					}
				//echo "<br>".$field." - ".$ftype." - ".$flen;
				if ($flen>0)
					{
					$sql1 = "SHOW COLUMNS FROM ".$table."3 LIKE '".$field."';";
					//echo $sql1;
					if ($res1 = mysql_query($sql1))
						{
						if (mysql_num_rows($res1)>0)
							{
							$row1 = mysql_fetch_array($res1);					
							$ftype3= $row1[1];
							$par=isin($ftype3,"(");
							$flen3 = 0;
							if ($par>0)
								{
								$flenY = mb_substr($ftype3,$par+1);
								$flen3 = mb_substr($flenY,0,isin($flenY,")"));
								}
							$type3 = mb_substr($ftype3,0,$par);	
							//echo "<br> --> ".$field." - ".$ftype3." - ".$flen3;
							if ($flen > $flen3)
								{
								$sqlu = "ALTER TABLE ".$table."3 CHANGE ".$field." ".$field." ".$type3."(".$flen.");" ;
								$resu = mysql_query($sqlu);
								if ($resu === true)
									{
									$logtxt->LF();
									$logtxt->addTxt(" Allongement de la zone ".$table."3.".$field." de ".$flen3." à ".$flen." caractères.");
									}
								 else
									{
									$logtxt->LF();
									$logtxt->addErr(mysql_error());
									}
								}
							}
						}
					}				
				//echo $field." - ".$ftype." - ".$flen;
			  }
			}
		}
	//return;	
	} // allonge ...

//-----------------------------------------------------	

function loaddata()

	{
	$T0 = time();
	$MT0 = microtime_float();
	$Max_time = min(ini_get("max_execution_time")-3,MAX_EXEC_TIME);
	
	$logtxt = new divMsg();
	$noEtape = getMaintenance();
	$codes = array(11=>'N',12=>'M',13=>'D',14=>'V',15=>'U');
	$tables = array("nai","mar","dec","div","user");
	
	if ($noEtape<2) $noEtape = 8;
	
	if ($noEtape==8)
		{ // création des meta tables
		$res = mysql_query("SHOW TABLES LIKE '".EA_DB."_mgrplg';");
		if (mysql_num_rows($res)==0)
			{
			$ok = execute_script_sql('creemeta3.sql');  // création des tables
			if ($ok)
				{
				$logtxt->LF();
				$logtxt->addBold("Création des métadonnées effectuée.");
				$logtxt->LF();
				}
			 else
				{
				$logtxt->LF();			
				$logtxt->addErr("04x : Problème prendant l'exécution du script de création des métadonnées V3.");
				die();
				}		
			}
		$noEtape = 9;
		setMaintenance($noEtape);
		}

	elseif ($noEtape==9)
		{ // création des tables nouvelles
		
		$ok = execute_script_sql('creetables3.sql');  // création des tables
		if ($ok)
			{
			$logtxt->LF();
			$logtxt->addBold("Création des tables au format V3 effectuée.");
			$logtxt->LF();
			}
		 else
			{
			$logtxt->LF();			
			$logtxt->addErr("04y : Problème prendant l'exécution du script de génération des tables V3.");
			die();
			}		  
		$noEtape = 10;	
		setMaintenance($noEtape);
		}
	
	elseif ($noEtape==10)
		{ // maise a mesure des champs selon l'ancienne base
	
		foreach ($tables as $table)
			allongeZoneTables(EA_DB."_".$table,$logtxt);  
		$noEtape = 11;	
		setMaintenance($noEtape);
		}
		
	elseif ($noEtape>=11 and $noEtape<=14)
	  { // Transfert des données ACTES
		$code=$codes[$noEtape];

		$totload = 0;

		// selection de la liste des champs à transférer
		$zlist ="";
		$req = "select zone,typ, taille, oblig from ".EA_DB."_metadb where dtable='".$code."' and OV2 > 0 order by OV2";

		$res = mysql_query($req);
		while ($row = mysql_fetch_array($res))
			{
			if ($zlist<>"") 
				$zlist .= ", ";
			$zlist .= $row["zone"];
			}

		// TRANSFERT proprement dit

		$nid = nextID($code);
		while ($nid >= 0 and (time()-$T0<$Max_time))
			{ // on continue un paquet de lignes

			$req0 = "select max(ID) as MAXID from ".tablename3($code,3);
			$res0 = mysql_query($req0);
			$row0 = mysql_fetch_array($res0);
			$nid = $row0["MAXID"];
			if (empty($nid)) 
				$nid = 0;

			$req1 = "select ".$zlist." from ".tablename3($code,2)." where ID>".$nid." order by ID limit 0,100";
			$res1 = mysql_query($req1);
			//echo $req1;
			$i=0;
			$req2 = "insert into ".tablename3($code,3)."(".$zlist.") values ";
			$zblocs = "";
			while ($row1 = mysql_fetch_assoc($res1))
				{
				$i++;
				//echo "<p>".$i." ".$row1["ID"]." -> ".$row1["NOM"];
				$zvalues = "";
				foreach ($row1 as $laval)
					{
					if (!empty($zvalues))
						$zvalues .= ",";
					$zvalues = $zvalues."'".sql_quote($laval)."'";
					}
				if (!empty($zblocs))
					$zblocs .= ",";
				$zblocs .= "(".$zvalues.")";	
				}
			$req2 .= $zblocs;
			$res2 = mysql_query($req2);
			//echo "<p>".$req2;
			$inserted = mysql_affected_rows();
			//echo '<p>'.$inserted." lignes insérées</p>";
			$totload += $inserted;
			if ($inserted<0) 
				{
			  $logtxt->addErr("Problème lors de l'insertion des données : ".mysql_error());
			  setMaintenance(1); // exit
				die();
				}
			$nid = nextID($code); // faut il continuer ? 
			}
		$logtxt->LF();
		$typtext = " actes de type ".typact_txt($code);
		$logtxt->addTxt($totload.$typtext." transférés en ".round(microtime_float()-$MT0,3).' sec.');
		writelog('V2->V3 '.$typtext,"UPGRADE",$totload);
		}  // transfert des données 
	
	elseif ($noEtape==15)
	  { // Transfert des données USERS
		$code=$codes[$noEtape];

		$totload = 0;

		// selection de la liste des champs à transférer
		$zlist = "LOGIN,NOM,PRENOM,EMAIL,LEVEL,REGIME,SOLDE,MAJ_SOLDE,STATUT,DTCREATION,PT_CONSO,REM,ID";
		// new : HASHPASS, DTEXPIRATION,LIBRE

		// TRANSFERT proprement dit

		$nid = nextID($code);
		while ($nid >= 0 and (time()-$T0<$Max_time))
			{ // on continue un paquet de lignes

			$req0 = "select max(ID) as MAXID from ".tablename3($code,3);
			$res0 = mysql_query($req0);
			$row0 = mysql_fetch_array($res0);
			$nid = $row0["MAXID"];
			if (empty($nid)) 
				$nid = 0;

			$req1 = "select PASSW,".$zlist." from ".tablename3($code,2)." where ID>".$nid." order by ID limit 0,100";
			$res1 = mysql_query($req1);
			//echo $req1;
			$i=0;
			$req2 = "insert into ".tablename3($code,3)."(HASHPASS,".$zlist.") values ";
			$zblocs = "";
			while ($row1 = mysql_fetch_assoc($res1))
				{
				$i++;
				//echo "<p>".$i." ".$row1["ID"]." -> ".$row1["NOM"];
				$zvalues = "";
				$row1['PASSW']= sha1($row1['PASSW']); // conversion du mot de passe
				foreach ($row1 as $laval)
					{
					if (!empty($zvalues))
						$zvalues .= ",";
					$zvalues = $zvalues."'".sql_quote($laval)."'";
					}
				if (!empty($zblocs))
					$zblocs .= ",";
				$zblocs .= "(".$zvalues.")";	
				}
			$req2 .= $zblocs;
			$res2 = mysql_query($req2);
			//echo "<p>".$req2;
			$inserted = mysql_affected_rows();
			//echo '<p>'.$inserted." lignes insérées</p>";
			$totload += $inserted;
			if ($inserted<0) 
				{
			  $logtxt->addErr("Problème lors de l'insertion des données : ".mysql_error());
			  setMaintenance(1); // exit
			  die(); // exit
				}
			$nid = nextID($code); // faut il continuer ? 
			}
		$logtxt->LF();
		$typtext = " enregistrements d'utilisateurs";
		$logtxt->addTxt($totload.$typtext." transférés en ".round(microtime_float()-$MT0,3).' sec.');
		writelog('V2->V3 '.$typtext,"UPGRADE",$totload);
		
		}  // transfert des données 

	elseif ($noEtape==16)
		{
		$logtxt->LF();
		$logtxt->LF();
		$logtxt->addBold("Transfert des données terminé !"); 
		$logtxt->LF();
		$logtxt->LF();
		$logtxt->addLink("update.php","Lancer la mise à jour des paramètres"); 
		setMaintenance(0);
		}
		
	if ($nid == -1)
		{
		$logtxt->LF();
		$logtxt->addBold("Transfert complet pour les ".$typtext);
		$logtxt->LF();
		$noEtape++;
		setMaintenance($noEtape);
		}
		
	if ($noEtape==1)
		{
		$logtxt->LF();
		$logtxt->LF();
		$logtxt->addBold("Transfert non terminé sur erreur !"); 
		$logtxt->LF();
		}

	return $logtxt->show();
	} // loaddata
	
//-----------------------------------------------------	
//echo loaddata();
//============================================================


$root="";
$xcomm=$xpatr=$page="";
pathroot($root,$path,$xcomm,$xpatr,$page);

$serveur = $_SERVER['SERVER_NAME'].' ['.$_SERVER['SERVER_ADDR'].']';
$db = con_db();  

// test du login ... V2 ou V3 selon ..
$login = 3;
$res = mysql_query("SHOW TABLES LIKE '".EA_DB."_user3';");
if (mysql_num_rows($res)>0)
	{
	$res = mysql_query("SELECT * from ".EA_DB."_user3;");
	if (mysql_num_rows($res)==0)
		$login = 2;
	}
	else
	$login = 2;
if ($login==3)
	{// On peut déjà utilser V3 normal
	$userlogin="";
	$userlevel=logonok(9);
	if ($userlevel<9)
		{
		login($root);
		}
	}
	else
	{
	// LOGIN V2 (une dernière fois !!)
	$userlogin="";
	$userlevel=logonokV2(9);
	if ($userlevel<9)
		{
		open_page("Conversion ExpoActes V2 -> V3 ".$serveur,$root);	
		echo '<h2>Vous devez vous identifier en tant qu\'administrateur : </h2>'."\n";

		echo '<form id="log" name="logform" method="post">'."\n";
		echo '<table align="center" summary="Formulaire">'."\n";
		echo '<tr><td align="right">Login</td><td><input name="login" size="15" maxlength="15" /></td></tr>'."\n";
		echo '<tr><td align="right">Mot de passe</td><td><input type="password" name="passwd" size="15" maxlength="15" /></td></tr>'."\n";
		echo '<tr><td colspan="2" align="center"><input type="submit" value=" Me connecter " /></td></tr>'."\n";
		echo '</table>'."\n";
		echo '<input type="hidden" name="codedpass" value="" />';
		echo '<input type="hidden" name="iscoded" value="N" />';
		echo '</form>'."\n";
		echoln( '<p>&nbsp;</p>'."\n");
		}
	}
if ($userlevel==9)
	{
	open_page("Conversion ExpoActes V2 -> V3 ".$serveur,$root);
	$missingargs=true;
	echo "<h1>Conversion ExpoActes V2 -> V3   ".$serveur."</h1>";
	}
?>
<script type="text/javascript">
var drapeau = 0;  /* 0 = libre 1 = requete en cours */

function Controleur() {
  if (drapeau==0)
  	{
		drapeau = 1; 
		document.getElementById("msgdelai").style.visibility="hidden";		
		document.getElementById("actionLink").style.display="none";
		document.getElementById("msgpatience").innerHTML="Chargement en cours ...";		
		loaddata('',{target:'logtxt','preloader':'patience',mode:'aw',onFinish:function(response, xmlhttp){ ActionDeFin(response)} });
		}
	/* sinon ne rien faire */	
  }

function StopJob() {
	drapeau = 2;	
	/* appeler une proc pour remttre la maintenance !!*/
  }
  
function ActionDeFin(response) {
  if (response.lastIndexOf("termin")>0)
  	{
		drapeau = 2;
		}
		else
  	{
		if (drapeau!=2) drapeau = 0;
		document.getElementById("msgdelai").style.visibility="visible";		
		setTimeout("Controleur()",3000);
		}		
  }
 
</script>
<?php 

//{ print '<pre>';  print_r($_REQUEST); echo '</pre>'; }

if ($userlevel==9)
	{
	//echo loaddata();
	// SUITE de UPDATE3	
	$phplive = new PHPLiveX(array("loaddata")); //Register the function with PHPLiveX
	$phplive->Run(false,"../tools/PHPLiveX/phplivex.js");

	echo '<p><b>La conversion de la base de données et la migration des données va nécessiter plusieurs étapes qui s\'enchaînent automatiquement.</b>';
	echo '<p><div id="logtxt"></div></p>';
	echo '<p>';
	echo '<span id="actionLink"><input type="button" id="btCharger" value=" Lancer la migration des données ... " onclick="Controleur();"></span> ';
	echo '<span id="patience" style="visibility:hidden"> &nbsp; &nbsp; <img src="../img/spinner.gif"><span id="msgpatience">Chargement ...</span></span><br />';
	echo '<span id="msgdelai" style="visibility:hidden">3 sec. de patience avant la poursuite du travail..."></span>'; 
  //echo '<span id="msgdelai" style="visibility:hidden"><input type="button" id="btStop" value=" Interrompre le travail " onclick="StopJob();"></span>'; 
	echo '</span></p>';
	//load_params();  // pour rafraichir le pied de page 
	}
close_page(0);
?>