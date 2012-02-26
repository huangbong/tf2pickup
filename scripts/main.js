(function($) {
    /* Common jQuery handles */
    var $alert, $login_box, $start_pug_box,
        $close_alert,
        $no_pugs, $pugs_container, $PUGListingTemplate;

    /* Cached data */
    var pugs_cache;

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

    /*
    var defaultPlayers = function(pug_type) {
        if (pug_type === 2) {
            // Highlander
            return [
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"},
                {avatar: "img/class_icons/med.png", class="empty"}
            ];
        }
        else {
            // 6s

        }
    } */

    var updatePUGListing = function() {
        var current_ids = {};
        $.each(pugs_cache, function() {
            /* If this PUG does not exist yet... */
            current_ids["pug_id_" + this.id] = true;
            if ($("#pug_id_" + this["id"]).size() === 0) {
                var html = $PUGListingTemplate.render(this);
                $pugs_container.append($(html));
            }
            else {
                /* PUG Listing already exists, update? */
            }
        });

        $(".pug").each(function () {
            if (!current_ids[this.id]) {
                $(this).remove();
            }
        });

        $no_pugs.toggle($("#pugs_container .pug").size() === 0);
        applyPUGFilter();
    }

    /* Will apply filter_options to currently displayed pugs */
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
        /* Various handles we want to keep a reference to */
        $pugs_container = $("#pugs_container");
        $no_pugs = $("#no_pugs");
        $PUGListingTemplate = $("#PUGListingTemplate");

        /* Alert box panels */
        $alert = $("#alert");
        $login_box = $("#login_box");
        $start_pug_box = $("#start_pug_box");

        /* Get initial PUG listing */
        $.ajax({
            type: "GET",
            url: "ajax/getPUGs.php",
            success: function(_data) {
                var data = JSON.parse(_data);

                pugs_cache = $.map(data, function(pug) {
                    var players_per_team = (pug["pug_type"] === "1")? 6:9;
                    var teams = [{players: []}, {players: []}];
                    var player_count = 0;


                    $.each(pug["players"], function() {
                        /* teams[+this["team"]].players.push({
                            avatar: this["avatar"],
                            id: this[id]
                        }); */

                        if (!this["empty"]) {
                            ++player_count;
                        }
                    });

                    return {
                        id: pug["id"],
                        region: pug["region"],
                        map: pug["map"],
                        name: pug["name"],
                        players_per_team: players_per_team,
                        max_players: 2*players_per_team,
                        player_count: player_count,
                        server_name: pug["servername"],
                        host_name: pug["hostname"] /*,
                        teams: teams */
                    };
                });
                $("#pugs_loading").hide();
                updatePUGListing();
            }
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
