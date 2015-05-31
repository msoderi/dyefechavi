<?php

session_start();

require_once("config.php");
require_once("clusterpoint.php");
require_once("include/myphplib.php");


$channelscount = getChannelsdCount();

if(isset($_GET["anno"]) && is_numeric($_GET["anno"])) 
{
	if(!isset($_SESSION["primo_piano__anno"])) $_SESSION["primo_piano__anno"] = array();
	if((0 < round($_GET["anno"])) && (!in_array(round($_GET["anno"]),$_SESSION["primo_piano__anno"]))) $_SESSION["primo_piano__anno"][] = round($_GET["anno"]);
	if((0 > round($_GET["anno"])) && (in_array(abs(round($_GET["anno"])),$_SESSION["primo_piano__anno"]))) 
	{
		$pos = array_search(abs(round($_GET["anno"])), $_SESSION["primo_piano__anno"]);
		unset($_SESSION["primo_piano__anno"][$pos]);
	}
}

if(isset($_GET["mese"]) && is_numeric($_GET["mese"]))
{
        if(!isset($_SESSION["primo_piano__mese"])) $_SESSION["primo_piano__mese"] = array();
        if((0 < round($_GET["mese"])) && (!in_array(round($_GET["mese"]),$_SESSION["primo_piano__mese"]))) $_SESSION["primo_piano__mese"][] = round($_GET["mese"]);
        if((0 > round($_GET["mese"])) && (in_array(abs(round($_GET["mese"])),$_SESSION["primo_piano__mese"])))
        {
                $pos = array_search(abs(round($_GET["mese"])), $_SESSION["primo_piano__mese"]);
                unset($_SESSION["primo_piano__mese"][$pos]);
        }
}

if(isset($_GET["giorno"]) && is_numeric($_GET["giorno"]))
{
        if(!isset($_SESSION["primo_piano__giorno"])) $_SESSION["primo_piano__giorno"] = array();
        if((0 < round($_GET["giorno"])) && (!in_array(round($_GET["giorno"]),$_SESSION["primo_piano__giorno"]))) $_SESSION["primo_piano__giorno"][] = round($_GET["giorno"]);
        if((0 > round($_GET["giorno"])) && (in_array(abs(round($_GET["giorno"])),$_SESSION["primo_piano__giorno"])))
        {
                $pos = array_search(abs(round($_GET["giorno"])), $_SESSION["primo_piano__giorno"]);
                unset($_SESSION["primo_piano__giorno"][$pos]);
        }
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Synd - Alla fonte della notizia</title>
        <meta name="keywords" content="aggregatore, rss, notizia, pubblica, amministrazione" />
        <meta name="description" content="Un aggregatore di notizie e contenuti dalla Pubblica Amministrazione italiana. Sul Web, completo, gratuito, e per tutti." />
        
<link href="<?php if(false === strpos($_SERVER["HTTP_USER_AGENT"],"Mobile Safari")) echo("styles.css"); else echo("msafari.css"); ?>"  rel="stylesheet" type="text/css" />
        <script language="javascript" type="application/x-javascript" src="include/myjslib.js"></script>

<meta property="og:title" content="Synd.it" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?=$baseurl?>/" />
<!-- <meta property="og:image" content="http://www.synd.it/synd/images/head_icon_squared.png" /> -->
<meta property="og:image" content="http://www.synd.it/synd/images/fb_page_icon.png" />
<meta property="og:site_name" content="Synd.it, alla fonte della notizia" />
<meta property="fb:admins" content="<?=$fb_admins?>" />
<meta property="fb:app_id" content="<?=$fb_appid?>" />
<meta property="og:description" content="Un aggregatore di notizie e contenuti dalla Pubblica Amministrazione italiana. Sul Web, completo, gratuito, e per tutti." />
<meta property="og:locale" content="<?=$fb_locale?>" />

</head>

<body onload="javascript:document.getElementById('pleasewait_content').style.display='none'; document.getElementById('left').style.visibility = 'visible'; document.getElementById('right').style.visibility = 'visible';">
<div id="main">

	<div id="logo"><a href="index.php"><img src="images/head_icon.png" alt="Synd"/></a></div>

	<div id="top"></div>

        <div id="header">
                <div id="buttons">
                <div class="but1"><a id="abut1" href="progetto.php" class="but1"  title="Progetto">progetto</a></div>
                <div class="but3"><a id="abut3" href="network.php"  class="but3" title="Network">network</a></div>
                <div class="but4"><a id="abut4" href="primo_piano.php"  class="but4" title="Primo Piano">in evidenza</a></div>
                <div class="but2"><a id="abut2" href="segnalibri.php" class="but2" title="Segnalibri">segnalibri</a></div>
                <div class="but5"><a id="abut5" href="contatti.php" class="but5" title="Contatti">contatti</a></div>
                </div>
        </div>

	<div id="top_box">
		
                <div id="left_box">
                        <div id="server_status">
                                <table id="server_status_table">
                                        <thead>
                                                <tr><td colspan="2">Top News Ever</td></tr>
                                                <tr><td colspan="2">-------------------------------------------------------------------------------</td></tr>
                                        </thead>
                                        <tbody>
<?php
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$topnewsever = mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC LIMIT 10");
$wasSomethingWritten = false;
while($currtopnews = mysqli_fetch_array($topnewsever, MYSQLI_ASSOC))
{
        $wasSomethingWritten = true; ?><tr><td style="white-space:nowrap;"><a href="notizia.php?id=<?=urlencode($currtopnews["newsid"])?>"><?=substr($currtopnews["newstitle"],0,78)?></a></td></tr><?php
}
if(!$wasSomethingWritten) echo("<tr><td>Nessuna notizia da visualizzare.</td></tr>");
mysqli_close($conn);
?>
                                        </tbody>
                                </table>
                        </div>
                        <div id="search_box"><form action="#" method="get"><div id="search_div"><input type="text" name="query" style="-moz-border-radius-bottomleft: 5px; -moz-border-radius-topleft: 5px; font-weight:bold;" value="<?=htmlentities($_GET["query"])?>" onkeypress="javascript:this.style.fontWeight = 'normal';"/><input type="submit" value="&nbsp;" style="-moz-border-radius-bottomright: 5px; -moz-border-radius-topright: 5px;"/></div></form></div>
                </div>

                
<div id="right_box">
                        <h1><span style="font-style:normal;">Benvenuto su Synd,</span>&nbsp;alla fonte della notizia.</h1>
<img id="mirco_soderi" src="images/329.jpg" class="img" alt="Mirco Soderi" />
<span>Synd si propone come punto di accesso privilegiato verso il patrimonio di informazioni che l'intera Pubblica Amministrazione italiana, dalla Presidenza della Repubblica al pi&ugrave; piccolo dei comuni, pubblica quotidianamente sul Web.</span><br/><br/>
Tecnicamente, Synd &egrave; un aggregatore di notizie. Il motore del progetto &egrave; costituito da tre programmi che non si arrestano mai e che individuano i canali di notizie, le estraggono, e rendono possibile una ricerca rapidissima tra le decine di migliaia di notizie scaricate, che aumentano ogni giorno che passa.<div style="text-align:right;"><a href="progetto.php" title="Progetto Synd" id="editoriale">[leggi tutto]</a></div>
                </div>

        </div>

    	<div id="content">

                <div id="pleasewait_content" style="height:160px;">
			<p style="padding:20px; text-align:center;">
				<img src="images/loading.jpg" alt="Caricamento in corso..." title="Caricamento in corso..." />
			</p>
			<p><img src="images/bot.png" alt="" title="" /></p>
		</div>

        	<div id="right" style="visibility:hidden;">
            	
		<h1>Anno</h1>
		<div><table class="filtro_primo_piano"><tr>
		<?php 
		$annoctr = 0;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
		
$result = mysqli_query($conn, "SELECT DISTINCT YEAR(datetime) anno FROM stats__visualizzazioni");
		while($anno = mysqli_fetch_array($result, MYSQLI_ASSOC))
		{
		if($annoctr == 3) { echo("</tr><tr>"); $annoctr = 0; } else $annoctr++;
		?>
			<td>
			<a href="primo_piano.php?anno=<?php if(isset($_SESSION["primo_piano__anno"]) && in_array($anno["anno"], $_SESSION["primo_piano__anno"])) echo("-"); ?><?=$anno["anno"]?>" title="<?=$anno["anno"]?>" class="<?php if(isset($_SESSION["primo_piano__anno"]) && in_array($anno["anno"], $_SESSION["primo_piano__anno"])) echo("primo_piano_selected"); else echo("primo_piano"); ?>"><?=$anno["anno"]?></a>
			</td>
		<?php
		}
		if(mysqli_num_rows($result) == 0) 
		{ $anno["anno"] = date("Y");
		?> 
                        <td>
                        <a href="primo_piano.php?anno=<?php if(isset($_SESSION["primo_piano__anno"]) && in_array($anno["anno"], $_SESSION["primo_piano__anno"])) echo("-"); ?><?=$anno["anno"]?>" title="<?=$anno["anno"]?>" class="<?php if(isset($_SESSION["primo_piano__anno"]) && in_array($anno["anno"], $_SESSION["primo_piano__anno"])) echo("primo_piano_selected"); else echo("primo_piano"); ?>"><?=$anno["anno"]?></a>
                        </td>

		<?php
		}
		?>
		<td colspan="<?=(3-$annoctr)?>">&nbsp;</td></tr></table></div>
		
		<h1>Mese</h1>
		<div><table class="filtro_primo_piano"><tr>
		<?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(1,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=1" title="Gennaio" class="primo_piano">Gennaio</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-1" title="Gennaio" class="primo_piano_selected">Gennaio</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(2,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=2" title="Febbraio" class="primo_piano">Febbraio</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-2" title="Febbraio" class="primo_piano_selected">Febbraio</a></td><?php }?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(3,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=3" title="Marzo" class="primo_piano">Marzo</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-3" title="Marzo" class="primo_piano_selected">Marzo</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(4,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=4" title="Aprile" class="primo_piano">Aprile</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-4" title="Aprile" class="primo_piano_selected">Aprile</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(5,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=5" title="Maggio" class="primo_piano">Maggio</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-5" title="Maggio" class="primo_piano_selected">Maggio</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(6,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=6" title="Giugno" class="primo_piano">Giugno</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-6" title="Giugno" class="primo_piano_selected">Giugno</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(7,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=7" title="Luglio" class="primo_piano">Luglio</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-7" title="Luglio" class="primo_piano_selected">Luglio</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(8,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=8" title="Agosto" class="primo_piano">Agosto</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-8" title="Agosto" class="primo_piano_selected">Agosto</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(9,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=9" title="Settembre" class="primo_piano">Settembre</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-9" title="Settembre" class="primo_piano_selected">Settembre</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(10,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=10" title="Ottobre" class="primo_piano">Ottobre</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-10" title="Ottobre" class="primo_piano_selected">Ottobre</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(11,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=11" title="Novembre" class="primo_piano">Novembre</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-11" title="Novembre" class="primo_piano_selected">Novembre</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__mese"])) || (!in_array(12,$_SESSION["primo_piano__mese"])) ) { ?><td><a href="primo_piano.php?mese=12" title="Dicembre" class="primo_piano">Dicembre</a></td><?php } else { ?><td><a href="primo_piano.php?mese=-12" title="Dicembre" class="primo_piano_selected">Dicembre</a></td><?php } ?>
		</tr></table></div>

		<h1>Giorno</h1>
		<div><table class="filtro_primo_piano_giorno"><tr>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(1,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=1" title="1" class="primo_piano">01</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-1" title="1" class="primo_piano_selected">01</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(2,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=2" title="2" class="primo_piano">02</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-2" title="2" class="primo_piano_selected">02</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(3,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=3" title="3" class="primo_piano">03</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-3" title="3" class="primo_piano_selected">03</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(4,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=4" title="4" class="primo_piano">04</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-4" title="4" class="primo_piano_selected">04</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(5,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=5" title="5" class="primo_piano">05</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-5" title="5" class="primo_piano_selected">05</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(6,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=6" title="6" class="primo_piano">06</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-6" title="6" class="primo_piano_selected">06</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(7,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=7" title="7" class="primo_piano">07</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-7" title="7" class="primo_piano_selected">07</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(8,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=8" title="8" class="primo_piano">08</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-8" title="8" class="primo_piano_selected">08</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(9,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=9" title="9" class="primo_piano">09</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-9" title="9" class="primo_piano_selected">09</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(10,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=10" title="10" class="primo_piano">10</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-10" title="10" class="primo_piano_selected">10</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(11,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=11" title="11" class="primo_piano">11</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-11" title="11" class="primo_piano_selected">11</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(12,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=12" title="12" class="primo_piano">12</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-12" title="12" class="primo_piano_selected">12</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(13,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=13" title="13" class="primo_piano">13</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-13" title="13" class="primo_piano_selected">13</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(14,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=14" title="14" class="primo_piano">14</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-14" title="14" class="primo_piano_selected">14</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(15,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=15" title="15" class="primo_piano">15</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-15" title="15" class="primo_piano_selected">15</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(16,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=16" title="16" class="primo_piano">16</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-16" title="16" class="primo_piano_selected">16</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(17,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=17" title="17" class="primo_piano">17</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-17" title="17" class="primo_piano_selected">17</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(18,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=18" title="18" class="primo_piano">18</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-18" title="18" class="primo_piano_selected">18</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(19,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=19" title="19" class="primo_piano">19</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-19" title="19" class="primo_piano_selected">19</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(20,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=20" title="20" class="primo_piano">20</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-20" title="20" class="primo_piano_selected">20</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(21,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=21" title="21" class="primo_piano">21</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-21" title="21" class="primo_piano_selected">21</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(22,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=22" title="22" class="primo_piano">22</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-22" title="22" class="primo_piano_selected">22</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(23,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=23" title="23" class="primo_piano">23</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-23" title="23" class="primo_piano_selected">23</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(24,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=24" title="24" class="primo_piano">24</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-24" title="24" class="primo_piano_selected">24</a></td><?php } ?>
		</tr><tr>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(25,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=25" title="25" class="primo_piano">25</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-25" title="25" class="primo_piano_selected">25</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(26,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=26" title="26" class="primo_piano">26</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-26" title="26" class="primo_piano_selected">26</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(27,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=27" title="27" class="primo_piano">27</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-27" title="27" class="primo_piano_selected">27</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(28,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=28" title="28" class="primo_piano">28</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-28" title="28" class="primo_piano_selected">28</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(29,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=29" title="29" class="primo_piano">29</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-29" title="29" class="primo_piano_selected">29</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(30,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=30" title="30" class="primo_piano">30</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-30" title="30" class="primo_piano_selected">30</a></td><?php } ?>
                <?php if( (!isset($_SESSION["primo_piano__giorno"])) || (!in_array(31,$_SESSION["primo_piano__giorno"])) ) { ?><td><a href="primo_piano.php?giorno=31" title="31" class="primo_piano">31</a></td><?php } else { ?><td><a href="primo_piano.php?giorno=-31" title="31" class="primo_piano_selected">31</a></td><?php } ?>
		</tr></table></div>


		<br />

		</div>  

            <div id="left" style="visibility: hidden;">

		<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>><h1>Primo Piano<br />
		<span class="filtro_primo_piano_applicato">
		<?php

			$filtro = "";

			if(isset($_SESSION["primo_piano__giorno"]) && 0 < count($_SESSION["primo_piano__giorno"])) 
			{
				if(1 == count($_SESSION["primo_piano__giorno"])) $filtro.="Giorno ";
				else $filtro.="Giorni ";
				foreach($_SESSION["primo_piano__giorno"] as $giorno) $filtro.=str_pad($giorno,2,"0",STR_PAD_LEFT)." ";
			}

			if(isset($_SESSION["primo_piano__mese"]) && 0 < count($_SESSION["primo_piano__mese"]))
			{
				if(0 < strlen($filtro)) $filtro.="/ ";
				if(1 == count($_SESSION["primo_piano__mese"])) $filtro.="Mese ";
				else $filtro.="Mesi ";
				foreach($_SESSION["primo_piano__mese"] as $mese) $filtro.=str_pad($mese,2,"0",STR_PAD_LEFT)." ";
			}

                        if(isset($_SESSION["primo_piano__anno"]) && 0 < count($_SESSION["primo_piano__anno"]))
                        {
                                if(0 < strlen($filtro)) $filtro.="/ ";
				if(1 == count($_SESSION["primo_piano__anno"])) $filtro.="Anno ";
				else $filtro.="Anni ";
                                foreach($_SESSION["primo_piano__anno"] as $anno) $filtro.="$anno ";
                        }

			if($filtro == "") $filtro = "Ultimi sette giorni";

			echo($filtro);

		?>
		</span></h1>
		<div class="text">
                
<?php
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$where_anno = "true";
$where_mese = "true";
$where_giorno = "true";

if(0 < count($_SESSION["primo_piano__anno"])) 
{
	$where_anno = " ( "; 
	foreach($_SESSION["primo_piano__anno"] as $anno) $where_anno.="YEAR(datetime) = $anno OR ";
	$where_anno = substr($where_anno, 0, strlen($where_anno)-3).") ";
}

if(0 < count($_SESSION["primo_piano__mese"]))
{
	$where_mese = " ( ";
	foreach($_SESSION["primo_piano__mese"] as $mese) $where_mese.="MONTH(datetime) = $mese OR ";
	$where_mese = substr($where_mese, 0, strlen($where_mese)-3).") ";
}

if(0 < count($_SESSION["primo_piano__giorno"]))
{
	$where_giorno = " ( ";
	foreach($_SESSION["primo_piano__giorno"] as $giorno) $where_giorno.="DAYOFMONTH(datetime) = $giorno OR ";
	$where_giorno = substr($where_giorno, 0, strlen($where_giorno)-3).") ";
}

$offset = 0;
if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $offset = round($_GET["offset"]);

// Gli hits servono per la navigazione a fondo pagina
if($where_anno != "true" || $where_mese != "true" || $where_giorno != "true") $hits = mysqli_num_rows(mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE $where_anno AND $where_mese AND $where_giorno GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC"));
else $hits = mysqli_num_rows(mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE datetime > DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC"));

if($where_anno != "true" || $where_mese != "true" || $where_giorno != "true") 
{
	$result = mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE $where_anno AND $where_mese AND $where_giorno GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC LIMIT $offset,10");
}
else
{
	$result = mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE datetime > DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC LIMIT $offset,10");
}

while($primopiano = mysqli_fetch_array($result, MYSQLI_ASSOC))
{
?>
<div class="right_b"><a class="primo_piano" href="notizia.php?id=<?=$primopiano["newsid"]?>" title="<?php if(trim(str_replace("\"","&quot;",$primopiano["newstitle"])) != "") echo(trim(str_replace("\"","&quot;",$primopiano["newstitle"]))); else echo("Notizia senza titolo"); ?>"><?=trim($primopiano["newstitle"])?trim($primopiano["newstitle"]):"Notizia senza titolo" ?></a></div>
<?php
}
if(0 == mysqli_num_rows($result)) echo("<span style=\"font-style:italic;\">Nessuna notizia da visualizzare.<br />&nbsp;</span>");

mysqli_close($conn);

?>

		</div>

            </div>







<?php
			
			$offset = 0;  if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $offset  = round($_GET["offset"]);
                        $foffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $foffset = round($_GET["offset"]); $foffset+=10;
                        $boffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $boffset = round($_GET["offset"]); $boffset-=10;
                        if($foffset < $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top: 20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="primo_piano.php?offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+1?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="primo_piano.php?offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>
                        <?php
                        }
                        else if($foffset >= $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="primo_piano.php?offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+1?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                &nbsp;
                                        </h1>
                                </div></div>

                        <?php
			 }
                        else if($foffset < $hits && $boffset < 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                        &nbsp;</h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+1?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="primo_piano.php?offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>

                        <?php
                        }
                        ?>












</div>

            <br />

            <div style="clear: both"><img src="images/spaser.gif" alt="" width="1" height="1" /></div>

        </div>
    <!-- content ends -->
    <div id="bot"></div>

        <!-- footer begins -->
<div id="footer"><a href="http://nutch.apache.org/" title="Apache Nutch">Apache Nutch</a> | <a href="http://www.clusterpoint.com/" title="Clusterpoint">Clusterpoint XML Server</a> | <a href="http://www.metamorphozis.com/" title="Flash Templates">Flash Templates Graphics</a> | <a href="http://www.mysql.com/" title="MySql">MySql</a> | <a href="http://www.php.net/" title="PHP">PHP</a> | <a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional"><abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a> | <a href="http://jigsaw.w3.org/css-validator/validator?uri=http://www.synd.it/synd/styles.css&amp;profile=css3" title="This page validates as CSS"><abbr title="Cascading Style Sheets">CSS</abbr></a></div>

<!-- footer ends -->

</div>
</body>
</html>
