<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'destroy_session') {
    session_destroy();
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $conn = new mysqli('localhost', 'root', '', 'telesale');

    if ($conn->connect_error) {
        header('Location: reset_password.php?msg=fail');
        exit;
    }

    $email = $_SESSION['reset_email'];
    $sql = "UPDATE master_accounts SET password = '$new_password' WHERE username = '$email'";

    if ($conn->query($sql) === TRUE) {
        // Gửi email thông báo mật khẩu mới
        require 'send_email.php';
        $content = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #4CAF50;'>Thay Đổi Mật Khẩu Thành Công</h2>
            <p>Xin chào,</p>
            <p>Mật khẩu cho tài khoản website của bạn đã được thay đổi thành công.</p>
            <p style='font-size: 18px;'><b>Mật khẩu mới của bạn là:</b> $new_password</p>
            <p>Vui lòng đăng nhập lại và thay đổi mật khẩu nếu cần.</p>
            <p>Trân trọng,</p>
            <p style='color: #4CAF50;'>Đội ngũ hỗ trợ TruongThanhWeb</p>
        </div>";

        sendEmail($email, 'Người dùng', $content, '=?UTF-8?B?' . base64_encode('Thông Báo Thay Đổi Mật Khẩu') . '?=');

        header('Location: reset_password.php?msg=success');
    } else {
        header('Location: reset_password.php?msg=fail');
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Đặt Lại Mật Khẩu</title>
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
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

    .swal-title, .swal-content {
        font-family: 'Roboto', sans-serif;
    }
</style>
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="https://truongthanhweb.com/wp-content/uploads/sites/208/2020/06/logo-header-4.png" alt="IMG">
                </div>
                <!-- reset_password.php -->
                <form class="login100-form validate-form" id="reset-password-form" action="" method="POST">
                    <span class="login100-form-title" style="font-family: roboto; font-weight: 600">Đặt Lại Mật Khẩu</span>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="password" name="new_password" placeholder="New Password..." required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    // Hiển thị SweetAlert2 theo msg trong URL
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');

    if (msg === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Mật khẩu đã được thay đổi!',
            confirmButtonText: 'OK'
        }).then(() => {
            // Gửi yêu cầu AJAX để hủy session
            fetch('reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=destroy_session'
            }).then(() => {
                window.location.href = '../index.php';
            });
        });
    } else if (msg === 'fail') {
        Swal.fire({
            icon: 'error',
            title: 'Thay đổi mật khẩu thất bại!',
            text: 'Vui lòng thử lại.',
            confirmButtonText: 'OK'
        });
    }
});

    </script>
</body>
</html>
