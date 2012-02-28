<?php
/* Returns complete list of open pugs to the client or
 * information about a specific lobby if an id is given. */
require_once "../mysql.php";

$db = Model::getInstance();
$db->connect();

$export = array();

if (isset($_GET['pugs'])) {
    // Should be a list of id,time;id,time;id,time etc...
    $param = $_GET['pugs'];

    $pug_ids_times = explode(";", $param);

    if (count($pug_ids_times) > 100) {
        // This seems sketchy...
        die();
    }

    $pugs = array_map(function($id_time) {
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

    $export = $db->fetchUpdatedPUGs($pugs);
}
else {
    /* For every currently open pug... */
    $pugs = $db->fetchOpenPUGs();
    foreach ($pugs as $pug) {
        /* Copy the data we want */
        $pug_data = array();
        $pug_data["id"] = $pug["id"];
        $pug_data["region"] = $pug["region"];
        $pug_data["map"] = $pug["map"];
        $pug_data["name"] = $pug["name"];
        $pug_data["pug_type"] = $pug["pug_type"];
        $pug_data["servername"] = $pug["servername"];
        $pug_data["updated"] = $pug["updated"];

        /* Get all the players for this pug */
        $players = $db->fetchPlayersInPUG((int)$pug["id"]);
        $players_data = array();
        foreach ($players as $player) {
            $player_data = array();

            $player_data["team"] = $player["team"];
            $player_data["class"] = $player["class"];
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
}

echo json_encode($export);
