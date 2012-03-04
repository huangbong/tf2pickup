<?php
require_once '../config.php';
require_once 'geoipcity.inc';

$gi = geoip_open(GEOIP_DB_FILE, GEOIP_STANDARD);

$record = geoip_record_by_addr($gi,$_GET['ip']);

geoip_close($gi);

var_dump($record);

