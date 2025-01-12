<?php
session_start();
include '../config/db_connection.php';

if (isset($_GET['category_name'])) {
    $category_name = trim($_GET['category_name']);

    if (!empty($category_name)) {
        // Kiểm tra xem danh mục đã tồn tại chưa
        $checkCategory = "SELECT * FROM category WHERE CategoryName = ?";
        $stmt = $conn->prepare($checkCategory);
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Danh mục đã tồn tại
            header("Location: index.php?msg=exists");
            exit();
        } else {
            // Thêm danh mục vào cơ sở dữ liệu
            $insertCategory = "INSERT INTO category (CategoryName) VALUES (?)";
            $stmt = $conn->prepare($insertCategory);
            $stmt->bind_param("s", $category_name);

            if ($stmt->execute()) {
                // Thêm thành công
                header("Location: index.php?msg=added");
                exit();
            } else {
                // Thêm thất bại
                header("Location: index.php?msg=error");
                exit();
            }
        }
    } else {
        // Tên danh mục trống
        header("Location: index.php?msg=empty");
        exit();
    }
}
?>
