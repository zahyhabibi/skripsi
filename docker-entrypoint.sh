#!/usr/bin/env sh
set -eu

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
DEFAULT_FILE="$TARGET_DIR/service-account.json"

mkdir -p "$TARGET_DIR"

# Path tujuan: dari ENV kalau ada, kalau tidak pakai default
TARGET_FILE="${FIREBASE_PRIVATE_KEY_PATH:-$DEFAULT_FILE}"

# Tulis kredensial dari ENV (dukung 3 nama env)
if [ -n "${FIREBASE_CREDENTIALS:-}" ]; then
  printf "%s" "$FIREBASE_CREDENTIALS" > "$TARGET_FILE"
elif [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
  printf "%s" "$FIREBASE_CREDENTIALS_JSON" > "$TARGET_FILE"
elif [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  php -r 'file_put_contents("'"$TARGET_FILE"'", base64_decode(getenv("FIREBASE_CREDENTIALS_BASE64")));'
else
  echo "[entrypoint] ERROR: no FIREBASE_* credentials env set" >&2
fi

# Set permission & export var
if [ -s "$TARGET_FILE" ]; then
  chmod 600 "$TARGET_FILE" || true
  chown -R www-data:www-data "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" || true
  export GOOGLE_APPLICATION_CREDENTIALS="${GOOGLE_APPLICATION_CREDENTIALS:-$TARGET_FILE}"
  echo "[entrypoint] Firebase credentials written to $TARGET_FILE"
else
  echo "[entrypoint] ERROR: $TARGET_FILE missing or empty" >&2
fi

# (opsional) hilangkan warning AH00558
# echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf && a2enconf servername || true

exec apache2-foreground
