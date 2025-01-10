<?php
include '../config/db_connection.php';

// Số bản ghi hiển thị mỗi trang
$records_per_page = 5;

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
    $total_records_sql = "SELECT COUNT(*) AS total FROM news WHERE news_name LIKE ?";
    $stmt = $conn->prepare($total_records_sql);
    $search_param = '%' . $search_keyword . '%';
    $stmt->bind_param('s', $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $stmt->close();

    // Truy vấn lấy dữ liệu phân trang với tìm kiếm
    $sql = "SELECT * FROM news WHERE news_name LIKE ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $search_param, $records_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Nếu không có tìm kiếm
    $total_records_sql = "SELECT COUNT(*) AS total FROM news";
    $result = $conn->query($total_records_sql);
    $total_records = $result->fetch_assoc()['total'];

    // Truy vấn lấy dữ liệu phân trang
    $sql = "SELECT * FROM news LIMIT $records_per_page OFFSET $offset";
    $result = $conn->query($sql);
}

// Tính tổng số trang
$total_pages = ceil($total_records / $records_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tin Tức</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap1.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
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
    <div class="container mt-5">
        <h2 class="text-center mb-4">Quản Lý Tin Tức</h2>

        <!-- Form tìm kiếm -->
        <form action="" method="POST" class="mb-3">
            <div class="input-group">
                <input type="text" name="search_keyword" class="form-control" placeholder="Tìm kiếm bài viết..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <!-- Table hiển thị danh sách tin tức -->
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr class="text-center">
                    <th>STT</th>
                    <th>Tên Bài Viết</th>
                    <th>Nội Dung</th>
                    <th>Tác Giả</th>
                    <th>Hình Ảnh</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $stt = $offset + 1; // Tính số thứ tự
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='text-center'>";
                        echo "<td>" . $stt . "</td>";
                        echo "<td>" . htmlspecialchars($row['news_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['news_author']) . "</td>";
                        echo "<td><img src='../uploads/" . htmlspecialchars($row['news_img']) . "' alt='Hình ảnh' style='width: 100px; height: auto;'></td>";
                        echo "<td>" . htmlspecialchars($row['create_time']) . "</td>";
                        echo "<td>
                                <button class='btn btn-primary btn-sm edit-btn' data-id='" . $row['news_id'] . "' data-name='" . htmlspecialchars($row['news_name']) . "' data-author='" . htmlspecialchars($row['news_author']) . "'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['news_id'] . "'>
                                    <i class='fas fa-trash'></i> Xóa
                                </button>
                              </td>";
                        echo "</tr>";
                        $stt++;
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Không có bài viết nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Hiển thị phân trang -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>">&laquo; Trước</a></li>
                <?php endif; ?>
                <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                    <li class="page-item <?php echo ($page == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page; ?>"><?php echo $page; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Sau &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- jQuery và Bootstrap JS -->
    <script src="../js/jquery1-3.4.1.min.js"></script>
    <script src="../js/bootstrap1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Xử lý sự kiện nút "Xóa"
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", function() {
                    const newsID = this.getAttribute("data-id");

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
                            window.location.href = `delete_news.php?id=${newsID}`;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
