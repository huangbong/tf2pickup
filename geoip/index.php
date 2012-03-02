It looks like you live in:
<?php
require_once('geoip.php');
echo findme::city();
?>,
<?php
echo findme::country();
echo '<br />';
findme::map(600,300);
?>
