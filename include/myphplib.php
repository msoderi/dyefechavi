<?php 

require_once("config.php");

function messageConfirmationCharfix($message)
{
$container["messaggio"] = $message;
mycharfix($container, "messaggio");
return $container["messaggio"];
}

function mycharfix(&$notizia, $field)
{
                        
			for($i = 0; $i < strlen($notizia[$field]); $i++)
                        {
                                if
                                (
                                        substr($notizia[$field],$i, 1) != "'" &&
                                        substr($notizia[$field],$i, 1) != "\"" &&
                                        substr($notizia[$field],$i, 1) != "&" &&
                                        substr($notizia[$field],$i, 1) != "<" &&
                                        substr($notizia[$field],$i, 1) != ">"
                                )
                                {
                                        $notizia[$field] = str_replace(substr($notizia[$field],$i,1),htmlentities(substr($notizia[$field],$i,1)), $notizia[$field]);
                                }

                        }

			$notizia[$field] = str_replace("&Atilde;&copy;","&eacute;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&uml;","&egrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&not;","&igrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&Atilde;\x83&Acirc;&shy;","&igrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&sup2;","&ograve;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&sup1;","&ugrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&copy;","&eacute;",$notizia[$field]);
			$notizia[$field] = str_replace("&Atilde;\x88","&Egrave;",$notizia[$field]);                        
			$notizia[$field] = str_replace("&Atilde;\x83","&agrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x93","-",$notizia[$field]);
                        $notizia[$field] = str_replace("&iuml;\x81&para;","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x82&not;","&euro;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x9c","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x9d","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x99","'",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;","&agrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x80","&Agrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&deg;","&deg;",$notizia[$field]);

                        $backupNotiziaDescription = $notizia[$field];
                        $offset = 0;
                        $safectr1 = 0;
                        $safectr2 = 0;
                        while(false !== strpos($notizia[$field],"&deg;",$offset))
                        {
                                $opos = strpos($notizia[$field],"&deg;",$offset)-1;
                                $pos = strpos($notizia[$field],"&deg;",$offset)-1;
                                while(!is_numeric(substr($notizia[$field],$pos,1)))
                                {
                                        $notizia[$field] = substr($notizia[$field],0,$pos).substr($notizia[$field],$pos+1);
                                        $pos = strpos($notizia[$field],"&deg;",$offset)-1;
					if($pos == -1) { $notizia[$field] = $backupNotiziaDescription; break; }
                                        $safectr1++; if($safectr1 == 20) { $notizia[$field] = $backupNotiziaDescription; $safectr2 = 99; break; }
                                }
                                $offset = $opos+6;

                                $safectr2++; if($safectr2 == 100) { $notizia[$field] = $backupNotiziaDescription; break; }

                        }

                        $notizia[$field] = str_replace("&Acirc;&laquo;","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&quot;","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&Acirc;-","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x93","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&raquo;","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&quot;","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&Acirc;-","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x94","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x8c","&Igrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&uml;","&egrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&copy;","&egrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&not;","&igrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&sup1;","&ugrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x88","&egrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&sup2;","&ograve;", $notizia[$field]);
                        $notizia[$field] = str_replace(" &iuml;&iquest;&frac12;"," &egrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("\"&iuml;&iquest;&frac12;","\"&egrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("pi&iuml;&iquest;&frac12;","pi&ugrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&iuml;&iquest;&frac12;","&agrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&shy;","", $notizia[$field]);
                        $notizia[$field] = str_replace(chr(239).chr(191).chr(189), "&ugrave;", $notizia[$field]);
                        $notizia[$field] = str_replace("&Euml;\x9a","&deg;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&frac14;","&uuml;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80&brvbar;","&hellip;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&curren;","&auml;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x80","&euro;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&ordf;","&ordf;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x91","'",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x9e","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x85","&hellip;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x96","-",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&reg;","&reg;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x9c","&Uuml;",$notizia[$field]);
                        $notizia[$field] = str_replace("cos&agrave;","cos&igrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x99","'",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;&nbsp;","&agrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;\x80\x98","'",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x9c","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x9d","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x98","'",$notizia[$field]);
			$notizia[$field] = str_replace("&hellip;&raquo;","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&hellip;&quot;","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&hellip;-","&quot;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;&brvbar;", "&hellip;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;\x88", "&Egrave;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&deg;", "", $notizia[$field]);
			$notizia[$field] = str_replace("e&Igrave;&euro;", "&egrave;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&laquo;","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&quot;","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82-","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&raquo;","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&quot;","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82-","&quot;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;&frac14;","&uuml;",$notizia[$field]); 
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&#x0093;","-",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&Acirc;&ordm;","&ordm;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&ordf;","&ordf;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&laquo;","-",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&quot;","-",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;-","-",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&reg;","&reg;",$notizia[$field]);
			$notizia[$field] = str_replace("&Igrave;&Acirc;\x81n","&nacute;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;","&acirc;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&sect;","&ccedil;",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;\x80&sup2;","&deg;",$notizia[$field]);
			$notizia[$field] = str_replace("&Auml;\x83","&#x103;",$notizia[$field]);
			$notizia[$field] = str_replace("&Aring;&pound;","&#x163;",$notizia[$field]);
			$notizia[$field] = str_replace("&Aring;\x9f","&#x15F",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&reg;","&icirc;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&laquo;","&euml;",$notizia[$field]);
			$notizia[$field] = str_replace("&Egrave;\x99","&#x15F;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Egrave;\x9b","&#x163;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Egrave;\x98","&#x15E;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Aring;\x9e","&#x15E;",$notizia[$field]);
		        $notizia[$field] = str_replace("&acirc;\x86\x92","&hellip;",$notizia[$field]);
		        $notizia[$field] = str_replace("&acirc;\x80\x94","-",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x92","'",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&para;","&ouml;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&pound;","&atilde;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x9f","&szlig;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&copy;","&copy;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&acute;","'",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&iexcl;","&aacute;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Aring;\x8d","&#x014D;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&sup3;","&oacute;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80&sup3;","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80&cent;","&bull;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x89","&Eacute;",$notizia[$field]);
			$notizia[$field] = str_replace("&Acirc;&nbsp;"," ",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;","",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&ordf;","&ecirc;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&raquo;","&ucirc;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;&acute;","&ocirc;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x96&cedil;","&#x25b6;",$notizia[$field]);
			$notizia[$field] = str_replace("tutto &quot;","tutto &raquo;",$notizia[$field]);
			$notizia[$field] = str_replace("\x82","&eacute;",$notizia[$field]);
			$notizia[$field] = str_replace("&iuml;&raquo;&iquest;","",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;\x80&uml;","",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&plusmn;","&ntilde;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&ordm;","&uuml;",$notizia[$field]);
			$notizia[$field] = str_replace("&Auml;\x97","&egrave;",$notizia[$field]);
			$notizia[$field] = str_replace("Continua &quot;","Continua &raquo;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x92","&Ograve;",$notizia[$field]);
			$notizia[$field] = str_replace("&Auml;\x93","&#275;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&yen;","&#229;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Euml;\x86","&Egrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;&euro;&acirc;\x84&cent;","'",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&eacute;","",$notizia[$field]);	
			$notizia[$field] = str_replace("&agrave;&nbsp;","&agrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&Icirc;\x9c","M",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;\x97\x8a","&#149;",$notizia[$field]);
			$notizia[$field] = str_replace("&iuml;&eacute;&iexcl;","&#149;",$notizia[$field]);
			$notizia[$field] = str_replace(chr(38).chr(97).chr(103).chr(114).chr(97).chr(118).chr(101).chr(59).chr(141),"&igrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&shy;","&iacute;",$notizia[$field]);
			$notizia[$field] = str_replace(chr(137),"&permil;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;".chr(153),"&Ugrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&Auml;".chr(141),"&#x10D;",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;".chr(134).chr(144),"&larr;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;".chr(147),"&Oacute;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;".chr(148),"&Ocirc;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;".chr(150),"&Ouml;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;".chr(129),"&Agrave;",$notizia[$field]);
			if($field != "title" && false === strpos($notizia[$field],"<br") && false === strpos($notizia[$field],"<p") && false === strpos($notizia[$field],"<div") ) 
			{
				$notizia[$field] = str_replace("\n","<br/>",trim($notizia[$field]));
				if(false !== strpos($notizia[$field],"<br/><br/><br/>")) while(false !== strpos($notizia[$field],"<br/><br/>"))
				{
					 $notizia[$field] = str_replace("<br/><br/>","<br/>",$notizia[$field]);				
				}
			}
			
}

function tidy($text)
{
  if (function_exists('tidy_parse_string'))
  {
    $config = array('indent' => true,
      'output-xhtml' => true,
      'wrap' => 200);
    $tidy = tidy_parse_string($text, $config, 'UTF8');
    $body = tidy_get_body($tidy);
    $text = preg_replace('/< \/?body[^>]*>/im', '', $body->value);
	$text = str_replace("<body>","",$text); $text = str_replace("</body>","",$text);
    return trim($text);
  }

  trigger_error('tidy extension not found', E_USER_WARNING);
  
	return $text;
}

function channel_specific($channelurl, &$notizia)
{
}

function myDownloadDatetimeFormat($idatetime)
{

	$datetime = trim($idatetime);
	$datetime = str_replace("Mon,","Luned&igrave;",$datetime);
	$datetime = str_replace("Tue,","Marted&igrave;",$datetime);
	$datetime = str_replace("Wed,","Mercoled&igrave;",$datetime);
	$datetime = str_replace("Thu,","Gioved&igrave;",$datetime);
	$datetime = str_replace("Fri,","Venerd&igrave;",$datetime);
	$datetime = str_replace("Sat,","Sabato",$datetime);
	$datetime = str_replace("Sun,","Domenica",$datetime);
	$datetime = str_replace("Jan ","gennaio ",$datetime);
	$datetime = str_replace("Feb ","febbraio ",$datetime);
	$datetime = str_replace("Mar ","marzo ",$datetime);
	$datetime = str_replace("Apr ","aprile ",$datetime);
	$datetime = str_replace("May ","maggio ",$datetime);
	$datetime = str_replace("Jun ","giugno ",$datetime);
	$datetime = str_replace("Jul ","luglio ",$datetime);
	$datetime = str_replace("Aug ","agosto ",$datetime);
	$datetime = str_replace("Sep ","settembre ",$datetime);
	$datetime = str_replace("Oct ","ottobre ",$datetime);
	$datetime = str_replace("Nov ","novembre ",$datetime);
	$datetime = str_replace("Dec ","dicembre ",$datetime);
	$datetime = substr($datetime,0,strrpos($datetime," "));
	$datetime = substr($datetime,0,strrpos($datetime," "))." alle ".substr($datetime,strrpos($datetime," "));
	return $datetime;
	
}

function myPublicationDatetimeFormat($idatetime)
{
	
	$datetime = date_create($idatetime);
	
	if(!$datetime)
	{
		$fidatetime = $idatetime;
		$fidatetime = str_replace("lun,","Mon,",$fidatetime);
		$fidatetime = str_replace("mar,","Tue,",$fidatetime);
		$fidatetime = str_replace("mer,","Wed,",$fidatetime);
		$fidatetime = str_replace("gio,","Thu,",$fidatetime);
		$fidatetime = str_replace("ven,","Fri,",$fidatetime);
		$fidatetime = str_replace("sab,","Sat,",$fidatetime);
		$fidatetime = str_replace("dom,","Sun,",$fidatetime);
		$fidatetime = str_replace("Lun,","Mon,",$fidatetime);
		$fidatetime = str_replace("Mar,","Tue,",$fidatetime);
		$fidatetime = str_replace("Mer,","Wed,",$fidatetime);
		$fidatetime = str_replace("Gio,","Thu,",$fidatetime);
		$fidatetime = str_replace("Ven,","Fri,",$fidatetime);
		$fidatetime = str_replace("Sab,","Sat,",$fidatetime);
		$fidatetime = str_replace("Dom,","Sun,",$fidatetime);
		
		$fidatetime = str_replace("gen ","Jan ",$fidatetime);
		$fidatetime = str_replace("feb ","Feb ",$fidatetime);
		$fidatetime = str_replace("mar ","Mar ",$fidatetime);
		$fidatetime = str_replace("apr ","Apr ",$fidatetime);
		$fidatetime = str_replace("mag ","May ",$fidatetime);
		$fidatetime = str_replace("giu ","Jun ",$fidatetime);
		$fidatetime = str_replace("lug ","Jul ",$fidatetime);
		$fidatetime = str_replace("ago ","Aug ",$fidatetime);
		$fidatetime = str_replace("set ","Sep ",$fidatetime);
		$fidatetime = str_replace("ott ","Oct ",$fidatetime);
		$fidatetime = str_replace("nov ","Nov ",$fidatetime);
		$fidatetime = str_replace("dic ","Dec ",$fidatetime);
		$fidatetime = str_replace("Gen ","Jan ",$fidatetime);
		$fidatetime = str_replace("Feb ","Feb ",$fidatetime);
		$fidatetime = str_replace("Mar ","Mar ",$fidatetime);
		$fidatetime = str_replace("Apr ","Apr ",$fidatetime);
		$fidatetime = str_replace("Mag ","May ",$fidatetime);
		$fidatetime = str_replace("Giu ","Jun ",$fidatetime);
		$fidatetime = str_replace("Lug ","Jul ",$fidatetime);
		$fidatetime = str_replace("Ago ","Aug ",$fidatetime);
		$fidatetime = str_replace("Set ","Sep ",$fidatetime);
		$fidatetime = str_replace("Ott ","Oct ",$fidatetime);
		$fidatetime = str_replace("Nov ","Nov ",$fidatetime);
		$fidatetime = str_replace("Dic ","Dec ",$fidatetime);
	
		$datetime = date_create($fidatetime);

	}

	if(!$datetime) return $idatetime;

	$datetime = $datetime->format(DateTime::RSS);
	$datetime = trim($datetime);
        $datetime = str_replace("Mon,","Luned&igrave;",$datetime);
        $datetime = str_replace("Tue,","Marted&igrave;",$datetime);
        $datetime = str_replace("Wed,","Mercoled&igrave;",$datetime);
        $datetime = str_replace("Thu,","Gioved&igrave;",$datetime);
        $datetime = str_replace("Fri,","Venerd&igrave;",$datetime);
        $datetime = str_replace("Sat,","Sabato",$datetime);
        $datetime = str_replace("Sun,","Domenica",$datetime);
        $datetime = str_replace("Jan ","gennaio ",$datetime);
        $datetime = str_replace("Feb ","febbraio ",$datetime);
        $datetime = str_replace("Mar ","marzo ",$datetime);
        $datetime = str_replace("Apr ","aprile ",$datetime);
        $datetime = str_replace("May ","maggio ",$datetime);
        $datetime = str_replace("Jun ","giugno ",$datetime);
        $datetime = str_replace("Jul ","luglio ",$datetime);
        $datetime = str_replace("Aug ","agosto ",$datetime);
        $datetime = str_replace("Sep ","settembre ",$datetime);
        $datetime = str_replace("Oct ","ottobre ",$datetime);
        $datetime = str_replace("Nov ","novembre ",$datetime);
        $datetime = str_replace("Dec ","dicembre ",$datetime);
        $datetime = substr($datetime,0,strrpos($datetime," "));
        $datetime = substr($datetime,0,strrpos($datetime," "));
        return $datetime;

}

function isMailAddress($address)
{
	return preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $address);
}

function isFavoriteNews($user, $newsid)
{
global $dbhost, $dbuser, $dbpass, $dbname;

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
	
$isFavorite =  mysqli_num_rows(mysqli_query($conn, "SELECT * FROM favorite__news WHERE user = '".mysqli_escape_string($conn, $user)."' AND newsid = '".mysqli_escape_string($conn, $newsid)."'"));
	mysqli_close($conn);
	return $isFavorite; 
}

function isFavoriteChannel($user, $channel)
{
	if(false !== strpos($channel, "?")) $channel = substr($channel, 0, strpos($channel, "?"));
global $dbhost, $dbuser, $dbpass, $dbname;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
        
$isFavorite =  mysqli_num_rows(mysqli_query($conn, "SELECT * FROM favorite_channel WHERE user = '".mysqli_escape_string($conn, $user)."' AND channel = '".mysqli_escape_string($conn, $channel)."'"));
        mysqli_close($conn);
        return $isFavorite;
}

function getChannelsdCount()
{
global $dbhost, $dbuser, $dbpass, $dbname;
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
	
$result = mysqli_query($conn, "SELECT count(*) canali FROM admin__channels");
	$resulta = mysqli_fetch_array($result, MYSQLI_ASSOC);
	$canali = $resulta["canali"];
	mysqli_close($conn);
	return $canali;
}

?>
