<?php

require_once("config.php");
require_once("clusterpoint.php");
require_once("include/myphplib.php");

$widgetType = "";
if(isset($_GET["type"])) 
{
	if($_GET["type"] == "primo_piano") $widgetType = "primo_piano";
	if($_GET["type"] == "list_last") $widgetType = "list_last";
	if($_GET["type"] == "custom_query") $widgetType = "custom_query";
}
if($widgetType == "") exit;

$widgetQuery = "";
if(isset($_GET["query"])) $widgetQuery = $_GET["query"];

if($widgetType == "custom_query" && $widgetQuery == "") exit;

$widgetWidth = 280;
if(isset($_GET["width"]) && is_numeric($_GET["width"]) && floor($_GET["width"]) > 280) $widgetWidth = floor($_GET["width"]);

$widgetHeight = 180;
if(isset($_GET["height"]) && is_numeric($_GET["height"]) && floor($_GET["height"]) > 180) $widgetHeight = floor($_GET["height"]);

$contentsArray = array();






if($widgetType == "list_last")
{

	$newslist = getListLast(null, 0, 20);

	foreach($newslist as $notizia)
	{
		
        	$notizia["id"] = strip_tags($notizia["id"]);
                $notizia["title"] = strip_tags(htmlspecialchars_decode($notizia["title"]));
		if(trim($notizia["title"]) == "") continue;

		$contentsArray[] = array
		(
			"title" => $notizia["title"],
			"href" => "http://www.synd.it/synd/notizia.php?id=".urlencode($notizia["id"])
		);
	
	}
	
}

if($widgetType == "custom_query")
{


	$hits = 0;
        $newslist = getSearchResults( null, $widgetQuery, 0, 20, $hits );

        foreach($newslist as $notizia)
        {

                $notizia["id"] = strip_tags($notizia["id"]);
                $notizia["title"] = strip_tags(htmlspecialchars_decode($notizia["title"]));
		if(trim($notizia["title"]) == "") continue;

                $contentsArray[] = array
                (
                        "title" => $notizia["title"],
                        "href" => "http://www.synd.it/synd/notizia.php?id=".urlencode($notizia["id"])
                );

        }

}

if($widgetType == "primo_piano")
{

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

	$result = mysqli_query($conn, "SELECT newsid, newstitle, count(*) visualizzazioni FROM stats__visualizzazioni WHERE datetime > DATE_SUB(CURDATE(),INTERVAL 7 DAY) GROUP BY newsid, newstitle ORDER BY visualizzazioni DESC LIMIT 0, 20");

	while($primopiano = mysqli_fetch_array($result, MYSQLI_ASSOC))
	{
		$contentsArray[] = array
		(
			"title" => $primopiano["newstitle"],
			"href" => "http://www.synd.it/synd/notizia.php?id=".urlencode($primopiano["newsid"])
		);
	}

	mysqli_close($conn);

}

?>

<div id="synd" style="font-family: sans-serif; font-size:small; width: <?=$widgetWidth?>px; height: <?=$widgetHeight?>px; padding:4px; border:thin solid blue;">
<div id="synd_head" style=" background-color: #165abf;"><a href="http://www.synd.it/synd/" title="Synd.it"><img src="http://www.synd.it/synd/images/head_icon.png" title="Synd" /></a></div>
<div id="synd_content" style="margin-top:4px; height: <?=$widgetHeight-64?>px; overflow:auto;">
<?php $ctr = 1; foreach($contentsArray as $content) { ?>
<a style="font-weight:bold; text-decoration:none;" href="<?=$content["href"]?>" title="<?=$content["title"]?>" target="_blank"><?=$content["title"]?></a>
<?php if($ctr < count($contentsArray)) { echo("<br /><br />"); $ctr++; } ?>
<?php } ?>
</div>
</div>

<?php exit(0); ?>
