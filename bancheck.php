<?php
/* Include in a page to perform to check if a user is banned */
require_once 'session.php';
require_once 'mysql.php';

if (isset($_SESSION['steam64']))
{
    $steam64 = $_SESSION['steam64'];
    $db = Model::getInstance();
    $db->connect();

    if($db->isBanned($steam64)) {
        header("Location: http://banned.tf2pickup.com/");
        exit();
    }

    $db->disconnect();
}
