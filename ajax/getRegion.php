<?php

include("../geoip/geoipcity.inc");
include("../geoip/geoipregionvars.php");

$gi = geoip_open("/usr/local/share/GeoIP/GeoLiteCity.dat",GEOIP_STANDARD);

$record = geoip_record_by_addr($gi,$_SERVER["REMOTE_ADDR"]);

geoip_close($gi);

echo strtolower($record["continent_code"]);