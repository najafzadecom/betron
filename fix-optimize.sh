#!/bin/bash
set -euo pipefail
cd /var/www/bankexpress3

# Eski git surumu Closure iceriyorsa kaldir
python3 << 'PY'
from pathlib import Path
import re

p = Path("config/logging.php")
text = p.read_text()
text = text.replace("use App\\Logging\\TelegramLoggerHandler;\n", "")
text = re.sub(
    r"\n        'telegram' => \[.*?\n        \],\n        'stack_telegram' => \[.*?\n        \],",
    "",
    text,
    count=1,
    flags=re.DOTALL,
)
p.write_text(text)
PY

rm -f app/Logging/TelegramLoggerHandler.php
rmdir app/Logging 2>/dev/null || true

APP_DEBUG=$(grep -E '^APP_DEBUG=' .env | cut -d= -f2 | tr -d ' "' || echo "false")

run_artisan() {
    sudo -u nginx php artisan "$@"
}

run_artisan optimize:clear

if [[ "${APP_DEBUG,,}" == "true" ]]; then
    # Debug acikken view:cache kullanma (root/nginx izin catismasi + filemtime hatalari)
    echo "APP_DEBUG=true: view cache atlaniyor."
else
    run_artisan optimize
fi

chown -R nginx:nginx storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
systemctl reload php-fpm 2>/dev/null || true

echo "OK: optimize tamamlandi."
