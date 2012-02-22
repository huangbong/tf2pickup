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
		echo $row['time'] . " " . $row['username'] . ": " . $row['message'] . "\n";
	}
	
	mysql_close($con);
?>
