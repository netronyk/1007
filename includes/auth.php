<?php
// File Path: includes/Auth.php

class Auth {
    private $db;
    
    public function __construct() {
        // קבלת אינסטנס של Database
        $this->db = Database::getInstance();
    }
    
    /**
     * התחברות משתמש
     */
    public function login($email, $password, $remember_me = false) {
        try {
            // בדיקת פרמטרים
            if (empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'נא למלא את כל השדות'];
            }
            
            // חיפוש משתמש לפי אימייל - שימוש באינסטנס
            $sql = "
                SELECT 
                    u.id, u.username, u.email, u.password_hash, 
                    u.first_name, u.last_name, u.status, u.membership_type,
                    u.email_verified
                FROM users u 
                WHERE u.email = ? AND u.status = 'active'
            ";
            
            $user = $this->db->fetch($sql, [$email]);
            
            if (!$user) {
                // רישום ניסיון התחברות כושל
                $this->logActivity(null, 'login_failed', null, null, [
                    'email' => $email,
                    'reason' => 'user_not_found'
                ]);
                
                return ['success' => false, 'message' => 'שם משתמש או סיסמה שגויים'];
            }
            
            // בדיקת אימות אימייל
            if (!$user['email_verified']) {
                return ['success' => false, 'message' => 'נא לאמת את כתובת האימייל שלך לפני ההתחברות'];
            }
            
            // בדיקת סיסמה
            if (!password_verify($password, $user['password_hash'])) {
                // רישום ניסיון התחברות כושל
                $this->logActivity($user['id'], 'login_failed', null, null, [
                    'email' => $email,
                    'reason' => 'wrong_password'
                ]);
                
                return ['success' => false, 'message' => 'שם משתמש או סיסמה שגויים'];
            }
            
            // עדכון last_login - שימוש ב-query ישיר
            $this->db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            
            // יצירת session
            $this->createSession($user, $remember_me);
            
            // רישום התחברות מוצלחת
            $this->logActivity($user['id'], 'login', 'User', $user['id'], [
                'details' => 'התחברות מוצלחת'
            ]);
            
            return [
                'success' => true, 
                'message' => 'התחברות מוצלחת',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'שגיאה במערכת. נסה שוב מאוחר יותר.'];
        }
    }
    
    /**
     * יצירת session למשתמש
     */
    private function createSession($user, $remember_me = false) {
        // התחלת session אם לא קיימת
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // שמירת נתוני משתמש ב-session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['membership_type'] = $user['membership_type'];
        $_SESSION['is_logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // טעינת תפקידי המשתמש
        $roles = $this->getUserRoles($user['id']);
        $_SESSION['user_roles'] = $roles;
        $_SESSION['is_admin'] = in_array('admin', $roles) || in_array('super_admin', $roles);
        
        // Remember Me - פשוט יותר
        if ($remember_me) {
            try {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 ימים
                
                // בדיקה אם הטבלה קיימת
                $tables = $this->db->getTables();
                if (in_array('user_remember_tokens', $tables)) {
                    // שמירת token במסד הנתונים
                    $this->db->query("
                        INSERT INTO user_remember_tokens (user_id, token, expires_at) 
                        VALUES (?, ?, FROM_UNIXTIME(?))
                        ON DUPLICATE KEY UPDATE 
                        token = VALUES(token), expires_at = VALUES(expires_at)
                    ", [$user['id'], $token, $expires]);
                    
                    // יצירת cookie
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
            } catch (Exception $e) {
                error_log("Remember me error: " . $e->getMessage());
            }
        }
        
        // חידוש session ID למניעת session fixation
        session_regenerate_id(true);
    }
    
    /**
     * בדיקה אם המשתמש מחובר
     */
    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // בדיקת session רגילה
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
            return true;
        }
        
        // בדיקת Remember Me token
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * אימות Remember Me token
     */
    private function validateRememberToken($token) {
        try {
            // בדיקה אם הטבלה קיימת
            $tables = $this->db->getTables();
            if (!in_array('user_remember_tokens', $tables)) {
                return false;
            }
            
            $sql = "
                SELECT u.* 
                FROM users u
                JOIN user_remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = ? 
                AND rt.expires_at > NOW()
                AND u.status = 'active'
            ";
            $user = $this->db->fetch($sql, [$token]);
            
            if ($user) {
                $this->createSession($user, false);
                return true;
            }
            
            // מחיקת token לא תקין
            setcookie('remember_token', '', time() - 3600, '/');
            return false;
            
        } catch (Exception $e) {
            error_log("Remember token validation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * קבלת תפקידי המשתמש
     */
    private function getUserRoles($userId) {
        try {
            $sql = "SELECT role FROM user_roles WHERE user_id = ? AND status = 'active'";
            $roles_data = $this->db->fetchAll($sql, [$userId]);
            
            $roles = [];
            foreach ($roles_data as $row) {
                $roles[] = $row['role'];
            }
            
            return $roles;
            
        } catch (Exception $e) {
            error_log("Get user roles error: " . $e->getMessage());
            return ['member']; // תפקיד ברירת מחדל
        }
    }
    
    /**
     * התנתקות
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // מחיקת Remember Me token
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            try {
                // בדיקה אם הטבלה קיימת
                $tables = $this->db->getTables();
                if (in_array('user_remember_tokens', $tables)) {
                    $this->db->query("DELETE FROM user_remember_tokens WHERE token = ?", [$token]);
                }
            } catch (Exception $e) {
                error_log("Token deletion error: " . $e->getMessage());
            }
            
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // מחיקת session
        session_unset();
        session_destroy();
        
        // רישום התנתקות
        if ($userId) {
            $this->logActivity($userId, 'logout', 'User', $userId, [
                'details' => 'התנתקות מוצלחת'
            ]);
        }
        
        return true;
    }
    
    /**
     * קבלת נתוני המשתמש המחובר
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'membership_type' => $_SESSION['membership_type'] ?? 'basic',
            'roles' => $_SESSION['user_roles'] ?? ['member'],
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }
    
    /**
     * רישום פעילות - גרסה פשוטה יותר
     */
    private function logActivity($userId, $action, $model = null, $modelId = null, $changes = null) {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $changesJson = $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null;
            
            // שימוש ב-query ישיר במקום insert
            $this->db->query("
                INSERT INTO activity_log 
                (user_id, action, model, model_id, changes, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$userId, $action, $model, $modelId, $changesJson, $ipAddress, $userAgent]);
            
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
    
    /**
     * בדיקת הרשאות
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && in_array($role, $user['roles']);
    }
    
    /**
     * בדיקת הרשאת אדמין
     */
    public function isAdmin() {
        $user = $this->getCurrentUser();
        return $user && $user['is_admin'];
    }
}
?>