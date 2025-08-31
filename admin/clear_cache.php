<?php
require_once '../utils/cache.php';

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Clear all cache
Cache::clear();

// Redirect back to performance analysis page
header('Location: analyze_performance.php?cache_cleared=1');
exit;
?>