<?php
require_once('mysql.php');
$time = "00:00:00";
$username = "frailbod";
$message = "lolcat";
$con = mysql::connect();
mysql_select_db("tf2pickup", $con);
mysql_query("INSERT INTO chat (id, time, username, message)
VALUES (NULL, '$time','$username','$message')");
mysql_close($con);
?>
