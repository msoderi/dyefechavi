<?php

//require_once("php_cps_api-120312/cps_api.php");
require_once("config.php");

date_default_timezone_set("Europe/Rome");

class Notizia
{
	private $id;
	private $datetime;
	private $newstype;
	private $title;
	private $description;
	private $link;
	public function __construct($pid, $pdatetime, $pnewstype, $ptitle, $pdescription, $plink)
	{
		$this->id = $pid;
		$this->datetime = $pdatetime;
		$this->newstype = $pnewstype;
		$this->title = $ptitle;
		$this->description = $pdescription;
		$this->link = $plink;
	}
}

function getStatus($clusterpointConnection)
{

	$statusRequest = new CPS_StatusRequest();

	$statusResponse = $clusterpointConnection->sendRequest($statusRequest);

	return $statusResponse->getStatus();
	
}

function getListlastResponse($clusterpointConnection, $offset, $docs)
{

	try
	{	

        	$listlastRequest = new CPS_ListLastRequest(array(),$offset,$docs);

	        $listlastResponse = $clusterpointConnection->sendRequest($listlastRequest);

		return $listlastResponse;

	} 
	catch(Exception $e) 
	{ 
		return false; 
	}

}

function getListLast($clusterpointConnection, $offset, $docs)
{
	/*
	$listlastResponse = getListlastResponse($clusterpointConnection, $offset, $docs);
	while(!$listlastResponse) $listlastResponse = getListlastResponse($clusterpointConnection, $offset, $docs);
	$listlastNodesArray = $listlastResponse->getDocuments(DOC_TYPE_SIMPLEXML);
	$listlastNodesArrayKeys = array_keys($listlastNodesArray);
	*/
global $dbhost, $dbuser, $dbpass, $dbname;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

$listlast = mysqli_query($conn, "SELECT id, content FROM admin__news WHERE skip = 0 ORDER BY autoinc DESC LIMIT $offset,$docs");
$listlastNodesArray = array();
while($currnews = mysqli_fetch_array($listlast, MYSQLI_ASSOC)) 
{
	$listlastNodesArray[$currnews["id"]] = simplexml_load_string($currnews["content"]);
	if($listlastNodesArray[$currnews["id"]] == null) unset($listlastNodesArray[$currnews["id"]]);
}
mysqli_close($conn);
$listlastNodesArrayKeys = array_keys($listlastNodesArray);
	
	$listlast = array();
	for($i = 0; $i < count($listlastNodesArrayKeys); $i++)
	{
		
		$node = null;         	
		$node = $listlastNodesArray[$listlastNodesArrayKeys[$i]];
		$node->registerXPathNamespace("default","http://www.w3.org/2005/Atom");
		
		$id = "";
		$datetime = "";
		$newstype = "";
		$title = "";
		$description = "";
		$link = "";
		$attachments = array();

		if(null != $node->rss[0])
		{

	                $id = $node->id[0]->__toString();
	
        	        $datetime = $node->datetime[0]->__toString();

                	$newstype = $node->type[0]->__toString();
			
			if($node->rss[0]->channel[0]->item[0]->title[0]) $title = $node->rss[0]->channel[0]->item[0]->title[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->description[0]) $description = $node->rss[0]->channel[0]->item[0]->description[0]->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "")
			{
				if($node->rss[0]->channel[0]->item[0]->contentencoded[0]) $description = trim($node->rss[0]->channel[0]->item[0]->contentencoded[0]->__toString());
				if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";
			}

			if($node->rss[0]->channel[0]->item[0]->link[0]) $link = $node->rss[0]->channel[0]->item[0]->link[0]->__toString();

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediacontent); $j++)
			{
				
				$attachment_title = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
				{
					 $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

				$attachment_url = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]);
				if(trim($attachment_url) == "") continue;

				if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;
 
				$attachments[] = array
				(
					$attachment_title,
					$attachment_url
				);

			}

			for($g = 0; $g < count($node->rss[0]->channel[0]->item[0]->mediagroup); $g++)
                        for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent); $j++)
                        {

                                $attachment_title = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
                                {
                                         $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

                                $attachment_url = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]);
                                if(trim($attachment_url) == "") continue;

                                if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;

                                $attachments[] = array
                                (
                                        $attachment_title,
                                        $attachment_url
                                );
                        }

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->enclosure); $j++)
			{
				if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]))
				{
					
					$alreadyThere = false;
					for($a = 0; $a < count($attachments); $a++) if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]) == trim($attachments[$a][1])) $alreadyThere = true;
					if($alreadyThere) continue;
					$attachments[] = array
					(
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"],
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]
					);
				}	
			}

			if(1 == count($attachments) && ( $attachments[0][0] == $attachments[0][1] || trim($attachments[0][0]) == "") && trim($title) != "")
			{
				$attachments[0][0] = $title;
			}

		}
		else if(null != $node->RDF[0]) 
		{ 

			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

			if($node->RDF[0]->item[0]->title[0]) $title = $node->RDF[0]->item[0]->title[0]->__toString();

			if($node->RDF[0]->item[0]->description[0]) $description = $node->RDF[0]->item[0]->description[0]->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			if($node->RDF[0]->item[0]->link[0]) $link = $node->RDF[0]->item[0]->link[0]->__toString();

			for($j = 0; $j < count($node->RDF[0]->item[0]->encenclosure); $j++)
			{
				$attachment_url = "";
				if($node->RDF[0]->item[0]->encenclosure[$j]["encurl"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]);
				}
				else if($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]);
				} 
				else
				{
					continue;
				}

				if(trim($attachment_url) == "") continue;
			
				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
				if($alreadyThere) continue;

				$attachments[] = array
				(
					$attachment_url,
					$attachment_url
				);
			}

		}
		else if(null != $node->children("http://www.w3.org/2005/Atom")->feed) 
		{ 
			
			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();
			
			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->title) $title = $node->children("http://www.w3.org/2005/Atom")->feed->entry->title->__toString();

                  	if($node->children("http://www.w3.org/2005/Atom")->feed->entry->summary) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->summary->__toString();

                        if(trim(str_replace(chr(194).chr(160)," ",$description)) == "")
                        {
				if($node->children("http://www.w3.org/2005/Atom")->feed->entry->content) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString();
				if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			}

			$link = "";
			foreach($node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->entry->children("http://www.w3.org/2005/Atom")->link as $atomlink)
			{	
				
				$atomlinkAttributes = array();

				foreach ($atomlink->attributes() as $attrname=>$attrval) 
				{
					$atomlinkAttributes[$attrname] = $attrval;
				}
				
				if( (!array_key_exists("rel",$atomlinkAttributes)) || strtolower(trim($atomlinkAttributes["rel"])) == "alternate" )
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$link = $atomlinkAttributes["href"];
					}
				}
				else
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$attachment_url = trim($atomlinkAttributes["href"]);
						$attachment_title = "";
						if(array_key_exists("title",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["title"])))
						{
							$attachment_title = trim($atomlinkAttributes["title"]);
						}
						else
						{
							$attachment_title = $attachment_url;
						}
						
						$alreadyThere = false;
						for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
						if($alreadyThere) continue;

						$attachments[] = array
						(
							$attachment_title,
							$attachment_url
						);
					}
				} 
				
			}

		}

		if
		(
			0 < strlen(trim($id)) &&
			0 < strlen(trim($newstype)) &&
			0 < strlen(trim($datetime)) &&
			0 < strlen(trim($title).trim($description).trim($link))
		) 
		{
			
			$listlast[] = array
			(
				"id" => trim($id), 
				"type" => trim($newstype), 
				"datetime" => trim($datetime), 
				"title" => trim($title), 
				"description" => trim($description), 
				"link" => trim($link),
				"attachments" => $attachments
			);

		}
	
	}
	
	return $listlast;

}

function getSearchResponse($clusterpointConnection, $query, $offset, $docs)
{

	try
	{

	        $searchRequest = new CPS_SearchRequest($query, $offset, $docs);

		if(false !== strpos($query,"<channelurl>") && false !== strpos($query,"<datetime>")) $searchRequest->setExtraXmlParam("<numeric_ordering>descending</numeric_ordering>");

/*
		if(trim($query) != "")
		{
			$queryparts = explode(" ",str_replace("}","",str_replace("{","",$query)));
			$isSearchByChannel = true;
			for($i = 0; $i < count($queryparts); $i++)
			{
				if(false === strpos($queryparts[$i], "http://")) $isSearchByChannel = false;
			}
			
			echo(htmlentities(CPS_NumericOrdering('/synd/datetime','descending')));
			if($isSearchByChannel) $searchRequest->setOrdering(CPS_NumericOrdering('/synd/datetime','decreasing')); 
		}
*/

        	$searchResponse = $clusterpointConnection->sendRequest($searchRequest);

		return $searchResponse;

	}
	catch(Exception $e)
	{
		return false;
	}

}

function getSearchResults($clusterpointConnection, $query, $offset, $docs, &$hits)
{

	/*
	$searchResponse = getSearchResponse($clusterpointConnection, $query, $offset, $docs);
	while(!$searchResponse) $searchResponse = getSearchResponse($clusterpointConnection, $query, $offset, $docs);

	$hits = $searchResponse->getHits();

        $searchNodesArray = $searchResponse->getDocuments(DOC_TYPE_SIMPLEXML);
        $searchNodesArrayKeys = array_keys($searchNodesArray);
        */
$channelsearch = false;
$channelqueryarray = array();
$channelquerystring = "";
if(trim($query) != "") if(substr($query,0,1) == "[" && substr($query,strlen($query)-1) == "]") 
{
	$channelsearch = true;
	$channelqueryarray = explode(",",$query);
}
for($i = 0; $i < count($channelqueryarray); $i++)
{
	$channelquerystring.=" channel = '".trim($channelqueryarray[$i],"[]")."' ";
	if($i != -1+count($channelqueryarray)) $channelquerystring.=" OR ";
}
$channelquerystring = "( $channelquerystring )";
global $dbhost, $dbuser, $dbpass, $dbname;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$searchResults = null;
if(!$channelsearch) $searchResults = mysqli_query($conn, "SELECT id, content FROM admin__news WHERE skip = 0 AND MATCH(content) AGAINST ('$query') LIMIT $offset,$docs");
else 
{
	$searchResults =mysqli_query($conn, "SELECT id, content FROM admin__news WHERE skip = 0 AND $channelquerystring LIMIT $offset,$docs"); 
	
// debug
// return "SELECT id, content FROM admin__news WHERE skip = 0 AND $channelquerystring LIMIT $offset,$docs";
// mysqli_close($conn);
 
}
$searchNodesArray = array();
while($currnews = mysqli_fetch_array($searchResults, MYSQLI_ASSOC)) $searchNodesArray[$currnews["id"]] = simplexml_load_string($currnews["content"]);
$searchNodesArrayKeys = array_keys($searchNodesArray);
$searchResultsCount = null;
if(!$channelsearch) $searchResultsCount = mysqli_query($conn, "SELECT count(*) hits FROM admin__news WHERE skip = 0 AND MATCH(content) AGAINST ('$query')");
else $searchResultsCount = mysqli_query($conn, "SELECT count(*) hits FROM admin__news WHERE skip = 0 AND $channelquerystring");
while($searchResultsCountCurr = mysqli_fetch_array($searchResultsCount, MYSQLI_ASSOC)) $hits += $searchResultsCountCurr["hits"];
mysqli_close($conn);

	$searchResults = array();
        
	for($i = 0; $i < count($searchNodesArrayKeys); $i++)
        {

		
		$node = null;         	
		
		$node = $searchNodesArray[$searchNodesArrayKeys[$i]];
		if($node == null) continue;
	
		$node->registerXPathNamespace("default","http://www.w3.org/2005/Atom");
		
		$id = "";
		$datetime = "";
		$newstype = "";
		$title = "";
		$description = "";
		$link = "";
		$attachments = array();

		if(null != $node->rss[0])
		{

	                $id = $node->id[0]->__toString();
	
        	        $datetime = $node->datetime[0]->__toString();

                	$newstype = $node->type[0]->__toString();
			
			if($node->rss[0]->channel[0]->item[0]->title[0]) $title = $node->rss[0]->channel[0]->item[0]->title[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->description[0]) $description = $node->rss[0]->channel[0]->item[0]->description[0]->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "")
			{
				if($node->rss[0]->channel[0]->item[0]->contentencoded[0]) $description = trim($node->rss[0]->channel[0]->item[0]->contentencoded[0]->__toString());
				if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";
			}

			if($node->rss[0]->channel[0]->item[0]->link[0]) $link = $node->rss[0]->channel[0]->item[0]->link[0]->__toString();

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediacontent); $j++)
			{
				
				$attachment_title = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
				{
					 $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

				$attachment_url = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]);
				if(trim($attachment_url) == "") continue;

				if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;
 
				$attachments[] = array
				(
					$attachment_title,
					$attachment_url
				);

			}

			for($g = 0; $g < count($node->rss[0]->channel[0]->item[0]->mediagroup); $g++)
                        for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent); $j++)
                        {

                                $attachment_title = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
                                {
                                         $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

                                $attachment_url = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]);
                                if(trim($attachment_url) == "") continue;

                                if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;

                                $attachments[] = array
                                (
                                        $attachment_title,
                                        $attachment_url
                                );
                        }

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->enclosure); $j++)
			{
				if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]))
				{
					
					$alreadyThere = false;
					for($a = 0; $a < count($attachments); $a++) if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]) == trim($attachments[$a][1])) $alreadyThere = true;
					if($alreadyThere) continue;
					$attachments[] = array
					(
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"],
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]
					);
				}	
			}

			if(1 == count($attachments) && ( $attachments[0][0] == $attachments[0][1] || trim($attachments[0][0]) == "") && trim($title) != "")
			{
				$attachments[0][0] = $title;
			}

		}
		else if(null != $node->RDF[0]) 
		{ 

			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

			if($node->RDF[0]->item[0]->title[0]) $title = $node->RDF[0]->item[0]->title[0]->__toString();

			if($node->RDF[0]->item[0]->description[0]) $description = $node->RDF[0]->item[0]->description[0]->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			if($node->RDF[0]->item[0]->link[0]) $link = $node->RDF[0]->item[0]->link[0]->__toString();

			for($j = 0; $j < count($node->RDF[0]->item[0]->encenclosure); $j++)
			{
				$attachment_url = "";
				if($node->RDF[0]->item[0]->encenclosure[$j]["encurl"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]);
				}
				else if($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]);
				} 
				else
				{
					continue;
				}

				if(trim($attachment_url) == "") continue;
			
				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
				if($alreadyThere) continue;

				$attachments[] = array
				(
					$attachment_url,
					$attachment_url
				);
			}

		}
		else if( null != $node->children("http://www.w3.org/2005/Atom")->feed) 
		{ 
			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();
			
			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->title) $title = $node->children("http://www.w3.org/2005/Atom")->feed->entry->title->__toString();

                  	if($node->children("http://www.w3.org/2005/Atom")->feed->entry->summary) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->summary->__toString();
	

                        if(trim(str_replace(chr(194).chr(160)," ",$description)) == "")
                        {
				if($node->children("http://www.w3.org/2005/Atom")->feed->entry->content) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString();
				if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			}
			else if((strlen(str_replace("<","",str_replace(">","",str_replace("&quot;","",str_replace("&gt;","",str_replace("&lt;","",str_replace(chr(194).chr(160)," ",$description)))))))) / strlen( str_replace("<","",str_replace(">","",str_replace("&quot;","",str_replace("&gt;","",str_replace("&lt;","",str_replace(chr(194).chr(160)," ",$node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString()))))))) >= 1)
			{
                                if($node->children("http://www.w3.org/2005/Atom")->feed->entry->content) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString();
                                if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			} 
	 

			$link = "";
			foreach($node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->entry->children("http://www.w3.org/2005/Atom")->link as $atomlink)
			{	
				
				$atomlinkAttributes = array();

				foreach ($atomlink->attributes() as $attrname=>$attrval) 
				{
					$atomlinkAttributes[$attrname] = $attrval;
				}
				
				if( (!array_key_exists("rel",$atomlinkAttributes)) || strtolower(trim($atomlinkAttributes["rel"])) == "alternate" )
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$link = $atomlinkAttributes["href"];
					}
				}
				else
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$attachment_url = trim($atomlinkAttributes["href"]);
						$attachment_title = "";
						if(array_key_exists("title",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["title"])))
						{
							$attachment_title = trim($atomlinkAttributes["title"]);
						}
						else
						{
							$attachment_title = $attachment_url;
						}
						
						$alreadyThere = false;
						for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
						if($alreadyThere) continue;

						$attachments[] = array
						(
							$attachment_title,
							$attachment_url
						);
					}
				} 
				
			}

		}

		if
		(
			0 < strlen(trim($id)) &&
			0 < strlen(trim($newstype)) &&
			0 < strlen(trim($datetime)) &&
			0 < strlen(trim($title).trim($description).trim($link))
		) 
		{
			
			$searchResults[] = array
			(
				"id" => trim($id), 
				"type" => trim($newstype), 
				"datetime" => trim($datetime), 
				"title" => trim($title), 
				"description" => trim($description), 
				"link" => trim($link),
				"attachments" => $attachments
			);

		}

/*
                $node = null;

                $node = $se:archNodesArray[$searchNodesArrayKeys[$i]];
                
		$node->registerXPathNamespace("default","http://www.w3.org/2005/Atom");

                $id = "";
                $datetime = "";
                $newstype = "";
                $title = "";
                $description = "";
                $link = "";

                if(null != $node->rss[0])
                {

                        $id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

                        if($node->rss[0]->channel[0]->item[0]->title[0]) $title = $node->rss[0]->channel[0]->item[0]->title[0]->__toString();

                        if($node->rss[0]->channel[0]->item[0]->description[0]) $description = $node->rss[0]->channel[0]->item[0]->description[0]->__toString();

                        if($node->rss[0]->channel[0]->item[0]->link[0]) $link = $node->rss[0]->channel[0]->item[0]->link[0]->__toString();

                }
                else if(null != $node->RDF[0])
                {

                        $id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

                        if($node->RDF[0]->item[0]->title[0]) $title = $node->RDF[0]->item[0]->title[0]->__toString();

                        if($node->RDF[0]->item[0]->description[0]) $description = $node->RDF[0]->item[0]->description[0]->__toString();

                        if($node->RDF[0]->item[0]->link[0]) $link = $node->RDF[0]->item[0]->link[0]->__toString();

                }
                else if(null != $node->children("http://www.w3.org/2005/Atom")->feed)
                {

                        $id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

                        if($node->children("http://www.w3.org/2005/Atom")->feed->entry->title) $title = $node->children("http://www.w3.org/2005/Atom")->feed->entry->title->__toString();

                        if($node->children("http://www.w3.org/2005/Atom")->feed->entry->summary) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->summary->__toString();

			if(trim($description) == "")
			{
				if($node->children("http://www.w3.org/2005/Atom")->feed->entry->content) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString();
			}

                        $link = "";
                        foreach($node->children("http://www.w3.org/2005/Atom")->feed->entry->link as $atomlink)
                        {

                                $atomlinkAttributes = array();

                                foreach ($atomlink->attributes() as $attrname=>$attrval)
                                {
                                        $atomlinkAttributes[$attrname] = $attrval;
                                }

                                if( (!array_key_exists("rel",$atomlinkAttributes)) || strtolower(trim($atomlinkAttributes["rel"])) == "alternate" )
                                {
                                        if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
                                        {
                                                $link = $atomlinkAttributes["href"];
                                                break;
                                        }
                                }

                        }

                }

                if
                (
                        0 < strlen(trim($id)) &&
                        0 < strlen(trim($newstype)) &&
                        0 < strlen(trim($datetime)) &&
                        0 < strlen(trim($title).trim($description).trim($link))
                )
                {

                        $searchResults[] = array
                        (
                                "id" => trim($id),
                                "type" => trim($newstype),
                                "datetime" => trim($datetime),
                                "title" => trim($title),
                                "description" => trim($description),
                                "link" => trim($link)
                        );

                }
*/
        }

        return $searchResults;

}

function getDocument($unused, $id)
{
global $dbhost, $dbuser, $dbpass, $dbname;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
$documentbyid = mysqli_query($conn, "SELECT content FROM admin__news WHERE id = '$id'");
$document = null;
while($currnews = mysqli_fetch_array($documentbyid, MYSQLI_ASSOC)) $document = simplexml_load_string($currnews["content"]);
mysqli_close($conn);
return $document;

}

function getDocument_old($clusterpointConnection, $id)
{
	try{
	$request = new CPS_RetrieveRequest($id);
	/*$request->setList
	(
		array
		(
			"//synd/rss/channel/item/author" => "yes",
			"//synd/rss/channel/item/comments" => "yes",
			"//synd/rss/channel/item/pubDate" => "yes",
			"//synd/rss/channel/title" => "yes",
			"//synd/rss/channel/description" => "yes",
			"//synd/rss/channel/managingEditor" => "yes",
			"//synd/rss/channel/webMaster" => "yes",
			"//synd/rss/channel/lastBuildDate" => "yes",
			"//synd/rss/channel/textInput" => "yes",
			"//synd/rss/channel/textInput/title" => "yes",
			"//synd/rss/channel/textInput/description" => "yes",
			"//synd/rss/channel/textInput/name" => "yes",
			"//synd/rss/channel/textInput/link" => "yes",
			"//synd/default:feed/default:entry/default:author" => "yes",
			"//synd/default:feed/default:entry/default:author/default:name" => "yes",
			"//synd/default:feed/default:entry/default:author/default:uri" => "yes",
			"//synd/default:feed/default:entry/default:author/default:email" => "yes",
			"//synd/default:feed/default:entry/default:contributor" => "yes",
			"//synd/default:feed/default:entry/default:contributor/default:name" => "yes",
			"//synd/default:feed/default:entry/default:contributor/default:uri" => "yes",
			"//synd/default:feed/default:entry/default:contributor/default:email" => "yes",
			"//synd/default:feed/default:entry/default:published" => "yes",
			"//synd/default:feed/default:entry/default:updated" => "yes",
			"//synd/default:feed/default:title" => "yes",
			"//synd/default:feed/default:subtitle" => "yes",
			"//synd/default:feed/default:author" => "yes",
			"//synd/default:feed/default:author/default:name" => "yes",
			"//synd/default:feed/default:author/default:uri" => "yes",
			"//synd/default:feed/default:author/default:email" => "yes",
			"//synd/default:feed/default:contributor" => "yes",
			"//synd/default:feed/default:contributor/default:name" => "yes",
			"//synd/default:feed/default:contributor/default:uri" => "yes",
			"//synd/default:feed/default:contributor/default:email" => "yes",
			"//synd/default:feed/default:link" => "yes",
			"//synd/default:feed/default:update" => "yes"
		)
	);*/
	$response = $clusterpointConnection->sendRequest($request);
	if(0 < $response->getFound())
	{
		$docs = $response->getDocuments();
		$doc = $docs[$id];
		return $doc;
	}
	else
	{
		return false;
	}
	}
	catch(Exception $e) 
	{
		return false;
	}
}

function xhtml($text)
{

	if(!function_exists("tidy_parse_string")) return $text;
	$config = array("indent" => true, "output-xhtml" => true, "drop-proprietary-attributes" => true);
	$tidy = tidy_parse_string($text, $config, "UTF8");
	$body = tidy_get_body($tidy);
	$text = preg_replace('/< \/?body[^>]*>/im', '', $body->value);
	return trim(str_replace("</body>","",str_replace("<body>","",$text)));
}

function DOMDocument2Notizia($xmldoc)
{
		
		$node = null;         	
		
		$node = simplexml_import_dom($xmldoc);
		
		$node->registerXPathNamespace("default","http://www.w3.org/2005/Atom");
		
		$id = "";
		$datetime = "";
		$newstype = "";
		$title = "";
		$description = "";
		$link = "";
		$author = array();
		$comments = "";
		$pubDate = "";
		$contributor = array();
		$published = "";
		$updated = "";
		$attachments = array();
		$channelTitle = "";
		$channelDescription = "";
		$channelManagingEditor = "";
		$channelWebMaster = "";
		$channelLastBuildDate = "";
		$channelTextInput = array();
		$channelSubtitle = "";
		$channelAuthor = array();
		$channelContributor = array();
		$channelLink = array();
		$channelUpdated = "";

		if(null != $node->rss[0])
		{

	                $id = $node->id[0]->__toString();
	
        	        $datetime = $node->datetime[0]->__toString();

                	$newstype = $node->type[0]->__toString();
			
			if($node->rss[0]->channel[0]->item[0]->title[0]) $title = $node->rss[0]->channel[0]->item[0]->title[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->description[0]) $description = $node->rss[0]->channel[0]->item[0]->description[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->contentencoded[0]) $description = trim($node->rss[0]->channel[0]->item[0]->contentencoded[0]->__toString());

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			if($node->rss[0]->channel[0]->item[0]->link[0]) $link = $node->rss[0]->channel[0]->item[0]->link[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->author[0])
			{
				$author[] = array
				(
					"name" => "",
					"uri" => "",
					"email" => $node->rss[0]->channel[0]->item[0]->author[0]->__toString()
				);
			}

			if($node->rss[0]->channel[0]->item[0]->comments[0]) $comments = $node->rss[0]->channel[0]->item[0]->comments[0]->__toString();

			if($node->rss[0]->channel[0]->item[0]->pubDate[0]) $pubDate = $node->rss[0]->channel[0]->item[0]->pubDate[0]->__toString();

			if($node->rss[0]->channel[0]->title[0]) $channelTitle = $node->rss[0]->channel[0]->title[0]->__toString();

			if($node->rss[0]->channel[0]->description[0]) $channelDescription = $node->rss[0]->channel[0]->description[0]->__toString();

			if($node->rss[0]->channel[0]->managingEditor[0]) $channelManagingEditor = $node->rss[0]->channel[0]->managingEditor[0]->__toString();

			if($node->rss[0]->channel[0]->webMaster[0]) $channelWebMaster = $node->rss[0]->channel[0]->webMaster[0]->__toString();

			if($node->rss[0]->channel[0]->lastBuildDate[0]) $channelLastBuildDate = $node->rss[0]->channel[0]->lastBuildDate[0]->__toString();				
			if
			(
				$node->rss[0]->channel[0]->textInput[0] &&
				$node->rss[0]->channel[0]->textInput[0]->title[0] &&
				$node->rss[0]->channel[0]->textInput[0]->description[0] &&
				$node->rss[0]->channel[0]->textInput[0]->name[0] &&
				$node->rss[0]->channel[0]->textInput[0]->link[0]
			) 
			{
				$channelTextInput = array
				(
					"title" => $node->rss[0]->channel[0]->textInput[0]->title[0]->__toString(),
					"description" => $node->rss[0]->channel[0]->textInput[0]->description[0]->__toString(),
					"name" => $node->rss[0]->channel[0]->textInput[0]->name[0]->__toString(),
					"link" => $node->rss[0]->channel[0]->textInput[0]->link[0]->__toString()
				);
			} 
 

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediacontent); $j++)
			{
				
				$attachment_title = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
				{
					$attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
				{
					 $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
				}
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
				else if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediacontent[$j]->mediadescription[0]->__toString();
                                } 
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

				$attachment_url = "";
				if($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediacontent[$j]["url"]);
				if(trim($attachment_url) == "") continue;

				if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;
 
				$attachments[] = array
				(
					$attachment_title,
					$attachment_url
				);

			}

			for($g = 0; $g < count($node->rss[0]->channel[0]->item[0]->mediagroup); $g++)
                        for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent); $j++)
                        {

                                $attachment_title = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediatitle[0] && trim($node->rss[0]->channel[0]->mediatitle[0]->__toString()))
                                {
                                         $attachment_title = $node->rss[0]->channel[0]->mediatitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediasubTitle[0] && trim($node->rss[0]->channel[0]->mediasubTitle[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediasubTitle[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->item[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->item[0]->mediadescription[0]->__toString();
                                }
                                else if($node->rss[0]->channel[0]->mediadescription[0] && trim($node->rss[0]->channel[0]->mediadescription[0]->__toString()))
                                {
                                        $attachment_title = $node->rss[0]->channel[0]->mediadescription[0]->__toString();
                                }

                                $attachment_url = "";
                                if($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]) $attachment_url = trim($node->rss[0]->channel[0]->item[0]->mediagroup[$g]->mediacontent[$j]["url"]);
                                if(trim($attachment_url) == "") continue;

                                if(trim($attachment_title) == "") $attachment_title = $attachment_url;

				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;

				if($alreadyThere) continue;

                                $attachments[] = array
                                (
                                        $attachment_title,
                                        $attachment_url
                                );
                        }

			for($j = 0; $j < count($node->rss[0]->channel[0]->item[0]->enclosure); $j++)
			{
				if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]))
				{
					
					$alreadyThere = false;
					for($a = 0; $a < count($attachments); $a++) if(trim($node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]) == trim($attachments[$a][1])) $alreadyThere = true;
					if($alreadyThere) continue;
					$attachments[] = array
					(
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"],
						$node->rss[0]->channel[0]->item[0]->enclosure[$j]["url"]
					);
				}	
			}

			if(1 == count($attachments) && ( $attachments[0][0] == $attachments[0][1] || trim($attachments[0][0]) == "") && trim($title) != "")
			{
				$attachments[0][0] = $title;
			}

		}
		else if(null != $node->RDF[0]) 
		{ 

			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();

			if($node->RDF[0]->item[0]->title[0]) $title = $node->RDF[0]->item[0]->title[0]->__toString();

			if($node->RDF[0]->item[0]->description[0]) $description = $node->RDF[0]->item[0]->description[0]->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			if($node->RDF[0]->item[0]->link[0]) $link = $node->RDF[0]->item[0]->link[0]->__toString();

			for($j = 0; $j < count($node->RDF[0]->item[0]->encenclosure); $j++)
			{
				$attachment_url = "";
				if($node->RDF[0]->item[0]->encenclosure[$j]["encurl"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["encurl"]);
				}
				else if($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"] && trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]) != "")
				{
					$attachment_url = trim($node->RDF[0]->item[0]->encenclosure[$j]["rdfresource"]);
				} 
				else
				{
					continue;
				}

				if(trim($attachment_url) == "") continue;
			
				$alreadyThere = false;
				for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
				if($alreadyThere) continue;

				$attachments[] = array
				(
					$attachment_url,
					$attachment_url
				);
			}

			if($node->RDF[0]->channel[0]->title[0]) $channelTitle = $node->RDF[0]->channel[0]->title[0]->__toString();

			if($node->RDF[0]->channel[0]->description[0]) $channelDescription = $node->RDF[0]->channel[0]->description[0]->__toString();

			if($node->RDF[0]->channel[0]->link[0]) $channelLink[] = array($node->RDF[0]->channel[0]->link[0],$node->RDF[0]->channel[0]->link[0]);

		}
		else if(null != $node->children("http://www.w3.org/2005/Atom")->feed) 
		{ 
			
			$id = $node->id[0]->__toString();

                        $datetime = $node->datetime[0]->__toString();

                        $newstype = $node->type[0]->__toString();
			
			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->title) $title = $node->children("http://www.w3.org/2005/Atom")->feed->entry->title->__toString();

                  	if($node->children("http://www.w3.org/2005/Atom")->feed->entry->summary) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->summary->__toString();

			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->content) $description = $node->children("http://www.w3.org/2005/Atom")->feed->entry->content->__toString();

			foreach($node->children("http://www.w3.org/2005/Atom")->feed->entry->author as $currauthor)
			{
				$author_name = "";
				if($currauthor->children("http://www.w3.org/2005/Atom")->name) $author_name = $currauthor->children("http://www.w3.org/2005/Atom")->name->__toString();
				$author_uri = "";
				if($currauthor->children("http://www.w3.org/2005/Atom")->uri) $author_uri = $currauthor->children("http://www.w3.org/2005/Atom")->uri->__toString();
				$author_email = "";
				if($currauthor->children("http://www.w3.org/2005/Atom")->email) $author_email = $currauthor->children("http://www.w3.org/2005/Atom")->email->__toString();
				
				$author[] = array
				(
					"name" => $author_name,
					"uri" => $author_uri,
					"email" => $author_email
				);
			}

                        foreach($node->children("http://www.w3.org/2005/Atom")->feed->entry->contributor as $currcontributor)
                        {
                                $contributor_name = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->name) $contributor_name = $currcontributor->children("http://www.w3.org/2005/Atom")->name->__toString();
                                $contributor_uri = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->uri) $contributor_uri = $currcontributor->children("http://www.w3.org/2005/Atom")->uri->__toString();
                                $contributor_email = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->email) $contributor_email = $currauthor->children("http://www.w3.org/2005/Atom")->email->__toString();

                                $contributor[] = array
                                (
                                        "name" => $contributor_name,
                                        "uri" => $contributor_uri,
                                        "email" => $contributor_email
                                );
                        }

			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->published) $published = $node->children("http://www.w3.org/2005/Atom")->feed->entry->published->__toString();

			if($node->children("http://www.w3.org/2005/Atom")->feed->entry->updated) $updated = $node->children("http://www.w3.org/2005/Atom")->feed->entry->updated->__toString();
			
			if($node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->title) $channelTitle = $node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->title->__toString();

			if($node->children("http://www.w3.org/2005/Atom")->feed->subtitle) $channelSubtitle = $node->children("http://www.w3.org/2005/Atom")->feed->subtitle->__toString();

			foreach($node->children("http://www.w3.org/2005/Atom")->feed->author as $currauthor)
                        {
                                $author_name = "";
                                if($currauthor->children("http://www.w3.org/2005/Atom")->name) $author_name = $currauthor->children("http://www.w3.org/2005/Atom")->name->__toString();
                                $author_uri = "";
                                if($currauthor->children("http://www.w3.org/2005/Atom")->uri) $author_uri = $currauthor->children("http://www.w3.org/2005/Atom")->uri->__toString();
                                $author_email = "";
                                if($currauthor->children("http://www.w3.org/2005/Atom")->email) $author_email = $currauthor->children("http://www.w3.org/2005/Atom")->email->__toString();

                                $channelAuthor[] = array
                                (
                                        "name" => $author_name,
                                        "uri" => $author_uri,
                                        "email" => $author_email
                                );
                        }

                        foreach($node->children("http://www.w3.org/2005/Atom")->feed->contributor as $currcontributor)
                        {
                                $contributor_name = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->name) $contributor_name = $currcontributor->children("http://www.w3.org/2005/Atom")->name->__toString();
                                $contributor_uri = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->uri) $contributor_uri = $currcontributor->children("http://www.w3.org/2005/Atom")->uri->__toString();
                                $contributor_email = "";
                                if($currcontributor->children("http://www.w3.org/2005/Atom")->email) $contributor_email = $currauthor->children("http://www.w3.org/2005/Atom")->email->__toString();

                                $channelContributor[] = array
                                (
                                        "name" => $contributor_name,
                                        "uri" => $contributor_uri,
                                        "email" => $contributor_email
                                );
                        }

			if($node->children("http://www.w3.org/2005/Atom")->feed->updated) $channelUpdated = $node->children("http://www.w3.org/2005/Atom")->feed->updated->__toString();

			if(trim(str_replace(chr(194).chr(160)," ",$description)) == "") $description = "";

			$link = "";
			foreach($node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->entry->children("http://www.w3.org/2005/Atom")->link as $atomlink)
			{	
				
				$atomlinkAttributes = array();

				foreach ($atomlink->attributes() as $attrname=>$attrval) 
				{
					$atomlinkAttributes[$attrname] = $attrval;
				}
				
				if( (!array_key_exists("rel",$atomlinkAttributes)) || strtolower(trim($atomlinkAttributes["rel"])) == "alternate" )
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$link = $atomlinkAttributes["href"];
					}
				}
				else
				{
					if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
					{
						$attachment_url = trim($atomlinkAttributes["href"]);
						$attachment_title = "";
						if(array_key_exists("title",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["title"])))
						{
							$attachment_title = trim($atomlinkAttributes["title"]);
						}
						else
						{
							$attachment_title = $attachment_url;
						}
						
						$alreadyThere = false;
						for($a = 0; $a < count($attachments); $a++) if(trim($attachments[$a][1]) == trim($attachment_url)) $alreadyThere = true;
						if($alreadyThere) continue;

						$attachments[] = array
						(
							$attachment_title,
							$attachment_url
						);
					}
				} 
				
			}

			foreach($node->children("http://www.w3.org/2005/Atom")->feed->children("http://www.w3.org/2005/Atom")->link as $atomlink)
                        {

                                $atomlinkAttributes = array();

                                foreach ($atomlink->attributes() as $attrname=>$attrval)
                                {
                                        $atomlinkAttributes[$attrname] = $attrval;
                                }

                                if( false && ( (!array_key_exists("rel",$atomlinkAttributes)) || strtolower(trim($atomlinkAttributes["rel"])) == "alternate" ))
                                {
                                        if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
                                        {
                                                $link = $atomlinkAttributes["href"];
                                        }
                                }
                                else
                                {
                                        if(array_key_exists("href",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["href"])))
                                        {
                                                $attachment_url = trim($atomlinkAttributes["href"]);
                                                $attachment_title = "";
                                                if(array_key_exists("title",$atomlinkAttributes) && 0 < strlen(trim($atomlinkAttributes["title"])))
                                                {
                                                        $attachment_title = trim($atomlinkAttributes["title"]);
                                                }
                                                else
                                                {
                                                        $attachment_title = $attachment_url;
                                                }

                                                $alreadyThere = false;
                                                for($a = 0; $a < count($channelLink); $a++) if(trim($channelLink[$a][1]) == trim($attachment_url)) $alreadyThere = true;
                                                if($alreadyThere) continue;

                                                $channelLink[] = array
                                                (
                                                        $attachment_title,
                                                        $attachment_url
                                                );
					}
				}
			}


		}

		if
		(
			0 < strlen(trim($id)) &&
			0 < strlen(trim($newstype)) &&
			0 < strlen(trim($datetime)) &&
			0 < strlen(trim($title).trim($description).trim($link))
		) 
		{
			
			return array
			(
				"id" => trim($id), 
				"type" => trim($newstype), 
				"datetime" => trim($datetime), 
				"title" => trim($title), 
				"description" => trim($description), 
				"link" => trim($link),
				"author" => $author,
				"comments" => trim($comments),
				"pubdate" => trim($pubDate),
				"contributor" => $contributor,
				"published" => trim($published),
				"updated" => trim($updated),
				"channel_title" => trim($channelTitle),
				"channel_description" => trim($channelDescription),
				"channel_managingEditor" => trim($channelManagingEditor),
				"channel_webMaster" => trim($channelWebMaster),
				"channel_lastBuildDate" => trim($channelLastBuildDate),
				"channel_textinput" => $channelTextInput,
				"channel_subtitle" => trim($channelSubtitle),
				"channel_author" => $channelAuthor,
				"channel_contributor" => $channelContributor,
				"channel_link" => $channelLink,
				"channel_updated" => trim($channelUpdated),
				"attachments" => $attachments
			);

		}

		
}
?>
