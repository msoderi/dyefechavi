<?php

session_start();

require_once("config.php");
require_once("clusterpoint.php");
require_once("include/myphplib.php");
require_once("include/facebook.php");

if(!isFacebookDowntime())
{
$facebook = new Facebook(array(
  'appId'  => $fb_appid,
  'secret' => $fb_secret,
));

$fbUserid = $facebook->getUser();

$fbUser = null;
if($fbUserid) $fbUser = $facebook->api('/'+$fbUserid);

}

$channelscount = getChannelsdCount();

$mittente = "";
if(isset($_POST["mittente"]) && trim($_POST["mittente"]) != "") $mittente = "MITTENTE:\n".htmlspecialchars($_POST["mittente"])."\n\n";
$email = "";
if(isset($_POST["email"]) && trim($_POST["email"])) $email = "E-MAIL RISPOSTA:\n".htmlspecialchars($_POST["email"])."\n\n";
$oggetto = "";
if(isset($_POST["oggetto"]) && trim($_POST["oggetto"])) $oggetto = "OGGETTO:\n".htmlspecialchars($_POST["oggetto"])."\n\n";
$contenuto = "";
if(isset($_POST["contenuto"]) && trim($_POST["contenuto"])) $contenuto = "CONTENUTO:\n".htmlspecialchars($_POST["contenuto"])."\n\n";
$from = "";
if( isset($_POST["email"]) && trim($_POST["email"]) != "" ) $from = "From: {$_POST["email"]}"; else $from = "From: info@synd.it";

$messaggioInviato = mail("info@synd.it",str_replace("<br/>"," ",html_entity_decode(messageConfirmationCharfix("CONTATTO WEB - $oggetto"))),str_replace("<br/>","\n",html_entity_decode(messageConfirmationCharfix("$mittente$email$oggetto$contenuto"))),"Content-Type: text/plain;\r\n$from");

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Synd - Alla fonte della notizia</title>
        <meta name="keywords" content="aggregatore, rss, notizia, pubblica, amministrazione" />
        <meta name="description" content="Un aggregatore di notizie e contenuti dalla Pubblica Amministrazione italiana. Sul Web, completo, gratuito, e per tutti." />
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
 
<link href="<?php if(false === strpos($_SERVER["HTTP_USER_AGENT"],"Mobile Safari")) echo("styles.css"); else echo("msafari.css"); ?>"  rel="stylesheet" type="text/css" />
        <script type="application/x-javascript" src="include/myjslib.js"></script>

</head>

<body>

<div id="fb-root"></div>

<?php if(!isFacebookDowntime()) { ?>
      <script type="text/javascript">
/* <![CDATA[ */

        window.fbAsyncInit = function() {
          FB.init({
            appId      : '<?=$fb_appid?>', // App ID
            channelUrl : '<?=$fb_channelfile?>', // Channel File
            status     : true, // check login status
            cookie     : true, // enable cookies to allow the server to access the session
            xfbml      : true  // parse XFBML
          });

        FB.Event.subscribe('auth.login', function() {
          window.location.reload();
        });

          // Additional initialization code here
        };
        // Load the SDK Asynchronously
        (function(d){
           var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
           if (d.getElementById(id)) {return;}
           js = d.createElement('script'); js.id = id; js.async = true;
           js.src = "//connect.facebook.net/<?=$fb_locale?>/all.js";
           ref.parentNode.insertBefore(js, ref);
         }(document));

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?=$fb_locale?>/all.js#xfbml=1&appId=<?=$fb_appid?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
/* ]]> */
      </script>
<?php }  ?>


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
        	<div id="right">

<?php if($fbUser == null) { ?>
<h1>Autenticazione</h1>
<p style="margin-bottom:10px;">Synd.it non conserva dati personali. Per la memorizzazione dei segnalibri, la condivisione delle notizie, l'invio di commenti, la rapida visualizzazione delle notizie scaricate dai tuoi canali preferiti ed altre funzionalit&agrave; minori, &egrave; necessaria un'utenza Facebook.</p>
<div class="fb-login-button">Login with Facebook</div>
<br />
<br />
<?php } else { ?>
<h1><?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?></h1>
<img src="http://graph.facebook.com/<?=$fbUser["username"]?>/picture" title="<?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?>" alt="<?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?>" style="float:left; margin-left:10px; margin-top:10px; margin-right:10px;" /><p style="position: relative; top:-2px;">Avendo eseguito l'accesso, puoi memorizzare nel tuo profilo Synd.it i canali e le notizie che ti interessano maggiormente, visualizzare facilmente l'elenco delle ultime notizie pubblicate sui canali che maggiormente ti interessano, commentare le notizie, condividerle, vedere quali tra i tuoi amici utilizzano Synd, quali attivit&agrave; vi svolgono, ed altro ancora.</p>
<?php } ?>
<br />
<h1>Ti piace Synd? Spargi la voce...</h1>
<p>Synd si integra con le due pi&ugrave; importanti realt&agrave; del Social Web a livello mondiale: Facebook, e Twitter. Usali per far sapere ai tuoi amici, conoscenti, colleghi, che hai scoperto questo sito...</p>
<p style="margin-top:10px;">
<a href="https://twitter.com/share?url=<?=urlencode("http://www.synd.it/")?>&amp;text=Un%20aggregatore%20di%20notizie%20e%20contenuti%20dalla%20PA%20italiana.%20Sul%20Web,%20completo,%20gratuito,%20e%20per%20tutti.%20Scoprite%20anche%20voi&amp;related=syndchannel:Account%20Twitter%20di%20Synd.it,mircosoderi:L'ideatore,%20creatore,%20amministratore...%20di%20Synd.it&amp;count=horizontal&amp;lang=it&amp;size=medium" class="twitter-share-button">Tweet</a>
<script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</p>
               
<script type="text/javascript">
/* <![CDATA[ */
document.write('<div style="margin-top:10px;" class="fb-like" data-href="http://www.synd.it/synd/" data-send="true" data-layout="standard" data-width="365" data-show-faces="true" data-font="arial"></div>');
/* ]]> */
</script>
 
           	</div>  
            <div id="left">
		<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                  	<?php if($messaggioInviato) { ?><h1>Messaggio inviato!</h1><?php } else { ?><h1>Errore!</h1><?php } ?>
			<div class="text">
<?php if($messaggioInviato) { ?><p style="font-family:monospace;"><?=str_replace("\n","<br />",trim(messageConfirmationCharfix("$mittente$email$oggetto$contenuto")))?></p><?php }  else echo("<p>Si &egrave; verificato un errore durante l'invio del messaggio.</p>"); ?><br />
			</div>

           	  </div>

<div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="javascript:history.back();">&laquo;&nbsp;indietro</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;">&nbsp;</h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                &nbsp;
                                        </h1>
                                </div></div>

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
 
