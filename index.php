<?php
require_once 'config.php';
require_once 'session.php';
require_once 'bancheck.php';
require_once 'openid.php';

$logged_in = isset($_SESSION['steam64']);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>TF2Pickup - Play Team Fortress 2 Pickup Games</title>
        <link rel="shortcut icon" type="image/x-icon" href="http://cdn.tf2pickup.com/favicon.ico"/>
        <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <script src="scripts/jsrender.js"></script>
        <script src="scripts/chat<?php if(isset($_GET["gc"])) {?>_gc<?php } ?>.js"></script>
        <script src="scripts/main.js"></script>
    </head>
    <body>
        <script id="PUGListingTemplate" type="text/x-jquery-tmpl">
            <div class="pug" id="pug_id_{{=id}}">
                <div class="pug_map">
                    <img src="img/{{=region}}.png"
                         width="25px"
                         height="65px" />
                    <img src="http://cdn.tf2pickup.com/maps/320/{{=map}}.jpg"
                         alt="badlands"
                         width="116"
                         height="65" />
                </div>
                <div class="pug_title">
                    {{=name}}
                    <div class="pug_info">
                        {{=players_per_team}}v{{=players_per_team}} cp_badlands
                    </div>
                </div>
                <div class="pug_teams">
<?php /*            {{#each teams}}
                        <div class="pug_team team_1">
                            {{#each players}}
                                <img src="{{=avatar}}"
                                     id="{{=id}}"
                                     class="{{=class}}" />
                            {{/each}} */ ?>

                        <div class="pug_team">
    <img src="img/class_icons/scout.png" class="empty" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/4f/4fdc405b2b955056cbc8e0aae4ef0c7a3cf98105.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/5c/5c237bb671fca4de02a4aee1008a5f81bdf77e3c.jpg" class="mini_player" />
    <img src="img/class_icons/demo.png" class="empty" />
    <img src="img/class_icons/heavy.png" class="empty" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/b3/b37b7921857d138a42a4be6ac56ca50d9e689abd.jpg" class="mini_player friend" />
    <img src="img/class_icons/medic.png" class="empty" />
    <img src="img/class_icons/sniper.png" class="empty" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/73/73adc812c1cbe28b0d284522a248debf0a021a87.jpg" class="mini_player" />
                        </div>
                        <div class="pug_team">
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/8e/8e4b419bd8ce849dd919d8317ee374082138c92a.jpg" steamid="steam54" class="mini_player friend" />
    <img src="img/class_icons/soldier.png" class="empty" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/72/72f78b4c8cc1f62323f8a33f6d53e27db57c2252.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/8b/8bf9d62ba0e6e1c43630da0f9b905bb82641c117.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/7f/7f7cd93b9d8fe1663f8fe13225b38247c6f94364.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/69/696931177080ab219edaddda48de07f30dcba561.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/7f/7f7cd93b9d8fe1663f8fe13225b38247c6f94364.jpg" class="mini_player" />
    <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/a8/a8d7d3d1762464bae43274cb1ab0d42d27481861.jpg" class="mini_player" />
    <img src="img/class_icons/spy.png" class="empty" />
                        </div>
                </div>
                <div class="pug_player_count">
                    {{=player_count}} / {{=max_players}}
                </div>
                <div class="pug_server_info">
                    <table>
                        <tr>
                            <td>Server:</td>
                            <td>{{=server_name}}</td>
                        </tr>
                        <tr>
                            <td>Host:</td>
                            <td>{{=host_name}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </script>

        <div id="alert"<?php if ($logged_in) { ?> class="hidden"<?php } ?>>
            <div id="alert_contents">
                <div id="login_box">
                    <h2>Please login with Steam to<br />use this site</h2>
                    <a href="<?php echo SteamSignIn::genUrl(); ?>">
                        <img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_border.png"
                             alt="steam login"/>
                    </a>
                </div>
                <div id="start_pug_box">
                    <div class="close_alert">X</div>
                    <h2>Create a PUG</h2>
<form>
PUG Name:
<p><input type="text" name="name"/></p>
Server IP:
<p><input type="text" name="ip"/></p>
Server Port:
<p><input type="text" name="port"/></p>
Password:
<p><input type="password" name="password"/></p>
<input type="radio" name="gameType" value="6"/>6s<br/>
<input type="radio" name="gameType" value="9"/>9s<br/>
</form>

                </div>
            </div>
        </div>

        <div id="superwrapper">
            <div id="wrapper">
                <div id="header">
                    <div id="logo"></div>
<?php
if (isset($logged_in)) {
?>
                    <div id="steam">
                        <img src="<?php echo $_SESSION['avatar']; ?>" alt="avatar" width="30" height="30" />
                        <?php echo $_SESSION['username']; ?> ||
                        <a href="/stats" target="_self">stats</a>
                      - <a href="/settings" target="_self">settings</a>
                      - <a href="/logout.php" target="_self">logout</a>
                    </div><?php
} ?>
                </div>
                <div id="middle">
                    <div id="pug_list_header">
                        Filter:
                        <span id="filter_6s" filter="players_per_team=6">
                            6v6
                        </span>
                        <span id="filter_9s" filter="players_per_team=9">
                            9v9
                        </span>
                        <span id="filter_na" filter="region=na">
                            <img src="img/na_icon.png" />
                        </span>
                        <span id="filter_eu" filter="region=eu">
                            <img src="img/eu_icon.png" />
                        </span>
                        <span id="filter_5cp" filter="game_mode_5cp">
                            5CP
                        </span>
                        <span id="filter_ctf" filter="game_mode_ctf">
                            CTF
                        </span>
                        <span id="filter_pl" filter="game_mode_pl">
                            PL
                        </span>
                        <span id="filter_ad" filter="game_mode_ad">
                            A/D
                        </span>
                        <span id="filter_arena" filter="game_mode_arena">
                            ARENA
                        </span>
                        <input type="text" id="filter_map"
                               placeholder="Map name" />
                        <a href="#" id="start_pug">
                            Start PUG
                        </a>
                    </div>
                    <div id="no_pugs">
                        No PUGs are open!
                    </div>
                    <div id="pugs_loading">
                        <img src="img/ajax-loader.gif" />
                    </div>
                    <div id="pugs_container">

                    </div>
                    <div id="footer">
                        &copy; 2012 TF2Pickup.com
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
