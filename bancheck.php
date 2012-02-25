<?php
require_once 'session.php';
require_once 'mysql.php';

if (isset($_SESSION['steam64']))
{
    $steam64 = $_SESSION['steam64'];
    $con = mysql::connect();
    $search = "SELECT * FROM `users` WHERE `id` = '$steam64'";
    $result = mysql_query($search);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    if($row[banned] == 1) {
        header("Location: http://banned.tf2pickup.com/");
    }
}

mysql_close($con);
