<?php

session_start();
if(isset($_SESSION['db_connected'])){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $db_name = $_SESSION['db_connected'];   
    // Kết nối đến database
    $conn = new mysqli($servername, $username, $password, $db_name);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
}
else{
    header('location: https://id.truongthanhweb.com');
}
?>
