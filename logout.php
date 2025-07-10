<?php
// File Path: logout.php

// התחלת session
session_start();

// טעינת קבצים
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

try {
    // יצירת אובייקט Auth
    $auth = new Auth();
    
    // התנתקות
    $auth->logout();
    
    // הפניה לדף הראשי עם הודעת התנתקות
    header('Location: login.php?logout=1');
    exit;
    
} catch (Exception $e) {
    // במקרה של שגיאה, עדיין נבצע התנתקות בסיסית
    session_unset();
    session_destroy();
    
    // מחיקת remember token cookie אם קיים
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    header('Location: login.php?logout=1&error=1');
    exit;
}
?>