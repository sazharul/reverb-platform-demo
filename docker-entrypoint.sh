#!/bin/sh
set -e

if [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

php artisan migrate --force --seed

if [ ! -d public/build ]; then
  if command -v npm >/dev/null 2>&1; then
    npm ci && npm run build
  fi
fi

exec "$@"
