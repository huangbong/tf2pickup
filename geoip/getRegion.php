<?php
require_once dirname(__FILE__).'/geoip.php';

/* For testing */
if ($_SERVER['REMOTE_ADDR'] === "127.0.0.1")
    die ("na");

GeoIP::loadData();

echo strtolower(GeoIP::getRegion());