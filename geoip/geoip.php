<?php
require_once('city.php');

  class findme
  {
   public static function map($width,$height) { 
			global $longitude, $latitude;
			echo '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $latitude . ',' . $longitude . '&zoom=5&size=' . $width . 'x' . $height . '&sensor=false"/>';
		}

	public static function country() {
			global $country;
			return $country;
		}
   public static function city() {
         global $city;
         return $city;
      }
}
?>
