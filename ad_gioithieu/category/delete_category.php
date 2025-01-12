<?php
require '../config/db_connection.php';

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $category_id = $_GET['id'];
    
    // Kiểm tra xem có sản phẩm nào tham chiếu đến CategoryID này không
    $checkProducts = "SELECT COUNT(*) AS count FROM products WHERE CategoryID = ?";
    $stmt = $conn->prepare($checkProducts);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header("Location: index.php?msg=error&reason=linked");
        exit();
    } else {
        $deleteCategory = "DELETE FROM category WHERE CategoryID = ?";
        $stmt = $conn->prepare($deleteCategory);
        $stmt->bind_param("i", $category_id);

        if ($stmt->execute()) {
            header("Location: index.php?msg=deleted");
            exit();
        } else {
            header("Location: index.php?msg=error&reason=failed");
            exit();
        }
    }
}
?>