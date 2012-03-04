<?php
require_once dirname(__FILE__).'/geoip/geoip.php';

GeoIP::loadData();

echo 'You are in: ' . GeoIP::getCity() . ',' . GeoIP::getCountry();
echo '<br />';
GeoIP::map(600,300);

