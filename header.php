<?php
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
              <div id="welcome">
                 <div id="logo">
                 </div>
              </div>
              <div id="steam">
                <?php include 'userbar.php';?>
              </div>
           </div>
