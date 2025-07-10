<?php
// File Path: dashboards/member-dashboard.php

$db = Database::getInstance()->getConnection();

// וידוא שיש כל השדות הנדרשים למניעת warnings
if (!isset($current_user['profile_image'])) {
    $current_user['profile_image'] = null;
}
if (!isset($current_user['membership_end'])) {
    $current_user['membership_end'] = null;
}

// סטטיסטיקות חבר
$stats = [
    'bookings' => 0,
    'orders' => 0,
    'events' => 0,
    'coupons_used' => 0
];

// הזמנות שירותים
$stmt = $db->prepare("SELECT COUNT(*) FROM service_bookings WHERE user_id = ?");
$stmt->execute([$current_user['id']]);
$stats['bookings'] = $stmt->fetchColumn();

// הזמנות מוצרים
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$current_user['id']]);
$stats['orders'] = $stmt->fetchColumn();

// אירועים רשומים
$stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id = ?");
$stmt->execute([$current_user['id']]);
$stats['events'] = $stmt->fetchColumn();

// קופונים שנוצלו
$stmt = $db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE user_id = ?");
$stmt->execute([$current_user['id']]);
$stats['coupons_used'] = $stmt->fetchColumn();

// הזמנות אחרונות
$stmt = $db->prepare("
    SELECT sb.*, s.name as service_name, sp.business_name 
    FROM service_bookings sb
    JOIN services s ON sb.service_id = s.id
    JOIN service_providers sp ON sb.provider_id = sp.id
    WHERE sb.user_id = ?
    ORDER BY sb.created_at DESC
    LIMIT 5
");
$stmt->execute([$current_user['id']]);
$recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// קופונים זמינים
$stmt = $db->prepare("
    SELECT c.*, b.name as business_name
    FROM coupons c
    JOIN businesses b ON c.business_id = b.id
    WHERE c.status = 'active' 
    AND c.valid_from <= NOW() 
    AND c.valid_until >= NOW()
    AND (c.usage_limit IS NULL OR c.usage_count < c.usage_limit)
    AND c.id NOT IN (
        SELECT coupon_id FROM coupon_usage 
        WHERE user_id = ? AND status = 'used'
        GROUP BY coupon_id
        HAVING COUNT(*) >= c.per_user_limit
    )
    ORDER BY c.discount_value DESC
    LIMIT 6
");
$stmt->execute([$current_user['id']]);
$available_coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'דשבורד - Sport365';
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Dashboard CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        שלום <?php echo htmlspecialchars($current_user['first_name']); ?>!
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>
                                הזמן שירות
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                חנות
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            הזמנות שירותים
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['bookings']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            הזמנות מוצרים
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['orders']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-bag fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            אירועים
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['events']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-trophy fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            קופונים שנוצלו
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['coupons_used']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-ticket-alt fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Coupons -->
                <?php if (!empty($available_coupons)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-gift me-2"></i>
                                    קופונים זמינים עבורכם
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($available_coupons as $coupon): ?>
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <div class="coupon-card">
                                            <div class="coupon-header">
                                                <h6 class="coupon-title"><?php echo htmlspecialchars($coupon['title']); ?></h6>
                                                <span class="coupon-business"><?php echo htmlspecialchars($coupon['business_name']); ?></span>
                                            </div>
                                            <div class="coupon-discount">
                                                <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                                    <span class="discount-value"><?php echo $coupon['discount_value']; ?>%</span>
                                                    <span class="discount-text">הנחה</span>
                                                <?php else: ?>
                                                    <span class="discount-value">₪<?php echo number_format($coupon['discount_value']); ?></span>
                                                    <span class="discount-text">הנחה</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="coupon-footer">
                                                <small class="text-muted">
                                                    בתוקף עד: <?php echo date('d/m/Y', strtotime($coupon['valid_until'])); ?>
                                                </small>
                                                <button class="btn btn-sm btn-primary use-coupon" 
                                                        data-coupon-id="<?php echo $coupon['id']; ?>">
                                                    השתמש
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    הזמנות אחרונות
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($recent_bookings)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>שירות</th>
                                                <th>ספק</th>
                                                <th>תאריך</th>
                                                <th>סטטוס</th>
                                                <th>סכום</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['service_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($booking['business_name']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadgeClass($booking['booking_status']); ?>">
                                                        <?php echo getBookingStatusText($booking['booking_status']); ?>
                                                    </span>
                                                </td>
                                                <td>₪<?php echo number_format($booking['total_amount'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">עדיין לא ביצעתם הזמנות</p>
                                    <a href="services.php" class="btn btn-primary">
                                        הזמינו שירות ראשון
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    פרופיל
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="profile-avatar mb-3">
                                    <?php if (isset($current_user['profile_image']) && $current_user['profile_image']): ?>
                                        <img src="<?php echo htmlspecialchars($current_user['profile_image']); ?>" 
                                             alt="תמונת פרופיל" class="rounded-circle" width="80" height="80">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h6><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h6>
                                <p class="text-muted small"><?php echo htmlspecialchars($current_user['email']); ?></p>
                                
                                <div class="membership-info mb-3">
                                    <span class="badge bg-<?php echo getMembershipBadgeClass($current_user['membership_type']); ?> mb-2">
                                        <?php echo getMembershipText($current_user['membership_type']); ?>
                                    </span>
                                    <?php if (isset($current_user['membership_end']) && $current_user['membership_end']): ?>
                                    <small class="d-block text-muted">
                                        בתוקף עד: <?php echo date('d/m/Y', strtotime($current_user['membership_end'])); ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    ערוך פרופיל
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dashboard JS -->
    <script src="assets/js/dashboard.js"></script>
</body>
</html>

<?php
// Helper functions
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        case 'completed': return 'info';
        default: return 'secondary';
    }
}

function getBookingStatusText($status) {
    switch ($status) {
        case 'pending': return 'ממתין';
        case 'confirmed': return 'מאושר';
        case 'cancelled': return 'בוטל';
        case 'completed': return 'הושלם';
        default: return 'לא ידוע';
    }
}

function getMembershipBadgeClass($type) {
    switch ($type) {
        case 'premium': return 'warning';
        case 'vip': return 'danger';
        default: return 'primary';
    }
}

function getMembershipText($type) {
    switch ($type) {
        case 'basic': return 'חבר בסיסי';
        case 'premium': return 'חבר פרימיום';
        case 'vip': return 'חבר VIP';
        default: return 'חבר';
    }
}
?>