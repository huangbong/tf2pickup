<?php
require_once 'config.php';
require_once 'session.php';
require_once 'bancheck.php';
require_once 'browsercheck.php';
require_once 'openid.php';
$logged_in = isset($_SESSION['steam64']);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>TF2Pickup - Play Team Fortress 2 Pickup Games</title>
        <link rel="shortcut icon" type="image/x-icon" href="http://cdn.tf2pickup.com/favicon.ico"/>
        <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script src="scripts/underscore-min.js"></script>
        <script src="scripts/jsrender.js"></script>
        <!--<script src="scripts/chat<?php if(isset($_GET["gc"])) {?>_gc<?php } ?>.js"></script>-->
        <script src="scripts/main.js"></script>
    </head>
    <body>

        <!-- JSRender templates -->
        <script id="PUGListingTemplate" type="text/x-jsrender">
            <div class="pug" id="pug_id_{{zeroFix:id}}">
                <div class="pug_map" unselectable="on">
                    <div class="large_region region_{{:region}}"></div>
                    <img src="http://cdn.tf2pickup.com/maps/320/{{:map}}.jpg"
                         alt="{{:map}}"
                         title="{{:map}}"
                         width="116"
                         height="64" unselectable="on" />
                </div>
                <div class="pug_details">
                    <span class="pug_name">{{:name}}</span>
                    <div class="pug_info">
                        {{zeroFix:players_per_team}}v{{zeroFix:players_per_team}} {{:map}}
                        <br/>
                        {{:server_name}}
                    </div>
                </div>
                <div class="pug_player_count">
                    {{zeroFix:player_count}}/{{zeroFix:max_players}}
                </div>
                <div class="pug_teams">
                    {{for teams ~pug_id=id tmpl="#PUGTeamTemplate"/}}
                </div>
            </div>
        </script>

        <script id="PUGTeamTemplate" type="text/x-jsrender">
            <div class="pug_team">
                {{for players ~team_id=team_id tmpl="#PUGSmallSlotTemplate"/}}
            </div>
        </script>

        <script id="PUGSmallSlotTemplate" type="text/x-jsrender">
            <span id="slot_{{zeroFix:~pug_id}}_{{zeroFix:~team_id}}_{{zeroFix:slot_id}}">
                {{if empty}}
                    <div class="empty small_class class_{{zeroFix:class_id}}"></div>
                {{else}}
                    <img src="{{:avatar}}"
                         {{if friend}}
                         class="friend"
                         {{/if}}
                         id="player_{{:steamid}}" />
                {{/if}}
            </span>
        </script>

        <script id="InPUGTeamsTemplate" type="text/x-jsrender">
            {{for teams}}
                <div class="in_pug_team">
                    <div class="in_pug_team_header team_{{:team_id + 1}}">
                        {{if team_id === 0}}BLU{{else}}RED{{/if}}
                    </div>
                    {{for players}}
                    <div class="in_pug_player">
                        <div class="med_class class_{{:class_id}}"></div>
                        {{if !empty}}
                            <img src="{{:avatar}}"
                                 alt="{{:name}}"
                                 title="{{:name}}"
                                 width="32"
                                 height="32"
                                 {{if friend}}
                                 class="friend"
                                 {{/if}}
                                 id="player_{{:steamid}}" />
                            <div class="player_name">{{:name}}</div>
                        {{/if}}
                    </div>
                    {{/for}}
                </div>
            {{/for}}
        </script>

        <!-- Alert box -->
        <div id="alert"<?php if ($logged_in) { ?> class="hidden"<?php } ?>>
            <div id="alert_contents">
                <div id="login_box">
                    <h2>Please login with Steam to<br />use this site.</h2>
                    <a href="<?php echo SteamSignIn::genUrl(); ?>">
                        <img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_border.png"
                             alt="steam login"/>
                    </a>
                </div>

                <div id="start_pug_box">
                    <div class="close_alert">X</div>

                    <h2><div id="start_pug_box_title">Create a PUG</div></h2>
                    PUG Name:<br />
                    <!-- TODO: REPLACE TESTING VALUES! -->
                    <input type="text" id="new_pug_name" maxlength="150" value="test"/><br />
                    Map Name:<br />
                    <input type="text" id="new_pug_map" maxlength="150" value="pl_swiftwater"/><br />
                    Server IP:<br /><!--
                 --><div id="new_pug_ip_wrapper"><!--
                        --><input type="text" id="new_pug_ip" maxlength="15" value="70.42.74.154" /><!--
                        --><div id="new_pug_region_preview" class="small_region"></div><!--
                 --></div><!--
                 --><br />
                    Server Port:<br />
                    <input type="text" id="new_pug_port" value="27016" /><br />
                    RCON:<br />
                    <input type="password" id="new_pug_rcon" value="reddit" /><br />
                    PUG Password:<br />
                    <input type="password" id="new_pug_password" /><br />
                    Game Type:<br />
                    <select id="new_pug_type">
                        <option value="1">Standard</option>
                        <option value="2">Highlander</option>
                    </select>
                    <input type="button" id="launch_pug" value="Start PUG" />
                </div>

                <div id="settings_box">
                    <div class="close_alert">X</div>

                    TODO: Put various settings and stuff here
                </div>

                <div id="creating_pug_box">
                    <div class="close_alert">X</div>

                    <img src="http://cdn.tf2pickup.com/ajax-loader.gif" />
                    <br />
                    Creating PUG
                </div>

                <div id="stats_box">
                    <div class="close_alert">X</div>
                    TODO: Put various stats and stuff here
                </div>
            </div>
        </div>

        <!-- Main page body -->
        <div id="wrapper">
            <div id="tf2_icon"></div>
            <div id="header">
                <div id="logo"></div>
<?php
if ($logged_in) {
?>
                <div id="steam">
                    <img src="<?php echo $_SESSION['avatar']; ?>" alt="avatar" width="30" height="30" />
                    <?php echo $_SESSION['username']; ?> ||
                    <a href="#stats" id="open_stats">stats</a>
                  - <a href="#settings" id="open_settings">settings</a>
                  - <a href="logout.php">logout</a>
                </div><?php
} ?>
            </div>

            <div id="pages_wrapper">

                <div id="lobby_listing">
                    <div class="page_header">
                        Filter:
                        <span id="filter_6s" filter="players_per_team=6" class="filter_button">
                            6v6
                        </span>
                        <span id="filter_9s" filter="players_per_team=9" class="filter_button">
                            9v9
                        </span>
                        <div id="filter_na" filter="region=na"
                             class="small_region region_na filter_button"></div>
                        <div id="filter_eu" filter="region=eu"
                             class="small_region region_eu filter_button"></div>
                        <div id="filter_au" filter="region=au"
                             class="small_region region_au filter_button"></div>
                        <a href="#" id="start_pug">
                            Start PUG
                        </a>
                    </div>
                    <div id="no_pugs">
                        No PUGs are open!
                    </div>
                    <div id="comm_error">
                        Error retreiving servers. Automatically retrying.
                    </div>
                    <div id="pugs_loading">
                        <img src="http://cdn.tf2pickup.com/ajax-loader.gif" />
                    </div>
                    <div class="pugs_scrollbar_fix">
                        <div id="pugs_container"></div>
                    </div>
                </div>

                <div id="in_pug">
                    <div class="page_header">
                        <span id="leave_pug">
                            Leave PUG
                        </span>
                    </div>
                    <div class="pugs_scrollbar_fix">
                        <div id="in_pug_teams_container"></div>
                    </div>
                </div>

            </div>

        </div>
        <div id="footer">
            &copy; 2012 TF2Pickup.com
        </div>
    </body>
</html>
