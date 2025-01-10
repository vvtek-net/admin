<?php
include '../config/db_connection.php';

$img_path = '../img/' . $_SESSION['username'] . '/';
$success_message = ""; // Biến để hiển thị thông báo thành công
$errors = []; // Biến lưu lỗi

// Kiểm tra nếu ID sản phẩm được truyền qua URL và ID là một số hợp lệ
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Lấy thông tin sản phẩm từ database
    $sql = "SELECT 
    p.product_id,
    p.product_name,
    p.CategoryID,
    c.CategoryName,
    p.product_description,
    p.product_price,
    p.product_rate,
    p.product_img,
    p.product_album,
    p.product_comment,
    p.create_at,
    p.update_at
    FROM products p
    LEFT JOIN category c ON p.CategoryID = c.CategoryID
    WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Sản phẩm không tồn tại!";
        exit();
    }
} else {
    echo "ID sản phẩm không hợp lệ!";
    exit();
}

// Xử lý khi người dùng nhấn nút cập nhật
if (isset($_POST['update_product'])) {
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
                    // Xóa hình ảnh cũ nếu có
                    if (!empty($product['product_img']) && file_exists($img_path . $product['product_img'])) {
                        unlink($img_path . $product['product_img']);
                    }
                    // Gán tên file mới vào biến để cập nhật database
                    $product_img = $file_name;
                } else {
                    $errors[] = "Không thể tải lên hình ảnh sản phẩm.";
                }
            } else {
                $errors[] = "Hình ảnh sản phẩm vượt quá kích thước cho phép (3MB).";
            }
        } else {
            $errors[] = "Lỗi khi tải lên hình ảnh sản phẩm.";
        }
    } else {
        // Nếu không upload ảnh mới, giữ nguyên ảnh cũ
        $product_img = $product['product_img'];
    }

    // Xử lý album hình ảnh
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

        // Lấy album hiện tại từ database và gộp với ảnh mới
        $existing_album = !empty($product['product_album']) ? explode(',', $product['product_album']) : [];
        $new_album = array_merge($existing_album, $uploaded_images);
        $product_album = implode(',', $new_album); // Chuyển mảng thành chuỗi để lưu vào database
    } else {
        // Nếu không upload ảnh mới, giữ nguyên album cũ
        $product_album = $product['product_album'];
    }

    // Cập nhật thông tin sản phẩm trong cơ sở dữ liệu
    $sql = "UPDATE products 
            SET product_name = ?, 
                CategoryID = ?, 
                product_description = ?, 
                product_price = ?, 
                product_img = ?, 
                product_album = ?, 
                product_comment = ? 
            WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sisssssi",
        $product_name,
        $category_id,
        $product_description,
        $product_price,
        $product_img,
        $product_album,
        $product_comment,
        $product_id
    );

    if ($stmt->execute()) {
        $success_message = "Sản phẩm đã được cập nhật thành công!";
    } else {
        $errors[] = "Cập nhật thất bại. Vui lòng thử lại!";
    }
}

// Hiển thị thông báo lỗi nếu có
if (!empty($errors)) {
    $error_message = implode('<br>', $errors); // Nối tất cả lỗi thành chuỗi
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Cập nhật thất bại',
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
                    // Làm mới trang hiện tại để xóa trạng thái POST
                    window.location.href = '" . $_SERVER['REQUEST_URI'] . "';
                }
            });
        });
    </script>";
}

?>





<!DOCTYPE html>
<html lang="vi">


<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Chỉnh Sửa Danh Mục</title>

    <link rel="icon" type="image/ico" href="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/favicon.ico">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap1.min.css" />
    <!-- themefy CSS -->
    <link rel="stylesheet" href="../vendors/themefy_icon/themify-icons.css" />
    <!-- swiper slider CSS -->
    <link rel="stylesheet" href="../vendors/swiper_slider/css/swiper.min.css" />
    <!-- select2 CSS -->
    <link rel="stylesheet" href="../vendors/select2/css/select2.min.css" />
    <!-- select2 CSS -->
    <link rel="stylesheet" href="../vendors/niceselect/css/nice-select.css" />
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="../vendors/owl_carousel/css/owl.carousel.css" />
    <!-- gijgo css -->
    <link rel="stylesheet" href="../vendors/gijgo/gijgo.min.css" />
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="../vendors/font_awesome/css/all.min.css" />
    <link rel="stylesheet" href="../vendors/tagsinput/tagsinput.css" />
    <!-- datatable CSS -->
    <link rel="stylesheet" href="../vendors/datatable/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="../vendors/datatable/css/responsive.dataTables.min.css" />
    <link rel="stylesheet" href="../vendors/datatable/css/buttons.dataTables.min.css" />
    <!-- text editor css -->
    <link rel="stylesheet" href="../vendors/text_editor/summernote-bs4.css" />
    <!-- morris css -->
    <link rel="stylesheet" href="../vendors/morris/morris.css">
    <!-- metarial icon css -->
    <link rel="stylesheet" href="../vendors/material_icon/material-icons.css" />

    <!-- menu css  -->
    <link rel="stylesheet" href="../css/metisMenu.css">
    <!-- style CSS -->
    <link rel="stylesheet" href="../css/style1.css" />
    <link rel="stylesheet" href="../css/colors/default.css" id="colorSkinCSS">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CKEDITOR -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- font-awsome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="crm_body_bg">

<!-- sidebar  -->
 <!-- sidebar part here -->
 <nav class="sidebar">
    <div class="logo d-flex justify-content-between">
        <a href=".././index.php"><img src="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/logo-header-4.png" alt=""></a>
        <div class="sidebar_close_icon d-lg-none">
            <i class="ti-close"></i>
        </div>
    </div>
    <ul id="sidebar_menu">
        <li class="">
          <a class=""  href="../../index.php"  aria-expanded="false">
          <!-- <i class="fas fa-th"></i> -->
          <img src="../img/menu-icon/dashboard.svg" alt="">
            <span>Dashboard</span>
          </a>

        </li>
        <li class="">
          <a class=""  href="../category/index.php"  aria-expanded="false">
          <!-- <i class="fas fa-th"></i> -->
          <img src="../img/menu-icon/dashboard.svg" alt="">
            <span>Danh Mục</span>
          </a>

        </li>
        <li class="mm-active">
          <a   class="" href="../products/index.php" aria-expanded="true">
            <img src="../img/menu-icon/2.svg" alt="">
            <span>Sản Phẩm</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="../#" aria-expanded="false">
            <img src="../img/menu-icon/3.svg" alt="">
            <span>Tin Tức</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="../#" aria-expanded="false">
            <img src="../img/menu-icon/4.svg" alt="">
            <span>Đánh giá</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="../#" aria-expanded="false">
            <img src="../img/menu-icon/5.svg" alt="">
            <span>SEO Từ Khóa</span>
          </a>
        </li>
      </ul>
    
</nav>
<!-- sidebar part end -->
<!--/ sidebar  -->

<section class="main_content dashboard_part">
        <!-- menu  -->
        <div class="container-fluid g-0">
            <div class="row">
                <div class="col-lg-12 p-0 ">
                    <div class="header_iner d-flex justify-content-between align-items-center">
                        <div class="sidebar_icon d-lg-none">
                            <i class="ti-menu"></i>
                        </div>
                        <div class="serach_field-area">
                                <div class="search_inner">
                                    <form action="#">
                                        <div class="search_field">
                                            <input type="text" placeholder="Search here..." >
                                        </div>
                                        <button type="submit"> <img src="../img/icon/icon_search.svg" alt=""> </button>
                                    </form>
                                </div>
                            </div>
                        <div class="header_right d-flex justify-content-between align-items-center">
                            <div class="header_notification_warp d-flex align-items-center">
                                <li>
                                    <a class="bell_notification_clicker" href="../#"> <img src="../img/icon/bell.svg" alt="">
                                        <span>04</span>
                                    </a>
                                    <!-- Menu_NOtification_Wrap  -->
                                <div class="Menu_NOtification_Wrap">
                                    <div class="notification_Header">
                                        <h4>Notifications</h4>
                                    </div>
                                    <div class="Notification_body">
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/2.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>Cool Directory </h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/4.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>Awesome packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/3.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>what a packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/2.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>Cool Directory </h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/4.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>Awesome packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="../#"><img src="../img/staf/3.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="../#"><h5>what a packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="nofity_footer">
                                        <div class="submit_button text-center pt_20">
                                            <a href="../#" class="btn_1">See More</a>
                                        </div>
                                    </div>
                                </div>
                                <!--/ Menu_NOtification_Wrap  -->
                                </li>
                                <li>
                                    <a class="CHATBOX_open" href="../#"> <img src="../img/icon/msg.svg" alt="">  <span>01</span> </a>
                                </li>
                            </div>
                            <div class="profile_info">
                                <img src="../img/client_img-1.png" alt="#">
                                <div class="profile_info_iner">
                                    <div class="profile_author_name">
                                        <p>Xin Chào </p>
                                        <h5><?php echo$_SESSION['username']; ?></h5>
                                    </div>
                                    <div class="profile_info_details">
                                        <a href="../#">My Profile </a>
                                        <a href="../#">Settings</a>
                                        <a href="../logout.php">Log Out </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ menu  -->
    <div class="main_content_iner ">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
              
                <div class="col-12">
                    <div class="QA_section">
                        

                        <div class="QA_table mb_30">
                            <div class="table-responsive">
                                <div class="container mt-5">
                                    <h2 class="text-center mb-4">Cập nhật sản phẩm</h2>
                                    <form action="" method="POST" class="p-4 border rounded shadow" enctype="multipart/form-data">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">Tên Sản Phẩm:</label>
                                            <input 
                                                type="text" 
                                                id="product_name" 
                                                name="product_name" 
                                                value="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                                class="form-control" 
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="CategoryID" class="form-label">Danh Mục:</label>
                                            <select name="CategoryID" id="CategoryID" class="form-control">
                                                <option value="">Chọn danh mục</option>
                                                <?php
                                                // Truy vấn danh sách danh mục từ bảng category
                                                $categorySql = "SELECT CategoryID, CategoryName FROM category";
                                                $categoryResult = $conn->query($categorySql);

                                                // Kiểm tra và hiển thị danh mục trong dropdown list
                                                if ($categoryResult->num_rows > 0) {
                                                    while ($row = $categoryResult->fetch_assoc()) {
                                                        // Kiểm tra nếu danh mục đã được chọn
                                                        $selected = ($row['CategoryID'] == $product['CategoryID']) ? 'selected' : '';
                                                        echo '<option value="' . $row['CategoryID'] . '" ' . $selected . '>' . $row['CategoryName'] . '</option>';
                                                    }
                                                } else {
                                                    echo '<option value="">Không có danh mục</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>


                                        <div class="mb-3">
                                            <label for="product_description" class="form-label">Mô Tả:</label>
                                            <textarea 
                                                id="product_description" 
                                                name="product_description" 
                                                class="form-control" 
                                                rows="4" 
                                                required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product_price" class="form-label">Giá Sản Phẩm:</label>
                                            <input 
                                                type="text" 
                                                id="product_price" 
                                                name="product_price" 
                                                value="<?php echo htmlspecialchars($product['product_price']); ?>" 
                                                class="form-control" 
                                                required>
                                        </div>
                                            <div class="mb-3">
                                                <label for="product_img" class="form-label">Hình Ảnh Sản Phẩm:</label>
                                                <input 
                                                    type="file" 
                                                    id="product_img" 
                                                    name="product_img" 
                                                    class="form-control" 
                                                    accept="image/*">
                                            </div>

                                            <div class="mb-3">
                                                <label for="product_img" class="form-label">Ảnh Hiện Tại:</label>
                                                <img src="<?php echo htmlspecialchars($img_path . $product['product_img']); ?>" alt="Ảnh sản phẩm" style="width: 150px; height: 150px; margin-top: 15px; border-radius: 15px;">
                                            </div>


                                        <!-- Upload album -->
                                         
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

                                            <div class="mb-3">
                                                <label for="product_album" class="form-label">Album Sản Phẩm:</label>
                                                <input 
                                                    type="file" 
                                                    id="product_album" 
                                                    name="product_album[]" 
                                                    class="form-control" 
                                                    accept="image/*" 
                                                    multiple>
                                                <h6 class="text-muted">Bạn có thể chọn nhiều ảnh. Mỗi ảnh không quá <span style="color: red; font-weight: 600;">3MB</span>.</h6>
                                            </div>

                                            <button type="submit" class="btn btn-primary" name="update_product">Cập nhật sản phẩm</button>
                                            <a href="index.php" class="btn btn-secondary">Danh Sách Sản Phẩm</a>
                                        </form>

                                        <!-- Upload album -->


                                        <div class="mb-3">
                                            <label for="product_album" class="form-label">Album Sản Phẩm:</label>
                                            <div class="d-flex flex-wrap">
                                                <?php
                                                // Tách các tên ảnh trong mảng album
                                                if (!empty($product['product_album'])) {
                                                    $album_images = explode(',', $product['product_album']);
                                                    foreach ($album_images as $image) {
                                                        $image = htmlspecialchars(trim($image));
                                                        $image_path = '../img/' . $_SESSION['username'] . '/' . $image;

                                                        echo "<div class='position-relative me-3 mb-3' style='width: 150px; height: 150px;'>
                                                                <img src='$image_path' alt='Ảnh trong album' style='width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: 8px;'>
                                                                <button class='btn btn-danger btn-sm position-absolute top-0 end-0 delete-image-btn' 
                                                                        data-image='$image' 
                                                                        style='border-radius: 50%; padding: 5px 8px;' 
                                                                        title='Xóa ảnh'>
                                                                    <i class='fa-solid fa-trash'></i>
                                                                </button>
                                                            </div>";
                                                    }
                                                } else {
                                                    echo "<p>Không có ảnh trong album.</p>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- main content part end -->

<!-- footer  -->
<!-- jquery slim -->
<script src="../js/jquery1-3.4.1.min.js"></script>
<!-- popper js -->
<script src="../js/popper1.min.js"></script>
<!-- bootstarp js -->
<script src="../js/bootstrap1.min.js"></script>
<!-- sidebar menu  -->
<script src="../js/metisMenu.js"></script>
<!-- waypoints js -->
<script src="../vendors/count_up/jquery.waypoints.min.js"></script>
<!-- waypoints js -->
<script src="../vendors/chartlist/Chart.min.js"></script>
<!-- counterup js -->
<script src="../vendors/count_up/jquery.counterup.min.js"></script>
<!-- swiper slider js -->
<script src="../vendors/swiper_slider/js/swiper.min.js"></script>
<!-- nice select -->
<script src="../vendors/niceselect/js/jquery.nice-select.min.js"></script>
<!-- owl carousel -->
<script src="../vendors/owl_carousel/js/owl.carousel.min.js"></script>
<!-- gijgo css -->
<script src="../vendors/gijgo/gijgo.min.js"></script>
<!-- responsive table -->
<script src="../vendors/datatable/js/jquery.dataTables.min.js"></script>
<script src="../vendors/datatable/js/dataTables.responsive.min.js"></script>
<script src="../vendors/datatable/js/dataTables.buttons.min.js"></script>
<script src="../vendors/datatable/js/buttons.flash.min.js"></script>
<script src="../vendors/datatable/js/jszip.min.js"></script>
<script src="../vendors/datatable/js/pdfmake.min.js"></script>
<script src="../vendors/datatable/js/vfs_fonts.js"></script>
<script src="../vendors/datatable/js/buttons.html5.min.js"></script>
<script src="../vendors/datatable/js/buttons.print.min.js"></script>

<script src="../js/chart.min.js"></script>
<!-- progressbar js -->
<script src="../vendors/progressbar/jquery.barfiller.js"></script>
<!-- tag input -->
<script src="../vendors/tagsinput/tagsinput.js"></script>
<!-- text editor js -->
<script src="../vendors/text_editor/summernote-bs4.js"></script>

<script src="../vendors/apex_chart/apexcharts.js"></script>

<!-- custom js -->
<script src="../js/custom.js"></script>


<!-- Thêm ajax để xử lý xóa ảnh từ album -->
 <script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-image-btn");

    deleteButtons.forEach(button => {
        button.addEventListener("click", function (event) {
            event.preventDefault(); // Ngăn chặn hành động mặc định của nút

            const imageName = this.getAttribute("data-image"); // Lấy tên ảnh
            const imageContainer = this.parentElement; // Lấy thẻ chứa ảnh hiện tại

            // Hiển thị thông báo xác nhận bằng SweetAlert2
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Ảnh này sẽ bị xóa khỏi album!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Nếu người dùng xác nhận, gửi yêu cầu AJAX để xóa ảnh
                    fetch("delete_image.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            image: imageName,
                            product_id: <?php echo json_encode($product['product_id']); ?>
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Xóa ảnh khỏi giao diện
                                imageContainer.remove();

                                // Hiển thị thông báo thành công
                                Swal.fire(
                                    'Đã xóa!',
                                    'Ảnh đã được xóa thành công.',
                                    'success'
                                );
                            } else {
                                // Hiển thị thông báo lỗi
                                Swal.fire(
                                    'Lỗi!',
                                    data.message || 'Đã xảy ra lỗi. Vui lòng thử lại!',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error("Lỗi:", error);
                            Swal.fire(
                                'Lỗi!',
                                'Đã xảy ra lỗi. Vui lòng thử lại!',
                                'error'
                            );
                        });
                }
            });
        });
    });
});
 </script>

 <!-- Kích hoạt CKEDITOR -->
<script>
    ClassicEditor
        .create(document.querySelector('#product_description'), {
            toolbar: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontFamily', 'fontSize', '|',
                'bulletedList', 'numberedList', '|',
                'blockQuote', 'insertTable', '|',
                'undo', 'redo', 'link', 'imageUpload'
            ],
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Verdana, Geneva, sans-serif'
                ]
            },
            fontSize: {
                options: [
                    10,
                    12,
                    14,
                    16,
                    18,
                    20,
                    22,
                    24,
                    28,
                    32
                ],
                supportAllValues: true
            }
        })
        .catch(error => {
            console.error(error);
        });
</script>





</body>
</html>
