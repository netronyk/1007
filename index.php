<?php
// File Path: index.php

/**
 * Sport365 - Clean Index Page
 * עמוד ראשי נקי עם CSS נפרד
 */

// הגדרות בסיסיות
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SPORT365_ROOT', __DIR__);

// בדיקה אם קבצי התצורה קיימים
$configOK = true;
$dbOK = false;
$errorMessage = '';

// בדיקת קבצי התצורה
$requiredFiles = [
    'config/config.php',
    'includes/Database.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists(SPORT365_ROOT . '/' . $file)) {
        $configOK = false;
        $errorMessage = "חסר קובץ: {$file}";
        break;
    }
}

// טעינת קבצים אם קיימים
if ($configOK) {
    try {
        require_once SPORT365_ROOT . '/config/config.php';
        require_once SPORT365_ROOT . '/includes/Database.php';
        
        // בדיקת חיבור למסד נתונים
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        $dbOK = true;
        
    } catch (Exception $e) {
        $dbOK = false;
        $errorMessage = "שגיאת חיבור למסד נתונים: " . $e->getMessage();
    }
}

// קבלת נתונים בסיסיים אם המסד עובד
$stats = [
    'total_members' => 1247,
    'total_businesses' => 89, 
    'total_coupons' => 156,
    'total_providers' => 67
];

if ($dbOK) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['total_members'] = $stmt->fetchColumn() ?: $stats['total_members'];
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM businesses WHERE status = 'active'");
        $stats['total_businesses'] = $stmt->fetchColumn() ?: $stats['total_businesses'];
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM coupons WHERE status = 'active' AND valid_until > NOW()");
        $stats['total_coupons'] = $stmt->fetchColumn() ?: $stats['total_coupons'];
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM service_providers WHERE status = 'active'");
        $stats['total_providers'] = $stmt->fetchColumn() ?: $stats['total_providers'];
        
    } catch (Exception $e) {
        // אם יש שגיאה, נשאיר את המספרים בברירת מחדל
    }
}

// בדיקה האם המשתמש כבר מחובר - אבל רק אם session עדיין לא פעיל
$isLoggedIn = false;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
}

// הגדרות SEO
$pageTitle = "Sport365 - מועדון הצרכנות לספורט ובריאות";
$pageDescription = "הצטרפו למועדון הצרכנות הגדול בישראל לספורט ובריאות. הטבות, קופונים, שירותים ועוד!";
$pageKeywords = "ספורט, בריאות, הטבות, קופונים, מאמנים אישיים, כושר, תזונה";
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="author" content="Sport365">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://windex.co.il/sport365/">
    <meta property="og:image" content="https://windex.co.il/sport365/assets/images/og-image.jpg">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/homepage.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    
    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Sport365",
        "description": "<?= htmlspecialchars($pageDescription) ?>",
        "url": "https://windex.co.il/sport365/",
        "logo": "https://windex.co.il/sport365/assets/images/logo.png",
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "04-8204465",
            "contactType": "customer service",
            "availableLanguage": "Hebrew"
        },
        "sameAs": [
            "https://www.facebook.com/sport365.co.il",
            "https://www.instagram.com/sport365.co.il"
        ]
    }
    </script>
</head>
<body>
    
    <!-- Status Alert -->
    <?php if (!$configOK || !$dbOK): ?>
    <div class="status-alert">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>התראת מערכת:</strong> <?= htmlspecialchars($errorMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.style.display='none'">×</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-trophy me-2"></i> Sport365
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">בית</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">תכונות</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">אודות</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">צור קשר</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-1"></i>
                                דשבורד
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                התנתק
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                התחברות
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2 px-3" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>
                                הרשמה
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">
                            <i class="fas fa-trophy text-warning"></i> 
                            ברוכים הבאים ל-Sport365
                        </h1>
                        <p class="lead mb-4">
                            מועדון הצרכנות הגדול והמוביל בישראל לספורט ובריאות
                        </p>
                        <p class="fs-5 mb-4 text-muted">
                            הטבות, קופונים, שירותי ספורט מקצועיים, נופשים בריאותיים ועוד - הכל במקום אחד!<br>
                            הצטרפו למועדון עם <strong><?= number_format($stats['total_members']) ?>+</strong> חברים מרוצים ותיהנו מהטבות בלעדיות.
                        </p>
                        
                        <div class="hero-buttons">
                            <?php if (!$isLoggedIn): ?>
                                <a href="register.php" class="btn btn-warning btn-lg me-3 px-4">
                                    <i class="fas fa-user-plus me-2"></i> הצטרפו עכשיו בחינם
                                </a>
                                <a href="login.php" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-sign-in-alt me-2"></i> התחברו
                                </a>
                            <?php else: ?>
                                <a href="dashboard.php" class="btn btn-warning btn-lg me-3 px-4">
                                    <i class="fas fa-tachometer-alt me-2"></i> לדשבורד שלי
                                </a>
                                <a href="#features" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-info-circle me-2"></i> למידע נוסף
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <i class="fas fa-dumbbell display-1 text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-3">
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                            <h3 class="fw-bold"><?= number_format($stats['total_members']) ?></h3>
                            <p class="text-muted mb-0">חברים פעילים</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-3">
                                <i class="fas fa-store fa-3x"></i>
                            </div>
                            <h3 class="fw-bold"><?= number_format($stats['total_businesses']) ?></h3>
                            <p class="text-muted mb-0">בתי עסק שותפים</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-3">
                                <i class="fas fa-ticket-alt fa-3x"></i>
                            </div>
                            <h3 class="fw-bold"><?= number_format($stats['total_coupons']) ?></h3>
                            <p class="text-muted mb-0">הטבות פעילות</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="text-info mb-3">
                                <i class="fas fa-dumbbell fa-3x"></i>
                            </div>
                            <h3 class="fw-bold"><?= number_format($stats['total_providers']) ?></h3>
                            <p class="text-muted mb-0">ספקי שירותים</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">מה מחכה לכם ב-Sport365?</h2>
                <p class="lead">
                    גלו את כל התכונות והשירותים המתקדמים שאנחנו מציעים לחברי המועדון שלנו
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-primary mb-3">
                                <i class="fas fa-percentage fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">הטבות וקופונים בלעדיים</h4>
                            <p class="text-muted">
                                מאות הטבות בתחום הספורט והבריאות. הנחות עד 70% במוצרים ושירותים איכותיים מהמותגים המובילים.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-success mb-3">
                                <i class="fas fa-dumbbell fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">שירותי ספורט מקצועיים</h4>
                            <p class="text-muted">
                                מאמנים אישיים מוסמכים, חדרי כושר מתקדמים, שיעורי יוגה ופילאטיס - כל השירותים במקום אחד ובמחירי חברים.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-info mb-3">
                                <i class="fas fa-plane fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">נופשי ספורט ובריאות</h4>
                            <p class="text-muted">
                                חבילות נופש מיוחדות, מלונות ספורט וצימרים עם מתקני כושר מתקדמים. חוויות בלתי נשכחות במחירים מיוחדים.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-warning mb-3">
                                <i class="fas fa-shopping-cart fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">חנות מקוונת מתקדמת</h4>
                            <p class="text-muted">
                                ציוד ספורט מקצועי, תוספי תזונה איכותיים ומוצרי בריאות במחירי חברים בלעדיים עם משלוחים מהירים.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-danger mb-3">
                                <i class="fas fa-calendar-alt fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">אירועי ספורט מרגשים</h4>
                            <p class="text-muted">
                                תחרויות ספורט, אירועי ריצה, סדנאות בריאות וכנסים מקצועיים בתחום הספורט והבריאות ברחבי הארץ.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 h-100 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="text-secondary mb-3">
                                <i class="fas fa-mobile-alt fa-3x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">אפליקציה חכמה</h4>
                            <p class="text-muted">
                                ניהול החברות, הזמנת שירותים, מעקב אחר ההטבות ותזכורות אישיות - הכל מהטלפון החכם שלכם.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <?php if (!$isLoggedIn): ?>
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold mb-4">מוכנים להצטרף למהפכת הספורט?</h2>
                    <p class="lead mb-4">
                        הצטרפו עוד היום למועדון Sport365 ותתחילו ליהנות מאלפי הטבות ושירותים מיוחדים בתחום הספורט והבריאות!
                    </p>
                    <a href="register.php" class="btn btn-warning btn-lg px-5">
                        <i class="fas fa-rocket me-2"></i> הצטרפו עכשיו בחינם
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-trophy text-warning"></i> Sport365
                    </h5>
                    <p class="text-muted">
                        מועדון הצרכנות הגדול והמוביל בישראל לספורט ובריאות. 
                        מעל <?= number_format($stats['total_members']) ?> חברים מרוצים כבר נהנים מההטבות שלנו!
                    </p>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">צור קשר</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-phone me-2"></i> 04-8204465 | 03-3106166
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i> info@sport365.co.il
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-2"></i> ישראל
                    </p>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">קישורים מהירים</h6>
                    <ul class="list-unstyled">
                        <li><a href="#about" class="text-muted text-decoration-none">אודות המועדון</a></li>
                        <li><a href="#contact" class="text-muted text-decoration-none">צור קשר</a></li>
                        <li><a href="privacy.php" class="text-muted text-decoration-none">מדיניות פרטיות</a></li>
                        <li><a href="terms.php" class="text-muted text-decoration-none">תנאי שימוש</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">עקבו אחרינו</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted fs-4" title="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-muted fs-4" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-muted fs-4" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="text-muted fs-4" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> Sport365. כל הזכויות שמורות.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-status">
                        <?php if ($dbOK): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-database me-1"></i>
                                מסד נתונים: מחובר
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-database me-1"></i>
                                מסד נתונים: לא מחובר
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = target.offsetTop - navbarHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-hide status alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.status-alert .alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Add fade-in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe cards and sections
        document.querySelectorAll('.card, section').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });

        // Add loading animation class when page loads
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
            
            // Show first section immediately
            const heroSection = document.querySelector('#home');
            if (heroSection) {
                heroSection.style.opacity = '1';
                heroSection.style.transform = 'translateY(0)';
            }
        });


        // Add navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
    
    <style>
        .navbar.scrolled {
            backdrop-filter: blur(10px);
            background: rgba(13, 110, 253, 0.95) !important;
        }
        
        .min-vh-75 {
            min-height: 75vh;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(248, 181, 0, 0.1) 100%);
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        
        .status-alert {
            position: fixed;
            top: 80px;
            left: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        @media (max-width: 768px) {
            .status-alert {
                top: 70px;
                left: 10px;
                right: 10px;
            }
            
            .display-4 {
                font-size: 2rem;
            }
            
            .display-5 {
                font-size: 1.8rem;
            }
        }
    </style>
</body>
</html>