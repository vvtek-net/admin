<?php
session_start();
require_once './config/db_connection.php';

// Kiểm tra người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['username']) || !isset($_SESSION['role_id']) || !isset($_SESSION['db_connected'])) {
    die("Bạn chưa đăng nhập. Vui lòng <a href='/login.php'>đăng nhập</a>.");
}

// Kiểm tra quyền admin
if ($_SESSION['role_id'] !== 'admin') {
    die("Bạn không có quyền truy cập trang này.");
}

// Kiểm tra và kết nối với database tương ứng
$expected_db = 'db_banhang'; // Database tương ứng cho ad_banhang
if ($_SESSION['db_connected'] !== $expected_db) {
    die("Kết nối database không hợp lệ.");
}

// Kết nối database
$conn = connectDatabase($expected_db);
?>
