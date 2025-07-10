<?php
/**
 * Sport365 - User Class
 * classes/User.php
 * מחלקת משתמש מתקדמת עם ניהול הרשאות ואבטחה
 */

class User {
    private $db;
    private $data = [];
    private $roles = [];
    private $permissions = [];
    
    public function __construct($userId = null) {
        $this->db = Database::getInstance();
        
        if ($userId) {
            $this->loadUser($userId);
        }
    }
    
    /**
     * טעינת נתוני משתמש
     */
    public function loadUser($userId) {
        $query = "
            SELECT u.*, 
                   GROUP_CONCAT(ur.role) as user_roles
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.status = 'active'
            WHERE u.id = ?
            GROUP BY u.id
        ";
        
        $userData = $this->db->fetchRow($query, [$userId]);
        
        if ($userData) {
            $this->data = $userData;
            $this->roles = $userData['user_roles'] ? explode(',', $userData['user_roles']) : [];
            return true;
        }
        
        return false;
    }
    
    /**
     * יצירת משתמש חדש
     */
    public static function create($userData) {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // וולידציה
            if (!self::validateUserData($userData)) {
                throw new Exception("Invalid user data");
            }
            
            // בדיקת קיום של username ואימייל
            if (self::usernameExists($userData['username'])) {
                throw new Exception("Username already exists");
            }
            
            if (self::emailExists($userData['email'])) {
                throw new Exception("Email already exists");
            }
            
            // הצפנת סיסמא
            $userData['password_hash'] = self::hashPassword($userData['password']);
            unset($userData['password']);
            
            // הוספת נתונים נוספים
            $userData['verification_token'] = self::generateToken();
            $userData['created_at'] = date('Y-m-d H:i:s');
            $userData['membership_start'] = date('Y-m-d');
            $userData['membership_end'] = date('Y-m-d', strtotime('+1 year')); // שנה ראשונה חינם
            
            // הכנסת המשתמש
            $userId = $db->insert('users', $userData);
            
            // הוספת תפקיד בסיסי
            $roleData = [
                'user_id' => $userId,
                'role' => ROLE_MEMBER,
                'granted_at' => date('Y-m-d H:i:s'),
                'status' => USER_STATUS_ACTIVE
            ];
            $db->insert('user_roles', $roleData);
            
            $db->commit();
            
            // שליחת אימייל אימות
            self::sendVerificationEmail($userData['email'], $userData['verification_token']);
            
            return $userId;
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
    
    /**
     * התחברות משתמש
     */
    public static function login($usernameOrEmail, $password, $rememberMe = false) {
        $db = Database::getInstance();
        
        // בדיקת ניסיונות כושלים
        if (self::isLockedOut($usernameOrEmail)) {
            throw new Exception("Account temporarily locked due to multiple failed login attempts");
        }
        
        // חיפוש המשתמש
        $query = "
            SELECT id, username, email, password_hash, status, email_verified, last_login
            FROM users 
            WHERE (username = ? OR email = ?) AND status = 'active'
        ";
        
        $user = $db->fetchRow($query, [$usernameOrEmail, $usernameOrEmail]);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::recordFailedLogin($usernameOrEmail);
            throw new Exception("Invalid username/email or password");
        }
        
        // בדיקת אימות אימייל
        if (!$user['email_verified']) {
            throw new Exception("Please verify your email before logging in");
        }
        
        // עדכון last_login
        $db->update('users', 
            ['last_login' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$user['id']]
        );
        
        // איפוס ניסיונות כושלים
        self::clearFailedLogins($usernameOrEmail);
        
        // יצירת session
        self::createSession($user['id'], $rememberMe);
        
        return $user['id'];
    }
    
    /**
     * התנתקות
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // מחיקת remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    /**
     * בדיקת הרשאה
     */
    public function hasRole($role) {
        return in_array($role, $this->roles);
    }
    
    /**
     * בדיקת הרשאה מינימלית
     */
    public function hasMinimumRole($requiredRole) {
        foreach ($this->roles as $userRole) {
            if (hasPermissionLevel($userRole, $requiredRole)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * הוספת תפקיד
     */
    public function addRole($role, $grantedBy = null) {
        if (!in_array($role, getAllRoles())) {
            throw new Exception("Invalid role");
        }
        
        if ($this->hasRole($role)) {
            return true; // כבר יש לו את התפקיד
        }
        
        $roleData = [
            'user_id' => $this->getId(),
            'role' => $role,
            'granted_by' => $grantedBy,
            'granted_at' => date('Y-m-d H:i:s'),
            'status' => USER_STATUS_ACTIVE
        ];
        
        $this->db->insert('user_roles', $roleData);
        $this->roles[] = $role;
        
        return true;
    }
    
    /**
     * הסרת תפקיד
     */
    public function removeRole($role) {
        $this->db->update('user_roles', 
            ['status' => USER_STATUS_INACTIVE], 
            'user_id = ? AND role = ?', 
            [$this->getId(), $role]
        );
        
        $this->roles = array_filter($this->roles, function($r) use ($role) {
            return $r !== $role;
        });
        
        return true;
    }
    
    /**
     * עדכון פרופיל
     */
    public function updateProfile($data) {
        // סינון נתונים שאסור לעדכן
        unset($data['id'], $data['username'], $data['email'], $data['password_hash'], 
              $data['created_at'], $data['email_verified']);
        
        if (empty($data)) {
            return false;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $result = $this->db->update('users', $data, 'id = ?', [$this->getId()]);
        
        if ($result) {
            // עדכון הנתונים המקומיים
            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }
        
        return $result > 0;
    }
    
    /**
     * שינוי סיסמא
     */
    public function changePassword($currentPassword, $newPassword) {
        // בדיקת סיסמא נוכחית
        if (!password_verify($currentPassword, $this->data['password_hash'])) {
            throw new Exception("Current password is incorrect");
        }
        
        // וולידציה של סיסמא חדשה
        if (!self::validatePassword($newPassword)) {
            throw new Exception("New password does not meet requirements");
        }
        
        // עדכון הסיסמא
        $hashedPassword = self::hashPassword($newPassword);
        
        return $this->db->update('users', 
            ['password_hash' => $hashedPassword], 
            'id = ?', 
            [$this->getId()]
        ) > 0;
    }
    
    /**
     * אימות אימייל
     */
    public static function verifyEmail($token) {
        $db = Database::getInstance();
        
        $user = $db->fetchRow(
            "SELECT id FROM users WHERE verification_token = ? AND email_verified = 0", 
            [$token]
        );
        
        if (!$user) {
            return false;
        }
        
        return $db->update('users', 
            [
                'email_verified' => 1, 
                'verification_token' => null,
                'status' => USER_STATUS_ACTIVE
            ], 
            'id = ?', 
            [$user['id']]
        ) > 0;
    }
    
    /**
     * שחזור סיסמא
     */
    public static function requestPasswordReset($email) {
        $db = Database::getInstance();
        
        $user = $db->fetchRow("SELECT id FROM users WHERE email = ? AND status = 'active'", [$email]);
        
        if (!$user) {
            return false; // לא לחשוף אם האימייל קיים
        }
        
        $token = self::generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $result = $db->update('users', 
            [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry
            ], 
            'id = ?', 
            [$user['id']]
        );
        
        if ($result) {
            self::sendPasswordResetEmail($email, $token);
        }
        
        return true;
    }
    
    /**
     * איפוס סיסמא עם טוקן
     */
    public static function resetPassword($token, $newPassword) {
        $db = Database::getInstance();
        
        $user = $db->fetchRow(
            "SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()", 
            [$token]
        );
        
        if (!$user) {
            throw new Exception("Invalid or expired reset token");
        }
        
        if (!self::validatePassword($newPassword)) {
            throw new Exception("Password does not meet requirements");
        }
        
        $hashedPassword = self::hashPassword($newPassword);
        
        return $db->update('users', 
            [
                'password_hash' => $hashedPassword,
                'reset_token' => null,
                'reset_token_expiry' => null
            ], 
            'id = ?', 
            [$user['id']]
        ) > 0;
    }
    
    // ===== פונקציות עזר =====
    
    /**
     * וולידציה של נתוני משתמש
     */
    private static function validateUserData($data) {
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        // וולידציה של אימייל
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // וולידציה של סיסמא
        if (!self::validatePassword($data['password'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * וולידציה של סיסמא
     */
    private static function validatePassword($password) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return false;
        }
        
        if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/\d/', $password)) {
            return false;
        }
        
        if (PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^a-zA-Z\d]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * הצפנת סיסמא
     */
    private static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * יצירת טוקן אקראי
     */
    private static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * בדיקת קיום username
     */
    private static function usernameExists($username) {
        $db = Database::getInstance();
        return $db->exists('users', 'username = ?', [$username]);
    }
    
    /**
     * בדיקת קיום אימייל
     */
    private static function emailExists($email) {
        $db = Database::getInstance();
        return $db->exists('users', 'email = ?', [$email]);
    }
    
    /**
     * רישום ניסיון התחברות כושל
     */
    private static function recordFailedLogin($identifier) {
        // כאן אפשר להוסיף לוגיקה לשמירת ניסיונות כושלים
        // לדוגמה בקובץ או במסד נתונים נפרד
    }
    
    /**
     * בדיקה אם החשבון נעול
     */
    private static function isLockedOut($identifier) {
        // כאן אפשר להוסיף לוגיקה לבדיקת נעילה
        return false;
    }
    
    /**
     * איפוס ניסיונות כושלים
     */
    private static function clearFailedLogins($identifier) {
        // כאן אפשר להוסיף לוגיקה לאיפוס
    }
    
    /**
     * יצירת session
     */
    private static function createSession($userId, $rememberMe = false) {
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        
        if ($rememberMe) {
            // יצירת remember me token
            $token = self::generateToken();
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 יום
            
            // שמירת הטוקן במסד נתונים
            // כאן אפשר להוסיף טבלה לremember tokens
        }
    }
    
    /**
     * שליחת אימייל אימות
     */
    private static function sendVerificationEmail($email, $token) {
        // כאן תהיה האימפלמנטציה של שליחת אימייל
        // נוסיף את זה בשלבים הבאים
    }
    
    /**
     * שליחת אימייל שחזור סיסמא
     */
    private static function sendPasswordResetEmail($email, $token) {
        // כאן תהיה האימפלמנטציה של שליחת אימייל
        // נוסיף את זה בשלבים הבאים
    }
    
    // ===== Getters =====
    
    public function getId() {
        return $this->data['id'] ?? null;
    }
    
    public function getUsername() {
        return $this->data['username'] ?? null;
    }
    
    public function getEmail() {
        return $this->data['email'] ?? null;
    }
    
    public function getFullName() {
        return trim(($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''));
    }
    
    public function getFirstName() {
        return $this->data['first_name'] ?? null;
    }
    
    public function getLastName() {
        return $this->data['last_name'] ?? null;
    }
    
    public function getStatus() {
        return $this->data['status'] ?? null;
    }
    
    public function isActive() {
        return $this->getStatus() === USER_STATUS_ACTIVE;
    }
    
    public function isEmailVerified() {
        return !empty($this->data['email_verified']);
    }
    
    public function getRoles() {
        return $this->roles;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
}
?>