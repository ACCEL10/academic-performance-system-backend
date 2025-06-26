<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../utils/mailer.php';
require __DIR__ . '/../vendor/autoload.php';

function sendStudentNotification($toEmail, $studentName, $courseCode, $componentName, $mark)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // e.g., smtp.gmail.com
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_email_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@example.com', 'TrendyNest System');
        $mail->addAddress($toEmail, $studentName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Mark Update for $courseCode";
        $mail->Body = "Dear $studentName,<br><br>You have received a new mark in <strong>$courseCode</strong> for the component <strong>$componentName</strong>: <strong>$mark</strong>.<br><br>Best regards,<br>Lecturer";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}
