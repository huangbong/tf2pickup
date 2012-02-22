<script type="text/javascript">
//<![CDATA[
$(document).keypress(function(e)
{
	if (e.which == 13 && document.getElementById("chatinput").value != "")
	{
		var dataString = "msg=" + encodeURIComponent(document.getElementById("chatinput").value);
		document.getElementById("chatinput").value="";

		$.ajax({
			type: "POST",
			url: "chat/post.php",
			data: dataString,
			success: function(message) {
			}
		});
	}
});


function update() 
{
	$.ajax({
		type: "POST",
		url: "chat/retrieve.php",
		data: ""
	}).done(function(msg) {
		if (msg != document.getElementById("chatbox").value)
		{
			var txt = $("textarea#chatbox");
			txt.val(msg);
			//document.getElementById("chatbox").innerHTML = msg;
			var elem = document.getElementById('chatbox');  
    		elem.scrollTop = elem.scrollHeight;
		}	
	});

	setTimeout(update, 100);
}

setTimeout(update, 100);
</script>

<textarea rows="10" cols="30" readonly="readonly" id="chatbox">
</textarea><br />

<input type="text" name="chat" id="chatinput" autocomplete="off"/>
