<?php 

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

                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&uml;","&egrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&not;","&igrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&sup2;","&ograve;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&sup1;","&ugrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83&Acirc;&copy;","&eacute;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Atilde;\x83","&agrave;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x93","&quot;",$notizia[$field]);
                        $notizia[$field] = str_replace("&iuml;\x81&para;","&raquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x82&not;","&euro;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x9c","&laquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&acirc;\x80\x9d","&raquo;",$notizia[$field]);
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
                                        $safectr1++; if($safectr1 == 20) { $notizia[$field] = $backupNotiziaDescription; $safectr2 = 99; break; }
                                }
                                $offset = $opos+6;

                                $safectr2++; if($safectr2 == 100) { $notizia[$field] = $backupNotiziaDescription; break; }

                        }

                        $notizia[$field] = str_replace("&Acirc;&laquo;","&laquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x93","&laquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&raquo;","&raquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x94","&raquo;",$notizia[$field]);
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
                        $notizia[$field] = str_replace("&acirc;\x80\x9e","&raquo;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x85","&hellip;",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;\x96","-",$notizia[$field]);
                        $notizia[$field] = str_replace("&Acirc;&reg;","&reg;",$notizia[$field]);
                        $notizia[$field] = str_replace("&agrave;\x9c","&Uuml;",$notizia[$field]);
                        $notizia[$field] = str_replace("cos&agrave;","cos&igrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x99","'",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;&nbsp;","&agrave;",$notizia[$field]);
			$notizia[$field] = str_replace("&acirc;\x80\x98","'",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x9c","&laquo;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x9d","&raquo;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;\x98","'",$notizia[$field]);
			$notizia[$field] = str_replace("&hellip;&raquo;","&raquo;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&Acirc;&brvbar;", "&hellip;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;\x88", "&Egrave;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&deg;", "", $notizia[$field]);
			$notizia[$field] = str_replace("e&Igrave;&euro;", "&egrave;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&laquo;","&laquo;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&raquo;","&raquo;", $notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&Acirc;&frac14;","&uuml;",$notizia[$field]); 
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&#x0093;","-",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&Acirc;&ordm;","&ordm;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;\x82&ordf;","&ordf;",$notizia[$field]);
			$notizia[$field] = str_replace("&agrave;&cent;&euro;&laquo;","-",$notizia[$field]);


}
