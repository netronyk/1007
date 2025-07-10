<?php
// File Path: config/config.php

// מניעת גישה ישירה
if (!defined('SPORT365_ROOT')) {
    die('Access denied');
}

// הגדרות כלליות
define('SITE_NAME', 'Sport365');
define('SITE_URL', 'https://sport365.co.il');
define('SITE_EMAIL', 'info@sport365.co.il');

// הגדרות debug
define('DEBUG_MODE', true); // שנה ל-false בפרודקשן
define('LOG_ERRORS', true);

// הגדרות מסד נתונים
define('DB_HOST', 'localhost');
define('DB_NAME', 'windexco_sport365');
define('DB_USER', 'windexco_sport365u');
define('DB_PASS', 'Netron~611'); // החלף בסיסמה האמיתית
define('DB_CHARSET', 'utf8mb4');

// הגדרות שגיאות
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// הגדרות session - רק אם session לא פעילה
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // משנה ל-0 לפיתוח ללא HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// הגדרות timezone
date_default_timezone_set('Asia/Jerusalem');

// הגדרות אימייל
define('SMTP_HOST', 'mail.sport365.co.il');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@sport365.co.il');
define('SMTP_PASSWORD', 'your_smtp_password');
define('SMTP_ENCRYPTION', 'tls');

// הגדרות תשלומים
define('TRANZILA_TERMINAL', '');
define('TRANZILA_USERNAME', '');
define('CURRENCY', 'ILS');

// נתיבים
define('UPLOAD_PATH', SPORT365_ROOT . '/uploads/');
define('LOGS_PATH', SPORT365_ROOT . '/logs/');

// יצירת תיקיות אם לא קיימות
$dirs = [UPLOAD_PATH, LOGS_PATH];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// הגדרת error handler מותאם
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // התעלם מ-warnings על ini_set
    if ($errno === E_WARNING && strpos($errstr, 'ini_set') !== false) {
        return true;
    }
    
    $error = "Error: [$errno] $errstr in $errfile on line $errline";
    
    if (LOG_ERRORS) {
        error_log($error, 3, LOGS_PATH . 'errors.log');
    }
    
    if (DEBUG_MODE && $errno !== E_WARNING) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px; border-radius: 4px;'>";
        echo "<strong>Debug Error:</strong> $error";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('customErrorHandler');

// פונקציית עזר לדיבוג
function debug($data, $label = '') {
    if (DEBUG_MODE) {
        echo "<div style='background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin: 10px; border-radius: 4px;'>";
        if ($label) echo "<strong>$label:</strong><br>";
        echo "<pre>" . print_r($data, true) . "</pre>";
        echo "</div>";
    }
}

// פונקציית sanitize
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// פונקציית redirect
function redirect($url, $permanent = false) {
    $code = $permanent ? 301 : 302;
    header("Location: $url", true, $code);
    exit;
}

// בדיקת HTTPS בפרודקשן - מושבת לפיתוח
if (!DEBUG_MODE && !isset($_SERVER['HTTPS'])) {
    redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true);
}
?>