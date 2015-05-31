<?php
$width = 0;
if(isset($_GET["width"]) && is_numeric($_GET["width"])) $width = floor($_GET["width"]);
$height = 0;
if(isset($_GET["height"]) && is_numeric($_GET["height"])) $height = floor($_GET["height"]);
$type = "";
if(isset($_GET["type"]) && ( $_GET["type"] == "list_last" || $_GET["type"] == "primo_piano" || $_GET["type"] == "custom_query")) $type = $_GET["type"];
if($type == "") exit(0);
$query = "";
if(isset($_GET["query"])) $query = urlencode($_GET["query"]);
?>

function assegnaXMLHttpRequest() {

var
 XHR = null,
 
 browserUtente = navigator.userAgent.toUpperCase();


 if(typeof(XMLHttpRequest) === "function" || typeof(XMLHttpRequest) === "object")
  XHR = new XMLHttpRequest();

 else if(
  window.ActiveXObject &&
  browserUtente.indexOf("MSIE 4") <  0
 ) {
 
  if(browserUtente.indexOf("MSIE 5") < 0)
   XHR = new ActiveXObject("Msxml2.XMLHTTP");

  else
   XHR = new ActiveXObject("Microsoft.XMLHTTP");
 }

 return XHR;
} 

var synd__xmlhttp = assegnaXMLHttpRequest();

synd__xmlhttp.open("GET", "<?=$baseurl?>/widget.php?width=<?=$width?>&height=<?=$height?>&type=<?=$type?>&;query=<?=$query?>",true);

synd__xmlhttp.setRequestHeader("connection", "close");

synd__xmlhttp.onreadystatechange=function() {
  if (synd__xmlhttp.readyState==4) {
   document.getElementById('synd_div').innerHTML = synd__xmlhttp.responseText;
  }
 }

synd__xmlhttp.send(null);

