<?php

session_start();
if(isset($_SESSION['db_connected']) && $_SESSION['website_type'] == 'gioi-thieu'){
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
    if($_SESSION['website_type'] != 'gioi-thieu')
    {
    session_destroy();
    header('location: https://id.truongthanhweb.com?msg=404');
    exit();
    }

    else
    {
    session_destroy();
    header('location: https://id.truongthanhweb.com');
    exit();
    }
}
?>
