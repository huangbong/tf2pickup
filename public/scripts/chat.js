var curMessageID = 0;

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

        }
    });

    setTimeout(update, 300);
}

setTimeout(update, 300);
