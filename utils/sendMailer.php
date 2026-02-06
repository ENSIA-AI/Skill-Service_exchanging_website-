<?php
/**
 * Email sending utility using PHPMailer
 * This file contains functions for sending various types of emails
 * 
 * SETUP: Each teammate must create their own .env file from .env.example
 * See .env.example for instructions
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

// ============================================================
// LOAD ENVIRONMENT VARIABLES FROM .env FILE
// ============================================================
// This allows each teammate to have their own credentials
// without modifying shared code
// ============================================================

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) continue;
        // Skip lines without =
        if (strpos($line, '=') === false) continue;
        
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        $value = trim($value, '"\'');
        
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
} else {
    // Show helpful error if .env is missing
    error_log("WARNING: .env file not found. Please copy .env.example to .env and add your SMTP credentials.");
}

define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 2525));
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@skillserviceexchange.com');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'Skill Service Exchange');
define('SMTP_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
define('SMTP_DEBUG', ($_ENV['SMTP_DEBUG'] ?? 'false') === 'true');

// Detect production environment
$_isProduction = !empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'infinityfreeapp') !== false || strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') === false);

/**
 * Send a verification email with a code
 * 
 * @param string $recipientEmail The recipient's email address
 * @param string $recipientName The recipient's name
 * @param string $verificationCode The verification code to send
 * @return bool True if email was sent successfully, false otherwise
 */
function sendVerificationEmail($recipientEmail, $recipientName, $verificationCode) {
    
    // Check if credentials are configured
    if (empty(SMTP_USERNAME) || empty(SMTP_PASSWORD) || 
        SMTP_USERNAME === 'your_mailtrap_username_here' || 
        SMTP_PASSWORD === 'your_mailtrap_password_here') {
        
        $errorMsg = "SMTP credentials not configured! Email sending is disabled. ";
        $errorMsg .= "Please configure .env file with SMTP credentials.";
        error_log($errorMsg);
        
        // For production, log silently, for development show helpful message
        if (!$_isProduction) {
            if (SMTP_DEBUG) {
                echo "<pre style='background:#fff3cd;padding:15px;margin:10px;border:2px solid #ffc107;border-radius:5px;'>";
                echo "<strong>⚠️ Email Configuration Required</strong><br><br>";
                echo "Your .env file is missing SMTP credentials.<br><br>";
                echo "<strong>To fix this:</strong><br>";
                echo "1. Open the file: <code>.env</code> in your project root<br>";
                echo "2. Sign up at <a href='https://mailtrap.io' target='_blank'>https://mailtrap.io</a> (FREE)<br>";
                echo "3. Go to: Email Testing → Inboxes → My Inbox → Show Credentials<br>";
                echo "4. Copy your Username and Password into .env<br>";
                echo "5. Save and try again<br>";
                echo "</pre>";
            }
        }
        
        // Return true in development to allow signup to proceed, false in production
        return !$_isProduction;  // Allow signup flow to continue locally without email
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Enable debugging if SMTP_DEBUG is true
        if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
            $mail->SMTPDebug = 2; // Show client and server messages
            $mail->Debugoutput = function($str, $level) {
                error_log("SMTP Debug: $str");
            };
        } else {
            $mail->SMTPDebug = 0;
        }
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code - Skill Service Exchange';
        $mail->CharSet = 'UTF-8';
        
        // HTML email body
        $mail->Body = getVerificationEmailTemplate($recipientName, $verificationCode);
        
        // Plain text alternative for non-HTML email clients
        $mail->AltBody = "Hello {$recipientName},\n\n" .
                        "Your verification code is: {$verificationCode}\n\n" .
                        "This code will expire in 10 minutes.\n\n" .
                        "If you didn't request this code, please ignore this email.\n\n" .
                        "Best regards,\nSkill Service Exchange Team";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log the error for debugging
        $errorMsg = "Email sending failed: {$mail->ErrorInfo}";
        error_log($errorMsg);
        
        // If debug mode is on, also display the error (remove in production)
        if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
            echo "<pre style='background:#ffebee;padding:10px;margin:10px;border:1px solid #f44336;'>";
            echo "<strong>Email Error:</strong> " . htmlspecialchars($mail->ErrorInfo);
            echo "</pre>";
        }
        
        return false;
    }
}

/**
 * Generate HTML email template for verification code
 * 
 * @param string $name Recipient's name
 * @param string $code Verification code
 * @return string HTML email content
 */
function getVerificationEmailTemplate($name, $code) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td align="center" style="padding: 40px 0;">
                    <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="padding: 40px 30px; background-color: #2d3748; border-radius: 8px 8px 0 0; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 24px;">Skill Service Exchange</h1>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="margin: 0 0 20px; color: #2d3748; font-size: 20px;">Email Verification</h2>
                                <p style="margin: 0 0 15px; color: #4a5568; font-size: 16px; line-height: 1.6;">
                                    Hello ' . htmlspecialchars($name) . ',
                                </p>
                                <p style="margin: 0 0 25px; color: #4a5568; font-size: 16px; line-height: 1.6;">
                                    Thank you for signing up! Please use the verification code below to complete your registration:
                                </p>
                                
                                <!-- Verification Code Box -->
                                <div style="text-align: center; margin: 30px 0;">
                                    <div style="display: inline-block; padding: 20px 40px; background-color: #edf2f7; border-radius: 8px; border: 2px dashed #cbd5e0;">
                                        <span style="font-size: 32px; font-weight: bold; color: #e07850; letter-spacing: 8px;">' . htmlspecialchars($code) . '</span>
                                    </div>
                                </div>
                                
                                <p style="margin: 25px 0 15px; color: #718096; font-size: 14px; line-height: 1.6;">
                                    <strong>Note:</strong> This code will expire in <strong>10 minutes</strong>.
                                </p>
                                <p style="margin: 0 0 15px; color: #718096; font-size: 14px; line-height: 1.6;">
                                    If you didn\'t request this code, please ignore this email or contact our support team.
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="padding: 30px; background-color: #f7fafc; border-radius: 0 0 8px 8px; text-align: center; border-top: 1px solid #e2e8f0;">
                                <p style="margin: 0 0 10px; color: #718096; font-size: 14px;">
                                    Best regards,<br>
                                    <strong>Skill Service Exchange Team</strong>
                                </p>
                                <p style="margin: 0; color: #a0aec0; font-size: 12px;">
                                    This is an automated message. Please do not reply directly to this email.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

/**
 * Send a general notification email
 * 
 * @param string $recipientEmail The recipient's email address
 * @param string $recipientName The recipient's name
 * @param string $subject Email subject
 * @param string $htmlBody HTML content of the email
 * @param string $textBody Plain text content of the email
 * @return bool True if email was sent successfully, false otherwise
 */
function sendEmail($recipientEmail, $recipientName, $subject, $htmlBody, $textBody = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->CharSet = 'UTF-8';
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags($htmlBody);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>
