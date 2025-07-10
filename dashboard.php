<?php
// File Path: dashboard.php

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';
require_once 'includes/User.php';

$auth = new Auth();

// בדיקה האם המשתמש מחובר
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = new User();
$current_user = $user->getCurrentUser();
$user_roles = $user->getUserRoles($current_user['id']);

// קביעת סוג הדשבורד לפי התפקיד
$dashboard_type = 'member'; // ברירת מחדל
if (in_array('super_admin', $user_roles) || in_array('admin', $user_roles)) {
    $dashboard_type = 'admin';
} elseif (in_array('service_provider', $user_roles)) {
    $dashboard_type = 'provider';
} elseif (in_array('store_manager', $user_roles)) {
    $dashboard_type = 'store';
} elseif (in_array('property_manager', $user_roles)) {
    $dashboard_type = 'property';
} elseif (in_array('sales_agent', $user_roles)) {
    $dashboard_type = 'agent';
}

// הפניה לדשבורד המתאים
switch ($dashboard_type) {
    case 'admin':
        include 'dashboards/admin-dashboard.php';
        break;
    case 'provider':
        include 'dashboards/provider-dashboard.php';
        break;
    case 'store':
        include 'dashboards/store-dashboard.php';
        break;
    case 'property':
        include 'dashboards/property-dashboard.php';
        break;
    case 'agent':
        include 'dashboards/agent-dashboard.php';
        break;
    default:
        include 'dashboards/member-dashboard.php';
        break;
}
?>