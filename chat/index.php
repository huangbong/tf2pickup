<html>
<head>
<title>TF2 Pickup Chat</title>

<script src="http://code.jquery.com/jquery-latest.js"></script>

<style type="text/css">
#chatbox
{
	resize:none;
	margin: 0; 
	padding: 0;
}
</style>

</head>
<body>

<script type="text/javascript">
$(document).keypress(function(e)
{
	if (e.which == 13 && document.getElementById("chat").value != "")
	{
		var dataString = "msg=" + encodeURIComponent(document.getElementById("chat").value);

		$.ajax({
			type: "POST",
			url: "post.php",
			data: dataString,
			success: function() {
				alert(dataString);
			}
		});
	}
});


function update() 
{
	$.get("chatlog.txt", function(data) 
	{
  		document.getElementById("chatbox").innerHTML=data;
	});
	var elem = document.getElementById("chatbox");
	elem.scrollTop = elem.scrollHeight;

	setTimeout(update, 100);
}
setTimeout(update, 100);
</script>

<textarea rows="10" cols="30" readonly="readonly" id="chatbox">
</textarea></br>

<input type="text" name="chat" id="chat"/>

</body>

</html>