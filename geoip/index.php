<?php
require_once 'geoip.php';

GeoIP::loadData();

echo 'You are in: ' . GeoIP::getCity() . ',' . GeoIP::getCountry();
echo '<br />';
GeoIP::map(600,300);

