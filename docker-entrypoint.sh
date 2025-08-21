#!/usr/bin/env sh
set -eu

echo "[ENTRYPOINT] Script starting..."

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
DEFAULT_FILE="$TARGET_DIR/service-account.json"

# Siapkan folder kredensial
mkdir -p "$TARGET_DIR"

echo "[ENTRYPOINT] Preparing to write Firebase credentials..."
# --- Tulis kredensial Firebase (hanya dari Base64 untuk konsistensi) ---
if [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  php -r 'file_put_contents("'"$DEFAULT_FILE"'", base64_decode(getenv("FIREBASE_CREDENTIALS_BASE64")));'
  echo "[ENTRYPOINT] Credentials successfully written from BASE64 variable."
else
  echo "[ENTRYPOINT] WARNING: FIREBASE_CREDENTIALS_BASE64 variable not found."
fi

# Set permission & export var untuk SDK
if [ -s "$DEFAULT_FILE" ]; then
  chmod 600 "$DEFAULT_FILE" || true
  chown www-data:www-data "$DEFAULT_FILE" || true
  export GOOGLE_APPLICATION_CREDENTIALS="$DEFAULT_FILE"
  echo "[ENTRYPOINT] GOOGLE_APPLICATION_CREDENTIALS variable has been set."
fi

# Patch port Apache untuk Railway
if [ -n "${PORT:-}" ]; then
  echo "[ENTRYPOINT] PORT environment variable found. Patching Apache port to ${PORT}..."
  sed -ri "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true
  sed -ri "s/\*:80/*:${PORT}/" /etc/apache2/sites-available/000-default.conf || true
fi

echo "[ENTRYPOINT] Handing over execution to the main command (CMD)..."
exec "$@"