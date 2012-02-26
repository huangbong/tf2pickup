<?php
/* Returns complete list of open pugs to the client or
 * information about a specific lobby if an id is given. */
require_once "../mysql.php";

$db = Model::getInstance();
$db->connect();

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $data = $db->fetchPUG($id);

    echo json_encode($data);
}
else {
    $data = $db->fetchOpenPUGs();

    echo json_encode($data);
}

