<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

function sendVerificationEmail($to, $name, $code)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skill.exchange.swap@gmail.com';
        $mail->Password = 'pfhy qdlj zufe zjwg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('swap.noreply@gmail.com', 'Skill Service Exchange');
        $mail->addAddress($to, $name);

        $mail->isHTML(false);
        $mail->Subject = 'Your Verification Code - Skill Service Exchange';
        $mail->Body =
            "Hello $name,\n\n" .
            "Your verification code is: $code\n\n" .
            "This code expires in 10 minutes.\n\n" .
            "Skill Service Exchange Team";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
