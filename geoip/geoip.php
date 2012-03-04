<?php
require_once dirname(__FILE__).'/../config.php';
require_once dirname(__FILE__).'/geoipcity.inc';

/* This is still poorly designed, but at least it attempts to use
 * OOP, after what frailbod did to it
 *
 * And, sadly, we can't name this GeoIP because that is used
 */
class GeoIP
{
    private static $record;

    /* Takes an IP address to load the geolocation data for, or defaults to
     * the current request IP */
    public static function loadData($ip = NULL) {
        if ($ip === NULL)
            $ip = $_SERVER['REMOTE_ADDR'];

        $gi = geoip_open(GEOIP_DB_FILE, GEOIP_STANDARD);
            self::$record = geoip_record_by_addr($gi, $ip);
        geoip_close($gi);
    }

    /* Outputs a Google maps image, centered at the current location and
     *
     */
    public static function map($width, $height) {
        if (self::$record === NULL)
            return;

    	echo '<img src="http://maps.googleapis.com/maps/api/staticmap?center='
    	       . self::$record->latitude . ',' . self::$record->longitude
    	       . '&zoom=5&size=' . $width . 'x' . $height . '&sensor=false" '.
    	       'width="' . $width . '" height="' . $height . '" />';
    }

	public static function getCountry() {
        if (self::$record === NULL)
            return "";

		return self::$record->country;
	}

    public static function getCity() {
        if (self::$record === NULL)
            return "";

         return self::$record->city;
    }

    public static function getRegion() {
        if (self::$record === NULL)
            return "";

         return self::$record->continent_code;
    }

}

