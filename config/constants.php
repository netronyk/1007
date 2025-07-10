<?php
/**
 * Sport365 - System Constants
 * config/constants.php
 * קבועי מערכת וערכים קבועים
 */

// מניעת גישה ישירה לקובץ
if (!defined('SPORT365_ROOT')) {
    exit('Direct access denied');
}

// ===== גרסת מערכת =====
define('SPORT365_VERSION', '1.0.0');
define('SPORT365_BUILD', '2025010801');
define('SPORT365_CODENAME', 'Genesis');

// ===== סטטוסי משתמשים =====
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');
define('USER_STATUS_PENDING', 'pending');

// ===== תפקידי משתמשים =====
define('ROLE_MEMBER', 'member');
define('ROLE_STORE_MANAGER', 'store_manager');
define('ROLE_PROPERTY_MANAGER', 'property_manager');
define('ROLE_SERVICE_PROVIDER', 'service_provider');
define('ROLE_SALES_AGENT', 'sales_agent');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// ===== סוגי חברות =====
define('MEMBERSHIP_BASIC', 'basic');
define('MEMBERSHIP_PREMIUM', 'premium');
define('MEMBERSHIP_VIP', 'vip');

// ===== סטטוסי עסקים =====
define('BUSINESS_STATUS_ACTIVE', 'active');
define('BUSINESS_STATUS_INACTIVE', 'inactive');
define('BUSINESS_STATUS_PENDING', 'pending');
define('BUSINESS_STATUS_SUSPENDED', 'suspended');

// ===== סוגי מנויים עסקיים =====
define('SUBSCRIPTION_BASIC', 'basic');
define('SUBSCRIPTION_COUPONS', 'coupons');
define('SUBSCRIPTION_STORE', 'store');
define('SUBSCRIPTION_SERVICES', 'services');
define('SUBSCRIPTION_TOURISM', 'tourism');
define('SUBSCRIPTION_PREMIUM', 'premium');

// ===== סטטוסי קופונים =====
define('COUPON_STATUS_ACTIVE', 'active');
define('COUPON_STATUS_INACTIVE', 'inactive');
define('COUPON_STATUS_EXPIRED', 'expired');

// ===== סוגי הנחות =====
define('DISCOUNT_PERCENTAGE', 'percentage');
define('DISCOUNT_FIXED_AMOUNT', 'fixed_amount');
define('DISCOUNT_FREE_SHIPPING', 'free_shipping');

// ===== סטטוסי הזמנות =====
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_PROCESSING', 'processing');
define('ORDER_STATUS_SHIPPED', 'shipped');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');
define('ORDER_STATUS_REFUNDED', 'refunded');

// ===== סטטוסי תשלום =====
define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_PAID', 'paid');
define('PAYMENT_STATUS_PARTIALLY_PAID', 'partially_paid');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');
define('PAYMENT_STATUS_CANCELLED', 'cancelled');

// ===== אמצעי תשלום =====
define('PAYMENT_METHOD_TRANZILA', 'tranzila');
define('PAYMENT_METHOD_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_METHOD_CHECK', 'check');
define('PAYMENT_METHOD_CASH', 'cash');

// ===== סטטוסי מוצרים =====
define('PRODUCT_STATUS_ACTIVE', 'active');
define('PRODUCT_STATUS_INACTIVE', 'inactive');
define('PRODUCT_STATUS_DRAFT', 'draft');

// ===== סטטוסי מלאי =====
define('STOCK_STATUS_IN_STOCK', 'in_stock');
define('STOCK_STATUS_OUT_OF_STOCK', 'out_of_stock');
define('STOCK_STATUS_BACKORDER', 'backorder');

// ===== סוגי נכסים =====
define('PROPERTY_TYPE_HOTEL', 'hotel');
define('PROPERTY_TYPE_ZIMMER', 'zimmer');
define('PROPERTY_TYPE_VILLA', 'villa');
define('PROPERTY_TYPE_APARTMENT', 'apartment');
define('PROPERTY_TYPE_RESORT', 'resort');

// ===== סטטוסי הזמנות נופש =====
define('BOOKING_STATUS_PENDING', 'pending');
define('BOOKING_STATUS_CONFIRMED', 'confirmed');
define('BOOKING_STATUS_CHECKED_IN', 'checked_in');
define('BOOKING_STATUS_CHECKED_OUT', 'checked_out');
define('BOOKING_STATUS_CANCELLED', 'cancelled');

// ===== סוגי אירועים =====
define('EVENT_TYPE_SPORTS', 'sports');
define('EVENT_TYPE_FITNESS', 'fitness');
define('EVENT_TYPE_WELLNESS', 'wellness');
define('EVENT_TYPE_COMPETITION', 'competition');
define('EVENT_TYPE_WORKSHOP', 'workshop');

// ===== סטטוסי אירועים =====
define('EVENT_STATUS_DRAFT', 'draft');
define('EVENT_STATUS_PUBLISHED', 'published');
define('EVENT_STATUS_CANCELLED', 'cancelled');
define('EVENT_STATUS_COMPLETED', 'completed');

// ===== רמות כישור =====
define('SKILL_LEVEL_BEGINNER', 'beginner');
define('SKILL_LEVEL_INTERMEDIATE', 'intermediate');
define('SKILL_LEVEL_ADVANCED', 'advanced');
define('SKILL_LEVEL_ALL', 'all');

// ===== סוגי שירותים =====
define('SERVICE_TYPE_PERSONAL_TRAINING', 'personal_training');
define('SERVICE_TYPE_GROUP_SESSION', 'group_session');
define('SERVICE_TYPE_CONSULTATION', 'consultation');
define('SERVICE_TYPE_WORKSHOP', 'workshop');
define('SERVICE_TYPE_ONLINE_SESSION', 'online_session');

// ===== סוגי מיקום שירות =====
define('LOCATION_TYPE_PROVIDER', 'provider_location');
define('LOCATION_TYPE_CLIENT', 'client_location');
define('LOCATION_TYPE_ONLINE', 'online');
define('LOCATION_TYPE_FLEXIBLE', 'flexible');

// ===== העדפות מגדר =====
define('GENDER_PREFERENCE_MALE', 'male');
define('GENDER_PREFERENCE_FEMALE', 'female');
define('GENDER_PREFERENCE_MIXED', 'mixed');
define('GENDER_PREFERENCE_NO_PREFERENCE', 'no_preference');

// ===== סטטוסי הזמנות שירותים =====
define('SERVICE_BOOKING_PENDING', 'pending');
define('SERVICE_BOOKING_CONFIRMED', 'confirmed');
define('SERVICE_BOOKING_IN_PROGRESS', 'in_progress');
define('SERVICE_BOOKING_COMPLETED', 'completed');
define('SERVICE_BOOKING_CANCELLED', 'cancelled');
define('SERVICE_BOOKING_NO_SHOW', 'no_show');

// ===== סוגי חבילות שירותים =====
define('PACKAGE_TYPE_SESSIONS', 'sessions');
define('PACKAGE_TYPE_DURATION', 'duration');
define('PACKAGE_TYPE_UNLIMITED', 'unlimited');

// ===== סטטוסי חבילות =====
define('PACKAGE_STATUS_ACTIVE', 'active');
define('PACKAGE_STATUS_EXPIRED', 'expired');
define('PACKAGE_STATUS_CANCELLED', 'cancelled');
define('PACKAGE_STATUS_FULLY_USED', 'fully_used');

// ===== שיטות אישור =====
define('CONFIRMATION_METHOD_QR_SCAN', 'qr_scan');
define('CONFIRMATION_METHOD_MANUAL', 'manual');
define('CONFIRMATION_METHOD_AUTO', 'auto');

// ===== דירוגי איכות =====
define('QUALITY_RATING_EXCELLENT', 'excellent');
define('QUALITY_RATING_GOOD', 'good');
define('QUALITY_RATING_AVERAGE', 'average');
define('QUALITY_RATING_POOR', 'poor');

// ===== סוגי עסקאות =====
define('TRANSACTION_TYPE_SUBSCRIPTION', 'subscription');
define('TRANSACTION_TYPE_COMMISSION', 'commission');
define('TRANSACTION_TYPE_BOOKING', 'booking');
define('TRANSACTION_TYPE_ORDER', 'order');
define('TRANSACTION_TYPE_REFUND', 'refund');

// ===== סוגי עמלות סוכנים =====
define('COMMISSION_TYPE_SIGNUP', 'signup');
define('COMMISSION_TYPE_MONTHLY_RECURRING', 'monthly_recurring');
define('COMMISSION_TYPE_TRANSACTION', 'transaction');

// ===== תדירות תשלום =====
define('PAYOUT_FREQUENCY_DAILY', 'daily');
define('PAYOUT_FREQUENCY_WEEKLY', 'weekly');
define('PAYOUT_FREQUENCY_MONTHLY', 'monthly');

// ===== סטטוסי ביקורות =====
define('REVIEW_STATUS_PENDING', 'pending');
define('REVIEW_STATUS_APPROVED', 'approved');
define('REVIEW_STATUS_REJECTED', 'rejected');

// ===== סוגי התראות =====
define('NOTIFICATION_TYPE_BOOKING', 'booking');
define('NOTIFICATION_TYPE_PAYMENT', 'payment');
define('NOTIFICATION_TYPE_REVIEW', 'review');
define('NOTIFICATION_TYPE_SYSTEM', 'system');
define('NOTIFICATION_TYPE_PROMOTION', 'promotion');

// ===== רמות לוג =====
define('LOG_LEVEL_DEBUG', 'debug');
define('LOG_LEVEL_INFO', 'info');
define('LOG_LEVEL_WARNING', 'warning');
define('LOG_LEVEL_ERROR', 'error');
define('LOG_LEVEL_CRITICAL', 'critical');

// ===== קודי שגיאה =====
define('ERROR_CODE_GENERAL', 1000);
define('ERROR_CODE_DATABASE', 1001);
define('ERROR_CODE_AUTHENTICATION', 1002);
define('ERROR_CODE_AUTHORIZATION', 1003);
define('ERROR_CODE_VALIDATION', 1004);
define('ERROR_CODE_NOT_FOUND', 1005);
define('ERROR_CODE_PAYMENT', 1006);
define('ERROR_CODE_FILE_UPLOAD', 1007);
define('ERROR_CODE_EMAIL', 1008);
define('ERROR_CODE_SMS', 1009);
define('ERROR_CODE_EXTERNAL_API', 1010);

// ===== הודעות מערכת =====
define('MESSAGE_SUCCESS', 'success');
define('MESSAGE_ERROR', 'error');
define('MESSAGE_WARNING', 'warning');
define('MESSAGE_INFO', 'info');

// ===== ימי השבוע =====
define('DAY_SUNDAY', 0);
define('DAY_MONDAY', 1);
define('DAY_TUESDAY', 2);
define('DAY_WEDNESDAY', 3);
define('DAY_THURSDAY', 4);
define('DAY_FRIDAY', 5);
define('DAY_SATURDAY', 6);

// ===== תוכן מערכת =====
define('CONTENT_STATUS_DRAFT', 'draft');
define('CONTENT_STATUS_PUBLISHED', 'published');
define('CONTENT_STATUS_ARCHIVED', 'archived');

// ===== סוגי תוכן =====
define('CONTENT_TYPE_ARTICLE', 'article');
define('CONTENT_TYPE_PAGE', 'page');
define('CONTENT_TYPE_NEWS', 'news');
define('CONTENT_TYPE_BLOG', 'blog');

// ===== פורמטי תאריך זמן =====
define('FORMAT_DATE_DISPLAY', 'd/m/Y');
define('FORMAT_DATE_DATABASE', 'Y-m-d');
define('FORMAT_TIME_DISPLAY', 'H:i');
define('FORMAT_DATETIME_DISPLAY', 'd/m/Y H:i');
define('FORMAT_DATETIME_DATABASE', 'Y-m-d H:i:s');

// ===== הגדרות קבצים =====
define('FILE_TYPE_IMAGE', 'image');
define('FILE_TYPE_DOCUMENT', 'document');
define('FILE_TYPE_VIDEO', 'video');
define('FILE_TYPE_AUDIO', 'audio');

// ===== גדלי תמונות =====
define('IMAGE_SIZE_THUMBNAIL', 'thumbnail');
define('IMAGE_SIZE_SMALL', 'small');
define('IMAGE_SIZE_MEDIUM', 'medium');
define('IMAGE_SIZE_LARGE', 'large');
define('IMAGE_SIZE_ORIGINAL', 'original');

// ===== מערך רמות הרשאות =====
$PERMISSIONS_HIERARCHY = [
    ROLE_SUPER_ADMIN => 100,
    ROLE_ADMIN => 80,
    ROLE_SALES_AGENT => 60,
    ROLE_SERVICE_PROVIDER => 50,
    ROLE_STORE_MANAGER => 40,
    ROLE_PROPERTY_MANAGER => 40,
    ROLE_MEMBER => 10
];

// ===== מערך סטטוסים עם תיאורים =====
$STATUS_DESCRIPTIONS = [
    'active' => 'פעיל',
    'inactive' => 'לא פעיל',
    'pending' => 'ממתין לאישור',
    'suspended' => 'מושעה',
    'cancelled' => 'בוטל',
    'completed' => 'הושלם',
    'processing' => 'בטיפול',
    'failed' => 'נכשל',
    'approved' => 'אושר',
    'rejected' => 'נדחה'
];

// ===== פונקציות עזר =====

/**
 * קבלת תיאור סטטוס
 */
function getStatusDescription($status) {
    global $STATUS_DESCRIPTIONS;
    return $STATUS_DESCRIPTIONS[$status] ?? $status;
}

/**
 * בדיקת רמת הרשאה
 */
function hasPermissionLevel($userRole, $requiredRole) {
    global $PERMISSIONS_HIERARCHY;
    $userLevel = $PERMISSIONS_HIERARCHY[$userRole] ?? 0;
    $requiredLevel = $PERMISSIONS_HIERARCHY[$requiredRole] ?? 100;
    return $userLevel >= $requiredLevel;
}

/**
 * קבלת כל התפקידים הזמינים
 */
function getAllRoles() {
    return [
        ROLE_MEMBER,
        ROLE_STORE_MANAGER,
        ROLE_PROPERTY_MANAGER,
        ROLE_SERVICE_PROVIDER,
        ROLE_SALES_AGENT,
        ROLE_ADMIN,
        ROLE_SUPER_ADMIN
    ];
}

/**
 * קבלת כל הסטטוסים הזמינים
 */
function getAllStatuses() {
    return [
        USER_STATUS_ACTIVE,
        USER_STATUS_INACTIVE,
        USER_STATUS_SUSPENDED,
        USER_STATUS_PENDING
    ];
}
?>