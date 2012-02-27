<?php
/* Creates a PUG given lots of POST parameters
 * TODO: Checking if the same user makes multiple pugs
 */
require_once "../mysql.php";
require_once "../session.php";

if (!isset($_SESSION['steam64'])) {
    die ("Not authenticated!");
}

$required_keys = array("name", "pugtype", "map", "serverip", "serverport", "rcon");

$db = Model::getInstance();
$db->connect();

$db->createPUG($_POST["name"], $region, $pug_type, $map_name, $host_id, $server_name, $server_ip, $server_port, $rcon)
