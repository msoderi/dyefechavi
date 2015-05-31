<?php
require_once("config.php");

$conn = mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

mysqli_query($conn, "DELETE FROM favorite__channel WHERE user = '".mysqli_escape_string($conn, $_GET["user"])."' AND channel = '".mysqli_escape_string($conn, $_GET["channel"])."'");
mysqli_query($conn, "INSERT INTO favorite__channel(user, channel) VALUES ('".mysqli_escape_string($conn, $_GET["user"])."','".mysqli_escape_string($conn, $_GET["channel"])."')");
mysqli_close($conn);
if(false !== strpos($_SERVER["HTTP_REFERER"],"display=channels")) header("Location: http://www.synd.it/synd/segnalibri.php?display=channels");
else header("Location: {$_SERVER["HTTP_REFERER"]}");
exit(0);
