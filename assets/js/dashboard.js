// File Path: assets/js/dashboard.js

/**
 * Dashboard JavaScript Functions
 * פונקציות JavaScript לדשבורד
 */

// Global variables
let dashboardData = {};
let activeModals = [];

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    loadDashboardData();
    setupEventListeners();
    initializeTooltips();
    startPeriodicUpdates();
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Initialize data tables if DataTables is available
    if (typeof $.fn.DataTable !== 'undefined') {
        initializeDataTables();
    }
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize notifications
    initializeNotifications();
    
    console.log('Dashboard initialized successfully');
}

/**
 * Load dashboard data via AJAX
 */
function loadDashboardData() {
    fetch('ajax/dashboard-data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dashboardData = data.data;
                updateDashboardUI(data.data);
            } else {
                showToast('שגיאה בטעינת נתונים: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            showToast('שגיאה בחיבור לשרת', 'error');
        });
}

/**
 * Update dashboard UI with new data
 */
function updateDashboardUI(data) {
    // Update stat cards
    updateStatCards(data.stats);
    
    // Update recent activities
    updateRecentActivities(data.recent_activities);
    
    // Update notifications
    updateNotifications(data.notifications);
    
    // Update charts
    updateCharts(data.chart_data);
}

/**
 * Update statistics cards
 */
function updateStatCards(stats) {
    Object.keys(stats).forEach(key => {
        const element = document.getElementById('stat-' + key);
        if (element) {
            const value = stats[key];
            animateNumber(element, value);
        }
    });
}

/**
 * Animate number counting
 */
function animateNumber(element, targetValue) {
    const startValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
    const increment = Math.ceil((targetValue - startValue) / 30);
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.textContent = formatNumber(currentValue);
    }, 50);
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Update recent activities list
 */
function updateRecentActivities(activities) {
    const container = document.getElementById('recent-activities');
    if (!container || !activities) return;
    
    container.innerHTML = '';
    
    activities.forEach(activity => {
        const activityElement = createActivityElement(activity);
        container.appendChild(activityElement);
    });
}

/**
 * Create activity element
 */
function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'activity-item d-flex align-items-center mb-3';
    
    div.innerHTML = `
        <div class="activity-icon me-3">
            <i class="fas ${getActivityIcon(activity.type)} text-${getActivityColor(activity.type)}"></i>
        </div>
        <div class="activity-content flex-grow-1">
            <div class="activity-title">${activity.title}</div>
            <small class="text-muted">${formatRelativeTime(activity.created_at)}</small>
        </div>
    `;
    
    return div;
}

/**
 * Get activity icon based on type
 */
function getActivityIcon(type) {
    const icons = {
        'booking': 'fa-calendar-check',
        'order': 'fa-shopping-cart',
        'payment': 'fa-credit-card',
        'coupon': 'fa-gift',
        'review': 'fa-star',
        'login': 'fa-sign-in-alt',
        'default': 'fa-info-circle'
    };
    
    return icons[type] || icons.default;
}

/**
 * Get activity color based on type
 */
function getActivityColor(type) {
    const colors = {
        'booking': 'primary',
        'order': 'success',
        'payment': 'warning',
        'coupon': 'info',
        'review': 'warning',
        'login': 'secondary',
        'default': 'primary'
    };
    
    return colors[type] || colors.default;
}

/**
 * Format relative time
 */
function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'עכשיו';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `לפני ${minutes} דקות`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `לפני ${hours} שעות`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `לפני ${days} ימים`;
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Coupon usage buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('use-coupon')) {
            e.preventDefault();
            const couponId = e.target.getAttribute('data-coupon-id');
            useCoupon(couponId);
        }
    });
    
    // Refresh buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('refresh-data')) {
            e.preventDefault();
            loadDashboardData();
        }
    });
    
    // Quick action buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quick-action')) {
            e.preventDefault();
            const action = e.target.getAttribute('data-action');
            handleQuickAction(action);
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('dashboard-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }
    
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    }
}

/**
 * Use coupon functionality
 */
function useCoupon(couponId) {
    if (!couponId) return;
    
    // Show confirmation modal
    if (confirm('האם אתם בטוחים שברצונכם להשתמש בקופון זה?')) {
        fetch('ajax/use-coupon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                coupon_id: couponId,
                csrf_token: getCsrfToken()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('הקופון נוצל בהצלחה!', 'success');
                // Remove coupon from UI or refresh
                loadDashboardData();
            } else {
                showToast('שגיאה: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error using coupon:', error);
            showToast('שגיאה בשימוש בקופון', 'error');
        });
    }
}

/**
 * Handle quick actions
 */
function handleQuickAction(action) {
    switch (action) {
        case 'book-service':
            window.location.href = 'services.php';
            break;
        case 'browse-store':
            window.location.href = 'store.php';
            break;
        case 'view-events':
            window.location.href = 'events.php';
            break;
        case 'check-coupons':
            window.location.href = 'coupons.php';
            break;
        default:
            console.log('Unknown action:', action);
    }
}

/**
 * Perform search
 */
function performSearch(query) {
    if (query.length < 2) {
        document.getElementById('search-results').innerHTML = '';
        return;
    }
    
    fetch('ajax/search.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query: query,
            type: 'dashboard'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySearchResults(data.results);
        }
    })
    .catch(error => {
        console.error('Search error:', error);
    });
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const container = document.getElementById('search-results');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (results.length === 0) {
        container.innerHTML = '<p class="text-muted">לא נמצאו תוצאות</p>';
        return;
    }
    
    results.forEach(result => {
        const resultElement = document.createElement('div');
        resultElement.className = 'search-result-item';
        resultElement.innerHTML = `
            <a href="${result.url}" class="d-block p-2 text-decoration-none">
                <i class="fas ${result.icon} me-2"></i>
                ${result.title}
                <small class="text-muted d-block">${result.description}</small>
            </a>
        `;
        container.appendChild(resultElement);
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Example chart initialization
    const ctx = document.getElementById('dashboard-chart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני'],
                datasets: [{
                    label: 'הזמנות',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

/**
 * Initialize data tables
 */
function initializeDataTables() {
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        if (typeof $.fn.DataTable !== 'undefined') {
            $(table).DataTable({
                language: {
                    url: 'assets/js/datatables-hebrew.json'
                },
                responsive: true,
                pageLength: 10
            });
        }
    });
}

/**
 * Initialize date pickers
 */
function initializeDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Add Hebrew formatting and validation
        input.addEventListener('change', function() {
            validateDateInput(this);
        });
    });
}

/**
 * Validate date input
 */
function validateDateInput(input) {
    const selectedDate = new Date(input.value);
    const today = new Date();
    
    if (input.hasAttribute('data-min-date')) {
        const minDate = new Date(input.getAttribute('data-min-date'));
        if (selectedDate < minDate) {
            showToast('התאריך שנבחר מוקדם מדי', 'warning');
            input.value = '';
        }
    }
    
    if (input.hasAttribute('data-max-date')) {
        const maxDate = new Date(input.getAttribute('data-max-date'));
        if (selectedDate > maxDate) {
            showToast('התאריך שנבחר מאוחר מדי', 'warning');
            input.value = '';
        }
    }
}

/**
 * Initialize notifications
 */
function initializeNotifications() {
    // Check for new notifications every 30 seconds
    setInterval(checkForNotifications, 30000);
    
    // Mark notifications as read when clicked
    document.addEventListener('click', function(e) {
        if (e.target.closest('.notification-item')) {
            const notificationId = e.target.closest('.notification-item').getAttribute('data-notification-id');
            markNotificationAsRead(notificationId);
        }
    });
}

/**
 * Check for new notifications
 */
function checkForNotifications() {
    fetch('ajax/get-notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                updateNotificationBadge(data.unread_count);
                updateNotificationDropdown(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

/**
 * Update notification dropdown
 */
function updateNotificationDropdown(notifications) {
    const container = document.getElementById('notifications-dropdown');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (notifications.length === 0) {
        container.innerHTML = '<p class="text-muted text-center p-3">אין התראות חדשות</p>';
        return;
    }
    
    notifications.forEach(notification => {
        const notificationElement = createNotificationElement(notification);
        container.appendChild(notificationElement);
    });
}

/**
 * Create notification element
 */
function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.className = `notification-item ${notification.read_at ? '' : 'unread'}`;
    div.setAttribute('data-notification-id', notification.id);
    
    div.innerHTML = `
        <div class="d-flex align-items-start p-3">
            <div class="notification-icon me-3">
                <i class="fas ${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)}"></i>
            </div>
            <div class="notification-content flex-grow-1">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <small class="text-muted">${formatRelativeTime(notification.created_at)}</small>
            </div>
            ${!notification.read_at ? '<div class="unread-indicator"></div>' : ''}
        </div>
    `;
    
    return div;
}

/**
 * Get notification icon
 */
function getNotificationIcon(type) {
    const icons = {
        'booking_confirmed': 'fa-calendar-check',
        'payment_received': 'fa-credit-card',
        'new_coupon': 'fa-gift',
        'event_reminder': 'fa-bell',
        'system': 'fa-cog',
        'default': 'fa-info-circle'
    };
    
    return icons[type] || icons.default;
}

/**
 * Get notification color
 */
function getNotificationColor(type) {
    const colors = {
        'booking_confirmed': 'success',
        'payment_received': 'primary',
        'new_coupon': 'warning',
        'event_reminder': 'info',
        'system': 'secondary',
        'default': 'primary'
    };
    
    return colors[type] || colors.default;
}

/**
 * Mark notification as read
 */
function markNotificationAsRead(notificationId) {
    if (!notificationId) return;
    
    fetch('ajax/mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId,
            csrf_token: getCsrfToken()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (element) {
                element.classList.remove('unread');
                const indicator = element.querySelector('.unread-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }
            
            // Update badge count
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                let count = parseInt(badge.textContent) - 1;
                updateNotificationBadge(Math.max(0, count));
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

/**
 * Update charts with new data
 */
function updateCharts(chartData) {
    if (typeof Chart === 'undefined' || !chartData) return;
    
    // Update existing charts
    Chart.helpers.each(Chart.instances, function(instance) {
        const chart = instance.chart;
        const type = chart.canvas.getAttribute('data-chart-type');
        
        if (chartData[type]) {
            chart.data = chartData[type];
            chart.update();
        }
    });
}

/**
 * Start periodic updates
 */
function startPeriodicUpdates() {
    // Update dashboard data every 5 minutes
    setInterval(() => {
        loadDashboardData();
    }, 5 * 60 * 1000);
    
    // Update time displays every minute
    setInterval(() => {
        updateTimeDisplays();
    }, 60000);
}

/**
 * Update time displays
 */
function updateTimeDisplays() {
    const timeElements = document.querySelectorAll('[data-time]');
    timeElements.forEach(element => {
        const timestamp = element.getAttribute('data-time');
        element.textContent = formatRelativeTime(timestamp);
    });
}

/**
 * Get CSRF token
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info', duration = 5000) {
    // Create toast container if it doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${getToastIcon(type)} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, {
        delay: duration
    });
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Get toast icon based on type
 */
function getToastIcon(type) {
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-circle',
        'info': 'fa-info-circle',
        'default': 'fa-info-circle'
    };
    
    return icons[type] || icons.default;
}

/**
 * Export data functionality
 */
function exportData(type, format = 'csv') {
    const url = `ajax/export-data.php?type=${type}&format=${format}`;
    const link = document.createElement('a');
    link.href = url;
    link.download = `${type}_${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Print functionality
 */
function printDashboard(section = 'all') {
    const printContent = document.getElementById(section === 'all' ? 'main-content' : section);
    if (!printContent) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Sport365 Dashboard</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        .card { break-inside: avoid; }
                    }
                </style>
            </head>
            <body dir="rtl">
                ${printContent.innerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

/**
 * Keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('dashboard-search');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Ctrl/Cmd + R for refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadDashboardData();
        showToast('נתונים מתעדכנים...', 'info', 2000);
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        activeModals.forEach(modal => {
            modal.hide();
        });
    }
});

/**
 * Handle offline/online status
 */
window.addEventListener('online', function() {
    showToast('חיבור לאינטרנט התחדש', 'success', 3000);
    loadDashboardData();
});

window.addEventListener('offline', function() {
    showToast('אין חיבור לאינטרנט', 'warning', 5000);
});

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// Export functions for global use
window.DashboardJS = {
    loadDashboardData,
    useCoupon,
    showToast,
    exportData,
    printDashboard,
    debounce
};