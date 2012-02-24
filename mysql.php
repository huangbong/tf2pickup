<?php

define("DB_HOST", "localhost");
define("DB_NAME", "tf2pickup");
define("DB_USER", "tf2pickup");
define("DB_PASS", "dHuY4k3MNflJ6RV");

class mysql {
    public static function connect() {
        /* If this fails, the whole site will just be replaced by
         * an error message... */
        $con = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die("Error connecting to MySQL");
        mysql_select_db(DB_NAME, $con);
        return $con;
    }
}

