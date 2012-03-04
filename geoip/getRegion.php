<?php
require_once dirname(__FILE__).'/geoip.php';

if (isset($_GET['ip']))
    $ip = $_GET['ip'];
else
    $ip = $_SERVER['REMOTE_ADDR'];

GeoIP::loadData();
echo strtolower(GeoIP::getRegion());