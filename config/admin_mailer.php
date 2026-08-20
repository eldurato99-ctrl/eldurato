<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendAdminOrderEmail($order_id, $customer_name, $customer_phone, $total_amount, $payment_method) {
    $admin_email = "eldurato99@gmail.com";
    $suffix = ($payment_method == 'COD') ? 'COD' : 'ONL';
    $custom_order_id = "ELD-" . $suffix . "-" . $order_id;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_EMAIL'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);
        
        $mail->setFrom($_ENV['SMTP_EMAIL'] ?? '', $_ENV['SITE_NAME'] ?? 'ELDURATO');
        $mail->addAddress($admin_email, 'Admin');

        $mail->isHTML(true);
        $mail->Subject = '🚀 New Order Received - ' . $custom_order_id;
        
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 600px; margin: auto;'>
                    <div style='background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; padding: 12px 15px; border-radius: 6px 6px 0 0;'>
                        <h3 style='margin:0;'>📦 New Order Alert!</h3>
                    </div>
                    <div style='padding: 15px 0; color: #333;'>
                        <p><strong>Order ID:</strong> {$custom_order_id}</p>
                        <p><strong>Customer Name:</strong> " . htmlspecialchars($customer_name) . "</p>
                        <p><strong>Phone:</strong> " . htmlspecialchars($customer_phone) . "</p>
                        <p><strong>Total Amount:</strong> ₹" . number_format($total_amount) . "</p>
                        <p><strong>Payment Method:</strong> " . strtoupper($payment_method) . "</p>
                    </div>
                    <div style='font-size: 12px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 10px; margin-top: 15px;'>
                        <p>Please check your Admin Fulfillment Pipeline to manage order tracking and status.</p>
                    </div>
                </div>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Admin Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
