<?php
// File Path: includes/config.php

/**
 * Sport365 Main Configuration File
 * קובץ התצורה הראשי של Sport365
 */

// בדיקה שהקובץ לא נטען כבר
if (defined('SPORT365_CONFIG_LOADED')) {
    return;
}
define('SPORT365_CONFIG_LOADED', true);

// הגדרת SPORT365_ROOT רק אם לא הוגדר כבר
if (!defined('SPORT365_ROOT')) {
    define('SPORT365_ROOT', dirname(__DIR__));
}

// הגדרות שגיאות
error_reporting(E_ALL);
ini_set('display_errors', 1);

// הגדרת אזור זמן
date_default_timezone_set('Asia/Jerusalem');

// הגדרות בסיסיות
if (!defined('SPORT365_VERSION')) define('SPORT365_VERSION', '1.0.0');
if (!defined('SPORT365_URL')) define('SPORT365_URL', 'https://windex.co.il/sport365/');

// הגדרות מסד נתונים
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'windexco_sport365');
if (!defined('DB_USER')) define('DB_USER', 'windexco_sport365u');
if (!defined('DB_PASS')) define('DB_PASS', 'Netron~611');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// הגדרות Session
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'sport365_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600 * 24 * 30);
if (!defined('SESSION_SECURE')) define('SESSION_SECURE', false);
if (!defined('SESSION_HTTPONLY')) define('SESSION_HTTPONLY', true);

// הגדרות אבטחה
if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 6);
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);

// הגדרות כלליות
if (!defined('SITE_NAME')) define('SITE_NAME', 'Sport365');
if (!defined('CONTACT_EMAIL')) define('CONTACT_EMAIL', 'biz@sport365.co.il');
if (!defined('CONTACT_PHONE')) define('CONTACT_PHONE', '04-8204465');

// הגדרות בסיסיות לסביבה
if (!defined('DEBUG_MODE')) {
    $isLocalhost = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;
    define('DEBUG_MODE', $isLocalhost);
}

// אתחול Session בסיסי
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== פונקציות עזר בסיסיות =====

/**
 * ניקוי קלט מהמשתמש
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($input) {
        if (is_array($input)) {
            return array_map('sanitize_input', $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * בדיקת תקינות אימייל
 */
if (!function_exists('validate_email')) {
    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

/**
 * בדיקת חוזק סיסמה
 */
if (!function_exists('validate_password')) {
    function validate_password($password) {
        return strlen($password) >= 6;
    }
}

/**
 * הצפנת סיסמה
 */
if (!function_exists('hash_password')) {
    function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

/**
 * בדיקת סיסמה
 */
if (!function_exists('verify_password')) {
    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }
}

/**
 * יצירת טוקן אקראי
 */
if (!function_exists('generate_token')) {
    function generate_token($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

/**
 * הפניה לעמוד אחר
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        if (headers_sent()) {
            echo "<script>window.location.href = '$url';</script>";
        } else {
            header("Location: $url");
        }
        exit;
    }
}

/**
 * בדיקה אם הבקשה היא POST
 */
if (!function_exists('is_post')) {
    function is_post() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

/**
 * תגובת JSON
 */
if (!function_exists('json_response')) {
    function json_response($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>