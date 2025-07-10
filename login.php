<?php
// File Path: login.php

// התחלת session
session_start();

// טעינת קבצים - מותאם למבנה שלך
$loadErrors = [];

try {
    require_once 'includes/config.php';
} catch (Exception $e) {
    $loadErrors[] = 'Config loading error: ' . $e->getMessage();
}

try {
    require_once 'includes/functions.php';
} catch (Exception $e) {
    $loadErrors[] = 'Functions loading error: ' . $e->getMessage();
}

try {
    require_once 'includes/Database.php';
} catch (Exception $e) {
    $loadErrors[] = 'Database loading error: ' . $e->getMessage();
}

try {
    require_once 'includes/Auth.php';
} catch (Exception $e) {
    $loadErrors[] = 'Auth loading error: ' . $e->getMessage();
}

// אם יש שגיאות טעינה, הצג אותן
if (!empty($loadErrors)) {
    die('<div style="background: #ffebee; padding: 20px; margin: 20px; border: 1px solid #f44336;">
        <h3>Loading Errors:</h3>' . implode('<br>', $loadErrors) . '
        <br><br><a href="debug.php">Check Debug Info</a>
        </div>');
}

// יצירת אובייקט auth עם error handling
try {
    $auth = new Auth();
} catch (Exception $e) {
    die('<div style="background: #ffebee; padding: 20px; margin: 20px; border: 1px solid #f44336;">
        <h3>Auth Error:</h3>' . $e->getMessage() . '
        <br><br><a href="debug.php">Check Debug Info</a>
        </div>');
}

// אם המשתמש כבר מחובר, הפנה לדשבורד
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// הצגת הודעת התנתקות
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success = 'התנתקת בהצלחה מהמערכת';
}

// טיפול בטופס
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // וולידציה בסיסית
    if (empty($email) || empty($password)) {
        $error = 'נא למלא את כל השדות';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'כתובת אימייל לא תקינה';
    } else {
        // ניסיון התחברות
        try {
            $result = $auth->login($email, $password, $remember_me);
            
            if ($result['success']) {
                // התחברות מוצלחת - הפניה לדשבורד
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'שגיאה במערכת: ' . $e->getMessage();
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $error .= '<br><small>Debug: ' . $e->getTraceAsString() . '</small>';
            }
        }
    }
}

$page_title = 'התחברות - Sport365';
$meta_description = 'התחבר לחשבון Sport365 שלך ותיהנה מהטבות חברים, הזמנת שירותים ועוד';
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Heebo', sans-serif;
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .btn-auth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .links-section {
            text-align: center;
            margin-top: 30px;
        }
        
        .links-section a {
            color: #667eea;
            text-decoration: none;
        }
        
        .links-section a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 3;
            color: #666;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Debug Info -->
            <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                Config loaded: <?php echo file_exists('includes/config.php') ? 'Yes' : 'No'; ?><br>
                Database class: <?php echo class_exists('Database') ? 'Yes' : 'No'; ?><br>
                Auth class: <?php echo class_exists('Auth') ? 'Yes' : 'No'; ?><br>
                Session status: <?php echo session_status(); ?><br>
                <a href="debug.php" target="_blank">Full Debug Info</a>
            </div>
            <?php endif; ?>
            
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h2 class="mb-0">ברוכים הבאים</h2>
                <p class="mb-0 opacity-75">התחברו לחשבון Sport365 שלכם</p>
            </div>

            <!-- Body -->
            <div class="auth-body-content">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <!-- Email Field -->
                    <div class="form-floating">
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required>
                        <label for="email">
                            <i class="fas fa-envelope me-2"></i>כתובת אימייל
                        </label>
                    </div>

                    <!-- Password Field -->
                    <div class="form-floating position-relative">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Password"
                               required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>סיסמה
                        </label>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="remember_me" 
                               name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            זכור אותי
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-auth">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        התחבר
                    </button>
                </form>

                <!-- Links Section -->
                <div class="links-section">
                    <p class="mb-2">
                        <a href="forgot-password.php">שכחתם סיסמה?</a>
                    </p>
                    <p class="mb-0">
                        עדיין אין לכם חשבון? 
                        <a href="register.php">הרשמו כאן</a>
                    </p>
                    <p class="mt-3">
                        <a href="index.php">← חזרה לעמוד הראשי</a>
                    </p>
                    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                    <p class="mt-2">
                        <a href="login_test.php">🔧 Test Login</a> |
                        <a href="debug.php">🔍 Debug Info</a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Focus on email field when page loads
        window.addEventListener('load', function() {
            document.getElementById('email').focus();
        });
        
        // Add debugging
        console.log('Login page loaded');
        console.log('Form element:', document.querySelector('form'));
        
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
        console.log('Debug mode enabled');
        <?php endif; ?>
    </script>
</body>
</html>