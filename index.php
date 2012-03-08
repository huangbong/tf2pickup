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
            <div class="pug" id="pug_id_{{:id}}">
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
                        {{:players_per_team}}v{{:players_per_team}} {{:map}}
                        <br/>
                        {{:server_name}}
                    </div>
                </div>
                <div class="pug_player_count">
                    {{:player_count}}/{{:max_players}}
                </div>
                <div class="pug_teams">
                    {{for teams ~pug_id=id tmpl="#PUGTeamTemplate"/}}
                </div>
            </div>
        </script>

        <script id="PUGTeamTemplate" type="text/x-jsrender">
            <div class="pug_team">
                {{for players ~team_id=team_id tmpl="#PUGSlotTemplate"/}}
            </div>
        </script>

        <script id="PUGSlotTemplate" type="text/x-jsrender">
            <span id="slot_{{:~pug_id}}_{{:~team_id}}_{{:slot_id}}">
                {{if empty}}
                    <div class="empty small_class class_{{:class_id}}"></div>
                {{else}}
                    <img src="{{:avatar}}"
                         id="player_{{:steamid}}" />
                {{/if}}
            </span>
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
                    <input type="text" id="new_pug_name" maxlength="150"/><br />
                    Server IP:<br /><!--
                 --><div id="new_pug_ip_wrapper"><!--
                        --><input type="text" id="new_pug_ip" maxlength="15" /><!--
                        --><div id="new_pug_region_preview" class="small_region"></div><!--
                 --></div><!--
                 --><br />
                    Server Port:<br />
                    <input type="text" id="new_pug_port" value="27015" /><br />
                    RCON:<br />
                    <input type="password" id="new_pug_rcon" /><br />
                    PUG Password:<br />
                    <input type="password" id="new_pug_password" /><br />
                    Game Type:<br />
                    <select id="new_pug_type">
                        <option>Standard</option>
                        <option>Highlander</option>
                    </select>
                    <input type="button" id="launch_pug" value="Start PUG" />
                </div>

                <div id="settings_box">
                    <div class="close_alert">X</div>

                    TODO: Put various settings and stuff here
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
                    <a href="/stats" target="_self">stats</a>
                  - <a href="/settings" target="_self">settings</a>
                  - <a href="logout.php" target="_self">logout</a>
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
                    <div id="pugs_scrollbar_fix">
                        <div id="pugs_container">
                        </div>
                    </div>
                </div>

                <div id="in_pug">
                    <div class="page_header">
                        <span id="leave_pug">
                            Leave PUG
                        </span>
                    </div>
                    <div id="in_pug_players_list">
                        <div class="in_pug_team">
                            <div class="in_pug_team_header team_1">RED</div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                        </div>
                        <div class="in_pug_team">
                            <div class="in_pug_team_header team_2">BLU</div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                            <div class="in_pug_player">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <div id="footer">
            &copy; 2012 TF2Pickup.com
        </div>
    </body>
</html>
