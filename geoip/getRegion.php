<?php
require_once '../config.php';
require_once '../geoip/geoipcity.inc';
require_once '../geoip/geoipregionvars.php';

/* For testing */
if ($_SERVER['REMOTE_ADDR'] === "127.0.0.1")
    die ("na");

GeoIP::loadData();

echo strtolower(GeoIP::getRegion());