<?php
$get_browser = get_browser(null, true);

$browser = $get_browser["browser"];
$version = $get_browser["version"];

if ($browser == "IE" and $version < 9) {
    header('Location: getanewbrowser.php');
}
