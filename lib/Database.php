<?php
/**
 * User: Ransom Roberson
 * Date: 1/29/14
 * Time: 10:45 PM
 */

class Database {
    private $db = null;

    public function __CONSTRUCT($config = "database") {
        self::connect($config);
    }

    /**
     * Attempt to connect to the database
     * @param string $config Config file in settings directory. Do not include .ini
     * @return bool true if success, false if failure
     * @throws PDOException if failure to connect to db
     * @throws Exception if failure to load settings
     */
    public function connect($config = "database") {
        $fileName = "settings/".$config.".ini";
        if (file_exists($fileName)) {
            $settings = parse_ini_file($fileName);
            try {
                $this->db = new PDO('mysql:host='.$settings['host'].';dbname='.$settings['dbname'].';charset=utf8', $settings['username'], $settings['password']);
                return true;
            } catch(PDOException $e) {
                throw new PDOException($e);
            }
        } else {
            throw new Exception("Failed to load database config file: ". $config);
        }
    }

    public function getHandle() {
        if ($this->db == null) {
            throw new Exception("No database connection present.");
        }
        return $this->db;
    }

    /**
     * Destroy the pdo session
     */
    public function disconnect() {
        $this->db = null;
    }

    /**
     * Disconnect from the db at end of lifecycle
     */
    function __DESTRUCT() {
        self::disconnect();
    }
} 