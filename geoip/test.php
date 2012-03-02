<?php

include("geoipcity.inc");
include("geoipregionvars.php");

$gi = geoip_open("/usr/local/share/GeoIP/GeoLiteCity.dat",GEOIP_STANDARD);

$record = geoip_record_by_addr($gi,$_GET['ip']);

var_dump($record);

geoip_close($gi);

?>

