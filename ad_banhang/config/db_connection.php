<?php
// config.php - Kết nối database theo db_name

function connectDatabase($db_name) {
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Kết nối đến database
    $conn = new mysqli($servername, $username, $password, $db_name);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    return $conn;
}
?>
