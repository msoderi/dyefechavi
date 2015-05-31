<?php
require_once("config.php");

$channel = $_GET["channel"];
if(false !== strpos($channel,"?")) $channel = substr($channel,0,strpos($channel,"?"));
$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
if(0 == mysqli_num_rows(mysqli_query($conn, "SELECT * FROM favorite__channel WHERE user = '".mysqli_escape_string($conn, $_GET["user"])."' AND channel = '".mysqli_escape_string($conn, $channel)."'"))) 
{
	mysqli_query($conn, "INSERT INTO favorite__channel(user, channel) VALUES ('".mysqli_escape_string($conn, $_GET["user"])."','".mysqli_escape_string($conn, $channel)."')");
}
else
{
mysqli_query($conn, "DELETE FROM favorite__channel WHERE user = '".mysqli_escape_string($conn, $_GET["user"])."' AND channel = '".mysqli_escape_string($conn, $channel)."'");
}
mysqli_close($conn);
header("Location: {$_SERVER["HTTP_REFERER"]}");
exit(0);
?>
