<?php
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
        <script src="scripts/chat<?php if(isset($_GET["gc"])) {?>_gc<?php } ?>.js"></script>
        <script src="scripts/main.js"></script>
    </head>
    <body>
        <div id="alert"<?php if ($logged_in) { ?> class="hidden"<?php } ?>>
            <div id="alert_contents">
                <div id="login_box">
                    <h2>You have to sign in with Steam to use this site!</h2>
                    <a href="<?php echo SteamSignIn::genUrl(); ?>">
                        <img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_large_border.png"
                             alt="steam login"/>
                    </a>
                </div>
                <div id="start_pug_box">
                    <div class="close_alert">X</div>
                    <h2>Create a PUG</h2>
                </div>
            </div>
        </div>
        <div id="superwrapper">
            <div id="wrapper">
                <div id="header">
                    <div id="logo"></div>
                    <div id="steam">
<?php
if (isset($logged_in)) {
?>
                        <img src="<?php echo $_SESSION['avatar']; ?>" alt="avatar" width="30" height="30" />
                        <?php echo $_SESSION['username']; ?> ||
                        <a href="/stats" target="_self">stats</a>
                      - <a href="/settings" target="_self">settings</a>
                      - <a href="/logout.php" target="_self">logout</a><?php
} ?>

                    </div>
                </div>
                <div id="middle">
                    <div id="left">
                        <div id="info_box">
                            <div id="news">
                                2.19.2012 - haxing noobs
                            </div>
                            <div id="player_stats">
                                <img src="http://media.steampowered.com/steamcommunity/public/images/avatars/8e/8e4b419bd8ce849dd919d8317ee374082138c92a.jpg"
                                     class="friend"
                                     height="64"
                                     width="64" />
                                <div id="stats_name">
                                    <a href="http://steamcommunity.com/id/gcommer">
                                        bEAST Gcommer
                                    </a>
                                </div>
                                <table>
                                    <tr>
                                        <td>
                                            Favorite Class
                                        </td>
                                        <td>
                                            <img src="img/class_icons/spy.png"
                                                 id="stats_fav_class"
                                                 width="22"
                                                 height="22" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Favorite Map
                                        </td>
                                        <td id="stats_fav_map">
                                            koth_saw
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            KA:D
                                        </td>
                                        <td id="stats_kad">
                                            3.2
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            PUGs Played
                                        </td>
                                        <td id="stats_pugs_played">
                                            2,098
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div id="pug_info">
                                <img src="img/maps/cp_badlands.jpg" alt="badlands" width="114" height="64" />
                                badlands?
                                &lt;LIST OF PLAYERS&gt;
                            </div>
                        </div>
                        <div id="chat_wrapper">
                            <div id="chat">
                                <textarea readonly="readonly" id="chatbox"></textarea>
                                <input type="text" id="chatinput" autocomplete="off"/>
                            </div>
                        </div>
                    </div>

                    <div id="right">
                        <div id="pug_list_header">
                            Filter:
                            <span id="filter_6s" filter="pug_type_1">
                                6v6
                            </span>
                            <span id="filter_9s" filter="pug_type_2">
                                9v9
                            </span>
                            <span id="filter_us" filter="region_us">
                                <img src="img/us.png" />
                            </span>
                            <span id="filter_eu" filter="region_eu" class="filter_disabled">
                                <img src="img/eu.png" />
                            </span>
                            <input type="text" id="filter_map" placeholder="Map name" />
                            <a href="#" id="start_pug">
                                Start PUG
                            </a>
                        </div>
                        <div class="pug" pugid="3">
                            <div class="pug_map">
                                <div class="map_image">
                                    <img src="img/maps/cp_badlands.jpg" alt="badlands" width="114" height="64" />
                                </div>
                            </div>
                            <div class="pug_title">
                                <img src="img/us.png" />
                                cp_badlands - 9v9 - Awesome pug title!
                            </div>
                            <div class="pug_player_count">
                                11/18
                            </div>
                            <div class="pug_server_info">
                                Server: RUGC East Coast Match Server<br />
                                Host: bEAST Gcommer<br />

                            </div>
                            <div class="pug_teams">
                                <div class="pug_team team_1">
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
                                <div class="pug_team team_2">
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
                        </div>

                        <div id="footer">
                            <div id="copy">
                                &copy; 2012 TF2Pickup.com
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
