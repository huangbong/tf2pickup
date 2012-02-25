<?php
require_once '../session.php';
require_once '../mysql.php';

header("Content-type: text/plain");

/* TODO: Rate limiting
 * TODO: Error handling
 * TODO: DON'T USE MYSQL_* FUNCTIONS!!!!
 */

$db = mysql::connect();
$msg_id = -1;

/* Retrieve the client's current ID if it was provided and post
 * the chat message if they gave one.
 */
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['id'])) {
        $msg_id = (int) $_POST['id'];
    }

    if (isset($_POST['msg']) && isset($_SESSION['username'])) {
        $username = $_SESSION["username"];
        $message = mysql_real_escape_string($_POST['msg']);

        $query = "INSERT INTO `chat` (`time`, `username`, `message`) VALUES (NOW(), '$username', '$message')";
        mysql_query($query, $db);
    }
}
else {
    if (isset($_GET['id'])) {
        $msg_id = (int) $_GET['id'];
    }
}

/* Build query to get either last 50 rows or at most 50 rows after the
 * given id, in ascending order. */
$query = "SELECT * FROM `chat` ";
if ($msg_id > -1) {
    $query .= "WHERE `id` > '$msg_id' LIMIT 0,50";
}
else {
    $query .= "WHERE `id` > ((SELECT MAX(id) FROM `chat`) - 50)";
}

$result = mysql_query($query, $db);
$data = array();

while ($row = mysql_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);

mysql_close($db);