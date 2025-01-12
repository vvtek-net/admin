<?php
include '../config/db_connection.php';

$img_path = '../img/' . $_SESSION['username'];
// Số sản phẩm hiển thị trên mỗi trang
$records_per_page = 10;

// Xác định trang hiện tại (nếu không có, mặc định là trang 1)
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Tính toán giá trị OFFSET
$offset = ($current_page - 1) * $records_per_page;

// Xử lý tìm kiếm
$search_keyword = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_keyword'])) {
    $search_keyword = trim($_POST['search_keyword']);
}

// Lấy tổng số sản phẩm
if (!empty($search_keyword)) {
    // Nếu có tìm kiếm
    $total_records_sql = "SELECT COUNT(*) AS total FROM products WHERE product_name LIKE ?";
    $stmt = $conn->prepare($total_records_sql);
    $search_param = '%' . $search_keyword . '%';
    $stmt->bind_param('s', $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $stmt->close();

    // Truy vấn dữ liệu phân trang kèm tìm kiếm và JOIN
    $sql = "SELECT p.*, c.CategoryName 
            FROM products p
            LEFT JOIN category c ON p.CategoryID = c.CategoryID
            WHERE p.product_name LIKE ? 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $search_param, $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Nếu không có tìm kiếm
    $total_records_sql = "SELECT COUNT(*) AS total FROM products";
    $result = $conn->query($total_records_sql);
    $total_records = $result->fetch_assoc()['total'];

    // Truy vấn dữ liệu phân trang và JOIN
    $sql = "SELECT p.*, c.CategoryName 
            FROM products p
            LEFT JOIN category c ON p.CategoryID = c.CategoryID
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

// Tính tổng số trang
$total_pages = ceil($total_records / $records_per_page);

// Xử lý xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Kiểm tra xem sản phẩm có tồn tại không
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
            header("Location: index.php?msg=deleted");
            exit();
        } else {
            header("Location: index.php?msg=error&reason=failed");
            exit();
        }
    } else {
        // Nếu sản phẩm không tồn tại
        header("Location: index.php?msg=error&reason=not_found");
        exit();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="zxx">


<!-- Mirrored from demo.dashboardpack.com/directory-html/data_table.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 07 Jan 2025 06:46:36 GMT -->
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Quản Lý Sản Phẩm</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
</head>
<body class="crm_body_bg">
 <!-- sidebar  -->
 <!-- sidebar part here -->
 <nav class="sidebar">
    <div class="logo d-flex justify-content-between">
        <a href="../index.php"><img src="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/logo-header-4.png" alt=""></a>
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
          <a   class="" href="index.php" aria-expanded="false">
            <img src="../img/menu-icon/2.svg" alt="">
            <span>Sản Phẩm</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="../news/index.php" aria-expanded="false">
            <img src="../img/menu-icon/3.svg" alt="">
            <span>Tin Tức</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="#" aria-expanded="false">
            <img src="../img/menu-icon/4.svg" alt="">
            <span>Đánh giá</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="#" aria-expanded="false">
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
                                    <form action="" method="POST">
                                        <div class="search_field">
                                            <input type="text" name="search_keyword" placeholder="Tìm kiếm..." value="<?php echo isset($_POST['search_keyword']) ? $_POST['search_keyword'] : ''; ?>">
                                        </div>
                                        <button type="submit"> <img src="../img/icon/icon_search.svg" alt=""> </button>
                                    </form>
                                </div>
                            </div>
                        <div class="header_right d-flex justify-content-between align-items-center">
                            <div class="header_notification_warp d-flex align-items-center">
                                <li>
                                    <a class="bell_notification_clicker" href="#"> <img src="../img/icon/bell.svg" alt="">
                                        <span>04</span>
                                    </a>
                                    <!-- Menu_NOtification_Wrap  -->
                                <div class="Menu_NOtification_Wrap">
                                    <div class="notification_Header">
                                        <h4>Thông Báo</h4>
                                    </div>
                                    <div class="Notification_body">
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/2.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>Cool Directory </h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/4.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>Awesome packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/3.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>what a packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/2.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>Cool Directory </h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/4.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>Awesome packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                        <!-- single_notify  -->
                                        <div class="single_notify d-flex align-items-center">
                                            <div class="notify_thumb">
                                                <a href="#"><img src="../img/staf/3.png" alt=""></a>
                                            </div>
                                            <div class="notify_content">
                                                <a href="#"><h5>what a packages</h5></a>
                                                <p>Lorem ipsum dolor sit amet</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="nofity_footer">
                                        <div class="submit_button text-center pt_20">
                                            <a href="#" class="btn_1">See More</a>
                                        </div>
                                    </div>
                                </div>
                                <!--/ Menu_NOtification_Wrap  -->
                                </li>
                                <li>
                                    <a class="CHATBOX_open" href="#"> <img src="../img/icon/msg.svg" alt="">  <span>01</span> </a>
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
                                        <a href="#">My Profile </a>
                                        <a href="#">Settings</a>
                                        <a href="../logout">Log Out </a>
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
                <div class="dashboard_header mb_50">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="dashboard_header_title">
                                <h3> Quản Lý Sản Phẩm</h3>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="dashboard_breadcam text-end">
                                <p><a href="../index.php">Dashboard</a> <i class="fas fa-caret-right"></i> Sản Phẩm</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="col-12">
                    <div class="QA_section">
                        <div class="white_box_tittle list_header">
                            <!-- <h4>Bảng Sản Phẩm</h4> -->
                            <div class="box_right d-flex lms_block">
                                <div class="serach_field_2">
                                    <div class="search_inner">
                                        <form Active="#">
                                            <div class="search_field">
                                                <input type="text" placeholder="Tìm kiếm...">
                                            </div>
                                            <button type="submit"> <i class="ti-search"></i> </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="add_button ms-2">
                                    <a href="add_product.php"  class="btn_1">Thêm Sản Phẩm</a>
                                </div>
                            </div>
                        </div>

                        <div class="QA_table mb_30">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">STT</th>
                                            <th scope="col">Tên Sản Phẩm</th>
                                            <th scope="col">Giá</th>
                                            <th scope="col">Ảnh</th>
                                            <th scope="col">Danh Mục</th>
                                            <th scope="col">Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            $stt = $offset + 1; // Tính số thứ tự theo trang
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $stt . "</td>";
                                                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['product_price']) . "</td>";
                                                echo "<td><img src='" . htmlspecialchars($img_path . '/' . $row['product_img']) . "' alt='Ảnh sản phẩm' style='width: 150px; height: 150px; border-radius: 0%;'></td>";
                                                echo "<td>" . htmlspecialchars($row['CategoryName']) . "</td>"; // Hiển thị Category Name
                                                echo "<td>
                                                        <a href='edit_product.php?id=" . urlencode($row['product_id']) . "' class='btn btn-primary text-white btn-sm'>
                                                            <i class='fa-solid fa-pen-to-square'></i>
                                                        </a>
                                                        <button class='btn btn-danger text-white btn-sm delete-btn' data-id='" . $row['product_id'] . "'>
                                                            <i class='fa-solid fa-trash'></i>
                                                        </button>
                                                    </td>";
                                                $stt++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>Không có sản phẩm nào.</td></tr>";
                                        }
                                        ?>
                                        </tbody>

                                </table>
                            </div>
                        </div>
                        <!-- Phân Trang -->
                    <div class="pagination">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search_keyword=<?php echo urlencode($search_keyword); ?>">&laquo; Trước</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                    <li class="page-item <?php echo ($page == $current_page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page; ?>&search_keyword=<?php echo urlencode($search_keyword); ?>"><?php echo $page; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search_keyword=<?php echo urlencode($search_keyword); ?>">Sau &raquo;</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
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

<!-- sweet alert 2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Lấy tham số từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const reason = urlParams.get('reason'); // Lý do (nếu có)

    // Hiển thị SweetAlert2 dựa trên giá trị của msg
    if (msg) {
        switch (msg) {
            case 'added':
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Danh mục đã được thêm thành công.',
                    confirmButtonText: 'OK'
                });
                break;
            case 'deleted':
                Swal.fire({
                    icon: 'success',
                    title: 'Xóa thành công!',
                    text: 'Danh mục đã được xóa.',
                    confirmButtonText: 'OK'
                });
                break;
            case 'exists':
                Swal.fire({
                    icon: 'warning',
                    title: 'Cảnh báo!',
                    text: 'Danh mục đã tồn tại.',
                    confirmButtonText: 'Đóng'
                });
                break;
            case 'error':
                if (reason === 'linked') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Không thể xóa!',
                        text: 'Danh mục này có sản phẩm liên kết.',
                        confirmButtonText: 'Đóng'
                    });
                } else if (reason === 'failed') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra trong quá trình xóa.',
                        confirmButtonText: 'Đóng'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Xóa không thành công. Vui lòng thử lại.',
                        confirmButtonText: 'Đóng'
                    });
                }
                break;
            case 'empty':
                Swal.fire({
                    icon: 'warning',
                    title: 'Dữ liệu không hợp lệ!',
                    text: 'Tên danh mục không được để trống.',
                    confirmButtonText: 'Đóng'
                });
                break;
            default:
                console.error('Tham số msg không hợp lệ:', msg);
        }

        // Xóa tham số khỏi URL sau khi hiển thị thông báo
        const cleanUrl = window.location.origin + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
</script>

<!-- Xác nhận xóa danh mục -->
 <script>
    // Lắng nghe sự kiện click trên các nút "Xóa"
    document.addEventListener("DOMContentLoaded", () => {
        const deleteButtons = document.querySelectorAll(".delete-btn");

        deleteButtons.forEach(button => {
            button.addEventListener("click", function () {
                const categoryId = this.getAttribute("data-id"); // Lấy CategoryID từ nút xóa

                // Hiển thị SweetAlert2 để xác nhận
                Swal.fire({
                    title: 'Bạn có chắc chắn muốn xóa?',
                    text: 'Hành động này không thể hoàn tác!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Chuyển hướng đến URL xóa khi xác nhận
                        window.location.href = `index.php?action=delete&id=${categoryId}`;
                    }
                });
            });
        });
    });
</script>


</body>

<!-- Mirrored from demo.dashboardpack.com/directory-html/data_table.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 07 Jan 2025 06:46:36 GMT -->
</html>