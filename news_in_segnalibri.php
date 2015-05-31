                <?php
	
		$contatoreNotizia = 0; 
		foreach($newslist as $notizia) { try{
	
			$contatoreNotizia++;

			if(trim(strip_tags(htmlspecialchars_decode(str_replace("&nbsp;","",str_replace("&#160;","",$notizia["description"]))),"<img>")) == "") $notizia["description"] = "<span style=\"font-style:italic; font-weight: normal;\">Nessun testo riassuntivo appare disponibile per questa notizia.</span>";

			$notizia["id"] = strip_tags($notizia["id"]);
			$notizia["title"] = strip_tags(htmlspecialchars_decode($notizia["title"]));
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


			if(false === strpos($notizia["link"],"http://") && trim($notizia["link"]) != "" )
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
			<?php if(trim($notizia["link"]) != "") { ?>
<div class="outlink_hotspot">
<?php if($fbUserid) { ?><a href="favorite_news.php?user=<?=md5($fbUser["username"])?>&amp;newsid=<?=urlencode($notizia["id"])?>&amp;newstitle=<?=urlencode($notizia["title"])?>" title="Notizie preferite"><?php if(!isFavoriteNews(md5($fbUser["username"]),$notizia["id"])) { ?><img src="images/notfavorite.gif" title="Aggiungi ai favoriti" alt="Aggiungi ai favoriti" /><?php } else { ?><img src="images/favorite.gif" title="Rimuovi dai favoriti" alt="Rimuovi dai favoriti" /><?php } ?></a>&nbsp;<?php } ?>
	<a target="_blank" href="<?=trim($notizia["link"])?>" title="<?=trim($notizia["link"])?>"><img src="images/outlink.png" title="<?=trim($notizia["link"])?>" alt="<?=trim($notizia["link"])?>"/></a>
</div>
<?php } ?>
                	<h1>
				<?php if(trim($notizia["link"]) != "") { ?><a class="titolo_notizia" <?=$notiziaSenzaTitolo?> title="<?=str_replace("\"","&quot;",$notizia["title"])?>" href="notizia.php?id=<?=$notizia["id"]?>"><?php } ?><?=$notizia["title"]?><?php if(trim($notizia["link"]) != "") { ?></a><?php } ?><br/>
				<span class="news_datetime">
					Canale:&nbsp;<a href="index.php?query=<?=urlencode("<channelurl>".substr($notizia["id"],1+strpos($notizia["id"],"@"))."</channelurl><datetime>&gt; 0</datetime>")?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" class="canale_notizia"><?php echo(substr($notizia["id"],1+strpos($notizia["id"],"@"))); ?></a>&nbsp;<?php if($fbUserid) { ?><a href="favorite_channel.php?user=<?=md5($fbUser["username"])?>&amp;channel=<?=urlencode(substr($notizia["id"],1+strpos($notizia["id"],"@")))?>" title="Canali preferiti"><?php if(!isFavoriteChannel(md5($fbUser["username"]),substr($notizia["id"],1+strpos($notizia["id"],"@")))) { ?><img style="position: relative; top:3px;" src="images/channel_notfavorite.gif" title="Aggiungi ai segnalibri" alt="Aggiungi ai segnalibri" /><?php } else { ?><img style="position:relative; top:3px;"src="images/channel_favorite.gif" title="Rimuovi dai segnalibri" alt="Rimuovi dai segnalibri" /><?php } ?></a>&nbsp;<?php } ?><a target="_blank" href="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>"><img src="images/outlink_channel.png" alt="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" title="<?=substr($notizia["id"],1+strpos($notizia["id"],"@"))?>" style="position:relative; top:3px;" /></a>

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
}

var form<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("form");
for(i = 0; i < form<?=$tmpid?>.length; i++)
{
        var currform = form<?=$tmpid?>.item(i);
        if(-1 < currform.action.indexOf('http://www.synd.it/synd/')) currform.action = '<?=$channelpath?>' + currform.action.substring(24);
        else if(-1 < currform.action.indexOf('http://www.synd.it/')) currform.action = '<?=$websiteurl?>' + currform.action.substring(19);
        else if(0 != currform.action.indexOf('http://') && 0 != currform.action.indexOf('https://') && 0 != currform.action..indexOf('www'))  currform.style.display = 'none';
}


var embed<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("embed");
for(i = 0; i < embed<?=$tmpid?>.length; i++) 
{
	var currembed = embed<?=$tmpid?>.item(i);

        if(-1 < currembed.src.indexOf('http://www.synd.it/synd/')) currembed.src = '<?=$channelpath?>' + currembed.src.substring(24);
        else if(-1 < currembed.src.indexOf('http://www.synd.it/')) currembed.src = '<?=$websiteurl?>' + currembed.src.substring(19);

if(-1 < currembed.src.indexOf("file=/")) currembed.src = currembed.src.substring(0,currembed.src.indexOf("file=")) + 'file=<?=$websiteurl?>' + currembed.src.substring(6+currembed.src.indexOf("file="));
else currembed.src = currembed.src.substring(0,currembed.src.indexOf("file=")) + 'file=<?=$channelpath?>' + currembed.src.substring(5+currembed.src.indexOf("file="));
 
if(-1 < currembed.src.indexOf("image=/")) currembed.src = currembed.src.substring(0,currembed.src.indexOf("image=")) + 'image=<?=$websiteurl?>' + currembed.src.substring(7+currembed.src.indexOf("image="));
else currembed.src = currembed.src.substring(0,currembed.src.indexOf("image=")) + 'image=<?=$channelpath?>' + currembed.src.substring(6+currembed.src.indexOf("image="));

currembed.src = currembed.src.substring(0,currembed.src.indexOf("?"))+currembed.src.substring(currembed.src.indexOf("?")).replace(new RegExp("/", 'g'),"%2F").replace(new RegExp(":", 'g'),"%3A").replace(new RegExp("$", 'g'),"%24").replace(new RegExp("+", 'g'),"%2B").replace(new RegExp(",", 'g'),"%2C").replace(new RegExp(";", 'g'),"%3B").replace(new RegExp("=", 'g'),"%3D").replace(new RegExp("?", 'g'),"%3F").replace(new RegExp("@", 'g'),"%40");

}
var param<?=$tmpid?> = document.getElementById("div<?=$tmpid?>").getElementsByTagName("param");
for(i = 0; i < param<?=$tmpid?>.length; i++)
{
        if(param<?=$tmpid?>.item(i).name != "movie") continue;
	var currparam = param<?=$tmpid?>.item(i);

	if(currparam.value.substring(0,1) != "/") currparam.value = '<?=$channelpath?>' + currparam.value;
        else currparam.value = '<?=$websiteurl?>' + currparam.value.substring(1);

	if(-1 < currparam.value.indexOf("file=/")) currparam.value = currparam.value.substring(0,currparam.value.indexOf("file=")) + 'file=<?=$websiteurl?>' + currparam.value.substring(6+currparam.value.indexOf("file="));
	else currparam.value = currparam.value.substring(0,currparam.value.indexOf("file=")) + 'file=<?=$channelpath?>' + currparam.value.substring(5+currparam.value.indexOf("file="));

        if(-1 < currparam.value.indexOf("image=/")) currparam.value = currparam.value.substring(0,currparam.value.indexOf("image=")) + 'image=<?=$websiteurl?>' + currparam.value.substring(7+currparam.value.indexOf("image="));
	else currparam.value = currparam.value.substring(0,currparam.value.indexOf("image=")) + 'image=<?=$websiteurl?>' + currparam.value.substring(6+currparam.value.indexOf("image="));
	
currparam.value = currparam.value.substring(0,currparam.value.indexOf("?"))+currparam.value.substring(currparam.value.indexOf("?")).replace(new RegExp("/", 'g'),"%2F").replace(new RegExp(":", 'g'),"%3A").replace(new RegExp("$", 'g'),"%24").replace(new RegExp("+", 'g'),"%2B").replace(new RegExp(",", 'g'),"%2C").replace(new RegExp(";", 'g'),"%3B").replace(new RegExp("=", 'g'),"%3D").replace(new RegExp("?", 'g'),"%3F").replace(new RegExp("@", 'g'),"%40")
;

}

/* ]]> */
</script>

<?php if(0 < count($notizia["attachments"])) { ?>

<div class="attachments" style="border-top: thin dotted #7e9fb0; line-height:200%; margin-top:10px; padding-top:10px;">
<?php for($i = 0; $i < count($notizia["attachments"]); $i++) { ?>
<a target="_blank" style="text-decoration:none;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>" href="<?php if(false === strpos($notizia["attachments"][$i][1],"&amp;")) echo(str_replace("&","&amp;",$notizia["attachments"][$i][1])); else echo($notizia["attachments"][$i][1]); ?>"  onclick="if(-1 &lt; this.href.indexOf('http://www.synd.it/synd/')) this.href = '<?=$channelpath?>' + this.href.substring(24); if(-1 &lt; this.href.indexOf('http://www.synd.it/')) this.href = '<?=$websiteurl?>' + this.href.substring(19); return true;">
<img style="position:relative; top:4px;" title="<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>" src="images/attachment.png" alt="> "/>
<?php if(false === strpos(str_replace("\"","&quot;",$notizia["attachments"][$i][0]),"&amp;")) echo(str_replace("&","&amp;",str_replace("\"","&quot;",$notizia["attachments"][$i][0]))); else echo(str_replace("\"","&quot;",$notizia["attachments"][$i][0])); ?>
</a><br/>
<?php } ?>
</div>

<?php } ?>


			<div style="clear:both; height: 10px; overflow:hidden; font-size:1px; margin:0px;">&nbsp;</div>

		</div>

		<?php } catch(Exception $e) {} ?>
		
		<?php } ?>

		<?php if(0 == count($newslist) && (!$nessun_canale_preferito)) { ?>

                <div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                        <h1>nessun risultato</h1>
                        <div class="text">
                                <p>Spiacenti, nessuna notizia trovata nei tuoi canali preferiti.</p>
				<p>&nbsp;</p>
                        </div>
                </div>

		<?php } ?>

		<?php if($nessun_canale_preferito) { ?>
                <div class="tit_bot" <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("style=\"background: url('images/bg_tit_ie.png') left top no-repeat !important; width:570px;\""); ?>>
                        <h1>Segnalibri</h1><div class="text">

<p>Synd prevede per gli utenti autenticati la possibilit&agrave; di contrassegnare i propri canali preferiti, e le proprie notizie preferite.</p>
<p>Per contrassegnare un canale come preferito, si deve agire sulla stelletta che si trova alla destra dell'indirizzo Internet del canale, nell'intestazione di una qualsiasi notizia che sia stata scaricata dal canale di interesse. La stelletta da vuota diverr&agrave; piena, ed il canale sar&agrave; aggiunto alla lista dei canali preferiti, che pu&ograve; essere consultata sempre in questa pagina previa autenticazione. Un canale pu&ograve; essere rimosso dai preferiti sempre agendo sulla stessa stelletta in una qualsiasi notizia scaricata dal canale.</p><p>Agendo su di uno dei collegamenti ai canali preferiti mostrati previa autenticazione su questa stessa pagina, si otterr&agrave; una vista di tutte le notizie scaricate dal canale, ordinate temporalmente a partire dalla pi&ugrave; recemte.</p><p>Inoltre, agendo sui collegamenti <span style="font-style:italic;">top</span> che si trovano a fianco dei canali preferiti elencati previa autenticazione in questa pagina, si potranno selezionare i tre canali super preferiti. Il significato di questi canali &egrave; che nella pagina iniziale dei segnalibri, che si apre agendo sulla voce relativa del men&ugrave; principale, sono mostrate nella sezione dei contenuti tutte le notizie raccolte dai canali super preferiti ordinate temporalmente a partire dalla pi&ugrave; recente.</p>
<p>Synd prevede anche per gli utenti autenticati la possibilit&agrave; di contrassegnare le proprie notizie preferite, agendo sulla stella azzurra o blu che si trova alla destra del titolo della notizia. Agendo sulla stessa stella sar&agrave; possibile nel caso lo si desiderasse, rimuovere la notizia dall'elenco delle notizie preferite. L'elenco delle notizie preferite &egrave; mostrato, previa autenticazione, in questa pagina.</p>
<p>Non &egrave; imposto alcun limite al numero massimo di notizie e di canali che possono essere contrassegnati come preferiti da ciascun utente. Per usufruire della funzionalit&agrave;, &egrave; per&ograve; come detto richiesto di aver precedentemente eseguito l'accesso, utilizzando una propria utenza Facebook. Synd non conserva infatti per propria scelta alcun dato personale relativo agli utenti, e non prevede pertanto alcun meccanismo di registrazione degli utenti direttamente presso di s&eacute;.</p>
<br /></div></div>
<!--
</div>

<br />
            <div style="clear: both"><img src="images/spaser.gif" alt="" width="1" height="1" /></div>
-->
		<?php } ?>

                <?php
                if(true)
                {
                        $offset = 0;  if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $offset  = round($_GET["offset"]); 
			$foffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $foffset = round($_GET["offset"]); $foffset+=10;
                        $boffset = 0; if(isset($_GET["offset"]) && is_numeric($_GET["offset"])) $boffset = round($_GET["offset"]); $boffset-=10;
                        if($foffset < $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top: 20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="segnalibri.php?offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
					<h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+($hits%10?1:0)?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="segnalibri.php?offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>
                        <?php
                        }
                        else if($foffset >= $hits && $boffset >= 0)
                        {
                        ?>
                                <div class="rev_tit_bot" style="margin-top:20px; display: table; <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"Safari")) { ?>width:590px;<?php } else { ?>width: 570px;<?php } ?> <?php if(false !== strpos($_SERVER["HTTP_USER_AGENT"],"MSIE")) echo("background: url('images/bg_nav_ie.png') left top no-repeat !important;"); ?>"><div style="display:table-row;">
                                        <h1 style="display:table-cell; text-align:left; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="segnalibri.php?offset=<?=$boffset?>">&laquo;&nbsp;precedente</a>
                                        </h1>
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+($hits%10?1:0)?></h1>
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
                                        <h1 style="display:table-cell; text-align:center; vertical-align:middle;"><?=$offset/10+1?> / <?=floor($hits/10)+($hits%10?1:0)?></h1>
                                        <h1 style="display: table-cell; text-align:right; width:200px; vertical-align:middle;">
                                                <a style="color:#004262;" href="segnalibri.php?offset=<?=$foffset?>">successiva&nbsp;&raquo;</a>
                                        </h1>
                                </div></div>

                        <?php
                        }
                        ?>

                <?php
                }
                ?>

            </div>
            <br />
            <div style="clear: both"><img src="images/spaser.gif" alt="" width="1" height="1" /></div>
