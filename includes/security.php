<?php
/**
 * Sport365 - Security Functions
 * includes/security.php
 * פונקציות אבטחה מתקדמות
 */

// מניעת גישה ישירה לקובץ
if (!defined('SPORT365_ROOT')) {
    exit('Direct access denied');
}

class Security {
    
    /**
     * יצירת CSRF Token
     */
    public static function generateCSRFToken() {
        if (!SessionManager::has('csrf_token') || 
            !SessionManager::has('csrf_token_time') ||
            time() - SessionManager::get('csrf_token_time') > CSRF_TOKEN_LIFETIME) {
            
            $token = bin2hex(random_bytes(32));
            SessionManager::set('csrf_token', $token);
            SessionManager::set('csrf_token_time', time());
        }
        
        return SessionManager::get('csrf_token');
    }
    
    /**
     * אימות CSRF Token
     */
    public static function validateCSRFToken($token) {
        $sessionToken = SessionManager::get('csrf_token');
        $tokenTime = SessionManager::get('csrf_token_time');
        
        if (!$sessionToken || !$tokenTime) {
            return false;
        }
        
        // בדיקת תוקף הטוקן
        if (time() - $tokenTime > CSRF_TOKEN_LIFETIME) {
            SessionManager::remove('csrf_token');
            SessionManager::remove('csrf_token_time');
            return false;
        }
        
        // השוואת הטוקנים
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * קבלת CSRF field לטפסים
     */
    public static function getCSRFField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * בדיקת CSRF בבקשות POST
     */
    public static function checkCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!self::validateCSRFToken($token)) {
                http_response_code(403);
                die('CSRF token mismatch');
            }
        }
    }
    
    /**
     * ניקוי קלט מהמשתמש
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }
        
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
                
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
                
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'html':
                // ניקוי HTML בסיסי - להשאיר רק תגיות בטוחות
                $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>';
                return strip_tags(trim($input), $allowedTags);
                
            case 'filename':
                // ניקוי שם קובץ
                $input = preg_replace('/[^a-zA-Z0-9._-]/', '', $input);
                return substr($input, 0, 255);
                
            case 'slug':
                // יצירת slug
                $input = strtolower(trim($input));
                $input = preg_replace('/[^a-z0-9-]/', '-', $input);
                $input = preg_replace('/-+/', '-', $input);
                return trim($input, '-');
                
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * וולידציה של קלט
     */
    public static function validateInput($input, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $input[$field] ?? null;
            $fieldRules = explode('|', $rule);
            
            foreach ($fieldRules as $singleRule) {
                $ruleParts = explode(':', $singleRule, 2);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "Field {$field} is required";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Field {$field} must be a valid email";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleParam) {
                            $errors[$field][] = "Field {$field} must be at least {$ruleParam} characters";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleParam) {
                            $errors[$field][] = "Field {$field} must not exceed {$ruleParam} characters";
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "Field {$field} must be numeric";
                        }
                        break;
                        
                    case 'url':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = "Field {$field} must be a valid URL";
                        }
                        break;
                        
                    case 'phone':
                        if (!empty($value) && !preg_match('/^[\d\-\+\(\)\s]+$/', $value)) {
                            $errors[$field][] = "Field {$field} must be a valid phone number";
                        }
                        break;
                        
                    case 'password':
                        if (!empty($value)) {
                            if (strlen($value) < PASSWORD_MIN_LENGTH) {
                                $errors[$field][] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters";
                            }
                            if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $value)) {
                                $errors[$field][] = "Password must contain at least one uppercase letter";
                            }
                            if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/\d/', $value)) {
                                $errors[$field][] = "Password must contain at least one number";
                            }
                            if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^a-zA-Z\d]/', $value)) {
                                $errors[$field][] = "Password must contain at least one special character";
                            }
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * מניעת XSS
     */
    public static function preventXSS($data) {
        if (is_array($data)) {
            return array_map([self::class, 'preventXSS'], $data);
        }
        
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * מניעת SQL Injection - אבטחה נוספת
     */
    public static function escapeSQLString($string) {
        $db = Database::getInstance();
        return $db->quote($string);
    }
    
    /**
     * בדיקת העלאת קבצים
     */
    public static function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        $errors = [];
        
        // בדיקה אם הקובץ הועלה
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = "No file uploaded or upload failed";
            return $errors;
        }
        
        // בדיקת שגיאות העלאה
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                UPLOAD_ERR_PARTIAL => 'File upload incomplete',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
            ];
            
            $errors[] = $uploadErrors[$file['error']] ?? 'Unknown upload error';
            return $errors;
        }
        
        // בדיקת גודל הקובץ
        $maxSize = $maxSize ?? MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            $errors[] = "File too large. Maximum size: " . formatBytes($maxSize);
        }
        
        // בדיקת סוג הקובץ
        $allowedTypes = $allowedTypes ?? array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_FILE_TYPES);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowedTypes);
        }
        
        // בדיקת MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (isset($allowedMimes[$fileExtension]) && $mimeType !== $allowedMimes[$fileExtension]) {
            $errors[] = "File MIME type doesn't match extension";
        }
        
        // בדיקת שם הקובץ
        if (strlen($file['name']) > 255) {
            $errors[] = "Filename too long";
        }
        
        // בדיקת תווים מסוכנים בשם הקובץ
        if (preg_match('/[<>:"|?*]/', $file['name'])) {
            $errors[] = "Filename contains invalid characters";
        }
        
        return $errors;
    }
    
    /**
     * בדיקת Rate Limiting
     */
    public static function checkRateLimit($action, $identifier, $maxRequests = 10, $timeWindow = 60) {
        $cacheKey = "rate_limit_{$action}_{$identifier}";
        
        // כאן נשתמש בקובץ cache או Redis
        // לעת עתה נחזיר true (אין הגבלה)
        return true;
    }
    
    /**
     * הצפנת נתונים רגישים
     */
    public static function encrypt($data, $key = null) {
        if ($key === null) {
            $key = getSetting('encryption_key', 'default_key_change_this');
        }
        
        $cipher = 'AES-256-CBC';
        $iv = random_bytes(openssl_cipher_iv_length($cipher));
        $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * פענוח נתונים מוצפנים
     */
    public static function decrypt($encryptedData, $key = null) {
        if ($key === null) {
            $key = getSetting('encryption_key', 'default_key_change_this');
        }
        
        $data = base64_decode($encryptedData);
        $cipher = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($cipher);
        
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
    }
    
    /**
     * יצירת hash מאובטח
     */
    public static function generateSecureHash($data, $salt = null) {
        if ($salt === null) {
            $salt = bin2hex(random_bytes(16));
        }
        
        return hash('sha256', $data . $salt) . ':' . $salt;
    }
    
    /**
     * אימות hash
     */
    public static function verifySecureHash($data, $hash) {
        list($hashedData, $salt) = explode(':', $hash, 2);
        return hash_equals($hashedData, hash('sha256', $data . $salt));
    }
    
    /**
     * ניקוי שם קובץ
     */
    public static function sanitizeFilename($filename) {
        // הסרת תווים מסוכנים
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // הגבלת אורך
        $filename = substr($filename, 0, 255);
        
        // מניעת שמות מסוכנים
        $dangerous = ['con', 'prn', 'aux', 'nul', 'com1', 'com2', 'com3', 'lpt1', 'lpt2', 'lpt3'];
        if (in_array(strtolower(pathinfo($filename, PATHINFO_FILENAME)), $dangerous)) {
            $filename = 'safe_' . $filename;
        }
        
        return $filename;
    }
    
    /**
     * בדיקת IP חשוד
     */
    public static function isSuspiciousIP($ip) {
        // רשימת IP-ים חשודים - אפשר לטעון מקובץ או מסד נתונים
        $blockedIPs = [
            // '192.168.1.100',
            // '10.0.0.5'
        ];
        
        return in_array($ip, $blockedIPs);
    }
    
    /**
     * רישום פעולת אבטחה
     */
    public static function logSecurityEvent($event, $details = []) {
        try {
            $db = Database::getInstance();
            
            $logData = [
                'event_type' => $event,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'user_id' => SessionManager::getUserId(),
                'details' => json_encode($details),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // כאן נוסיף טבלת security_log בעתיד
            // $db->insert('security_log', $logData);
            
            // לעת עתה נרשום לקובץ לוג
            error_log("Security Event: {$event} - " . json_encode($logData));
            
        } catch (Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
    
    /**
     * בדיקת כותרות אבטחה
     */
    public static function setSecurityHeaders() {
        // מניעת XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // מניעת MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // מניעת Clickjacking
        header('X-Frame-Options: DENY');
        
        // הגדרת Content Security Policy בסיסי
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
        
        // הסרת כותרות חשיפת מידע
        header_remove('X-Powered-By');
        header_remove('Server');
        
        // HTTPS Strict Transport Security (רק אם זה HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * יצירת טוקן אקראי מאובטח
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * השוואת מחרוזות בזמן קבוע (מניעת timing attacks)
     */
    public static function secureCompare($str1, $str2) {
        return hash_equals($str1, $str2);
    }
}

// הגדרת כותרות אבטחה אוטומטית
Security::setSecurityHeaders();
?>