<?php include '../session.php';?>
<?php
	$curID = $_POST['ID'];
	$newID = $_POST['newID'];
	$toID = $newID - $curID;

	//if ($curID != 0)
	//{
	//	$curID -= 2;
	//}

	$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
	if (!$con)
  	{
  		die('Could not connect: ' . mysql_error());
  		echo "failed";
  	}

	mysql_select_db("tf2pickup", $con);
	
	$result = mysql_query("SELECT * FROM chat LIMIT $curID, $toID");
	
	while ($row = mysql_fetch_array($result))
	{
		$string = urldecode($row['time'] . " " . $row['username'] . ": " . $row['message'] . "\n");
		echo $string;
	}
	
	mysql_close($con);
?>
