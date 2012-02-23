<?php
class mysql {
function connect() {
$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
return $con;
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
}
}
?>
