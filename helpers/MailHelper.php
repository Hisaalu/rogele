<?php
// File: /helpers/MailHelper.php

class MailHelper {
    
    /**
     * Send an email
     */
    public static function send($to, $subject, $message, $from = null) {
        $from = $from ?? 'noreply@raysofgrace.com';
        $fromName = 'Rays of Grace E-Learning';
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $fromName . ' <' . $from . '>',
            'Reply-To: support@raysofgrace.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        try {
            if (mail($to, $subject, $message, implode("\r\n", $headers))) {
                error_log("Email sent successfully to: " . $to);
                return ['success' => true, 'message' => 'Email sent'];
            } else {
                error_log("Failed to send email to: " . $to);
                return ['success' => false, 'message' => 'Failed to send email'];
            }
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Send upgrade confirmation email
     */
    public static function sendUpgradeConfirmation($user, $fromPlan, $toPlan, $amount, $newEndDate) {
        $subject = "🎉 Your Subscription Has Been Upgraded!";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; color: #666; }
                .value { color: #333; }
                .total { font-size: 1.2em; font-weight: bold; color: #667eea; }
                .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 50px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 30px; color: #999; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Subscription Upgrade Confirmation</h2>
                </div>
                <div class='content'>
                    <h3>Hello " . htmlspecialchars($user['first_name']) . "! 👋</h3>
                    <p>Great news! Your subscription has been successfully upgraded.</p>
                    
                    <div class='details'>
                        <div class='detail-row'>
                            <span class='label'>Previous Plan:</span>
                            <span class='value'>" . ucfirst($fromPlan) . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>New Plan:</span>
                            <span class='value'>" . ucfirst($toPlan) . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Upgrade Amount:</span>
                            <span class='value total'>UGX " . number_format($amount) . "</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>New Expiry Date:</span>
                            <span class='value'>" . date('F j, Y', strtotime($newEndDate)) . "</span>
                        </div>
                    </div>
                    
                    <p>You now have access to all premium features of the <strong>" . ucfirst($toPlan) . "</strong> plan!</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . BASE_URL . "/external/dashboard' class='button'>Go to Dashboard</a>
                    </div>
                    
                    <p>Thank you for choosing Rays of Grace E-Learning!</p>
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Rays of Grace E-Learning. All rights reserved.</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($user['email'], $subject, $message);
    }
}