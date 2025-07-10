<?php
// File Path: includes/Database.php

class Database {
    private static $instance = null;
    private static $connection = null;
    
    private function __construct() {
        // מניעת יצירת אובייקט
    }
    
    public static function getInstance() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];
                
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    error_log("Database connection established successfully");
                }
                
            } catch (PDOException $e) {
                $error = "Database connection failed: " . $e->getMessage();
                error_log($error);
                
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    die("Database Error: " . $e->getMessage());
                } else {
                    die("Database connection failed. Please try again later.");
                }
            }
        }
        
        return self::$connection;
    }
    
    // מניעת שכפול
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * בדיקת חיבור
     */
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $stmt = $db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>