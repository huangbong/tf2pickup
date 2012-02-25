(function($) {
    /* Common jQuery handles */
    var $news, $stats, $lobby_info;

    var log = function() {
        if (console) {
            console.log.apply(console, $.makeArray(arguments));
        }
    }


    /* Display news in */
    var displayNews = function($steam64) {
        log("Displaying news");

        $stats.hide();
        $lobby_info.hide();
        $news.show();
    };

    var displayUserStats = function(steam64) {
        log("Displaying stats of: " + steam64);

        $stats.show();
        $lobby_info.hide();
        $news.hide();
    };

    var displayLobbyInfo = function(lobby_id) {
        log("Displaying stats of: " + lobby_id);

        $stats.hide();
        $lobby_info.show();
        $news.hide();
    };


    /* On page load - setup keybinds and get handles to common page elements */
    $(function() {
        /* Various information panels */
        $news = $("#news");
        $stats = $("#player_stats");
        $lobby_info = $("#lobby_info");

        /* when hovering over player icons */
        $("img.mini_player").mouseover(function(e) {
            displayUserStats($(this).attr("steamid"));
            e.stopPropagation()
        });

        $(".pug").mouseover(function(e) {
            displayLobbyInfo($(this).attr("pugid"));
            e.stopPropagation()
        });

        $("body").mouseover(function(e) {
            displayNews();
            e.stopPropagation()
        });
    });

})(jQuery);
