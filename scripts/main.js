(function($) {
    /* Common jQuery handles */
    var $news, $stats, $lobby_info,
        $alert, $login_box, $start_lobby_box,
        $close_alert;

    /* Persistent data */
    var filter_options = {
        pug_type_1: true,
        pug_type_2: true,
        region_us: true,
        region_eu: false
    };

    /* Display news in */
    var displayNews = function($steam64) {
        $stats.hide();
        $lobby_info.hide();
        $news.show();
    };

    var displayUserStats = function(steam64) {
        $stats.show();
        $lobby_info.hide();
        $news.hide();
    };

    var displayPUGInfo = function(lobby_id) {
        $stats.hide();
        $lobby_info.show();
        $news.hide();
    };

    /* Will apply filter_options to current lobbies */
    var applyPUGFilter = function() {
        // TODO...
    };

    var toggleFilter = function() {
        var $this = $(this), filter = $this.attr("filter");
        filter_options[filter] = !filter_options[filter];
        $this.toggleClass("filter_disabled", !filter_options[filter]);
        applyPUGFilter();
    };

    /* On page load - setup keybinds and get handles to common page elements */
    $(function() {
        /* Various information panels */
        $news = $("#news");
        $stats = $("#player_stats");
        $lobby_info = $("#lobby_info");

        /* Alert box panels */
        $alert = $("#alert");
        $login_box = $("#login_box");
        $start_lobby_box = $("#start_lobby_box");


        /* when hovering over player icons */
        $("img.mini_player").mouseover(function(e) {
            displayUserStats($(this).attr("steamid"));
            e.stopPropagation()
        });

        $(".pug").mouseover(function(e) {
            displayPUGInfo($(this).attr("pugid"));
            e.stopPropagation()
        });

        $("body").mouseover(function(e) {
            displayNews();
            e.stopPropagation()
        });

        /* When clicking on filter icons... */
        $("div#pug_list_header span").click(toggleFilter);

        /* Open lobby creation window */
        $("a#start_lobby").click(function() {
            $login_box.hide();
            $start_lobby_box.show();
            $alert.show();
        });

        $("div.close_alert").click(function() {
            $alert.hide();
        })
    });

})(jQuery);
