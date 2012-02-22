<?php
$time = "00:00:00";
$username = "frailbod";
$message = "lolcat";
$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
mysql_select_db("tf2pickup", $con);
mysql_query("INSERT INTO chat (id, time, username, message)
VALUES (NULL, '$time','$username','$message')");
mysql_close($con);
?>
