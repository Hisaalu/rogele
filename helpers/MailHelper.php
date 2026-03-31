<?php
// File: /helpers/MailHelper.php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//load environment variables from .env file
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');

// Only try to load if the .env file actually exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv->load();
}

class MailHelper {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings - Using the exact configuration
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        $this->mail->Debugoutput = function($str, $level) {
            error_log("SMTP DEBUG: $str");
        };

        $this->mail->isSMTP();
        $this->mail->Host       = 'mail.privateemail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'info@raysofgrace.ac.ug';
        $password = $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?? $_SERVER['MAIL_PASSWORD'];
        $this->mail->Password   = $password;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        $this->mail->setFrom('info@raysofgrace.ac.ug', 'ROGELE');
        $this->mail->addReplyTo('info@raysofgrace.ac.ug', 'ROGELE');
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Timeout = 30;
        
        error_log("MailHelper initialized");
    }
    
    /**
     * Send password reset email
     */
    public function sendResetEmail($to, $name, $resetLink) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to, $name);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request - Rays of Grace';
            
            $this->mail->Body = $this->getResetEmailTemplate($name, $resetLink);
            $this->mail->AltBody = "Hello $name,\n\nClick this link to reset your password: $resetLink\n\nThis link expires in 20 minutes.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nRays of Grace Team";
            
            $this->mail->send();
            error_log("Password reset email sent successfully to: $to");
            return true;
            
        } catch (Exception $e) {
            error_log("Password reset email failed. Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Test email connection
     */
    public function testConnection() {
        try {
            $this->mail->smtpConnect();
            error_log("SMTP Connection successful!");
            return true;
        } catch (Exception $e) {
            error_log("SMTP Connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get password reset email template
     */
    private function getResetEmailTemplate($name, $resetLink) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
                    background: #f8fafc;
                    margin: 0;
                    padding: 40px 20px;
                    line-height: 1.6;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #7f2677);
                    padding: 40px 30px;
                    text-align: center;
                }
                .header h1 {
                    color: white;
                    margin: 0;
                    font-size: 28px;
                    font-weight: 700;
                }
                .header p {
                    color: rgba(255,255,255,0.9);
                    margin: 10px 0 0;
                    font-size: 18px;
                }
                .content {
                    padding: 40px 30px;
                }
                .greeting {
                    font-size: 20px;
                    font-weight: 600;
                    color: black;
                    margin-bottom: 20px;
                }
                .message {
                    color: black;
                    font-size: 15px;
                    margin-bottom: 20px;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                    color: white;
                }
                .button {
                    display: inline-block;
                    padding: 14px 35px;
                    background: #f06724;
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 600;
                    font-size: 16px;
                    transition: all 0.3s ease;
                }
                .button:hover {
                    background: #f06724;
                    transform: translateY(-2px);
                }
                .footer {
                    padding: 20px 30px;
                    background: #f8fafc;
                    text-align: center;
                    font-size: 12px;
                    color: black;
                    border-top: 1px solid #e2e8f0;
                }
                .footer a {
                    color: #7f2677;
                    text-decoration: none;
                }
                @media (max-width: 600px) {
                    .header h1 { font-size: 24px; }
                    .content { padding: 30px 20px; }
                    .button { display: block; text-align: center; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>ROGELE</h1>
                    <p>Password Reset Request</p>
                </div>
                <div class="content">
                    <div class="greeting">
                        Hi ' . htmlspecialchars($name) . '!
                    </div>
                    <div class="message">
                        A password reset for your account was requested.
                    </div>
                    <div class="message">
                        Please click the button below to change your password. 
                    </div>
                    <div class="message">
                        Note that this link is valid for 20 minutes. 
                        After the time limit has expired, you will 
                        have to resubmit the request for a password reset
                    </div>
                    <div class="message">
                        Click the link below to reset your password:
                    </div>
                    <div class="button-container">
                        <a href="' . $resetLink . '" class="button" style="color: white;">Reset Your Password</a>
                    </div>
                    <div class="message">
                        If you did not make this request, please contact Support and ignore this email! 
                    </div>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ROGELE | All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                    <p>Need help? Contact us at <a href="mailto:info@raysofgrace.ac.ug">info@raysofgrace.ac.ug</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
}