<?php
// File Path: register.php

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

$auth = new Auth();

// אם המשתמש כבר מחובר, הפנה לדשבורד
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$form_data = [];

if ($_POST) {
    $form_data = [
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'date_of_birth' => $_POST['date_of_birth'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'terms_agreed' => isset($_POST['terms_agreed'])
    ];
    
    // בדיקות תקינות
    $validation_errors = [];
    
    if (empty($form_data['first_name'])) {
        $validation_errors[] = 'שם פרטי הוא שדה חובה';
    }
    
    if (empty($form_data['last_name'])) {
        $validation_errors[] = 'שם משפחה הוא שדה חובה';
    }
    
    if (empty($form_data['email'])) {
        $validation_errors[] = 'כתובת אימייל היא שדה חובה';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = 'כתובת אימייל לא תקינה';
    }
    
    if (empty($form_data['phone'])) {
        $validation_errors[] = 'מספר טלפון הוא שדה חובה';
    } elseif (!preg_match('/^[0-9-+() ]+$/', $form_data['phone'])) {
        $validation_errors[] = 'מספר טלפון לא תקין';
    }
    
    if (empty($form_data['password'])) {
        $validation_errors[] = 'סיסמה היא שדה חובה';
    } elseif (strlen($form_data['password']) < 6) {
        $validation_errors[] = 'סיסמה חייבת להכיל לפחות 6 תווים';
    }
    
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $validation_errors[] = 'אישור סיסמה לא תואם';
    }
    
    if (!$form_data['terms_agreed']) {
        $validation_errors[] = 'יש לאשר את תנאי השימוש';
    }
    
    if (!empty($validation_errors)) {
        $error = implode('<br>', $validation_errors);
    } else {
        // בדיקה האם המשתמש כבר קיים
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$form_data['email'], $form_data['phone']]);
        
        if ($stmt->fetch()) {
            $error = 'משתמש עם אימייל או טלפון זה כבר קיים במערכת';
        } else {
            // יצירת משתמש חדש
            $result = $auth->register([
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'],
                'password' => $form_data['password'],
                'date_of_birth' => $form_data['date_of_birth'] ?: null,
                'gender' => $form_data['gender'] ?: null
            ]);
            
            if ($result['success']) {
                $success = 'ההרשמה הושלמה בהצלחה! נא לבדוק את האימייל לאימות החשבון';
                $form_data = []; // נקה את הטופס
            } else {
                $error = $result['message'];
            }
        }
    }
}

$page_title = 'הרשמה - Sport365';
$meta_description = 'הצטרפו לקהילת Sport365 ותיהנו מהטבות חברים, הזמנת שירותים ועוד';
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
    <!-- Auth CSS -->
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-container register">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="mb-0">הצטרפו אלינו</h2>
                <p class="mb-0 opacity-75">הרשמו לקהילת Sport365</p>
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
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>

                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active" id="step-indicator-1">1</div>
                    <div class="step-line" id="line-1"></div>
                    <div class="step" id="step-indicator-2">2</div>
                    <div class="step-line" id="line-2"></div>
                    <div class="step" id="step-indicator-3">3</div>
                </div>

                <form method="POST" action="" novalidate id="registerForm">
                    <!-- Step 1: Personal Info -->
                    <div class="form-section active" id="step1">
                        <h4 class="mb-4 text-center">פרטים אישיים</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           placeholder="שם פרטי"
                                           value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>"
                                           required>
                                    <label for="first_name">
                                        <i class="fas fa-user me-2"></i>שם פרטי *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           placeholder="שם משפחה"
                                           value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>"
                                           required>
                                    <label for="last_name">
                                        <i class="fas fa-user me-2"></i>שם משפחה *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating">
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="your@email.com"
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   required>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>כתובת אימייל *
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="050-1234567"
                                   value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                                   required>
                            <label for="phone">
                                <i class="fas fa-phone me-2"></i>מספר טלפון *
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-next" onclick="nextStep(1)">
                                המשך <i class="fas fa-arrow-left ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Additional Info -->
                    <div class="form-section" id="step2">
                        <h4 class="mb-4 text-center">פרטים נוספים</h4>

                        <div class="form-floating">
                            <input type="date" 
                                   class="form-control" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? ''); ?>">
                            <label for="date_of_birth">
                                <i class="fas fa-calendar me-2"></i>תאריך לידה
                            </label>
                        </div>

                        <div class="form-floating">
                            <select class="form-select" id="gender" name="gender">
                                <option value="">בחר מגדר</option>
                                <option value="male" <?php echo ($form_data['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>זכר</option>
                                <option value="female" <?php echo ($form_data['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>נקבה</option>
                                <option value="other" <?php echo ($form_data['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>אחר</option>
                            </select>
                            <label for="gender">
                                <i class="fas fa-venus-mars me-2"></i>מגדר
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-prev" onclick="prevStep(2)">
                                    <i class="fas fa-arrow-right me-2"></i> חזור
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-next" onclick="nextStep(2)">
                                    המשך <i class="fas fa-arrow-left ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Password & Terms -->
                    <div class="form-section" id="step3">
                        <h4 class="mb-4 text-center">סיסמה ותנאים</h4>

                        <div class="form-floating position-relative">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Password"
                                   required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>סיסמה *
                            </label>
                            <span class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </span>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar">
                                    <div class="strength-fill"></div>
                                </div>
                                <small class="strength-text"></small>
                            </div>
                        </div>

                        <div class="form-floating position-relative">
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Confirm Password"
                                   required>
                            <label for="confirm_password">
                                <i class="fas fa-lock me-2"></i>אישור סיסמה *
                            </label>
                            <span class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </span>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="terms_agreed" 
                                   name="terms_agreed"
                                   required>
                            <label class="form-check-label" for="terms_agreed">
                                אני מסכים ל<a href="terms.php" target="_blank">תנאי השימוש</a> 
                                ול<a href="privacy.php" target="_blank">מדיניות הפרטיות</a> *
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-prev" onclick="prevStep(3)">
                                    <i class="fas fa-arrow-right me-2"></i> חזור
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-auth">
                                    <i class="fas fa-user-plus me-2"></i> הרשמה
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Links Section -->
                <div class="links-section">
                    <p class="mb-0">
                        כבר יש לכם חשבון? 
                        <a href="login.php">התחברו כאן</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Auth JS -->
    <script src="assets/js/auth.js"></script>
    <!-- Register JS -->
    <script src="assets/js/register.js"></script>
</body>
</html>