<?php
/* Creates a PUG given lots of POST parameters
 * TODO: Checking if the same user makes multiple pugs
 */

// steam condenser is borked... so just in case
error_reporting(0);
ini_set('display_errors', '0');

require_once "../mysql.php";
require_once "../session.php";
require_once "../steam-condenser/steam-condenser.php";

//if (!isset($_SESSION['steam64']))
//    die ("Not authenticated!");
/* Make sure all the parameters were passed */
$required_keys = array("name", "pugtype", "map", "serverip", "serverport"
                     , "rcon");
foreach ($required_keys as $rkey) {
    if (!isset($_GET[$rkey]))
        die ("Missing parameter: " . $rkey);
}

/* Get the parameters into nicer variables and do basic sanity checks */
$ip = $_GET['serverip'];
$rcon = $_GET['rcon'];
$map = $_GET['map'];
$name = substr($_GET['name'], 0, 150);

$port = (int) $_GET['serverport'];
if (!(1 <= $port && $port <= 65535))
    die ("Invalid port number ($port)");

$pug_type = (int) $_GET['pugtype'];
if (!($pug_type === 1 || $pug_type === 2))
    die ("Invalid PUG type");

$server = new SourceServer($ip, $port);
try {
    $server->rconAuth($rcon);
    $status = $server->rconExec('status');
}
catch(RCONNoAuthException $e) {
    die ('Invalid RCON Password');
}

$lines = explode("\n", $status);
$host_name_line_parts = explode(" ", $lines[0], 1);
$server_name = $host_name_line_parts[1];


$db = Model::getInstance();
$db->connect();

$db->createPUG($name, $region, $pug_type, $map_name, $_SESSION['steam64'],
               $server_name, $server_ip, $server_port, $rcon)
