<?php
session_start();
require 'send_email.php'; // Bao gồm file chứa hàm sendEmail()

// Đặt múi giờ thành Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $conn = new mysqli('localhost', 'root', '', 'telesale');

    if ($conn->connect_error) {
        http_response_code(500);
        header('Location: forgot_password.php?msg=error');
        exit;
    }

    // Kiểm tra email trong database
    $email = $conn->real_escape_string($email);
    $sql = "SELECT * FROM master_accounts WHERE username = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $otp_code = rand(100000, 999999); // Tạo mã OTP
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP hết hạn sau 10 phút

        // Lưu OTP vào database
        $sql = "INSERT INTO password_reset (email, otp_code, otp_expiry, is_used) VALUES ('$email', '$otp_code', '$otp_expiry', FALSE)";
        $conn->query($sql);

        // Gửi email bằng hàm sendEmail()
        $content = "Mã OTP của bạn là: <b>$otp_code</b>. Hết hạn sau 10 phút.";
        sendEmail($email, 'Người dùng', $content, 'Reset Password - TruongThanhWeb');
        $_SESSION['mail'] = $email;
        // Chuyển hướng sau khi gửi mail thành công
        header('Location: forgot_password.php?msg=success');
        exit;
    } else {
        header('Location: forgot_password.php?msg=notfound');
        exit;
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

                <!-- Form gửi mã OTP -->
                <form class="login100-form validate-form" id="forgot-password-form" action="" method="POST">
                    <span class="login100-form-title" style="font-family: roboto; font-weight: 600">Quên Mật Khẩu</span>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="email" name="email" placeholder="Email..." required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">Gửi Mã OTP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 Script -->
    <script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../vendor/bootstrap/js/popper.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/select2/select2.min.js"></script>
    <script src="../vendor/tilt/tilt.jquery.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.js-tilt').tilt({
            scale: 1.1
        });

        // Kiểm tra msg trong URL và hiển thị SweetAlert2
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');

        if (msg === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Mã OTP đã được gửi!',
                text: 'Vui lòng kiểm tra email của bạn.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'verify_otp.php';
            });
        } else if (msg === 'notfound') {
            Swal.fire({
                icon: 'error',
                title: 'Email không tồn tại!',
                text: 'Vui lòng kiểm tra lại email của bạn.',
                confirmButtonText: 'OK'
            });
        } else if (msg === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi kết nối!',
                text: 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.',
                confirmButtonText: 'OK'
            });
        }
    });
</script>

</body>
</html>
