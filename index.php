<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: modules/auth/login.php");
    exit();
}

// Include header
include_once 'includes/header.php';
// Include sidebar
include_once 'includes/sidebar.php';

// Default page is dashboard
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$module = isset($_GET['module']) ? $_GET['module'] : '';

// Load appropriate page based on URL parameters
if ($module && $page) {
    $file_path = "modules/{$module}/{$page}.php";
    if (file_exists($file_path)) {
        include_once $file_path;
    } else {
        include_once 'modules/dashboard/dashboard.php';
    }
} else if ($page === 'dashboard') {
    include_once 'modules/dashboard/dashboard.php';
} else {
    include_once 'modules/dashboard/dashboard.php';
}

// Include footer
include_once 'includes/footer.php';
?>
