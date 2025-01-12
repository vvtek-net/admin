<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $otp_code = $_POST['otp'];
    $conn = new mysqli('localhost', 'root', '', 'telesale');

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Lỗi kết nối!";
        exit;
    }

    // Kiểm tra mã OTP
    $email = $conn->real_escape_string($email);
    $otp_code = $conn->real_escape_string($otp_code);
    $sql = "SELECT * FROM password_reset WHERE email = '$email' AND otp_code = '$otp_code' AND otp_expiry > NOW() AND is_used = FALSE";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Đánh dấu OTP là đã sử dụng
        $sql = "UPDATE password_reset SET is_used = TRUE WHERE email = '$email' AND otp_code = '$otp_code'";
        $conn->query($sql);

        $_SESSION['reset_email'] = $email; // Lưu email để đặt lại mật khẩu
        header("Location: reset_password.php");
        exit;
    } else {
        echo "Mã OTP không hợp lệ hoặc đã hết hạn!";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quên Mật Khẩu</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/ico" href="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/favicon.ico">
    <link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="../vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="../vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="../css/util.css">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/logo-header-4.png" alt="IMG">
                </div>
                <!-- verify_otp.php -->
                <form class="login100-form validate-form" id="verify-otp-form" action="" method="POST">
                    <span class="login100-form-title" style="font-family: roboto; font-weight: 600">Xác Thực Mã OTP</span>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="email" name="email" placeholder="Nhập Email" value="<?php echo $_SESSION['mail']; ?>" readonly>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" name="otp" placeholder="Nhập Mã OTP" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-key" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">Xác Thực</button>
                    </div>
                </form>
    <script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/select2/select2.min.js"></script>
    <script src="../vendor/tilt/tilt.jquery.min.js"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        });
    </script>
</body>
</html>
