<?php

function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = array_filter($emails, fn($e) => trim($e) !== $email);
    file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL);
}

function sendVerificationEmail($email, $code) {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: no-reply@example.com";

    // Simulate email by writing to HTML file
    $logDir = __DIR__ . '/emails';
    if (!is_dir($logDir)) mkdir($logDir);
    $safe = str_replace(['@', '.'], '_', $email);
    file_put_contents("$logDir/verification_{$safe}.html", "<h3>Subject: $subject</h3><p>Sender: no-reply@example.com</p>$message");

    // Save code to file
    $codeDir = __DIR__ . '/codes';
    if (!is_dir($codeDir)) mkdir($codeDir);
    file_put_contents("$codeDir/$email.code", $code);
}

function sendUnsubscribeVerificationEmail($email, $code) {
    $subject = "Confirm Un-subscription";
    $message = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: no-reply@example.com";

    $logDir = __DIR__ . '/emails';
    if (!is_dir($logDir)) mkdir($logDir);
    $safe = str_replace(['@', '.'], '_', $email);
    file_put_contents("$logDir/unsubscribe_{$safe}.html", "<h3>Subject: $subject</h3><p>Sender: no-reply@example.com</p>$message");

    $codeDir = __DIR__ . '/codes';
    if (!is_dir($codeDir)) mkdir($codeDir);
    file_put_contents("$codeDir/$email.unsubscribe", $code);
}

function verifyCode($email, $code) {
    $codeFile = __DIR__ . "/codes/$email.code";
    if (!file_exists($codeFile)) return false;
    $savedCode = trim(file_get_contents($codeFile));
    return $savedCode === $code;
}

function verifyUnsubscribeCode($email, $code) {
    $codeFile = __DIR__ . "/codes/$email.unsubscribe";
    if (!file_exists($codeFile)) return false;
    $savedCode = trim(file_get_contents($codeFile));
    return $savedCode === $code;
}

function fetchAndFormatXKCDData() {
    $id = rand(1, 2800);
    $json = @file_get_contents("https://xkcd.com/$id/info.0.json");
    if (!$json) return false;
    $data = json_decode($json, true);
    if (!isset($data['img'])) return false;

    $img = $data['img'];
    return "<h2>XKCD Comic</h2><img src='$img' alt='XKCD Comic'><p><a href='#' id='unsubscribe-button'>Unsubscribe</a></p>";
}

function sendXKCDUpdatesToSubscribers() {
    echo "[INFO] Starting to send XKCD updates...\n";

    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        echo "[ERROR] registered_emails.txt not found.\n";
        return;
    }

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        echo "[INFO] No emails to send to.\n";
        return;
    }

    $html = fetchAndFormatXKCDData();
    if (!$html) {
        echo "[ERROR] Failed to fetch XKCD comic.\n";
        return;
    }

    $subject = "Your XKCD Comic";
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: no-reply@example.com";

    foreach ($emails as $email) {
        echo "[SENDING] Email to: $email\n";

        // Simulated mail
        mail($email, $subject, $html, $headers);

        $logDir = __DIR__ . '/emails';
        if (!is_dir($logDir)) mkdir($logDir);
        $safe = str_replace(['@', '.'], '_', $email);
        file_put_contents("$logDir/comic_$safe.html", "<h3>Subject: $subject</h3><p>Sender: no-reply@example.com</p>$html");
    }

    echo "[DONE] Comics sent and saved to /emails.\n";
}

