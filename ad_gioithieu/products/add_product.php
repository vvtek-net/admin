<?php
include '../config/db_connection.php';

$img_path = '../img/' . $_SESSION['username'] . '/';
$success_message = ""; // Biến để hiển thị thông báo thành công
$errors = []; // Biến lưu lỗi

// Xử lý khi người dùng nhấn nút tạo sản phẩm
if (isset($_POST['create_product'])) {
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
                    window.location.href = 'index.php'; // Thay bằng URL phù hợp
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
          <a class=""  href="../index.php"  aria-expanded="false">
          <!-- <i class="fas fa-th"></i> -->
          <img src="../img/menu-icon/dashboard.svg" alt="">
            <span>Dashboard</span>
          </a>

        </li>
        <li class="">
          <a class=""  href="../category/index.php"  aria-expanded="true">
          <!-- <i class="fas fa-th"></i> -->
          <img src="../img/menu-icon/dashboard.svg" alt="">
            <span>Danh Mục</span>
          </a>

        </li>
        <li class="mm-active">
          <a   class="" href="../products/index.php" aria-expanded="false">
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
                                        <a href="../#">Log Out </a>
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
                                <h2 class="text-center mb-4">Tạo sản phẩm mới</h2>

                                <!-- Hiển thị thông báo thành công (nếu có) -->
                                <?php if (!empty($success_message)): ?>
                                    <div class="alert alert-success text-center">
                                        <?php echo $success_message; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Form tạo sản phẩm -->
                                <form action="" method="POST" class="p-4 border rounded shadow" enctype="multipart/form-data">
                                    <!-- Các trường input -->
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label">Tên Sản Phẩm:</label>
                                        <input type="text" id="product_name" name="product_name" class="form-control" placeholder="Nhập tên sản phẩm" required>
                                    </div>

                                    <!-- Sửa thành dropdown list để chọn danh mục -->
                                    <div class="mb-3">
                                        <label for="CategoryID" class="form-label">Danh Mục:</label>
                                        <select name="category_id" id="CategoryID" class="form-control" required>
                                            <option value="">Chọn danh mục</option>
                                            <?php
                                            // Truy vấn danh sách danh mục từ bảng category
                                            $categorySql = "SELECT CategoryID, CategoryName FROM category";
                                            $categoryResult = $conn->query($categorySql);

                                            // Kiểm tra và hiển thị danh mục trong dropdown list
                                            if ($categoryResult->num_rows > 0) {
                                                while ($row = $categoryResult->fetch_assoc()) {
                                                    echo '<option value="' . $row['CategoryID'] . '">' . $row['CategoryName'] . '</option>';
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
                                                required><?php echo "Nhập Mô Tả"; ?></textarea>
                                        </div>

                                    <div class="mb-3">
                                        <label for="product_price" class="form-label">Giá Sản Phẩm:</label>
                                        <input type="text" id="product_price" name="product_price" class="form-control" placeholder="Nhập giá sản phẩm" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_img" class="form-label">Hình Ảnh Sản Phẩm:</label>
                                        <input type="file" id="product_img" name="product_img" class="form-control" accept="image/*" required>
                                        <small class="text-muted">Hình ảnh sản phẩm chính (bắt buộc).</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_album" class="form-label">Album Hình Ảnh:</label>
                                        <input type="file" id="product_album" name="product_album[]" class="form-control" accept="image/*" multiple>
                                        <small class="text-muted">Bạn có thể chọn nhiều ảnh. Mỗi ảnh không quá 3MB.</small>
                                    </div>
                                    <!-- Nút submit -->
                                    <button type="submit" class="btn btn-primary" name="create_product">Tạo sản phẩm</button>
                                    <a href="index.php" class="btn btn-secondary">Danh Sách Sản Phẩm</a>
                                </form>

                                <!-- Kết thúc form -->
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
