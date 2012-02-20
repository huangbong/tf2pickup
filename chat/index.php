<html>
<head>
<title>TF2 Pickup Chat</title>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>
</head>
<body>

<?php
$file = file_get_contents('chatlog.txt') or exit ("Unable to open file!");

if (isset($_POST['submit']))
{
	if ($_POST["chat"] != "") 
	{
		$addFile = fopen("chatlog.txt", 'a') or exit ("Unable to open file!");
		$append = "\n" . $_POST["chat"];
		fwrite($addFile, $append);
		fclose($addFile);
	}
}
?>

<script type="text/javascript">
function update() 
{
	$.get("chatlog.txt", function(data) 
	{
  		document.getElementById("chatbox").innerHTML=data;
  		alert(data);
	});

	setTimeout(update, 1000);
}

setTimeout(update, 1000);
</script>

<textarea rows="10" cols="30" readonly="readonly" id="chatbox">
<?php echo htmlentities($file); ?>
</textarea>

<form method="post">
Chat: <input type="text" name="chat"/>
<input name="submit" type="submit"/>
</form>

<div id="test">
hello. test.
</div>

</body>

</html>