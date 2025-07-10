<?php
/**
 * Sport365 - Session Management
 * includes/session.php
 * מערכת ניהול Session מאובטחת
 */

// מניעת גישה ישירה לקובץ
if (!defined('SPORT365_ROOT')) {
    exit('Direct access denied');
}

class SessionManager {
    private static $started = false;
    private static $currentUser = null;
    
    /**
     * התחלת session מאובטח
     */
    public static function start() {
        if (self::$started) {
            return true;
        }
        
        // הגדרות אבטחה לסשן
        ini_set('session.cookie_httponly', SESSION_HTTPONLY);
        ini_set('session.cookie_secure', SESSION_SECURE);
        ini_set('session.cookie_samesite', SESSION_SAMESITE);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_lifetime', SESSION_LIFETIME);
        
        // שם הסשן
        session_name(SESSION_NAME);
        
        // התחלת הסשן
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        self::$started = true;
        
        // בדיקות אבטחה
        self::validateSession();
        
        // בדיקת remember me
        self::checkRememberMe();
        
        return true;
    }
    
    /**
     * בדיקות אבטחה לסשן
     */
    private static function validateSession() {
        // בדיקת IP (אופציונלי)
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== self::getClientIP()) {
                self::destroy();
                return;
            }
        } else {
            $_SESSION['ip_address'] = self::getClientIP();
        }
        
        // בדיקת User Agent
        if (isset($_SESSION['user_agent'])) {
            if ($_SESSION['user_agent'] !== self::getUserAgent()) {
                self::destroy();
                return;
            }
        } else {
            $_SESSION['user_agent'] = self::getUserAgent();
        }
        
        // בדיקת תוקף הסשן
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
                self::destroy();
                return;
            }
        }
        
        // חידוש ID סשן מעת לעת
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // כל 5 דקות
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * בדיקת remember me token
     */
    private static function checkRememberMe() {
        if (!self::isLoggedIn() && isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // כאן נבדק את הטוקן במסד נתונים
            // לעת עתה נשאיר את זה פשוט
            // TODO: הוספת טבלת remember_tokens
        }
    }
    
    /**
     * התחברת משתמש
     */
    public static function login($userId, $rememberMe = false) {
        self::start();
        
        // חידוש ID סשן למניעת session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = self::getClientIP();
        $_SESSION['user_agent'] = self::getUserAgent();
        $_SESSION['last_regeneration'] = time();
        
        // טעינת נתוני המשתמש
        self::$currentUser = new User($userId);
        
        // remember me
        if ($rememberMe) {
            self::setRememberMe($userId);
        }
        
        // רישום פעילות
        self::logActivity('login', $userId);
        
        return true;
    }
    
    /**
     * התנתקות
     */
    public static function logout() {
        self::start();
        
        $userId = self::getUserId();
        
        // מחיקת remember me
        self::clearRememberMe();
        
        // רישום פעילות
        if ($userId) {
            self::logActivity('logout', $userId);
        }
        
        // איפוס משתנים
        self::$currentUser = null;
        
        // מחיקת הסשן
        $_SESSION = [];
        
        // מחיקת cookie הסשן
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        session_destroy();
        self::$started = false;
        
        return true;
    }
    
    /**
     * השמדת session
     */
    public static function destroy() {
        self::logout();
    }
    
    /**
     * בדיקה אם המשתמש מחובר
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * קבלת ID המשתמש המחובר
     */
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * קבלת אובייקט המשתמש המחובר
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        if (self::$currentUser === null) {
            self::$currentUser = new User(self::getUserId());
        }
        
        return self::$currentUser;
    }
    
    /**
     * בדיקת הרשאה
     */
    public static function hasRole($role) {
        $user = self::getCurrentUser();
        return $user ? $user->hasRole($role) : false;
    }
    
    /**
     * בדיקת הרשאה מינימלית
     */
    public static function hasMinimumRole($requiredRole) {
        $user = self::getCurrentUser();
        return $user ? $user->hasMinimumRole($requiredRole) : false;
    }
    
    /**
     * דרישת התחברות
     */
    public static function requireLogin($redirectUrl = null) {
        if (!self::isLoggedIn()) {
            $redirectUrl = $redirectUrl ?: '/login.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * דרישת הרשאה
     */
    public static function requireRole($role, $redirectUrl = null) {
        self::requireLogin();
        
        if (!self::hasRole($role)) {
            $redirectUrl = $redirectUrl ?: '/unauthorized.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * דרישת הרשאה מינימלית
     */
    public static function requireMinimumRole($requiredRole, $redirectUrl = null) {
        self::requireLogin();
        
        if (!self::hasMinimumRole($requiredRole)) {
            $redirectUrl = $redirectUrl ?: '/unauthorized.php';
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * הגדרת remember me
     */
    private static function setRememberMe($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 יום
        
        // שמירת הטוקן בעוגיה
        setcookie('remember_token', $token, $expiry, '/', '', SESSION_SECURE, true);
        
        // כאן נשמור את הטוקן במסד נתונים
        // TODO: הוספת טבלת remember_tokens
    }
    
    /**
     * מחיקת remember me
     */
    private static function clearRememberMe() {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_token']);
        }
    }
    
    /**
     * קבלת IP הלקוח
     */
    private static function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * קבלת User Agent
     */
    private static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * רישום פעילות
     */
    private static function logActivity($action, $userId = null) {
        try {
            $db = Database::getInstance();
            
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'ip_address' => self::getClientIP(),
                'user_agent' => self::getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('activity_log', $logData);
        } catch (Exception $e) {
            // שגיאה ברישום לא צריכה לעצור את התהליך
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
    
    /**
     * הגדרת משתנה בסשן
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * קבלת משתנה מהסשן
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * מחיקת משתנה מהסשן
     */
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * בדיקה אם משתנה קיים בסשן
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * הגדרת הודעת flash
     */
    public static function setFlash($type, $message) {
        self::start();
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * קבלת הודעות flash
     */
    public static function getFlash() {
        self::start();
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
    
    /**
     * בדיקה אם יש הודעות flash
     */
    public static function hasFlash() {
        self::start();
        return !empty($_SESSION['flash_messages']);
    }
    
    /**
     * קבלת מידע על הסשן
     */
    public static function getSessionInfo() {
        self::start();
        
        return [
            'session_id' => session_id(),
            'user_id' => self::getUserId(),
            'login_time' => self::get('login_time'),
            'last_regeneration' => self::get('last_regeneration'),
            'ip_address' => self::get('ip_address'),
            'user_agent' => self::get('user_agent'),
            'is_logged_in' => self::isLoggedIn()
        ];
    }
}

// התחלת סשן אוטומטית
SessionManager::start();
?>