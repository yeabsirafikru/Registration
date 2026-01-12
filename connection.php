<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new SQLite3('users.db');
            $this->connection->busyTimeout(5000);
            // Enable foreign keys
            $this->connection->exec('PRAGMA foreign_keys = ON;');
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Helper function to get database connection
function getDB() {
    $db = Database::getInstance();
    return $db->getConnection();
}
?>
