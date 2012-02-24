<?php
require 'session.php';
require 'header.php';
?>
    <div id="middle">
        <div id="left">
            <div id="news">
            2.19.2012 - haxing noobs
            </div>
            <div id="chat">
                <?php include 'chat/chat.php';?>
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
        </div>
    </div>
<?php
require "footer.php";
