<?php
require '../config/db_connection.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Kiểm tra xem sản phẩm này có tồn tại trong bảng products không
    $checkProduct = "SELECT COUNT(*) AS count FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($checkProduct);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Xóa sản phẩm
        $deleteProduct = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($deleteProduct);
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            header("Location: products_list.php?msg=deleted");
            exit();
        } else {
            header("Location: products_list.php?msg=error&reason=failed");
            exit();
        }
    } else {
        // Nếu sản phẩm không tồn tại
        header("Location: products_list.php?msg=error&reason=not_found");
        exit();
    }
}
?>
