#!/usr/bin/env sh
set -eu

APP_ROOT="/var/www/html"
TARGET_DIR="$APP_ROOT/storage/app/firebase"
DEFAULT_FILE="$TARGET_DIR/service-account.json"

# Siapkan folder kredensial
mkdir -p "$TARGET_DIR"

# Path tujuan: dari ENV kalau ada, kalau tidak pakai default
TARGET_FILE="${FIREBASE_PRIVATE_KEY_PATH:-$DEFAULT_FILE}"

# --- Tulis kredensial Firebase (prioritas: JSON -> BASE64 -> CREDENTIALS) ---
if [ -n "${FIREBASE_CREDENTIALS_JSON:-}" ]; then
  # JSON mentah di env
  printf "%s" "$FIREBASE_CREDENTIALS_JSON" > "$TARGET_FILE"
elif [ -n "${FIREBASE_CREDENTIALS_BASE64:-}" ]; then
  # JSON dalam base64 di env
  php -r 'file_put_contents("'"$TARGET_FILE"'", base64_decode(getenv("FIREBASE_CREDENTIALS_BASE64")));'
elif [ -n "${FIREBASE_CREDENTIALS:-}" ]; then
  # Bisa berupa PATH file atau JSON mentah
  case "$FIREBASE_CREDENTIALS" in
    /*)  # kelihatan seperti path absolut
      if [ -f "$FIREBASE_CREDENTIALS" ]; then
        cp "$FIREBASE_CREDENTIALS" "$TARGET_FILE"
      else
        # bukan file yang exist; treat sebagai JSON mentah
        printf "%s" "$FIREBASE_CREDENTIALS" > "$TARGET_FILE"
      fi
      ;;
    \{*) # diawali '{' -> kemungkinan JSON mentah
      printf "%s" "$FIREBASE_CREDENTIALS" > "$TARGET_FILE"
      ;;
    *)
      # fallback: treat sebagai JSON mentah
      printf "%s" "$FIREBASE_CREDENTIALS" > "$TARGET_FILE"
      ;;
  esac
else
  echo "[entrypoint] ERROR: no FIREBASE_* credentials env set" >&2
fi

# Set permission & export var untuk SDK
if [ -s "$TARGET_FILE" ]; then
  chmod 600 "$TARGET_FILE" || true
  chown -R www-data:www-data "$APP_ROOT/storage" "$APP_ROOT/bootstrap/cache" || true
  export GOOGLE_APPLICATION_CREDENTIALS="${GOOGLE_APPLICATION_CREDENTIALS:-$TARGET_FILE}"
  echo "[entrypoint] Firebase credentials written to $TARGET_FILE"
else
  echo "[entrypoint] ERROR: $TARGET_FILE missing or empty" >&2
fi

# (opsional) Hilangkan warning AH00558
# echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf && a2enconf servername || true

# Patch port Apache untuk Railway bila PORT != 80
if [ -n "${PORT:-}" ]; then
  sed -ri "s/^Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf || true
  sed -ri "s/\*:80/*:${PORT}/" /etc/apache2/sites-available/000-default.conf || true
fi

# Jalankan Apache sebagai proses utama
exec apache2-foreground
