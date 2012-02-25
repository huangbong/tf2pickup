$(function() {
    var curId = false,
        timer,
        $chatbox = $("#chatbox"),
        $chatinput = $("#chatinput"),
        onSuccess = function(_data) {
            var data = JSON.parse(_data);
            $.each(data, function(index, val) {
                curId = val["id"];
                $chatbox.append(val["time"] + " "
                              + val["username"] + ": "
                              + val["message"] + "\n");
            });
            timer = setTimeout(update, 2500);
            $chatbox.scrollTop(999999);
        },
        update = function() {
            var data = {}
            if (curId !== false)
                data["id"] = curId;

            // Check for updates
            $.ajax({
                type: "GET",
                url: "chat/chat_gc.php",
                data: data,
                success: onSuccess
            });
        },
        post = function(message) {
            var data = {msg: message}
            if (curId !== false)
                data["id"] = curId;

            // Submit a new message
            $.ajax({
                type: "POST",
                url: "chat/chat_gc.php",
                data: data,
                success: onSuccess
            })
        };

    $chatinput.keypress(function(e) {
       var val = $chatinput.val();
       if (e.which == 13 && $.trim(val).length > 0) {
           // Cancel current update timer
           clearTimeout(timer);

           // Post message (which also updates the chatbox
           // immediately)
           post(val);

           // Clear input
           $chatinput.val("");
       }
    });

    update();
});