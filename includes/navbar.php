<?php
// File Path: includes/navbar.php

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-dumbbell me-2"></i>
            Sport365
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        דשבורד
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'services' ? 'active' : ''; ?>" href="services.php">
                        <i class="fas fa-concierge-bell me-1"></i>
                        שירותים
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'store' ? 'active' : ''; ?>" href="store.php">
                        <i class="fas fa-shopping-cart me-1"></i>
                        חנות
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'events' ? 'active' : ''; ?>" href="events.php">
                        <i class="fas fa-calendar-alt me-1"></i>
                        אירועים
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'tourism' ? 'active' : ''; ?>" href="tourism.php">
                        <i class="fas fa-map-marked-alt me-1"></i>
                        תיירות
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">התראות חדשות</h6></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            הזמנה מאושרת למחר
                        </a></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-gift text-warning me-2"></i>
                            קופון חדש זמין
                        </a></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-star text-info me-2"></i>
                            בקשה לדירוג שירות
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="notifications.php">צפה בכל ההתראות</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <?php if (isset($current_user['profile_image']) && $current_user['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($current_user['profile_image']); ?>" 
                                 alt="פרופיל" class="rounded-circle me-2" width="30" height="30">
                        <?php else: ?>
                            <div class="bg-light rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                 style="width: 30px; height: 30px;">
                                <i class="fas fa-user text-dark"></i>
                            </div>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($current_user['first_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-2"></i>
                            פרופיל אישי
                        </a></li>
                        <li><a class="dropdown-item" href="my-bookings.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            ההזמנות שלי
                        </a></li>
                        <li><a class="dropdown-item" href="my-orders.php">
                            <i class="fas fa-shopping-bag me-2"></i>
                            ההזמנות שלי
                        </a></li>
                        <li><a class="dropdown-item" href="favorites.php">
                            <i class="fas fa-heart me-2"></i>
                            מועדפים
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog me-2"></i>
                            הגדרות
                        </a></li>
                        <li><a class="dropdown-item" href="help.php">
                            <i class="fas fa-question-circle me-2"></i>
                            עזרה
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            התנתק
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>