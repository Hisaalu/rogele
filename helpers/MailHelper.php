<?php
// File: /helpers/MailHelper.php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv->load();
}

class MailHelper {
    private $isRender;
    private $mail;
    private $resendApiKey;

    public function __construct() {
        $this->isRender = isset($_ENV['RENDER']) || getenv('RENDER') !== false;
        $this->resendApiKey = $_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY') ?? '';

        if (!$this->isRender) {
            $this->mail = new PHPMailer(true);
            $this->mail->isSMTP();
            $this->mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
            $this->mail->setFrom($this->mail->Username, 'ROGELE');
            $this->mail->CharSet    = 'UTF-8';
            $this->mail->Timeout    = 5;
        }
    }

    public function sendResetEmail($to, $name, $resetLink) {
        $subject = 'Password Reset Request | ROGELE';
        $htmlBody = $this->getResetEmailTemplate($name, $resetLink);
        $textBody = "Hello $name,\n\nClick this link to reset your password: $resetLink\n\nBest regards,\nRays of Grace Team";

        if ($this->isRender) {
            return $this->sendViaResendApi($to, $name, $subject, $htmlBody);
        } else {
            try {
                $this->mail->clearAddresses();
                $this->mail->addAddress($to, $name);
                $this->mail->isHTML(true);
                $this->mail->Subject = $subject;
                $this->mail->Body = $htmlBody;
                $this->mail->AltBody = $textBody;
                
                $this->mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Local SMTP sending error: " . $e->getMessage());
                return false;
            }
        }
    }

    private function sendViaResendApi($to, $name, $subject, $htmlBody) {
        $configuredEmail = $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?? '';

        if (empty($configuredEmail) || strpos($configuredEmail, '@gmail.com') !== false) {
            $senderEmail = 'onboarding@resend.dev';
        } else {
            $senderEmail = $configuredEmail;
        }

        $fromHeader = "ROGELE <" . $senderEmail . ">";
        
        $payload = json_encode([
            'from' => $fromHeader,
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlBody
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->resendApiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("Resend API failed with status $httpCode: " . $response);
            return false;
        }
    }
    
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
                    background-color: #7f2677;
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
                    color: #000;
                    margin-bottom: 20px;
                }
                .message {
                    color: #000;
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
                    color: #555;
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
                        have to resubmit the request for a password reset. 
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
                </div>
            </div>
        </body>
        </html>
        ';
    }
}