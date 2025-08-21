#!/usr/bin/env sh
set -eu

echo "[ENTRYPOINT] Script starting..."

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
DEFAULT_FILE="$TARGET_DIR/service-account.json"

# Siapkan folder kredensial
mkdir -p "$TARGET_DIR"

# Path tujuan: dari ENV kalau ada, kalau tidak pakai default
TARGET_FILE="${FIREBASE_PRIVATE_KEY_PATH:-$DEFAULT_FILE}"

echo "[ENTRYPOINT] Preparing to write Firebase credentials..."

# --- Tulis kredensial Firebase ---
if [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  # JSON dalam base64 di env
  php -r 'file_put_contents("'"$TARGET_FILE"'", base64_decode(getenv("FIREBASE_CREDENTIALS_BASE64")));'
  echo "[ENTRYPOINT] Credentials successfully written from BASE64 variable."
elif [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
  # JSON mentah di env
  printf "%s" "$FIREBASE_CREDENTIALS_JSON" > "$TARGET_FILE"
  echo "[ENTRYPOINT] Credentials successfully written from JSON variable."
else
  echo "[ENTRYPOINT] No FIREBASE_CREDENTIALS_BASE64 or _JSON variable found."
fi

# Set permission & export var untuk SDK
if [ -s "$TARGET_FILE" ]; then
  chmod 600 "$TARGET_FILE" || true
  chown www-data:www-data "$TARGET_FILE" || true
  export GOOGLE_APPLICATION_CREDENTIALS="${GOOGLE_APPLICATION_CREDENTIALS:-$TARGET_FILE}"
  echo "[ENTRYPOINT] GOOGLE_APPLICATION_CREDENTIALS variable has been set."
else
  echo "[ENTRYPOINT] WARNING: Firebase credentials file is missing or empty after attempt to write." >&2
fi

# Patch port Apache untuk Railway bila PORT != 80
if [ -n "${PORT:-}" ]; then
  echo "[ENTRYPOINT] PORT environment variable found. Patching Apache port to ${PORT}..."
  sed -ri "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true
  sed -ri "s/\*:80/*:${PORT}/" /etc/apache2/sites-available/000-default.conf || true
  echo "[ENTRYPOINT] Apache port patched."
fi

echo "[ENTRYPOINT] Handing over execution to the main command (CMD)..."
# Jalankan perintah utama yang diteruskan dari Dockerfile (misal: "apache2-foreground")
exec "$@"