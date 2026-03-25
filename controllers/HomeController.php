<?php
// File: /controllers/HomeController.php
class HomeController {
    public function index() {
        // If user is logged in, redirect to their dashboard
        // if (isset($_SESSION['user_id'])) {
        //     switch ($_SESSION['user_role']) {
        //         case 'admin':
        //             header('Location: ' . BASE_URL . '/admin/dashboard');
        //             break;
        //         case 'teacher':
        //             header('Location: ' . BASE_URL . '/teacher/dashboard');
        //             break;
        //         case 'learner':
        //             header('Location: ' . BASE_URL . '/learner/dashboard');
        //             break;
        //         case 'external':
        //             header('Location: ' . BASE_URL . '/external/dashboard');
        //             break;
        //     }
        //     exit;
        // }
        
        // Show landing page
        require_once __DIR__ . '/../views/home.php';
    }

    /**
     * Send contact email
     */
    public function sendContact() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validate
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
        if (empty($subject)) $errors[] = 'Subject is required';
        if (empty($message)) $errors[] = 'Message is required';
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
            exit;
        }
        
        // Check if we're on localhost
        $isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
        
        if ($isLocal) {
            // For local development: Save to a log file instead of sending email
            $logFile = __DIR__ . '/../logs/contact_messages.log';
            
            // Create logs directory if it doesn't exist
            if (!is_dir(__DIR__ . '/../logs')) {
                mkdir(__DIR__ . '/../logs', 0777, true);
            }
            
            $logEntry = "========================================\n";
            $logEntry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $logEntry .= "Name: $name\n";
            $logEntry .= "Email: $email\n";
            $logEntry .= "Subject: $subject\n";
            $logEntry .= "Message:\n$message\n";
            $logEntry .= "========================================\n\n";
            
            $written = file_put_contents($logFile, $logEntry, FILE_APPEND);
            
            if ($written) {
                echo json_encode(['success' => true, 'message' => 'Message saved! (Local mode - email will be sent in production)']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save message. Please try again.']);
            }
            exit;
        }
        
        // For production: Send actual email
        $to = 'info@raysofgrace.ac.ug';
        $emailSubject = 'Contact Form: ' . $subject;
        
        $emailMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .label { font-weight: bold; color: #8B5CF6; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Contact Form Message</h2>
                </div>
                <div class='content'>
                    <div class='info'>
                        <p><span class='label'>Name:</span> " . htmlspecialchars($name) . "</p>
                        <p><span class='label'>Email:</span> " . htmlspecialchars($email) . "</p>
                        <p><span class='label'>Subject:</span> " . htmlspecialchars($subject) . "</p>
                        <p><span class='label'>Message:</span></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    <p>This message was sent from the contact form on your website.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: Rays of Grace <noreply@raysofgrace.ac.ug>\r\n";
        $headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
        
        // Send email
        $sent = mail($to, $emailSubject, $emailMessage, $headers);
        
        if ($sent) {
            // Send auto-reply to user
            $autoReplySubject = "Thank you for contacting Rays of Grace";
            $autoReplyMessage = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                    .content { padding: 30px; background: #f9f9f9; border-radius: 10px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Thank You for Contacting Us!</h2>
                    </div>
                    <div class='content'>
                        <p>Dear " . htmlspecialchars($name) . ",</p>
                        <p>Thank you for reaching out to Rays of Grace E-Learning. We have received your message and will get back to you within 24-48 hours.</p>
                        <p><strong>Your message:</strong></p>
                        <p style='background: white; padding: 15px; border-left: 3px solid #8B5CF6;'>" . nl2br(htmlspecialchars($message)) . "</p>
                        <p>Thank you for choosing Rays of Grace!</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $autoReplyHeaders = "MIME-Version: 1.0\r\n";
            $autoReplyHeaders .= "Content-type: text/html; charset=utf-8\r\n";
            $autoReplyHeaders .= "From: Rays of Grace <noreply@raysofgrace.ac.ug>\r\n";
            
            mail($email, $autoReplySubject, $autoReplyMessage, $autoReplyHeaders);
            
            echo json_encode(['success' => true, 'message' => 'Message sent successfully! We will get back to you soon.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
        }
        exit;
    }

    /**
     * Contact page
     */
    public function contact() {
        $hideFooter = false;
        require_once __DIR__ . '/../views/contact/send-contact.php';
    }
}
?>