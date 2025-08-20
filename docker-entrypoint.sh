#!/usr/bin/env sh
set -eux

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
TARGET_FILE="$TARGET_DIR/iotskripsi-7d02b-firebase-adminsdk-fbsvc-73705e57ea.json"

echo "[entrypoint] start"
echo "[entrypoint] APP_ROOT=$APP_ROOT"
echo "[entrypoint] TARGET_DIR=$TARGET_DIR"
echo "[entrypoint] TARGET_FILE=$TARGET_FILE"

mkdir -p "$TARGET_DIR"

# Tulis file dari ENV (pakai PHP untuk decode base64 agar 100% ada)
if [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  echo "[entrypoint] writing credential from FIREBASE_CREDENTIALS_BASE64 (len=${#FIREBASE_CREDENTIALS_BASE64})"
  php -r 'file_put_contents("'"$TARGET_FILE"'", base64_decode(getenv("FIREBASE_CREDENTIALS_BASE64")));'
elif [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
  echo "[entrypoint] writing credential from FIREBASE_CREDENTIALS_JSON (raw json)"
  php -r 'file_put_contents("'"$TARGET_FILE"'", getenv("FIREBASE_CREDENTIALS_JSON"));'
else
  echo "[entrypoint][WARN] FIREBASE_CREDENTIALS_* env not set; NOT creating credential file" >&2
fi

# Tunjukkan hasilnya di log
if [ -f "$TARGET_FILE" ]; then
  echo "[entrypoint] file created:"
  ls -l "$TARGET_FILE"
  echo "[entrypoint] first 2 lines:"
  head -n 2 "$TARGET_FILE" || true
else
  echo "[entrypoint][ERROR] credential file missing at $TARGET_FILE" >&2
fi

# Permission aman + writable untuk Laravel
chmod 600 "$TARGET_FILE" || true
chown -R www-data:www-data "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" || true

echo "[entrypoint] starting apache..."
exec apache2-foreground
