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
        <script src="http://cdn.tf2pickup.com/jquery-mini.js"></script>
        <script src="scripts/jsrender.js"></script>
        <!--<script src="scripts/chat<?php if(isset($_GET["gc"])) {?>_gc<?php } ?>.js"></script>-->
        <script src="scripts/main.js"></script>
    </head>
    <body>
        <script id="PUGListingTemplate" type="text/x-jquery-tmpl">
            <div class="pug" id="pug_id_{{=id}}">
                <div class="pug_map" unselectable="on">
                    <div class="large_region region_{{=region}}"></div>
                    <img src="http://cdn.tf2pickup.com/maps/320/{{=map}}.jpg"
                         alt="badlands"
                         width="116"
                         height="64" unselectable="on" />
                </div>
                <div class="pug_details">
                    <span class="pug_name">{{=name}}</span>
                    <div class="pug_info">
                        {{=players_per_team}}v{{=players_per_team}} cp_badlands
                        <br/>
                        {{=server_name}}
                    </div>
                </div>
                <div class="pug_player_count">
                    {{=player_count}} / {{=max_players}}
                </div>
                <div class="pug_teams">
                    {{#each teams}}
                        <div class="pug_team">
                            {{#each players}}
                                {{#if empty}}
                                <div class="empty small_class class_{{=class_id}}"></div>
                                {{else}}
                                <img src="{{=avatar}}"
                                     id="{{=steamid}}" />
                                {{/if}}
                            {{/each}}
                        </div>
                    {{/each}}
                </div>
            </div>
        </script>

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
                    PUG Name:
                    <p><input type="text" id="new_pug_name" maxlength="150"/></p>
                    Server IP:
                    <p><input type="text" id="new_pug_ip" /></p>
                    Server Port:
                    <p><input type="text" id="new_pug_port" value="27015" /></p>
                    RCON:
                    <p><input type="password" id="new_pug_rcon" /></p>
                    PUG Password:
                    <p><input type="password" id="new_pug_password" /></p>
                    Game Type:
                    <select id="new_pug_type">
                        <option>Standard</option>
                        <option>Highlander</option>
                    </select>
                    <input type="button" id="launch_pug" value="Start PUG" />
                </div>
            </div>
        </div>

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
                    <div id="pug_list_header">
                        Filter:
                        <span id="filter_6s" filter="players_per_team=6" class="filter_button">
                            6v6
                        </span>
                        <span id="filter_9s" filter="players_per_team=9" class="filter_button">
                            9v9
                        </span>
                        <div id="filter_na" filter="region=na" class="small_region region_na filter_button"></div>
                        <div id="filter_eu" filter="region=eu" class="small_region region_eu filter_button"></div>
                        <div id="filter_au" filter="region=au" class="small_region region_au filter_button"></div>
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
                    <div id="pugs_container">
                    </div>
                </div>

                <div id="in_pug">
                    This panel will hold stuff for when a player is in a pug! For now just click here to go back to the lobby listing.
                </div>
            </div>
        </div>
        <div id="footer">
            &copy; 2012 TF2Pickup.com
        </div>
    </body>
</html>
