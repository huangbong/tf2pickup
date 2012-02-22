<?php include '../session.php';?>
<?php
	$message = $_POST['msg'];
	$curDate = date('h:i:s');
	
	if (!empty($_SESSION['username']))
	{
		$username = $_SESSION['username'];

		$con = mysql_connect("localhost","tf2pickup","dHuY4k3MNflJ6RV");
		if (!$con)
  		{
  			die('Could not connect: ' . mysql_error());
  		}

		mysql_select_db("tf2pickup", $con);
		mysql_query("INSERT INTO chat (id, time, username, message)
		VALUES (NULL, '$curDate','$username','$message')");
		mysql_close($con);
	}
?>
