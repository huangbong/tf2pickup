<?php
// steam condenser is borked... so just in case
error_reporting(0);
ini_set('display_errors', '0');

require_once dirname(__FILE__).'/../session.php';
require_once dirname(__FILE__).'/../steam-condenser/steam-condenser.php';

if (!isset($_SESSION['steam64']))
    die ("[]");

try {
    $user = new SteamId($_SESSION['steam64']);
    $friends = $user->getFriends();
} catch (SteamCondenserException $e) {die("");}

$steamids = array();
foreach ($friends as $friend) {
    array_push($steamids, $friend->getSteamID64());
}

echo json_encode($steamids);