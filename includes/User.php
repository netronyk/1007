<?php
// File Path: includes/User.php

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * קבלת נתוני המשתמש הנוכחי
     */
    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $sql = "
                SELECT 
                    id, username, email, first_name, last_name, 
                    membership_type, status, created_at, last_login
                FROM users 
                WHERE id = ? AND status = 'active'
            ";
            
            return $this->db->fetch($sql, [$_SESSION['user_id']]);
            
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * קבלת תפקידי המשתמש
     */
    public function getUserRoles($userId) {
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
     * קבלת משתמש לפי ID
     */
    public function getUserById($userId) {
        try {
            $sql = "
                SELECT 
                    id, username, email, first_name, last_name, 
                    membership_type, status, created_at, last_login
                FROM users 
                WHERE id = ? AND status = 'active'
            ";
            
            return $this->db->fetch($sql, [$userId]);
            
        } catch (Exception $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * עדכון נתוני משתמש
     */
    public function updateUser($userId, $data) {
        try {
            // הסרת שדות שאסור לעדכן
            unset($data['id'], $data['password_hash'], $data['created_at']);
            
            if (empty($data)) {
                return false;
            }
            
            // בניית שאילתת עדכון
            $fields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            
            $values[] = $userId; // הוספת user_id לסוף
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $this->db->query($sql, $values);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * בדיקת הרשאות משתמש
     */
    public function hasRole($userId, $role) {
        $roles = $this->getUserRoles($userId);
        return in_array($role, $roles);
    }
    
    /**
     * בדיקה אם המשתמש אדמין
     */
    public function isAdmin($userId) {
        $roles = $this->getUserRoles($userId);
        return in_array('admin', $roles) || in_array('super_admin', $roles);
    }
}
?>