<?php
include '../config/db_connection.php';

header('Content-Type: application/json');

try {
    // Lấy dữ liệu từ yêu cầu AJAX
    $data = json_decode(file_get_contents('php://input'), true);
    $image_to_delete = $data['image'];
    $product_id = $data['product_id'];

    // Lấy thông tin sản phẩm từ database
    $sql = "SELECT product_album FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $album_images = explode(',', $product['product_album']);
        $updated_album = array_filter($album_images, function ($image) use ($image_to_delete) {
            return trim($image) !== $image_to_delete;
        });

        // Cập nhật lại album trong cơ sở dữ liệu
        $new_album = implode(',', $updated_album);
        $update_sql = "UPDATE products SET product_album = ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_album, $product_id);
        $update_stmt->execute();

        // Xóa file ảnh khỏi thư mục
        $image_path = '../img/' . $_SESSION['username'] . '/' . $image_to_delete;
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Không tìm thấy sản phẩm."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
