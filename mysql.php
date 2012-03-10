<?php
/* mysql.php (should be called db.php or model.php...)
 *
 * Provides database access through a convenient singleton.
 */
require_once 'config.php';

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
        if ($query->execute($args) === FALSE) return false;
        ++$this->operations;
        return $query->fetchAll();
    }

    /* Fetch all currently open lobbies */
    public function fetchOpenPUGs() {
        $sql = <<<SQL
    SELECT *, UNIX_TIMESTAMP(`pugs`.`last_updated`) as `updated`
    FROM `pugs` WHERE `pugs`.`started` = FALSE
SQL;
        return $this->__query($sql);
    }

    /* Takes an array of [id, timestamp] pairs and fetches all
     * lobbies with an id in the list and timestamp after the
     * provided timestamp. */
    public function fetchUpdatedPUGs($pugs) {
        $sql = <<<SQL
    SELECT *, UNIX_TIMESTAMP(`pugs`.`last_updated`) as `updated`
    FROM `pugs`
    WHERE (
SQL;
        $first = true;
        $params = array();
        $ids = array();
        foreach ($pugs as $pug) {
            if (!$first)
                $sql .= " OR ";
            $first = false;

            // Could use a HAVING clause instead of re-applying UNIX_TIMESTAMP,
            // but HAVING clauses can hurt performance, while UNIX_TIMESTAMP
            // has practically no overhead
            $sql .= "(`pugs`.`id` = ? AND UNIX_TIMESTAMP(`pugs`.`last_updated`) > ?)";

            list($id, $time) = $pug;
            array_push($ids, $id);
            array_push($params, $id, $time);
        }
        $sql .= " OR (`pugs`.`started` = 0 AND !(`pugs`.`id` in (" . implode(",", array_fill(0, count($ids), "?")) . "))) )";

        return $this->__query($sql, array_merge($params, $ids));
    }

    /* Takes an array of ids and fetches all associated lobbies */
    public function fetchPUGs($ids) {
        $sql = <<<SQL
    SELECT *, UNIX_TIMESTAMP(`pugs`.`last_updated`) as `updated`
    FROM `pugs` WHERE `teams`.`id` in (:id_list)
SQL;
        $id_list = implode(",", $ids);
        return $this->__query($sql, array(':id_list' => $team_id));
    }

    public function fetchPUG($id) {
        $sql = <<<SQL
    SELECT *, UNIX_TIMESTAMP(`pugs`.`last_updated`) as `updated`
    FROM `pugs` WHERE `pugs`.`id` = :id
SQL;
        return $this->__query($sql, array(':id' => $id));
    }

    /* Fetch all players in the lobby with the given id */
    public function fetchPlayersInPUG($id) {
        $sql = <<<SQL
    SELECT `players`.`team`
         , `players`.`class`
         , `players`.`user_id`
         , `users`.`username`
         , `users`.`avatar`
    FROM `players` INNER JOIN `users`
        ON `players`.`user_id` = `users`.`id`
        WHERE `players`.`pug_id` = :id
          AND `players`.`left` = 0
SQL;
        return $this->__query($sql, array(':id' => $id));
    }

    public function getMapId($name) {
        $sql = <<<SQL
    SELECT `id` FROM `maps` WHERE `maps`.`name` = ':name'
SQL;
        return $this->__query($sql, array(':name' => $name));
    }

    public function isBanned($steam64) {
        $sql = <<<SQL
    SELECT `banned` FROM `users` WHERE `id`=:steam64
SQL;
        $result = $this->__query($sql, array(':steam64' => $steam64));
        return (count($result) === 1 && $result["banned"] === 1);
    }

    public function createPUG($name, $region, $pug_type,
                              $map, $host_id, $server_name,
                              $ip, $port, $rcon) {
        // TODO: my __query didn't really fit that well here...
        $sql = <<<SQL
    INSERT INTO `pugs`
        (`name`, `region`, `pug_type`, `map`, `host_id`
       , `server_name`, `server_ip`, `server_port`, `rcon`)
    VALUES
        (:name, :region, :pug_type, :map,
         :host_id, :server_name, :server_ip, :server_port,
         :rcon)
SQL;

        if (!preg_match('/\d+/', $host_id)) {
            echo "invalid host";
            return false;
        }

        // These values need to be replaced manually
        //$sql = str_replace(':pug_type', (int)$pug_type, $sql);
        //$sql = str_replace(':host_id', $host_id, $sql);
        //$sql = str_replace(':server_port', (int)$server_port, $sql);

        $query = $this->connection->prepare($sql);

        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':region', $region, PDO::PARAM_STR);
        $query->bindParam(':pug_type', $pug_type, PDO::PARAM_INT);
        $query->bindParam(':map', $map, PDO::PARAM_STR);
        $query->bindParam(':host_id', $host_id, PDO::PARAM_STR);
        $query->bindParam(':server_name', $name, PDO::PARAM_STR);
        $query->bindParam(':server_ip', $ip, PDO::PARAM_STR);
        $query->bindParam(':server_port', $port, PDO::PARAM_INT);
        $query->bindParam(':rcon', $rcon, PDO::PARAM_STR);

        if ($query->execute() === FALSE) {
            echo "[" . $query->queryString . "]";
            echo "MySQL error (" . $query->errorCode()
                . "): ";
                 var_dump($query->errorInfo());
            return false;
        }

        ++$this->operations;
        return $query->fetchAll();
    }

    public function createUser($steam64, $username, $avatar, $country) {
        $sql = <<<SQL
    INSERT INTO users (id, username, avatar, country)
               VALUES (':steam64',':username',':avatar',':country')
SQL;
        return $this->__query($sql, array(
            ':steam64' => $steam64,
            ':username' => $username,
            ':avatar' => $avatar,
            ':country' => $country
        ));
    }

    public function userExists($steam64) {
        $sql = <<<SQL
    SELECT `id` FROM `users` WHERE `id`=:steam64
SQL;
        $result = $this->__query($sql, array(':steam64' => $steam64));
        return count($result) === 1;
    }

    public function updateUser($steam64, $username, $avatar, $country) {
        $sql = <<<SQL
    UPDATE `users` SET `username` = ':username'
                     , `avatar` = ':avatar'
                     , `country` = ':country'
                   WHERE `id` = ':steam64'
SQL;
        return $this->__query($sql, array(
            ':steam64' => $steam64,
            ':username' => $username,
            ':avatar' => $avatar,
            ':country' => $country
        ));
    }
}