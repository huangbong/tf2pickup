(function($) {
  "use strict";

  /* Socket.IO */
  var socket;

  /* Extract data passed in from server in page source */
  var logged_in = TF2PICKUP_DATA.logged_in
    , user_data = TF2PICKUP_DATA.user_data;

  var utils = TF2PICKUP_UTILITY;

  /* Common jQuery handles */
  var $alert
    , $no_pugs, $pugs_container
    , $PUGListingTemplate, $InPUGTeamsTemplate
    , $new_pug_ip, $server_preview
    , $in_pug_teams_container
    , $loading_status
    , $create_pug_error;

  /* PUG the player is viewing */
  var current_pug_id = -1;

  /* Persist.JS handle */
  var local_data;

  /* Map of panel name -> jquery handle for each panel in the alert box */
  var alert_panels = {};

  /* Timer(s) */
  var pugs_update_timer;

  /* Cached SteamIDs of friends */
  var friends_cache = user_data.friends || [];

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

  /* Constructor for a PUG listing.  Accepts decoded JSON straight from
   * the server */
  var PUGListing = function(data) {
    this.load(data);
  };

  PUGListing.prototype.load = function(data) {
    var self = this
      , required_data = ["id", "region", "map", "name", "pug_type"
                         , "server_name", "host_name"
                         , "players", "started"];

    this.needs_redisplay = false;

    $.each(required_data, function(idx, key) {
      if (self[key] !== data[key])
        self.needs_redisplay = true;
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

      // Remove pugs that have started
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
          // PUG Listing already exists, update?
          // Currently we just completely re-render the pug... but it
          // would be preferbale to only update what we have to
          /* $pug_name = $(".pug_name", $pug);
          if ($pug_name.text() !== this.name) {
              $pug_name.text(this.name);
            } */
          html = $PUGListingTemplate.render(pug);
          $new_pug = $(html);
          $new_pug.on("click", _.bind(enterPUG, $new_pug, id));
          $pug.replaceWith($new_pug);
        }

        if (pug.id === current_pug_id) {
          // Update the PUG we're currently in and move
          // it to the top of the listng
          renderCurrentPUG();
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
    _.each(pugs_cache, function (pug, id) {
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

  /* Receive PUG data from the server */
  var receivePUGData = function(data) {
    _.each(data, function(pug, id) {
      if (pugs_cache.hasOwnProperty("" + id))
        pugs_cache[id].load(pug);
      else
        pugs_cache[id] = new PUGListing(pug);
    });

    $("#pugs_loading").hide();
    updatePUGListing();
  };

  /* Callback from "start pug" button. Reads data from the
   * create PUG form and sends ajax request */
  var createPUG = function() {
    // TODO client side sanity checks
    socket.emit('create pug', {
      name: $("#new_pug_name").val(),
      type: $("#new_pug_type").val(),
      map: $("#new_pug_map").val(),
      ip: $("#new_pug_ip").val(),
      port: $("#new_pug_port").val(),
      rcon: $("#new_pug_rcon").val()
    });

    showAlert("creating_pug");
  };

  var onPUGCreated = function(data) {
  };

  /* Get the region for an IP from the server */
  var fetchRegionPreview = _.debounce(function(ip) {
    socket.emit('region', ip);
  }, 500);

  /* Update the region preview flag when creating a server */
  var updateRegionPreview = function() {
      var ip = utils.simplifyIP($new_pug_ip.val()), region = null;

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

    renderCurrentPUG();
    hideAlert();
  };

  var leavePUG = function() {
      $("#lobby_listing").stop(true).animate({left: '0px'});
      $("#in_pug").stop(true).animate({left: '1100px'}).hide(0);

      $("#pug_id_" + current_pug_id).removeClass("current_pug");
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
    alert_panels["disconnected"] = $("#disconnected");
    alert_panels["create_pug_error"] = $("#create_pug_error");

    /* Error label: */
    $create_pug_error = $("p", alert_panels["create_pug_error"]);

    $new_pug_ip = $("#new_pug_ip");
    $server_preview = $("#new_pug_region_preview");

    /* Error and info panels */

    $loading_status = $("#loading_status");

    /* Persist.JS data store */
    local_data = new Persist.Store("tf2pickup_data");

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

    socket = io.connect();

    /* Connection events */
    socket.on('disconnect', function() { showAlert('disconnected'); });
    socket.on('reconnect', function()  { console.log("Reconnected?!"); });
    socket.on('connect', function() {
      $loading_status.text('Connected, Loading Pugs...');
    });

    /* Our custom events */
    socket.on('pug', receivePUGData);
    socket.on('pug created', enterPUG);

    socket.on('region', function(data) {
      ip_region_cache[data.ip] = data.region;
      updateRegionPreview();
    });

    socket.on('create pug error', function(err) {
      showAlert('create_pug_error');
      $create_pug_error.text(err);
    });

  });

})(jQuery);