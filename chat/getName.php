<?php include '../session.php';?>
<?php
if (!empty($_SESSION['username']))
{
	echo "0";
}
else
{
	echo "1";
}
?>