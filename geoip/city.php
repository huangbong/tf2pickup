<?php
// This code demonstrates how to lookup the country, region, city,
// postal code, latitude, and longitude by IP Address.
// It is designed to work with GeoIP/GeoLite City

// Note that you must download the New Format of GeoIP City (GEO-133).
// The old format (GEO-132) will not work.

include("geoipcity.inc");
include("geoipregionvars.php");

// uncomment for Shared Memory support
// geoip_load_shared_mem("/usr/local/share/GeoIP/GeoIPCity.dat");
// $gi = geoip_open("/usr/local/share/GeoIP/GeoIPCity.dat",GEOIP_SHARED_MEMORY);

$gi = geoip_open("/usr/local/share/GeoIP/GeoLiteCity.dat",GEOIP_STANDARD);

$remoteip = $_SERVER['REMOTE_ADDR'];

$record = geoip_record_by_addr($gi,$remoteip);

//print $record->country_code . " " . $record->country_code3 . " " . $record->country_name . "\n";
//print $record->region . " " . $GEOIP_REGION_NAME[$record->country_code][$record->region] . "\n";
//print $record->city . "\n";
//print $record->postal_code . "\n";
//print $record->latitude . "\n";
//print $record->longitude . "\n";
//print $record->metro_code . "\n";
//print $record->area_code . "\n";
//print $record->continent_code . "\n";

$country = $record->country_name;
$city = $record->city;
$latitude = $record->latitude;
$longitude = $record->longitude;

geoip_close($gi);

//echo '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $latitude . ',' . $longitude . '&zoom=11&size=500x200&sensor=false" alt="map"/>';
?>
