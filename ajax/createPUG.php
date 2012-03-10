<?php
/* Creates a PUG given lots of POST parameters
 * TODO: Checking if the same user makes multiple pugs
 */

// steam condenser is borked... so just in case
error_reporting(0);
ini_set('display_errors', '0');

require_once dirname(__FILE__).'/../mysql.php';
require_once dirname(__FILE__).'/../session.php';
require_once dirname(__FILE__).'/../steam-condenser/steam-condenser.php';
require_once dirname(__FILE__).'/../geoip/geoip.php';

function normalizeIP($ip) {
    $octets = array_map(intval, explode(".", $ip));
    $valid = array_reduce($octets, function($valid, $x) {
        return $valid && ($x >= 0 && $x <= 255);
    }, TRUE);
    if (!$valid) return false;
    else return implode(".", $octets);
}

if (!isset($_SESSION['steam64']))
    die ("Not authenticated!");

/* Make sure all the parameters were passed */
$required_keys = array("name", "pugtype", "map", "serverip", "serverport"
                     , "rcon");
foreach ($required_keys as $rkey) {
    if (!isset($_POST[$rkey]))
        die ("Missing parameter: " . $rkey);
}

// TODO: Move this validation into utility functions that can be unit tested

/* Get the parameters in to nicer variables and do basic sanity checks */
$ip = normalizeIP($_POST['serverip']);
if (!$ip) die ("Invalid IP");

$rcon = $_POST['rcon'];
$map = $_POST['map'];
$name = substr($_POST['name'], 0, 150);

$port = (int) $_POST['serverport'];
if (!(1 <= $port && $port <= 65535))
    die ("Invalid port");

$pug_type = (int) $_POST['pugtype'];
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

GeoIP::loadData($ip);
$region = strtolower(GeoIP::getRegion());

$lines = explode("\n", $status);
$host_name_line_parts = explode(" ", $lines[0], 1);
$server_name = $host_name_line_parts[1];

$db = Model::getInstance();
$db->connect();

$result = $db->createPUG($name, $region, $pug_type, $map
        , $_SESSION['steam64'], $server_name, $ip, $port, $rcon);

echo $result? "ok":"error";
