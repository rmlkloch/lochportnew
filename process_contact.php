<?php

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php'; // Make sure the path is correct relative to your script

// Only process POST requests.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Configuration ---
    $recipient_email = "rmlkranathunga@gamil.com"; // **** ඔයාගෙ Email එක මෙතන දාන්න ****
    $email_subject_prefix = "Portfolio Contact Form";
    $success_redirect_url = "index.html?status=success#contact";
    $error_redirect_url = "index.html?status=error#contact";
    $validation_redirect_url = "index.html?status=validation_error#contact";

    // --- SMTP Configuration ---
    // **** REPLACE with your actual SMTP credentials and settings ****
    $smtp_host = 'smtp.example.com';       // e.g., 'smtp.gmail.com' or your hosting provider's SMTP server
    $smtp_username = 'your_email@example.com'; // Your SMTP username (often your full email address)
    $smtp_password = 'YOUR_SMTP_PASSWORD';   // Your SMTP password (e.g., Gmail App Password or hosting email password)
    $smtp_port = 465;                      // Port: 465 for SSL, 587 for TLS
    $smtp_secure = PHPMailer::ENCRYPTION_SMTPS; // Use `PHPMailer::ENCRYPTION_STARTTLS` for port 587

    // Optional: Sender 'From' address and Name
    $sender_email = 'noreply@yourdomain.com'; // A 'no-reply' address from your domain is good practice
    $sender_name = 'Lochana Portfolio';      // Name shown in the 'From' field

    // --- Get & Sanitize Form Data ---
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    // --- Basic Validation ---
    $errors = [];
    if (empty(trim((string)$name))) { $errors[] = "Name is required."; }
    if (empty(trim((string)$email))) {
        $errors[] = "Email is required.";
    } else {
        $email_for_validation = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email_for_validation) { $errors[] = "Please enter a valid email address."; }
        else { $email = $email_for_validation; } // Use validated email
    }
    if (empty(trim((string)$message))) { $errors[] = "Message cannot be empty."; }

    // --- Process if No Errors ---
    if (empty($errors)) {
        // Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // --- Server settings ---
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output - Remove for production!
            $mail->isSMTP();                       // Send using SMTP
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;              // Enable SMTP authentication
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;    // **** SECURITY WARNING: Avoid hardcoding passwords. Use environment variables or config files. ****
            $mail->SMTPSecure = $smtp_secure;
            $mail->Port       = $smtp_port;

            // --- Recipients ---
            $mail->setFrom($sender_email, $sender_name); // Who the email is FROM
            $mail->addAddress($recipient_email);         // Add a recipient (your email)
            $mail->addReplyTo($email, $name);          // Set reply-to to the user's email

            // --- Content ---
            $mail->isHTML(false); // Set email format to plain text
            $mail->Subject = $email_subject_prefix . " from " . $name;
            $email_body = "You received a message via your portfolio contact form:\n\n";
            $email_body .= "Name: " . $name . "\n";
            $email_body .= "Email: " . $email . "\n";
            $email_body .= "Message:\n--------------------\n" . $message . "\n--------------------\n";
            $mail->Body    = $email_body;
            $mail->AltBody = $email_body; // AltBody is good practice even for plain text

            // Send the email
            $mail->send();
            // echo 'Message has been sent'; // For debugging
            header("Location: " . $success_redirect_url);
            exit;

        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // For debugging
            // Redirect back with a generic error message
            $errorMessage = urlencode("Sorry, the message could not be sent. Please try again later.");
            // You could log the detailed error: error_log("Mailer Error: " . $mail->ErrorInfo);
            header("Location: " . $error_redirect_url . "&msg=" . $errorMessage);
            exit;
        }
    } else {
        // Validation errors occurred
        $errorString = implode(" ", $errors);
        $errorMessage = urlencode($errorString);
        header("Location: " . $validation_redirect_url . "&msg=" . $errorMessage);
        exit;
    }

} else {
    // Redirect if not POST
    header("Location: index.html");
    exit;
}
?>