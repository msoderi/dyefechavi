<?php

session_start();

require_once("config.php");
require_once("clusterpoint.php");
require_once("include/myphplib.php");
require_once("include/facebook.php");
require_once("include/Mobile-Detect-2.8.3/Mobile_Detect.php");


$getargs = array_keys($_GET);
if(is_numeric($getargs[0])) 
{
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	$res = mysqli_query($conn, "SELECT news FROM twitter__shorturl WHERE id = {$getargs[0]}");
	$row = $res->fetch_assoc();
	mysqli_close($conn);
	header("Location: $baseurl/notizia.php?id=".urlencode($row["news"]));
	exit;
}

$isMobile = false;
$mobile_detector = new Mobile_Detect;
if($mobile_detector->isMobile() && (!$mobile_detector->isTablet()))
{
	$isMobile = true;
}

//$isMobile = true; 

$news_per_page = 10;
if($isMobile) $news_per_page = 30;

if(!(isFacebookDowntime() || $isMobile) ) {

$facebook = new Facebook(array(
  'appId'  => $fb_appid,
  'secret' => $fb_secret,
));

$fbUserid = $facebook->getUser();

$fbUser = null;
if($fbUserid) $fbUser = $facebook->api('/'+$fbUserid);

}

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$ip = $_SERVER["REMOTE_ADDR"];
$sessid = session_id();
$datetime = time();
$referer = $_SERVER["HTTP_REFERER"];
if(false === strpos($referer, "http://www.synd.it/")) mysqli_query($conn,"INSERT INTO stats__referer(ip, sessid, datetime, referer, newsid) VALUES ('$ip','$sessid',FROM_UNIXTIME($datetime), '$referer', '".mysqli_escape_string($conn, $_GET["id"])."')");
mysqli_close($conn);

$hits = 0;
$newslist = array();
if( ( !isset($_GET["query"]) ) || trim($_GET["query"]) == "" )
{
	$offset = 0;
        if(isset($_GET['offset']) && is_numeric($_GET['offset'])) $offset = round($_GET['offset']);
	$newslist = getListLast($clusterpointConnection, $offset, $news_per_page);

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$newscount = mysqli_query($conn, "SELECT count(*) hits FROM admin__news");
while($currnewscount = mysqli_fetch_array($newscount, MYSQLI_ASSOC)) $hits = $currnewscount["hits"];
mysqli_close($conn);
		
	/*
	$hits = $status["repository"]["documents"];
	*/
	
	
}
/*
elseif ( ( !isset($_GET["query"]) || trim($_GET["query"]) == "" ) && (isset($_GET["channelquery"]) && trim($_GET["channelquery"]) != ""  ) )
{
        $offset = 0;
        if(isset($_GET['offset']) && is_numeric($_GET['offset'])) $offset = round($_GET['offset']);
        $newslist = getSearchResults
        (
                $clusterpointConnection,
                $_GET["channelquery"],
                $offset,
                $news_per_page,
                $hits,
		true
        );
	
}
*/
else
{

	$offset = 0;
	if(isset($_GET['offset']) && is_numeric($_GET['offset'])) $offset = round($_GET['offset']);
	$newslist = getSearchResults
	(
		$clusterpointConnection,
		$_GET["query"],
		$offset,
		$news_per_page,
		$hits
	);

}



/*
if(isset($_GET["fblogout"]) && $_GET["fblogout"] == "yes")
{

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies"))
        {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"] );
        }

        // Finally, destroy the session.
        session_destroy();

        header("Location: http://www.synd.it/synd/index.php"); exit(0);


}
*/

/*
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/">

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

	<link href="<?php 
	if($isMobile) echo("mobile.css"); 
	else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Mobile Safari")) echo("msafari.css");
	else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Chrome")) echo("chrome.css");
	else echo("styles.css"); 
	 
	?>"  rel="stylesheet" type="text/css" />
	<?php if(!$isMobile) { ?><script type="text/javascript" src="include/myjslib.js"></script><?php } ?>
</head>

<body onload="javascript:<?php if(isset($_GET["fblogout"]) && $_GET["fblogout"] == "yes") echo("document.location.href='http://www.synd.it/synd/';"); else {  ?>document.getElementById('pleasewait_content').style.display='none'; document.getElementById('left').style.visibility = 'visible'; document.getElementById('right').style.visibility = 'visible';<?php } ?>">

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
<?php } ?>

<div id="main">
	<div id="logo"><a href="index.php"><img src="images/head_icon.png" alt="Synd"/></a>
<div id="mobile_search" style="display:none;">
 <div id="search_box"><form action="#" method="get"><div id="search_div"><input type="text" name="query" style="-moz-border-radius-bottomleft: 5px; -moz-border-radius-topleft: 5px; font-weight:bold;" value="<?=htmlentities($_GET["query"])?>" onkeypress="javascript:this.style.fontWeight = 'normal';"/><input type="submit" value="&nbsp;" style="-moz-border-radius-bottomright: 5px; -moz-border-radius-topright: 5px;"/></div></form></div>
<div id="full_version" style="font-size:smaller; font-style:normal; position:relative; top:-20px;"><a href="http://www.synd.it/synd" title="Synd.it" style="font-size:small; text-transform:none; position:relative; left:20px;">Passa alla versione completa</a></div>
</div><!-- chiude div id mobile_earch -->
</div><!-- chiude div id logo -->

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






                <div id="pleasewait_content" style="height:170px;">
			<p style="padding:20px; text-align:center;">
				<img src="images/loading.jpg" alt="Caricamento in corso..." title="Caricamento in corso..." />
			</p>
			<p><img src="images/bot.png" alt="" title="" /></p>
		</div>





        	<div id="right" style="visibility:hidden;">

<?php
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$result = mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE datetime > DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC LIMIT 10");
if(0 < mysqli_num_rows($result)) {
?>

            	<h1>Primo Piano</h1>
		<?php
while($primopiano = mysqli_fetch_array($result, MYSQLI_ASSOC))
{
?>
<div class="right_b"><a class="primo_piano" href="notizia.php?id=<?=urlencode($primopiano["newsid"])?>" title="<?=str_replace("\"","&quot;",$primopiano["newstitle"])?>"><?=trim($primopiano["newstitle"])?trim($primopiano["newstitle"]):"Notizia senza titolo"?></a></div>
<?php
}
?>
                <br />
<p style="text-align:right;"><a class="primo_piano" title="Sezione Primo Piano" href="primo_piano.php">[Tutte le notizie in Primo Piano]</a></p>
<br />
<?php
}
mysqli_close($conn);
?>           	

<?php if(!(0 == count($newslist) && ((!isset($_GET["query"])) || (trim($_GET["query"]) == "") ))) { // Se il database non e' vuoto... ?>

<?php if($fbUser == null) { ?>
<h1>Autenticazione</h1>
<p style="margin-bottom:10px;">Synd.it non conserva dati personali. Per la memorizzazione dei segnalibri, la condivisione delle notizie, l'invio di commenti, la rapida visualizzazione delle notizie scaricate dai tuoi canali preferiti ed altre funzionalit&agrave; minori, &egrave; necessaria un'utenza Facebook.</p>
<?php if(!isFacebookDowntime()) { ?><div class="fb-login-button">Login with Facebook</div><?php } else { ?><div style="font-weight:bold;">In questo momento non &egrave; possibile eseguire l'accesso: Facebook pare non funzionare correttamente.</div><?php } ?>
<br /><br /> 
<?php } else { ?>
<h1><?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?></h1>
<img src="http://graph.facebook.com/<?=$fbUser["username"]?>/picture" title="<?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?>" alt="<?=$fbUser["first_name"]?> <?=$fbUser["last_name"]?>" style="float:left; margin-left:10px; margin-top:10px; margin-right:10px;" /><p style="position: relative; top:-2px;">Avendo eseguito l'accesso, puoi memorizzare nel tuo profilo Synd.it i canali e le notizie che ti interessano maggiormente, visualizzare facilmente l'elenco delle ultime notizie pubblicate sui canali che maggiormente ti interessano, commentare le notizie, condividerle, vedere quali tra i tuoi amici utilizzano Synd, quali attivit&agrave; vi svolgono, ed altro ancora.</p>
<div class="right_b" style="margin-top:10px;">
<a href="<?php echo($facebook->getLogoutUrl(array("next" => "http://www.synd.it/synd/index.php?fblogout=yes"))); ?>" title="Logout" style="font-weight:bold; text-decoration:none;">Disconnetti</a>
</div>
<br />
<?php } ?>

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

<br />

<?php if($fbUser != null) { ?>
<h1>I tuoi amici su Synd...</h1>
<script type="text/javascript">
/* <![CDATA[ */
document.write('<div class="fb-activity" data-app-id="355773491165210" data-width="365" data-height="365" data-header="false" data-font="arial" data-recommendations="false"></div>');
/* ]]> */
</script>

<br />
<br />
<?php } ?>

<?php if($fbUser != null) { ?>
<h1>Vuoi lasciare un tuo commento?</h1>
<script type="text/javascript">
/* <![CDATA[ */
document.write('<div class="fb-comments" data-href="http://www.synd.it/synd/" data-num-posts="5" data-width="365"></div>');
/* ]]> */
</script>
<br /><br />
<?php } ?>
<br/>
<?php } // chiude la condizione che vuole che almeno un documento sia presente nel database ?>

<h1>Lo sapevi che...</h1>
<p>
<?php
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$result = mysqli_query($conn, "SELECT * FROM admin__query_sintax_tips ORDER BY RAND() LIMIT 1");
$tip = mysqli_fetch_array($result, MYSQLI_ASSOC);
mysqli_close($conn);
echo($tip["intro"]);
?>
</p>
<p style="text-align:right; margin-top:10px;"><a class="tip" title="Tip" href="tip.php?id=<?=$tip["id"]?>">[scopri come]</a></p>
<br />

<div class="text"><h1>Diritto d'Autore</h1>
I contenuti aggregati da Synd.it sono di propriet&agrave; dei rispettivi autori. L'autore di ciascun contenuto pu&ograve; essere facilmente individuato guardando all'indirizzo del canale su cui lo stesso &egrave; stato originariamente pubblicato, sempre riportato sotto al titolo del contenuto. Gli autori dei contenuti, ed i soggetti da questi incaricati dell'esercizio del diritto d'autore, impartiscono le proprie disposizioni scrivendo a <a href="mailto:mirco.soderi@postecert.it" title="mirco.soderi@postecert.it">mirco.soderi@postecert.it</a>. Il messaggio deve essere inviato da una casella di posta elettronica certificata, e deve essere firmato digitalmente. 
</div>

</div>  

            <div id="left" style="visibility: hidden;">

                <?php
	
		$contatoreNotizia = 0; 
		foreach($newslist as $notizia) { try{

			$contatoreNotizia++;

			if(trim(strip_tags(htmlspecialchars_decode(str_replace("&nbsp;","",str_replace("&#160;","",$notizia["description"]))),"<img>")) == "") $notizia["description"] = "<span style=\"font-style:italic; font-weight: normal;\">Nessun testo riassuntivo appare disponibile per questa notizia.</span>";

			$notizia["id"] = strip_tags($notizia["id"]);
			$notizia["title"] = htmlspecialchars_decode($notizia["title"]);
                        $notizia["title"] = str_replace("&#39;","'",$notizia["title"]);
                        $notizia["title"] = str_replace("&#60;","<",$notizia["title"]);
                        $notizia["title"] = str_replace("&#62;",">",$notizia["title"]);
                        $notizia["title"] = str_replace("&#34;","\"",$notizia["title"]);
			$notizia["title"] = strip_tags($notizia["title"]);
                        $notiziaSenzaTitolo = "";
			if(trim($notizia["title"]) == "") 
			{
				$notizia["title"] = "Notizia senza titolo";
				$notiziaSenzaTitolo = "style=\"font-style:italic; font-weight:normal;\"";
			}
			$notizia["datetime"] = strip_tags(htmlspecialchars_decode($notizia["datetime"]));
			$notizia["link"] = str_replace("&","&amp;",(strip_tags(htmlspecialchars_decode($notizia["link"]))));

			$notizia["description"] = htmlspecialchars_decode($notizia["description"]);
			$notizia["description"] = str_replace("&#39;","'",$notizia["description"]);
			$notizia["description"] = str_replace("&#60;","<",$notizia["description"]);
			$notizia["description"] = str_replace("&#62;",">",$notizia["description"]);
			$notizia["description"] = str_replace("&#34;","\"",$notizia["description"]);

			
			//mycharfix($notizia, "title");
			//mycharfix($notizia, "description");

//for($l = 0; $l < strlen($notizia["title"]); $l++) echo("[".ord(substr($notizia["title"],$l,1))."]");
			$channelurl = substr($notizia["id"],1+strpos($notizia["id"],"@")); 
			$websiteurl = substr($channelurl, 0, 1+strpos($channelurl, "/", 8));
			$channelpath = substr($channelurl, 0, 1+strrpos($channelurl, "/"));
/*
			$notizia["title"] = str_replace("<img","<img onerror=\"if(-1 < this.src.indexOf('http://www.synd.it/synd/')) this.src = '$channelpath' + this.src.substring(24); else if(-1 < this.src.indexOf('http://www.synd.it/')) this.src = '$websiteurl' + this.src.substring(19); else this.style.display = 'none';\"",$notizia["title"]);
                        $notizia["link"] = str_replace("<img","<img onerror=\"if(-1 < this.src.indexOf('http://www.synd.it/synd/')) this.src = '$channelpath' + this.src.substring(24); else if(-1 < this.src.indexOf('http://www.synd.it/')) this.src = '$websiteurl' + this.src.substring(19); else this.style.display = 'none';\"",$notizia["link"]);
                        $notizia["description"] = str_replace("<img","<img onerror=\"if(-1 < this.src.indexOf('http://www.synd.it/synd/')) this.src = '$channelpath' + this.src.substring(24); else if(-1 < this.src.indexOf('http://www.synd.it/')) this.src = '$websiteurl' + this.src.substring(19); else this.style.display = 'none';\"",$notizia["description"]);
*/
                        $notizia["title"] = str_replace("<a","<a onclick=\"if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '$channelpath' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '$websiteurl' + this.href.substring(19); return true;\"",$notizia["title"]);
                        $notizia["link"] = str_replace("<a","<a onclick=\"if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '$channelpath' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '$websiteurl' + this.href.substring(19); return true;\"",$notizia["link"]);
                        $notizia["description"] = str_replace("<a","<a onclick=\"if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '$channelpath' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '$websiteurl' + this.href.substring(19); return true;\"",$notizia["description"]);


			if(false === strpos($notizia["link"],"http://") && false === strpos($notizia["link"], "https://") && trim($notizia["link"]) != "" )
			{
				if(substr(trim($notizia["link"]),0,1) == "/")
				{
					$notizia["link"] = $websiteurl.substr($notizia["link"],1);
				}
				else
				{
					$notizia["link"] = $channelpath.substr($notizia["link"],1);
				}
			}

if(false !== strpos(trim($notizia["title"]),"Notizia senza titolo") && false !== strpos(trim($notizia["description"]), "Nessun testo riassuntivo appare disponibile per questa notizia."))
{
	if(trim($notizia["link"]) == "") continue;
	else $notizia["title"] = $notizia["link"];
}

// Azioni specifiche condotte sulla base del canale
channel_specific($channelurl, $notizia);

		 ?>
		
		<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
			<?php if(true || trim($notizia["link"]) != "") { ?>
<div class="outlink_hotspot">
<?php if($fbUserid) { ?><a href="favorite_news.php?user=<?=md5($fbUser["username"])?>&amp;newsid=<?=urlencode($notizia["id"])?>&amp;newstitle=<?=urlencode($notizia["title"])?>" title="Notizie preferite"><?php if(!isFavoriteNews(md5($fbUser["username"]),$notizia["id"])) { ?><img src="images/notfavorite.gif" title="Aggiungi ai favoriti" alt="Aggiungi ai favoriti" /><?php } else { ?><img src="images/favorite.gif" title="Rimuovi dai favoriti" alt="Rimuovi dai favoriti" /><?php } ?></a>&nbsp;<?php } ?>
	<?php if(trim($notizia["link"]) != "") { ?><a target="_blank" href="<?=trim($notizia["link"])?>" title="<?=trim($notizia["link"])?>"><img src="images/outlink.png" title="<?=trim($notizia["link"])?>" alt="<?=trim($notizia["link"])?>"/></a><?php } ?>
</div>
<?php } ?>
                	<h1>
				<?php if(true || trim($notizia["link"]) != "") { ?><a class="titolo_notizia" <?=$notiziaSenzaTitolo?> title="<?=str_replace("\"","&quot;",$notizia["title"])?>" href="<?php if(!$isMobile){ ?>notizia.php?id=<?=urlencode($notizia["id"])?><?php }else{ echo(trim($notizia["link"])); } ?>"><?php } ?><?=$notizia["title"]?><?php if(true || trim($notizia["link"]) != "") { ?></a><?php } ?><br/>
				<span class="news_datetime">
					Canale:&nbsp;<a href="index.php?query=[<?=urlencode((false === strpos($notizia["id"],"?") ? substr($notizia["id"],1+strpos($notizia["id"],"@")) : substr(substr($notizia["id"],1+strpos($notizia["id"],"@")),0,strpos(substr($notizia["id"],1+strpos($notizia["id"],"@")),"?"))))?>]" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" class="canale_notizia"><?=htmlentities(substr(substr($notizia["id"],1+strpos($notizia["id"],"@")), 0, 70)) ?><?php if(strlen(substr($notizia["id"],1+strpos($notizia["id"],"@"))) > 70) echo("..."); ?></a>&nbsp;<?php if($fbUserid) { ?><a href="favorite_channel.php?user=<?=md5($fbUser["username"])?>&amp;channel=<?=urlencode(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="Canali preferiti"><?php if(!isFavoriteChannel(md5($fbUser["username"]),substr($notizia["id"],1+strpos($notizia["id"],"@")))) { ?><img style="position: relative; top:3px;" src="images/channel_notfavorite.gif" title="Aggiungi ai segnalibri" alt="Aggiungi ai segnalibri" /><?php } else { ?><img style="position:relative; top:3px;"src="images/channel_favorite.gif" title="Rimuovi dai segnalibri" alt="Rimuovi dai segnalibri" /><?php } ?></a>&nbsp;<?php } ?><a target="_blank" href="<?=strpos(substr($notizia["id"],1+strpos($notizia["id"],"@")),"&amp;")?substr($notizia["id"],1+strpos($notizia["id"],"@")):str_replace("&","&amp;",substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>"><img src="images/outlink_channel.png" alt="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" style="position:relative; top:3px;" /></a>

<br/>
Scaricata: <?=myDownloadDatetimeFormat(date("r",$notizia["datetime"]))?>
				</span>
			</h1>
                  	<?php $tmpid = md5(rand()); ?><div class="text" id="div<?=$tmpid?>">
<?php//  for($l = 0; $l < strlen(tidy($notizia["description"])); $l++) echo("[".ord(substr(tidy($notizia["description"]),$l,1))."]"); // debug ?>
				<?=tidy($notizia["description"])?>			
	
				<div style="clear: both; height:6px; overflow:hidden; font-size:1px; margin:0px;">&nbsp;</div>
           	  	</div>
<script type="text/javascript">
/* <![CDATA[ */
var img<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("img");
for(i = 0; i < img<?=$tmpid?>.length; i++)
{
	var currimg = img<?=$tmpid?>.item(i);
	if(-1 < currimg.src.indexOf('http://www.synd.it/synd/')) currimg.src = '<?=$channelpath?>' + currimg.src.substring(24); 
	else if(-1 < currimg.src.indexOf('http://www.synd.it/')) currimg.src = '<?=$websiteurl?>' + currimg.src.substring(19); 
	else if(0 != currimg.src.indexOf('http://') && 0 != currimg.src.indexOf('https://') && 0 != currimg.src.indexOf('www')) currimg.style.display = 'none';	
//	if(currimg.offsetWidth > 400) currimg.style.marginRight = (564-currimg.offsetWidth)+'px';
}

var form<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("form");
for(i = 0; i < form<?=$tmpid?>.length; i++)
{
        var currform = form<?=$tmpid?>.item(i);
        if(-1 < currform.action.indexOf('http://www.synd.it/synd/')) currform.action = '<?=$channelpath?>' + currform.action.substring(24);
        else if(-1 < currform.action.indexOf('http://www.synd.it/')) currform.action = '<?=$websiteurl?>' + currform.action.substring(19);
	else if(0 != currform.action.indexOf('http://') && 0 != currform.action.indexOf('www')) currform.style.display = 'none';
}


var embed<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("embed");
for(i = 0; i < embed<?=$tmpid?>.length; i++) 
{
	var currembed = embed<?=$tmpid?>.item(i);

        if(-1 < currembed.src.indexOf('http://www.synd.it/synd/')) currembed.src = '<?=$channelpath?>' + currembed.src.substring(24);
        else if(-1 < currembed.src.indexOf('http://www.synd.it/')) currembed.src = '<?=$websiteurl?>' + currembed.src.substring(19);

if(-1 < currembed.src.indexOf("file=")) {
if(-1 < currembed.src.indexOf("file=/")) currembed.src = currembed.src.substring(0,currembed.src.indexOf("file=")) + 'file=<?=$websiteurl?>' + currembed.src.substring(6+currembed.src.indexOf("file="));
else currembed.src = currembed.src.substring(0,currembed.src.indexOf("file=")) + 'file=<?=$channelpath?>' + currembed.src.substring(5+currembed.src.indexOf("file="));
 }

if(-1 < currembed.src.indexOf("image="))
{
if(-1 < currembed.src.indexOf("image=/")) currembed.src = currembed.src.substring(0,currembed.src.indexOf("image=")) + 'image=<?=$websiteurl?>' + currembed.src.substring(7+currembed.src.indexOf("image="));
else currembed.src = currembed.src.substring(0,currembed.src.indexOf("image=")) + 'image=<?=$channelpath?>' + currembed.src.substring(6+currembed.src.indexOf("image="));
}

currembed.src = currembed.src.substring(0,currembed.src.indexOf("?"))+currembed.src.substring(currembed.src.indexOf("?")).replace(new RegExp("/", 'g'),"%2F").replace(new RegExp(":", 'g'),"%3A").replace(new RegExp("$", 'g'),"%24").replace(new RegExp("+", 'g'),"%2B").replace(new RegExp(",", 'g'),"%2C").replace(new RegExp(";", 'g'),"%3B").replace(new RegExp("=", 'g'),"%3D").replace(new RegExp("?", 'g'),"%3F").replace(new RegExp("@", 'g'),"%40");

}
var param<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("param");
for(i = 0; i < param<?=$tmpid?>.length; i++)
{
        if(param<?=$tmpid?>.item(i).name != "movie") continue;
	var currparam = param<?=$tmpid?>.item(i);

	if(0 != currparam.value.indexOf("http://") && 0 != currparam.value.indexOf("https://"))
	{
	if(currparam.value.substring(0,1) != "/") currparam.value = '<?=$channelpath?>' + currparam.value;
        else currparam.value = '<?=$websiteurl?>' + currparam.value.substring(1);
	}

	if(-1 < currparam.value.indexOf("file=")) {
	if(-1 < currparam.value.indexOf("file=/")) currparam.value = currparam.value.substring(0,currparam.value.indexOf("file=")) + 'file=<?=$websiteurl?>' + currparam.value.substring(6+currparam.value.indexOf("file="));
	else currparam.value = currparam.value.substring(0,currparam.value.indexOf("file=")) + 'file=<?=$channelpath?>' + currparam.value.substring(5+currparam.value.indexOf("file="));
	}

	if(-1 < currparam.value.indexOf("image="))
	{
        if(-1 < currparam.value.indexOf("image=/")) currparam.value = currparam.value.substring(0,currparam.value.indexOf("image=")) + 'image=<?=$websiteurl?>' + currparam.value.substring(7+currparam.value.indexOf("image="));
	else currparam.value = currparam.value.substring(0,currparam.value.indexOf("image=")) + 'image=<?=$websiteurl?>' + currparam.value.substring(6+currparam.value.indexOf("image="));
	}

currparam.value = currparam.value.substring(0,currparam.value.indexOf("?"))+currparam.value.substring(currparam.value.indexOf("?")).replace(new RegExp("/", 'g'),"%2F").replace(new RegExp(":", 'g'),"%3A").replace(new RegExp("$", 'g'),"%24").replace(new RegExp("+", 'g'),"%2B").replace(new RegExp(",", 'g'),"%2C").replace(new RegExp(";", 'g'),"%3B").replace(new RegExp("=", 'g'),"%3D").replace(new RegExp("?", 'g'),"%3F").replace(new RegExp("@", 'g'),"%40")
;

}

/* ]]> */
</script>

<?php if(false && 0 < count($notizia["attachments"])) { ?>
<div class="attachments" style="border-top: thin dotted #7e9fb0; line-height:200%; margin-top:10px; padding-top:10px;">
<?php for($i = 0; $i < count($notizia["attachments"]); $i++) { ?>
<a target="_blank" style="text-decoration:none;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>" href="<?php if(false === strpos($notizia["attachments"][$i][1],"&amp;")) echo(str_replace("&","&amp;",$notizia["attachments"][$i][1])); else echo($notizia["attachments"][$i][1]); ?>"  onclick="if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '<?=$channelpath?>' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '<?=$websiteurl?>' + this.href.substring(19); return true;">
<img style="position:relative; top:4px;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>" src="images/attachment.png" alt="> "/>
<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>
</a><br/>
<?php } ?>
</div>
<?php } ?>

<?php if(0 < count($notizia["attachments"])) { ?>
<div class="attachments" style="border-top: thin dotted #7e9fb0; line-height:200%; margin-top:10px; padding-top:10px; margin-bottom:10px;">
<?php for($i = 0; $i < count($notizia["attachments"]); $i++) { ?>
<a target="_blank" style="text-decoration:none;" title="<?=str_replace("\"","&quot;",$notizia["attachments"][$i][0])?>" href="<?php if(false === strpos($notizia["attachments"][$i][1],"&amp;")) echo(str_replace("&","&amp;",$notizia["attachments"][$i][1])); else echo($notizia["attachments"][$i][1]); ?>"  onclick="if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '<?=$channelpath?>' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '<?=$websiteurl?>' + this.href.substring(19); return true;">
<img style="position:relative; top:4px;" title="<?=str_replace("\"","&quot;",$notizia["attachments"][$i][0])?>" src="images/attachment.png" alt="> "/>
<?=str_replace("&amp;","&",htmlspecialchars($notizia["attachments"][$i][0]))?>
</a><br/>
<?php } ?>
</div>
<?php } ?>


			<div style="clear:both; height: 10px; overflow:hidden; font-size:1px; margin:0px;">&nbsp;</div>

		</div>

		<?php } catch(Exception $e) {} ?>
		
		<?php } ?>

		<?php if(0 == count($newslist) && isset($_GET["query"]) && trim($_GET["query"]) != "") { ?>

                <div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                        <h1>nessun risultato</h1>
                        <div class="text">
                                <p>Spiacenti, la ricerca non ha prodotto alcun esito.</p>
				<p>&nbsp;</p>
                        </div>
                </div>

		<?php } ?>

		<?php if(0 == count($newslist) && ((!isset($_GET["query"])) || (trim($_GET["query"]) == "") )) { ?>
                <div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                        <h1>nessun documento</h1>
                        <div class="text">
                                <p>Nessun documento &egrave; stato sino ad oggi inviato al server per la memorizzazione.</p>
                                <p>&nbsp;</p>
                        </div>
                </div>
		<?php } ?>

                <?php
                if(!(0 == count($newslist) && ((!isset($_GET["query"])) || (trim($_GET["query"]) == "") )))
                {
                        $offset = 0;  if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $offset  = round($_GET["offset"]); 
			$foffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $foffset = round($_GET["offset"]); $foffset+=$news_per_page;
                        $boffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $boffset = round($_GET["offset"]); $boffset-=$news_per_page;
                        if($foffset < $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top: 20px; display: table; <?php if($isMobile) { } else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php }  ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="index.php?query=<?=urlencode($_GET["query"])?>&amp;offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
					<h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/$news_per_page+1?> / <?=floor($hits/$news_per_page)+($hits%$news_per_page?1:0)?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="index.php?query=<?=urlencode($_GET["query"])?>&amp;offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>
                        <?php
                        }
                        else if($foffset >= $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if($isMobile){} else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php }  ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="index.php?query=<?=urlencode($_GET["query"])?>&amp;offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/$news_per_page+1?> / <?=floor($hits/$news_per_page)+($hits%$news_per_page?1:0)?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                        	&nbsp;
					</h1>
                                </div></div>

                        <?php
                        }
                        else if($foffset < $hits && $boffset < 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if($isMobile){} else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php }  ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                        &nbsp;</h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/$news_per_page+1?> / <?=floor($hits/$news_per_page)+($hits%$news_per_page?1:0)?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="index.php?query=<?=urlencode($_GET["query"])?>&amp;offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>

                        <?php
                        }
                        ?>










<?php if($hits < $news_per_page+1) { ?>
<div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="javascript:history.back();">&laquo;&nbsp;indietro</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?php if($hits == 0) echo("&nbsp;"); else echo("1 / 1"); ?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                &nbsp;
                                        </h1>
                                </div></div>
<?php } ?>










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
