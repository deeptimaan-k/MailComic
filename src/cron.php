<?php
require_once __DIR__ . '/functions.php';

echo "Running cron...\n";
sendXKCDUpdatesToSubscribers();
echo "Cron completed.\n";
?>
