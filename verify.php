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

$country = findme::country();

$con = mysql::connect();

$search = "SELECT * FROM `users` WHERE `id` = '$steam64'";
$result = mysql_query($search);

if (mysql_num_rows($result) === 1) {
    mysql_query("UPDATE `users` SET `username` = '$username', `avatar` = '$avatar', `country` = '$country' WHERE `id` = '$steam64'");
}
else {
    mysql_query("INSERT INTO users (id, username, avatar, country)
                            VALUES ('$steam64','$username','$avatar','$country')");
}

mysql_close($con);

$_SESSION['steam64'] = $steam64;
$_SESSION['username'] = $username;
$_SESSION['avatar'] = $avatar;

header("Location: /");

