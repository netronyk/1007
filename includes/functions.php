<?php
/**
 * Sport365 - Helper Functions
 * includes/functions.php
 * פונקציות עזר בסיסיות
 */

// מניעת גישה ישירה לקובץ
if (!defined('SPORT365_ROOT')) {
    exit('Direct access denied');
}

// בדיקה שהקובץ לא נטען כבר
if (defined('SPORT365_FUNCTIONS_LOADED')) {
    return;
}
define('SPORT365_FUNCTIONS_LOADED', true);

/**
 * רישום שגיאה לוג
 */
function log_error($message, $context = []) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $log_message .= " - Context: " . json_encode($context);
    }
    error_log($log_message);
}

/**
 * קבלת IP הלקוח
 */
function get_client_ip() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * פורמט תאריך לתצוגה
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * פורמט תאריך ושעה לתצוגה
 */
function format_datetime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * פורמט מחיר
 */
function format_price($price, $currency = 'ILS') {
    return number_format($price, 2) . ' ' . $currency;
}

/**
 * קיצור טקסט
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * בדיקה אם זה AJAX request
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

/**
 * בדיקה אם זה מובייל
 */
function is_mobile() {
    return preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

/**
 * יצירת slug מטקסט
 */
function create_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * בדיקת קובץ קיים וניתן לקריאה
 */
function file_is_readable($file) {
    return file_exists($file) && is_readable($file);
}

/**
 * debug output (רק בסביבת פיתוח)
 */
function dd($data) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit;
    }
}

/**
 * לוג debug
 */
function debug_log($message, $data = null) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $logMessage = "[DEBUG] " . $message;
        if ($data !== null) {
            $logMessage .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        error_log($logMessage);
    }
}
?>