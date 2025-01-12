<?php
include '../config/db_connection.php';

$img_path = '../img/' . $_SESSION['username'] . '/';
$success_message = ""; // Biến để hiển thị thông báo thành công
$errors = []; // Biến lưu lỗi

// Xử lý khi người dùng nhấn nút tạo sản phẩm
if (isset($_POST['create_news'])) {
    // Nhận giá trị từ form
    $news_name = $_POST['news_name'];
    $news_content = $_POST['news_content'];
    $news_author = $_POST['news_author'];

    // Tạo thư mục lưu ảnh nếu chưa tồn tại
    if (!is_dir($img_path)) {
        if (!mkdir($img_path, 0777, true) && !is_dir($img_path)) {
            $errors[] = "Không thể tạo thư mục lưu trữ hình ảnh.";
        }
    }

    // Xử lý hình ảnh bìa tin tức
    $news_img = "";
    if (!empty($_FILES['news_img']['name'])) {
        $tmp_name = $_FILES['news_img']['tmp_name'];
        $size = $_FILES['news_img']['size'];
        $error = $_FILES['news_img']['error'];
        $name = $_FILES['news_img']['name'];

        if ($error === UPLOAD_ERR_OK) {
            if ($size <= 3 * 1024 * 1024) { // Kiểm tra kích thước ảnh <= 3MB
                $file_ext = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext; // Tạo tên file duy nhất
                $file_dest = $img_path . $file_name;

                // Di chuyển file vào thư mục đích
                if (move_uploaded_file($tmp_name, $file_dest)) {
                    $news_img = $file_name; // Gán tên file để lưu vào database
                } else {
                    $errors[] = "Không thể tải lên hình ảnh.";
                }
            } else {
                $errors[] = "Hình ảnh vượt quá kích thước cho phép (3MB).";
            }
        } else {
            $errors[] = "Lỗi khi tải lên hình ảnh.";
        }
    }

    // Chèn thông tin bài viết vào cơ sở dữ liệu
    if (empty($errors)) {
        $sql = "INSERT INTO news (news_name, news_content, news_author, news_img, create_time) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Lỗi SQL: " . $conn->error);
        }

        $stmt->bind_param(
            "ssss",
            $news_name,
            $news_content,
            $news_author,
            $news_img
        );

        if ($stmt->execute()) {
            $success_message = "Bài viết mới đã được tạo thành công!";
        } else {
            $errors[] = "Không thể tạo bài viết. Vui lòng thử lại.";
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
                title: 'Tạo bài viết thất bại',
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
    <title>Thêm Bài Viết Mới</title>

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
        <li class="">
          <a   class="" href="../products/index.php" aria-expanded="false">
            <img src="../img/menu-icon/2.svg" alt="">
            <span>Sản Phẩm</span>
          </a>
        </li>

        <li class="mm-active">
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
                                <h2 class="text-center mb-4">Tạo bài viết mới</h2>

                                <!-- Hiển thị thông báo thành công (nếu có) -->
                                <?php if (!empty($success_message)): ?>
                                    <div class="alert alert-success text-center">
                                        <?php echo $success_message; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="" method="POST" class="p-4 border rounded shadow" enctype="multipart/form-data">
                                    <!-- Tên bài viết -->
                                    <div class="mb-3">
                                        <label for="news_name" class="form-label">Tên Bài Viết:</label>
                                        <input type="text" id="news_name" name="news_name" class="form-control" placeholder="Nhập tên bài viết" required>
                                    </div>

                                    <!-- Nội dung bài viết -->
                                    <div class="mb-3">
                                            <label for="product_description" class="form-label">Nội Dung bài viết:</label>
                                            <textarea 
                                                id="product_description" 
                                                name="news_content" 
                                                class="form-control" 
                                                rows="4" 
                                                required>Nhập nội dung</textarea>
                                        </div>

                                    <!-- Tác giả -->
                                    <div class="mb-3">
                                        <label for="news_author" class="form-label">Tác Giả:</label>
                                        <input type="text" id="news_author" name="news_author" class="form-control" placeholder="Nhập tên tác giả" required>
                                    </div>

                                    <!-- Ảnh bìa -->
                                    <div class="mb-3">
                                        <label for="news_img" class="form-label">Ảnh Bìa Bài Viết:</label>
                                        <input type="file" id="news_img" name="news_img" class="form-control" accept="image/*" required>
                                        <small class="text-muted">Hình ảnh bài viết chính (bắt buộc).</small>
                                    </div>

                                    <!-- Nút submit -->
                                    <button type="submit" class="btn btn-primary" name="create_news">Tạo Bài Viết</button>
                                    <a href="index.php" class="btn btn-secondary">Danh Sách Bài Viết</a>
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
