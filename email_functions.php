<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Debug mode (for testing only)
//        $mail->SMTPDebug = 2;
//        $mail->Debugoutput = 'html';

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ra7527753@gmail.com'; 
        $mail->Password   = 'mtoixnibmxocgevr';    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom('ra7527753@gmail.com', 'EMIS System');

        // Recipient
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Send email
        if($mail->send()) {
            echo "<script>alert(`Email sent successfully to {$to}`);</script>";
            return true;
        } else {
            echo "<p>Failed to send email to {$to}</p>";
            return false;
        }

    } catch (Exception $e) {
        echo "<pre>Mailer Error: {$mail->ErrorInfo}</pre>";
        return false;
    }
}
?>
