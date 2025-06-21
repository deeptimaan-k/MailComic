#!/bin/bash

# === Configuration ===
PHP_PATH=$(which php)                             # Get absolute path to PHP
CRON_SCRIPT="$(pwd)/cron.php"                    # Full path to cron.php
LOG_FILE="$(pwd)/cron.log"                       # Output log for CRON job

# === Check script existence ===
if [ ! -f "$CRON_SCRIPT" ]; then
    echo "❌ ERROR: cron.php not found at $CRON_SCRIPT"
    exit 1
fi

# === Setup CRON Job if Not Exists ===
if crontab -l 2>/dev/null | grep -q "$CRON_SCRIPT"; then
    echo "ℹ️  CRON job already exists for $CRON_SCRIPT"
else
    (crontab -l 2>/dev/null; echo "0 0 * * * $PHP_PATH $CRON_SCRIPT >> $LOG_FILE 2>&1") | crontab -
    echo "✅ CRON job added to run daily at midnight"
    echo "🕒 Command: $PHP_PATH $CRON_SCRIPT"
    echo "📄 Output Log: $LOG_FILE"
fi
