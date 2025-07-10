<?php
/**
 * Sport365 - Database Class
 * classes/Database.php
 * מחלקת מסד נתונים מתקדמת עם Singleton Pattern
 */

class Database {
    private static $instance = null;
    private $connection = null;
    private $transactionCount = 0;
    private $queries = [];
    private $queryCount = 0;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * קבלת instance יחיד של המחלקה (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * התחברות למסד נתונים
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => DB_PERSISTENT,
                PDO::ATTR_TIMEOUT => DB_TIMEOUT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->connection->exec("SET time_zone = '+03:00'");
            
        } catch (PDOException $e) {
            $this->logError("Database connection failed", $e);
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * קבלת החיבור
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * הכנת statement
     */
    public function prepare($query) {
        try {
            $this->logQuery($query);
            return $this->connection->prepare($query);
        } catch (PDOException $e) {
            $this->logError("Query preparation failed", $e, $query);
            throw $e;
        }
    }
    
    /**
     * ביצוע query פשוט
     */
    public function query($query) {
        try {
            $this->logQuery($query);
            return $this->connection->query($query);
        } catch (PDOException $e) {
            $this->logError("Query execution failed", $e, $query);
            throw $e;
        }
    }
    
    /**
     * ביצוע query עם פרמטרים
     */
    public function execute($query, $params = []) {
        try {
            $stmt = $this->prepare($query);
            $result = $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError("Query execution with params failed", $e, $query, $params);
            throw $e;
        }
    }
    
    /**
     * קבלת שורה אחת
     */
    public function fetchRow($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }
    
    /**
     * קבלת כל השורות
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * קבלת עמודה יחידה
     */
    public function fetchColumn($query, $params = [], $column = 0) {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchColumn($column);
    }
    
    /**
     * הוספת רשומה חדשה
     */
    public function insert($table, $data) {
        try {
            $fields = array_keys($data);
            $placeholders = ':' . implode(', :', $fields);
            $query = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
            
            $stmt = $this->prepare($query);
            $stmt->execute($data);
            
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->logError("Insert failed", $e, $query ?? '', $data);
            throw $e;
        }
    }
    
    /**
     * עדכון רשומה
     */
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $fields = [];
            foreach (array_keys($data) as $field) {
                $fields[] = "{$field} = :{$field}";
            }
            
            $query = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$where}";
            $params = array_merge($data, $whereParams);
            
            $stmt = $this->prepare($query);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError("Update failed", $e, $query ?? '', $params ?? []);
            throw $e;
        }
    }
    
    /**
     * מחיקת רשומה
     */
    public function delete($table, $where, $params = []) {
        try {
            $query = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->prepare($query);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError("Delete failed", $e, $query, $params);
            throw $e;
        }
    }
    
    /**
     * בדיקה אם רשומה קיימת
     */
    public function exists($table, $where, $params = []) {
        $query = "SELECT 1 FROM {$table} WHERE {$where} LIMIT 1";
        return $this->fetchColumn($query, $params) !== false;
    }
    
    /**
     * ספירת רשומות
     */
    public function count($table, $where = '1', $params = []) {
        $query = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return (int) $this->fetchColumn($query, $params);
    }
    
    /**
     * תחילת טרנזקציה
     */
    public function beginTransaction() {
        if ($this->transactionCount == 0) {
            $this->connection->beginTransaction();
        }
        $this->transactionCount++;
        return true;
    }
    
    /**
     * commit טרנזקציה
     */
    public function commit() {
        if ($this->transactionCount == 1) {
            $this->connection->commit();
        }
        $this->transactionCount = max(0, $this->transactionCount - 1);
        return true;
    }
    
    /**
     * rollback טרנזקציה
     */
    public function rollback() {
        if ($this->transactionCount >= 1) {
            $this->connection->rollback();
            $this->transactionCount = 0;
        }
        return true;
    }
    
    /**
     * בדיקה אם בתוך טרנזקציה
     */
    public function inTransaction() {
        return $this->transactionCount > 0;
    }
    
    /**
     * קבלת ID האחרון שהוכנס
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * קבלת מספר השורות שהושפעו
     */
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }
    
    /**
     * ביצוע טרנזקציה עם callback
     */
    public function transaction($callback) {
        try {
            $this->beginTransaction();
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * הכנת query עם pagination
     */
    public function paginate($query, $params = [], $page = 1, $perPage = 20) {
        // ספירת כל הרשומות
        $countQuery = "SELECT COUNT(*) FROM ({$query}) as count_table";
        $total = (int) $this->fetchColumn($countQuery, $params);
        
        // חישוב pagination
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // הוספת LIMIT לשאילתה
        $paginatedQuery = $query . " LIMIT {$offset}, {$perPage}";
        $data = $this->fetchAll($paginatedQuery, $params);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }
    
    /**
     * escape string
     */
    public function quote($string) {
        return $this->connection->quote($string);
    }
    
    /**
     * רישום query בלוג
     */
    private function logQuery($query, $params = []) {
        if (DEBUG_MODE) {
            $this->queryCount++;
            $this->queries[] = [
                'query' => $query,
                'params' => $params,
                'time' => microtime(true)
            ];
        }
    }
    
    /**
     * רישום שגיאה בלוג
     */
    private function logError($message, $exception, $query = '', $params = []) {
        $errorData = [
            'message' => $message,
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'query' => $query,
            'params' => $params,
            'time' => date('Y-m-d H:i:s')
        ];
        
        if (function_exists('error_log')) {
            error_log("Database Error: " . json_encode($errorData, JSON_UNESCAPED_UNICODE));
        }
    }
    
    /**
     * קבלת סטטיסטיקות queries
     */
    public function getQueryStats() {
        return [
            'query_count' => $this->queryCount,
            'queries' => DEBUG_MODE ? $this->queries : []
        ];
    }
    
    /**
     * איפוס סטטיסטיקות
     */
    public function resetStats() {
        $this->queryCount = 0;
        $this->queries = [];
    }
    
    /**
     * בדיקת חיבור
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * סגירת חיבור
     */
    public function close() {
        $this->connection = null;
    }
    
    // מניעת clone
    private function __clone() {}
    
    // מניעת unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize Database instance");
    }
}
?>