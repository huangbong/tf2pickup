<?php include '../session.php';?>
<?php
	$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
	if (!$con)
  	{
  		die('Could not connect: ' . mysql_error());
  		echo "failed";
  	}

	mysql_select_db("tf2pickup", $con);
	
	$result = mysql_query("SELECT * FROM chat");
	
	while ($row = mysql_fetch_array($result))
	{
		$rawString = $row['time'] . " " . $row['username'] . ": " . $row['message'] . "\n";
		$urlCodes = array("%22", "+", "%2B", "%2C", "%21", "%2F", "%3F", "%3A", "%28", "%29", "%40", "%23", "%24", "%25", "%26", "%27", "%5E", "%2A", "%3B", "%5B", "%5D", "%7B", "%7D", "%5C", "%7C", "%3C", "%3E", "%3D", "%60");
		$actual = array("\"", " ", "+", ",", "!", "/", "?", ":", "(", ")", "@", "#", "$", "%", "&", "'", "^", "*", ";", "[", "]", "{", "}", "\\", "|", "<", ">", "=", "`");
		$string = str_replace($urlCodes, $actual, $rawString);
		//echo $row['time'] . " " . $row['username'] . ": " . $row['message'] . "\n";
		echo $string;
	}
	
	mysql_close($con);
?>
