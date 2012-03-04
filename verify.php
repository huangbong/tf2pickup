<?php
require_once 'session.php';
require_once 'config.php';
require_once 'openid.php';
require_once 'mysql.php';
require_once 'geoip/geoip.php';

$steam64 = SteamSignIn::validate();

if ($steam64 === false)
{
    header("Location: /");
    exit(0);
}

$steamapi = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" . STEAM_API_KEY . "&steamids=" . $steam64);
$json = json_decode($steamapi);
$username = $json->response->players[0]->personaname;
$avatar = $json->response->players[0]->avatar;

GeoIP::loadData();
$country = GeoIP::getCountry();

$db = Model::getInstance();
$db->connect();

if ($db->userExists($steam64)) {
    $db->updateUser($steam64, $username, $avatar, $country);
}
else {
    $db->createUser($steam64, $username, $avatar, $country);
}

mysql_close($con);

$_SESSION['steam64'] = $steam64;
$_SESSION['username'] = $username;
$_SESSION['avatar'] = $avatar;

header("Location: /");

