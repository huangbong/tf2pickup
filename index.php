<?php
require_once 'session.php';
require_once 'openid.php';

$logged_in = isset($_SESSION['steam64']);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>TF2 Pickup Games</title>
        <link rel="shortcut icon" type="image/x-icon" href="http://cdn.tf2pickup.com/favicon.ico">
        <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <script src="scripts/chat<?php if(isset($_GET["gc"])) {?>_gc<?php } ?>.js"></script>
    </head>
    <body>
        <div id="alert"<?php if ($logged_in) { ?> class="hidden"<?php } ?>>
            <div id="alert_contents">
                <h2>You have to sign in with Steam to use this site!</h2>
                <a href="<?php echo SteamSignIn::genUrl(); ?>">
                    <img src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png"
                         alt="steam login"/>
                </a>
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
                        <div id="news">
                            2.19.2012 - haxing noobs
                        </div>
                        <div id="chat_wrapper">
                            <div id="chat">
                                <textarea readonly="readonly" id="chatbox"></textarea>
                                <input type="text" id="chatinput" autocomplete="off"/>
                            </div>
                        </div>
                    </div>

                    <div id="right">
                        <div class="pug">
                            <div class="pug1">
                                6v6 Badlands<br />cp_badlands
                            </div>
                            <div class="pug2">
                                map
                            </div>
                            <div class="pug3">
                                location
                            </div>
                            <div class="pug4">
                                15/18
                            </div>
                        </div>

                        <div id="footer">
                            <div id="copy">
                                &copy;2012 TF2Pickup.com
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
