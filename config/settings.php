<?php
/**
 * Sport365 - General Settings Configuration
 * הגדרות כלליות למערכת
 */

// מניעת גישה ישירה לקובץ
if (!defined('SPORT365_ROOT')) {
    exit('Direct access denied');
}

// ===== הגדרות סביבה =====
define('ENVIRONMENT', 'development'); // development / production
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('MAINTENANCE_MODE', false);

// ===== הגדרות אתר =====
define('SITE_NAME', 'Sport365');
define('SITE_DESCRIPTION', 'מועדון הצרכנות לספורט ובריאות');
define('SITE_KEYWORDS', 'ספורט, בריאות, הטבות, קופונים, מאמנים');
define('SITE_LANGUAGE', 'he');
define('SITE_DIRECTION', 'rtl');
define('SITE_CHARSET', 'UTF-8');

// ===== הגדרות URL - עדכן לכתובת שלך =====
define('SITE_URL', 'https://windexco.com/sport365'); // עדכן את הדומיין שלך
define('ADMIN_URL', SITE_URL . '/admin');
define('API_URL', SITE_URL . '/api');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');

// ===== הגדרות קבצים =====
define('UPLOADS_PATH', SPORT365_ROOT . '/assets/uploads');
define('LOGS_PATH', SPORT365_ROOT . '/logs');
define('CACHE_PATH', SPORT365_ROOT . '/cache');

// ===== הגדרות אבטחה =====
define('SESSION_NAME', 'sport365_session');
define('SESSION_LIFETIME', 86400); // 24 שעות
define('SESSION_SECURE', false); // false אם אין SSL, true אם יש
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');

define('CSRF_TOKEN_NAME', 'sport365_csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600); // שעה

define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);

define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 דקות

// ===== הגדרות אימייל =====
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', ''); // עדכן
define('SMTP_PASSWORD', ''); // עדכן
define('SMTP_ENCRYPTION', 'tls');

define('FROM_EMAIL', 'noreply@sport365.co.il');
define('FROM_NAME', 'Sport365');
define('ADMIN_EMAIL', 'admin@sport365.co.il');

// ===== הגדרות תשלומים =====
define('CURRENCY', 'ILS');
define('CURRENCY_SYMBOL', '₪');
define('CURRENCY_POSITION', 'after'); // before / after

// Tranzila Settings
define('TRANZILA_TERMINAL', ''); // מספר טרמינל
define('TRANZILA_USERNAME', ''); // שם משתמש
define('TRANZILA_API_URL', 'https://secure5.tranzila.com/cgi-bin/tranzila71u.cgi');
define('TRANZILA_TEST_MODE', ENVIRONMENT === 'development');

// ===== הגדרות קבצים והעלאות =====
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

define('IMAGE_QUALITY', 85);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);

// ===== הגדרות מערכת =====
define('TIMEZONE', 'Asia/Jerusalem');
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'd/m/Y H:i');

define('ITEMS_PER_PAGE', 20);
define('SEARCH_MIN_LENGTH', 3);
define('CACHE_LIFETIME', 3600); // שעה

// ===== הגדרות SEO =====
define('META_TITLE_MAX_LENGTH', 60);
define('META_DESCRIPTION_MAX_LENGTH', 160);
define('SLUG_MAX_LENGTH', 100);

// ===== הגדרות API =====
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // בקשות לשעה
define('API_TOKEN_LIFETIME', 7200); // שעתיים

// ===== הגדרות התראות =====
define('NOTIFICATION_EMAIL_ENABLED', true);
define('NOTIFICATION_SMS_ENABLED', false);
define('NOTIFICATION_PUSH_ENABLED', false);

// ===== הגדרות לוגים =====
define('LOG_LEVEL', DEBUG_MODE ? 'debug' : 'error');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 30);

// ===== הגדרות Google Services =====
define('GOOGLE_MAPS_API_KEY', ''); // מפתח Google Maps
define('GOOGLE_ANALYTICS_ID', ''); // Google Analytics
define('GOOGLE_RECAPTCHA_SITE_KEY', ''); // reCAPTCHA
define('GOOGLE_RECAPTCHA_SECRET_KEY', '');

// ===== הגדרות QR Code =====
define('QR_CODE_SIZE', 300);
define('QR_CODE_MARGIN', 2);
define('QR_CODE_ERROR_CORRECTION', 'M'); // L, M, Q, H

// ===== הגדרות חיפוש =====
define('SEARCH_ENGINE', 'mysql'); // mysql / elasticsearch
define('ELASTICSEARCH_HOST', 'localhost:9200');

// ===== הגדרות Redis (אם זמין) =====
define('REDIS_ENABLED', false);
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', '');
define('REDIS_DATABASE', 0);

// ===== פונקציות עזר =====

/**
 * קבלת הגדרה מהמסד או ברירת מחדל
 */
function getSetting($key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        try {
            if (class_exists('Database')) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE 1");
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (Exception $e) {
            // אם יש בעיה עם המסד, נשתמש בברירות מחדל
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * עדכון הגדרה במסד
 */
function updateSetting($key, $value, $type = 'string') {
    try {
        if (class_exists('Database')) {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                setting_type = VALUES(setting_type),
                updated_at = CURRENT_TIMESTAMP
            ");
            return $stmt->execute([$key, $value, $type]);
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * בדיקה אם המערכת במצב תחזוקה
 */
function isMaintenanceMode() {
    return getSetting('maintenance_mode', MAINTENANCE_MODE);
}

/**
 * קבלת URL מלא
 */
function getFullUrl($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * הגדרת timezone
 */
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(TIMEZONE);
}

/**
 * הגדרת locale לעברית
 */
if (function_exists('setlocale')) {
    setlocale(LC_TIME, 'he_IL.UTF-8', 'Hebrew_Israel.1255', 'heb');
}
?>