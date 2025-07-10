<?php
// File Path: debug.php

// הגדרת root path
define('SPORT365_ROOT', __DIR__);

echo "<h1>Sport365 Debug Information</h1>";
echo "<div style='font-family: Arial; background: #f5f5f5; padding: 20px;'>";

// בדיקת קבצים
echo "<h2>📁 Files Check</h2>";
$files = [
    'config/config.php',
    'includes/Database.php', 
    'includes/Auth.php',
    'login.php'
];

foreach ($files as $file) {
    $path = SPORT365_ROOT . '/' . $file;
    $exists = file_exists($path);
    echo "<div style='padding: 5px; margin: 5px; background: " . ($exists ? '#e8f5e8' : '#ffe8e8') . ";'>";
    echo "📄 {$file}: " . ($exists ? '✅ קיים' : '❌ לא קיים');
    if ($exists) {
        echo " (Size: " . number_format(filesize($path)) . " bytes)";
    }
    echo "</div>";
}

// טעינת config
echo "<h2>⚙️ Config Loading</h2>";
try {
    require_once SPORT365_ROOT . '/config/config.php';
    echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Config loaded successfully</div>";
    
    echo "<h3>Database Settings:</h3>";
    echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px;'>";
    echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
    echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
    echo "User: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
    echo "Password: " . (defined('DB_PASS') ? '***' : 'Not defined') . "<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Config error: " . $e->getMessage() . "</div>";
}

// בדיקת Database class
echo "<h2>🗄️ Database Class</h2>";
try {
    require_once SPORT365_ROOT . '/includes/Database.php';
    echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Database class loaded</div>";
    
    if (class_exists('Database')) {
        echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Database class exists</div>";
        
        // בדיקת חיבור פשוטה
        try {
            $db = Database::getInstance();
            echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Database instance created</div>";
            
            // בדיקת חיבור
            $stmt = $db->query("SELECT 1 as test");
            $result = $stmt->fetch();
            if ($result && $result['test'] == 1) {
                echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Database connection working</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Database connection error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Database class not found</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Database class error: " . $e->getMessage() . "</div>";
}

// בדיקת Auth class
echo "<h2>🔐 Auth Class</h2>";
try {
    require_once SPORT365_ROOT . '/includes/Auth.php';
    echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Auth class loaded</div>";
    
    if (class_exists('Auth')) {
        echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Auth class exists</div>";
        
        try {
            $auth = new Auth();
            echo "<div style='padding: 5px; background: #e8f5e8;'>✅ Auth instance created</div>";
        } catch (Exception $e) {
            echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Auth instance error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Auth class not found</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Auth class error: " . $e->getMessage() . "</div>";
}

// בדיקת טבלות
echo "<h2>📊 Database Tables</h2>";
try {
    if (isset($db)) {
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px;'>";
        echo "Total tables: " . count($tables) . "<br>";
        foreach ($tables as $table) {
            echo "• " . $table . "<br>";
        }
        echo "</div>";
        
        // בדיקת משתמשים
        $users = $db->query("SELECT COUNT(*) as count FROM users")->fetch();
        echo "<div style='padding: 5px; background: #e8f5e8;'>👥 Users in database: " . $users['count'] . "</div>";
        
        // בדיקת משתמש ספציפי
        $user = $db->query("SELECT * FROM users WHERE email = 'yaron@netron.co.il'")->fetch();
        if ($user) {
            echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px;'>";
            echo "<strong>Test User Found:</strong><br>";
            echo "ID: " . $user['id'] . "<br>";
            echo "Email: " . $user['email'] . "<br>";
            echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
            echo "Status: " . $user['status'] . "<br>";
            echo "Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "<br>";
            echo "</div>";
        } else {
            echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Test user not found</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='padding: 5px; background: #ffe8e8;'>❌ Tables check error: " . $e->getMessage() . "</div>";
}

// בדיקת PHP
echo "<h2>🐘 PHP Information</h2>";
echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px;'>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";

echo "</div>";
echo "<br><a href='login.php'>🔄 Try Login Page</a>";
?>