(function($) {
    /* Common jQuery handles */
    var $news, $stats, $pug_info,
        $alert, $login_box, $start_pug_box,
        $close_alert,
        $pugs_container, $PUGListingTemplate;

    /* Persistent data */
    var filter_options = {
        pug_type_1: true,
        pug_type_2: true,
        region_us: true,
        region_eu: false,
        game_mode_5cp: true,
        game_mode_ctf: true,
        game_mode_pl: true,
        game_mode_ad: true,
        game_mode_arena: false
    };

    /* Display news in */
    var displayNews = function($steam64) {
        $stats.hide();
        $pug_info.hide();
        $news.show();
    };

    var displayUserStats = function(steam64) {
        $stats.show();
        $pug_info.hide();
        $news.hide();
    };

    var displayPUGInfo = function(pug_id) {
        $stats.hide();
        $pug_info.show();
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
        $pug_info = $("#pug_info");
        $pugs_container = $("#pugs_container");
        $PUGListingTemplate = $("#PUGListingTemplate");

        /* Alert box panels */
        $alert = $("#alert");
        $login_box = $("#login_box");
        $start_pug_box = $("#start_pug_box");

        $.ajax({
            type: "GET",
            url: "ajax/getPUGs.php",
            success: function(_data) {
                var data = JSON.parse(_data);
                var params = $.map(data, function(server) {
                    return {
                        id: server["id"],
                        region: server["region"],
                        map: server["map"],
                        name: server["name"],
                        players_per_team: server["pug_type"] === "1"? 6:9,
                        total_players: server["pug_type"] === "1"? 12:18,
                        server_name: server["servername"],
                        host_name: server["hostname"]
                    };
                });
                var html = $PUGListingTemplate.render(params);
                $pugs_container.html(html);
            }
        });

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

        /* Open pug creation window */
        $("a#start_pug").click(function() {
            $login_box.hide();
            $start_pug_box.show();
            $alert.show();
        });

        $("div.close_alert").click(function() {
            $alert.hide();
        })
    });

})(jQuery);
