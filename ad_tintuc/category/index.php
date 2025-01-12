<?php
include '../config/db_connection.php';

// Số bản ghi hiển thị mỗi trang
$records_per_page = 10;

// Xác định trang hiện tại (nếu không có, mặc định là trang 1)
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Tính toán giá trị OFFSET
$offset = ($current_page - 1) * $records_per_page;

// Kiểm tra từ khóa tìm kiếm
$search_keyword = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_keyword'])) {
    $search_keyword = trim($_POST['search_keyword']);
}

// Lấy tổng số bản ghi
if (!empty($search_keyword)) {
    // Nếu có tìm kiếm
    $total_records_sql = "SELECT COUNT(*) AS total FROM category WHERE CategoryName LIKE ?";
    $stmt = $conn->prepare($total_records_sql);
    $search_param = '%' . $search_keyword . '%';
    $stmt->bind_param('s', $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $stmt->close();

    // Truy vấn lấy dữ liệu phân trang với tìm kiếm
    $sql = "SELECT * FROM category WHERE CategoryName LIKE ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $search_param, $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Nếu không có tìm kiếm
    $total_records_sql = "SELECT COUNT(*) AS total FROM category";
    $result = $conn->query($total_records_sql);
    $total_records = $result->fetch_assoc()['total'];

    // Truy vấn lấy dữ liệu phân trang
    $sql = "SELECT * FROM category LIMIT $records_per_page OFFSET $offset";
    $result = $conn->query($sql);
}
// Tính tổng số trang
$total_pages = ceil($total_records / $records_per_page);

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


$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Quản Lý Danh Mục</title>
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
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <li class="mm-active">
          <a class=""  href="../category/index.php"  aria-expanded="true">
          <!-- <i class="fas fa-th"></i> -->
          <img src="../img/menu-icon/dashboard.svg" alt="">
            <span> Danh Mục</span>
          </a>

        </li>
        <li class="">
          <a   class="" href="../news/index.php" aria-expanded="false">
            <img src="../img/menu-icon/3.svg" alt="">
            <span> Tin Tức</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="#" aria-expanded="false">
            <img src="../img/menu-icon/4.svg" alt="">
            <span> Đánh giá</span>
          </a>
        </li>

        <li class="">
          <a   class="" href="#" aria-expanded="false">
            <img src="../img/menu-icon/5.svg" alt="">
            <span>Seo Từ Khóa</span>
          </a>
        </li>
      </ul>
    
</nav>
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
                                    <a class="bell_notification_clicker" href="#"> <img src="../img/icon/bell.svg" alt="">
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
                                        <h5><?php echo $_SESSION['username'];?></h5>
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
                                <h3> Quản Lý Danh Mục</h3>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="dashboard_breadcam text-end">
                                <p><a href="../index.php">Dashboard</a> <i class="fas fa-caret-right"></i> Bảng Danh Mục</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="col-12">
                    <div class="QA_section">
                        <div class="white_box_tittle list_header">
                            <!-- <h4></h4> -->
                            <div class="box_right d-flex lms_block">
                                <div class="serach_field_2">
                                    <div class="search_inner">
                                        <form action="" method="POST">
                                            <div class="search_field">
                                                <input type="text" name="search_keyword" placeholder="Tìm kiếm..." value="<?php echo isset($_POST['search_keyword']) ? $_POST['search_keyword'] : ''; ?>">
                                            </div>
                                            <button type="submit"> <i class="ti-search"></i> </button>
                                        </form>

                                    </div>
                                </div>
                                <!-- Nút Thêm Danh Mục -->
                                <div class="add_button ms-2">
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addcategory" class="btn_1">Thêm Danh Mục</a>
                                </div>
                                <!-- Modal Thêm Danh Mục -->
                                <div class="modal fade" id="addcategory" tabindex="-1" aria-labelledby="addcategoryLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addcategoryLabel">Thêm Danh Mục Mới</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="add_category.php" method="GET">
                                                    <div class="mb-3">
                                                        <label for="category_name" class="form-label">Tên Danh Mục:</label>
                                                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Thêm</button>
                                                </form>
                                                <div id="addResult" class="mt-3"></div> <!-- Kết quả thông báo -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="QA_table mb_30">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr class="text-center">
                                            <th scope="col">STT</th>
                                            <th scope="col">Tên Danh Mục</th>
                                            <th scope="col">Trạng Thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            $stt = $offset + 1; // Số thứ tự dựa trên trang
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr class='text-center'>";
                                                echo "<th scope='row'>" . $stt . "</th>";
                                                echo "<td>" . $row['CategoryName'] . "</td>";
                                                echo "<td>
                                                        <button class='btn btn-primary text-white btn-sm edit-btn' 
                                                                data-id='" . $row['CategoryID'] . "' 
                                                                data-name='" . $row['CategoryName'] . "'>
                                                            <i class='fa-solid fa-pen-to-square'></i>
                                                        </button>
                                                        <button class='btn btn-danger text-white btn-sm delete-btn' data-id='" . $row['CategoryID'] . "'>
                                                            <i class='fa-solid fa-trash'></i>
                                                        </button>
                                                    </td>";
                                                echo "</tr>";
                                                $stt++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center'>Không có danh mục nào.</td></tr>";
                                        }
                                        ?>
                                    </tbody>

                                </table>

                                <!-- Hiển thị các nút phân trang -->
                                <div class="pagination">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                                                <li class="page-item <?php echo ($page == $current_page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
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
        </div>
    </div>
     <!-- Modal Popup chỉnh sửa danh mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Chỉnh sửa danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <input type="hidden" id="editCategoryID" name="CategoryID">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Tên danh mục:</label>
                        <input type="text" class="form-control" id="editCategoryName" name="CategoryName" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- script hiện modal edit category -->
 <script>
    document.addEventListener('DOMContentLoaded', () => {
    // Khi người dùng nhấn vào nút "Sửa"
    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const categoryID = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');

            // Điền dữ liệu vào form trong modal
            document.getElementById('editCategoryID').value = categoryID;
            document.getElementById('editCategoryName').value = categoryName;

            // Hiển thị modal popup
            const editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            editCategoryModal.show();
        });
    });

    // Xử lý khi form chỉnh sửa được submit
    const editCategoryForm = document.getElementById('editCategoryForm');
    editCategoryForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Ngăn chặn hành động mặc định của form

        // Lấy dữ liệu từ form
        const categoryID = document.getElementById('editCategoryID').value;
        const categoryName = document.getElementById('editCategoryName').value;

        // Gửi yêu cầu AJAX để cập nhật dữ liệu
        fetch('edit_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ CategoryID: categoryID, CategoryName: categoryName })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    Swal.fire('Thành công!', 'Danh mục đã được cập nhật.', 'success');

                    // Làm mới bảng dữ liệu
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    // Hiển thị thông báo lỗi
                    Swal.fire('Lỗi!', data.message || 'Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                Swal.fire('Lỗi!', 'Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
            });
    });
});
 </script>

<script>
    $(document).ready(function() {
        // Khi form thêm danh mục được submit
        $("#addCategoryForm").on("submit", function(e) {
            e.preventDefault();  // Ngừng việc gửi form theo cách mặc định

            // Lấy dữ liệu từ form
            var categoryName = $("#category_name").val();

            // Gửi yêu cầu AJAX đến server
            $.ajax({
                url: "add_category.php",  // Đường dẫn đến file xử lý
                type: "POST",
                data: { category_name: CategoryName },  // Dữ liệu gửi đi
                success: function(response) {
                    // Hiển thị kết quả trả về
                    $("#addResult").html(response);  // In thông báo lên giao diện
                    if (response === "Danh mục đã được thêm thành công!") {
                        // Nếu thêm thành công, đóng modal và làm mới bảng danh mục (hoặc reload trang)
                        setTimeout(function() {
                            $('#addcategory').modal('hide');  // Đóng modal
                            location.reload();  // Tải lại trang (hoặc bạn có thể cập nhật bảng thông qua AJAX)
                        }, 1000);
                    }
                },
                error: function() {
                    $("#addResult").html("Có lỗi xảy ra khi thêm danh mục.");
                }
            });
        });
    });
</script>
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
<script src="js/chart.min.js"></script>
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
                        text: 'Thêm danh mục thất bại. Vui lòng thử lại.',
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
</html>