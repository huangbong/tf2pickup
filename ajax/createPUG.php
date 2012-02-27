<?php
/* Creates a PUG given lots of POST parameters
 * TODO: Checking if the same user makes multiple pugs
 */
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
$port = (int) $_GET['serverport'];
if (!(1 <= $port && $port <= 65535))
    die ("Invalid port number ($port)");

$pug_type = (int) $_GET['pugtype'];
if (!($pug_type === 1 || $pug_type === 2))
    die ("Invalid PUG type");

$ip = $_GET['serverip'];
$rcon = $_GET['rcon'];
$map = $_GET['map'];
$name = substr($_GET['name'], 0, 150);

$tf2_server = new SourceServer($ip, $port);
try {
  $server->rconAuth($rcon);
  $status = $server->rconExec('status');
}
catch(RCONNoAuthException $e) {
    die ('Invalid RCON Password');
}

echo $status;

/*
$db = Model::getInstance();
$db->connect();

$db->createPUG($name, $region, $pug_type, $map_name, $host_id,
               $server_name, $server_ip, $server_port, $rcon)
*/