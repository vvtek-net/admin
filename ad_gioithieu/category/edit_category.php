<?php
include '../config/db_connection.php';

// Đọc dữ liệu từ yêu cầu AJAX
$data = json_decode(file_get_contents('php://input'), true);

$category_id = $data['CategoryID'];
$category_name = $data['CategoryName'];

// Kiểm tra xem danh mục có tồn tại không
$checkCategory = "SELECT COUNT(*) AS count FROM category WHERE CategoryID = ?";
$stmt = $conn->prepare($checkCategory);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Cập nhật danh mục
    $updateCategory = "UPDATE category SET CategoryName = ? WHERE CategoryID = ?";
    $stmt = $conn->prepare($updateCategory);
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Danh mục không tồn tại.']);
}

$conn->close();
?>
