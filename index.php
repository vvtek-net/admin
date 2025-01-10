<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "telesale";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
$alert = ""; // Biến lưu script SweetAlert2
if ($conn->connect_error) {
    $alert = "
        Swal.fire({
            icon: 'error',
            title: 'Kết nối thất bại',
            text: '" . $conn->connect_error . "'
        });
    ";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Bảo vệ SQL Injection
        $username = $conn->real_escape_string($username);
        $password = $conn->real_escape_string($password);

        // Kiểm tra user
        $sql = "SELECT * FROM master_accounts WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $role_id = $user['role_id'];
            $website_type = $user['website_type'];
            $db_name = $user['db_name'];

            if ($role_id === 'admin' && !empty($db_name)) {
                // Tạo session
                $_SESSION['username'] = $username;
                $_SESSION['role_id'] = $role_id;
                $_SESSION['db_connected'] = $db_name;

                // Xác định URL chuyển hướng
                $redirect_url = "";
                switch ($website_type) {
                    case 'ban-hang':
                        $redirect_url = "./admin/ad_banhang/index.php";
                        break;
                    case 'gioi-thieu':
                        $redirect_url = "./admin/ad_gioithieu/index.php";
                        break;
                    case 'tin-tuc':
                        $redirect_url = "./admin/ad_tintuc/index.php";
                        break;
                    default:
                        $alert = "
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: 'Website type không hợp lệ.'
                            });
                        ";
                }

                if ($redirect_url !== "") {
                    $alert = "
                        Swal.fire({
                            icon: 'success',
                            title: 'Đăng nhập thành công!',
							html: '<b style=\"color: #ae66ff;\">Chào Mừng Đến Với Trường Thành Web</b>',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '$redirect_url';
                        });
                    ";
                }
            } else {
                $alert = "
                    Swal.fire({
                        icon: 'error',
                        title: 'Quyền truy cập không hợp lệ',
                        text: 'Vui lòng kiểm tra lại tài khoản.'
                    });
                ";
            }
        } else {
            $alert = "
                Swal.fire({
                    icon: 'error',
                    title: 'Sai tài khoản hoặc mật khẩu',
                    text: 'Vui lòng kiểm tra và thử lại.'
                });
            ";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/ico" href="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/favicon.ico">
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/logo-header-4.png" alt="IMG">
                </div>
                <form class="login100-form validate-form" action="" method="POST">
                    <span class="login100-form-title" style="font-family: roboto; font-weight: 600">
                        Chào Mừng Bạn Đến Với Trường Thành Web
                    </span>
                    <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                        <input class="input100" type="text" name="username" placeholder="Nhập Email...">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="password" placeholder="Nhập Mật Khẩu...">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn">
                            Đăng nhập
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/tilt/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        });
        <?php echo $alert; ?> <!-- Thực thi SweetAlert2 -->
    </script>
</body>
</html>
