<?php
// File Path: includes/Database.php

/**
 * Database Class - Singleton Pattern
 * קלאס מסד נתונים עם Singleton Pattern
 */

class Database {
    private static $instance = null;
    private $connection = null;
    private $host;
    private $database;
    private $username;
    private $password;
    private $charset;
    
    /**
     * Constructor - פרטי למניעת יצירה ישירה
     */
    private function __construct() {
        $this->host = DB_HOST;
        $this->database = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
        
        $this->connect();
    }
    
    /**
     * מניעת שכפול
     */
    private function __clone() {}
    
    /**
     * מניעת unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * קבלת מופע יחיד של המחלקה
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * יצירת חיבור למסד הנתונים
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // הגדרת אזור זמן
            $this->connection->exec("SET time_zone = '+03:00'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("שגיאה בחיבור למסד הנתונים: " . $e->getMessage());
        }
    }
    
    /**
     * קבלת חיבור למסד הנתונים
     */
    public function getConnection() {
        // בדיקה אם החיבור עדיין תקין
        if ($this->connection === null) {
            $this->connect();
        }
        
        // בדיקת חיות החיבור
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            // חיבור מת, יצירת חיבור חדש
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * ביצוע שאילתה עם הכנת משפט מוכן
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("שגיאה בביצוע שאילתה: " . $e->getMessage());
        }
    }
    
    /**
     * קבלת רשומה אחת
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * קבלת כל הרשומות
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת ערך יחיד
     */
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * הוספת רשומה חדשה
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->query($sql, $data);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Database insert failed: " . $e->getMessage());
            throw new Exception("שגיאה בהוספת רשומה: " . $e->getMessage());
        }
    }
    
    /**
     * עדכון רשומה
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        // מיזוג פרמטרים
        $params = array_merge($data, $whereParams);
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database update failed: " . $e->getMessage());
            throw new Exception("שגיאה בעדכון רשומה: " . $e->getMessage());
        }
    }
    
    /**
     * מחיקת רשומה
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database delete failed: " . $e->getMessage());
            throw new Exception("שגיאה במחיקת רשומה: " . $e->getMessage());
        }
    }
    
    /**
     * התחלת טרנזקציה
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * אישור טרנזקציה
     */
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    /**
     * ביטול טרנזקציה
     */
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    /**
     * בדיקה האם נמצאים בטרנזקציה
     */
    public function inTransaction() {
        return $this->getConnection()->inTransaction();
    }
    
    /**
     * קבלת ID של הרשומה האחרונה שנוספה
     */
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }
    
    /**
     * ספירת רשומות
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return $this->fetchColumn($sql, $params);
    }
    
    /**
     * בדיקה האם רשומה קיימת
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * קבלת מידע על טבלה
     */
    public function getTableInfo($table) {
        $sql = "DESCRIBE {$table}";
        return $this->fetchAll($sql);
    }
    
    /**
     * קבלת רשימת טבלאות
     */
    public function getTables() {
        $sql = "SHOW TABLES";
        return $this->fetchAll($sql);
    }
    
    /**
     * ביצוע שאילתת RAW (זהירות!)
     */
    public function raw($sql) {
        try {
            return $this->getConnection()->exec($sql);
        } catch (PDOException $e) {
            error_log("Database raw query failed: " . $e->getMessage());
            throw new Exception("שגיאה בביצוע שאילתה: " . $e->getMessage());
        }
    }
    
    /**
     * Backup טבלה
     */
    public function backupTable($table, $filePath) {
        $sql = "SELECT * FROM {$table}";
        $data = $this->fetchAll($sql);
        
        $backup = [
            'table' => $table,
            'timestamp' => date('Y-m-d H:i:s'),
            'count' => count($data),
            'data' => $data
        ];
        
        return file_put_contents($filePath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * ניקוי cache של prepared statements
     */
    public function clearCache() {
        $this->getConnection()->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $this->getConnection()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    
    /**
     * קבלת סטטיסטיקות מסד נתונים
     */
    public function getStats() {
        $stats = [];
        
        try {
            // גודל מסד נתונים
            $sql = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS DB_size_MB 
                    FROM information_schema.tables 
                    WHERE table_schema = :database";
            $stats['size_mb'] = $this->fetchColumn($sql, ['database' => $this->database]);
            
            // מספר טבלאות
            $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database";
            $stats['table_count'] = $this->fetchColumn($sql, ['database' => $this->database]);
            
            // חיבורים פעילים
            $sql = "SHOW STATUS LIKE 'Threads_connected'";
            $result = $this->fetch($sql);
            $stats['connections'] = $result['Value'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Failed to get database stats: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * בדיקת תקינות מסד נתונים
     */
    public function healthCheck() {
        try {
            $this->query('SELECT 1');
            return [
                'status' => 'healthy',
                'message' => 'מסד הנתונים פועל תקין',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'שגיאה במסד הנתונים: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * סגירת חיבור (יקרא אוטומטית)
     */
    public function __destruct() {
        $this->connection = null;
    }
}
?>