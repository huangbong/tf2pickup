<?php
/* Returns complete list of open pugs to the client or
 * information about a specific lobby if an id is given. */
require_once dirname(__FILE__).'/../mysql.php';
require_once dirname(__FILE__).'/../session.php';

$db = Model::getInstance();
$db->connect();

/* Fetch either all open pugs or, if a ?pugs parameter was given, fetch
 * only pugs which have been recently updated */
if (isset($_GET['pugs'])) {
    // Only give un-auth'ed clients an initial list, but block
    // further data
    // (This should be client side, so we don't get pinged at all...)
    if (!isset($_SESSION['steam64']))
        die ("[]");

    // Should be a list of id,time;id,time;id,time etc...
    $param = $_GET['pugs'];

    $pug_ids_times = explode(";", $param);

    if (count($pug_ids_times) > 100) {
        // This seems sketchy...
        die();
    }

    $pugs_params = array_map(function($id_time) {
        $id = $time = 0;

        if (strpos($id_time, ",") != false) {
            list($id, $time) = explode(",", $id_time, 2);
            $id = (int) $id;
            $time = (int) $time;
        }
        else
            $id = (int) $id_time;

        return array($id, $time);
    }, $pug_ids_times);

    $pugs = $db->fetchUpdatedPUGs($pugs_params);
}
else {
    /* For every currently open pug... */
    $pugs = $db->fetchOpenPUGs();
}

/* Parse data from server and send only the values we need to the client.
 * Also, fill out the players from a lobby */
$export = array();
foreach ($pugs as $pug) {
    /* Copy the data we want */
    $pug_data = array();
    $pug_data["id"] = $pug["id"];
    $pug_data["region"] = $pug["region"];
    $pug_data["map"] = $pug["map"];
    $pug_data["name"] = $pug["name"];
    $pug_data["pug_type"] = $pug["pug_type"];
    $pug_data["server_name"] = $pug["server_name"];
    $pug_data["updated"] = $pug["updated"];
    $pug_data["started"] = $pug["started"];

    /* Get all the players for this pug */
    $players = $db->fetchPlayersInPUG((int)$pug["id"]);
    $players_data = array();
    foreach ($players as $player) {
        $player_data = array();

        $player_data["team"] = $player["team"];
        /* class is a reserved word, so change it to class_id for our js */
        $player_data["class_id"] = $player["class"];
        $player_data["steam64"] = $player["user_id"];
        $player_data["name"] = $player["username"];
        $player_data["avatar"] = $player["avatar"];

        if ($pug["host_id"] === $player["user_id"]) {
            $pug_data["hostname"] = $player["username"];
        }

        array_push($players_data, $player_data);
    }
    $pug_data["players"] = $players_data;

    /* And add this pug's data to the list of data to
     * send to the client.                            */
    array_push($export, $pug_data);
}

echo json_encode($export);
