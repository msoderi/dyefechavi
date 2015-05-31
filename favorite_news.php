<?php
require_once("config.php");

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
if(0 == mysqli_num_rows(mysqli_query($conn, "SELECT * FROM favorite__news WHERE user = '".mysqli_escape_string($conn, $_GET["user"])."' AND newsid = '".mysqli_escape_string($conn, $_GET["newsid"])."'"))) 
{
	mysqli_query($conn, "INSERT INTO favorite__news(user, newsid, newstitle) VALUES ('".mysqli_escape_string($conn, $_GET["user"])."','".mysqli_escape_string($conn, $_GET["newsid"])."','".mysqli_escape_string($conn, $_GET["newstitle"])."')");
}
else
{
mysqli_query($conn, "DELETE FROM favorite__news WHERE user = '".mysqli_escape_string($conn, $_GET["user"])."' AND newsid = '".mysqli_escape_string($conn, $_GET["newsid"])."'");
}
mysqli_close($conn);
header("Location: {$_SERVER["HTTP_REFERER"]}");
exit(0);
?>
