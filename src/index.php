<?php
require_once __DIR__ . '/functions.php';
session_start();

$message = '';
$emailValue = $_SESSION['pending_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $_SESSION['pending_email'] = $email;
        $code = generateVerificationCode();
        sendVerificationEmail($email, $code);
        $safe = str_replace(['@', '.'], '_', $email);
        echo "<script>window.open('/emails/verification_{$safe}.html', '_blank');</script>";
        $message = 'Verification code sent to your email.';
    } elseif (isset($_POST['verification_code']) && isset($_SESSION['pending_email'])) {
        $code = trim($_POST['verification_code']);
        $email = $_SESSION['pending_email'];
        if (verifyCode($email, $code)) {
            registerEmail($email);
            unset($_SESSION['pending_email']);
            $emailValue = '';
            $message = 'Email verified and registered successfully!';
        } else {
            $message = 'Invalid verification code.';
        }
    }
}

function getRegisteredEmails() {
    $file = __DIR__ . '/registered_emails.txt';
    return file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XKCD Comic Subscription</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 2rem;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .title {
            font-size: 1.75rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }

        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .subscriber-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background-color: #6366f1;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .email {
            font-weight: 500;
        }

        .stats {
            text-align: center;
            padding: 1rem;
            background: #6366f1;
            color: white;
            border-radius: 12px;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
        }

        .section-title {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .empty-state {
            text-align: center;
            color: #9ca3af;
            padding: 1rem;
        }
    </style>
</head>
<body>
<div class="card">
    <?php if ($message): ?>
        <div class="message">✅ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="title">📧 XKCD Comic Subscription</div>
    <div class="subtitle">Get the latest comics delivered to your inbox</div>

    <div class="section">
        <div class="section-title">➕ Subscribe</div>
        <form method="POST">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required placeholder="you@example.com"
                   value="<?= htmlspecialchars($emailValue) ?>">
            <button type="submit" class="btn btn-primary">Send Verification Code</button>
        </form>
    </div>

    <div class="section">
        <div class="section-title">🔐 Verify Email</div>
        <form method="POST">
            <label for="verification_code">Verification Code</label>
            <input type="text" name="verification_code" id="verification_code" maxlength="6" required placeholder="123456">
            <button type="submit" class="btn btn-primary">Verify & Subscribe</button>
        </form>
    </div>

    <div class="section">
        <div class="section-title">✖️ Unsubscribe</div>
        <form method="POST" action="unsubscribe.php">
            <label for="unsubscribe_email">Email Address</label>
            <input type="email" name="unsubscribe_email" id="unsubscribe_email" required placeholder="you@example.com">
            <button type="submit" class="btn btn-danger">Send Unsubscribe Code</button>
        </form>

        <br>

        <form method="POST" action="unsubscribe.php">
            <label for="unsubscribe_verification_code">Verification Code</label>
            <input type="text" name="verification_code" id="unsubscribe_verification_code" maxlength="6" required placeholder="123456">
            <button type="submit" class="btn btn-danger">Verify & Unsubscribe</button>
        </form>
    </div>

    <div class="section">
        <div class="section-title">👥 Active Subscribers</div>
        <?php $emails = getRegisteredEmails(); ?>
        <?php if (empty($emails)): ?>
            <div class="empty-state">📭 No subscribers yet. Be the first!</div>
        <?php else: ?>
            <?php foreach ($emails as $email): ?>
                <div class="subscriber-item">
                    <div class="avatar"><?= strtoupper(substr($email, 0, 1)) ?></div>
                    <div class="email"><?= htmlspecialchars($email) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="stats">
                <div class="stats-number"><?= count($emails) ?></div>
                <div><?= count($emails) === 1 ? 'Subscriber' : 'Subscribers' ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
