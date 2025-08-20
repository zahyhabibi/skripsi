#!/usr/bin/env sh
set -eu

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
TARGET_FILE="$TARGET_DIR/iotskripsi-7d02b-firebase-adminsdk-fbsvc-73705e57ea.json"

mkdir -p "$TARGET_DIR"

if [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  echo "$FIREBASE_CREDENTIALS_BASE64" | base64 -d > "$TARGET_FILE"
elif [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
  printf '%s' "$FIREBASE_CREDENTIALS_JSON" > "$TARGET_FILE"
else
  echo "WARNING: FIREBASE_CREDENTIALS_* env not set; firebase credential file not created." >&2
fi

chmod 600 "$TARGET_FILE" || true
chown -R www-data:www-data "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" || true

exec apache2-foreground
