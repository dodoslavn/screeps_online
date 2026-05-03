#!/bin/bash
cd "$(dirname "$0")"
/usr/bin/php scan_new.php >> ../storage/logs/scan.log 2>&1
