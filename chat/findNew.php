<?php include '../session.php';?>
<?php
	$curID = $_POST['ID'];

	$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
	if (!$con)
  	{
  		die('Could not connect: ' . mysql_error());
  		echo "failed";
  	}

	mysql_select_db("tf2pickup", $con);

	$result = mysql_query("SELECT * FROM `chat` ORDER BY `chat`.`id` DESC");
	while ($row = mysql_fetch_array($result))
	{
		if ($row['id'] == $curID)
		{
			echo "nothing detected";
			break;
		}
		else if ($row['id'] != $curID)
		{
			echo $row['id'];
			break;
		}
	}

	mysql_close($con);
?>