<?php
/* mysql.php (should be called db.php or model.php...)
 *
 * Provides database access through a convenient singleton.
 */

define("DB_HOST", "localhost");
define("DB_NAME", "tf2pickup");
define("DB_USER", "tf2pickup");
define("DB_PASS", "dHuY4k3MNflJ6RV");

/* Solely for backwards compatibility with old code */
class mysql {
    public static function connect() {
        $con = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die("Error connecting to MySQL");
        mysql_select_db(DB_NAME, $con);
        return $con;
    }
}

/* Good code uses this: */
class Model {
    // singleton instance
    private static $instance;

    // private constructor function
    // to prevent external instantiation
    private function __construct() { }
    function __destruct() {
        if ($this->connected) {
            $this->disconnect();
        }
    }

    // getInstance method
    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private $connected = false;
    private $connection = false;
    private $operations = 0;

    /* Returns number of executed operations */
    public function getOpCount() {
       return $this->operations;
    }

    /*******************************************
     * Connection Methds                       *
     *******************************************/

    public function connect($hostname = DB_HOST, $dbname = DB_NAME,
                            $username = DB_USER, $password = DB_PASS) {
        $dsn = 'mysql:dbname=' . $dbname . ';host=' . $hostname;
        try {
            $this->connection = new PDO($dsn, $username, $password);
            $this->connected = true;
        } catch (PDOException $e) {
            /* TODO: Once we have a reasonable error-handling strategy
             * we won't just nuke the whole site when the DB goes down */
            die('Connection failed: ' . $e->getMessage());
            $this->connected = false;
        }
    }

    public function disconnect() {
        $this->connection = null;
        $this->connected = false;
    }

    /*******************************************
     * Query Methds                            *
     *******************************************/

    private function __query($sql, $args=array()) {
        $query = $this->connection->prepare($sql);
        if ($query->execute($args) === FALSE)
            return false;
        ++$this->operations;
        return $query->fetchAll();
    }

    /* Fetch all currently open lobbies */
    private function fetchOpenPUGs() {
        $sql = <<<SQL
    SELECT * FROM `pugs` WHERE `teams`.`id` in :id_list
SQL;
        return $this->__query($sql);
    }

    /* Takes an array of ids and fetches all associated lobbies */
    private function fetchPUGs($ids) {
        $sql = <<<SQL
    SELECT * FROM `pugs` WHERE `teams`.`id` in (:id_list)
SQL;
        $id_list = implode(",", $ids);
        return $this->__query($sql, array(':id_list' => $team_id));
    }

    /* Fetch all players in the lobby with the given id */
    private function fetchPlayersInPUG($id) {
        $sql = <<<SQL
    SELECT * FROM `players` WHERE `players`.`pug_id` = :id
SQL;
        return $this->__query($sql, array(':id' => $id));
    }

    private function createPUG() {

    }
}