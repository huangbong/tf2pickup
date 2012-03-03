(function($) {
    "use strict";

    /* Common jQuery handles */
    var $alert, $login_box, $start_pug_box
      , $close_alert
      , $no_pugs, $pugs_container, $PUGListingTemplate;

    /* Cached data from server, maps id -> PUGListing */
    var pugs_cache = {};

    /* Selected filters, maps filter_name -> filter_functions */
    var filters = {};

    /* Functions for centered alert box (for logins, starting pugs, etc)
     * (filled in on document load when the handle $alert is created) */
    var showAlert = function() {$alert.show();}, hideAlert = function() {$alert.hide()};

    var makeDefaultTeam = function(pug_type) {
        if (pug_type === 1) {
            var classes = [
                1, 1, 2, 2, 4, 7
            ];
        }
        else {
            var classes = [
                1, 2, 3, 4, 5, 6, 7, 8, 9
            ];
        }

        return {
            players: $.map(classes, function(class_id) {
                return {
                    class_id: class_id,
                    empty: true,
                    avatar: null,
                    steamid: null
                };
            })
        };
    };

    /* Constructor for a PUG listing.  Accepts decoded JSON straight from
     * the server */
    var PUGListing = function(data) {
        this.load(data);
    };

    PUGListing.prototype.load = function(data) {
        var self = this
          , required_data = ["id", "region", "map", "name", "pug_type"
                           , "server_name", "host_name", "updated", "players"];


        $.each(required_data, function(idx, key) {
            self[key] = data[key];
        });

        this.needs_redisplay = true;
        this.pug_type = +this.pug_type;
        this.players_per_team = (this.pug_type === 1)? 6:9;
        this.max_players = 2 * this.players_per_team ;
        this.player_count = 0;
        this.teams = [makeDefaultTeam(this.pug_type)
                    , makeDefaultTeam(this.pug_type)];


        $.each(this.players, function(idx, player) {
            var class_id = +player.class_id;
            if (class_id > 0) {
                $.each(self.teams[+player.team].players, function(idx, slot) {
                    if (slot.class_id === class_id && slot.empty) {
                        slot.empty = false;
                        slot.avatar = player.avatar;
                        slot.steamid = player.steam64;
                        return false; // break from the .each
                    }
                });
            }

            ++self.player_count;
        });
    }

    /* Updates the currently rendered PUG listing with data from pug_cache */
    var updatePUGListing = function() {
        var current_ids = {};
        $.each(pugs_cache, function(id, pug) {
            var $pug = $("#pug_id_" + id), $pug_name;
            current_ids["pug_id_" + id] = true;

            /* If this PUG does not exist yet... */
            if ($pug.size() === 0) {
                var html = $PUGListingTemplate.render(pug);
                $pugs_container.append($(html));
            }
            else {
                /* PUG Listing already exists, update?
                $pug_name = $(".pug_name", $pug);
                if ($pug_name.text() !== this.name) {
                    $pug_name.text(this.name);
                }
                */

                if (pug.needs_redisplay) {
                    var html = $PUGListingTemplate.render(pug);
                    $pug.replaceWith($(html));
                }
            }

            pug.needs_redisplay = false;
        });

        $(".pug").each(function () {
            if (!current_ids[this.id]) {
                $(this).remove();
            }
        });

        $no_pugs.toggle($("#pugs_container .pug").size() === 0);
        applyPUGFilters();
    };

    /* Will apply filter_options to currently displayed pugs */
    var applyPUGFilters = function() {
        $.each(pugs_cache, function (idx, pug) {
            /* If any of the filters match, hide this pug listing */
            var show = true;

            $.each(filters, function(filter_name, filter) {
                show = show && !filter(pug);
            });

            $("#pug_id_" + pug.id).toggle(show);
        });
    };

    /* Creates a filter function from  */
    var makeFilter = function(filter_string) {
        var parts = filter_string.split("=");
        if (parts.length !== 2) {
            return function() {return true;};
        }

        var attr_name = parts[0], reject_val = parts[1];
        return function(pug) {
            /* (Using == on purpose) */
            return (pug[attr_name] == reject_val);
        };
    };

    /* Callback when filters are clicked */
    var toggleFilter = function() {
        var $this = $(this);
        var filter_string = $this.attr("filter");

        if (filters[filter_string]) {
            delete filters[filter_string];
        } else {
            filters[filter_string] = makeFilter(filter_string);
        }

        $this.toggleClass("filter_disabled", !!filters[filter_string]);
        applyPUGFilters();
    };

    /* Request PUG info from the server */
    var updatePUGs = function(initial) {
        var data = "";
        if (!initial) {
            data = [];
            $.each(pugs_cache, function(key, pug) {
                data.push(pug.id + "," + pug.updated);
            });
            data = "pugs=" + data.join(";");
        }

        $.ajax({
            type: "GET",
            url: "ajax/getPUGs.php",
            data: data,
            success: receivePUGData,
            error: function() {
                $("#comm_error").show();
                setTimeout(updatePUGs, 20000);
            }
        });
    };

    /* Receive PUG data from the server */
    var receivePUGData = function(_data) {
        var data = JSON.parse(_data);

        $.each(data, function(idx, pug) {
            if (pugs_cache.hasOwnProperty(""+pug.id)) {
                pugs_cache[pug.id].load(pug);
            }
            else {
                pugs_cache[pug.id] = new PUGListing(pug);
            }
        });
        $("#pugs_loading").hide();
        updatePUGListing();

        /* Schedule next update */
        setTimeout(updatePUGs, 2500);
    };

    /* Callback from "start pug" button. Reads data from the
     * create PUG form and sends ajax request */
    var createPUG = function() {
        // TODO
        hideAlert();
    };

    /* On page load - setup keybinds and get handles
     * to common page elements */
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
        updatePUGs(true);

        /* When clicking on filter icons... */
        $("div#pug_list_header .filter_button").click(toggleFilter);

        /* Open pug creation window */
        $("a#start_pug").click(function() {
            $login_box.hide();
            $start_pug_box.show();
            showAlert();
        });

        $(document).on("click", ".pug", function() {
            $("#lobby_listing").animate({left: '-1050px'});
            $("#in_pug").show().animate({left: '0px'});
        });

        $("#in_pug").on("click", function() {
            $("#lobby_listing").animate({left: '0px'});
            $("#in_pug").animate({left: '1100px'});
        });

        $("div.close_alert").click(hideAlert);

        $("#launch_pug").click(createPUG);

        $("#new_pug_port").focus(function() {
            if ($(this).val() === "27015") {
                $(this).val("");
            }
        }).blur(function() {
            if ($(this).val() === "") {
                $(this).val("27015");
            }
        });
    });

})(jQuery);
