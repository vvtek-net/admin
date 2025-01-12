<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendEmail($email, $name, $content, $title) {
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'website.truongthanh@gmail.com';
        $mail->Password   = 'qrwr hcnb pbna dfxn'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Người gửi và người nhận
        $mail->setFrom('website.truongthanh@gmail.com', 'TruongThanhWeb');
        $mail->addAddress($email, $name);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body    = '<p>' . $content . '</p>';

        // Gửi email
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
