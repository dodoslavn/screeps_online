#!/bin/bash
#
# Screeps Server Scanner Wrapper
#
# This wrapper script runs the PHP scanner and logs output
# Add to crontab: * * * * * /path/to/screeps_online/backend/scan.sh
#

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_SCRIPT="$SCRIPT_DIR/scan.php"
LOG_DIR="$SCRIPT_DIR/../storage/logs"
LOG_FILE="$LOG_DIR/scan.log"

# Create log directory if it doesn't exist
mkdir -p "$LOG_DIR"

# Run scanner and append to log
php "$PHP_SCRIPT" >> "$LOG_FILE" 2>&1
