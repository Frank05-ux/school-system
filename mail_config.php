<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendEmail($to, $subject, $body) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // 🔴 CHANGE
        $mail->Password   = 'your_app_password';    // 🔴 Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'Student Portal');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;

        $mail->Body = "
        <div style='font-family:Arial;padding:20px'>
            <h2>$subject</h2>
            <p>$body</p>
            <br>
            <small>Student Portal System</small>
        </div>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>
