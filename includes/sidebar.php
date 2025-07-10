<?php
// File Path: includes/sidebar.php

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user_roles = $user_roles ?? [];
?>
<nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <!-- דשבורד ראשי -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    דשבורד
                </a>
            </li>
            
            <!-- פרופיל אישי -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'profile' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user me-2"></i>
                    פרופיל אישי
                </a>
            </li>
            
            <!-- שירותים -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['services', 'my-bookings']) ? 'active' : ''; ?>" 
                   data-bs-toggle="collapse" href="#servicesMenu" role="button">
                    <i class="fas fa-concierge-bell me-2"></i>
                    שירותים
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['services', 'my-bookings', 'book-service']) ? 'show' : ''; ?>" id="servicesMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'services' ? 'active' : ''; ?>" href="services.php">
                                <i class="fas fa-search me-2"></i>
                                חיפוש שירותים
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'my-bookings' ? 'active' : ''; ?>" href="my-bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                ההזמנות שלי
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- חנות -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['store', 'my-orders', 'cart']) ? 'active' : ''; ?>" 
                   data-bs-toggle="collapse" href="#storeMenu" role="button">
                    <i class="fas fa-shopping-cart me-2"></i>
                    חנות
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['store', 'my-orders', 'cart']) ? 'show' : ''; ?>" id="storeMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'store' ? 'active' : ''; ?>" href="store.php">
                                <i class="fas fa-store me-2"></i>
                                מוצרים
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'cart' ? 'active' : ''; ?>" href="cart.php">
                                <i class="fas fa-shopping-basket me-2"></i>
                                עגלת קניות
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'my-orders' ? 'active' : ''; ?>" href="my-orders.php">
                                <i class="fas fa-box me-2"></i>
                                ההזמנות שלי
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- אירועים -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['events', 'my-events']) ? 'active' : ''; ?>" 
                   data-bs-toggle="collapse" href="#eventsMenu" role="button">
                    <i class="fas fa-calendar-alt me-2"></i>
                    אירועים
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['events', 'my-events']) ? 'show' : ''; ?>" id="eventsMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'events' ? 'active' : ''; ?>" href="events.php">
                                <i class="fas fa-calendar me-2"></i>
                                אירועים זמינים
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'my-events' ? 'active' : ''; ?>" href="my-events.php">
                                <i class="fas fa-ticket-alt me-2"></i>
                                האירועים שלי
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- תיירות -->
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['tourism', 'my-trips']) ? 'active' : ''; ?>" 
                   data-bs-toggle="collapse" href="#tourismMenu" role="button">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    תיירות
                    <i class="fas fa-chevron-down float-end mt-1"></i>
                </a>
                <div class="collapse <?php echo in_array($current_page, ['tourism', 'my-trips']) ? 'show' : ''; ?>" id="tourismMenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'tourism' ? 'active' : ''; ?>" href="tourism.php">
                                <i class="fas fa-hotel me-2"></i>
                                נופשים וצימרים
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'my-trips' ? 'active' : ''; ?>" href="my-trips.php">
                                <i class="fas fa-suitcase me-2"></i>
                                הנופשים שלי
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <!-- קופונים -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'coupons' ? 'active' : ''; ?>" href="coupons.php">
                    <i class="fas fa-gift me-2"></i>
                    קופונים
                </a>
            </li>
            
            <!-- מועדפים -->
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'favorites' ? 'active' : ''; ?>" href="favorites.php">
                    <i class="fas fa-heart me-2"></i>
                    מועדפים
                </a>
            </li>
        </ul>
        
        <!-- תפריט ניהול (רק למנהלים) -->
        <?php if (in_array('admin', $user_roles) || in_array('super_admin', $user_roles)): ?>
        <hr class="my-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>ניהול</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'admin-dashboard' ? 'active' : ''; ?>" href="admin-dashboard.php">
                    <i class="fas fa-cogs me-2"></i>
                    פאנל ניהול
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'manage-users' ? 'active' : ''; ?>" href="manage-users.php">
                    <i class="fas fa-users-cog me-2"></i>
                    ניהול משתמשים
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'manage-businesses' ? 'active' : ''; ?>" href="manage-businesses.php">
                    <i class="fas fa-building me-2"></i>
                    ניהול עסקים
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    דוחות
                </a>
            </li>
        </ul>
        <?php endif; ?>
        
        <!-- תפריט ספק שירותים -->
        <?php if (in_array('service_provider', $user_roles)): ?>
        <hr class="my-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>ספק שירותים</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'provider-dashboard' ? 'active' : ''; ?>" href="provider-dashboard.php">
                    <i class="fas fa-chart-line me-2"></i>
                    דשבורד ספק
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'my-services' ? 'active' : ''; ?>" href="my-services.php">
                    <i class="fas fa-list me-2"></i>
                    השירותים שלי
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'service-bookings' ? 'active' : ''; ?>" href="service-bookings.php">
                    <i class="fas fa-calendar-check me-2"></i>
                    הזמנות שירותים
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'provider-earnings' ? 'active' : ''; ?>" href="provider-earnings.php">
                    <i class="fas fa-dollar-sign me-2"></i>
                    רווחים
                </a>
            </li>
        </ul>
        <?php endif; ?>
        
        <!-- תפריט מנהל חנות -->
        <?php if (in_array('store_manager', $user_roles)): ?>
        <hr class="my-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>ניהול חנות</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'store-dashboard' ? 'active' : ''; ?>" href="store-dashboard.php">
                    <i class="fas fa-store me-2"></i>
                    דשבורד חנות
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'manage-products' ? 'active' : ''; ?>" href="manage-products.php">
                    <i class="fas fa-boxes me-2"></i>
                    ניהול מוצרים
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'store-orders' ? 'active' : ''; ?>" href="store-orders.php">
                    <i class="fas fa-shopping-bag me-2"></i>
                    הזמנות
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'store-analytics' ? 'active' : ''; ?>" href="store-analytics.php">
                    <i class="fas fa-analytics me-2"></i>
                    אנליטיקה
                </a>
            </li>
        </ul>
        <?php endif; ?>
        
        <!-- תפריט סוכן מכירות -->
        <?php if (in_array('sales_agent', $user_roles)): ?>
        <hr class="my-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>סוכן מכירות</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'agent-dashboard' ? 'active' : ''; ?>" href="agent-dashboard.php">
                    <i class="fas fa-user-tie me-2"></i>
                    דשבורד סוכן
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'my-leads' ? 'active' : ''; ?>" href="my-leads.php">
                    <i class="fas fa-user-plus me-2"></i>
                    הלידים שלי
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'my-commissions' ? 'active' : ''; ?>" href="my-commissions.php">
                    <i class="fas fa-percentage me-2"></i>
                    עמלות
                </a>
            </li>
        </ul>
        <?php endif; ?>
        
        <hr class="my-3">
        
        <!-- הגדרות ועזרה -->
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-2"></i>
                    הגדרות
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'help' ? 'active' : ''; ?>" href="help.php">
                    <i class="fas fa-question-circle me-2"></i>
                    עזרה
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    התנתק
                </a>
            </li>
        </ul>
    </div>
</nav>