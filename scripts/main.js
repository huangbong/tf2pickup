(function($) {
    "use strict";

    /* Make this context accessible from window._pug for debugging */
    window._ctx = this;

    /* Common jQuery handles */
    var $alert
      , $no_pugs, $pugs_container
      , $PUGListingTemplate, $InPUGTeamsTemplate
      , $new_pug_ip, $server_preview
      , $in_pug_teams_container;

    /* PUG the player is viewing */
    var current_pug_id = -1;

    /* Persist.JS handle */
    var local_data;

    /* Map of panel name -> jquery handle for each panel in the alert box */
    var alert_panels = {};

    /* Timer(s) */
    var pugs_update_timer;

    /* Cached SteamIDs of friends */
    var friends_cache = [];

    /* Cached ip -> region mapping from server */
    var ip_region_cache = {};

    /* Cached pug data from server, maps id -> PUGListing */
    var pugs_cache = {};

    /* Selected filters, maps filter_name -> filter_functions */
    var filters = {};

    /* Slight fix for jsRender */
    $.views.converters({
        zeroFix: function (value) {return value || 0;}
    });

    /* Functions for centered alert box (for logins, starting pugs, etc)
     * (filled in on document load when the handle $alert is created) */
    var showAlert = function(desired_panel_name) {
        _.each(alert_panels, function($panel, panel_name) {
            $panel.toggle(panel_name === desired_panel_name);
        });
        $alert.show();
    };
    var hideAlert = function() {$alert.hide()};

    /* Creates a default team object for a given game mode */
    var makeDefaultTeam = function(pug_type, team_id) {
        var n = 0, classes;
        if (pug_type === 1)
            classes = [1, 1, 2, 2, 4, 7];
        else
            classes = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        return {
            team_id: team_id,
            players: $.map(classes, function(class_id) {
                return {
                    slot_id: ++n,
                    class_id: class_id, // Stringify so 0 renders correctly
                    empty: true,
                    avatar: null,
                    name: null,
                    steamid: null,
                    friend: false
                };
            })
        };
    };

    /* Validates an IP address given by the user and normalizes it to its
     * minimum possible representation (001.022.3.4 -> 1.2.3.4)
     * Returns false if the given IP is invalid
     */
    var simplifyIP = function(rawIP) {
        var octets = rawIP.split("."), invalid = false;
        if (octets.length !== 4) return false;

        octets = _.map(octets, function(octet) {
            octet = parseInt(octet, 10);
            if (_.isNaN(octet) || octet < 0 || octet > 255) invalid = true;
            return octet;
        });

        if (invalid)
            return false;
        else
            return octets.join(".");
    }

    /* Constructor for a PUG listing.  Accepts decoded JSON straight from
     * the server */
    var PUGListing = function(data) {
        this.load(data);
    };

    PUGListing.prototype.load = function(data) {
        var self = this
          , required_data = ["id", "region", "map", "name", "pug_type"
                           , "server_name", "host_name", "updated"
                           , "players", "started"];

        if (this.updated && data.updated) {
            if (this.updated < data.updated)
                this.needs_redisplay = true;
        }
        else
            this.needs_redisplay = true;

        $.each(required_data, function(idx, key) {
            self[key] = data[key];
        });

        this.started = this.started === "1";
        this.pug_type = +this.pug_type;
        this.players_per_team = (this.pug_type === 1)? 6:9;
        this.max_players = 2 * this.players_per_team ;
        this.player_count = 0;
        this.teams = [makeDefaultTeam(this.pug_type, 0)
                    , makeDefaultTeam(this.pug_type, 1)];


        $.each(this.players, function(idx, player) {
            var class_id = +player.class_id;
            if (class_id > 0) {
                $.each(self.teams[+player.team].players, function(idx, slot) {
                    if (slot.class_id === class_id && slot.empty) {
                        slot.empty = false;
                        slot.avatar = player.avatar;
                        slot.steamid = player.steam64;
                        slot.name = player.name;
                        slot.friend = _.indexOf(friends_cache, player.steam64) > -1;
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
            var $pug = $("#pug_id_" + id), $pug_name, html, $new_pug;

            if (pug.started && $pug.size() === 1) {
                $pug.animate({height: 0, opacity: 0, "border-width": 0, "margin-top": 0}).hide(0);
                delete pugs_cache[id];
                return;
            }

            current_ids["pug_id_" + id] = true;
            if (pug.needs_redisplay) {
                if ($pug.size() === 0) {
                    /* If this PUG does not exist yet... */
                    html = $PUGListingTemplate.render(pug);
                    $new_pug = $(html);
                    $new_pug.on("click", _.bind(enterPUG, $new_pug, id));
                    $new_pug.hide();
                    $pugs_container.append($new_pug);
                    $new_pug.fadeIn();
                }
                else {
                    /* PUG Listing already exists, update? */


                    /* Currently we just completely re-render the pug... but it
                     * would be preferbale to only update what we have to
                    $pug_name = $(".pug_name", $pug);
                    if ($pug_name.text() !== this.name) {
                        $pug_name.text(this.name);
                    } */

                    html = $PUGListingTemplate.render(pug);
                    $new_pug = $(html);
                    $new_pug.on("click", _.bind(enterPUG, $new_pug, id));
                    $pug.replaceWith($new_pug);
                }

                if (pug.id === current_pug_id) {
                    // Update the PUG if we're currently in it
                    renderCurrentPUG();
                    // and move it to the top of the listing
                    $pugs_container.append($pug);
                }
            }

            pug.needs_redisplay = false;
        });

        $(".pug").each(function () {
            var $this = $(this);
            if (!current_ids[this.id]) {
                // Remove element after animation has run
                setTimeout(_.bind($this.remove, $this), 500);
            }
        });

        $no_pugs.toggle($("#pugs_container .pug").size() === 0);

        applyPUGFilters();
    };

    /* Will apply filter_options to currently displayed pugs */
    var applyPUGFilters = function() {
        $.each(pugs_cache, function (idx, pug) {
            /* If none of the filters match, hide this pug listing */
            var show = !_.any(filters, function(f) { return f(pug); });
            $("#pug_id_" + pug.id).toggle(show);
        });

        /* Add a class to the first visible pug in the listing */
        $(".pug").removeClass("first_pug");
        $(".pug:visible:first").addClass("first_pug");
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

        if (filters[filter_string])
            delete filters[filter_string];
        else
            filters[filter_string] = makeFilter(filter_string);

        $this.toggleClass("filter_disabled", !!filters[filter_string]);
        applyPUGFilters();
    };

    /* Request PUG info from the server */
    var updatePUGs = function(initial) {
        /* If we were called manually, reset the current timer */
        if (pugs_update_timer) {
            clearTimeout(pugs_update_timer);
        }

        /* Build query string of id,last_update pairs */
        var data = "";
        if (!initial) {
            data = [];
            $.each(pugs_cache, function(key, pug) {
                data.push(pug.id + "," + pug.updated);
            });
            data = "pugs=" + data.join(";");
        }

        /* Send off ajax request */
        $.ajax({
            type: "GET",
            url: "ajax/getPUGs.php",
            data: data,
            success: receivePUGData,
            error: function() {
                $("#comm_error").show();
                pugs_update_timer = setTimeout(updatePUGs, 20000);
            }
        });
    };

    /* Receive PUG data from the server */
    var receivePUGData = function(_data) {
        var data = JSON.parse(_data);

        $.each(data, function(idx, pug) {
            if (pugs_cache.hasOwnProperty(""+pug.id))
                pugs_cache[pug.id].load(pug);
            else
                pugs_cache[pug.id] = new PUGListing(pug);
        });

        $("#pugs_loading").hide();
        updatePUGListing();

        /* Schedule next update */
        pugs_update_timer = setTimeout(updatePUGs, 2500);
    };

    /* Callback from "start pug" button. Reads data from the
     * create PUG form and sends ajax request */
    var createPUG = function() {
        // TODO client side sanity checks
        var data = {
            name: $("#new_pug_name").val(),
            pugtype: $("#new_pug_type").val(),
            map: $("#new_pug_map").val(),
            serverip: $("#new_pug_ip").val(),
            serverport: $("#new_pug_port").val(),
            rcon: $("#new_pug_rcon").val()
        };

        showAlert("creating_pug");
        return $.post("ajax/createPUG.php", data, onPUGCreated);
    };

    var onPUGCreated = function(data) {
        updatePUGs();
    };

    /* Get the region for an IP from the server */
    var fetchRegionPreview = _.debounce(function(ip) {
        $.ajax({
            type: "GET",
            url: "geoip/getRegion.php",
            data: "ip=" + ip,
            success: function(region) {
                ip_region_cache[ip] = region;
                updateRegionPreview();
            }
        });
    }, 500);

    /* Update the region preview flag when creating a server */
    var updateRegionPreview = function() {
        var ip = simplifyIP($new_pug_ip.val()), region = null;

        $.each($server_preview.attr('class').split(/\s+/), function () {
            if (this.indexOf("region_") === 0)
                $server_preview.removeClass(this);
        });

        if (!ip) return;
        if (ip_region_cache.hasOwnProperty(ip))
            region = ip_region_cache[ip];

        if (region === null)
            fetchRegionPreview(ip);
        else
            $server_preview.addClass("region_" + region);
    };

    var renderCurrentPUG = function() {
        var html = $InPUGTeamsTemplate.render(pugs_cache[current_pug_id]);
        $in_pug_teams_container.html(html);
    };

    var enterPUG = function(pug_id) {
        $("#lobby_listing").stop(true).animate({left: '-1050px'});
        $("#in_pug").stop(true)
                    .show()
                    .animate({left: '0px'}, function() {
                        var $pug = $("#pug_id_" + pug_id);
                        $pug.addClass("current_pug");
                        $pugs_container.prepend($pug);
                        applyPUGFilters();
                    });
        current_pug_id = pug_id;

        updatePUGs();
        renderCurrentPUG();
    };

    var leavePUG = function() {
        $("#lobby_listing").stop(true).animate({left: '0px'});
        $("#in_pug").stop(true).animate({left: '1100px'}).hide(0);

        $("#pug_id_" + current_pug_id).removeClass("current_pug");
        //current_pug_id = -1;
    };

    /* Fetch friend data from the server or local data store */
    var fetchFriends = function() {
        // TODO: Provide an option to invalidate friends data,
        // and/or do it automatically on login.
        friends_cache = local_data.get("friends_cache");
        if (!_.isString(friends_cache)) {
            return $.get("ajax/getFriends.php", function(friends) {
                friends_cache = JSON.parse(friends);
                local_data.set("friends_cache", friends_cache);
            });
        }
        else {
            friends_cache = friends_cache.split(",");
        }
    };

    /* On page load - setup keybinds and get handles
     * to common page elements */
    $(function() {
        /* Various handles we want to keep a reference to */
        $pugs_container = $("#pugs_container");
        $in_pug_teams_container = $("#in_pug_teams_container");
        $no_pugs = $("#no_pugs");
        $PUGListingTemplate = $("#PUGListingTemplate");
        $InPUGTeamsTemplate = $("#InPUGTeamsTemplate");

        /* Alert box panels */
        $alert = $("#alert");
        alert_panels["login"] = $("#login_box");
        alert_panels["start_pug"] = $("#start_pug_box");
        alert_panels["settings"] = $("#settings_box");
        alert_panels["stats"] = $("#stats_box");
        alert_panels["creating_pug"] = $("#creating_pug_box");

        $new_pug_ip = $("#new_pug_ip");
        $server_preview = $("#new_pug_region_preview");

        /* Persist.JS data store */
        local_data = new Persist.Store("tf2pickup_data");

        /* Get initial data */
        fetchFriends();
        updatePUGs(true);

        /* When clicking on filter icons... */
        $("div.page_header .filter_button").click(toggleFilter);

        /* Event handlers */
        $("a#start_pug").click(_.bind(showAlert, this, "start_pug"));
        $("a#open_settings").click(_.bind(showAlert, this, "settings"));
        $("a#open_stats").click(_.bind(showAlert, this, "stats"));
        $("div.close_alert").click(hideAlert);

        $("#launch_pug").click(createPUG);
        $("#leave_pug").click(leavePUG);

        $("#in_pug_teams_container").on("click", ".in_pug_player");

        /* Update region preview of a pug's ip */
        $new_pug_ip.on("keyup keydown change", updateRegionPreview);
        $server_preview.on("click", function() {$new_pug_ip.focus()});

        /* Default a pug's port to 27015. Using this instead of an HMTL
         * placeholder attribute so the value gets sent */
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
