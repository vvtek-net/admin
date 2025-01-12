<?php
include '../config/db_connection.php';

$img_path = '../img/' . $_SESSION['username'] . '/';
$success_message = ""; // Biến để hiển thị thông báo thành công
$errors = []; // Biến lưu lỗi

// Xử lý khi người dùng nhấn nút tạo sản phẩm
if (isset($_POST['create_products'])) {
    // Nhận giá trị từ form
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_comment = ""; // Nếu cần có giá trị, bạn có thể lấy từ form
    // Tạo thư mục lưu ảnh nếu chưa tồn tại
    if (!is_dir($img_path)) {
        if (!mkdir($img_path, 0777, true) && !is_dir($img_path)) {
            $errors[] = "Không thể tạo thư mục lưu trữ hình ảnh.";
        }
    }

    // Xử lý hình ảnh sản phẩm chính
    $product_img = "";
    if (!empty($_FILES['product_img']['name'])) {
        $tmp_name = $_FILES['product_img']['tmp_name'];
        $size = $_FILES['product_img']['size'];
        $error = $_FILES['product_img']['error'];
        $name = $_FILES['product_img']['name'];

        if ($error === UPLOAD_ERR_OK) {
            if ($size <= 3 * 1024 * 1024) { // Kiểm tra kích thước <= 3MB
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext; // Tạo tên file duy nhất
                $file_dest = $img_path . $file_name;

                // Di chuyển file vào thư mục đích
                if (move_uploaded_file($tmp_name, $file_dest)) {
                    $product_img = $file_name; // Gán tên file để lưu vào database
                } else {
                    $errors[] = "Không thể tải lên hình ảnh sản phẩm.";
                }
            } else {
                $errors[] = "Hình ảnh sản phẩm vượt quá kích thước cho phép (3MB).";
            }
        } else {
            $errors[] = "Lỗi khi tải lên hình ảnh sản phẩm.";
        }
    }

    // Xử lý album hình ảnh
    $product_album = "";
    if (!empty($_FILES['product_album']['name'][0])) {
        $uploaded_images = []; // Mảng lưu tên các file ảnh được upload
        foreach ($_FILES['product_album']['name'] as $key => $name) {
            $tmp_name = $_FILES['product_album']['tmp_name'][$key];
            $size = $_FILES['product_album']['size'][$key];
            $error = $_FILES['product_album']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                if ($size <= 3 * 1024 * 1024) { // Kiểm tra kích thước <= 3MB
                    $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_ext; // Tạo tên file duy nhất
                    $file_dest = $img_path . $file_name;

                    // Di chuyển file vào thư mục đích
                    if (move_uploaded_file($tmp_name, $file_dest)) {
                        $uploaded_images[] = $file_name;
                    } else {
                        $errors[] = "Không thể tải lên file $name.";
                    }
                } else {
                    $errors[] = "File $name vượt quá kích thước cho phép (3MB).";
                }
            } else {
                $errors[] = "Lỗi khi tải lên file $name.";
            }
        }

        // Chuyển mảng thành chuỗi để lưu vào database
        if (!empty($uploaded_images)) {
            $product_album = implode(',', $uploaded_images);
        }
    }

    // Chèn thông tin sản phẩm vào cơ sở dữ liệu
    if (empty($errors)) {
        $sql = "INSERT INTO products (product_name, CategoryID, product_description, product_price, product_img, product_album, product_comment, create_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sisssss",
            $product_name,
            $category_id,
            $product_description,
            $product_price,
            $product_img,
            $product_album,
            $product_comment
        );

        if ($stmt->execute()) {
            $success_message = "Sản phẩm mới đã được tạo thành công!";
        } else {
            $errors[] = "Không thể tạo sản phẩm. Vui lòng thử lại.";
        }
    }
}

// Hiển thị thông báo lỗi nếu có
if (!empty($errors)) {
    $error_message = implode('<br>', $errors); // Nối tất cả lỗi thành chuỗi
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Tạo sản phẩm thất bại',
                html: '$error_message',
                confirmButtonText: 'OK'
            });
        });
    </script>";
}

// Hiển thị thông báo thành công nếu có
if (!empty($success_message)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: '$success_message',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Chuyển hướng về trang danh sách sản phẩm hoặc làm mới trang
                    window.location.href = 'products_list.php'; // Thay bằng URL phù hợp
                }
            });
        });
    </script>";
}
?>