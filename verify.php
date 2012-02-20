<?php 
include 'openid.php';
include 'mysql.php';

$steam64 = SteamSignIn::validate();
$steamapi = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=2C90B16410B5B8BE3DB9A7FD67A76A89&steamids=" . $steam64);
$json = json_decode($steamapi);
$username = $json->response->players[0]->personaname;
$avatar = $json->response->players[0]->avatar;
//mysql_select_db("tf2pickup", $con);
//mysql_query("INSERT INTO users (id, username, avatar)
//VALUES ('$steam64', '$username', '$avatar')") or die(mysql_error());
//mysql_close($con);

$expire = time()+2592000;

setcookie("steam64", $steam64, $expire);
setcookie("username", $username, $expire);
setcookie("avatar", $avatar, $expire);

header("Location: /");
?>
