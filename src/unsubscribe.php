<?php
require_once __DIR__ . '/functions.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        $_SESSION['unsubscribe_email'] = $email;

        $code = generateVerificationCode();
        sendUnsubscribeVerificationEmail($email, $code);

        $safe = str_replace(['@', '.'], '_', $email);
        echo "<script>
            window.open('emails/unsubscribe_{$safe}.html', '_blank');
            alert('Verification code sent to your email!');
            window.location.href = 'index.php';
        </script>";
        exit;
    } elseif (isset($_POST['verification_code']) && isset($_SESSION['unsubscribe_email'])) {
        $code = trim($_POST['verification_code']);
        $email = $_SESSION['unsubscribe_email'];

        if (verifyUnsubscribeCode($email, $code)) {
            unsubscribeEmail($email);
            unset($_SESSION['unsubscribe_email']);
            $message = 'You have been unsubscribed successfully.';
        } else {
            $message = 'Invalid verification code.';
        }

        echo "<script>
            alert(" . json_encode($message) . ");
            window.location.href = 'index.php';
        </script>";
        exit;
    }
}
?>
