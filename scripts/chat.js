var curMessageID = 0;
var updateTime = 100;
var updateCounter = 0;
var alertBox = document.getElementById("alert");
var alertBackground = document.getElementById("alertbackground");

$(document).keypress(function(e)
{
    if (e.which == 13 && document.getElementById("chatinput").value != "")
    {
        $.ajax({
            type: "POST",
            url: "chat/getName.php",
            data: "",
            success: function(message) {
                if (message == "0")
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
                else
                {
                    //alertBox.style.height = 200px;
                    //alertBox.style.width = 500px;
                    //alertBox.backgroundColor = #8b7d6b;
                    //alertBox.innerHTML="Please login to use this feature.";
                    //alert("Please login before posting!");
                }
            }
        });
    }
});

function update()
{
    $.ajax({
        type: "POST",
        url: "chat/findNew.php",
        data: "ID=" + curMessageID,
        success: function(message) {
            if (message != "nothing detected")
            {
                $.ajax({
                    type: "POST",
                    url: "chat/retrieve.php",
                    data: "ID=" + curMessageID + "&newID=" + message,
                    success: function(message2) {
                        var txt = $("textarea#chatbox");
                        txt.append(message2.replace(/\n\r?/g, '<br />'));
                        var elem = document.getElementById('chatbox');
                        elem.scrollTop = elem.scrollHeight;
                        updateCounter = 0;
                    }
                });
            }
            else
            {
                updateCounter++;
            }
            if (message != "nothing detected")
            {
                curMessageID = message;
            }
            if (updateCounter == 100)
            {
                updateTime = 5000;
            }
            else if (updateCounter == 0)
            {
                updateTime = 100;
            }
        }
    });

    setTimeout(update, updateTime);
}

setTimeout(update, updateTime);