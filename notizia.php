<?php
session_start();

require_once("config.php");
require_once("clusterpoint.php");
require_once("include/myphplib.php");
require_once("include/facebook.php");

if(!isFacebookDowntime()) {

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


$channelscount = getChannelsdCount();

$dochtml = array();

if(isset($_GET["id"]))
{

        $xmldoc = getDocument($clusterpointConnection, $_GET["id"]);
	$xmldomdoc = new DOMDocument();
	$xmldomdoc->loadXML($xmldoc->asXML());
	$notizia = DOMDocument2Notizia($xmldoc);

	// Registro la visualizzazione della notizia cosi' da poter mostrare agli utenti le notizie piu' cliccate in primo piano.

	foreach($xmldomdoc->documentElement->childNodes as $child) 
	{ 

		if($child->nodeType != XML_TEXT_NODE && $child->tagName == "rss" && 0 < $child->getElementsByTagName("channel")->item(0)->getElementsByTagName("item")->item(0)->getElementsByTagName("title")->length )
		{
			$ip = $_SERVER["REMOTE_ADDR"];
			$sessid = session_id();
			$datetime = time();
			$newsid = $_GET["id"];
                        $conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

			$newstitle = mysqli_escape_string($conn, $child->getElementsByTagName("channel")->item(0)->getElementsByTagName("item")->item(0)->getElementsByTagName("title")->item(0)->textContent);
			if(!mysqli_num_rows(mysqli_query($conn,"SELECT * FROM stats__visualizzazioni WHERE ip = '$ip' AND sessid = '$sessid' AND newsid='$newsid' AND newstitle = '$newstitle'")))
			{
				mysqli_query($conn, "INSERT INTO stats__visualizzazioni(ip,sessid,datetime,newsid,newstitle) VALUES ('$ip','$sessid',FROM_UNIXTIME($datetime),'$newsid','$newstitle')");
			}
			mysqli_close($conn);
		}	

		if($child->nodeType != XML_TEXT_NODE && $child->tagName == "default:feed" && 0 < $child->getElementsByTagNameNS("http://www.w3.org/2005/Atom","entry")->item(0)->getElementsByTagNameNS("http://www.w3.org/2005/Atom","title")->length )
		{
                        $ip = $_SERVER["REMOTE_ADDR"];
                        $sessid = session_id();
                        $datetime = time();
                        $newsid = $_GET["id"];
                        $conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

			$newstitle = mysqli_escape_string($conn, $child->getElementsByTagNameNS("http://www.w3.org/2005/Atom","entry")->item(0)->getElementsByTagNameNS("http://www.w3.org/2005/Atom","title")->item(0)->textContent);
                        if(!mysqli_num_rows(mysqli_query($conn,"SELECT * FROM stats__visualizzazioni WHERE ip = '$ip' AND sessid = '$sessid' AND newsid='$newsid' AND newstitle = '$newstitle'")))
                        {
                                mysqli_query($conn, "INSERT INTO stats__visualizzazioni(ip,sessid,datetime,newsid,newstitle) VALUES ('$ip','$sessid',FROM_UNIXTIME($datetime),'$newsid','$newstitle')");
                        }
                        mysqli_close($conn);

		}

                if($child->nodeType != XML_TEXT_NODE && $child->tagName == "rdf:RDF" && 0 < $child->getElementsByTagNameNS("http://purl.org/rss/1.0/","item")->item(0)->getElementsByTagNameNS("http://purl.org/rss/1.0/","title")->length )
                {
                        $ip = $_SERVER["REMOTE_ADDR"];
                        $sessid = session_id();
                        $datetime = time();
                        $newsid = $_GET["id"];
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
                        
$newstitle = mysqli_escape_string($conn, $child->getElementsByTagNameNS("http://purl.org/rss/1.0/","item")->item(0)->getElementsByTagNameNS("http://purl.org/rss/1.0/","title")->item(0)->textContent);
                        if(!mysqli_num_rows(mysqli_query($conn,"SELECT * FROM stats__visualizzazioni WHERE ip = '$ip' AND sessid = '$sessid' AND newsid='$newsid' AND newstitle = '$newstitle'")))
                        {
                                mysqli_query($conn, "INSERT INTO stats__visualizzazioni(ip,sessid,datetime,newsid,newstitle) VALUES ('$ip','$sessid',FROM_UNIXTIME($datetime),'$newsid','$newstitle')");
                        }
                        mysqli_close($conn);

                }

	}
	

}
$channelpath = "";
$websiteurl = "";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"> 

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Synd - Alla fonte della notizia</title>
        <meta name="keywords" content="aggregatore, rss, notizia, pubblica, amministrazione" />
        <meta name="description" content="Un aggregatore di notizie e contenuti dalla Pubblica Amministrazione italiana. Sul Web, completo, gratuito, e per tutti." />

<meta property="og:title" content="<?=trim(strip_tags(htmlspecialchars_decode($notizia["title"]))) != "" ? strip_tags(htmlspecialchars_decode($notizia["title"])) : "Notizia senza titolo" ?>" />
<meta property="og:type" content="article" />
<meta property="og:url" content="<?=$baseurl?>/notizia.php?id=<?=urlencode($notizia["id"])?>" />
<!-- <meta property="og:image" content="http://www.synd.it/synd/images/head_icon_squared.png" /> -->
<meta property="og:image" content="http://www.synd.it/synd/images/fb_page_icon.png" />
<meta property="og:site_name" content="Synd.it, alla fonte della notizia" />
<meta property="fb:admins" content="<?=$fb_admins?>" />
<meta property="fb:app_id" content="<?=$fb_appid?>" />
<meta property="og:description" content="Scaricata da <?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" />
<meta property="og:locale" content="<?=$fb_locale?>" />

	<link href="<?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Mobile Safari")) echo("msafari.css"); 
        else if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Chrome")) echo("chrome.css");
else echo("styles.css");

 ?>"  rel="stylesheet" type="text/css" />
        <script language="javascript" type="application/x-javascript" src="include/myjslib.js"></script>

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
<?php } ?>

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

<h1>Notizia interessante? Condividila!</h1>

<p>Synd si integra con le due pi&ugrave; importanti realt&agrave; del Social Web a livello mondiale: Facebook, e Twitter. Usali per diffondere questa notizia tra i tuoi amici, conoscenti, colleghi...</p>
<?php
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
        
$res = mysqli_query($conn, "SELECT id FROM twitter__shorturl WHERE news = '{$notizia["id"]}'");
        $row = $res->fetch_assoc();
        mysqli_close($conn);
        $hashtag__channelurl = substr($notizia["id"],1+strpos($notizia["id"],"@"));
        $text__newstitle = trim(str_replace("\n"," ",strip_tags(tidy(str_replace("&#39;","'",htmlspecialchars_decode($notizia["title"]))))));
        while(false !== strpos($text__newstitle,"  ")) $text__newstitle = str_replace("  "," ",$text__newstitle);
        if($text__newstitle == "")
        {
                $text__newstitle = htmlspecialchars_decode($notizia["description"]);
                $text__newstitle = str_replace("&#39;","'",$text__newstitle);
                $text__newstitle = tidy($text__newstitle);
                $text__newstitle = strip_tags($text__newstitle);
                $text__newstitle = str_replace("\n"," ",$text__newstitle);
                $text__newstitle = trim($text__newstitle);
                while(false !== strpos($text__newstitle,"  ")) $text__newstitle = str_replace("  "," ",$text__newstitle);
        }
        $text__newstitle = str_replace("\"","&quot;",$text__newstitle);
        $link__syndshortlink = "http://www.synd.it/?{$row["id"]}";
?>
<p style="margin-top:10px;">
<!-- <a href="https://twitter.com/share" data-url="<?=$link__syndshortlink?>" data-text="<?=substr("#$hashtag__channelurl $text__newstitle",0,140)?>" data-related="syndchannel:Account Twitter di Synd.it,mircosoderi:L'ideatore, creatore, amministratore... di Synd.it" data-count="horizontal" data-lang="it" data-size="large" class="twitter-share-button">Tweet</a> -->
<script type="text/javascript">
/* <![CDATA[ */

document.write("<a href=\"https://twitter.com/share\" data-url=\"<?=$link__syndshortlink?>\" data-text=\"<?=substr("#$hashtag__channelurl $text__newstitle",0,140)?>\" data-related=\"syndchannel:Account Twitter di Synd.it,mircosoderi:L'ideatore, creatore, amministratore... di Synd.it\" data-count=\"horizontal\" data-lang=\"it\" data-size=\"medium\" class=\"twitter-share-button\">Tweet</a>");

!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");

/* ]]> */
</script>
</p>

<script type="text/javascript">
/* <![CDATA[ */
document.write('<div style="margin-top:10px;" class="fb-like" data-href="http://www.synd.it/synd/notizia.php?id=<?=urlencode($notizia["id"])?>" data-send="true" data-layout="standard" data-width="365" data-show-faces="true" data-font="arial"></div>');
/* ]]> */
</script>

<br />

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
    

          
           	</div>  
            <div id="left">
                  	














<?php	

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
/*			$notizia["title"] = str_replace("<img","<img onerror=\"if(-1 < this.src.indexOf('http://www.synd.it/synd/')) this.src = '$channelpath' + this.src.substring(24); else if(-1 < this.src.indexOf('http://www.synd.it/')) this.src = '$websiteurl' + this.src.substring(19); else this.style.display = 'none';\"",$notizia["title"]);
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

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
        
$res = mysqli_query($conn, "SELECT id FROM twitter__shorturl WHERE news = '{$notizia["id"]}'");
        $row = $res->fetch_assoc();
        mysqli_close($conn);
	$tweet_title = strtoupper(trim(strip_tags(html_entity_decode($notizia["title"]))))." - ";
	$tweet_text = substr(trim(strip_tags(html_entity_decode($notizia["description"]))), 0, 110-strlen($tweet_title));
	$tweet_text = trim(substr($tweet_text, 0, strrpos($tweet_text," ")))."... ";
        $tweet_shorturl = "http://www.synd.it/?{$row["id"]}";
	$tweet_this = urlencode($tweet_title.$tweet_text.$tweet_shorturl);

// Azioni specifiche condotte sulla base del canale
channel_specific($channelurl, $notizia);

		 ?>
		
		<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                        <?php if(true || trim($notizia["link"]) != "") { ?><div class="outlink_hotspot">

<?php if($fbUserid) { ?><a target="_blank" href="favorite_news.php?user=<?=md5($fbUser["username"])?>&amp;newsid=<?=urlencode($notizia["id"])?>&amp;$newstitle=<?=urlencode($notizia["title"])?>" title="Aggiungi ai favoriti"><?php if(!isFavoriteNews(md5($fbUser["username"]),$notizia["id"])) { ?><img src="images/notfavorite_inner.gif" title="Aggiungi ai favoriti" alt="Aggiungi ai favoriti" /><?php } else { ?><img src="images/favorite_inner.gif" title="Rimuovi dai favoriti" alt="Rimuovi dai favoriti" /><?php } ?></a>&nbsp;<?php } ?>

<?php if(trim($notizia["link"]) != "") { ?><a target="_blank" href="<?=trim($notizia["link"])?>" title="<?=trim($notizia["link"])?>"><img src="images/news_outlink.png" title="<?=trim($notizia["link"])?>" alt="<?=trim($notizia["link"])?>"/></a><?php } ?>

</div><?php } ?>                	
<h1>
				
				<?=$notizia["title"]?>

				<br/>

                                <span class="news_datetime">
<?php 
$channelQuery = substr($notizia["id"],1+strpos($notizia["id"],"@"));
if(false !== strpos($channelQuery,"?")) $channelQuery = substr($channelQuery,0,strpos($channelQuery,"?"));
?>
                                        <!-- Canale:&nbsp;<a href="index.php?query=<?=urlencode("[$channelQuery]")?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" class="canale_notizia"><?php echo(substr($notizia["id"],1+strpos($notizia["id"],"@"))); ?></a>&nbsp;<?php if($fbUserid) { ?><a href="favorite_channel.php?user=<?=md5($fbUser["username"])?>&amp;channel=<?=urlencode(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="Canali preferiti"><?php if(!isFavoriteChannel(md5($fbUser["username"]),substr($notizia["id"],1+strpos($notizia["id"],"@")))) { ?><img src="images/channel_notfavorite.gif" title="Aggiungi ai favoriti" alt="Aggiungi ai favoriti" style="position:relative; top:3px;" /><?php } else { ?><img src="images/channel_favorite.gif" title="Rimuovi dai favoriti" alt="Rimuovi dai favoriti" style="position:relative; top:3px;" /><?php } ?></a>&nbsp;<?php } ?><a target="_blank" href="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>"><img src="images/outlink_channel.png" alt="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" style="position:relative; top:3px;" /></a> -->
Canale:&nbsp;<a href="index.php?query=<?=urlencode("[".(false === strpos($notizia["id"],"?") ? substr($notizia["id"],1+strpos($notizia["id"],"@")) : substr(substr($notizia["id"],1+strpos($notizia["id"],"@")),0,strpos(substr($notizia["id"],1+strpos($notizia["id"],"@")),"?")))."]")?>" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" class="canale_notizia"><?=htmlentities(substr(substr($notizia["id"],1+strpos($notizia["id"],"@")), 0, 70)) ?><?php if(strlen(substr($notizia["id"],1+strpos($notizia["id"],"@"))) > 70) echo("..."); ?></a>&nbsp;<?php if($fbUserid) { ?><a href="favorite_channel.php?user=<?=md5($fbUser["username"])?>&amp;channel=<?=urlencode(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="Canali preferiti"><?php if(!isFavoriteChannel(md5($fbUser["username"]),substr($notizia["id"],1+strpos($notizia["id"],"@")))) { ?><img style="position: relative; top:3px;" src="images/channel_notfavorite.gif" title="Aggiungi ai segnalibri" alt="Aggiungi ai segnalibri" /><?php } else { ?><img style="position:relative; top:3px;"src="images/channel_favorite.gif" title="Rimuovi dai segnalibri" alt="Rimuovi dai segnalibri" /><?php } ?></a>&nbsp;<?php } ?><a target="_blank" href="<?=strpos(substr($notizia["id"],1+strpos($notizia["id"],"@")),"&amp;")?substr($notizia["id"],1+strpos($notizia["id"],"@")):str_replace("&","&amp;",substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>"><img src="images/outlink_channel.png" alt="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="<?=htmlentities(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" style="position:relative; top:3px;" /></a>




<br/>
Scaricata: <?=myDownloadDatetimeFormat(date("r",$notizia["datetime"]))?>
                                </span>

			</h1>
                  	<?php $tmpid=md5(rand()); ?><div class="text" id="div<?=$tmpid?>">
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
        else if(0 != currimg.src.indexOf('http://') && 0 != currimg.src.indexOf('https://')  && 0 != currimg.src.indexOf('www')) currimg.style.display = 'none';
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

if(-1 < currembed.src.indexOf("image=")) {
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
	if(0 != currparam.value.indexOf("http://") && 0 != currparam.value.indexOf("https://")) {
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

</div>

<!-- Dettagli notizia e canale -->
<?php $dettagliDisponibili = false; ?>
<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
<?php if($notizia["title"] != "") $notizia["title"] = ": ".$notizia["title"]; ?>
<h1>Dettagli notizia</h1>
<div class="text contenutoDettagliNotizia">

<?php if(0 < count($notizia["author"])) { $dettagliDisponibili = true; ?>
<?php if(1 == count($notizia["author"])) { ?><p><span style="font-weight:bold;">Autore della notizia</span><br/><?php } ?>
<?php if(1 < count($notizia["author"])) { ?><p><span style="font-weight:bold;">Autori della notizia</span><br/><?php } ?>
<?php for($i = 0; $i < count($notizia["author"]); $i++) { ?>
<?php if($i > 0) echo("<br/>"); ?>
<?php if(trim($notizia["author"][$i]["uri"]) != "") { ?><a target="_blank" href="<?=$notizia["author"][$i]["uri"]?>" title="<?=$notizia["author"][$i]["name"]?>"><?php } ?>
<?php if(trim($notizia["author"][$i]["name"]) != "") echo($notizia["author"][$i]["name"]); else echo($notizia["author"][$i]["uri"]); ?>
<?php if(trim($notizia["author"][$i]["uri"]) != "") { ?></a><?php } ?>
<?php if(trim($notizia["author"][$i]["uri"]) != "" || trim($notizia["author"][$i]["name"]) != "") { ?><br/><?php } ?>
<?php if(trim($notizia["author"][$i]["email"]) != "") { ?><?php if(isMailAddress(trim($notizia["author"][$i]["email"]))) { ?><a href="mailto:<?=trim($notizia["author"][$i]["email"])?>" title="<?=trim($notizia["author"][$i]["email"])?>"><?=trim($notizia["author"][$i]["email"])?></a><?php } else { ?><?=trim($notizia["author"][$i]["email"])?><?php } ?><?php } ?>
<?php } ?>
</p>
<?php } ?>

<?php if(0 < count($notizia["contributor"])) {  $dettagliDisponibili = true; ?>
<?php if(1 == count($notizia["contributor"])) { ?><p><span style="font-weight:bold;">Collaboratore alla notizia</span><br/><?php } ?>
<?php if(1 < count($notizia["contributor"])) { ?><p><span style="font-weight:bold;">Collaboratori alla notizia</span><br/><?php } ?>
<?php for($i = 0; $i < count($notizia["contributor"]); $i++) { ?>
<?php if($i > 0) echo("<br/>"); ?>
<?php if(trim($notizia["contributor"][$i]["uri"]) != "") { ?><a target="_blank" href="<?=$notizia["contributor"][$i]["uri"]?>" title="<?=$notizia["contributor"][$i]["name"]?>"><?php } ?>
<?php if(trim($notizia["contributor"][$i]["name"]) != "") echo($notizia["contributor"][$i]["name"]); else echo($notizia["contributor"][$i]["uri"]); ?>
<?php if(trim($notizia["contributor"][$i]["uri"]) != "") { ?></a><?php } ?>
<?php if(trim($notizia["contributor"][$i]["uri"]) != "" || trim($notizia["contributor"][$i]["name"]) != "") { ?><br/><?php } ?>
<?php if(trim($notizia["contributor"][$i]["email"]) != "") { ?><?php if(isMailAddress(trim($notizia["contributor"][$i]["email"]))) { ?><a href="mailto:<?=trim($notizia["contributor"][$i]["email"])?>" title="<?=trim($notizia["contributor"][$i]["email"])?>"><?=trim($notizia["contributor"][$i]["email"])?></a><?php } else { ?><?=trim($notizia["contributor"][$i]["email"])?><?php } ?><?php } ?>
<?php } ?>
</p>
<?php } ?>

<?php if(trim($notizia["pubdate"]) != "") { $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Data di pubblicazione della notizia</span><br/><?=myPublicationDatetimeFormat($notizia["pubdate"])?></p>
<?php } ?>

<?php if(trim($notizia["published"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Data di pubblicazione della notizia</span><br/><?=myPublicationDatetimeFormat($notizia["published"])?></p>
<?php } ?>

<?php if(trim($notizia["updated"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Data di ultimo aggiornamento della notizia</span><br/><?=myPublicationDatetimeFormat($notizia["updated"])?></p>
<?php } ?>

<?php if(trim($notizia["comments"]) != "") {  $dettagliDisponibili = true;  ?>
<p><span style="font-weight:bold;">Commenti alla notizia sul sito dell'autore</span><br/><a target="_blank" href="<?=$notizia["comments"]?>" title="commenti alla notizia"><?=$notizia["comments"]?></a></p>
<?php } ?>

<?php if(trim($notizia["channel_title"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Titolo del canale</span><br/>
<?php 
if(false === strpos($notizia["channel_title"],"</") && false === strpos($notizia["channel_title"],"&lt;/") && false === strpos($notizia["channel_title"],"<br") && false === strpos($notizia["channel_title"],"&lt;br")) echo(str_replace("\n","<br />",trim($notizia["channel_title"]))); else echo(htmlspecialchars_decode($notizia["channel_title"]));

?>
</p>
<?php } ?>

<?php if(trim($notizia["channel_subtitle"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Sottotitolo del canale</span><br/>
<?php
if(false === strpos($notizia["channel_subtitle"],"</") && false === strpos($notizia["channel_subtitle"],"&lt;/") && false === strpos($notizia["channel_subtitle"],"<br") && false === strpos($notizia["channel_subtitle"],"&lt;br")) echo(str_replace("\n","<br />",trim($notizia["channel_subtitle"]))); else echo(htmlspecialchars_decode($notizia["channel_subtitle"]));

?>
</p>
<?php } ?>

<?php if(trim($notizia["channel_description"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Descrizione del canale</span><br/>
<?php
if(false === strpos($notizia["channel_description"],"</") && false === strpos($notizia["channel_description"],"&lt;/") && false === strpos($notizia["channel_description"],"<br") && false === strpos($notizia["channel_description"],"&lt;br")) echo(str_replace("\n","<br />",trim($notizia["channel_description"]))); else echo(htmlspecialchars_decode($notizia["channel_description"]));

?>
</p>
<?php } ?>

<?php if(trim($notizia["channel_managingEditor"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Responsabile editoriale del canale</span><br/><?php if(isMailAddress(trim($notizia["channel_managingEditor"]))) { ?><a href="mailto:<?=trim($notizia["channel_managingEditor"])?>" title="<?=trim($notizia["channel_managingEditor"])?>"><?=trim($notizia["channel_managingEditor"])?></a><?php } else { ?><?=trim($notizia["channel_managingEditor"])?><?php } ?></p>
<?php } ?>

<?php if(trim($notizia["channel_webMaster"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Responsabile tecnico del canale</span><br/><?php if(isMailAddress(trim($notizia["channel_webMaster"]))) { ?><a href="mailto:<?=trim($notizia["channel_webMaster"])?>" title="<?=trim($notizia["channel_webMaster"])?>"><?=trim($notizia["channel_webMaster"])?></a><?php } else { ?><?=trim($notizia["channel_webMaster"])?><?php } ?></p>
<?php } ?>

<?php if(trim($notizia["channel_lastBuildDate"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Ultimo aggiornamento del canale</span><br/><?=myPublicationDatetimeFormat($notizia["channel_lastBuildDate"])?></p>
<?php } ?>

<?php if(isset($notizia["channel_textInput"]) && 0 < count(array_keys($notizia["channel_textInput"]))) {  $dettagliDisponibili = true; ?>
<form action="<?=$notizia["channel_textInput"]["link"]?>">
	<span style="font-weight:bold;"><?=$notizia["channel_textInput"]["title"]?></span><br/>
	<?=$notizia["channel_textInput"]["description"]?><br/>
	<input type="text" name="<?=$notizia["channel_textInput"]["name"]?>"><br/>
	<input type="submit" value="<?=$notizia["channel_textInput"]["title"]?>">
</form>
<?php } ?>

<?php if(0 < count($notizia["channel_author"])) {  $dettagliDisponibili = true; ?>
<?php if(1 == count($notizia["channel_author"])) { ?><p><span style="font-weight:bold;">Responsabile del canale</span><br/><?php } ?>
<?php if(1 < count($notizia["channel_author"])) { ?><p><span style="font-weight:bold;">Responsabili del canale</span><br/><?php } ?>
<?php for($i = 0; $i < count($notizia["channel_author"]); $i++) { ?>
<?php if($i > 0) echo("<br/>"); ?>
<?php if(trim($notizia["channel_author"][$i]["uri"]) != "") { ?><a target="_blank" href="<?=$notizia["channel_author"][$i]["uri"]?>" title="<?=$notizia["channel_author"][$i]["name"]?>"><?php } ?>
<?php if(trim($notizia["channel_author"][$i]["name"]) != "") echo($notizia["channel_author"][$i]["name"]); else echo($notizia["channel_author"][$i]["uri"]); ?>
<?php if(trim($notizia["channel_author"][$i]["uri"]) != "") { ?></a><?php } ?>
<?php if(trim($notizia["channel_author"][$i]["uri"]) != "" || trim($notizia["channel_author"][$i]["name"]) != "") { ?><br/><?php } ?>
<?php if(trim($notizia["channel_author"][$i]["email"]) != "") { ?><?php if(isMailAddress(trim($notizia["channel_author"][$i]["email"]))) { ?><a href="mailto:<?=trim($notizia["channel_author"][$i]["email"])?>" title="<?=trim($notizia["channel_author"][$i]["email"])?>"><?=trim($notizia["channel_author"][$i]["email"])?></a><?php } else { ?><?=trim($notizia["channel_author"][$i]["email"])?><?php } ?><?php } ?>
<?php } ?>
</p>
<?php } ?>

<?php if(0 < count($notizia["channel_contributor"])) {  $dettagliDisponibili = true; ?>
<?php if(1 == count($notizia["channel_contributor"])) { ?><p><span style="font-weight:bold;">Collabora al canale</span><br/><?php } ?>
<?php if(1 < count($notizia["author"])) { ?><p><span style="font-weight:bold;">Collaborano al canale</span><br/><?php } ?>
<?php for($i = 0; $i < count($notizia["channel_contributor"]); $i++) { ?>
<?php if($i > 0) echo("<br/>"); ?>
<?php if(trim($notizia["channel_contributor"][$i]["uri"]) != "") { ?><a target="_blank" href="<?=$notizia["channel_contributor"][$i]["uri"]?>" title="<?=$notizia["channel_contributor"][$i]["name"]?>"><?php } ?>
<?php if(trim($notizia["channel_contributor"][$i]["name"]) != "") echo($notizia["channel_contributor"][$i]["name"]); else echo($notizia["channel_contributor"][$i]["uri"]); ?>
<?php if(trim($notizia["channel_contributor"][$i]["uri"]) != "") { ?></a><?php } ?>
<?php if(trim($notizia["channel_contributor"][$i]["uri"]) != "" || trim($notizia["channel_contributor"][$i]["name"]) != "") { ?><br/><?php } ?>
<?php if(trim($notizia["channel_contributor"][$i]["email"]) != "") { ?><?php if(isMailAddress(trim($notizia["channel_contributor"][$i]["email"]))) { ?><a href="mailto:<?=trim($notizia["channel_contributor"][$i]["email"])?>" title="<?=trim($notizia["channel_contributor"][$i]["email"])?>"><?=trim($notizia["channel_contributor"][$i]["email"])?></a><?php } else { ?><?=trim($notizia["channel_contributor"][$i]["email"])?><?php } ?><?php } ?>

<?php } ?>
</p>
<?php } ?>

<?php if(trim($notizia["channel_updated"]) != "") {  $dettagliDisponibili = true; ?>
<p><span style="font-weight:bold;">Ultimo aggiornamento del canale</span><br/><?=myPublicationDatetimeFormat($notizia["channel_updated"])?></p>
<?php } ?>

<?php if(0 < count($notizia["channel_link"])) {  $dettagliDisponibili = true; ?>
<p style="line-height:200%;"><span style="font-weight:bold;">Allegati al canale</span><br/>
<?php for($i = 0; $i < count($notizia["channel_link"]); $i++) { ?>
<a target="_blank" style="text-decoration:none;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["channel_link"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["channel_link"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["channel_link"][$i][0])); ?>" href="<?php if(false === strpos($notizia["channel_link"][$i][1],"&amp")) echo(str_replace("&","&amp;",$notizia["channel_link"][$i][1])); else echo($notizia["channel_link"][$i][1]); ?>"  onclick="if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '<?=$channelpath?>' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '<?=$websiteurl?>' + this.href.substring(19); return true;">
<img style="position:relative; top:4px;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["channel_link"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["channel_link"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["channel_link"][$i][0])); ?>" src="images/attachment.png" alt="> "/>
<?php if(false === strpos(str_replace("\"","&quot;",$notizia["channel_link"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["channel_link"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["channel_link"][$i][0])); ?>
</a><br/>
<?php } ?>
</p>
<?php } ?>

<?php if(!$dettagliDisponibili) { ?><p style="font-style:italic;">Nessuna informazione aggiuntiva appare disponibile per questa notizia.</p><?php } ?>



<div style="clear: both; height:6px; overflow:hidden; font-size:1px; margin:0px;">&nbsp;</div>
</div>	

</div>


<!-- Fine dettagli notizia e canale -->

<!-- Inizio commenti -->
<div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px; padding-bottom:0px !important;\""); else echo("style=\"padding-bottom:0px !important;\"");  ?>>
<h1>Commenti alla notizia</h1>
<div class="text">
<br /><br />
<script type="text/javascript">
/* <![CDATA[ */
document.write('<div class="fb-comments" data-href="http://www.synd.it/synd/notizia.php?id=<?=urlencode($notizia["id"])?>" data-num-posts="5" data-width="566"></div>');
/* ]]> */
</script>

</div></div>

<!-- Fine commenti -->


                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:400px; vertical-align:middle;">
                                                <a style="color:#004262;" href="javascript:history.back();">&laquo;&nbsp;indietro</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;">&nbsp;</h1>
                                        <h1 style="display: table-cell; text-align:right; vertical-align:middle;">
                                                &nbsp;
                                        </h1>
                                </div></div>





           	  <!-- </div> -->
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
 
